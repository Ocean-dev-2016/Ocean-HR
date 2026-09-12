<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class AttendanceMusterReportExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles, WithTitle
{
    protected $data;
    protected $headings;
    protected $monthYear;
    protected $daysInMonth;

    public function __construct($reportData = null, $filter_params = null, $user = null, $modules = [])
    {
        // Use provided report data or empty array
        $this->monthYear = $reportData['monthYear'] ?? '';
        $this->daysInMonth = $reportData['daysInMonth'] ?? 0;

        // Build headings
        $this->headings = ['Sr No', 'Employee Name'];
        for ($day = 1; $day <= $this->daysInMonth; $day++) {
            $this->headings[] = str_pad($day, 2, '0', STR_PAD_LEFT);
        }
        // Summary Headers
        $this->headings[] = 'P';
        $this->headings[] = 'A';
        $this->headings[] = 'PL';
        $this->headings[] = 'SL';
        $this->headings[] = 'DL';
        $this->headings[] = 'C-off';
        $this->headings[] = 'LWP';
        $this->headings[] = 'H';
        $this->headings[] = 'WO';
        $this->headings[] = 'Total';

        // Build data array
        $this->data = [];
        $srNo = 1;

        // We need processed data for Muster. 
        // The controller passes data structured for the view. 
        // If it's the raw 'employees' array from generateDetailedReport return, 
        // we need to process it similar to generateMusterHTML but for array output.

        // Actually, let's reuse the logic from generateMusterHTML essentially, but writing to array.

        foreach (($reportData['employees'] ?? []) as $employeeData) {
            // $employeeData is likely the array returned by processDetailedEmployeeData
            // It has 'name', 'attendances' (collection of objects).

            // Handle both array and object for employee data
            $employeeName = is_array($employeeData) ? strip_tags($employeeData['name'] ?? '') : strip_tags($employeeData->name ?? '');
            $attendances = is_array($employeeData) ? ($employeeData['attendances'] ?? []) : ($employeeData->attendances ?? []);

            if (!is_iterable($attendances)) {
                $attendances = [];
            }

            $row = [
                $srNo++,
                $employeeName
            ];

            // Map attendances
            $attendanceMap = [];
            foreach ($attendances as $att) {
                // Handle both array and object for attendance
                $date = is_array($att) ? ($att['attendance_date'] ?? null) : ($att->attendance_date ?? null);

                if ($date) {
                    $dateParts = explode('-', $date);
                    if (count($dateParts) >= 3) {
                        $day = (int) $dateParts[2];
                        $attendanceMap[$day] = $att;
                    }
                }
            }

            $summary = [
                'P' => 0,
                'A' => 0,
                'PL' => 0,
                'SL' => 0,
                'DL' => 0,
                'C-off' => 0,
                'LWP' => 0,
                'H' => 0,
                'WO' => 0,
                'HD' => 0
            ];

            // Days Columns
            for ($day = 1; $day <= $this->daysInMonth; $day++) {
                // Logic duplicating generateMusterHTML
                $att = $attendanceMap[$day] ?? null;
                $code = '-';

                if ($att) {
                    $type = is_array($att) ? ($att['attendance_type'] ?? '') : ($att->attendance_type ?? '');

                    if ($type === 'present') {
                        $code = 'P';
                        $summary['P']++;
                    } elseif ($type === 'absent') {
                        $punchCount = is_array($att) ? ($att['punch_count'] ?? 0) : ($att->punch_count ?? 0);
                        if ($punchCount > 0) {
                            $code = 'MP';
                            $summary['P'] += 0.5;
                            $summary['LWP'] += 0.5;
                        } else {
                            $code = 'LWP';
                            $summary['LWP']++;
                        }
                    } elseif ($type === 'half_day') {
                        $code = 'HD';
                        $summary['P'] += 0.5;
                        $summary['LWP'] += 0.5;
                        $summary['HD']++;
                    } elseif ($type === 'leave') {
                        $halfDay = is_array($att) ? ($att['half_day'] ?? false) : ($att->half_day ?? false);
                        $shortName = is_array($att) ? ($att['leave_type_short_name'] ?? 'L') : ($att->leave_type_short_name ?? 'L');

                        $shortNameUpper = strtoupper(trim($shortName));
                        $leaveKey = 'LWP';
                        if ($shortNameUpper === 'PL' || $shortNameUpper === 'PRIVILEGE LEAVE') {
                            $leaveKey = 'PL';
                        } elseif ($shortNameUpper === 'SL' || $shortNameUpper === 'SICK LEAVE') {
                            $leaveKey = 'SL';
                        } elseif ($shortNameUpper === 'DL' || $shortNameUpper === 'DUTY LEAVE') {
                            $leaveKey = 'DL';
                        } elseif (in_array($shortNameUpper, ['C-OFF', 'COFF', 'COMP-OFF', 'COMPOFF'])) {
                            $leaveKey = 'C-off';
                        } elseif ($shortNameUpper === 'LWP' || $shortNameUpper === 'LEAVE WITHOUT PAY') {
                            $leaveKey = 'LWP';
                        }

                        $code = $shortName;
                        if ($halfDay) {
                            $code = ($halfDay === 'firsthalf') ? $shortName . '(FH)' : $shortName . '(SH)';
                            $summary[$leaveKey] += 0.5;
                            $summary['P'] += 0.5;
                        } else {
                            $summary[$leaveKey]++;
                        }
                    } elseif ($type === 'holiday') {
                        $code = 'H';
                        $summary['H']++;
                    } elseif ($type === 'week_off') {
                        $code = 'WO';
                        $summary['WO']++;
                    } elseif ($type === 'week_off_working') {
                        $code = 'WO(P)';
                        $summary['P']++;
                    }
                } else {
                    // Check for past absences if not in map
                    try {
                        $dateObj = Carbon::createFromDate($this->monthYear ? Carbon::parse($this->monthYear)->year : date('Y'), $this->monthYear ? Carbon::parse($this->monthYear)->month : date('m'), $day);

                        if ($dateObj->isPast() && !$dateObj->isFuture()) {
                            $code = 'LWP';
                            $summary['LWP']++;
                        }
                    } catch (\Exception $e) {
                    }
                }
                $row[] = $code;
            }

            // Summary Columns
            $row[] = $summary['P'];
            $row[] = $summary['A'];
            $row[] = $summary['PL'];
            $row[] = $summary['SL'];
            $row[] = $summary['DL'];
            $row[] = $summary['C-off'];
            $row[] = $summary['LWP'];
            $row[] = $summary['H'];
            $row[] = $summary['WO'];
            $row[] = $summary['P'] + $summary['A'] + $summary['PL'] + $summary['SL'] + $summary['DL'] + $summary['C-off'] + $summary['LWP'] + $summary['H'] + $summary['WO'];

            $this->data[] = $row;
        }

        // Add Grand Total Row for Excel
        if (count($this->data) > 0) {
            $grandSum = [
                'P' => 0, 'A' => 0, 'PL' => 0, 'SL' => 0, 'DL' => 0, 'C-off' => 0, 'LWP' => 0, 'H' => 0, 'WO' => 0, 'Total' => 0
            ];

            foreach ($this->data as $r) {
                // Summary columns start after Sr No (index 0), Employee Name (index 1), and Days (daysInMonth count)
                $startIndex = 2 + $this->daysInMonth;
                $grandSum['P'] += $r[$startIndex];
                $grandSum['A'] += $r[$startIndex + 1];
                $grandSum['PL'] += $r[$startIndex + 2];
                $grandSum['SL'] += $r[$startIndex + 3];
                $grandSum['DL'] += $r[$startIndex + 4];
                $grandSum['C-off'] += $r[$startIndex + 5];
                $grandSum['LWP'] += $r[$startIndex + 6];
                $grandSum['H'] += $r[$startIndex + 7];
                $grandSum['WO'] += $r[$startIndex + 8];
                $grandSum['Total'] += $r[$startIndex + 9];
            }

            $totalRow = [
                '',
                'Total Sum'
            ];
            for ($day = 1; $day <= $this->daysInMonth; $day++) {
                $totalRow[] = '';
            }
            $totalRow[] = $grandSum['P'];
            $totalRow[] = $grandSum['A'];
            $totalRow[] = $grandSum['PL'];
            $totalRow[] = $grandSum['SL'];
            $totalRow[] = $grandSum['DL'];
            $totalRow[] = $grandSum['C-off'];
            $totalRow[] = $grandSum['LWP'];
            $totalRow[] = $grandSum['H'];
            $totalRow[] = $grandSum['WO'];
            $totalRow[] = $grandSum['Total'];

            $this->data[] = $totalRow;
        }
    }

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function title(): string
    {
        return 'Muster Report';
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = count($this->data) + 1;
        $lastCol = count($this->headings);
        $lastColLetter = $sheet->getCellByColumnAndRow($lastCol, 1)->getColumn();

        // Style header row
        $sheet->getStyle('A1:' . $lastColLetter . '1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        // Style employee name column (column B)
        $sheet->getStyle('B2:B' . $lastRow)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E9EFF5']
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);

        // Center align all data cells
        $sheet->getStyle('C2:' . $lastColLetter . $lastRow)->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        // Conditional Formatting (Basic Colors)
        for ($row = 2; $row <= $lastRow; $row++) {
            for ($col = 3; $col <= $this->daysInMonth + 2; $col++) { // Columns C to End of Days
                $cell = $sheet->getCellByColumnAndRow($col, $row);
                $val = $cell->getValue();
                $color = null;

                if ($val == 'P' || $val == 'WO(P)')
                    $color = 'D9EAD3'; // Light Green
                elseif ($val == 'A' || $val == 'LWP')
                    $color = 'F4CCCC'; // Light Red
                elseif ($val == 'WO')
                    $color = 'EEEEEE'; // Light Gray
                elseif ($val == 'H')
                    $color = 'D9D2E9'; // Light Purple
                elseif ($val == 'HD' || $val == 'MP')
                    $color = 'FCE5CD'; // Light Orange
                elseif (strpos($val, 'L') !== false)
                    $color = 'CFE2F3'; // Light Blue

                if ($color) {
                    $sheet->getStyle($cell->getCoordinate())->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB($color);
                }
            }
        }

        // Style the Grand Total row (last row)
        $sheet->getStyle('A' . $lastRow . ':' . $lastColLetter . $lastRow)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E2E2E2']
            ]
        ]);

        // Set row height
        $sheet->getRowDimension(1)->setRowHeight(25);

        // Auto size columns is handled by trait, but we can enforce some widths
        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(25);

        // Day columns width
        for ($col = 3; $col <= $this->daysInMonth + 2; $col++) {
            $colLetter = $sheet->getCellByColumnAndRow($col, 1)->getColumn();
            $sheet->getColumnDimension($colLetter)->setWidth(5);
        }

        return [];
    }
}
