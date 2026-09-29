<?php

namespace App\Exports;

use App\Models\Company;
use App\Models\Employee;
use App\Models\RequestForm;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

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
        $query = RequestForm::with(['company', 'requestFromEmployee', 'requestToEmployee'])
            ->orderBy('id', 'DESC');

        $authUser = $this->user ?? Auth::guard('admin_software')->user() ?? Auth::guard('employees')->user();
        $loginUserId = $authUser?->id ?? null;
        $companyId = $this->modules['company_id'] ?? $authUser?->company_id ?? null;

        // Company scope for logged-in company / employee
        if (!empty($companyId)) {
            $query->where('company_id', $companyId);
        }

        // Personal data permission check
        $hasPersonalOnly = (!empty($this->modules['personal_data_permission']) && empty($this->modules['all_data_permission'])) ||
                           (!empty($this->modules['personalDataPermission']) && empty($this->modules['allDataPermission']));
        if ($hasPersonalOnly && $loginUserId) {
            $query->where('created_by', $loginUserId);
        }

        // Filter by Company
        $filterCompany = $this->filter_params->company_id ?? $this->filter_params->company ?? $this->filter_params->filter_company ?? null;
        if (!empty($filterCompany)) {
            $query->where('company_id', $filterCompany);
        }

        // Filter by Employee
        $filterEmployee = $this->filter_params->employee_id ?? $this->filter_params->employee ?? $this->filter_params->filter_employee ?? null;
        if (!empty($filterEmployee)) {
            $query->where(function ($q) use ($filterEmployee) {
                $q->where('request_from_employee_name', $filterEmployee)
                    ->orWhere('request_to_employee_name', $filterEmployee);
            });
        }

        // Filter by Status
        if (isset($this->filter_params->status) && $this->filter_params->status !== '' && $this->filter_params->status !== 'all') {
            $query->where('status', $this->filter_params->status);
        }

        // Filter by Search text
        if (!empty($this->filter_params->search)) {
            $search = $this->filter_params->search;
            $query->where(function ($q2) use ($search) {
                $q2->where('request_description', 'like', '%' . $search . '%')
                    ->orWhereHas('requestFromEmployee', function ($sub) use ($search) {
                        $sub->where('employee_code', 'like', '%' . $search . '%')
                            ->orWhere('first_name', 'like', '%' . $search . '%')
                            ->orWhere('last_name', 'like', '%' . $search . '%')
                            ->orWhere('full_name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('requestToEmployee', function ($sub) use ($search) {
                        $sub->where('employee_code', 'like', '%' . $search . '%')
                            ->orWhere('first_name', 'like', '%' . $search . '%')
                            ->orWhere('last_name', 'like', '%' . $search . '%')
                            ->orWhere('full_name', 'like', '%' . $search . '%');
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
            'Request From',
            'Request To',
            'Description',
            'Status',
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

        $fromEmp = $row->requestFromEmployee
            ? ($row->requestFromEmployee->employee_code . ' / ' . ($row->requestFromEmployee->full_name ?? ($row->requestFromEmployee->first_name . ' ' . $row->requestFromEmployee->last_name)))
            : '-';

        $toEmp = $row->requestToEmployee
            ? ($row->requestToEmployee->employee_code . ' / ' . ($row->requestToEmployee->full_name ?? ($row->requestToEmployee->first_name . ' ' . $row->requestToEmployee->last_name)))
            : '-';

        $rowData[] = $fromEmp;
        $rowData[] = $toEmp;
        $rowData[] = $row->request_description ?? '-';
        $rowData[] = ucfirst($row->status ?? 'Active');

        return $rowData;
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $fullRange = "A1:{$highestColumn}{$highestRow}";

        // Header Styling
        $sheet->getStyle("A1:{$highestColumn}1")->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Borders for the entire table
        $sheet->getStyle($fullRange)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D0D5DD'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Center align Sr No and Status columns
        $sheet->getStyle("A2:A{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("{$highestColumn}2:{$highestColumn}{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [];
    }

    public function title(): string
    {
        return 'Request Form';
    }
}
