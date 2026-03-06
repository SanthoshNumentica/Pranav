<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InvoiceAnalysisExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $collection;

    public function __construct($collection)
    {
        $this->collection = $collection;
    }

    public function collection()
    {
        return $this->collection;
    }

    public function headings(): array
    {
        return [
            'Sl.No',
            'Invoice ID',
            'Case ID',
            'Patient Name',
            'Referer',
            'Scan Type',
            'Amount',
            'Tax',
            'Total Amount',
            'Invoice Date',
        ];
    }

    public function map($invoice): array
    {
        static $index = 0;
        $index++;

        // Get scan types from case report items
        $scanTypes = $invoice->caseReport->items->map(fn($i) => $i->scanType->name ?? '')->filter()->unique()->implode(', ');

        return [
            $index,
            $invoice->invoice_no,
            $invoice->caseReport->case_id ?? 'N/A',
            $invoice->patient->name ?? 'N/A',
            $invoice->caseReport->referer->name ?? 'N/A',
            $scanTypes ?: 'N/A',
            number_format((float)$invoice->sub_total, 2, '.', ''),
            number_format((float)$invoice->tax_amount, 2, '.', ''),
            number_format((float)$invoice->total_amount, 2, '.', ''),
            $invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date)->format('d-m-Y') : 'N/A',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
