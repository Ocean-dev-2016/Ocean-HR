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

class AttendanceReportExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles, WithTitle
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
        $this->headings[] = 'Working Time';

        // Build data array
        $this->data = [];
        $srNo = 1;

        foreach (($reportData['employees'] ?? []) as $employee) {
            $row = [
                $srNo++,
                strip_tags($employee['name']) // Remove HTML tags
            ];

            // Add attendance for each day
            foreach ($employee['attendances'] ?? [] as $attendance) {
                // Handle both array and object formats
                $attType = strtolower(is_array($attendance) ? ($attendance['attendance_type'] ?? '') : ($attendance->attendance_type ?? ''));
                $content = '-';

                $punches = is_array($attendance) ? ($attendance['all_punches'] ?? []) : ($attendance->all_punches ?? []);
                $punchCount = count($punches);

                // Determine if Missing Punch should be forced (Odd punches on past date)
                // Note: Export might not have easy access to 'today' context as nicely as controller unless passed, 
                // but we can parse date.
                $attDateStr = is_array($attendance) ? ($attendance['attendance_date'] ?? '') : ($attendance->attendance_date ?? '');
                $forceMissingPunch = false;

                if ($attDateStr) {
                    $attDate = Carbon::parse($attDateStr);
                    if ($attDate->isPast() && !$attDate->isToday() && $punchCount > 0 && ($punchCount % 2 != 0)) {
                        $forceMissingPunch = true;
                    }
                }

                if ($attType === 'week_off') {
                    $content = 'WO';
                } elseif ($attType === 'holiday') {
                    $content = 'H';
                } elseif ($attType === 'absent' || $forceMissingPunch) {
                    // Check for Missing Punch (Absent but has punches OR Forced Missing Punch)
                    if (!empty($punches) && count($punches) > 0) {
                        $content = "MP";

                        // Show punches for MP
                        $inTime = is_array($attendance) ? ($attendance['time_in'] ?? '-') : ($attendance->time_in ?? '-');
                        $outTime = is_array($attendance) ? ($attendance['time_out'] ?? '-') : ($attendance->time_out ?? '-');

                        if ($inTime !== '-' || $outTime !== '-') {
                            $inTimeStr = is_string($inTime) ? substr($inTime, 0, 5) : (is_object($inTime) ? $inTime->format('H:i') : '-');
                            $outTimeStr = is_string($outTime) ? substr($outTime, 0, 5) : (is_object($outTime) ? $outTime->format('H:i') : '-');
                            $content .= "\n" . $inTimeStr . '-' . $outTimeStr;
                        }
                    } else {
                        $content = 'A';
                    }
                } elseif ($attType === 'leave') {
                    $halfDay = is_array($attendance) ? ($attendance['half_day'] ?? null) : ($attendance->half_day ?? null);
                    if ($halfDay) {
                        $content = ($halfDay === 'firsthalf' || $halfDay === 'secondhalf') ? ($halfDay === 'firsthalf' ? 'FH' : 'SH') : 'HD';
                    } else {
                        $content = is_array($attendance) ? ($attendance['leave_type_short_name'] ?? 'L') : ($attendance->leave_type_short_name ?? 'L');
                    }
                } elseif ($attType === 'present' || $attType === 'half_day' || $attType === 'week_off_working') {
                    $inTime = is_array($attendance) ? ($attendance['time_in'] ?? '-') : ($attendance->time_in ?? '-');
                    $outTime = is_array($attendance) ? ($attendance['time_out'] ?? '-') : ($attendance->time_out ?? '-');
                    $wh = is_array($attendance) ? ($attendance['working_hours'] ?? '00:00') : ($attendance->working_hours ?? '00:00');
                    $isLate = is_array($attendance) ? ($attendance['is_late'] ?? false) : ($attendance->is_late ?? false);

                    // Format time for Excel
                    if ($inTime !== '-' || $outTime !== '-') {
                        $inTimeStr = is_string($inTime) ? substr($inTime, 0, 5) : (is_object($inTime) ? $inTime->format('H:i') : '-');
                        $outTimeStr = is_string($outTime) ? substr($outTime, 0, 5) : (is_object($outTime) ? $outTime->format('H:i') : '-');
                        $whStr = is_string($wh) ? substr($wh, 0, 5) : (is_object($wh) ? $wh->format('H:i') : '00:00');

                        $content = $inTimeStr . '-' . $outTimeStr . "\n(" . $whStr . ")";

                        // Add Status Prefix
                        $prefix = 'P';
                        if ($attType === 'half_day')
                            $prefix = 'HD';
                        if ($attType === 'week_off_working')
                            $prefix = 'WOW';

                        $content = $prefix . ($isLate ? '(L)' : '') . "\n" . $content;
                    } else {
                        $whStr = is_string($wh) ? substr($wh, 0, 5) : (is_object($wh) ? $wh->format('H:i') : '00:00');
                        $content = $whStr;
                    }
                }

                $row[] = $content;
            }

            // Add working time
            $row[] = $employee['total_working_hours'] ?? '00:00:00';

            $this->data[] = $row;
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
        return 'Attendance Report';
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = count($this->data) + 1;
        $lastCol = count($this->headings);

        // Style header row
        $sheet->getStyle('A1:' . $sheet->getCellByColumnAndRow($lastCol, 1)->getCoordinate())->applyFromArray([
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

        // Style working time column (last column)
        $lastColLetter = $sheet->getCellByColumnAndRow($lastCol, 1)->getColumn();
        $sheet->getStyle($lastColLetter . '2:' . $lastColLetter . $lastRow)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E9EFF5']
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Set row height for header
        $sheet->getRowDimension(1)->setRowHeight(25);

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(25);
        for ($col = 3; $col <= $lastCol - 1; $col++) {
            $colLetter = $sheet->getCellByColumnAndRow($col, 1)->getColumn();
            $sheet->getColumnDimension($colLetter)->setWidth(12);
        }
        $sheet->getColumnDimension($lastColLetter)->setWidth(15);

        // Add borders to all cells
        $sheet->getStyle('A1:' . $lastColLetter . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Center align day columns
        $dayColStart = 'C';
        $dayColEnd = $sheet->getCellByColumnAndRow($lastCol - 1, 1)->getColumn();
        $sheet->getStyle($dayColStart . '2:' . $dayColEnd . $lastRow)->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        // Wrap text for cells with multiple lines
        $sheet->getStyle('A1:' . $lastColLetter . $lastRow)->getAlignment()->setWrapText(true);

        return [];
    }
}
