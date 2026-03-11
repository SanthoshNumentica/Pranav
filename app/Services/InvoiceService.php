<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    /**
     * Calculate and update invoice totals.
     */
    public function updateTotals(Invoice $invoice): Invoice
    {
        $subTotal = $invoice->items()->sum('amount');

        $totalAmount = ($subTotal - $invoice->discount_amount) + $invoice->tax_amount;

        $invoice->update([
            'sub_total' => $subTotal,
            'total_amount' => $totalAmount
        ]);

        $this->updateStatus($invoice);

        return $invoice;
    }

    /**
     * Update invoice status based on payments.
     */
    public function updateStatus(Invoice $invoice): void
    {
        if ($invoice->status === 'cancelled') {
            return;
        }

        $paidAmount = $invoice->paid_amount;
        $totalAmount = $invoice->total_amount;

        if ($paidAmount <= 0) {
            $invoice->status = 'unpaid';
        } elseif ($paidAmount < $totalAmount) {
            $invoice->status = 'due';
        } else {
            $invoice->status = 'fully_paid';
        }

        $invoice->save();
    }

    /**
     * Generate a unique invoice number.
     */
    public function generateInvoiceNo(): string
    {
        $year = date('Y');
        $lastInvoice = Invoice::withTrashed()
            ->where('invoice_id', 'like', "INV-$year-%")
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = 1;
        if ($lastInvoice) {
            $parts = explode('-', $lastInvoice->invoice_id);
            $nextNumber = (int) end($parts) + 1;
        }

        return "INV-$year-" . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get summary statistics for Invoices based on filters.
     */
    public function getInvoiceStats(array $filters = []): array
    {
        $query = Invoice::query()
            ->when(isset($filters['branch_id']) && $filters['branch_id'] !== 'all', function ($q) use ($filters) {
                $q->where('branch_fk_id', $filters['branch_id']);
            })
            ->when(isset($filters['from_date']) && isset($filters['to_date']), function ($q) use ($filters) {
                $q->whereBetween('invoice_date', [$filters['from_date'], $filters['to_date']]);
            });

        return [
            'total_revenue' => (clone $query)->sum('total_amount'),
            'total_collected' => (clone $query)->sum('paid_amount'),
            'paid_count' => (clone $query)->where('status', 'fully_paid')->count(),
            'pending_count' => (clone $query)->whereIn('status', ['unpaid', 'due'])->count(),
        ];
    }

    /**
     * Create a new invoice with its items.
     */
    public function createInvoice(\App\Models\CaseReport $caseReport, array $data): Invoice
    {
        $invoice = Invoice::create([
            'invoice_id' => $data['invoice_id'] ?? $this->generateInvoiceNo(),
            'case_report_fk_id' => $caseReport->id,
            'patient_fk_id' => $caseReport->patient_fk_id ?? $data['patient_fk_id'] ?? null,
            'branch_fk_id' => $data['branch_fk_id'] ?? $caseReport->branch_id,
            'discount_fk_id' => $this->resolveDiscountId($data['discount_fk_id'] ?? null),
            'discount_amount' => $data['discount_amount'] ?? 0,
            'tax_amount' => $data['tax_amount'] ?? 0,
            'sub_total' => $data['sub_total'] ?? 0,
            'total_amount' => $data['total_amount'] ?? 0,
            'invoice_date' => $data['invoice_date'] ?? now()->toDateString(),
            'notes' => $data['notes'] ?? null,
            'status' => 'unpaid',
        ]);

        if (!empty($data['invoice_items'])) {
            $this->syncInvoiceItems($invoice, $data['invoice_items']);
        }

        return $this->updateTotals($invoice);
    }

    /**
     * Update an existing invoice and its items.
     */
    public function updateInvoice(\App\Models\CaseReport $caseReport, array $data): Invoice
    {
        $invoice = $caseReport->invoice()->firstOrNew(['case_report_fk_id' => $caseReport->id]);
        
        if (!$invoice->exists) {
            return $this->createInvoice($caseReport, $data);
        }

        $invoice->update(array_filter([
            'patient_fk_id' => $caseReport->patient_fk_id ?? $data['patient_fk_id'] ?? null,
            'branch_fk_id' => $data['branch_fk_id'] ?? $caseReport->branch_id,
            'discount_fk_id' => $this->resolveDiscountId($data['discount_fk_id'] ?? null),
            'discount_amount' => $data['discount_amount'] ?? 0,
            'tax_amount' => $data['tax_amount'] ?? 0,
            'invoice_date' => $data['invoice_date'] ?? $invoice->invoice_date,
            'notes' => $data['notes'] ?? $invoice->notes,
        ], fn($v) => !is_null($v)));

        if (isset($data['invoice_items'])) {
            $this->syncInvoiceItems($invoice, $data['invoice_items']);
        }

        return $this->updateTotals($invoice);
    }

    /**
     * Synchronize invoice items based on action codes (1: add, 2: update, 3: delete).
     */
    public function syncInvoiceItems(Invoice $invoice, array $items): void
    {
        foreach ($items as $itemData) {
            $action = (int) ($itemData['action'] ?? 0);
            $itemId = $itemData['id'] ?? null; // Primary key of the invoice item

            switch ($action) {
                case 1: // Add
                    $invoice->items()->create([
                        'invoice_fk_id' => $invoice->id,
                        'case_report_item_fk_id' => $itemData['case_report_item_fk_id'] ?? null,
                        'scan_fk_id' => $itemData['scan_fk_id'] ?? null,
                        'description' => $itemData['description'] ?? 'Scan',
                        'amount' => $itemData['amount'] ?? 0,
                    ]);
                    break;

                case 2: // Update
                    if ($itemId) {
                        $invoice->items()->where('id', $itemId)->update([
                            'invoice_fk_id' => $invoice->id,
                            'case_report_item_fk_id' => $itemData['case_report_item_fk_id'] ?? null,
                            'scan_fk_id' => $itemData['scan_fk_id'] ?? null,
                            'description' => $itemData['description'] ?? 'Scan',
                            'amount' => $itemData['amount'] ?? 0,
                        ]);
                    }
                    break;

                case 3: // Delete
                    if ($itemId) {
                        $invoice->items()->where('id', $itemId)->delete();
                    }
                    break;
            }
        }
    }

    /**
     * Resolve discount_id if it's a name or "custom".
     */
    public function resolveDiscountId($discountId): ?int
    {
        if (empty($discountId) || $discountId === 'custom') {
            return null;
        }

        if (is_numeric($discountId)) {
            return (int) $discountId;
        }

        $discount = \App\Models\Discount::where('name', $discountId)->first();
        return $discount ? $discount->id : null;
    }

    /**
     * Synchronize paid_amount for existing invoices.
     */
    public function syncExistingPaidAmounts(): void
    {
        $invoices = Invoice::all();
        foreach ($invoices as $invoice) {
            $paidTotal = $invoice->payments()->sum('amount');
            $invoice->update(['paid_amount' => $paidTotal]);
            $this->updateStatus($invoice);
        }
    }
}
