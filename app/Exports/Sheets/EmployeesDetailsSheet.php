<?php

namespace App\Exports\Sheets;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class EmployeesDetailsSheet implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
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
        $query = Employee::with([
            'company',
            'country',
            'state',
            'city',
            'employmentDetail',
            'employmentDetail.designation',
            'employmentDetail.department',
            'employmentDetail.subdepartment',
            'employmentDetail.process',
            'employmentDetail.employee_type',
            'employmentDetail.shiftDetail'
        ]);

        if (isset($this->filter_params->search) && $this->filter_params->search !== '') {
            $search = $this->filter_params->search;
            $query->where(function ($q) use ($search) {
                $q->where('employee_code', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%")
                    ->orWhere('contact_number', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('middle_name', 'like', "%{$search}%")
                    ->orWhere('father_name', 'like', "%{$search}%");
            });
        }

        $companyId = $this->filter_params->filter_company ?? $this->filter_params->company ?? null;
        if (!empty($companyId)) {
            $query->where('company_id', $companyId);
        } else if ($this->user && $this->user?->company_id) {
            $query->where('company_id', $this->user?->company_id);
        }

        $parentId = $this->filter_params->filter_parent ?? $this->filter_params->parent_id ?? null;
        if (!empty($parentId)) {
            $query->where('parent_id', $parentId);
        }

        $branchId = $this->filter_params->filter_branch ?? $this->filter_params->branch ?? null;
        if (!empty($branchId)) {
            $query->where('branch_id', $branchId);
        }

        $departmentId = $this->filter_params->filter_department ?? $this->filter_params->department ?? null;
        if (!empty($departmentId)) {
            $query->whereHas('employmentDetail', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        if (
            isset($this->filter_params?->status) &&
            $this->filter_params->status !== '' &&
            $this->filter_params->status !== 'all'
        ) {
            $query->where('status', $this->filter_params->status);
        }

        if (!empty($this->filter_params->filter_created_by)) {
            $query->where('created_by', $this->filter_params->filter_created_by);
        } elseif ($this->user && $this->user->company_id && empty($this->modules['all_data_permission'])) {
            if (!empty($this->modules['personal_data_permission'])) {
                $query->where('created_by', $this->user->id);
            }
        }

        $route = $this->modules['route'] ?? null;
        $contractTypeIds = \App\Models\EmployeeType::where('name', 'like', '%contract%')->orWhere('name', 'like', '%contractor%')->pluck('id');
        if ($route === 'contractor-employees') {
            $query->whereHas('employmentDetail', function ($q) use ($contractTypeIds) {
                $q->whereIn('employment_type', $contractTypeIds);
            });
        } elseif ($route === 'employees') {
            $query->where(function ($q) use ($contractTypeIds) {
                $q->whereHas('employmentDetail', function ($innerQ) use ($contractTypeIds) {
                    $innerQ->whereNotIn('employment_type', $contractTypeIds)
                        ->orWhereNull('employment_type');
                })->orDoesntHave('employmentDetail');
            });
        }

        return $query->latest()->get();
    }

    public function headings(): array
    {
        $headings = [
            'Sr',
            'Employee Code',
            'Surname',
            'First Name',
            'Father Name',
            'Full Name',
            'Email Address',
            'Contact Number',
            'Other Number',
            'User Name',
            'DOB',
            'Gender',
            'Marital Status',
            'Anniversary Date',
            'Aadhar Card No',
            'PAN Card No',
            'Grade',
            'Bank Name',
            'Bank Account No',
            'IFSC Code',
            'Current Address',
            'Permanent Address',
            'Country',
            'State',
            'City',
            'Status',
            // Employment Details
            'Designation',
            'Department',
            'Sub Department',
            'Process',
            'Joining Date',
            'Confirmation Date',
            'PF No',
            'UAN No',
            'Employment Type',
            'Shift',
            'Outdoor Attendance'
        ];

        if (!$this->user || empty($this->user->company_id)) {
            array_splice($headings, 1, 0, 'Company Name');
        }

        return $headings;
    }

    public function map($row): array
    {
        $empDetail = $row->employmentDetail;

        $rowData = [
            $this->srNo++,
            $row->employee_code,
            $row->first_name,
            $row->middle_name,
            $row->father_name,
            $row->proper_name,
            $row->email,
            $row->contact_number,
            $row->other_number,
            $row->username,
            $row->date_of_birth ? Carbon::parse($row->date_of_birth)->format('d-m-Y') : '-',
            $row->gender,
            $row->marital_status,
            $row->date_of_anniversary ? Carbon::parse($row->date_of_anniversary)->format('d-m-Y') : '-',
            $row->aadhar_card_number,
            $row->pan_card_number,
            $row->grade,
            $row->bank_name,
            $row->bank_account_number,
            $row->ifsc_code,
            $row->current_address,
            $row->permanent_address,
            $row->country ? $row->country->name : '-',
            $row->state ? $row->state->name : '-',
            $row->city ? $row->city->name : '-',
            $row->status,
            // Employment Details
            $empDetail ? ($empDetail->designation ? $empDetail->designation->name : '-') : '-',
            $empDetail ? ($empDetail->department ? $empDetail->department->name : '-') : '-',
            $empDetail ? ($empDetail->subdepartment ? $empDetail->subdepartment->sub_department_name : '-') : '-',
            $empDetail ? ($empDetail->process ? $empDetail->process->name : '-') : '-',
            $empDetail ? ($empDetail->date_of_joining ? Carbon::parse($empDetail->date_of_joining)->format('d-m-Y') : '-') : '-',
            $empDetail ? ($empDetail->employment_confirmation_date ? Carbon::parse($empDetail->employment_confirmation_date)->format('d-m-Y') : '-') : '-',
            $empDetail ? $empDetail->employee_pf_no : '-',
            $empDetail ? $empDetail->uan_no : '-',
            $empDetail ? ($empDetail->employee_type ? $empDetail->employee_type->name : '-') : '-',
            $empDetail ? ($empDetail->shiftDetail ? $empDetail->shiftDetail->name : '-') : '-',
            $empDetail ? ucfirst($empDetail->outdoor_attendance) : '-'
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
        return 'Employee Details';
    }
}
