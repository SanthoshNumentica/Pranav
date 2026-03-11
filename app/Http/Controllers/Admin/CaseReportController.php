<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CaseReportService;
use App\Models\CaseReport;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Admin\CaseReport\StoreCaseReportRequest;
use App\Http\Requests\Admin\CaseReport\UpdateCaseReportRequest;
use App\Services\PatientService;
use App\Services\RefererService;
use App\Services\PaymentService;
use App\Services\InvoiceService;

class CaseReportController extends Controller
{
    public function __construct(
        protected CaseReportService $caseReportService
    ) {
    }

    /**
     * Display a listing of case reports.
     */
    public function index(Request $request): JsonResponse
    {
        $caseReports = $this->caseReportService->listCaseReports(
            $request->only(['status', 'search', 'branch_id', 'from_date', 'to_date', 'filter_type', 'filter_option', 'start_date', 'end_date']),
            $request->get('limit', 10)
        );

        $stats = $this->caseReportService->getReportStats(
            $request->only(['from_date', 'to_date', 'branch_id', 'filter_type', 'filter_option', 'start_date', 'end_date'])
        );

        return response()->json([
            'success' => true,
            'data' => $caseReports,
            'report_stats' => $stats,
        ]);
    }

    /**
     * Display the specified case report.
     */
    public function show(int $id): JsonResponse
    {
        $caseReport = $this->caseReportService->getCaseReport($id);

        return response()->json([
            'success' => true,
            'data' => $caseReport,
        ]);
    }

    /**
     * Store a newly created case report.
     */
    public function store(StoreCaseReportRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (auth()->user()->branch_id && !empty($data['case_reports'])) {
            $data['case_reports']['branch_fk_id'] = auth()->user()->branch_id;
        }

        if (isset($data['invoice_details']) && isset($data['invoice_details']['discount_fk_id'])) {
            $data['invoice_details']['discount_fk_id'] = app(InvoiceService::class)->resolveDiscountId($data['invoice_details']['discount_fk_id']);
        }

        $caseReport = $this->caseReportService->createCaseReport($data);

        return response()->json([
            'success' => true,
            'message' => 'Case report created successfully.',
            'data' => $caseReport,
        ]);
    }

    /**
     * Update an existing case report.
     */
    public function update(UpdateCaseReportRequest $request, int $id): JsonResponse
    {
        $data = $request->validated();

        if (isset($data['invoice_details']) && isset($data['invoice_details']['discount_fk_id'])) {
            $data['invoice_details']['discount_fk_id'] = app(InvoiceService::class)->resolveDiscountId($data['invoice_details']['discount_fk_id']);
        }

        $caseReport = $this->caseReportService->updateCaseReport($id, $data);

        return response()->json([
            'success' => true,
            'message' => 'Case report updated successfully.',
            'data' => $caseReport,
        ]);
    }

    /**
     * Update the status of the specified case report.
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'in:pending,available,expired,deleted'],
        ]);

        $caseReport = $this->caseReportService->getCaseReport($id);

        $updatedCaseReport = $this->caseReportService->updateStatus($caseReport, $request->status);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully.',
            'data' => $updatedCaseReport,
        ]);
    }

    /**
     * Update the check-out time of the specified case report.
     */
    public function updateCheckOut(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'check_out' => ['required', 'date_format:H:i'],
        ]);

        $caseReport = CaseReport::findOrFail($id);
        
        if (!$caseReport->check_in) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot check-out without a check-in time.',
            ], 422);
        }

        $caseReport->update(['check_out' => $request->check_out]);

        return response()->json([
            'success' => true,
            'message' => 'Check-out time updated successfully.',
            'data' => $caseReport,
        ]);
    }

    public function getNextCaseId(): JsonResponse
    {
        \Log::info("Fetching next Case ID");
        try {
            $nextId = $this->caseReportService->getNextCaseId();
            \Log::info("Next Case ID generated: " . $nextId);
            return response()->json(['success' => true, 'next_case_id' => $nextId]);
        } catch (\Exception $e) {
            \Log::error("Error in getNextCaseId: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to generate ID'], 500);
        }
    }

    public function getNextItemCustomId(int $scanTypeId): JsonResponse
    {
        try {
            $nextId = $this->caseReportService->getNextItemReference($scanTypeId);
            return response()->json(['success' => true, 'next_custom_id' => $nextId]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified case report from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->caseReportService->deleteCaseReport($id);

        return response()->json([
            'success' => true,
            'message' => 'Case report deleted successfully.',
        ]);
    }

    /**
     * Send WhatsApp notification for the case report.
     */
    public function notifyWhatsApp(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'recipients' => ['required', 'array', 'min:1'],
            'recipients.*' => ['required', 'in:referer,patient'],
            'custom_numbers' => ['nullable', 'array'],
            'custom_numbers.referer' => ['nullable', 'string'],
            'custom_numbers.patient' => ['nullable', 'string'],
        ]);

        $result = $this->caseReportService->sendWhatsAppNotification(
            $id,
            $request->recipients,
            $request->get('custom_numbers', [])
        );

        return response()->json([
            'success' => $result['status'],
            'message' => $result['message'],
            'results' => $result['results'] ?? null,
        ]);
    }

    /**
     * Get a public case report by sharing_token.
     */
    public function showPublic(string $token): JsonResponse
    {
        $data = $this->caseReportService->getPublicCaseReport($token);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
