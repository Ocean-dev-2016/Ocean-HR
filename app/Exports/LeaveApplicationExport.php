<?php

namespace App\Exports;

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

class LeaveApplicationExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
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
        $query = LeaveApplication::withTrashed()
            ->with(['company', 'branch', 'employee', 'leave_type'])
            ->orderBy('id', 'DESC');

        $authUser = $this->user ?? null;
        $loginUserId = $authUser->id ?? null;

        /*
        |--------------------------------------------------------------------------
        | Company Wise Restriction
        |--------------------------------------------------------------------------
        */
        if ($authUser && !empty($authUser->company_id)) {
            $query->where('company_id', $authUser->company_id);
        }

        /*
        |--------------------------------------------------------------------------
        | Employee Personal Permission
        |--------------------------------------------------------------------------
        */
        if (
            $authUser &&
            isset($authUser->role) &&
            $authUser->role === 'employee'
        ) {
            if (
                !empty($this->modules['personalDataPermission']) &&
                empty($this->modules['allDataPermission'])
            ) {
                $query->where('created_by', $loginUserId);
            }
        }

        // Module-specific contractor filter
        $contractTypeIds = \App\Models\EmployeeType::where('name', 'like', '%contract%')->pluck('id');

        if ($contractTypeIds->isNotEmpty()) {
            if (
                isset($this->modules['module_name']) &&
                $this->modules['module_name'] === 'Contractor Leave Application'
            ) {
                $query->whereHas('employee.employmentDetail', function ($q) use ($contractTypeIds) {
                    $q->whereIn('employment_type', $contractTypeIds);
                });
            } elseif (
                isset($this->modules['module_name']) &&
                $this->modules['module_name'] === 'Leave Application'
            ) {
                $query->whereDoesntHave('employee.employmentDetail', function ($q) use ($contractTypeIds) {
                    $q->whereIn('employment_type', $contractTypeIds);
                });
            }
        }

        // Filters
        if (!empty($this->filter_params->company_id)) {
            $query->where('company_id', $this->filter_params->company_id);
        }

        if (!empty($this->filter_params->employee_id)) {
            $query->where('employee_id', $this->filter_params->employee_id);
        }

        if (!empty($this->filter_params->filter_leave_type)) {
            $query->where('leave_type_id', $this->filter_params->filter_leave_type);
        }

        if (!empty($this->filter_params->halfday_fullday)) {
            $query->where('halfday_fullday', $this->filter_params->halfday_fullday);
        }

        if (isset($this->filter_params->status) && $this->filter_params->status !== 'all') {
            $query->where('status', $this->filter_params->status);
        }

        // Date Filter (તમારો હાલનો code જેમનો તેમ રાખવો)

        if (!empty($this->filter_params->search)) {
            $search = $this->filter_params->search;

            $query->where(function ($q) use ($search) {
                $q->where('leave_reason', 'like', "%{$search}%")
                    ->orWhere('rejection_reason', 'like', "%{$search}%");
            });
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
            'Employee Name',
            'Leave Type Name',
            'From Date',
            'To Date',
            'HalfDay / FullDay',
            'FirstHalf / SecondHalf',
            'Leave Reason',
            'Rejection Reason',
            'Status',
        ]);

        return $headings;
    }

    public function map($row): array
    {
        $rowData = [
            $this->srNo++,
        ];

        // Company Name
        if (!$this->user || empty($this->user['company_id'])) {
            $rowData[] = optional($row->company)->company_name ?? '-';
        }

        // Add the rest of the data in same order as headings
        $rowData = array_merge($rowData, [
            optional($row->employee)->employee_code . ' - ' . optional($row->employee)->proper_name ?? '-',

            optional($row->leave_type)->full_name ?? '-',
            $row->fromdate_time ? \Carbon\Carbon::parse($row->fromdate_time)->format('d/m/Y H:i') : '-',
            $row->todate_time ? \Carbon\Carbon::parse($row->todate_time)->format('d/m/Y H:i') : '-',
            $row->halfday_fullday ?? '-',
            $row->firsthalf_secondhalf ?? '-',
            $row->leave_reason ?? '-',
            $row->rejection_reason ?? '-',
            ucfirst($row->status),
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
