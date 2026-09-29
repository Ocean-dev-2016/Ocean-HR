<?php

namespace App\Exports;

use App\Models\Bonus;
use App\Models\Company;
use App\Models\Branch;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class BonusExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
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
        $query = Bonus::with(['company', 'branch', 'employeeRelation'])
            ->orderBy('id', 'DESC');

        $authUser = $this->user ?? Auth::guard('admin_software')->user() ?? Auth::guard('employees')->user();
        $loginUserId = $authUser?->id ?? null;
        $companyId = $this->modules['company_id'] ?? $authUser?->company_id ?? null;

        // Company scope for logged-in company user
        if (!empty($companyId)) {
            $query->where('company_id', $companyId);
        }

        // Personal data permission check
        $hasPersonalOnly = (!empty($this->modules['personal_data_permission']) && empty($this->modules['all_data_permission'])) ||
                           (!empty($this->modules['personalDataPermission']) && empty($this->modules['allDataPermission']));
        if ($hasPersonalOnly && $loginUserId) {
            $query->where('created_by', $loginUserId);
        }

        // Filter by Company (if passed from dropdown filter)
        $filterCompany = $this->filter_params->company_id ?? $this->filter_params->company ?? $this->filter_params->filter_company ?? null;
        if (!empty($filterCompany)) {
            $query->where('company_id', $filterCompany);
        }

        // Filter by Branch
        if (!empty($this->filter_params->branch)) {
            $query->where('branch', $this->filter_params->branch);
        }

        // Filter by Employee
        if (!empty($this->filter_params->employee_id)) {
            $query->where('employee', $this->filter_params->employee_id);
        }

        // Filter by Status
        if (isset($this->filter_params->status) && $this->filter_params->status !== '' && $this->filter_params->status !== 'all') {
            $query->where('status', $this->filter_params->status);
        }

        // Search text
        if (!empty($this->filter_params->search)) {
            $search = $this->filter_params->search;
            $query->where(function ($q) use ($search) {
                $q->where('year', 'like', "%{$search}%")
                    ->orWhere('month', 'like', "%{$search}%")
                    ->orWhere('amount', 'like', "%{$search}%")
                    ->orWhereHas('employeeRelation', function ($eq) use ($search) {
                        $eq->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('full_name', 'like', "%{$search}%")
                            ->orWhere('employee_code', 'like', "%{$search}%");
                    });
            });
        }

        return $query->get();
    }

    public function headings(): array
    {
        $headings = ['Sr No'];

        $companyId = $this->modules['company_id'] ?? $this->user?->company_id ?? null;
        if (empty($companyId)) {
            $headings[] = 'Company Name';
        }

        $headings = array_merge($headings, [
            'Branch',
            'Employee Code',
            'Employee Name',
            'Year',
            'Month',
            'Amount',
            'Status'
        ]);

        return $headings;
    }

    public function map($row): array
    {
        $companyId = $this->modules['company_id'] ?? $this->user?->company_id ?? null;

        $rowData = [$this->srNo++];

        if (empty($companyId)) {
            $rowData[] = $row->company?->company_name ?? '-';
        }

        $branchName = $row->branch?->name ?? $row->branch_name ?? '-';
        $empCode = $row->employeeRelation?->employee_code ?? '-';
        $empName = $row->employeeRelation?->proper_name ?? $row->employeeRelation?->full_name ?? ($row->employeeRelation ? trim(($row->employeeRelation->first_name ?? '') . ' ' . ($row->employeeRelation->last_name ?? '')) : '-');

        $rowData[] = $branchName;
        $rowData[] = $empCode;
        $rowData[] = $empName;
        $rowData[] = $row->year ?? '-';
        $rowData[] = $row->month ?? '-';
        $rowData[] = number_format((float) ($row->amount ?? 0), 2, '.', '');
        $rowData[] = ucfirst($row->status ?? 'Active');

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
        return 'Bonus List';
    }
}
