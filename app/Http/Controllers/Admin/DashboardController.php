<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {
    }

    public function index(): JsonResponse
    {
        $data = $this->reportService->getDashboardStats();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
