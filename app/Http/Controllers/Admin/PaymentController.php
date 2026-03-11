<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Invoice;
use App\Services\InvoiceService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    protected $paymentService;
    protected $invoiceService;

    public function __construct(PaymentService $paymentService, InvoiceService $invoiceService)
    {
        $this->paymentService = $paymentService;
        $this->invoiceService = $invoiceService;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'invoice_fk_id' => 'required|exists:invoices,id',
            'payment_details' => 'required|string', // Expecting JSON string
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $invoice = Invoice::findOrFail($data['invoice_fk_id']);
            $payment = $this->paymentService->createOrUpdatePayment($invoice, $data);

            DB::commit();

            return response()->json($payment, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to record payment', 'error' => $e->getMessage()], 500);
        }
    }

    public function index(Request $request)
    {
        $query = Payment::query();

        if ($request->has('invoice_fk_id')) {
            $query->where('invoice_fk_id', $request->invoice_fk_id);
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('payment_date', [$request->from_date, $request->to_date]);
        }

        return response()->json([
            'success' => true,
            'data' => $query->latest('payment_date')->paginate($request->get('limit', 15))
        ]);
    }

    /**
     * Get the next sequential payment_id.
     */
    public function getNextPaymentId()
    {
        return response()->json([
            'success' => true,
            'next_payment_id' => $this->paymentService->generatePaymentId()
        ]);
    }
}
