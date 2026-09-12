<?php

namespace App\Exports;

use App\Models\Department;
use App\Models\EmployeeEducationExperienceDetail;
use App\Models\LeaveApplication;
use App\Models\SubDepartment;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeeEducationExperienceDetailExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
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
        $query = EmployeeEducationExperienceDetail::withTrashed()
            ->with(['company'])
            ->orderBy('id', 'DESC');

        $authUser = $this->user ?? null; // Assuming $this->user is authenticated user
        $loginUserId = $authUser->id ?? null;

        // Employee guard-based access
        if ($authUser && $authUser->role === 'employee') { // adjust role check if needed
            $teamPersonCompanyId = $authUser->company_id;
            $query->where('company_id', $teamPersonCompanyId);

            if (!empty($this->modules['personalDataPermission']) && empty($this->modules['allDataPermission'])) {
                $query->where('created_by', $loginUserId);
            }
        }

        // Filters
        if (!empty($this->filter_params->filter_company)) {
            $query->where('company_id', 'LIKE', '%' . $this->filter_params->filter_company . '%');
        }

        if (!empty($this->filter_params->filter_employee)) {
            $query->where('employee_id', $this->filter_params->filter_employee);
        }

        if (!empty($this->filter_params->filter_department_name)) {
            $query->where('department_name', $this->filter_params->filter_department_name);
        }

        if (!empty($this->filter_params->filter_designation_name)) {
            $query->where('designation_name', $this->filter_params->filter_designation_name);
        }
        if (!empty($this->filter_params->filter_document_type)) {
            $query->where('document_type', $this->filter_params->filter_document_type);
        }

        if (isset($this->filter_params->status) && $this->filter_params->status !== 'all') {
            $query->where('status', $this->filter_params->status);
        }
        if (!empty($this->filter_params->search)) {
            $search = $this->filter_params->search;
            $query->where(function ($q) use ($search) {
                $q->where('degree', 'like', "%{$search}%")
                    ->orWhere('document_name', 'like', "%{$search}%")
                    ->orWhere('unit_change', 'like', "%{$search}%");
            });
        }
        if (!empty($this->filter_params->filter_created_by)) {
            $query->where('created_by', $this->filter_params->filter_created_by);
        } elseif ($this->user && $this->user->company_id && empty($this->modules['all_data_permission'])) {
            if (!empty($this->modules['personal_data_permission'])) {
                $query->where('created_by', $this->user->id);
            }
        }
        return $query->get();
    }


    public function headings(): array
    {
        $headings = [
            'Sr No',
            'Employee Code',
        ];

        // Include Company Name if user is not limited to a company
        if (!$this->user || empty($this->user['company_id'])) {
            $headings[] = 'Company Name';
        }

        // Add the rest of the columns
        $headings = array_merge($headings, [
            'Employee Name',
            'Degree',
            'Unit Change',
            'Document Name',
            'Status',
        ]);

        return $headings;
    }

    public function map($row): array
    {
        $rowData = [
            $this->srNo++,
            optional($row->employee)->employee_code ?? '-',
        ];

        // Company Name
        if (!$this->user || empty($this->user['company_id'])) {
            $rowData[] = optional($row->company)->company_name ?? '-';
        }

        // Add remaining fields in same order as headings
        $rowData = array_merge($rowData, [
            optional($row->employee)->full_name ?? '-',
            $row->degree ?? '-',
            $row->unit_change ?? '-',
            $row->document_name ?? '-',
            ucfirst($row->status ?? '-'),
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
