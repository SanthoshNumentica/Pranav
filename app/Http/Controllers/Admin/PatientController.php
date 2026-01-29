<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PatientService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PatientController extends Controller
{
    protected $patientService;

    public function __construct(PatientService $patientService)
    {
        $this->patientService = $patientService;
    }

    /**
     * Display a listing of patients.
     */
    public function index(Request $request): JsonResponse
    {
        $patients = $this->patientService->listPatients($request->all(), $request->get('limit', 15));

        return response()->json([
            'success' => true,
            'data' => $patients,
        ]);
    }

    /**
     * Store a newly created patient.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title_fk_id' => 'required|exists:titles,id',
            'name' => 'required|string|max:255',
            'father_name' => 'required|string|max:255',
            'email_id' => 'required|email|unique:patients,email_id',
            'mobile_no' => 'required|string',
            'gender_fk_id' => 'required|exists:genders,id',
            'address' => 'required|string',
            'street' => 'required|string',
            'pincode' => 'required|string',
            'city' => 'required|string',
            'dob' => 'nullable|date',
            'whatsapp_no' => 'nullable|string',
            'blood_group_fk_id' => 'nullable|exists:blood_groups,id',
            'remarks' => 'nullable|string',
        ]);

        $patient = $this->patientService->createPatient($request->all());

        return response()->json([
            'success' => true,
            'data' => $patient,
            'message' => 'Patient created successfully',
        ]);
    }

    /**
     * Display the specified patient.
     */
    public function show(int $id): JsonResponse
    {
        $patient = $this->patientService->updatePatient($id, []); // Just finding it
        return response()->json([
            'success' => true,
            'data' => $patient->load(['caseReports', 'gender']),
        ]);
    }

    /**
     * Update the specified patient.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'title_fk_id' => 'sometimes|required|exists:titles,id',
            'name' => 'sometimes|required|string|max:255',
            'father_name' => 'sometimes|required|string|max:255',
            'email_id' => 'sometimes|required|email|unique:patients,email_id,' . $id,
            'mobile_no' => 'sometimes|required|string',
            'gender_fk_id' => 'sometimes|required|exists:genders,id',
            'address' => 'sometimes|required|string',
            'street' => 'sometimes|required|string',
            'pincode' => 'sometimes|required|string',
            'city' => 'sometimes|required|string',
            'dob' => 'nullable|date',
            'whatsapp_no' => 'nullable|string',
            'blood_group_fk_id' => 'nullable|exists:blood_groups,id',
            'remarks' => 'nullable|string',
        ]);

        $patient = $this->patientService->updatePatient($id, $request->all());

        return response()->json([
            'success' => true,
            'data' => $patient,
            'message' => 'Patient updated successfully',
        ]);
    }

    /**
     * Remove the specified patient (soft delete).
     */
    public function destroy(int $id): JsonResponse
    {
        $this->patientService->deletePatient($id);

        return response()->json([
            'success' => true,
            'message' => 'Patient deleted successfully',
        ]);
    }

    /**
     * Update patient status.
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:active,inactive',
        ]);

        $patient = $this->patientService->updateStatus($id, $request->status);

        return response()->json([
            'success' => true,
            'data' => $patient,
            'message' => 'Patient status updated successfully',
        ]);
    }
}
