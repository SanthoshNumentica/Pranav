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
            ->with(['patient.gender', 'referer.refererType', 'branch', 'items.scanType', 'invoice', 'modifiedByUser'])
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
            ->when(isset($filters['scan_types_fk_id']) && $filters['scan_types_fk_id'] !== 'all', function (Builder $query) use ($filters) {
                $query->whereHas('items', function ($q) use ($filters) {
                    $q->where('scan_types_fk_id', $filters['scan_types_fk_id']);
                });
            });

        $this->applyBasicFilters($query, $filters);

        return $query->orderBy('scanning_date', 'desc')
            ->orderBy('check_in', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get basic stats for case reports.
     */
    public function getReportStats(array $filters = []): array
    {
        $query = CaseReport::query();

        if (isset($filters['filter_option']) && $filters['filter_option'] === 'today') {
            $query->whereDate('scanning_date', \Carbon\Carbon::today());
        } elseif (isset($filters['from_date']) && isset($filters['to_date'])) {
            $query->whereBetween('scanning_date', [$filters['from_date'], $filters['to_date']]);
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
        if (isset($filters['filter_option']) && $filters['filter_option'] === 'today') {
            $query->whereDate('scanning_date', \Carbon\Carbon::today());
        } elseif (isset($filters['from_date']) && isset($filters['to_date'])) {
            $query->whereBetween('scanning_date', [$filters['from_date'], $filters['to_date']]);
        }
    }

    /**
     * Get a single case report by ID.
     */
    public function getCaseReport(int $id): CaseReport
    {
        $caseReport = CaseReport::with(['patient.gender', 'referer.refererType', 'branch', 'items.scanType', 'items.scan', 'addedByUser', 'modifiedByUser', 'invoice.items.caseReportItem.scanType', 'invoice.payments'])->findOrFail($id);

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
            // 1 & 2. Ensure Patient and Referer exist via their respective services
            $patientFkId = app(PatientService::class)->createOrUpdatePatient($data['patient_details']);
            $refererFkId = app(RefererService::class)->createOrUpdateReferer($data['referer_details']);

            $caseData = $data['case_reports'];
            $generalDocPaths = $caseData['documents'] ?? [];

            // 3. Create the Case Report
            $caseReport = CaseReport::create([
                'case_id' => $caseData['case_id'],
                'patient_fk_id' => $patientFkId,
                'referer_fk_id' => $refererFkId,
                'description' => $caseData['description'] ?? null,
                'documents' => $generalDocPaths,
                'status' => 'pending',
                'sharing_token' => \Illuminate\Support\Str::random(32),
                'branch_id' => $caseData['branch_fk_id'] ?? null,
                'scanning_date' => $caseData['scanning_date'] ?? null,
                'check_in' => $caseData['check_in'] ?? null,
                'is_stat_case' => $caseData['is_stat_case'] ?? false,
            ]);

            // 4. Sync Items
            $this->syncCaseReportItems($caseReport, $caseData['case_report_items'] ?? []);
            
            // 5. Sync Invoice
            if (isset($data['invoice_details'])) {
                $invoice = app(InvoiceService::class)->createInvoice($caseReport, $data['invoice_details']);
                
                // 6. Sync Payment
                if (isset($data['payment_details'])) {
                    app(PaymentService::class)->createOrUpdatePayment($invoice, $data['payment_details']);
                }
            }

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
            
            $caseData = $data['case_reports'];
            $generalDocPaths = $caseData['documents'] ?? [];

            // 1 & 2. Sync Patient and Referer via their services
            $patientFkId = app(PatientService::class)->createOrUpdatePatient($data['patient_details']);
            $refererFkId = app(RefererService::class)->createOrUpdateReferer($data['referer_details']);

            // 3. Update main Case Report fields
            $caseReport->update([
                'patient_fk_id' => $patientFkId,
                'referer_fk_id' => $refererFkId,
                'description' => $caseData['description'] ?? null,
                'documents' => $generalDocPaths,
                'scanning_date' => $caseData['scanning_date'] ?? null,
                'check_in' => $caseData['check_in'] ?? null,
                'is_stat_case' => $caseData['is_stat_case'] ?? false,
                'branch_id' => $caseData['branch_fk_id'] ?? $caseReport->branch_id,
            ]);

            // Collect new docs before updating items for status/expiry logic
            $newItemDocs = [];
            foreach ($caseData['case_report_items'] ?? [] as $itemData) {
                $newItemDocs = array_merge($newItemDocs, $itemData['documents'] ?? []);
            }

            // 4. Sync Items
            $this->syncCaseReportItems($caseReport, $caseData['case_report_items'] ?? []);
            
            // 5. Update Status and Expiry Logic (DICOM only)
            $this->updateStatusAndExpiry($caseReport, $newItemDocs, $oldItemDocs);

            // 6. Update Invoice
            if (isset($data['invoice_details'])) {
                $invoice = app(InvoiceService::class)->updateInvoice($caseReport, $data['invoice_details']);
                
                // 7. Update Payment
                if (isset($data['payment_details'])) {
                    app(PaymentService::class)->createOrUpdatePayment($invoice, $data['payment_details']);
                }
            }

            // 8. Post-save Hook: Orthanc Sync
            $this->triggerOrthancSync($caseReport->id);

            return $caseReport->load(['patient', 'referer', 'items', 'invoice.items.caseReportItem.scanType']);
        });
    }

    /**
     * Helper to sync case report items.
     */
    private function syncCaseReportItems(CaseReport $caseReport, array $itemsData): void
    {
        foreach ($itemsData as $itemData) {
            $action = (int) ($itemData['action'] ?? 0);
            $itemId = $itemData['case_report_item_fk_id'] ?? $itemData['id'] ?? null;

            // Validate essential fields for creation/update
            if ($action === 1 || $action === 2) {
                if (empty($itemData['scans']) || !is_array($itemData['scans'])) {
                    continue; // Skip if no scans provided
                }
            }

            switch ($action) {
                case 1: // Add new item
                    $caseReport->items()->create([
                        'scan_types_fk_id' => $itemData['scan_types_fk_id'] ?? null,
                        'scan_type_id' => $itemData['scan_type_id'] ?? null,
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
                                'scan_types_fk_id' => $itemData['scan_types_fk_id'] ?? null,
                                'scan_type_id' => $itemData['scan_type_id'] ?? null,
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
