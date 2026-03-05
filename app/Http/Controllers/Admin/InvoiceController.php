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
        $query = Invoice::with(['patient', 'branch', 'caseReport'])->withSum('payments', 'amount');

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

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('invoice_date', [$request->from_date, $request->to_date]);
        }

        if ($request->filled('branch_id') && $request->branch_id !== 'all') {
            $query->where('branch_id', $request->branch_id);
        }

        $stats = $this->invoiceService->getInvoiceStats(
            $request->only(['from_date', 'to_date', 'branch_id'])
        );

        return response()->json([
            'success' => true,
            'data' => $query->orderBy('invoice_date', 'desc')->orderBy('id', 'desc')->paginate($request->get('limit', 15)),
            'report_stats' => $stats,
        ]);
    }

    public function show($id)
    {
        $invoice = Invoice::with(['patient', 'branch', 'caseReport', 'items.caseReportItem.scanType', 'payments.paymentMethod'])->findOrFail($id);
        return response()->json($invoice);
    }

    public function store(Request $request)
    {
        $request->validate([
            'case_report_id' => 'required|exists:case_reports,id',
            'discount_id' => 'nullable',
            'discount_amount' => 'nullable|numeric',
            'tax_amount' => 'nullable|numeric',
            'invoice_date' => 'required|date',
            'items' => 'required|array',
            'items.*.description' => 'required|string',
            'items.*.amount' => 'required|numeric',
        ]);

        $caseReport = CaseReport::findOrFail($request->case_report_id);

        try {
            DB::beginTransaction();

            $invoice = Invoice::create([
                'invoice_no' => $this->invoiceService->generateInvoiceNo(),
                'case_report_id' => $caseReport->id,
                'patient_id' => $caseReport->patient_fk_id,
                'branch_id' => $caseReport->branch_id,
                'discount_id' => $this->resolveDiscountId($request->discount_id),
                'discount_amount' => $request->discount_amount ?? 0,
                'tax_amount' => $request->tax_amount ?? 0,
                'sub_total' => 0, // Will be updated
                'total_amount' => 0, // Will be updated
                'status' => 'unpaid',
                'invoice_date' => $request->invoice_date,
                'notes' => $request->notes,
            ]);

            foreach ($request->items as $item) {
                $invoice->items()->create([
                    'case_report_item_id' => $item['case_report_item_id'] ?? null,
                    'scan_id' => $item['scan_id'] ?? null,
                    'description' => $item['description'],
                    'amount' => $item['amount'],
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

    /**
     * Resolve discount_id if it's a name or "custom".
     */
    private function resolveDiscountId($discountId): ?int
    {
        if (empty($discountId) || $discountId === 'custom') {
            return null;
        }

        if (is_numeric($discountId)) {
            return (int) $discountId;
        }

        // If it's a string name, try to find the ID
        $discount = \App\Models\Discount::where('name', $discountId)->first();
        return $discount ? $discount->id : null;
    }
}
