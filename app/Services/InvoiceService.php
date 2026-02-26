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
        $paidAmount = $invoice->payments()->sum('amount');

        if ($paidAmount >= $invoice->total_amount && $invoice->total_amount > 0) {
            $invoice->status = 'paid';
        } elseif ($invoice->status !== 'cancelled') {
            $invoice->status = 'pending';
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
            ->where('invoice_no', 'like', "INV-$year-%")
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = 1;
        if ($lastInvoice) {
            $parts = explode('-', $lastInvoice->invoice_no);
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
                $q->where('branch_id', $filters['branch_id']);
            })
            ->when(isset($filters['from_date']) && isset($filters['to_date']), function ($q) use ($filters) {
                $q->whereBetween('invoice_date', [$filters['from_date'], $filters['to_date']]);
            });

        return [
            'total_revenue' => (clone $query)->sum('total_amount'),
            'total_collected' => (clone $query)->withSum('payments', 'amount')->get()->sum('payments_sum_amount'),
            'paid_count' => (clone $query)->where('status', 'paid')->count(),
            'pending_count' => (clone $query)->whereIn('status', ['pending', 'unpaid'])->count(),
        ];
    }
}
