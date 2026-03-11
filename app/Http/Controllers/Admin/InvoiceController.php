<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\CaseReport;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Traits\AdvancedDateFilterTrait;

class InvoiceController extends Controller
{
    use AdvancedDateFilterTrait;

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
                $q->where('invoice_id', 'like', "%$search%")
                    ->orWhereHas('patient', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%$search%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $filters = $request->only(['filter_type', 'filter_option', 'from_date', 'to_date', 'branch_id', 'search', 'status']);
        
        $this->applyDateFilters($query, $filters, 'invoice_date');

        if ($request->filled('branch_id') && $request->branch_id !== 'all') {
            $query->where('branch_fk_id', $request->branch_id);
        }

        $stats = $this->invoiceService->getInvoiceStats($filters);

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
        $data = $request->validate([
            'case_report_fk_id' => 'required|exists:case_reports,id',
            'discount_fk_id' => 'nullable',
            'discount_amount' => 'nullable|numeric',
            'tax_amount' => 'nullable|numeric',
            'invoice_date' => 'required|date',
            'invoice_items' => 'required|array',
            'invoice_items.*.description' => 'required|string',
            'invoice_items.*.amount' => 'required|numeric',
            'notes' => 'nullable|string',
        ]);

        $caseReport = CaseReport::findOrFail($request->case_report_fk_id);

        try {
            DB::beginTransaction();

            // Structure data for service (adding action:1 for new items)
            $data['invoice_items'] = array_map(function($item) {
                $item['action'] = 1;
                return $item;
            }, $data['invoice_items']);

            $invoice = $this->invoiceService->createInvoice($caseReport, $data);

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
