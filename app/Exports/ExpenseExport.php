<?php

namespace App\Exports;

use App\Models\Expense;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExpenseExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $srNo = 1;
    protected $filter_params;
    protected $user;
    protected $modules;
    protected $loginUserId;

    public function __construct($filter_params = null, $user = null, $modules = [], $loginUserId = null)
    {
        $this->filter_params = (object) $filter_params;
        $this->user = $user;
        $this->modules = $modules;
        $this->loginUserId = $loginUserId;
    }

    public function collection()
    {
        $query = Expense::with(['company', 'branch', 'employees', 'expense_category', 'expense_sub_category'])
            ->orderBy('id', 'DESC');

        $authUser = $this->user ?? null;
        $loginUserId = $this->loginUserId ?? ($authUser->id ?? null);
        $companyId = $this->modules['company_id'] ?? ($authUser?->company_id ?? null);

        if (!empty($companyId)) {
            $query->where('company_id', $companyId);
        }

        if (
            $authUser &&
            isset($authUser->role) &&
            $authUser->role === 'employee'
        ) {
            if (
                !empty($this->modules['personal_data_permission']) &&
                empty($this->modules['all_data_permission'])
            ) {
                $query->where('created_by', $loginUserId);
            }
        }

        // Filters
        if (!empty($this->filter_params->company_id)) {
            $query->where('company_id', $this->filter_params->company_id);
        } elseif (!empty($this->filter_params->company)) {
            $query->where('company_id', $this->filter_params->company);
        } elseif (!empty($this->filter_params->filter_company)) {
            $query->where('company_id', $this->filter_params->filter_company);
        }

        if (!empty($this->filter_params->expense_category_id)) {
            $query->where('expense_category_id', $this->filter_params->expense_category_id);
        } elseif (!empty($this->filter_params->expense_category)) {
            $query->where('expense_category_id', $this->filter_params->expense_category);
        } elseif (!empty($this->filter_params->filter_expense_category)) {
            $query->where('expense_category_id', $this->filter_params->filter_expense_category);
        }

        if (!empty($this->filter_params->expense_subcategory_id)) {
            $query->where('expense_subcategory_id', $this->filter_params->expense_subcategory_id);
        } elseif (!empty($this->filter_params->expense_subcategory)) {
            $query->where('expense_subcategory_id', $this->filter_params->expense_subcategory);
        } elseif (!empty($this->filter_params->filter_expense_subcategory_id)) {
            $query->where('expense_subcategory_id', $this->filter_params->filter_expense_subcategory_id);
        }

        if (!empty($this->filter_params->team_person_id)) {
            $query->where('team_person_id', $this->filter_params->team_person_id);
        } elseif (!empty($this->filter_params->team_person)) {
            $query->where('team_person_id', $this->filter_params->team_person);
        } elseif (!empty($this->filter_params->team_person_ids)) {
            $query->where('team_person_id', $this->filter_params->team_person_ids);
        } elseif (!empty($this->filter_params->employee_id)) {
            $query->where('team_person_id', $this->filter_params->employee_id);
        }

        if (!empty($this->filter_params->from_date) && !empty($this->filter_params->to_date)) {
            $query->whereBetween('date', [
                $this->filter_params->from_date,
                $this->filter_params->to_date
            ]);
        }

        if (isset($this->filter_params->status) && $this->filter_params->status !== 'all' && $this->filter_params->status !== '') {
            $query->where('status', $this->filter_params->status);
        }

        if (!empty($this->filter_params->search)) {
            $search = $this->filter_params->search;
            $query->where(function ($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                    ->orWhere('remark', 'like', "%{$search}%")
                    ->orWhere('req_amount', 'like', "%{$search}%")
                    ->orWhere('pass_amount', 'like', "%{$search}%")
                    ->orWhereHas('employees', function ($empQ) use ($search) {
                        $empQ->where('full_name', 'like', "%{$search}%")
                            ->orWhere('employee_code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('expense_category', function ($catQ) use ($search) {
                        $catQ->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('expense_sub_category', function ($subQ) use ($search) {
                        $subQ->where('name', 'like', "%{$search}%");
                    });
            });
        }

        return $query->get();
    }

    public function headings(): array
    {
        $headings = [
            'Sr No',
        ];

        // Include Company Name if user is not limited to a company
        $companyId = $this->modules['company_id'] ?? ($this->user?->company_id ?? null);
        if (empty($companyId)) {
            $headings[] = 'Company Name';
        }

        $headings = array_merge($headings, [
            'Employee Name',
            'Expense Category',
            'Expense Sub Category',
            'Expense Date',
            'Requested Amount',
            'Passed Amount',
            'Reason',
            'Remark',
            'Status',
        ]);

        return $headings;
    }

    public function map($row): array
    {
        $rowData = [
            $this->srNo++,
        ];

        $companyId = $this->modules['company_id'] ?? ($this->user?->company_id ?? null);
        if (empty($companyId)) {
            $rowData[] = optional($row->company)->company_name ?? '-';
        }

        $employeeName = '-';
        if ($row->employees) {
            $employeeName = ($row->employees->employee_code ? $row->employees->employee_code . ' - ' : '') . ($row->employees->full_name ?? $row->employees->proper_name ?? '');
        }

        $rowData = array_merge($rowData, [
            $employeeName,
            optional($row->expense_category)->name ?? '-',
            optional($row->expense_sub_category)->name ?? '-',
            $row->date ? Carbon::parse($row->date)->format('d/m/Y') : '-',
            $row->req_amount ?? '0',
            $row->pass_amount ?? '0',
            $row->reason ?? '-',
            $row->remark ?? '-',
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
        return 'Expense';
    }
}
