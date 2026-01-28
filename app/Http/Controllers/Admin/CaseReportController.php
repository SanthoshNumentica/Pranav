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
            $request->get('limit', 15)
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
            'remarks' => ['nullable', 'string'],
            'documents' => ['nullable', 'array'],
            'documents.*' => ['required', 'string'], // Pre-uploaded paths
            'items' => ['required', 'array', 'min:1'],
            'items.*.scan_type_id' => ['required', 'exists:scan_types,id'],
            'items.*.scan_id' => ['required', 'exists:scans,id'],
            'items.*.remarks' => ['nullable', 'string'],
            'items.*.documents' => ['required', 'array'],
            'items.*.documents.*' => ['required', 'string'], // Pre-uploaded paths
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
            'remarks' => ['nullable', 'string'],
            'documents' => ['nullable', 'array'],
            'documents.*' => ['required', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.scan_type_id' => ['required', 'exists:scan_types,id'],
            'items.*.scan_id' => ['required', 'exists:scans,id'],
            'items.*.remarks' => ['nullable', 'string'],
            'items.*.documents' => ['required', 'array'],
            'items.*.documents.*' => ['required', 'string'],
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
            'status' => ['required', 'in:pending,closed'],
        ]);

        $caseReport = $this->caseReportService->getCaseReport($id);

        $updatedCaseReport = $this->caseReportService->updateStatus($caseReport, $request->status);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully.',
            'data' => $updatedCaseReport,
        ]);
    }
}
