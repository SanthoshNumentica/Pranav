<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\{Patient, Referer, ScanType, Scan, Branch, User, CaseReport, CaseReportItem, Invoice, InvoiceItem, Payment};
use App\Services\{CaseReportService, InvoiceService};

class ReportInvoiceSeeder extends Seeder
{
    protected CaseReportService $caseReportService;
    protected InvoiceService $invoiceService;

    /** Sample document paths inserted into fully_paid records */
    const SAMPLE_DOCS = ['dicom/sample_ct_brain.dcm'];

    public function __construct()
    {
        $this->caseReportService = app(CaseReportService::class);
        $this->invoiceService    = app(InvoiceService::class);
    }

    public function run(): void
    {
        $patients  = Patient::all();
        $referers  = Referer::all();
        $scanTypes = ScanType::with('scans')->get();
        $branches  = Branch::all();
        $adminUser = User::first();

        if ($patients->isEmpty() || $referers->isEmpty() || $scanTypes->isEmpty()) {
            $this->command->warn('Missing prerequisite data (patients, referers, or scan types). Aborted.');
            return;
        }

        if ($branches->isEmpty()) {
            $this->command->warn('No branches found. Case reports will have no branch.');
        }

        $this->command->info("Seeding reports for {$referers->count()} referers...");

        foreach ($referers as $refererIndex => $referer) {
            $isFirstReferer  = ($refererIndex === 0);
            $this->command->line("  → Referer [{$referer->name}] – " . ($isFirstReferer ? 'ALL fully_paid' : 'mixed statuses'));

            // Build a flat list of scans per scan type to guarantee coverage
            $scanCoverage = $this->buildScanCoverage($scanTypes);

            // How many case reports we need (at least enough to cover all scan types twice)
            $minReports = max(12, (int) ceil($scanCoverage->count() / 2));
            $totalReports = rand($minReports, $minReports + 4);

            // Status pool: first referer all fully_paid, others 40/40/20
            $statusPool = $this->buildStatusPool($totalReports, $isFirstReferer);

            DB::transaction(function () use (
                $referer, $patients, $branches, $scanCoverage,
                $statusPool, $totalReports, $adminUser
            ) {
                $coverageQueue = $scanCoverage->shuffle();
                $coverageIndex = 0;

                for ($i = 0; $i < $totalReports; $i++) {
                    $patient = $patients->random();
                    $branch  = $branches->isNotEmpty() ? $branches->random() : null;
                    $status  = $statusPool[$i];

                    // Pull 2–3 scan-type/scan pairings for this report's items
                    $itemCount = min(rand(2, 3), $coverageQueue->count());
                    $itemScans = [];
                    for ($j = 0; $j < $itemCount; $j++) {
                        $itemScans[] = $coverageQueue[$coverageIndex % $coverageQueue->count()];
                        $coverageIndex++;
                    }

                    $this->seedOneReport($referer, $patient, $branch, $itemScans, $status, $adminUser);
                }
            });
        }

        $this->command->info('ReportInvoiceSeeder complete.');
    }

    /**
     * Build a flat collection: each scan type appears at least twice.
     * Each entry = ['scan_type_id' => x, 'scan' => Scan].
     */
    private function buildScanCoverage($scanTypes): \Illuminate\Support\Collection
    {
        $pairs = collect();

        foreach ($scanTypes as $scanType) {
            $scans = $scanType->scans->where('status', 'active');
            if ($scans->isEmpty()) {
                // Use any scan from this type even if inactive fallback
                $scans = $scanType->scans;
            }
            if ($scans->isEmpty()) continue;

            // Add each scan type at least twice
            for ($rep = 0; $rep < 2; $rep++) {
                $pairs->push([
                    'scan_type_id' => $scanType->id,
                    'scan'         => $scans->random(),
                ]);
            }
        }

        return $pairs;
    }

    /**
     * Build a shuffled array of statuses respecting distribution.
     */
    private function buildStatusPool(int $total, bool $allPaid): array
    {
        if ($allPaid) {
            return array_fill(0, $total, 'fully_paid');
        }

        $paid    = (int) round($total * 0.40);
        $unpaid  = (int) round($total * 0.40);
        $due     = $total - $paid - $unpaid;

        $pool = array_merge(
            array_fill(0, $paid, 'fully_paid'),
            array_fill(0, $unpaid, 'unpaid'),
            array_fill(0, max(0, $due), 'due')
        );

        shuffle($pool);
        return $pool;
    }

    /**
     * Seed a single case report with items, invoice, invoice items, and optional payment.
     */
    private function seedOneReport($referer, $patient, $branch, array $itemScans, string $status, $adminUser): void
    {
        $rctDate = now()->subDays(rand(1, 180))->toDateString();
        $rctHour = sprintf('%02d:%02d', rand(8, 18), rand(0, 59));

        // 1. Case Report
        $caseReport = CaseReport::create([
            'case_id'       => $this->caseReportService->getNextCaseId(),
            'patient_fk_id' => $patient->id,
            'referer_id'    => $referer->id,
            'description'   => 'Seeded report for ' . $patient->name,
            'documents'     => [],   // Filled after invoice status determined
            'status'        => 'pending',
            'sharing_token' => Str::random(32),
            'branch_id'     => $branch?->id,
            'rct_date'      => $rctDate,
            'rct_hour'      => $rctHour,
            'is_stat'       => false,
            'patient_type'  => 'out_patient',
            'added_by'      => $adminUser?->id,
        ]);

        // 2. Case Report Items
        $createdItems = [];
        $totalAmount  = 0;

        foreach ($itemScans as $pair) {
            $scan       = $pair['scan'];
            $scanAmount = (float) ($scan->amount ?? rand(500, 5000));

            $item = $caseReport->items()->create([
                'scan_type_id'   => $pair['scan_type_id'],
                'item_reference' => $this->caseReportService->getNextItemReference($pair['scan_type_id']),
                'scan_details'   => [
                    ['scan_id' => $scan->id, 'scan_name' => $scan->name, 'amount' => $scanAmount],
                ],
                'documents'      => [],   // Filled after invoice status determined
                'remarks'        => null,
                'total_amount'   => $scanAmount,
            ]);

            $createdItems[] = $item;
            $totalAmount   += $scanAmount;
        }

        // 3. Invoice
        $invoice = Invoice::create([
            'invoice_no'      => $this->invoiceService->generateInvoiceNo(),
            'case_report_id'  => $caseReport->id,
            'patient_id'      => $patient->id,
            'branch_id'       => $branch?->id,
            'sub_total'       => $totalAmount,
            'discount_amount' => 0,
            'tax_amount'      => 0,
            'total_amount'    => $totalAmount,
            'paid_amount'     => 0,
            'status'          => 'unpaid',
            'invoice_date'    => $rctDate,
            'added_by'        => $adminUser?->id,
        ]);

        // 4. Invoice Items — mirror each case report item
        foreach ($createdItems as $caseItem) {
            $scan = $caseItem->scan_details[0] ?? null;
            InvoiceItem::create([
                'invoice_id'          => $invoice->id,
                'case_report_item_id' => $caseItem->id,
                'scan_id'             => $scan['scan_id'] ?? null,
                'description'         => $scan ? ($caseItem->scanType->name ?? 'Scan') . ' - ' . ($scan['scan_name'] ?? '') : 'Scan',
                'amount'              => $caseItem->total_amount,
            ]);
        }

        // 5. Payment & Invoice Status
        $paidAmount = 0;

        if ($status === 'fully_paid') {
            $paidAmount = $totalAmount;
            $this->createPayment($invoice, $totalAmount, $rctDate, $adminUser);
        } elseif ($status === 'due') {
            $paidAmount = round($totalAmount * (rand(20, 70) / 100), 2);
            $this->createPayment($invoice, $paidAmount, $rctDate, $adminUser);
        }
        // unpaid → no payment, paidAmount stays 0

        $invoice->update([
            'paid_amount' => $paidAmount,
            'status'      => $status,
        ]);

        // 6. Conditional Documents — insert only for fully_paid
        if ($status === 'fully_paid') {
            $caseReport->update(['documents' => self::SAMPLE_DOCS, 'status' => 'available']);
            foreach ($createdItems as $caseItem) {
                $caseItem->update(['documents' => self::SAMPLE_DOCS]);
            }
        }
    }

    /**
     * Create a payment record with an auto-generated payment_id.
     */
    private function createPayment(Invoice $invoice, float $amount, string $date, $adminUser): void
    {
        $last   = Payment::withTrashed()->whereNotNull('payment_id')->orderBy('id', 'desc')->first();
        $nextId = $last ? ((int) substr($last->payment_id, 3)) + 1 : 1;
        $payId  = 'PAY' . str_pad((string) $nextId, 4, '0', STR_PAD_LEFT);

        $paymentMethods = \App\Models\PaymentMethod::all();
        $method         = $paymentMethods->isNotEmpty() ? $paymentMethods->random() : null;

        Payment::create([
            'payment_id'        => $payId,
            'invoice_id'        => $invoice->id,
            'payment_method_id' => $method?->id,
            'amount'            => $amount,
            'payment_date'      => $date,
            'notes'             => 'Seeded payment',
            'added_by'          => $adminUser?->id,
        ]);
    }
}
