<?php

namespace App\Exports;

use App\Models\EmployeeEducationExperienceDetail;
use Illuminate\Support\Facades\Auth;
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
        $query = EmployeeEducationExperienceDetail::with(['company', 'employee'])
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
            // Admin without company selected: can filter by company
            $filterCompany = $this->filter_params->filter_company ?? $this->filter_params->company ?? null;
            if (!empty($filterCompany)) {
                $query->where('company_id', $filterCompany);
            }
        }

        // Filter by Employee Code
        if (!empty($this->filter_params->filter_employee_code)) {
            $query->whereHas('employee', function ($q) {
                $q->where('employee_code', 'like', '%' . $this->filter_params->filter_employee_code . '%');
            });
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

        if (isset($this->filter_params->status) && $this->filter_params->status !== '' && $this->filter_params->status !== 'all') {
            $query->where('status', $this->filter_params->status);
        }

        if (!empty($this->filter_params->search)) {
            $search = trim($this->filter_params->search);
            $query->where(function ($q) use ($search) {
                $q->where('degree', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('unit_change', 'like', "%{$search}%")
                    ->orWhere('document_name', 'like', "%{$search}%")
                    ->orWhereHas('employee', function ($eq) use ($search) {
                        $eq->where('full_name', 'like', "%{$search}%")
                            ->orWhere('employee_code', 'like', "%{$search}%");
                    });
            });
        }

        if (!empty($this->filter_params->filter_created_by)) {
            $query->where('created_by', $this->filter_params->filter_created_by);
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
            'Degree',
            'Institute Name',
            'Month Of Passing Year',
            'Class Or % Marks',
            'Previous Company Name',
            'Unit Change',
            'Document Name',
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

        $rowData = array_merge($rowData, [
            optional($row->employee)->full_name ?? '-',
            $row->degree ?? '-',
            $row->institution_name ?? '-',
            $row->month_of_passing_year ?? '-',
            $row->class_or_mark ?? '-',
            $row->company_name ?? '-',
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
        return 'Employee Education Experience';
    }
}
