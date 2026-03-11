<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Invoice;

class PaymentService
{
    /**
     * Create or update payment details for an invoice.
     */
    public function createOrUpdatePayment(Invoice $invoice, array $data)
    {
        // Don't create anything if no payments are provided
        $paymentDetailsJson = $data['payment_details'] ?? null;
        if (empty($paymentDetailsJson['payments'])) {
            return;
        }

        $payment = new Payment();
        $payment->invoice_fk_id = $invoice->id;
        $payment->payment_id = $data['payment_id'] ?? $this->generatePaymentId();
        $payment->added_by = auth()->id();
        $payment->payment_date = $data['payment_date'] ?? now()->toDateString();
        $payment->payment_details = is_array($paymentDetailsJson) ? json_encode($paymentDetailsJson) : $paymentDetailsJson;
        
        $payment->save();

        // Sync Invoice paid_amount and status
        $totalPaid = $invoice->payments()->get()->sum(function ($p) {
            $details = is_string($p->payment_details) ? json_decode($p->payment_details, true) : $p->payment_details;
            return array_sum(array_column($details['payments'] ?? [], 'amount'));
        });

        $invoice->update(['paid_amount' => $totalPaid]);
        
        // Use InvoiceService to update the status correctly (pending, due, fully_paid)
        app(InvoiceService::class)->updateStatus($invoice);

        return $payment;
    }

    /**
     * Generate a sequential payment_id in the format PAY0001.
     */
    public function generatePaymentId(): string
    {
        $last = Payment::withTrashed()->whereNotNull('payment_id')->orderBy('id', 'desc')->first();
        $nextId = $last ? ((int) substr($last->payment_id, 3)) + 1 : 1;
        return 'PAY' . str_pad((string) $nextId, 4, '0', STR_PAD_LEFT);
    }
}
