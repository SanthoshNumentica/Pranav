<?php

namespace App\Services;

use App\Models\CaseReport;
use App\Models\CaseReportItem;
use App\Models\ScanType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ReportService
{
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
        $this->applyDateFilters($query, $filters);

        // Get unfiltered stats (before scan_type_id filter)
        $unfilteredCaseIds = (clone $query)->pluck('id');

        // Apply Scan Type Filter for the final listing
        $this->applyScanTypeFilter($query, $filters['scan_type_id'] ?? 'all');

        $paginatedData = $query->latest()->paginate($perPage);
        $caseIds = $query->pluck('id');

        // Calculate Scan Type Stats
        $scanTypeStats = $this->calculateScanTypeStats($unfilteredCaseIds);

        return [
            'success' => true,
            'data' => $paginatedData,
            'report_stats' => [
                'total_cases' => count($caseIds),
                'total_cases_unfiltered' => count($unfilteredCaseIds),
                'scan_type_stats' => $scanTypeStats,
                'total' => count($caseIds),
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
    private function applyBranchFilter(Builder $query, $branchId): void
    {
        if ($branchId && $branchId !== 'all') {
            $query->where('branch_id', $branchId);
        }
    }

    /**
     * Apply Scan Type Filter.
     */
    private function applyScanTypeFilter(Builder $query, $scanTypeId): void
    {
        if ($scanTypeId && $scanTypeId !== 'all') {
            $query->whereHas('items', function ($q) use ($scanTypeId) {
                $q->where('scan_type_id', $scanTypeId);
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
                $count = CaseReportItem::whereIn('case_report_id', $caseIds)
                    ->where('scan_type_id', $type->id)
                    ->distinct('case_report_id')
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

        $query = \App\Models\Invoice::query()
            ->with(['patient'])
            ->withSum('payments', 'amount');

        // Apply Branch Filter (If Invoice has branch_id, check model)
        $this->applyBranchFilter($query, $filters['branch_id'] ?? 'all');

        // Apply Search Filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                    ->orWhereHas('patient', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Applying Date Filters (Using invoice_date for Invoices)
        $this->applyDateFilters($query, $filters, 'invoice_date');

        $paginatedData = $query->latest('invoice_date')->paginate($perPage);

        // Calculate Stats
        $statsQuery = clone $query;
        $totalRevenue = $statsQuery->sum('total_amount');
        
        $paidCount = (clone $query)->where('status', 'fully_paid')->count();
        $pendingCount = (clone $query)->whereIn('status', ['unpaid', 'due'])->count();

        return [
            'success' => true,
            'data' => $paginatedData,
            'report_stats' => [
                'total_revenue' => $totalRevenue,
                'paid_count' => $paidCount,
                'pending_count' => $pendingCount,
            ],
        ];
    }

    /**
     * Get Profit & Loss Analysis Report data.
     */
    public function getProfitLossReport(array $filters = []): array
    {
        $perPage = $filters['limit'] ?? 10;

        // Currently, we only have Income (Payments)
        // Expenses are not yet implemented in the system, but we'll leave placeholders
        $query = \App\Models\Payment::query()
            ->with(['invoice.patient', 'paymentMethod']);

        // Filter by Branch via Invoice
        if (!empty($filters['branch_id']) && $filters['branch_id'] !== 'all') {
            $query->whereHas('invoice', function ($q) use ($filters) {
                $q->where('branch_id', $filters['branch_id']);
            });
        }

        // Apply Search Filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                    ->orWhereHas('invoice.patient', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('paymentMethod', function ($mq) use ($search) {
                        $mq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Applying Date Filters (Using payment_date for Payments)
        $this->applyDateFilters($query, $filters, 'payment_date');

        $paginatedData = $query->latest('payment_date')->paginate($perPage);

        // Calculate Stats
        $statsQuery = clone $query;
        $totalIncome = $statsQuery->sum('amount');
        $totalExpenses = 0; // Placeholder
        $netProfit = $totalIncome - $totalExpenses;
        $profitPercentage = $totalIncome > 0 ? ($netProfit / $totalIncome) * 100 : 0;

        return [
            'success' => true,
            'data' => $paginatedData,
            'report_stats' => [
                'total_income' => $totalIncome,
                'total_expenses' => $totalExpenses,
                'net_profit' => $netProfit,
                'profit_percentage' => $profitPercentage,
            ],
        ];
    }

    /**
     * Helper to apply advanced date filters.
     */
    private function applyDateFilters(Builder $query, array $filters, string $dateColumn = 'created_at'): void
    {
        $type = $filters['filter_type'] ?? null;
        $option = $filters['filter_option'] ?? null;
        $startDate = $filters['from_date'] ?? $filters['start_date'] ?? null;
        $endDate = $filters['to_date'] ?? $filters['end_date'] ?? null;

        if (!$type || !$option) {
            if ($startDate && $endDate) {
                $query->whereBetween($dateColumn, [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            }
            return;
        }

        $start = null;
        $end = null;
        $now = Carbon::now();

        switch ($type) {
            case 'day':
                switch ($option) {
                    case 'today':
                        $start = $now->copy()->startOfDay();
                        $end = $now->copy()->endOfDay();
                        break;
                    case 'yesterday':
                        $start = $now->copy()->subDay()->startOfDay();
                        $end = $now->copy()->subDay()->endOfDay();
                        break;
                    case 'before_yesterday':
                        $start = $now->copy()->subDays(2)->startOfDay();
                        $end = $now->copy()->subDays(2)->endOfDay();
                        break;
                    case 'custom':
                        if ($startDate) {
                            $start = Carbon::parse($startDate)->startOfDay();
                            $end = Carbon::parse($startDate)->endOfDay();
                        }
                        break;
                }
                break;

            case 'week':
                switch ($option) {
                    case 'this_week':
                        $start = $now->copy()->startOfWeek();
                        $end = $now->copy()->endOfWeek();
                        break;
                    case 'last_week':
                        $start = $now->copy()->subWeek()->startOfWeek();
                        $end = $now->copy()->subWeek()->endOfWeek();
                        break;
                    case 'last_2_weeks':
                        $start = $now->copy()->subWeeks(2)->startOfWeek();
                        $end = $now->copy()->subWeek()->endOfWeek();
                        break;
                    case 'custom':
                        if ($startDate && $endDate) {
                            $start = Carbon::parse($startDate)->startOfDay();
                            $end = Carbon::parse($endDate)->endOfDay();
                        }
                        break;
                }
                break;

            case 'month':
                switch ($option) {
                    case 'this_month':
                        $start = $now->copy()->startOfMonth();
                        $end = $now->copy()->endOfMonth();
                        break;
                    case 'last_month':
                        $start = $now->copy()->subMonth()->startOfMonth();
                        $end = $now->copy()->subMonth()->endOfMonth();
                        break;
                    case 'last_3_months':
                        $start = $now->copy()->subMonths(2)->startOfMonth();
                        $end = $now->copy()->endOfMonth();
                        break;
                    case 'custom':
                        if ($startDate && $endDate) {
                            $start = Carbon::parse($startDate)->startOfMonth();
                            $end = Carbon::parse($endDate)->endOfMonth();
                        }
                        break;
                }
                break;

            case 'year':
                switch ($option) {
                    case 'this_year':
                        $start = $now->copy()->startOfYear();
                        $end = $now->copy()->endOfYear();
                        break;
                    case 'last_year':
                        $start = $now->copy()->subYear()->startOfYear();
                        $end = $now->copy()->subYear()->endOfYear();
                        break;
                    case 'last_3_years':
                        $start = $now->copy()->subYears(2)->startOfYear();
                        $end = $now->copy()->endOfYear();
                        break;
                    case 'custom':
                        if ($startDate && $endDate) {
                            $start = Carbon::parse($startDate)->startOfYear();
                            $end = Carbon::parse($endDate)->endOfYear();
                        }
                        break;
                }
                break;
        }

        if ($start && $end) {
            $query->whereBetween($dateColumn, [$start, $end]);
        }
    }

    /**
     * Get Dashboard Stats.
     * Moving this from DashboardController to centralize report logic.
     */
    public function getDashboardStats(): array
    {
        $stats = [
            'total_patients' => \App\Models\Patient::count(),
            'total_referers' => \App\Models\Referer::count(),
            'total_case_reports' => \App\Models\CaseReport::count(),
            'today_case_reports' => \App\Models\CaseReport::whereDate('created_at', Carbon::today())->count(),
        ];

        $recent_reports = CaseReport::with(['patient', 'referer'])
            ->latest()
            ->limit(5)
            ->get();

        $scan_stats = DB::table('case_report_items')
            ->join('scan_types', 'case_report_items.scan_type_id', '=', 'scan_types.id')
            ->select('scan_types.name', DB::raw('count(*) as total'))
            ->groupBy('scan_types.id', 'scan_types.name')
            ->orderBy('total', 'desc')
            ->get();

        return [
            'stats' => $stats,
            'recent_reports' => $recent_reports,
            'scan_stats' => $scan_stats,
        ];
    }
}
