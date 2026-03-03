<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    public function store(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $payment = Payment::create([
                'invoice_id' => $request->invoice_id,
                'payment_method_id' => $request->payment_method_id,
                'amount' => $request->amount,
                'payment_date' => $request->payment_date,
                'notes' => $request->notes,
            ]);

            $invoice = $payment->invoice;
            
            // Increment paid_amount
            $invoice->paid_amount += $payment->amount;

            // Optional: validation to prevent paid_amount from exceeding total_amount
            if ($invoice->paid_amount > $invoice->total_amount) {
                // You might want to allow overpayment or throw an error
                // For now, let's just proceed as requested, but we could cap it
            }

            $invoice->save();

            $this->invoiceService->updateStatus($invoice);

            DB::commit();

            return response()->json($payment->load('paymentMethod'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to record payment', 'error' => $e->getMessage()], 500);
        }
    }

    public function index(Request $request)
    {
        $query = Payment::with(['invoice', 'paymentMethod']);

        if ($request->has('invoice_id')) {
            $query->where('invoice_id', $request->invoice_id);
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('payment_date', [$request->from_date, $request->to_date]);
        }

        return response()->json([
            'success' => true,
            'data' => $query->latest('payment_date')->paginate($request->get('limit', 15))
        ]);
    }
}
