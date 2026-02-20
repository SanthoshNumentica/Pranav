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
}
