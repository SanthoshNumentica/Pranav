<?php

namespace App\Services;

use App\Models\CaseReport;
use App\Models\CaseReportItem;
use App\Models\Patient;
use App\Models\Referer;
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

        $query = CaseReport::query()
            ->with(['patient', 'referer.refererType', 'branch', 'items.scanType'])
            ->when(isset($filters['status']) && $filters['status'] !== 'all', function (Builder $query) use ($filters) {
                $query->where('status', $filters['status']);
                if ($filters['status'] === 'deleted') {
                    $query->onlyTrashed();
                }
            })
            ->when(isset($filters['search']) && !empty($filters['search']), function (Builder $query) use ($filters) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('case_id', 'like', "%{$search}%")
                        ->orWhereHas('branch', function ($bq) use ($search) {
                            $bq->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('items.scanType', function ($sq) use ($search) {
                            $sq->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('patient', function ($pq) use ($search) {
                            $pq->where('name', 'like', "%{$search}%")
                                ->orWhere('whatsapp_no', 'like', "%{$search}%")
                                ->orWhere('mobile_no', 'like', "%{$search}%");
                        })
                        ->orWhereHas('referer', function ($rq) use ($search) {
                            $rq->where('name', 'like', "%{$search}%")
                                ->orWhere('mobile_no', 'like', "%{$search}%");
                        });
                });
            })
            ->when(isset($filters['branch_id']) && $filters['branch_id'] !== 'all', function (Builder $query) use ($filters) {
                $query->where('branch_id', $filters['branch_id']);
            })
            ->when(isset($filters['scan_type_id']) && $filters['scan_type_id'] !== 'all', function (Builder $query) use ($filters) {
                $query->whereHas('items', function ($q) use ($filters) {
                    $q->where('scan_type_id', $filters['scan_type_id']);
                });
            });

        $this->applyBasicFilters($query, $filters);

        return $query->latest()
            ->paginate($perPage);
    }

    /**
     * Get basic stats for case reports.
     */
    public function getReportStats(array $filters = []): array
    {
        $query = CaseReport::query();

        if (isset($filters['from_date']) && isset($filters['to_date'])) {
            $query->whereBetween('created_at', [$filters['from_date'] . ' 00:00:00', $filters['to_date'] . ' 23:59:59']);
        }

        if (isset($filters['branch_id']) && $filters['branch_id'] !== 'all') {
            $query->where('branch_id', $filters['branch_id']);
        }

        return [
            'total_cases' => $query->count(),
            'pending_count' => (clone $query)->where('status', 'pending')->count(),
            'available_count' => (clone $query)->where('status', 'available')->count(),
        ];
    }

    /**
     * Apply basic filters for general listing.
     */
    private function applyBasicFilters(Builder $query, array $filters): void
    {
        if (isset($filters['from_date']) && isset($filters['to_date'])) {
            $query->whereBetween('created_at', [$filters['from_date'] . ' 00:00:00', $filters['to_date'] . ' 23:59:59']);
        }
    }

    /**
     * Get a single case report by ID.
     */
    public function getCaseReport(int $id): CaseReport
    {
        $caseReport = CaseReport::with(['patient.gender', 'referer.refererType', 'branch', 'items.scanType', 'items.scan', 'addedByUser', 'modifiedByUser', 'invoice.items.caseReportItem.scanType'])->findOrFail($id);

        // Auto-expire if needed before returning
        if ($caseReport->status === 'available' && $caseReport->expires_at && $caseReport->expires_at < now()) {
            $caseReport->update(['status' => 'expired']);
        }

        return $caseReport;
    }

    /**
     * Generate a unique Case ID (e.g., CAS0001).
     */
    private function generateCaseId(): string
    {
        try {
            $latest = CaseReport::latest('id')->first();
            if (!$latest) {
                return 'CAS0001';
            }

            // Handle potential null case_id in legacy records
            $lastId = $latest->case_id ?? '';

            if ($lastId && preg_match('/CAS(\d+)/', $lastId, $matches)) {
                $nextNum = intval($matches[1]) + 1;
                return 'CAS' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
            }

            // Fallback: Use ID increment
            return 'CAS' . str_pad($latest->id + 1, 4, '0', STR_PAD_LEFT);
        } catch (\Exception $e) {
            \Log::error("Case ID Generation Failed: " . $e->getMessage());
            return 'CAS' . date('ymdHis'); // Fallback to timestamp to prevent blocking
        }
    }

    /**
     * Get the next available Case ID.
     */
    public function getNextCaseId(): string
    {
        return $this->generateCaseId();
    }

    /**
     * Get the next available Item Reference for a scan item.
     */
    public function getNextItemReference(int $scanTypeId): string
    {
        $scanType = \App\Models\ScanType::find($scanTypeId);
        if (!$scanType) return '';

        $name = $scanType->name;
        // Generate prefix: 1st 2 letters, alphanumeric only
        $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $name), 0, 2));
        
        if (empty($prefix)) $prefix = 'ITM';

        $latest = \App\Models\CaseReportItem::where('item_reference', 'like', "{$prefix}%")
            ->orderBy('item_reference', 'desc')
            ->first();

        if (!$latest) {
            return $prefix . '0001';
        }

        $lastId = $latest->item_reference;
        // Search for the numeric part
        if (preg_match('/' . preg_quote($prefix, '/') . '(\d+)/', $lastId, $matches)) {
            $nextNum = intval($matches[1]) + 1;
            return $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
        }

        return $prefix . '0001';
    }

    /**
     * Create a new case report with items and documents.
     */
    public function createCaseReport(array $data): CaseReport
    {
        return \DB::transaction(function () use ($data) {
            $generalDocPaths = $data['documents'] ?? [];

            // 1. Create the Case Report
            $caseReport = CaseReport::create([
                'case_id' => $data['case_id'],
                'patient_fk_id' => $data['patient_fk_id'],
                'referer_id' => $data['referer_id'],
                'description' => $data['description'] ?? null,
                'documents' => $generalDocPaths,
                'status' => 'pending',
                'sharing_token' => \Illuminate\Support\Str::random(32),
                'branch_id' => $data['branch_id'] ?? null,
                'rct_date' => $data['rct_date'] ?? null,
                'rct_hour' => $data['rct_hour'] ?? null,
                'is_stat' => $data['is_stat'] ?? false,
                'patient_type' => $data['patient_type'] ?? 'out_patient',
            ]);

            // 2. Sync Related Data
            $this->syncPatientDetails($caseReport, $data);
            $this->syncRefererDetails($caseReport, $data);
            $this->syncCaseReportItems($caseReport, $data['case_report_items'] ?? []);
            $this->syncInvoice($caseReport, $data);

            // 3. Status/Expiry Handled by updateCaseReport specifically when docs change, 
            // but for create, we just trigger Orthanc sync
            $this->triggerOrthancSync($caseReport->id);

            return $caseReport->load(['patient', 'referer', 'items', 'invoice.items.caseReportItem.scanType']);
        });
    }

    /**
     * Update an existing case report with items and documents.
     */
    public function updateCaseReport(int $id, array $data): CaseReport
    {
        return \DB::transaction(function () use ($id, $data) {
            $caseReport = CaseReport::with('items')->findOrFail($id);
            $oldItemDocs = collect($caseReport->items)->pluck('documents')->flatten()->toArray();
            $generalDocPaths = $data['documents'] ?? [];

            // 1. Update main Case Report fields
            $caseReport->update([
                'patient_fk_id' => $data['patient_fk_id'],
                'referer_id' => $data['referer_id'],
                'description' => $data['description'] ?? null,
                'documents' => $generalDocPaths,
                'rct_date' => $data['rct_date'] ?? null,
                'rct_hour' => $data['rct_hour'] ?? null,
                'is_stat' => $data['is_stat'] ?? false,
                'patient_type' => $data['patient_type'] ?? 'out_patient',
                'branch_id' => $data['branch_id'] ?? $caseReport->branch_id,
            ]);

            // 2. Sync Related Data
            $this->syncPatientDetails($caseReport, $data);
            $this->syncRefererDetails($caseReport, $data);
            
            // Collect new docs before updating items for status/expiry logic
            $newItemDocs = [];
            foreach ($data['case_report_items'] ?? [] as $itemData) {
                $newItemDocs = array_merge($newItemDocs, $itemData['documents'] ?? []);
            }

            $this->syncCaseReportItems($caseReport, $data['case_report_items'] ?? []);
            
            // 3. Update Status and Expiry Logic (DICOM only)
            $this->updateStatusAndExpiry($caseReport, $newItemDocs, $oldItemDocs);

            // 4. Update Invoice
            $this->syncInvoice($caseReport, $data);

            // 5. Post-save Hook: Orthanc Sync
            $this->triggerOrthancSync($caseReport->id);

            return $caseReport->load(['patient', 'referer', 'items', 'invoice.items.caseReportItem.scanType']);
        });
    }

    /**
     * Helper to sync patient details.
     */
    private function syncPatientDetails(CaseReport $caseReport, array $data): void
    {
        $patient = Patient::find($data['patient_fk_id']);
        if ($patient) {
            $patient->update(array_filter([
                'name' => $data['patient_name'] ?? null,
                'place' => $data['patient_place'] ?? null,
                'whatsapp_no' => $data['whatsapp_no_patient'] ?? null,
            ]));
        }
    }

    /**
     * Helper to sync referer details.
     */
    private function syncRefererDetails(CaseReport $caseReport, array $data): void
    {
        $referer = Referer::find($data['referer_id']);
        if ($referer) {
            $referer->update(array_filter([
                'name' => $data['referer_name'] ?? null,
                'mobile_no' => $data['whatsapp_no_referer'] ?? null,
                'hospital_name' => $data['hospital_name'] ?? null,
                'hospital_id' => $data['hospital_id'] ?? null,
            ]));
        }
    }

    /**
     * Helper to sync case report items.
     */
    private function syncCaseReportItems(CaseReport $caseReport, array $itemsData): void
    {
        foreach ($itemsData as $itemData) {
            $action = (int) ($itemData['action'] ?? 0);
            $itemId = $itemData['case_report_item_id'] ?? $itemData['id'] ?? null;

            // Validate essential fields for creation/update
            if ($action === 1 || $action === 2) {
                if (empty($itemData['scans']) || !is_array($itemData['scans'])) {
                    continue; // Skip if no scans provided
                }
            }

            switch ($action) {
                case 1: // Add new item
                    $caseReport->items()->create([
                        'scan_type_id' => $itemData['scan_type_id'] ?? null,
                        'item_reference' => $itemData['item_reference'] ?? null,
                        'scan_details' => $itemData['scans'],
                        'documents' => $itemData['documents'] ?? [],
                        'remarks' => $itemData['remarks'] ?? null,
                        'total_amount' => $itemData['total_amount'] ?? array_sum(array_column($itemData['scans'], 'amount')),
                    ]);
                    break;

                case 2: // Update existing item
                    if ($itemId) {
                        $existingItem = $caseReport->items()->find($itemId);
                        if ($existingItem) {
                            $existingItem->update([
                                'scan_type_id' => $itemData['scan_type_id'] ?? null,
                                'item_reference' => $itemData['item_reference'] ?? null,
                                'scan_details' => $itemData['scans'],
                                'documents' => $itemData['documents'] ?? [],
                                'remarks' => $itemData['remarks'] ?? null,
                                'total_amount' => $itemData['total_amount'] ?? array_sum(array_column($itemData['scans'], 'amount')),
                            ]);
                        }
                    }
                    break;

                case 3: // Soft delete
                    if ($itemId) {
                        $existingItem = $caseReport->items()->find($itemId);
                        if ($existingItem) {
                            $existingItem->delete();
                        }
                    }
                    break;
            }
        }
    }

    /**
     * Helper to sync invoice and its items.
     */
    private function syncInvoice(CaseReport $caseReport, array $data): void
    {
        if (!isset($data['invoice_date'])) return;

        $invoice = $caseReport->invoice()->firstOrNew(['case_report_id' => $caseReport->id]);
        if (!$invoice->exists) {
            $invoice->invoice_no = app(InvoiceService::class)->generateInvoiceNo();
            $invoice->status = 'unpaid';
        }

        $invoice->fill([
            'patient_id' => $caseReport->patient_fk_id,
            'branch_id' => $data['branch_id'] ?? $caseReport->branch_id,
            'sub_total' => $invoice->sub_total ?? 0,
            'discount_id' => $data['discount_id'] ?? null,
            'discount_amount' => $data['discount_amount'] ?? 0,
            'tax_amount' => $data['tax_amount'] ?? 0,
            'total_amount' => $invoice->total_amount ?? 0,
            'invoice_date' => $data['invoice_date'],
            'notes' => $data['notes'] ?? null,
        ]);
        $invoice->save();

        // 6.2 Handle invoice items based on action codes
        if (isset($data['invoice_items']) && count($data['invoice_items']) > 0) {
            foreach ($data['invoice_items'] as $itemData) {
                $action = (int) ($itemData['action'] ?? 0);
                $itemId = $itemData['invoice_item_id'] ?? $itemData['id'] ?? null;

                switch ($action) {
                    case 1: // Add new item
                        $invoice->items()->create([
                            'case_report_item_id' => $itemData['case_report_item_id'] ?? null,
                            'scan_id' => $itemData['scan_id'] ?? null,
                            'description' => $itemData['description'] ?? 'Scan',
                            'amount' => $itemData['amount'] ?? 0,
                        ]);
                        break;

                    case 2: // Update existing item
                        if ($itemId) {
                            $invoice->items()->where('id', $itemId)->update([
                                'case_report_item_id' => $itemData['case_report_item_id'] ?? null,
                                'scan_id' => $itemData['scan_id'] ?? null,
                                'description' => $itemData['description'] ?? 'Scan',
                                'amount' => $itemData['amount'] ?? 0,
                            ]);
                        }
                        break;

                    case 3: // Soft delete
                        if ($itemId) {
                            $invoice->items()->where('id', $itemId)->delete();
                        }
                        break;
                }
            }
        }

        app(InvoiceService::class)->updateTotals($invoice);
    }

    /**
     * Helper for status and expiry logic.
     */
    private function updateStatusAndExpiry(CaseReport $caseReport, array $newItemDocs, array $oldItemDocs): void
    {
        $hasDicom = count($newItemDocs) > 0;
        $dicomChanged = (count(array_diff($newItemDocs, $oldItemDocs)) > 0 || count(array_diff($oldItemDocs, $newItemDocs)) > 0);

        $updateData = [];
        if ($hasDicom) {
            $updateData['status'] = 'available';
            if ($dicomChanged || !$caseReport->expires_at) {
                $updateData['expires_at'] = now()->addDays(7);
            }
        } else {
            if ($caseReport->status !== 'deleted') {
                $updateData['status'] = 'pending';
                $updateData['expires_at'] = null;
            }
        }

        if (!empty($updateData)) {
            $caseReport->update($updateData);
        }
    }

    /**
     * Trigger Orthanc Sync.
     */
    private function triggerOrthancSync(int $caseReportId): void
    {
        try {
            app(OrthancService::class)->uploadCaseReport($caseReportId);
        } catch (\Exception $e) {
            \Log::error("CaseReportService Sync Failed: " . $e->getMessage());
        }
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
    public function sendWhatsAppNotification(int $id, array $recipients = ['referer'], array $customNumbers = []): array
    {
        $caseReport = $this->getCaseReport($id);
        $whatsAppService = app(WhatsAppService::class);

        $template = \App\Models\WhatsappTemplate::where('event_name', 'SCAN_REPORT_READY')->first();
        if (!$template) {
            return ['status' => false, 'message' => 'WhatsApp template not found'];
        }

        $results = [];
        $overAllSuccess = true;

        // 2. Prepare common data
        $commonData = [
            'doctorName' => $caseReport->referer->name ?? 'Referer', // Keep for backward compatibility
            'refererName' => $caseReport->referer->name ?? 'Referer',
            'patientName' => $caseReport->patient->name ?? 'Patient',
            'reportId' => $caseReport->case_id,
            'reportDate' => $caseReport->created_at->format('d-m-Y'),
            'shareLink' => config('app.url') . '/case-reports/view-dicom?token=' . $caseReport->sharing_token,
        ];

        // 3. Send to each recipient
        $anySuccess = false;
        foreach ($recipients as $type) {
            $mobileNo = $customNumbers[$type] ?? null;
            if (!$mobileNo) {
                if ($type === 'referer') {
                    $mobileNo = $caseReport->referer->mobile_no ?? null;
                } elseif ($type === 'patient') {
                    $mobileNo = $caseReport->patient->whatsapp_no ?? $caseReport->patient->mobile_no ?? null;
                }
            }

            if ($mobileNo) {
                $data = array_merge($commonData, ['mobile_no' => $mobileNo]);
                $res = $whatsAppService->send($data, true, $template->id);
                $results[] = [
                    'recipient' => $type,
                    'status' => $res['status'],
                    'message' => $res['message']
                ];
                if ($res['status']) {
                    $anySuccess = true;
                } else {
                    $overAllSuccess = false;
                }
            }
        }

        if (empty($results)) {
            return ['status' => false, 'message' => 'No valid recipients selected or mobile numbers missing.'];
        }

        // 4. Update expiry and status ONLY if at least one message was sent successfully
        // AND the case actually has DICOM files (Scan Items with documents)
        if ($anySuccess) {
            $hasDicom = $caseReport->items()->where(function ($q) {
                $q->whereNotNull('documents')->where('documents', '!=', '[]')->where('documents', '!=', '[""]');
            })->exists();

            if ($hasDicom) {
                $caseReport->update([
                    'expires_at' => now()->addDays(7),
                    'status' => 'available'
                ]);
            }
        }

        return [
            'status' => $overAllSuccess,
            'message' => $overAllSuccess ? 'WhatsApp notification sent successfully.' : 'Some notifications failed.',
            'results' => $results
        ];
    }

    /**
     * Get a public case report by sharing_token.
     */
    public function getPublicCaseReport(string $token): array
    {
        $caseReport = CaseReport::where('sharing_token', $token)
            ->with(['patient.gender', 'referer', 'items.scanType', 'items.scan'])
            ->firstOrFail();

        // Auto-expire if needed
        if ($caseReport->status === 'available' && $caseReport->expires_at && $caseReport->expires_at < now()) {
            $caseReport->update(['status' => 'expired']);
            $caseReport->status = 'expired'; // Reflect in current model instance
        }

        if ($caseReport->status === 'expired') {
            abort(403, 'This case report has expired and is no longer available for viewing.');
        }

        if ($caseReport->status === 'deleted') {
            abort(404, 'The requested case report could not be found.');
        }

        $dicomPaths = [];
        foreach ($caseReport->items as $item) {
            if ($item->documents) {
                // Filter only DICOM files (dcm, zip, or empty extension)
                $itemPaths = array_filter($item->documents, function ($path) {
                    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                    return $ext === 'dcm' || $ext === 'zip' || $ext === '';
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
