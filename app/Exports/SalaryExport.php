<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Models\Salary;
use Illuminate\Support\Facades\Auth;

class SalaryExport implements FromCollection, WithHeadings, WithMapping
{
    protected $request;
    protected $authUser;
    protected $modules;

    public function __construct($request, $authUser, $modules)
    {
        $this->request = $request;
        $this->authUser = $authUser;
        $this->modules = $modules;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $modules = $this->modules;
        $loginUserId = ($this->authUser && $this->authUser->id) ? $this->authUser->id : null;
        $request = (object) $this->request;

        $data = Salary::with(['company', 'employee', 'branch', 'department'])
            ->whereHas('employee', function ($q) {
                $q->where('status', 'active');
            })
            ->where(function ($query) use ($modules, $loginUserId) {
                if (Auth::guard('employees')->check() || !empty($modules['company_id'])) {
                    $companyId = $modules['company_id'] ?? Auth::guard('employees')->user()->company_id;
                    $query->where('company_id', $companyId);

                    if (!empty($modules['personal_data_permission']) && empty($modules['all_data_permission'])) {
                        $query->where('created_by', $loginUserId);
                    }
                }
            });

        if (isset($modules['restore_permission']) && $modules['restore_permission']) {
            $data = $data->withTrashed();
        }

        // Apply filters
        if (isset($request->filter_company) && $request->filter_company) {
            $data->where('company_id', $request->filter_company);
        }
        if (isset($request->filter_branch) && $request->filter_branch) {
            $data->where('branch_id', $request->filter_branch);
        }
        if (isset($request->filter_department) && $request->filter_department) {
            $data->where('department_id', $request->filter_department);
        }
        if (isset($request->filter_employee) && $request->filter_employee) {
            $data->where('employee_id', $request->filter_employee);
        }
        if (isset($request->filter_year) && $request->filter_year) {
            $data->where('year', $request->filter_year);
        }
        if (isset($request->filter_month) && $request->filter_month) {
            $data->where('month', $request->filter_month);
        }
        if (isset($request->status) && $request->status !== null && $request->status !== 'all') {
            $data->where('status', $request->status);
        }

        return $data->orderBy('id', 'DESC')->get();
    }

    public function headings(): array
    {
        return [
            'Employee Code',
            'Company Name',
            'Employee Name',
            'Branch',
            'Department',
            'Year',
            'Month',
            'CTC',
            'Total Days',
            'Present Days',
            'Total Earnings',
            'Total Deductions',
            'Net Pay',
            'Status'
        ];
    }

    public function map($row): array
    {
        return [
            $row->employee->employee_code ?? '-',
            $row->company->company_name ?? '-',
            $row->employee->full_name ?? '-',
            $row->branch->name ?? '-',
            $row->department->name ?? '-',
            $row->year,
            \Carbon\Carbon::createFromDate(null, $row->month, 1)->format('F'),
            $row->ctc,
            $row->total_day,
            $row->total_present_day,
            $row->total_earning,
            $row->total_deduction,
            $row->net_bank_pay,
            ucfirst($row->status)
        ];
    }
}
