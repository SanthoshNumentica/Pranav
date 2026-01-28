<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PatientController extends Controller
{
    /**
     * Display a listing of patients.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Patient::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('patient_id', 'like', "%{$search}%")
                ->orWhere('mobile_no', 'like', "%{$search}%");
        }

        $patients = $query->with('gender')->latest()->paginate($request->get('limit', 15));

        return response()->json([
            'success' => true,
            'data' => $patients,
        ]);
    }

    /**
     * Display the specified patient.
     */
    public function show(int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Patient::with(['caseReports', 'gender'])->findOrFail($id),
        ]);
    }
}
