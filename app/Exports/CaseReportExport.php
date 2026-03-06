<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CaseReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
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
            'Case ID',
            'Scan Types',
            'Patient Name',
            'Mobile No',
            'Referer',
            'Referer Mobile',
            'Scanning Date',
            'Check-in',
            'Check-out',
        ];
    }

    public function map($caseReport): array
    {
        static $index = 0;
        $index++;

        return [
            $index,
            $caseReport->case_id,
            $caseReport->items->map(fn($i) => $i->scanType->name ?? '')->filter()->unique()->implode(', '),
            $caseReport->patient->name ?? 'N/A',
            $caseReport->patient->whatsapp_no ?? ($caseReport->patient->mobile_no ?? 'N/A'),
            $caseReport->referer->name ?? 'N/A',
            $caseReport->referer->mobile_no ?? 'N/A',
            $caseReport->rct_date ? \Carbon\Carbon::parse($caseReport->rct_date)->format('d-m-Y') : 'N/A',
            $caseReport->rct_hour ?? '—',
            $caseReport->check_out ?? '—',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
