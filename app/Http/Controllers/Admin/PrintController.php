<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PrintController extends Controller
{
    /**
     * Handle PDF generation for various types.
     */
    public function print($type, $id)
    {
        $type = strtolower($type);

        if ($type === 'invoice') {
            return $this->printInvoice($id);
        }

        abort(404, "Print type not supported");
    }

    /**
     * Generate PDF for an Invoice.
     */
    protected function printInvoice($id)
    {
        $invoice = Invoice::with(['patient', 'branch', 'items', 'caseReport.referer', 'payments.paymentMethod'])
            ->findOrFail($id);

        $pdf = Pdf::loadView('admin.print.invoice', [
            'invoice' => $invoice,
            'title' => 'Invoice #' . $invoice->invoice_no
        ]);

        return $pdf->stream('Invoice-' . $invoice->invoice_no . '.pdf');
    }
}
