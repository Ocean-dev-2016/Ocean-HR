<?php

namespace App\Exports;

use App\Models\AssetsAllocationMaster;
use App\Models\EmployeeIncrementDetails;
use App\Models\LeaveType;
use App\Models\ReferenceMaster;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeeIncrementDetailsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
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
        $query = EmployeeIncrementDetails::with('company', 'employee', 'designation');

        $companyId = $this->extractFilterValue(['company', 'filter_company']);
        $employeeId = $this->extractFilterValue(['employee', 'filter_employee']);
        $designationId = $this->extractFilterValue(['designation', 'filter_designation']);
        $status = $this->extractFilterValue(['status']);
        $searchTerm = $this->extractFilterValue(['search']);
        $createdBy = $this->extractFilterValue(['filter_created_by']);
        $dateRange = $this->extractFilterValue(['filter_date', 'employee_date']);

        if ($companyId) {
            $query->where('company_id', $companyId);
        } elseif ($this->user && $this->user?->company_id) {
            $query->where('company_id', $this->user?->company_id);
        }

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        if ($designationId) {
            $query->where('designation_id', $designationId);
        }

        if ($searchTerm) {
            $query->whereHas('employee', function ($q) use ($searchTerm) {
                $q->where('middle_name', 'like', "%{$searchTerm}%")
                    ->orWhere('first_name', 'like', "%{$searchTerm}%")
                    ->orWhere('last_name', 'like', "%{$searchTerm}%")
                    ->orWhere('full_name', 'like', "%{$searchTerm}%")
                    ->orWhere('employee_code', 'like', "%{$searchTerm}%");
            });
        }

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($dateRange) {
            $dates = explode(' to ', $dateRange);

            try {
                $fromDate = isset($dates[0]) && $dates[0] !== ''
                    ? Carbon::createFromFormat('d/m/Y', $dates[0])->startOfDay()
                    : null;
                $toDate = isset($dates[1]) && $dates[1] !== ''
                    ? Carbon::createFromFormat('d/m/Y', $dates[1])->endOfDay()
                    : null;
            } catch (\Exception $e) {
                $fromDate = $toDate = null;
            }

            $tableColumn = (new EmployeeIncrementDetails())->getTable() . '.icrement_date';

            if ($fromDate && $toDate) {
                $query->whereBetween($tableColumn, [$fromDate, $toDate]);
            } elseif ($fromDate) {
                $query->whereDate($tableColumn, $fromDate);
            }
        }

        if ($createdBy) {
            $query->where('created_by', $createdBy);
        } elseif ($this->user && $this->user->company_id && empty($this->modules['all_data_permission'])) {
            if (!empty($this->modules['personal_data_permission'])) {
                $query->where('created_by', $this->user->id);
            }
        }

        return $query->latest()->get();
    }

    private function extractFilterValue(array $keys): ?string
    {
        foreach ($keys as $key) {
            if (!isset($this->filter_params->{$key})) {
                continue;
            }

            $value = $this->filter_params->{$key};

            if (is_array($value)) {
                $value = reset($value);
            }

            if ($value === null) {
                continue;
            }

            $trimmed = trim((string) $value);

            if ($trimmed === '' || in_array(strtolower($trimmed), ['null', 'undefined'], true)) {
                continue;
            }

            return $trimmed;
        }

        return null;
    }

    public function headings(): array
    {
        $headings = [
            'Sr',
            'ID',
        ];

        // If not restricted to company, include company name
        if (!$this->user || empty($this->user['company_id'])) {
            $headings[] = 'Company Name';
        }

        $headings = array_merge($headings, [
            'Employee Name',
            'Increment Date',
            'Basic + D.A',
            'HRA',
            'Conveyance Allowance',
            'Medical Allowance',
            'Special Allowance',
            'PF',
            'Effective Month',
            'Effective Year',
            'Designation Name',
            'Per Day Salary',
            'Per Hour Salary',
            'Remark',
            'Status',
        ]);

        return $headings;
    }

    public function map($row): array
    {
        $rowData = [
            $this->srNo++,
            $row->id,
        ];

        // If not restricted to company, insert company name
        if (!$this->user || empty($this->user['company_id'])) {
            $rowData[] = optional($row->company)->company_name ?? '-';
        }

        $rowData = array_merge($rowData, [
            optional($row->employee)->employee_code . ' - ' . optional($row->employee)->full_name ?? '-',

            $row->icrement_date ? \Carbon\Carbon::parse($row->icrement_date)->format('d/m/Y') : '-',
            $row->basic_da,
            $row->hra,
            $row->conveyance_allowance,
            $row->medical_allowance,
            $row->special_allowance,
            $row->pf,
            $row->effective_month,
            $row->effective_year,
            $row->designation->name ?? '-',
            $row->per_day_salary,
            $row->per_hour_salary,
            $row->remark,
            ucfirst($row->status),
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
        return 'LeaveType';
    }
}
