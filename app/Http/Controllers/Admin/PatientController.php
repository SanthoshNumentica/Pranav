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
        $filters = $request->all();
        $patients = $this->patientService->listPatients($filters, $request->get('limit', 15));

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
        $data = $request->validate([
            'patient_id' => 'nullable|string',
            'mrn_id' => 'nullable|string',
            'title_fk_id' => 'nullable|exists:titles,id',
            'name' => 'required|string|max:255',
            'father_name' => 'nullable|string|max:255',
            'email_id' => 'nullable|email|unique:patients,email_id',
            'mobile_no' => ['required', 'digits:10', 'regex:/^[6-9][0-9]{9}$/'],
            'gender_fk_id' => 'required|exists:genders,id',
            'place' => 'nullable|string',
            'dob' => 'nullable|date',
            'whatsapp_no' => ['nullable', 'digits:10', 'regex:/^[6-9][0-9]{9}$/'],
            'remarks' => 'nullable|string',
        ]);

        $data['added_by'] = auth()->id();

        $patient = $this->patientService->createPatient($data);

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
        $patient = \App\Models\Patient::findOrFail($id);
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
            'mrn_id' => 'sometimes|nullable|string',
            'title_fk_id' => 'sometimes|nullable|exists:titles,id',
            'name' => 'sometimes|required|string|max:255',
            'father_name' => 'sometimes|nullable|string|max:255',
            'email_id' => 'sometimes|nullable|email|unique:patients,email_id,' . $id,
            'mobile_no' => ['sometimes', 'required', 'digits:10', 'regex:/^[6-9][0-9]{9}$/'],
            'gender_fk_id' => 'sometimes|required|exists:genders,id',
            'place' => 'sometimes|nullable|string',
            'dob' => 'nullable|date',
            'whatsapp_no' => ['nullable', 'digits:10', 'regex:/^[6-9][0-9]{9}$/'],

            'remarks' => 'nullable|string',
            'doctor_id' => 'nullable|exists:doctors,id',
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
