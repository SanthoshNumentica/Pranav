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
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'case_id' => ['required', 'string', 'max:255'],
            'patient_fk_id' => ['required', 'exists:patients,id'],
            'patient_name' => ['nullable', 'string', 'max:255'],
            'patient_place' => ['nullable', 'string', 'max:255'],
            'whatsapp_no_patient' => ['nullable', 'string', 'max:20'],
            'referer_id' => ['required', 'exists:referers,id'],
            'referer_name' => ['nullable', 'string', 'max:255'],
            'whatsapp_no_referer' => ['nullable', 'string', 'max:20'],
            'hospital_name' => ['nullable', 'string', 'max:255'],
            'hospital_id' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'documents' => ['nullable', 'array'],
            'documents.*' => ['required', 'string'],
            'case_report_items' => ['required', 'array', 'min:1'],
            'case_report_items.*.id' => ['nullable', 'exists:case_report_items,id'],
            'case_report_items.*.case_report_item_id' => ['nullable', 'exists:case_report_items,id'],
            'case_report_items.*.action' => ['nullable', 'integer', 'in:1,2,3'],
            'case_report_items.*.scan_type_id' => ['required_unless:case_report_items.*.action,3', 'nullable', 'exists:scan_types,id'],
            'case_report_items.*.scans' => ['required_unless:case_report_items.*.action,3', 'nullable', 'array', 'min:1'],
            'case_report_items.*.scans.*.scan_id' => ['required', 'exists:scans,id'],
            'case_report_items.*.scans.*.scan_name' => ['nullable', 'string', 'max:255'],
            'case_report_items.*.scans.*.amount' => ['nullable', 'numeric', 'min:0'],
            'case_report_items.*.item_reference' => ['nullable', 'string', 'max:255'],
            'case_report_items.*.group_token' => ['nullable', 'string', 'max:255'],
            'case_report_items.*.documents' => ['nullable', 'array'],
            'case_report_items.*.documents.*' => ['required', 'string'],
            'case_report_items.*.remarks' => ['nullable', 'string'],
            'case_report_items.*.amount' => ['nullable', 'numeric', 'min:0'],
            'case_report_items.*.total_amount' => ['nullable', 'numeric', 'min:0'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'scanning_date' => ['nullable', 'date'],
            'check_in' => ['nullable', 'string'],
            'is_stat_case' => ['nullable', 'boolean'],
            // Invoice Fields
            'invoice_date' => ['nullable', 'date'],
            'discount_id' => ['nullable'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'invoice_items' => ['nullable', 'array'],
            'invoice_items.*.id' => ['nullable', 'exists:invoice_items,id'],
            'invoice_items.*.invoice_item_id' => ['nullable', 'exists:invoice_items,id'],
            'invoice_items.*.case_report_item_id' => ['nullable', 'exists:case_report_items,id'],
            'invoice_items.*.scan_id' => ['nullable', 'exists:scans,id'],
            'invoice_items.*.action' => ['nullable', 'integer', 'in:1,2,3'],
            'invoice_items.*.description' => ['sometimes', 'required_unless:invoice_items.*.action,3', 'nullable', 'string'],
            'invoice_items.*.amount' => ['sometimes', 'required_unless:invoice_items.*.action,3', 'nullable', 'numeric', 'min:0'],
        ]);

        if (auth()->user()->branch_id) {
            $data['branch_id'] = auth()->user()->branch_id;
        }

        $data['discount_id'] = $this->resolveDiscountId($data['discount_id'] ?? null);

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
            'patient_name' => ['nullable', 'string', 'max:255'],
            'patient_place' => ['nullable', 'string', 'max:255'],
            'whatsapp_no_patient' => ['nullable', 'string', 'max:20'],
            'referer_id' => ['required', 'exists:referers,id'],
            'referer_name' => ['nullable', 'string', 'max:255'],
            'whatsapp_no_referer' => ['nullable', 'string', 'max:20'],
            'hospital_name' => ['nullable', 'string', 'max:255'],
            'hospital_id' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'documents' => ['nullable', 'array'],
            'documents.*' => ['required', 'string'],
            'case_report_items' => ['required', 'array', 'min:1'],
            'case_report_items.*.id' => ['nullable', 'exists:case_report_items,id'],
            'case_report_items.*.case_report_item_id' => ['nullable', 'exists:case_report_items,id'],
            'case_report_items.*.action' => ['nullable', 'integer', 'in:1,2,3'],
            'case_report_items.*.scan_type_id' => ['required_unless:case_report_items.*.action,3', 'nullable', 'exists:scan_types,id'],
            'case_report_items.*.scans' => ['required_unless:case_report_items.*.action,3', 'nullable', 'array', 'min:1'],
            'case_report_items.*.scans.*.scan_id' => ['required', 'exists:scans,id'],
            'case_report_items.*.scans.*.scan_name' => ['nullable', 'string', 'max:255'],
            'case_report_items.*.scans.*.amount' => ['nullable', 'numeric', 'min:0'],
            'case_report_items.*.item_reference' => ['nullable', 'string', 'max:255'],
            'case_report_items.*.documents' => ['nullable', 'array'],
            'case_report_items.*.documents.*' => ['required', 'string'],
            'case_report_items.*.remarks' => ['nullable', 'string'],
            'case_report_items.*.amount' => ['nullable', 'numeric', 'min:0'],
            'case_report_items.*.total_amount' => ['nullable', 'numeric', 'min:0'],
            'scanning_date' => ['nullable', 'date'],
            'check_in' => ['nullable', 'string'],
            'is_stat_case' => ['nullable', 'boolean'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            // Invoice Fields
            'invoice_date' => ['nullable', 'date'],
            'discount_id' => ['nullable'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'invoice_items' => ['nullable', 'array'],
            'invoice_items.*.id' => ['nullable', 'exists:invoice_items,id'],
            'invoice_items.*.invoice_item_id' => ['nullable', 'exists:invoice_items,id'],
            'invoice_items.*.case_report_item_id' => ['nullable', 'exists:case_report_items,id'],
            'invoice_items.*.scan_id' => ['nullable', 'exists:scans,id'],
            'invoice_items.*.action' => ['nullable', 'integer', 'in:1,2,3'],
            'invoice_items.*.description' => ['sometimes', 'required_unless:invoice_items.*.action,3', 'nullable', 'string'],
            'invoice_items.*.amount' => ['sometimes', 'required_unless:invoice_items.*.action,3', 'nullable', 'numeric', 'min:0'],
        ]);

        $data['discount_id'] = $this->resolveDiscountId($data['discount_id'] ?? null);

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

    /**
     * Resolve discount_id if it's a name or "custom".
     */
    private function resolveDiscountId($discountId): ?int
    {
        if (empty($discountId) || $discountId === 'custom') {
            return null;
        }

        if (is_numeric($discountId)) {
            return (int) $discountId;
        }

        // If it's a string name, try to find the ID
        $discount = \App\Models\Discount::where('name', $discountId)->first();
        return $discount ? $discount->id : null;
    }
}
