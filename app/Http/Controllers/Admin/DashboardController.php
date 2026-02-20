<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CaseReport;
use App\Models\Patient;
use App\Models\Referer;
use App\Models\ScanType;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $stats = [
            'total_patients' => Patient::count(),
            'total_referers' => Referer::count(),
            'total_case_reports' => CaseReport::count(),
            'today_case_reports' => CaseReport::whereDate('created_at', Carbon::today())->count(),
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

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'recent_reports' => $recent_reports,
                'scan_stats' => $scan_stats,
            ],
        ]);
    }
}
