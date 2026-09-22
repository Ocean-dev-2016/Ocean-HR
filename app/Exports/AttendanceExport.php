<?php

namespace App\Exports;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\LeaveApplication;
use App\Models\SubDepartment;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $srNo = 1;
    protected $filter_params;
    protected $user;

    protected $modules;


    public function __construct($filter_params = null, $user = null, $modules = [])
    {
        $this->filter_params = (object) $filter_params;
        $this->user = $user;
        $this->modules = $modules;
    }

    public function collection()
    {
        $query = Attendance::withTrashed()
            ->with(['company', 'shift', 'employee'])
            ->orderBy('id', 'DESC');

        $authUser = $this->user ?? null;
        $loginUserId = $authUser?->id ?? null;

        // Employee guard-based access or personal data restriction
        if ($authUser && (isset($authUser->company_id) || !empty($this->modules['company_id']))) {
            $teamPersonCompanyId = $authUser->company_id ?? $this->modules['company_id'];
            $query->where('company_id', $teamPersonCompanyId);

            $hasPersonalOnly = (!empty($this->modules['personal_data_permission']) && empty($this->modules['all_data_permission'])) ||
                               (!empty($this->modules['personalDataPermission']) && empty($this->modules['allDataPermission']));
            if ($hasPersonalOnly) {
                $query->where('employee_id', $loginUserId);
            }
        }

        // Filters
        if (!empty($this->filter_params->filter_company)) {
            $query->where('company_id', $this->filter_params->filter_company);
        }
        if (!empty($this->filter_params->filter_employee)) {
            $query->where('employee_id', $this->filter_params->filter_employee);
        }
        if (!empty($this->filter_params->filter_shift)) {
            $query->where('shift_id', $this->filter_params->filter_shift);
        }
        if (!empty($this->filter_params->attendace_type) && $this->filter_params->attendace_type !== 'all') {
            $query->where('attendace_type', $this->filter_params->attendace_type);
        }

        // Date filter
        if (!empty($this->filter_params->employee_date)) {
            $dates = explode(' to ', str_replace('-', '/', trim($this->filter_params->employee_date)));
            $tableColumn = (new Attendance())->getTable() . '.attendance_date';

            if (count($dates) === 2) {
                $from = Carbon::createFromFormat('d/m/Y', trim($dates[0]))->startOfDay()->format('Y-m-d H:i:s');
                $to   = Carbon::createFromFormat('d/m/Y', trim($dates[1]))->endOfDay()->format('Y-m-d H:i:s');

                $query->whereBetween($tableColumn, [$from, $to]);
            } elseif (count($dates) === 1 && !empty($dates[0])) {
                $singleDate = Carbon::createFromFormat('d/m/Y', trim($dates[0]))->format('Y-m-d');
                $query->whereDate($tableColumn, $singleDate);
            }
        }


        return $query->get();
    }



    public function headings(): array
    {
        $headings = [
            'Sr No',
        ];

        // Include Company Name if user is not limited to a company
        if (!$this->user || empty($this->user['company_id'])) {
            $headings[] = 'Company Name';
        }

        // Add the rest of the columns
        $headings = array_merge($headings, [
            'Employee ID',
            'Shift ID',
            'Attendance Date',
            'Create Date',
            'Punch In / Out Time',

            'Attendance Type',
        ]);

        return $headings;
    }

    public function map($row): array
    {
        $rowData = [
            $this->srNo++,
        ];


        if (!$this->user || empty($this->user['company_id'])) {
            $rowData[] = optional($row->company)->company_name ?? '-';
        }
        $rowData = array_merge($rowData, [
            optional($row->employee)->employee_code . ' - ' . optional($row->employee)->full_name ?? '-',
            $row->shift->name ?? '-',
            $row->attendance_date ? Carbon::parse($row->attendance_date)->format('d/m/Y H:i') : '-',
            $row->create_date ? Carbon::parse($row->create_date)->format('d/m/Y H:i') : '-',
            $row->punch_in_time ? Carbon::parse($row->punch_in_time)->format('H:i') : '-',

            $row->attendace_type ?? '-',
        ]);

        return $rowData;
    }



    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'alignment' => ['horizontal' => 'center']],
        ];
    }

    public function title(): string
    {
        return 'Department';
    }
}
