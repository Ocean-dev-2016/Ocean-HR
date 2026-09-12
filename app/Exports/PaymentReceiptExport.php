<?php

namespace App\Exports;

use App\Models\Department;
use App\Models\LeaveApplication;
use App\Models\PaymentReceipt;
use App\Models\SubDepartment;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PaymentReceiptExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
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
        $query = PaymentReceipt::with(['company', 'branch', 'employee', 'departments'])
            ->withTrashed()
            ->orderBy('id', 'DESC');

        $authUser = $this->user ?? null; // Authenticated user
        $loginUserId = $authUser->id ?? null;
        $company_id = $authUser->company_id ?? null;

        // Employee guard-based access
        if ($authUser && $authUser->role === 'employee') {
            $query->where('company_id', $company_id);

            if (!empty($this->modules['personalDataPermission']) && empty($this->modules['allDataPermission'])) {
                $query->where('created_by', $loginUserId);
            }
        }

        // Filters from request/params
        if (!empty($this->filter_params->company_id)) {
            $query->where('company_id', $this->filter_params->company_id);
        }

        if (!empty($this->filter_params->branch_id)) {
            $query->where('branch_id', $this->filter_params->branch_id);
        }

        if (!empty($this->filter_params->employee_id)) {
            $query->where('employee_id', $this->filter_params->employee_id);
        }

        if (!empty($this->filter_params->filter_effect_on_month)) {
            $query->where('effect_on_month', $this->filter_params->filter_effect_on_month);
        }

        if (!empty($this->filter_params->filter_effect_of_year)) {
            $query->where('effect_of_year', $this->filter_params->filter_effect_of_year);
        }

        if (isset($this->filter_params->status) && $this->filter_params->status !== 'all') {
            $query->where('status', $this->filter_params->status);
        }

        if (!empty($this->filter_params->filter_date)) {
            $filterDate = trim($this->filter_params->filter_date);
            $dates = explode(' to ', $filterDate);

            if (count($dates) === 2) {
                $start = Carbon::createFromFormat('d/m/Y', trim($dates[0]))->startOfDay();
                $end   = Carbon::createFromFormat('d/m/Y', trim($dates[1]))->endOfDay();
                $query->whereBetween('date', [$start, $end]);
            } else {
                $singleDate = Carbon::createFromFormat('d/m/Y', trim($dates[0]));
                $query->whereDate('date', $singleDate);
            }
        }

        if (!empty($this->filter_params->search)) {
            $search = $this->filter_params->search;
            $query->where(function ($q) use ($search) {
                $q->where('receipt_no', 'like', "%{$search}%")
                    ->orWhere('amount', 'like', "%{$search}%")
                    ->orWhere('remark', 'like', "%{$search}%");
            });
        }

        return $query->get();
    }

    public function headings(): array
    {
        $headings = [
            'Sr No.',
        ];

        // Include Company Name if user is not limited to a company
        if (!$this->user || empty($this->user['company_id'])) {
            $headings[] = 'Company';
        }

        // Add the rest of the columns separately
        $headings = array_merge($headings, [
            'Branch',
            'Department',
            'Employee',
            'Effect Month & Year',
            'Date',
            'Payment Mode',
            'Amount',
            'Payment Type',
            'Receipt No.',
            'Status',
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

        // Branch
        $rowData[] = optional($row->branch)->name ?? '-';

        // Department
        $rowData[] = optional($row->departments)->name ?? '-';

        // Employee
        $rowData[] = optional($row->employee)->employee_code . ' - ' . optional($row->employee)->full_name ?? '-';

        // Effect Month & Year
        $rowData[] = ($row->effect_on_month ?? '-') . ' / ' . ($row->effect_of_year ?? '-');

        // Date
        $rowData[] = $row->date ? Carbon::parse($row->date)->format('d/m/Y') : '-';

        // Payment Mode
        $rowData[] = $row->payment_mode ?? '-';

        // Amount
        $rowData[] = $row->amount ?? '-';

        // Payment Type
        $rowData[] = $row->payment_type ?? '-';

        // Receipt No.
        $rowData[] = $row->receipt_no ?? '-';

        // Status
        $rowData[] = ucfirst($row->status ?? '-');

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
