<?php

namespace App\Exports;

use App\Models\Bonus;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

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
        $query = Bonus::with('company','branch','employeeRelation');



        $authUser = $this->user ?? null;
        $loginUserId = $authUser->id ?? null;
        // Employee guard-based access
        if ($authUser && $authUser->role === 'employee') {
            $query->where('company_id', $authUser->company_id);
            if (!empty($this->modules['personalDataPermission']) && empty($this->modules['allDataPermission'])) {
                $query->where('created_by', $loginUserId);
            }
        }

        // Filters
        if (!empty($this->filter_params->company_id)) {
            $query->where('company_id', $this->filter_params->company_id);
        }

        if (!empty($this->filter_params->employee_id)) {
            $query->where('employee', $this->filter_params->employee_id);
        }

        if (isset($this->filter_params->status) && $this->filter_params->status !== 'all') {
            $query->where('status', $this->filter_params->status);
        }

        if (!empty($this->filter_params->search)) {
            $search = $this->filter_params->search;
            $query->where(function ($q) use ($search) {
                $q->where('year', 'like', "%{$search}%")
                    ->orWhere('month', 'like', "%{$search}%")
                    ->orWhere('amount', 'like', "%{$search}%");
            });
        }

        return $query->get();
    }

    public function headings(): array
    {
        $headings = [
            'Sr',
            'Year',
            'Month',
            'Amount',
            // 'Branch',
            'Employee',
            'Status',
        ];

        // Insert Company column if user is not restricted to a company
        if (!$this->user || empty($this->user['company_id'])) {
            array_splice($headings, 1, 0, 'Company Name');
        }

        return $headings;
    }

    public function map($row): array
    {
        // Build row data
        $rowData = [
            $this->srNo++,
            $row->year,
            $row->month,
            $row->amount,
            // $row->branch->name ?? '-',
            optional($row->employeeRelation)->employee_code ?
                optional($row->employeeRelation)->employee_code . ' - ' . optional($row->employeeRelation)->full_name
                : '-', // safe if employeeRelation is null
            $row->status
        ];

        // Insert Company Name if needed
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
        return 'Bonus';
    }
}
