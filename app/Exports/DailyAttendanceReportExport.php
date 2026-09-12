<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DailyAttendanceReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $srNo = 1;
    protected $data;
    protected $summary;
    protected $date;
    protected $modules;

    public function __construct(array $data, array $summary, string $date, array $modules)
    {
        $this->data = $data;
        $this->summary = $summary;
        $this->date = $date;
        $this->modules = $modules;
    }

    public function collection(): Collection
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        return [
            'Sr',
            'Employee Name',
            'Code',
            'Shift',
            'Designation',
            'Department',
            'Date',
            'In Time',
            'Out Time',
            'Working Hrs',
            'Status',
        ];
    }

    public function map($row): array
    {
        return [
            $this->srNo++,
            $row['employee_name'] ?? '-',
            $row['employee_code'] ?? '-',
            $row['shift_name'] ?? '-',
            $row['designation'] ?? '-',
            $row['department'] ?? '-',
            $row['date'] ?? '-',
            $row['in_time'] ?? '-',
            $row['out_time'] ?? '-',
            $row['working_hours'] ?? '-',
            $row['status'] ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'alignment' => ['horizontal' => 'center']],
        ];
    }
}
