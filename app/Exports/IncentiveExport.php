<?php

namespace App\Exports;

use App\Models\Department;
use App\Models\Incentive;
use App\Models\SubDepartment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class IncentiveExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
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
        $query = Incentive::with('company', 'department', 'employee');

        // Search filter
        if (!empty($this->filter_params->search)) {
            $search = $this->filter_params->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('company', function ($q2) use ($search) {
                        $q2->where('company_name', 'like', "%{$search}%");
                    })
                    ->orWhere('incentive_value', 'like', "%{$search}%")
                    ->orWhere('incentive_amount', 'like', "%{$search}%");
            });
        }

        // Filter by company
        if (!empty($this->filter_params->filter_company)) {
            $query->where('company_id', $this->filter_params->filter_company);
        } elseif ($this->user && $this->user?->company_id) {
            $query->where('company_id', $this->user->company_id);
        }

        // Filter by department
        if (!empty($this->filter_params->filter_department)) {
            $query->where('department_id', $this->filter_params->filter_department);
        }

        // Filter by employee
        if (!empty($this->filter_params->filter_employee)) {
            $query->where('employee_id', $this->filter_params->filter_employee);
        }

        // Filter by status
        if (isset($this->filter_params->status) && $this->filter_params->status !== '' && $this->filter_params->status !== 'all') {
            $query->where('status', $this->filter_params->status);
        }

        // Filter by created_by
        if (!empty($this->filter_params->filter_created_by)) {
            $query->where('created_by', $this->filter_params->filter_created_by);
        } elseif ($this->user && $this->user->company_id && empty($this->modules['all_data_permission'])) {
            if (!empty($this->modules['personal_data_permission'])) {
                $query->where('created_by', $this->user->id);
            }
        }

        return $query->latest()->get();
    }

    public function headings(): array
    {
        $headings = [
            'Sr',
            'Department Name',
            'Employee Name',
            'Incentive Value',
            'Incentive Amount',
            'Status',
        ];

        // If user is not limited to a company, insert Company Name after Sr No
        if (!$this->user || empty($this->user['company_id'])) {
            array_splice($headings, 1, 0, 'Company Name');
        }

        return $headings;
    }

    public function map($row): array
    {
        // Basic row data
        $rowData = [
            $this->srNo++,
            optional($row->department)->name ?? '-',
            optional($row->employee)->employee_code . ' - ' . optional($row->employee)->full_name ?? '-',

            $row->incentive_value ?? '-',
            $row->incentive_amount ?? '-',
            $row->status,
        ];

        // If user is not limited to a company, insert company name after Sr No
        if (!$this->user || empty($this->user['company_id'])) {
            array_splice($rowData, 1, 0, optional($row->company)->company_name ?? '-');
        }

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
