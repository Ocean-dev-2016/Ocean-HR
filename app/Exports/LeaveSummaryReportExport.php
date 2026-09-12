<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LeaveSummaryReportExport implements FromView, WithStyles
{
    protected $data;
    protected $modules;

    public function __construct($data, $modules)
    {
        $this->data = $data;
        $this->modules = $modules;
    }

    public function view(): View
    {
        return view($this->modules['folder_path'] . '.print', array_merge($this->data, [
            'is_excel' => true
        ]));
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        // 1. Set row heights
        $sheet->getRowDimension(1)->setRowHeight(90); // Header row (Logo & Name)
        $sheet->getRowDimension(2)->setRowHeight(20); // Filters row (Month, Department, Designation)
        $sheet->getRowDimension(3)->setRowHeight(25); // Table header row 1
        $sheet->getRowDimension(4)->setRowHeight(25); // Table header row 2


        for ($row = 5; $row <= $highestRow; $row++) {
            $sheet->getRowDimension($row)->setRowHeight(22); // Data rows
        }

        // 1b. Set explicit column widths for A, B, C to center logo accurately
        $sheet->getColumnDimension('A')->setAutoSize(false)->setWidth(16);
        $sheet->getColumnDimension('B')->setAutoSize(false)->setWidth(30);
        $sheet->getColumnDimension('C')->setAutoSize(false)->setWidth(16);
 
        // Auto-size columns D onwards
        $lastColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
        for ($colIndex = 4; $colIndex <= $lastColIndex; $colIndex++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        // 1c. Explicitly merge header cells
        $sheet->mergeCells('A1:C1');
        $sheet->mergeCells('D1:' . $highestColumn . '1');
        $sheet->mergeCells('A2:C2');
        $sheet->mergeCells('D2:L2');
        $sheet->mergeCells('M2:' . $highestColumn . '2');

        // 2. Default alignment (center-center)
        $range = 'A1:' . $highestColumn . $highestRow;
        $sheet->getStyle($range)->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        // 3. Align logo to Center and company name to Center (Row 1)
        $sheet->getStyle('A1:C1')->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        $sheet->getStyle('D1:' . $highestColumn . '1')->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        // 3b. Align Filters row (Row 2)
        $sheet->getStyle('A2:C2')->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        $sheet->getStyle('D2:L2')->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        $sheet->getStyle('M2:' . $highestColumn . '2')->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        $sheet->getStyle('A2:' . $highestColumn . '2')->getFont()
            ->setBold(true)
            ->setSize(10)
            ->getColor()->setARGB('FF475569'); // Slate color #475569

        // 4. Style Table Headers (Row 3 and 4)
        $headerRange = 'A3:' . $highestColumn . '4';
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE9ECEF'); // Soft gray #e9ecef

        $sheet->getStyle($headerRange)->getFont()->setBold(true);

        // 6. Style borders (Row 3 onwards)
        if ($highestRow >= 3) {
            $borderStyle = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => 'FFDEE2E6'], // Light grey #dee2e6
                    ],
                ],
            ];
            $sheet->getStyle('A3:' . $highestColumn . $highestRow)->applyFromArray($borderStyle);
        }

        // 5. Center drawing/logo in A1:C1
        foreach ($sheet->getDrawingCollection() as $drawing) {
            $drawing->setCoordinates('A1');
            $drawing->setOffsetX(142);
            $drawing->setOffsetY(8);
        }

        return [
            1 => ['font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => 'FF1E293B']]], // Charcoal color for company name
        ];
    }
}
