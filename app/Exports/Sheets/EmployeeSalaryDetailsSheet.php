<?php

namespace App\Exports\Sheets;

use App\Models\EmployeeWiseSalaryDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeeSalaryDetailsSheet implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
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
        $query = EmployeeWiseSalaryDetail::with(['company', 'employee']);

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
            if ($route === 'contractor-employees') {
                $contractTypeId = \App\Models\EmployeeType::where('name', 'Contractor Salary')->pluck('id');
                $q->whereHas('employmentDetail', function ($innerQ) use ($contractTypeId) {
                    $innerQ->whereIn('employment_type', $contractTypeId);
                });
            } elseif ($route === 'employees') {
                $payrollTypeId = \App\Models\EmployeeType::where('name', 'Company Payroll')->pluck('id');
                $q->whereHas('employmentDetail', function ($innerQ) use ($payrollTypeId) {
                    $innerQ->whereIn('employment_type', $payrollTypeId);
                });
            }
        });

        return $query->latest()->get();
    }

    public function headings(): array
    {
        $headings = [
            'Sr No',
            'Employee Code',
            'Employee Name',
            'Salary Classification',
            'Week Off',
            'Overtime',
            'Welfare Fund Applied',
            'Welfare Fund Amount',
            'Leave Eligibility',
            'Bonus Applied',
            'Salary Calculation Month Count',
            'PF Type',
            'PF',
            'PF %',
            'Pradhanmantri PF',
            'Pradhanmantri PF %',
            'Sandwich Rule',
            'Sandwich Rule Applied On',
            'Sandwich Rule Type',
            'TDS',
            'TDS %',
            'Insurance',
            'Insurance Amount',
            'PT',
            'PT Amount',
            'ESI Company Side',
            'ESI Company Side %',
            'ESI Employee Side',
            'ESI Employee Side %',
            'Gratuity Calculation',
            'Basic + D.A',
            'HRA',
            'Conveyance Allowance',
            'Medical Allowance',
            'Special Allowance',
            'CTC',
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
            $row->salary_classification ?? '-',
            $row->week_off ?? '-',
            $row->overtime == '1' ? 'Yes' : '-',
            $row->is_welfare_fund_applied == '1' ? 'Yes' : '-',
            $row->welfare_fund_amount ?? 0,
            $row->leave_elegiblity == '1' ? 'Yes' : '-',
            $row->is_bonus_applied == '1' ? 'Yes' : '-',
            $row->salary_calculation_month_count ?? '-',
            $row->pf_type ?? '-',
            $row->pf == '1' ? 'Yes' : '-',
            $row->pf_percentage ?? 0,
            $row->pradhanmantri_pf == '1' ? 'Yes' : '-',
            $row->pradhanmantri_pf_percentage ?? 0,
            $row->sandwich_rule_flag == '1' ? 'Yes' : '-',
            $row->sandwich_rule_applied_on ?? '-',
            $row->sandwich_rule_type ?? '-',
            $row->tds == '1' ? 'Yes' : '-',
            $row->tds_percentage ?? 0,
            $row->insurance == '1' ? 'Yes' : '-',
            $row->insurance_amount ?? 0,
            $row->pt == '1' ? 'Yes' : '-',
            $row->pt_amount ?? 0,
            $row->is_esi_company_side == '1' ? 'Yes' : '-',
            $row->esi_company_side_percentage ?? 0,
            $row->esi_employee_side == '1' ? 'Yes' : '-',
            $row->esi_employee_side_percentage ?? 0,
            $row->gratuity_calculation == '1' ? 'Yes' : '-',
            $row->basic_da ?? 0,
            $row->hra ?? 0,
            $row->conveyance_allowance ?? 0,
            $row->medical_allowance ?? 0,
            $row->special_allowance ?? 0,
            $row->ctc ?? 0,
            ucfirst($row->status ?? '-'),
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
        return 'Salary Details';
    }
}
