<?php

namespace App\Exports\Sheets;

use App\Models\EmployeeIncrementDetails;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class EmployeeIncrementDetailsSheet implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
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
        $query = EmployeeIncrementDetails::with('company', 'employee', 'designation');

        $companyId = $this->filter_params->filter_company ?? $this->filter_params->company ?? null;
        if (!empty($companyId)) {
            $query->where('company_id', $companyId);
        } else if ($this->user && $this->user?->company_id) {
            $query->where('company_id', $this->user?->company_id);
        }

        // Apply employee filters
        $query->whereHas('employee', function ($q) {
            $search = $this->filter_params->search ?? null;
            if ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('employee_code', 'like', "%{$search}%")
                        ->orWhere('full_name', 'like', "%{$search}%")
                        ->orWhere('contact_number', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('father_name', 'like', "%{$search}%");
                });
            }

            $employeeId = $this->filter_params->filter_employee ?? $this->filter_params->employee ?? null;
            if (!empty($employeeId)) {
                $q->where('id', $employeeId);
            }

            $parentId = $this->filter_params->filter_parent ?? $this->filter_params->parent_id ?? null;
            if (!empty($parentId)) {
                $q->where('parent_id', $parentId);
            }

            $branchId = $this->filter_params->filter_branch ?? $this->filter_params->branch ?? null;
            if (!empty($branchId)) {
                $q->where('branch_id', $branchId);
            }

            $departmentId = $this->filter_params->filter_department ?? $this->filter_params->department ?? null;
            if (!empty($departmentId)) {
                $q->whereHas('employmentDetail', function ($innerQ) use ($departmentId) {
                    $innerQ->where('department_id', $departmentId);
                });
            }

            $status = $this->filter_params->status ?? null;
            if ($status && $status !== 'all') {
                $q->where('status', $status);
            }

            $route = $this->modules['route'] ?? null;
            $contractTypeIds = \App\Models\EmployeeType::where('name', 'like', '%contract%')->orWhere('name', 'like', '%contractor%')->pluck('id');
            if ($route === 'contractor-employees') {
                $q->whereHas('employmentDetail', function ($innerQ) use ($contractTypeIds) {
                    $innerQ->whereIn('employment_type', $contractTypeIds);
                });
            } elseif ($route === 'employees') {
                $q->where(function ($subQ) use ($contractTypeIds) {
                    $subQ->whereHas('employmentDetail', function ($innerQ) use ($contractTypeIds) {
                        $innerQ->whereNotIn('employment_type', $contractTypeIds)
                            ->orWhereNull('employment_type');
                    })->orDoesntHave('employmentDetail');
                });
            }
        });

        return $query->latest()->get();
    }

    public function headings(): array
    {
        $headings = [
            'Sr',
            'Employee Code',
            'Employee Name',
            'Increment Date',
            'Basic + D.A',
            'HRA',
            'Conveyance Allowance',
            'Medical Allowance',
            'Special Allowance',
            'PF',
            'Effective Month',
            'Effective Year',
            'Designation Name',
            'Per Day Salary',
            'Per Hour Salary',
            'Remark',
            'Status',
        ];

        if (!$this->user || empty($this->user->company_id)) {
            array_splice($headings, 1, 0, 'Company Name');
        }

        return $headings;
    }

    public function map($row): array
    {
        $rowData = [
            $this->srNo++,
            optional($row->employee)->employee_code ?? '-',
            optional($row->employee)->full_name ?? '-',
            $row->icrement_date ? Carbon::parse($row->icrement_date)->format('d/m/Y') : '-',
            $row->basic_da,
            $row->hra,
            $row->conveyance_allowance,
            $row->medical_allowance,
            $row->special_allowance,
            $row->pf,
            $row->effective_month,
            $row->effective_year,
            $row->designation->name ?? '-',
            $row->per_day_salary,
            $row->per_hour_salary,
            $row->remark,
            ucfirst($row->status),
        ];

        if (!$this->user || empty($this->user->company_id)) {
            array_splice($rowData, 1, 0, optional($row->company)->company_name ?? '-');
        }

        return $rowData;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Increment Details';
    }
}
