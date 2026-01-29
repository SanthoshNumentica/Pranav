<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DoctorService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DoctorController extends Controller
{
    protected $doctorService;

    public function __construct(DoctorService $doctorService)
    {
        $this->doctorService = $doctorService;
    }

    /**
     * Display a listing of doctors.
     */
    public function index(Request $request): JsonResponse
    {
        $doctors = $this->doctorService->listDoctors($request->all(), $request->get('limit', 15));

        return response()->json([
            'success' => true,
            'data' => $doctors,
        ]);
    }

    /**
     * Store a newly created doctor.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title_fk_id' => 'required|exists:titles,id',
            'name' => 'required|string|max:255',
            'email_id' => 'required|email|unique:doctors,email_id',
            'mobile_no' => 'required|string',
            'gender_fk_id' => 'required|exists:genders,id',
            'address' => 'required|string',
            'street' => 'required|string',
            'pincode' => 'required|string',
            'city' => 'required|string',
            'blood_group_fk_id' => 'nullable|exists:blood_groups,id',
            'dob' => 'nullable|date',
        ]);

        $doctor = $this->doctorService->createDoctor($request->all());

        return response()->json([
            'success' => true,
            'data' => $doctor,
            'message' => 'Doctor created successfully',
        ]);
    }

    /**
     * Display the specified doctor.
     */
    public function show(int $id): JsonResponse
    {
        $doctor = $this->doctorService->updateDoctor($id, []); // Just finding it
        return response()->json([
            'success' => true,
            'data' => $doctor->load(['caseReports', 'gender']),
        ]);
    }

    /**
     * Update the specified doctor.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'title_fk_id' => 'sometimes|required|exists:titles,id',
            'name' => 'sometimes|required|string|max:255',
            'email_id' => 'sometimes|required|email|unique:doctors,email_id,' . $id,
            'mobile_no' => 'sometimes|required|string',
            'gender_fk_id' => 'sometimes|required|exists:genders,id',
            'address' => 'sometimes|required|string',
            'street' => 'sometimes|required|string',
            'pincode' => 'sometimes|required|string',
            'city' => 'sometimes|required|string',
            'blood_group_fk_id' => 'nullable|exists:blood_groups,id',
            'dob' => 'nullable|date',
        ]);

        $doctor = $this->doctorService->updateDoctor($id, $request->all());

        return response()->json([
            'success' => true,
            'data' => $doctor,
            'message' => 'Doctor updated successfully',
        ]);
    }

    /**
     * Remove the specified doctor (soft delete).
     */
    public function destroy(int $id): JsonResponse
    {
        $this->doctorService->deleteDoctor($id);

        return response()->json([
            'success' => true,
            'message' => 'Doctor deleted successfully',
        ]);
    }

    /**
     * Update doctor status.
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:active,inactive',
        ]);

        $doctor = $this->doctorService->updateStatus($id, $request->status);

        return response()->json([
            'success' => true,
            'data' => $doctor,
            'message' => 'Doctor status updated successfully',
        ]);
    }
}
