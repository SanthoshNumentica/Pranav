<?php

namespace App\Services;

use App\Models\Patient;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class PatientService
{
    /**
     * Get paginated patients with filters.
     */
    public function listPatients(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Patient::query()
            ->with(['gender', 'bloodGroup', 'addedByUser', 'modifiedByUser'])
            ->when(isset($filters['search']), function (Builder $query) use ($filters) {
                $query->where(function ($q) use ($filters) {
                    $q->where('name', 'like', "%{$filters['search']}%")
                        ->orWhere('patient_id', 'like', "%{$filters['search']}%")
                        ->orWhere('mobile_no', 'like', "%{$filters['search']}%");
                });
            })
            ->when(isset($filters['status']) && $filters['status'] !== 'all', function (Builder $query) use ($filters) {
                $query->where('status', $filters['status']);
            }, function (Builder $query) {
                // Default to active if status filter is not provided or 'all' is not selected
                // Actually if user says "dont show inactive list", we should default to active.
                if (!request()->has('status')) {
                    $query->where('status', 'active');
                }
            })
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Generate a new Patient ID in the format PAT0001.
     */
    protected function generatePatientId(): string
    {
        $lastPatient = Patient::withTrashed()->orderBy('id', 'desc')->first();
        $nextId = $lastPatient ? $lastPatient->id + 1 : 1;
        return 'PAT' . str_pad((string) $nextId, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create a new patient.
     */
    public function createPatient(array $data): Patient
    {
        $data['patient_id'] = $this->generatePatientId();
        $data['status'] = 'active';
        return Patient::create($data);
    }

    /**
     * Update an existing patient.
     */
    public function updatePatient(int $id, array $data): Patient
    {
        $patient = Patient::findOrFail($id);
        $patient->update($data);
        return $patient;
    }

    /**
     * Delete a patient.
     */
    public function deletePatient(int $id): bool
    {
        $patient = Patient::findOrFail($id);
        return $patient->delete();
    }

    /**
     * Update patient status.
     */
    public function updateStatus(int $id, string $status): Patient
    {
        $patient = Patient::findOrFail($id);
        $patient->update(['status' => $status]);
        return $patient;
    }
}
