<?php

namespace App\Exports;

use App\Models\OperationsRateList;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OperationsRateListExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $srNo = 1;
    protected $filter_params;
    protected $user;
    protected $modules;
    protected bool $showCompanyColumn = false;

    public function __construct($filter_params = null, $user = null, $modules = [])
    {
        $this->filter_params = (object) $filter_params;
        $this->user = $user;
        $this->modules = $modules;
        $this->showCompanyColumn = empty($this->modules['company_id']);
    }

    public function collection()
    {
        $query = OperationsRateList::with('company')
            ->leftJoin('employees', 'employees.id', '=', 'operations_rate_lists.employee_id')
            ->selectRaw('MIN(operations_rate_lists.id) as id, 
                         operations_rate_lists.company_id, 
                         operations_rate_lists.operation, 
                         MIN(operations_rate_lists.employee_id) as employee_id,
                         operations_rate_lists.month, 
                         operations_rate_lists.year, 
                         SUM(operations_rate_lists.total_qty) as total_qty, 
                         SUM(operations_rate_lists.total_amount) as total_amount, 
                         operations_rate_lists.status, 
                         GROUP_CONCAT(DISTINCT CONCAT(employees.employee_code, " - ", employees.full_name) SEPARATOR ", ") as multi_names');

        if (!empty($this->filter_params->filter_company)) {
            $query->where('operations_rate_lists.company_id', $this->filter_params->filter_company);
        } elseif (!empty($this->modules['company_id'])) {
            $query->where('operations_rate_lists.company_id', $this->modules['company_id']);
        } elseif ($this->user && $this->user?->company_id) {
            $query->where('operations_rate_lists.company_id', $this->user->company_id);
        }

        if (isset($this->filter_params?->operation) && $this->filter_params->operation !== '' && $this->filter_params->operation !== 'all') {
            $query->where('operations_rate_lists.operation', $this->filter_params->operation);
        }

        if (!empty($this->filter_params->employee_id)) {
            $query->where('operations_rate_lists.employee_id', $this->filter_params->employee_id);
        }

        if (isset($this->filter_params?->month) && $this->filter_params->month !== '') {
            $query->where('operations_rate_lists.month', $this->filter_params->month);
        }

        if (isset($this->filter_params?->year) && $this->filter_params->year !== '') {
            $query->where('operations_rate_lists.year', $this->filter_params->year);
        }

        return $query->groupBy([
            'operations_rate_lists.company_id',
            'operations_rate_lists.operation',
            'operations_rate_lists.employee_id',
            'operations_rate_lists.month',
            'operations_rate_lists.year',
            'operations_rate_lists.status',
        ])
            ->orderBy('id', 'DESC')
            ->get();
    }

    public function headings(): array
    {
        $headings = [
            'Sr',
            'Operation',
            'Name',
            'Month',
            'Year',
            'Total Qty',
            'Total Amount',
        ];

        if ($this->showCompanyColumn) {
            array_splice($headings, 1, 0, 'Company Name');
        }

        return $headings;
    }

    public function map($row): array
    {
        $names = $row->multi_names ? explode(', ', $row->multi_names) : [];
        $displayName = (count($names) > 1) ? 'ALL EMPLOYEE' : ($row->multi_names ?? '-');

        $rowData = [
            $this->srNo++,
            $row->operation,
            $displayName,
            date('F', mktime(0, 0, 0, $row->month, 1)),
            $row->year,
            $row->total_qty,
            $row->total_amount,
        ];

        if ($this->showCompanyColumn) {
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
}
