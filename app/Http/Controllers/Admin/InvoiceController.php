<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\CaseReport;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    public function index(Request $request)
    {
        $query = Invoice::with(['patient', 'branch', 'caseReport']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_no', 'like', "%$search%")
                    ->orWhereHas('patient', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%$search%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->paginate(15));
    }

    public function show($id)
    {
        $invoice = Invoice::with(['patient', 'branch', 'caseReport', 'items', 'payments.paymentMethod'])->findOrFail($id);
        return response()->json($invoice);
    }

    public function store(Request $request)
    {
        $request->validate([
            'case_report_id' => 'required|exists:case_reports,id',
            'discount_amount' => 'nullable|numeric',
            'tax_amount' => 'nullable|numeric',
            'invoice_date' => 'required|date',
            'items' => 'required|array',
            'items.*.description' => 'required|string',
            'items.*.unit_price' => 'required|numeric',
            'items.*.quantity' => 'required|integer',
        ]);

        $caseReport = CaseReport::findOrFail($request->case_report_id);

        try {
            DB::beginTransaction();

            $invoice = Invoice::create([
                'invoice_no' => $this->invoiceService->generateInvoiceNo(),
                'case_report_id' => $caseReport->id,
                'patient_id' => $caseReport->patient_fk_id,
                'branch_id' => $caseReport->branch_id,
                'discount_amount' => $request->discount_amount ?? 0,
                'tax_amount' => $request->tax_amount ?? 0,
                'sub_total' => 0, // Will be updated
                'total_amount' => 0, // Will be updated
                'status' => 'pending',
                'invoice_date' => $request->invoice_date,
                'notes' => $request->notes,
            ]);

            foreach ($request->items as $item) {
                $invoice->items()->create([
                    'case_report_item_id' => $item['case_report_item_id'] ?? null,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'amount' => $item['unit_price'] * $item['quantity'],
                ]);
            }

            $this->invoiceService->updateTotals($invoice);

            DB::commit();

            return response()->json($invoice->load('items'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create invoice', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->delete();
        return response()->json(['message' => 'Invoice deleted successfully']);
    }

    public function getNextInvoiceNo()
    {
        return response()->json(['invoice_no' => $this->invoiceService->generateInvoiceNo()]);
    }
}
