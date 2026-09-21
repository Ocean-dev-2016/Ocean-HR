<?php

namespace App\Exports;

use App\Models\EmploymentDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

use Carbon\Carbon;

class EmploymentDetailExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
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
        $query = EmploymentDetail::with([
            'company',
            'employee',
            'designation',
            'department',
            'subdepartment',
            'process',
        ]);

        if (Auth::guard('employees')->check()) {
            $teamPerson = Auth::guard('employees')->user();
            $query->where('company_id', $teamPerson->company_id);

            if (!empty($this->modules['personal_data_permission']) && empty($this->modules['all_data_permission'])) {
                $query->where('created_by', $teamPerson->id);
            }
        } elseif ($this->user) {
            if (!empty($this->user->company_id) && empty($this->filter_params->company) && empty($this->filter_params->filter_company)) {
                $query->where('company_id', $this->user->company_id);
            }

            if (!empty($this->modules['personal_data_permission']) && empty($this->modules['all_data_permission'])) {
                $query->where('created_by', $this->user->id);
            }
        }

        $route = $this->modules['route'] ?? null;
        $contractTypeIds = \App\Models\EmployeeType::where('name', 'like', '%contract%')->orWhere('name', 'like', '%contractor%')->pluck('id');
        if ($route === 'contractor-employment-details') {
            $query->whereIn('employment_type', $contractTypeIds);
        } elseif ($route === 'employment-details') {
            $query->where(function ($q) use ($contractTypeIds) {
                $q->whereNotIn('employment_type', $contractTypeIds)
                    ->orWhereNull('employment_type');
            });
        }


        $search = trim($this->filter_params->search ?? '');
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('designation_type', 'like', "%{$search}%")
                    ->orWhere('employee_pf_no', 'like', "%{$search}%")
                    ->orWhere('payment_mode', 'like', "%{$search}%")
                    ->orWhereHas('employee', function ($employeeQuery) use ($search) {
                        $employeeQuery->where('full_name', 'like', "%{$search}%")
                            ->orWhere('employee_code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('company', function ($companyQuery) use ($search) {
                        $companyQuery->where('company_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('department', function ($departmentQuery) use ($search) {
                        $departmentQuery->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('designation', function ($designationQuery) use ($search) {
                        $designationQuery->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('subdepartment', function ($subDepartmentQuery) use ($search) {
                        $subDepartmentQuery->where('sub_department_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('process', function ($processQuery) use ($search) {
                        $processQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $companyId = $this->filter_params->company
            ?? $this->filter_params->filter_company
            ?? null;
        if (!empty($companyId)) {
            $query->where('company_id', $companyId);
        }

        $employeeId = $this->filter_params->employee
            ?? $this->filter_params->filter_employee
            ?? null;
        if (!empty($employeeId)) {
            $query->where('employee_id', $employeeId);
        }

        $departmentId = $this->filter_params->department
            ?? $this->filter_params->filter_department
            ?? null;
        if (!empty($departmentId)) {
            $query->where('department_id', $departmentId);
        }

        if (!empty($this->filter_params->filter_parent)) {
            $query->where('parent_id', $this->filter_params->filter_parent);
        }


        if (!empty($this->filter_params->filter_date)) {
            $rawDate = $this->filter_params->filter_date;
            $separator = Str::contains($rawDate, ' to ') ? ' to ' : ' - ';
            $dates = array_map('trim', explode($separator, $rawDate));

            $fromDate = null;
            $toDate = null;

            try {
                if (!empty($dates[0])) {
                    $fromDate = Carbon::createFromFormat('d/m/Y', $dates[0])->format('Y-m-d');
                }
                if (!empty($dates[1] ?? null)) {
                    $toDate = Carbon::createFromFormat('d/m/Y', $dates[1])->format('Y-m-d');
                }
            } catch (\Exception $e) {
                $fromDate = $toDate = null;
            }

            if ($fromDate && $toDate) {
                $query->whereBetween('date_of_joining', [$fromDate, $toDate]);
            } elseif ($fromDate) {
                $query->where('date_of_joining', $fromDate);
            }
        }


        if (
            isset($this->filter_params->status) &&
            $this->filter_params->status !== '' &&
            $this->filter_params->status !== 'all'
        ) {
            $query->where('status', $this->filter_params->status);
        }


        if (!empty($this->filter_params->filter_created_by)) {
            $query->where('created_by', $this->filter_params->filter_created_by);
        }

        return $query->orderByDesc('id')->get();
    }


    public function headings(): array
    {
        $headings =  [
            'Sr',
            'Employee',
            'Designation Type',
            'Designation',
            'Department',
            'Sub Department',
            'Process',
            'Date',
            'Status',
        ];
        if (!$this->user || empty($this->user['company_id'])) {
            array_splice($headings, 1, 0, 'Company Name'); // insert after Sr No
        }

        return $headings;
    }

    public function map($row): array
    {
        // Basic row data
        $employeeLabel = optional($row->employee)->employee_code
            ? optional($row->employee)->employee_code . ' - ' . optional($row->employee)->full_name
            : optional($row->employee)->full_name;

        $rowData = [
            $this->srNo++,
            $employeeLabel ?? '-',
            $row->designation_type ?? '-',
            optional($row->designation)->name ?? '-',
            optional($row->department)->name ?? '-',
            optional($row->subdepartment)->sub_department_name ?? '-',
            optional($row->process)->name ?? '-',
            $row->date_of_joining ? Carbon::parse($row->date_of_joining)->format('d/m/Y') : '-',
            $row->status ? ucfirst($row->status) : '-',
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
        return 'Employee';
    }
}
