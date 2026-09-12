<?php

namespace App\Exports;

use App\Models\Department;
use App\Models\LeaveApplication;
use App\Models\Loan;
use App\Models\SubDepartment;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LoanExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
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
        $query = Loan::withTrashed()
            ->with(['company', 'employee','loan_type'])
            ->orderBy('id', 'DESC');

           // Filter by company
        if ($this->filter_params?->company_id) {
            $query->where('company_id', $this->filter_params->company_id);
        }

        if (!empty($this->filter_params->company_id)) {
            $query->where('company_id', $this->filter_params->company_id);
        } else if ($this->user && $this->user?->company_id) {
            $query->where('company_id', $this->user?->company_id);
        }

       

        if (!empty($this->filter_params->employee_id)) {
            $query->where('employee_id', $this->filter_params->employee_id);
        }


        if (isset($this->filter_params->status) && $this->filter_params->status !== 'all') {
            $query->where('status', $this->filter_params->status);
        }

        if (!empty($this->filter_params->search)) {
            $search = $this->filter_params->search;
            $query->where(function ($q) use ($search) {
                $q->where('leave_reason', 'like', "%{$search}%")
                    ->orWhere('rejection_reason', 'like', "%{$search}%");
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
        if (!$this->user || empty($this->user['company_id'])) {
            $headings[] = 'Company Name';
        }

        // Add the rest of the columns
        $headings = array_merge($headings, [
            'Employee Name',
            'Loan Type',
            'Loan Amount',
            'Balance Amount',
            'EMI Amount',
            'Total Installments',
            'Remaining Installments',
            'Interest Type',
            'Interest Rate',
            'Loan Date',
            'Status',
            'Remark',
        ]);

        return $headings;
    }

    public function map($row): array
    {
        $rowData = [
            $this->srNo++,
        ];

        // Company Name
        if (!$this->user || empty($this->user['company_id'])) {
            $rowData[] = optional($row->company)->company_name ?? '-';
        }

        // Add the rest of the data in the same order as headings
        $rowData = array_merge($rowData, [
            optional($row->employee)->employee_code . ' - ' . optional($row->employee)->full_name ?? '-',
            optional($row->loan_type)->name ?? '-',
            $row->loan_amount ?? '-',
            $row->balance_amount ?? '-',
            $row->emi_amount ?? '-',
            $row->total_installments ?? '-',
            $row->remaining_installments ?? '-',
            ucfirst($row->interest_type ?? '-'),
            $row->interest_rate ?? '-',
            $row->loan_date ? \Carbon\Carbon::parse($row->loan_date)->format('d/m/Y') : '-',
            ucfirst($row->status ?? '-'),
            $row->remark ?? '-',
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
        return 'Department';
    }
}
