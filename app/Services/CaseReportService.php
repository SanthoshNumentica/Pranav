<?php

namespace App\Services;

use App\Models\CaseReport;
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

        return CaseReport::query()
            ->with(['patient.gender', 'referer', 'branch', 'items.scanType', 'items.scan', 'addedByUser', 'modifiedByUser', 'invoice.items'])
            ->when(isset($filters['status']) && $filters['status'] !== 'all', function (Builder $query) use ($filters) {
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
            ->when(isset($filters['branch_id']) && $filters['branch_id'] !== 'all', function (Builder $query) use ($filters) {
                $query->where('branch_id', $filters['branch_id']);
            })
            ->when(isset($filters['from_date']) && isset($filters['to_date']), function (Builder $query) use ($filters) {
                $query->whereBetween('created_at', [$filters['from_date'] . ' 00:00:00', $filters['to_date'] . ' 23:59:59']);
            })
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get a single case report by ID.
     */
    public function getCaseReport(int $id): CaseReport
    {
        $caseReport = CaseReport::with(['patient.gender', 'referer', 'branch', 'items.scanType', 'items.scan', 'addedByUser', 'modifiedByUser', 'invoice.items'])->findOrFail($id);

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
                'case_id' => $data['case_id'],
                'patient_fk_id' => $data['patient_fk_id'],
                'referer_id' => $data['referer_id'],
                'description' => $data['description'] ?? null,
                'documents' => $generalDocPaths,
                'status' => 'pending',
                'sharing_token' => \Illuminate\Support\Str::random(32),
                'expires_at' => null,
                'branch_id' => $data['branch_id'] ?? null,
                'rct_date' => $data['rct_date'] ?? null,
                'rct_hour' => $data['rct_hour'] ?? null,
                'is_stat' => $data['is_stat'] ?? false,
                'patient_type' => $data['patient_type'] ?? 'out_patient',
            ]);

            // 2.1 Update Patient Details if provided (Name, Place, WhatsApp)
            $patient = Patient::find($data['patient_fk_id']);
            if ($patient) {
                $patientUpdate = [];
                if (isset($data['patient_name']))
                    $patientUpdate['name'] = $data['patient_name'];
                if (isset($data['patient_place']))
                    $patientUpdate['place'] = $data['patient_place'];
                if (isset($data['whatsapp_no_patient']))
                    $patientUpdate['whatsapp_no'] = $data['whatsapp_no_patient'];

                if (!empty($patientUpdate)) {
                    $patient->update($patientUpdate);
                }
            }

            // 2.2 Update Referer Details if provided
            $referer = Referer::find($data['referer_id']);
            if ($referer) {
                $refererUpdate = [];
                if (isset($data['referer_name']))
                    $refererUpdate['name'] = $data['referer_name'];
                if (isset($data['whatsapp_no_referer']))
                    $refererUpdate['mobile_no'] = $data['whatsapp_no_referer'];
                if (isset($data['hospital_name']))
                    $refererUpdate['hospital_name'] = $data['hospital_name'];
                if (isset($data['hospital_id']))
                    $refererUpdate['hospital_id'] = $data['hospital_id'];

                if (!empty($refererUpdate)) {
                    $referer->update($refererUpdate);
                }
            }

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
                    'amount' => $itemData['amount'] ?? null,
                ]);
            }

            // 5. Create Invoice
            if (isset($data['invoice_date'])) {
                $invoice = $caseReport->invoice()->create([
                    'invoice_no' => app(InvoiceService::class)->generateInvoiceNo(),
                    'patient_id' => $caseReport->patient_fk_id,
                    'branch_id' => $caseReport->branch_id,
                    'sub_total' => 0,
                    'discount_amount' => $data['discount_amount'] ?? 0,
                    'tax_amount' => $data['tax_amount'] ?? 0,
                    'total_amount' => 0,
                    'status' => 'pending',
                    'invoice_date' => $data['invoice_date'],
                    'notes' => $data['notes'] ?? null,
                ]);

                // Create invoice items from payload if provided, otherwise from case items
                if (isset($data['invoice_items']) && count($data['invoice_items']) > 0) {
                    foreach ($data['invoice_items'] as $itemData) {
                        $invoice->items()->create([
                            'case_report_item_id' => $itemData['case_report_item_id'] ?? null,
                            'description' => $itemData['description'] ?? 'Scan',
                            'quantity' => $itemData['quantity'] ?? 1,
                            'unit_price' => $itemData['unit_price'] ?? 0,
                            'amount' => $itemData['amount'] ?? 0,
                        ]);
                    }
                } else {
                    foreach ($caseReport->items as $item) {
                        $invoice->items()->create([
                            'case_report_item_id' => $item->id,
                            'description' => $item->scan->name ?? 'Scan',
                            'quantity' => 1,
                            'unit_price' => $item->amount ?? 0,
                            'amount' => $item->amount ?? 0,
                        ]);
                    }
                }

                app(InvoiceService::class)->updateTotals($invoice);
            }

            // 6. Post-save Hook: Orthanc Sync
            try {
                app(OrthancService::class)->uploadCaseReport($caseReport->id);
            } catch (\Exception $e) {
                \Log::error("CaseReportService Sync Failed: " . $e->getMessage());
            }

            return $caseReport->load(['patient', 'referer', 'items', 'invoice']);
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
                'referer_id' => $data['referer_id'],
                'description' => $data['description'] ?? null,
                'documents' => $generalDocPaths,
                'rct_date' => $data['rct_date'] ?? null,
                'rct_hour' => $data['rct_hour'] ?? null,
                'is_stat' => $data['is_stat'] ?? false,
                'patient_type' => $data['patient_type'] ?? 'out_patient',
                'branch_id' => $data['branch_id'] ?? $caseReport->branch_id,
            ]);

            // 2.1 Update Patient Details if provided
            $patient = Patient::find($data['patient_fk_id']);
            if ($patient) {
                $patientUpdate = [];
                if (isset($data['patient_name']))
                    $patientUpdate['name'] = $data['patient_name'];
                if (isset($data['patient_place']))
                    $patientUpdate['place'] = $data['patient_place'];
                if (isset($data['whatsapp_no_patient']))
                    $patientUpdate['whatsapp_no'] = $data['whatsapp_no_patient'];

                if (!empty($patientUpdate)) {
                    $patient->update($patientUpdate);
                }
            }

            // 2.2 Update Referer Details if provided
            $referer = Referer::find($data['referer_id']);
            if ($referer) {
                $refererUpdate = [];
                if (isset($data['referer_name']))
                    $refererUpdate['name'] = $data['referer_name'];
                if (isset($data['whatsapp_no_referer']))
                    $refererUpdate['mobile_no'] = $data['whatsapp_no_referer'];
                if (isset($data['hospital_name']))
                    $refererUpdate['hospital_name'] = $data['hospital_name'];
                if (isset($data['hospital_id']))
                    $refererUpdate['hospital_id'] = $data['hospital_id'];

                if (!empty($refererUpdate)) {
                    $referer->update($refererUpdate);
                }
            }

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
                    'amount' => $itemData['amount'] ?? null,
                ]);
            }

            // 4. Update Status and Expiry Logic (DICOM only)
            // General documents no longer trigger 'available' status or expiry refresh
            $hasDicom = count($newItemDocs) > 0;

            // Check if DICOM documents specifically have changed
            $dicomChanged = (count(array_diff($newItemDocs, $oldItemDocs)) > 0 || count(array_diff($oldItemDocs, $newItemDocs)) > 0);

            $updateData = [];

            if ($hasDicom) {
                // If has DICOMs, it can be available
                $updateData['status'] = 'available';
                // Only refresh expiry if DICOMs were added or changed
                if ($dicomChanged || !$caseReport->expires_at) {
                    $updateData['expires_at'] = now()->addDays(7);
                }
            } else {
                // No DICOMs means it must stay pending (unless it was already something else like deleted)
                if ($caseReport->status !== 'deleted') {
                    $updateData['status'] = 'pending';
                    $updateData['expires_at'] = null;
                }
            }

            $caseReport->update($updateData);

            // 6. Update/Create Invoice
            if (isset($data['invoice_date'])) {
                $invoice = $caseReport->invoice()->firstOrNew(['case_report_id' => $caseReport->id]);

                if (!$invoice->exists) {
                    $invoice->invoice_no = app(InvoiceService::class)->generateInvoiceNo();
                    $invoice->status = 'pending';
                }

                $invoice->fill([
                    'patient_id' => $caseReport->patient_fk_id,
                    'branch_id' => $data['branch_id'] ?? $caseReport->branch_id,
                    'sub_total' => $invoice->sub_total ?? 0,
                    'discount_amount' => $data['discount_amount'] ?? 0,
                    'tax_amount' => $data['tax_amount'] ?? 0,
                    'total_amount' => $invoice->total_amount ?? 0,
                    'invoice_date' => $data['invoice_date'],
                    'notes' => $data['notes'] ?? null,
                ]);
                $invoice->save();

                $invoice->items()->delete();

                // Create invoice items from payload if provided, otherwise from case items
                if (isset($data['invoice_items']) && count($data['invoice_items']) > 0) {
                    foreach ($data['invoice_items'] as $itemData) {
                        $invoice->items()->create([
                            'case_report_item_id' => $itemData['case_report_item_id'] ?? null,
                            'description' => $itemData['description'] ?? 'Scan',
                            'quantity' => $itemData['quantity'] ?? 1,
                            'unit_price' => $itemData['unit_price'] ?? 0,
                            'amount' => $itemData['amount'] ?? 0,
                        ]);
                    }
                } else {
                    foreach ($caseReport->items as $item) {
                        $invoice->items()->create([
                            'case_report_item_id' => $item->id,
                            'description' => $item->scan->name ?? 'Scan',
                            'quantity' => 1,
                            'unit_price' => $item->amount ?? 0,
                            'amount' => $item->amount ?? 0,
                        ]);
                    }
                }

                app(InvoiceService::class)->updateTotals($invoice);
            }

            // 7. Update Orthanc Sync
            try {
                app(OrthancService::class)->uploadCaseReport($caseReport->id);
            } catch (\Exception $e) {
                \Log::error("CaseReportService Update Sync Failed: " . $e->getMessage());
            }

            return $caseReport->load(['patient', 'referer', 'items', 'invoice']);
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
     * Get summary statistics for reports based on filters.
     */
    public function getReportStats(array $filters = []): array
    {
        $query = CaseReport::query()
            ->when(isset($filters['branch_id']) && $filters['branch_id'] !== 'all', function (Builder $query) use ($filters) {
                $query->where('branch_id', $filters['branch_id']);
            })
            ->when(isset($filters['from_date']) && isset($filters['to_date']), function (Builder $query) use ($filters) {
                $query->whereBetween('created_at', [$filters['from_date'] . ' 00:00:00', $filters['to_date'] . ' 23:59:59']);
            });

        return [
            'total' => (clone $query)->count(),
            'completed' => (clone $query)->where('status', 'available')->count(),
            'pending' => (clone $query)->whereIn('status', ['pending', 'draft'])->count(),
        ];
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
