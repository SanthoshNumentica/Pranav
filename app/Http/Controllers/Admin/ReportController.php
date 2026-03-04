<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {
    }

    /**
     * Get Case Analysis Report.
     */
    public function caseAnalysis(Request $request): JsonResponse
    {
        $filters = $request->only([
            'filter_type', 
            'filter_option', 
            'from_date', 
            'to_date', 
            'start_date', 
            'end_date', 
            'search', 
            'branch_id', 
            'scan_type_id',
            'limit'
        ]);

        $data = $this->reportService->getCaseAnalysisReport($filters);

        return response()->json($data);
    }

    /**
     * Get Invoice Analysis Report.
     */
    public function invoiceAnalysis(Request $request): JsonResponse
    {
        $filters = $request->only([
            'filter_type', 
            'filter_option', 
            'from_date', 
            'to_date', 
            'start_date', 
            'end_date', 
            'search', 
            'branch_id',
            'limit'
        ]);

        $data = $this->reportService->getInvoiceAnalysisReport($filters);

        return response()->json($data);
    }

    /**
     * Get Profit & Loss Analysis Report.
     */
    public function profitLossAnalysis(Request $request): JsonResponse
    {
        $filters = $request->only([
            'filter_type', 
            'filter_option', 
            'from_date', 
            'to_date', 
            'start_date', 
            'end_date', 
            'search', 
            'branch_id',
            'limit'
        ]);

        $data = $this->reportService->getProfitLossReport($filters);

        return response()->json($data);
    }
}
