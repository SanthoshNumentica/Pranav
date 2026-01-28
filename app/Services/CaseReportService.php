<?php

namespace App\Services;

use App\Models\CaseReport;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class CaseReportService
{
    /**
     * Get paginated case reports with filters.
     */
    public function listCaseReports(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return CaseReport::query()
            ->with(['patient.gender', 'doctor', 'items.scanType', 'items.scan'])
            ->when(isset($filters['status']), function (Builder $query) use ($filters) {
                $query->where('status', $filters['status']);
            })
            ->when(isset($filters['search']), function (Builder $query) use ($filters) {
                $query->where('case_id', 'like', "%{$filters['search']}%")
                    ->orWhereHas('patient', function ($q) use ($filters) {
                        $q->where('name', 'like', "%{$filters['search']}%")
                            ->orWhere('patient_id', 'like', "%{$filters['search']}%");
                    });
            })
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get a single case report by ID.
     */
    public function getCaseReport(int $id): CaseReport
    {
        return CaseReport::with(['patient.gender', 'doctor', 'items.scanType', 'items.scan'])->findOrFail($id);
    }

    /**
     * Generate a new Case ID in the format CAS0001.
     */
    protected function generateCaseId(): string
    {
        $lastCase = CaseReport::withTrashed()->orderBy('id', 'desc')->first();
        $nextId = $lastCase ? $lastCase->id + 1 : 1;
        return 'CAS' . str_pad((string) $nextId, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create a new case report with items and documents.
     */
    public function createCaseReport(array $data): CaseReport
    {
        return \DB::transaction(function () use ($data) {
            $hasDocuments = false;

            // 1. Handle General Documents (Paths already uploaded via FileController)
            $generalDocPaths = $data['documents'] ?? [];

            // 2. Create the Case Report
            $caseReport = CaseReport::create([
                'case_id' => $this->generateCaseId(),
                'patient_fk_id' => $data['patient_fk_id'],
                'doc_ref_fk_id' => $data['doc_ref_fk_id'],
                'description' => $data['description'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'documents' => $generalDocPaths,
                'status' => 'pending', // Default, updated later
            ]);

            // 3. Process Items
            foreach ($data['items'] as $index => $itemData) {
                $documentPaths = $itemData['documents'] ?? [];

                if (count($documentPaths) > 0) {
                    $hasDocuments = true;
                }

                $caseReport->items()->create([
                    'scan_type_id' => $itemData['scan_type_id'],
                    'scan_id' => $itemData['scan_id'],
                    'remarks' => $itemData['remarks'] ?? null,
                    'documents' => $documentPaths,
                ]);
            }

            // 3. Update Status
            if ($hasDocuments) {
                $caseReport->update(['status' => 'closed']);
            }

            // 4. Post-save Hook: Orthanc Sync
            try {
                app(OrthancService::class)->uploadCaseReport($caseReport->id);
            } catch (\Exception $e) {
                \Log::error("CaseReportService Sync Failed: " . $e->getMessage());
            }

            return $caseReport->load(['patient', 'doctor', 'items']);
        });
    }

    /**
     * Update an existing case report with items and documents.
     */
    public function updateCaseReport(int $id, array $data): CaseReport
    {
        return \DB::transaction(function () use ($id, $data) {
            $caseReport = CaseReport::findOrFail($id);
            $hasDocuments = false;

            // 1. Update main Case Report fields
            $caseReport->update([
                'patient_fk_id' => $data['patient_fk_id'],
                'doc_ref_fk_id' => $data['doc_ref_fk_id'],
                'description' => $data['description'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'documents' => $data['documents'] ?? [],
            ]);

            // 2. Refresh Items (Delete and Re-create for simplicity and consistency)
            $caseReport->items()->delete();

            foreach ($data['items'] as $itemData) {
                $documentPaths = $itemData['documents'] ?? [];

                if (count($documentPaths) > 0) {
                    $hasDocuments = true;
                }

                $caseReport->items()->create([
                    'scan_type_id' => $itemData['scan_type_id'],
                    'scan_id' => $itemData['scan_id'],
                    'remarks' => $itemData['remarks'] ?? null,
                    'documents' => $documentPaths,
                ]);
            }

            // 3. Update Status
            $status = $hasDocuments ? 'closed' : 'pending';
            $caseReport->update(['status' => $status]);

            // 4. Update Orthanc Sync
            try {
                app(OrthancService::class)->uploadCaseReport($caseReport->id);
            } catch (\Exception $e) {
                \Log::error("CaseReportService Update Sync Failed: " . $e->getMessage());
            }

            return $caseReport->load(['patient', 'doctor', 'items']);
        });
    }

    /**
     * Update case report status.
     */
    public function updateStatus(CaseReport $caseReport, string $status): CaseReport
    {
        $caseReport->update(['status' => $status]);
        return $caseReport;
    }
}
