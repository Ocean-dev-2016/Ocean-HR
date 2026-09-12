<?php

namespace App\Exports;

use App\Models\Department;
use App\Models\LeaveApplication;
use App\Models\Loan;
use App\Models\RequestForm;
use App\Models\SubDepartment;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RequestFormExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
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
        $query = RequestForm::withTrashed()
            ->with(['company', 'requestFromEmployee', 'requestToEmployee'])
            ->orderBy('id', 'DESC');

        $authUser = $this->user ?? null; // Assuming $this->user is authenticated user
        $loginUserId = $authUser->id ?? null;

        // Employee guard-based access
        if ($authUser && $authUser->role === 'employee') { // adjust role check if needed
            $teamPersonCompanyId = $authUser->company_id;
            $query->where('company_id', $teamPersonCompanyId);

            if (!empty($this->modules['personalDataPermission']) && empty($this->modules['allDataPermission'])) {
                $query->where('created_by', $loginUserId);
            }
        }

        // Filters
        if (!empty($this->filter_params->company_id)) {
            $query->where('company_id', 'LIKE', '%' . $this->filter_params->company_id . '%');
        }

        if (!empty($this->filter_params->employee_id)) {
            $employeeName = $this->filter_params->employee_id;
            $query->where(function ($q) use ($employeeName) {
                $q->where('request_from_employee_name', $employeeName)
                    ->orWhere('request_to_employee_name', $employeeName);
            });
        }


        if (isset($this->filter_params->status) && $this->filter_params->status !== 'all') {
            $query->where('status', $this->filter_params->status);
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
            'Request From',
            'Request To',

            'Description',
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

        // Add the rest of the data in same order as headings
        $rowData = array_merge($rowData, [
            optional($row->requestFromEmployee)->employee_code . ' - ' . optional($row->requestFromEmployee)->full_name ?? '-',
            optional($row->requestToEmployee)->employee_code . ' - ' . optional($row->requestToEmployee)->full_name ?? '-',

            $row->request_description ?? '-',
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
        return 'Department';
    }
}
