<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RefererScanAnalysisExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $rows = [];
        foreach ($this->data as $index => $row) {
            $rows[] = [
                $index + 1,
                $row->referer_name,
                $row->scan_type_name,
                $row->total_scans,
                number_format((float)$row->total_amount, 2, '.', ''),
                $row->date ? \Carbon\Carbon::parse($row->date)->format('d-m-Y') : 'N/A',
            ];
        }
        return $rows;
    }

    public function headings(): array
    {
        return [
            'Sl.No',
            'Referer Name',
            'Scan Type',
            'Total Scans',
            'Total Amount',
            'Date',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
