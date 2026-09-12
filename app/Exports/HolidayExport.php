<?php

namespace App\Exports;

use App\Models\Holiday;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

use Carbon\Carbon as CarbonCarbon;
use Illuminate\Support\Carbon as SupportCarbon;

class HolidayExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
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
        $query = Holiday::with('company');

        // Apply search filter
        if (isset($this->filter_params->search) && $this->filter_params->search !== '') {
            $query->where(function ($q) {
                $q->where('employee_designation_type', 'like', '%' . $this->filter_params->search . '%')
                    ->orwhere('remark', 'like', '%' . $this->filter_params->search . '%')
                    ->orWhereHas('company', function ($q2) {
                        $q2->where('company_name', 'like', '%' . $this->filter_params->search . '%');
                    });
            });
        }

        // Date filter
        if (!empty($this->filter_params?->filter_date)) {
            $dates = explode(' to ', $this->filter_params->filter_date);

            try {
                $fromDate = isset($dates[0]) && $dates[0] != ''
                    ? Carbon::createFromFormat('d/m/Y', $dates[0])->startOfDay()
                    : null;
                $toDate = isset($dates[1]) && $dates[1] != ''
                    ? Carbon::createFromFormat('d/m/Y', $dates[1])->endOfDay()
                    : null;
            } catch (\Exception $e) {
                $fromDate = $toDate = null;
            }

            $tableColumn = (new Holiday())->getTable() . '.date';

            if ($fromDate && $toDate) {
                $query->whereBetween($tableColumn, [$fromDate, $toDate]);
            } elseif ($fromDate) {
                $query->whereDate($tableColumn, $fromDate);
            }
        }

        if (!empty($this->filter_params->filter_company)) {
            $query->where('company_id', $this->filter_params->filter_company);
        } else if ($this->user && $this->user?->company_id) {
            $query->where('company_id', $this->user?->company_id);
        }
        // Filter by company
        if ($this->filter_params?->company) {
            $query->where('company_id', $this->filter_params->company);
        }
        if (!empty($this->filter_params?->search)) {
            $search = $this->filter_params->search;

            $query->where(function ($q) use ($search) {
                $q->where('employee_designation_type', 'like', '%' . $search . '%')
                    ->orWhere('remark', 'like', '%' . $search . '%');
            });
        }


        // Filter by status
        if (
            isset($this->filter_params?->status) &&
            $this->filter_params->status !== '' &&
            $this->filter_params->status !== 'all'
        ) {
            $query->where('status', $this->filter_params->status);
        }
        if (!empty($this->filter_params->filter_created_by)) {
            $query->where('created_by', $this->filter_params->filter_created_by);
        } elseif ($this->user && $this->user->company_id && empty($this->modules['all_data_permission'])) {
            if (!empty($this->modules['personal_data_permission'])) {
                $query->where('created_by', $this->user->id);
            }
        }

        return $query->latest()->get();
        //  $query = $query->latest();
        // // dd("L-69", $this->filter_params, Helper::interpolateQuery($query->toSql(), $query->getBindings()), $this->user?->toArray());
        // $query = $query->get();
        // return $query;
    }

    public function headings(): array
    {
        $headings =  [
            'Sr',
            'Employee Designation Type',
            'Date',
            'Remark',
            'Status',
        ];
        if (!$this->user || empty($this->user['company_id'])) {
            array_splice($headings, 1, 0, 'Company Name'); // insert after Sr No
        }

        return $headings;
    }

    public function map($row): array
    {


        // Basic row data
        $rowData = [
            $this->srNo++,
            $row->employee_designation_type,
            \Carbon\Carbon::parse($row->date)->format('d M, Y'),
            $row->remark,
            $row->status
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
        return 'Holiday';
    }
}
