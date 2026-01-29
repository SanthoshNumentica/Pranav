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
        // Automatically mark as expired if time has passed
        CaseReport::where('status', 'available')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->update(['status' => 'expired']);

        return CaseReport::query()
            ->with(['patient.gender', 'doctor', 'items.scanType', 'items.scan'])
            ->when(isset($filters['status']), function (Builder $query) use ($filters) {
                $query->where('status', $filters['status']);
                if ($filters['status'] === 'deleted') {
                    $query->onlyTrashed();
                }
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
            if (count($generalDocPaths) > 0) {
                $hasDocuments = true;
            }

            // 2. Create the Case Report
            $caseReport = CaseReport::create([
                'case_id' => $this->generateCaseId(),
                'patient_fk_id' => $data['patient_fk_id'],
                'doc_ref_fk_id' => $data['doc_ref_fk_id'],
                'description' => $data['description'] ?? null,
                'documents' => $generalDocPaths,
                'status' => 'available',
                'sharing_token' => \Illuminate\Support\Str::random(32),
                'expires_at' => now()->addDays(30),
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
                    'documents' => $documentPaths,
                    'remarks' => $itemData['remarks'] ?? null,
                ]);
            }

            // 3. Update Status and Expiry
            // 3. Status and Expiry are already set to available/30 days by default.
            // If documents exist, they are already covered. 
            // If No documents exist, it stays available as per default request.

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
            $caseReport = CaseReport::with('items')->findOrFail($id);
            $hasDocuments = false;

            // 1. Handle General Documents
            $generalDocPaths = $data['documents'] ?? [];
            if (count($generalDocPaths) > 0) {
                $hasDocuments = true;
            }

            // Check if any documents (general or items) have changed
            // This is needed to decide if we reset expires_at
            $currentGeneralDocs = $caseReport->documents ?: [];
            $documentsChanged = (count(array_diff($generalDocPaths, $currentGeneralDocs)) > 0 || count(array_diff($currentGeneralDocs, $generalDocPaths)) > 0);

            // 2. Update main Case Report fields
            $caseReport->update([
                'patient_fk_id' => $data['patient_fk_id'],
                'doc_ref_fk_id' => $data['doc_ref_fk_id'],
                'description' => $data['description'] ?? null,
                'documents' => $generalDocPaths,
            ]);

            // 3. Refresh Items
            // Before deleting, let's collect old item docs to check for changes
            $oldItemDocs = collect($caseReport->items)->pluck('documents')->flatten()->toArray();
            $newItemDocs = [];

            foreach ($data['items'] as $itemData) {
                $documentPaths = $itemData['documents'] ?? [];
                $newItemDocs = array_merge($newItemDocs, $documentPaths);

                if (count($documentPaths) > 0) {
                    $hasDocuments = true;
                }
            }

            if (!$documentsChanged) {
                $documentsChanged = (count(array_diff($newItemDocs, $oldItemDocs)) > 0 || count(array_diff($oldItemDocs, $newItemDocs)) > 0);
            }

            $caseReport->items()->delete();

            foreach ($data['items'] as $itemData) {
                $documentPaths = $itemData['documents'] ?? [];

                $caseReport->items()->create([
                    'scan_type_id' => $itemData['scan_type_id'],
                    'scan_id' => $itemData['scan_id'],
                    'documents' => $documentPaths,
                    'remarks' => $itemData['remarks'] ?? null,
                ]);
            }

            // 4. Update Status and Expiry
            $status = $hasDocuments ? 'available' : 'pending';

            $updateData = ['status' => $status];
            if ($documentsChanged && $hasDocuments) {
                $updateData['expires_at'] = now()->addDays(30);
                $updateData['status'] = 'available'; // Ensure available if refreshed
            } elseif (!$hasDocuments) {
                $updateData['expires_at'] = null;
            }

            $caseReport->update($updateData);

            // 5. Update Orthanc Sync
            try {
                app(OrthancService::class)->uploadCaseReport($caseReport->id);
            } catch (\Exception $e) {
                \Log::error("CaseReportService Update Sync Failed: " . $e->getMessage());
            }

            return $caseReport->load(['patient', 'doctor', 'items']);
        });
    }

    /**
     * Delete a case report (soft delete).
     */
    public function deleteCaseReport(int $id): bool
    {
        return \DB::transaction(function () use ($id) {
            $caseReport = CaseReport::findOrFail($id);

            // Set status to deleted and set expires_at to null
            $caseReport->update([
                'status' => 'deleted',
                'expires_at' => null
            ]);

            // Soft delete relations
            $caseReport->items()->delete();

            // Soft delete the main report
            return $caseReport->delete();
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

    /**
     * Send WhatsApp notification for the case report.
     */
    public function sendWhatsAppNotification(int $id): array
    {
        $caseReport = $this->getCaseReport($id);
        $whatsAppService = app(WhatsAppService::class);

        $template = \App\Models\WhatsappTemplate::where('event_name', 'SCAN_REPORT_READY')->first();
        if (!$template) {
            return ['status' => false, 'message' => 'WhatsApp template not found'];
        }

        // Prepare data for template
        $data = [
            'mobile_no' => $caseReport->doctor->mobile_no ?? $caseReport->patient->mobile_no,
            'doctorName' => $caseReport->doctor->name ?? 'Doctor',
            'patientName' => $caseReport->patient->name ?? 'Patient',
            'reportId' => $caseReport->case_id,
            'reportDate' => $caseReport->created_at->format('d-m-Y'),
            'shareLink' => config('app.url') . '/case-reports/view-dicom?token=' . $caseReport->sharing_token,
        ];

        return $whatsAppService->send($data, true, $template->id);
    }

    /**
     * Get a public case report by sharing_token.
     */
    public function getPublicCaseReport(string $token): array
    {
        $caseReport = CaseReport::where('sharing_token', $token)
            ->with(['patient.gender', 'doctor', 'items.scanType', 'items.scan'])
            ->firstOrFail();

        $dicomPaths = [];
        foreach ($caseReport->items as $item) {
            if ($item->documents) {
                // Filter only DICOM files
                $itemPaths = array_filter($item->documents, function ($path) {
                    return strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'dcm';
                });
                $dicomPaths = array_merge($dicomPaths, $itemPaths);
            }
        }

        return [
            'report' => $caseReport,
            'dicom_paths' => $dicomPaths,
        ];
    }
}
