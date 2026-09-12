<?php

namespace App\Exports;

use App\Models\EmployeeAsignAssets;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class EmployeeAsignAssetsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
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
        $query = EmployeeAsignAssets::with(['company', 'assets', 'employee']);

        if (Auth::guard('employees')->check()) {
            $teamPerson = Auth::guard('employees')->user();
            $query->where('company_id', $teamPerson->company_id);

            if (!empty($this->modules['personal_data_permission']) && empty($this->modules['all_data_permission'])) {
                $query->where('created_by', $teamPerson->id);
            }
        } elseif ($this->user) {
            if (!empty($this->modules['personal_data_permission']) && empty($this->modules['all_data_permission'])) {
                $query->where('created_by', $this->user->id);
            }

            if (!empty($this->user->company_id) && empty($this->filter_params->company) && empty($this->filter_params->filter_company)) {
                $query->where('company_id', $this->user->company_id);
            }
        }


        $search = trim($this->filter_params->search ?? '');
        if ($search !== '') {
            $query->whereHas('assets', function ($assetQuery) use ($search) {
                $assetQuery->where('name', 'like', "%{$search}%");
            });
        }

        $companyId = $this->filter_params->company
            ?? $this->filter_params->filter_company
            ?? null;
        if (!empty($companyId)) {
            $query->where('company_id', $companyId);
        }

        $employeeId = $this->filter_params->employee
            ?? $this->filter_params->filter_employee
            ?? null;
        if (!empty($employeeId)) {
            $query->where('employee_id', $employeeId);
        }

        // Status filter
        if (
            isset($this->filter_params?->status) &&
            $this->filter_params->status !== '' &&
            $this->filter_params->status !== 'all'
        ) {
            $query->where('status', $this->filter_params->status);
        }

        if (!empty($this->filter_params?->filter_created_by)) {
            $query->where('created_by', $this->filter_params->filter_created_by);
        }

        if (!empty($this->filter_params?->filter_date)) {
            $rawDate = $this->filter_params->filter_date;
            $separator = Str::contains($rawDate, ' to ') ? ' to ' : ' - ';
            $dates = array_map('trim', explode($separator, $rawDate));

            $fromDate = null;
            $toDate = null;

            try {
                if (!empty($dates[0])) {
                    $fromDate = Carbon::createFromFormat('d/m/Y', $dates[0])->format('Y-m-d');
                }
                if (!empty($dates[1] ?? null)) {
                    $toDate = Carbon::createFromFormat('d/m/Y', $dates[1])->format('Y-m-d');
                }
            } catch (\Exception $e) {
                $fromDate = $toDate = null;
            }

            $tableColumn = (new EmployeeAsignAssets())->getTable() . '.date';

            if ($fromDate && $toDate) {
                $query->whereBetween($tableColumn, [$fromDate, $toDate]);
            } elseif ($fromDate) {
                $query->where($tableColumn, $fromDate);
            }
        }
        return $query->orderByDesc('id')->get();
        //  $query = $query->latest();
        // // dd("L-69", $this->filter_params, Helper::interpolateQuery($query->toSql(), $query->getBindings()), $this->user?->toArray());
        // $query = $query->get();
        // return $query;
    }

    public function headings(): array
    {
        $headings = [
            'Sr No',
            'Employee Name',
            'Asset Name',
            'Date',
            'Reference No',

            'Description',
            'Status',
        ];

        // If user is not limited to a company, insert company name after Sr No
        if (!$this->user || empty($this->user['company_id'])) {
            array_splice($headings, 1, 0, 'Company Name');
        }

        return $headings;
    }

    public function map($row): array
    {
        // Basic row data
        $rowData = [
            $this->srNo++,
            // optional($row->employee)->middle_name ?? '-',
            optional($row->employee)->employee_code . ' - ' . optional($row->employee)->full_name ?? '-',
            optional($row->assets)->name ?? '-',
            $row->date ? \Carbon\Carbon::parse($row->date)->format('d/m/Y') : '-',
            $row->reference_no ?? '-',
            $row->descrption ?? '-',
            ucfirst($row->status ?? '-')
        ];

        // If user is not limited to a company, insert company name after Sr No
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
        return 'LeaveType';
    }
}
