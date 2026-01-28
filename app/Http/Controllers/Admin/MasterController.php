<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScanType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MasterController extends Controller
{
    public function scans(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => \App\Models\Scan::orderBy('scan_type_id')->get(),
        ]);
    }
    public function scanTypes(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => ScanType::with('scans')->get(),
        ]);
    }

    public function patients(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => \App\Models\Patient::orderBy('name')->get(),
        ]);
    }

    public function doctors(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => \App\Models\Doctor::orderBy('name')->get(),
        ]);
    }

    public function users(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => User::all(),
        ]);
    }
}
