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
            $request->only(['status', 'search']),
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
            'patient_fk_id' => ['required', 'exists:patients,id'],
            'doc_ref_fk_id' => ['required', 'exists:doctors,id'],
            'description' => ['nullable', 'string'],
            'documents' => ['nullable', 'array'],
            'documents.*' => ['required', 'string'], // Pre-uploaded paths
            'items' => ['required', 'array', 'min:1'],
            'items.*.scan_type_id' => ['required', 'exists:scan_types,id'],
            'items.*.scan_id' => ['required', 'exists:scans,id'],
            'items.*.documents' => ['required', 'array'],
            'items.*.documents.*' => ['required', 'string'], // Pre-uploaded paths
            'items.*.remarks' => ['nullable', 'string'],
        ]);

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
            'patient_fk_id' => ['required', 'exists:patients,id'],
            'doc_ref_fk_id' => ['required', 'exists:doctors,id'],
            'description' => ['nullable', 'string'],
            'documents' => ['nullable', 'array'],
            'documents.*' => ['required', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.scan_type_id' => ['required', 'exists:scan_types,id'],
            'items.*.scan_id' => ['required', 'exists:scans,id'],
            'items.*.documents' => ['required', 'array'],
            'items.*.documents.*' => ['required', 'string'],
            'items.*.remarks' => ['nullable', 'string'],
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
            'recipients.*' => ['required', 'in:doctor,patient'],
        ]);

        $result = $this->caseReportService->sendWhatsAppNotification($id, $request->recipients);

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
