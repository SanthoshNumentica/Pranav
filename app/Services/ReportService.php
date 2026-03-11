<?php

namespace App\Services;

use App\Models\CaseReport;
use App\Models\CaseReportItem;
use App\Models\ScanType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Traits\AdvancedDateFilterTrait;

class ReportService
{
    use AdvancedDateFilterTrait;

    /**
     * Get Case Analysis Report data including stats and paginated list.
     */
    public function getCaseAnalysisReport(array $filters = []): array
    {
        $perPage = $filters['limit'] ?? 10;

        $query = CaseReport::query()
            ->with(['patient', 'referer', 'branch', 'items.scanType']);

        // Applying Global Search
        $this->applySearchFilter($query, $filters['search'] ?? null);

        // Applying Branch Filter
        $this->applyBranchFilter($query, $filters['branch_id'] ?? 'all');

        // Applying Date Filters
        $this->applyDateFilters($query, $filters, 'scanning_date');

        // Get unfiltered stats (before scan_type_id filter)
        $unfilteredCaseIds = (clone $query)->pluck('id');

        // Apply Scan Type Filter for the final listing
        $this->applyScanTypeFilter($query, $filters['scan_types_fk_id'] ?? 'all');

        $query->orderBy('scanning_date', 'desc');

        if ($perPage == -1) {
            $data = $query->get();
            $paginatedData = new \Illuminate\Pagination\LengthAwarePaginator($data, $data->count(), $data->count() ?: 1, 1);
        } else {
            $paginatedData = $query->paginate($perPage);
        }

        // Calculate Scan Type Stats (based on date+branch+search filtered IDs, before scan type filter)

        // Calculate Scan Type Stats (based on date+branch+search filtered IDs, before scan type filter)
        $scanTypeStats = $this->calculateScanTypeStats($unfilteredCaseIds);

        return [
            'success' => true,
            'data' => $paginatedData,
            'report_stats' => [
                'total_cases'             => $paginatedData->total(),          // filtered by scan type too
                'total_cases_unfiltered'  => count($unfilteredCaseIds),        // date/branch/search only
                'scan_type_stats'         => $scanTypeStats,
            ],
        ];
    }

    /**
     * Apply Search Filter across multiple fields.
     */
    private function applySearchFilter(Builder $query, ?string $search): void
    {
        if (empty($search)) return;

        $query->where(function ($q) use ($search) {
            $q->where('case_id', 'like', "%{$search}%")
                ->orWhereHas('branch', function ($bq) use ($search) {
                    $bq->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('items.scanType', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('patient', function ($pq) use ($search) {
                    $pq->where('name', 'like', "%{$search}%")
                        ->orWhere('whatsapp_no', 'like', "%{$search}%")
                        ->orWhere('mobile_no', 'like', "%{$search}%");
                })
                ->orWhereHas('referer', function ($rq) use ($search) {
                    $rq->where('name', 'like', "%{$search}%")
                        ->orWhere('mobile_no', 'like', "%{$search}%");
                });
        });
    }

    /**
     * Apply Branch Filter.
     */
    private function applyBranchFilter($query, $branchId, string $column = 'branch_id'): void
    {
        if ($branchId && $branchId !== 'all') {
            $query->where($column, $branchId);
        }
    }

    /**
     * Apply Scan Type Filter.
     */
    private function applyScanTypeFilter($query, $scanTypeId): void
    {
        if ($scanTypeId && $scanTypeId !== 'all') {
            $query->whereHas('items', function ($q) use ($scanTypeId) {
                $q->where('scan_types_fk_id', $scanTypeId);
            });
        }
    }

    /**
     * Calculate stats for each scan type based on the current filtered case IDs.
     */
    private function calculateScanTypeStats($caseIds): array
    {
        return ScanType::select('id', 'name')
            ->get()->map(function ($type) use ($caseIds) {
                $count = CaseReportItem::whereIn('case_report_fk_id', $caseIds)
                    ->where('scan_types_fk_id', $type->id)
                    ->distinct('case_report_fk_id')
                    ->count();
                return [
                    'id' => $type->id,
                    'name' => $type->name,
                    'count' => $count
                ];
            })->filter(fn($stat) => $stat['count'] > 0)->values()->all();
    }

    /**
     * Get Invoice Analysis Report data.
     */
    public function getInvoiceAnalysisReport(array $filters = []): array
    {
        $perPage = $filters['limit'] ?? 10;
        $statusFilter = $filters['status_filter'] ?? 'all';

        $baseQuery = \App\Models\Invoice::query()
            ->with(['patient', 'caseReport.branch', 'caseReport.referer', 'caseReport.items.scanType'])
            ->withSum('payments', 'amount');

        // Apply Branch Filter
        $this->applyBranchFilter($baseQuery, $filters['branch_id'] ?? 'all');

        // Apply Search Filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $baseQuery->where(function ($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                    ->orWhereHas('patient', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Apply Date Filter
        $this->applyDateFilters($baseQuery, $filters, 'invoice_date');

        // ── Aggregate Stats (computed BEFORE status filter so cards always show date-range totals) ──
        $allInvoices = (clone $baseQuery)->get(['total_amount', 'paid_amount', 'status']);

        $totalRevenueCount  = $allInvoices->count();
        $totalRevenueAmount = $allInvoices->sum('total_amount');

        $fullyPaidGroup     = $allInvoices->where('status', 'fully_paid');
        $fullyPaidCount     = $fullyPaidGroup->count();
        $fullyPaidAmount    = $fullyPaidGroup->sum('total_amount');

        $pendingGroup       = $allInvoices->whereIn('status', ['unpaid', 'due']);
        $pendingCount       = $pendingGroup->count();
        $pendingAmount      = $pendingGroup->sum(fn ($i) => $i->total_amount - $i->paid_amount);

        // ── Apply Status Filter for table listing ──
        $listQuery = clone $baseQuery;
        if ($statusFilter === 'fully_paid') {
            $listQuery->where('status', 'fully_paid');
        } elseif ($statusFilter === 'pending') {
            $listQuery->whereIn('status', ['unpaid', 'due']);
        }

        $listQuery->orderBy('invoice_date', 'desc');

        if ($perPage == -1) {
            $data = $listQuery->get();
            $paginatedData = new \Illuminate\Pagination\LengthAwarePaginator($data, $data->count(), $data->count() ?: 1, 1);
        } else {
            $paginatedData = $listQuery->paginate($perPage);
        }

        return [
            'success' => true,
            'data'    => $paginatedData,
            'report_stats' => [
                // Legacy keys (kept for any other consumer)
                'total_revenue'         => $totalRevenueAmount,
                'paid_count'            => $fullyPaidCount,
                'pending_count'         => $pendingCount,
                // Enhanced keys
                'total_revenue_count'   => $totalRevenueCount,
                'total_revenue_amount'  => $totalRevenueAmount,
                'fully_paid_count'      => $fullyPaidCount,
                'fully_paid_amount'     => $fullyPaidAmount,
                'pending_count_detail'  => $pendingCount,
                'pending_amount'        => $pendingAmount,
            ],
        ];
    }

    /**
     * Get Referer × Scan Type pivot matrix for the P&L Analysis screen.
     */
    public function getRefererScanMatrix(array $filters = []): array
    {
        $perPage = $filters['limit'] ?? 10;
        $search = $filters['search'] ?? null;

        // 1. Define base query scope with common filters (Date, Branch, Search)
        $baseQuery = DB::table('case_report_items as cri')
            ->join('case_reports as cr', 'cri.case_report_fk_id', '=', 'cr.id')
            ->join('referers as r', 'cr.referer_fk_id', '=', 'r.id')
            ->whereNull('cri.deleted_at')
            ->whereNull('cr.deleted_at')
            ->whereNull('r.deleted_at');

        $this->applyDateFilters($baseQuery, $filters, 'cr.scanning_date');
        $this->applyBranchFilter($baseQuery, $filters['branch_id'] ?? 'all', 'cr.branch_id');

        if (!empty($search)) {
            $baseQuery->where('r.name', 'like', "%{$search}%");
        }

        // 2. Identify all filtered referers IDs
        $filteredRefererIds = (clone $baseQuery)->distinct()->pluck('r.id')->all();

        // 3. Calculate Global Column Totals and Scan Types for the entire filtered set
        $globalStatsQuery = (clone $baseQuery)
            ->join('scan_types as st', 'cri.scan_types_fk_id', '=', 'st.id')
            ->select(
                'st.id as scan_type_id',
                'st.name as scan_type_name',
                DB::raw('COUNT(DISTINCT cri.case_report_fk_id) as scan_count')
            )
            ->groupBy('st.id', 'st.name');

        $globalStats = $globalStatsQuery->get();

        $scanTypes = $globalStats->map(fn($row) => [
            'id' => $row->scan_type_id,
            'name' => $row->scan_type_name
        ])->sortBy('name')->values()->all();

        $columnTotals = $globalStats->pluck('scan_count', 'scan_type_id')->all();
        $grandTotal = array_sum($columnTotals);

        // 4. Paginate referers (the rows)
        if ($perPage == -1) {
            $rowsQuery = DB::table('referers')->whereIn('id', $filteredRefererIds)->orderBy('name')->get();
            $currentPageRefererIds = $rowsQuery->pluck('id')->all();
            $paginatedReferers = $rowsQuery; // Not really paginated, but the variable name is kept
        } else {
            $paginatedReferers = DB::table('referers')
                ->whereIn('id', $filteredRefererIds)
                ->orderBy('name')
                ->paginate($perPage);
            $currentPageRefererIds = $paginatedReferers->pluck('id')->all();
        }

        // 5. Fetch Matrix Data only for the current page referers
        $matrixData = (clone $baseQuery)
            ->join('scan_types as st', 'cri.scan_types_fk_id', '=', 'st.id')
            ->whereIn('r.id', $currentPageRefererIds)
            ->select(
                'r.id as referer_id',
                'r.name as referer_name',
                'st.id as scan_type_id',
                'st.name as scan_type_name',
                DB::raw('COUNT(DISTINCT cri.case_report_fk_id) as scan_count')
            )
            ->groupBy('r.id', 'r.name', 'st.id', 'st.name')
            ->get();

        // 6. Transform matrix data into rows
        $refererGroups = $matrixData->groupBy('referer_id');
        $rows = [];

        // We use the paginated referers to maintain consistent order even if some scan counts are zero
        foreach ($paginatedReferers as $referer) {
            $items = $refererGroups->get($referer->id) ?? collect();
            $counts = [];
            $rowTotal = 0;

            foreach ($items as $item) {
                $counts[$item->scan_type_id] = (int) $item->scan_count;
                $rowTotal += (int) $item->scan_count;
            }

            $rows[] = [
                'referer_id'   => $referer->id,
                'referer_name' => $referer->name,
                'counts'       => $counts,
                'total'        => $rowTotal,
            ];
        }

        return [
            'success'       => true,
            'data'          => $paginatedReferers, // Contains pagination metadata
            'scan_types'    => $scanTypes,
            'rows'          => $rows,
            'column_totals' => $columnTotals,
            'grand_total'   => $grandTotal,
        ];
    }



    /**
     * Get Dashboard Stats.
     * Moving this from DashboardController to centralize report logic.
     */
    public function getDashboardStats(?int $branchId = null): array
    {
        $today = Carbon::today();

        $statsQuery = [
            'total_patients' => \App\Models\Patient::query(),
            'total_referers' => \App\Models\Referer::query(),
            'total_case_reports' => \App\Models\CaseReport::query(),
            'today_case_reports' => \App\Models\CaseReport::whereDate('scanning_date', $today),
        ];

        if ($branchId) {
            // Note: Referers and Patients might not have branch_id directly, 
            // but CaseReport definitely does. 
            // In this specific system, Referers and Patients are currently global 
            // or we only filter CaseReports. Let's filter CaseReports by branch.
            $statsQuery['total_case_reports']->where('branch_id', $branchId);
            $statsQuery['today_case_reports']->where('branch_id', $branchId);
        }

        $stats = [
            'total_patients' => $statsQuery['total_patients']->count(),
            'total_referers' => $statsQuery['total_referers']->count(),
            'total_case_reports' => $statsQuery['total_case_reports']->count(),
            'today_case_reports' => $statsQuery['today_case_reports']->count(),
        ];

        $recentReportsQuery = CaseReport::with(['patient', 'referer', 'branch'])
            ->whereDate('scanning_date', $today)
            ->orderByRaw('check_out IS NULL DESC')
            ->orderBy('check_in', 'desc');

        if ($branchId) {
            $recentReportsQuery->where('branch_id', $branchId);
        }

        $recent_reports = $recentReportsQuery->get();

        $scanStatsQuery = DB::table('case_report_items')
            ->join('case_reports', 'case_report_items.case_report_fk_id', '=', 'case_reports.id')
            ->join('scan_types', 'case_report_items.scan_types_fk_id', '=', 'scan_types.id')
            ->select('scan_types.name', DB::raw('count(*) as total'));

        if ($branchId) {
            $scanStatsQuery->where('case_reports.branch_id', $branchId);
        }

        $scan_stats = $scanStatsQuery->groupBy('scan_types.id', 'scan_types.name')
            ->orderBy('total', 'desc')
            ->get();

        return [
            'stats' => $stats,
            'recent_reports' => $recent_reports,
            'scan_stats' => $scan_stats,
        ];
    }

    /**
     * Get Referer × Scan Type flat data for Excel export.
     */
    public function getRefererScanFlatData(array $filters = []): array
    {
        $query = DB::table('case_report_items as cri')
            ->join('case_reports as cr', 'cri.case_report_fk_id', '=', 'cr.id')
            ->join('referers as r', 'cr.referer_fk_id', '=', 'r.id')
            ->join('scan_types as st', 'cri.scan_types_fk_id', '=', 'st.id')
            ->whereNull('cri.deleted_at')
            ->whereNull('cr.deleted_at')
            ->whereNull('r.deleted_at')
            ->select(
                'r.name as referer_name',
                'st.name as scan_type_name',
                DB::raw('COUNT(cri.id) as total_scans'),
                DB::raw('SUM(cri.total_amount) as total_amount'),
                'cr.scanning_date as date'
            );

        $this->applyDateFilters($query, $filters, 'cr.scanning_date');
        $this->applyBranchFilter($query, $filters['branch_id'] ?? 'all', 'cr.branch_id');

        if (!empty($filters['search'])) {
            $query->where('r.name', 'like', "%{$filters['search']}%");
        }

        return $query->groupBy('r.name', 'st.name', 'cr.scanning_date')
            ->orderBy('cr.scanning_date', 'desc')
            ->get()
            ->toArray();
    }
}
