<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DoctorController extends Controller
{
    /**
     * Display a listing of doctors.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Doctor::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('email_id', 'like', "%{$search}%");
        }

        $doctors = $query->with('gender')->latest()->paginate($request->get('limit', 15));

        return response()->json([
            'success' => true,
            'data' => $doctors,
        ]);
    }

    /**
     * Display the specified doctor.
     */
    public function show(int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Doctor::with(['caseReports', 'gender'])->findOrFail($id),
        ]);
    }
}
