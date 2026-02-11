<?php

namespace App\Services;

use App\Models\Doctor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class DoctorService
{
    /**
     * Get paginated doctors with filters.
     */
    public function listDoctors(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Doctor::query()
            ->with(['gender', 'bloodGroup', 'addedBy', 'modifiedBy'])
            ->when(isset($filters['search']), function (Builder $query) use ($filters) {
                $query->where(function ($q) use ($filters) {
                    $q->where('name', 'like', "%{$filters['search']}%")
                        ->orWhere('doctor_id', 'like', "%{$filters['search']}%")
                        ->orWhere('email_id', 'like', "%{$filters['search']}%");
                });
            })
            ->when(isset($filters['status']) && $filters['status'] !== 'all', function (Builder $query) use ($filters) {
                $query->where('status', $filters['status']);
            }, function (Builder $query) {
                if (!request()->has('status')) {
                    $query->where('status', 'active');
                }
            })
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Generate a new Doctor ID in the format DOC0001.
     */
    protected function generateDoctorId(): string
    {
        $lastDoctor = Doctor::withTrashed()->orderBy('id', 'desc')->first();
        $nextId = $lastDoctor ? $lastDoctor->id + 1 : 1;
        return 'DOC' . str_pad((string) $nextId, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create a new doctor.
     */
    public function createDoctor(array $data): Doctor
    {
        $data['doctor_id'] = $this->generateDoctorId();
        $data['status'] = 'active';
        $data['added_by'] = auth()->id();
        return Doctor::create($data);
    }

    /**
     * Update an existing doctor.
     */
    public function updateDoctor(int $id, array $data): Doctor
    {
        $doctor = Doctor::findOrFail($id);
        $data['modified_by'] = auth()->id();
        $doctor->update($data);
        return $doctor;
    }

    /**
     * Delete a doctor.
     */
    public function deleteDoctor(int $id): bool
    {
        $doctor = Doctor::findOrFail($id);
        return $doctor->delete();
    }

    /**
     * Update doctor status.
     */
    public function updateStatus(int $id, string $status): Doctor
    {
        $doctor = Doctor::findOrFail($id);
        $doctor->update(['status' => $status]);
        return $doctor;
    }
}
