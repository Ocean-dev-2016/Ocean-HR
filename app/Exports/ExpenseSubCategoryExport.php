<?php

namespace App\Exports;

use App\Models\ExpenseSubCategory;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExpenseSubCategoryExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
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
        $query = ExpenseSubCategory::with(['company', 'branch', 'expense_category'])
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
        }

        if (!empty($this->filter_params->branch_id)) {
            $query->where('branch_id', $this->filter_params->branch_id);
        } elseif (!empty($this->filter_params->branch)) {
            $query->where('branch_id', $this->filter_params->branch);
        }

        if (!empty($this->filter_params->expense_category_id)) {
            $query->where('expense_category_id', $this->filter_params->expense_category_id);
        } elseif (!empty($this->filter_params->expense_category)) {
            $query->where('expense_category_id', $this->filter_params->expense_category);
        }

        if (isset($this->filter_params->status) && $this->filter_params->status !== 'all' && $this->filter_params->status !== '') {
            $query->where('status', $this->filter_params->status);
        }

        if (!empty($this->filter_params->search)) {
            $search = $this->filter_params->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('expense_type', 'like', "%{$search}%")
                    ->orWhereHas('expense_category', function ($catQ) use ($search) {
                        $catQ->where('name', 'like', "%{$search}%");
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

        $companyId = $this->modules['company_id'] ?? ($this->user?->company_id ?? null);
        if (empty($companyId)) {
            $headings[] = 'Company Name';
        }

        $headings = array_merge($headings, [
            'Branch Name',
            'Expense Sub Category Name',
            'Expense Category Name',
            'Team Person Names',
            'Expense Type',
            'Min Amount',
            'Max Amount',
            'Per KM Rate',
            'Fix Amount',
            'From Time',
            'To Time',
            'Image Required',
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

        $teamPersons = '-';
        if ($row->employees && $row->employees->isNotEmpty()) {
            $teamPersons = $row->employees->pluck('proper_name')->filter()->implode(', ');
            if (empty($teamPersons)) {
                $teamPersons = $row->employees->pluck('full_name')->filter()->implode(', ');
            }
        }

        $rowData = array_merge($rowData, [
            optional($row->branch)->name ?? '-',
            $row->name ?? '-',
            optional($row->expense_category)->name ?? '-',
            $teamPersons ?: 'All',
            $row->expense_type ?? '-',
            $row->min_amount ?? '-',
            $row->max_amount ?? '-',
            $row->per_km_rate ?? '-',
            $row->fix_amount ?? '-',
            $row->from_time ?? '-',
            $row->to_time ?? '-',
            $row->is_image_required == 1 ? 'Yes' : 'No',
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
        return 'Expense Sub Category';
    }
}
