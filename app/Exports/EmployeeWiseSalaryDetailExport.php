<?php

namespace App\Exports;

use App\Models\EmployeeWiseSalaryDetail;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeeWiseSalaryDetailExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
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
        $query = EmployeeWiseSalaryDetail::with(['company', 'employee'])
            ->orderBy('id', 'DESC');

        if (isset($this->modules['restore_permission']) && $this->modules['restore_permission']) {
            $query->withTrashed();
        }

        $authUser = $this->user ?? null;
        $loginUserId = $authUser?->id ?? null;
        $companyId = $this->modules['company_id'] ?? $authUser?->company_id ?? (Auth::guard('employees')->check() ? Auth::guard('employees')->user()?->company_id : null);

        if (!empty($companyId)) {
            $query->where('company_id', $companyId);

            if (!empty($this->modules['personal_data_permission']) && empty($this->modules['all_data_permission'])) {
                $query->where('created_by', $loginUserId);
            }
        } elseif (Auth::guard('employees')->check()) {
            $teamPerson = Auth::guard('employees')->user();
            $query->where('company_id', $teamPerson->company_id);

            if (!empty($this->modules['personal_data_permission']) && empty($this->modules['all_data_permission'])) {
                $query->where('created_by', $teamPerson->id);
            }
        } else {
            $filterCompany = $this->filter_params->filter_company ?? $this->filter_params->company ?? null;
            if (!empty($filterCompany)) {
                $query->where('company_id', $filterCompany);
            }
        }

        if (!empty($this->filter_params->filter_employee_code)) {
            $query->whereHas('employee', function ($q) {
                $q->where('employee_code', 'like', '%' . $this->filter_params->filter_employee_code . '%');
            });
        }

        if (!empty($this->filter_params->filter_employee) || !empty($this->filter_params->employee)) {
            $empId = $this->filter_params->filter_employee ?? $this->filter_params->employee;
            $query->where('employee_id', $empId);
        }

        if (!empty($this->filter_params->filter_salary_classification) || !empty($this->filter_params->salary_classification)) {
            $salClass = $this->filter_params->filter_salary_classification ?? $this->filter_params->salary_classification;
            $query->where('salary_classification', $salClass);
        }

        if (!empty($this->filter_params->filter_pf_type) || !empty($this->filter_params->pf_type)) {
            $pfType = $this->filter_params->filter_pf_type ?? $this->filter_params->pf_type;
            $query->where('pf_type', $pfType);
        }

        if (!empty($this->filter_params->filter_salary_calculation_month_count) || !empty($this->filter_params->salary_calculation_month_count)) {
            $monthCount = $this->filter_params->filter_salary_calculation_month_count ?? $this->filter_params->salary_calculation_month_count;
            $query->where('salary_calculation_month_count', $monthCount);
        }

        if (isset($this->filter_params->status) && $this->filter_params->status !== '' && $this->filter_params->status !== 'all') {
            $query->where('status', $this->filter_params->status);
        }

        if (!empty($this->filter_params->search)) {
            $search = trim($this->filter_params->search);
            $query->where(function ($q) use ($search) {
                $q->where('salary_classification', 'like', "%{$search}%")
                    ->orWhere('pf_type', 'like', "%{$search}%")
                    ->orWhere('salary_calculation_month_count', 'like', "%{$search}%")
                    ->orWhereHas('employee', function ($eq) use ($search) {
                        $eq->where('full_name', 'like', "%{$search}%")
                            ->orWhere('employee_code', 'like', "%{$search}%");
                    });
            });
        }

        return $query->get();
    }

    public function headings(): array
    {
        $headings = [
            'Sr No',
            'Employee Code',
        ];

        $companyId = $this->modules['company_id'] ?? $this->user?->company_id ?? (Auth::guard('employees')->check() ? Auth::guard('employees')->user()?->company_id : null);
        if (empty($companyId)) {
            $headings[] = 'Company Name';
        }

        $headings = array_merge($headings, [
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
        ]);

        return $headings;
    }

    public function map($row): array
    {
        $companyId = $this->modules['company_id'] ?? $this->user?->company_id ?? (Auth::guard('employees')->check() ? Auth::guard('employees')->user()?->company_id : null);

        $rowData = [
            $this->srNo++,
            optional($row->employee)->employee_code ?? '-',
        ];

        if (empty($companyId)) {
            $rowData[] = optional($row->company)->company_name ?? '-';
        }

        $weekOffText = '-';
        if (!empty($row->week_off)) {
            $weekOffArr = is_array($row->week_off) ? $row->week_off : json_decode($row->week_off, true);
            $weekOffText = is_array($weekOffArr) ? implode(', ', $weekOffArr) : $row->week_off;
        }

        $rowData = array_merge($rowData, [
            optional($row->employee)->full_name ?? '-',
            $row->salary_classification ?? '-',
            $weekOffText,
            $row->overtime == '1' ? 'Yes' : 'No',
            $row->is_welfare_fund_applied == '1' ? 'Yes' : 'No',
            $row->welfare_fund_amount ?? 0,
            $row->leave_elegiblity == '1' ? 'Yes' : 'No',
            $row->is_bonus_applied == '1' ? 'Yes' : 'No',
            $row->salary_calculation_month_count ?? '-',
            $row->pf_type ?? '-',
            $row->pf == '1' ? 'Yes' : 'No',
            $row->pf_percentage ?? 0,
            $row->pradhanmantri_pf == '1' ? 'Yes' : 'No',
            $row->pradhanmantri_pf_percentage ?? 0,
            $row->sandwich_rule_flag == '1' ? 'Yes' : 'No',
            $row->sandwich_rule_applied_on ?? '-',
            $row->sandwich_rule_type ?? '-',
            $row->tds == '1' ? 'Yes' : 'No',
            $row->tds_percentage ?? 0,
            $row->insurance == '1' ? 'Yes' : 'No',
            $row->insurance_amount ?? 0,
            $row->pt == '1' ? 'Yes' : 'No',
            $row->pt_amount ?? 0,
            $row->is_esi_company_side == '1' ? 'Yes' : 'No',
            $row->esi_company_side_percentage ?? 0,
            $row->esi_employee_side == '1' ? 'Yes' : 'No',
            $row->esi_employee_side_percentage ?? 0,
            $row->gratuity_calculation == '1' ? 'Yes' : 'No',
            $row->basic_da ?? 0,
            $row->hra ?? 0,
            $row->conveyance_allowance ?? 0,
            $row->medical_allowance ?? 0,
            $row->special_allowance ?? 0,
            $row->ctc ?? 0,
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
        return 'Employee Wise Salary Details';
    }
}
