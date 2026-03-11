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
            ->with(['gender', 'addedByUser', 'modifiedByUser'])
            ->when(isset($filters['search']), function (Builder $query) use ($filters) {
                $query->where(function ($q) use ($filters) {
                    $q->where('name', 'like', "%{$filters['search']}%")
                        ->orWhere('patient_id', 'like', "%{$filters['search']}%")
                        ->orWhere('mrn_id', 'like', "%{$filters['search']}%")
                        ->orWhere('mobile_no', 'like', "%{$filters['search']}%")
                        ->orWhere('place', 'like', "%{$filters['search']}%");
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
     * Create or update a patient based on details array (orchestration helper).
     */
    public function createOrUpdatePatient(array $data): int
    {
        if (!empty($data['patient_fk_id'])) {
            $patient = Patient::find($data['patient_fk_id']);
            if ($patient) {
                $updateData = array_filter([
                    'title_fk_id' => $data['title_fk_id'] ?? null,
                    'name' => $data['name'] ?? null,
                    'place' => $data['place'] ?? null,
                    'whatsapp_no' => $data['whatsapp_no'] ?? null,
                    'mobile_no' => $data['mobile_no'] ?? null,
                    'gender_fk_id' => $data['gender_fk_id'] ?? null,
                ], fn($v) => !is_null($v));
                
                if (!empty($updateData)) {
                    $updateData['modified_by'] = auth()->id();
                    $patient->update($updateData);
                }
                return $patient->id;
            }
        }
        
        $newPatient = $this->createPatient([
            'title_fk_id' => $data['title_fk_id'] ?? null,
            'name' => $data['name'],
            'place' => $data['place'] ?? null,
            'whatsapp_no' => $data['whatsapp_no'] ?? null,
            'mobile_no' => $data['mobile_no'] ?? null,
            'gender_fk_id' => $data['gender_fk_id'] ?? null,
            'added_by' => auth()->id()
        ]);

        return $newPatient->id;
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
