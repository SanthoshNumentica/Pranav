<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CaseReportService;
use App\Models\CaseReport;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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
            $request->only(['status', 'search', 'branch_id']),
            $request->get('limit', 10)
        );

        return response()->json([
            'success' => true,
            'data' => $caseReports,
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
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'case_id' => ['required', 'string', 'max:255'],
            'patient_fk_id' => ['required', 'exists:patients,id'],
            'referer_id' => ['required', 'exists:referers,id'],
            'description' => ['nullable', 'string'],
            'documents' => ['nullable', 'array'],
            'documents.*' => ['required', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.scan_type_id' => ['required', 'exists:scan_types,id'],
            'items.*.scan_id' => ['required', 'exists:scans,id'],
            'items.*.documents' => ['nullable', 'array'],
            'items.*.documents.*' => ['required', 'string'],
            'items.*.remarks' => ['nullable', 'string'],
            'items.*.amount' => ['nullable', 'numeric', 'min:0'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'rct_date' => ['nullable', 'date'],
            'rct_hour' => ['nullable', 'string'],
            'is_stat' => ['nullable', 'boolean'],
            'patient_type' => ['nullable', 'in:in_patient,out_patient'],
        ]);

        if (auth()->user()->branch_id) {
            $data['branch_id'] = auth()->user()->branch_id;
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
    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'case_id' => ['required', 'string', 'max:255'],
            'patient_fk_id' => ['required', 'exists:patients,id'],
            'referer_id' => ['required', 'exists:referers,id'],
            'description' => ['nullable', 'string'],
            'documents' => ['nullable', 'array'],
            'documents.*' => ['required', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.scan_type_id' => ['required', 'exists:scan_types,id'],
            'items.*.scan_id' => ['required', 'exists:scans,id'],
            'items.*.documents' => ['nullable', 'array'],
            'items.*.documents.*' => ['required', 'string'],
            'items.*.remarks' => ['nullable', 'string'],
            'items.*.amount' => ['nullable', 'numeric', 'min:0'],
            'rct_date' => ['nullable', 'date'],
            'rct_hour' => ['nullable', 'string'],
            'is_stat' => ['nullable', 'boolean'],
            'patient_type' => ['nullable', 'in:in_patient,out_patient'],
            'branch_id' => ['nullable', 'exists:branches,id'],
        ]);

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
