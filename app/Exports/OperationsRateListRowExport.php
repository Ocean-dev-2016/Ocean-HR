<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OperationsRateListRowExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    protected $rows;
    protected $baseRecord;
    protected $srNo = 1;

    public function __construct($rows, $baseRecord)
    {
        $this->rows = $rows;
        $this->baseRecord = $baseRecord;

        if (!$this->baseRecord->relationLoaded('employee')) {
            $this->baseRecord->load('employee');
        }
    }

    public function title(): string
    {
        return $this->baseRecord->operation . ' - ' .
            date('F', mktime(0, 0, 0, $this->baseRecord->month, 1)) . ' ' . $this->baseRecord->year;
    }

    public function collection()
    {
        return $this->rows;
    }

    public function headings(): array
    {
        $isLathe = ($this->baseRecord->operation === 'Lathe Employee wise');

        $heads = ['Sr'];
        $heads[] = $isLathe ? 'Operator Name' : 'Product Name';
        $heads[] = $isLathe ? 'Contract Process' : 'Grade';

        $isRepairOp = in_array($this->baseRecord->operation, ['BUFF', 'RRL', 'FLEXIBLE', 'ASS-2', 'COATING']);
        $isOtOp     = in_array($this->baseRecord->operation, ['RRL', 'FLEXIBLE']);

        // Day columns 1-31
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $this->baseRecord->month, $this->baseRecord->year);
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $dayName = date('D', mktime(0, 0, 0, $this->baseRecord->month, $i, $this->baseRecord->year));
            $heads[] = $i . "\n(" . $dayName . ')';
            if ($isRepairOp) {
                $heads[] = "R";
            }
            if ($isOtOp) {
                $heads[] = "OT";
            }
        }

        $heads[] = 'TOTAL QTY';
        $heads[] = 'RATE';
        $heads[] = 'TOTAL AMT';

        if ($isLathe) {
            return $heads;
        }

        $employeeName = $this->baseRecord->employee?->proper_name ?? '-';
        return [
            ['Employee Name: ' . $employeeName],
            $heads
        ];
    }

    public function map($row): array
    {
        $isLathe = ($this->baseRecord->operation === 'Lathe Employee wise');

        $data = [$this->srNo++];
        $data[] = $isLathe
            ? ($row->employee?->full_name ?? '-')
            : ($row->product?->name ?? '-');
        $data[] = $isLathe
            ? ($row->contractProcess?->name ?? ($row->product?->process ?? '-'))
            : ($row->grade?->name ?? '-');

        $isRepairOp = in_array($this->baseRecord->operation, ['BUFF', 'RRL', 'FLEXIBLE', 'ASS-2', 'COATING']);
        $isOtOp     = in_array($this->baseRecord->operation, ['RRL', 'FLEXIBLE']);

        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $this->baseRecord->month, $this->baseRecord->year);
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $val = $row->{'day_' . $i} ?? 0;
            $data[] = $val > 0 ? number_format((float) $val, 2, '.', '') : '';
            
            if ($isRepairOp) {
                $rVal = $row->{'day_' . $i . '_r'} ?? 0;
                $data[] = $rVal > 0 ? number_format((float) $rVal, 2, '.', '') : '';
            }
            if ($isOtOp) {
                $otVal = $row->{'day_' . $i . '_ot'} ?? 0;
                $data[] = $otVal > 0 ? number_format((float) $otVal, 2, '.', '') : '';
            }
        }

        $data[] = number_format((float) $row->total_qty, 2, '.', '');
        $data[] = number_format((float) $row->rate, 2, '.', '');
        $data[] = number_format((float) $row->total_amount, 2, '.', '');

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        $isLathe = ($this->baseRecord->operation === 'Lathe Employee wise');

        if ($isLathe) {
            return [
                1 => ['font' => ['bold' => true], 'alignment' => ['horizontal' => 'center', 'wrapText' => true]],
            ];
        }

        // For non-Lathe, we have a merged title row in row 1
        $lastCol = $sheet->getHighestColumn();
        $sheet->mergeCells('A1:' . $lastCol . '1');

        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center']
            ],
            2 => [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => 'center', 'wrapText' => true]
            ],
        ];
    }
}
