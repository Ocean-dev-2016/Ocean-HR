<?php

namespace App\Exports;

use App\Models\Attendance;
use App\Models\Company;
use App\Models\Employee;
use App\Models\LeaveApplication;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
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
        $authUser = $this->user ?? Auth::guard('admin_software')->user() ?? Auth::guard('employees')->user();
        $loginUserId = $authUser?->id ?? null;
        $companyId = $this->modules['company_id'] ?? $authUser?->company_id ?? null;

        $hasPersonalOnly = (!empty($this->modules['personal_data_permission']) && empty($this->modules['all_data_permission'])) ||
                           (!empty($this->modules['personalDataPermission']) && empty($this->modules['allDataPermission']));

        // Parse date range
        $fromDate = null;
        $toDate = null;
        $dateInput = $this->filter_params->filter_date ?? $this->filter_params->employee_date ?? null;
        if (!empty($dateInput)) {
            $dates = (strpos($dateInput, ' to ') !== false)
                ? explode(' to ', $dateInput)
                : [$dateInput, $dateInput];

            try {
                $fromDate = Carbon::createFromFormat('d/m/Y', trim($dates[0]))->startOfDay();
                $toDate = Carbon::createFromFormat('d/m/Y', trim($dates[1]))->endOfDay();
            } catch (\Exception $e) {
                try {
                    $fromDate = Carbon::parse(trim($dates[0]))->startOfDay();
                    $toDate = Carbon::parse(trim($dates[1]))->endOfDay();
                } catch (\Exception $e2) {
                    $fromDate = $toDate = null;
                }
            }
        }

        $companyConstraint = function ($q) use ($companyId, $hasPersonalOnly, $loginUserId) {
            if (!empty($companyId)) {
                $q->where('company_id', $companyId);
            }
            if ($hasPersonalOnly && $loginUserId) {
                $q->where('employee_id', $loginUserId);
            }
        };

        // Handle Absent Status filter
        if (isset($this->filter_params->attendance_status) && $this->filter_params->attendance_status === 'absent') {
            if (!$fromDate) {
                $fromDate = Carbon::today()->startOfDay();
                $toDate = Carbon::today()->endOfDay();
            }

            $presentQuery = Attendance::whereBetween('attendance_date', [$fromDate->format('Y-m-d'), $toDate->format('Y-m-d')])
                ->where($companyConstraint);
            if (!empty($this->filter_params->filter_company)) {
                $presentQuery->where('company_id', $this->filter_params->filter_company);
            }
            $presentEmployeeIds = $presentQuery->pluck('employee_id')->toArray();

            $leaveQuery = LeaveApplication::where('status', 'approved')
                ->where(function ($q) use ($fromDate, $toDate) {
                    $q->where(function ($sub) use ($fromDate, $toDate) {
                        $sub->whereNotNull('todate_time')
                            ->whereDate('fromdate_time', '<=', $toDate)
                            ->whereDate('todate_time', '>=', $fromDate);
                    })->orWhere(function ($sub) use ($fromDate, $toDate) {
                        $sub->whereNull('todate_time')
                            ->whereDate('fromdate_time', '>=', $fromDate)
                            ->whereDate('fromdate_time', '<=', $toDate);
                    });
                });

            if (!empty($this->filter_params->filter_company)) {
                $leaveQuery->where('company_id', $this->filter_params->filter_company);
            }
            $leaveEmployeeIds = $leaveQuery->pluck('employee_id')->toArray();

            $dataQuery = Employee::with(['company', 'employmentDetail.shiftDetail'])
                ->where('status', 'active')
                ->whereNotIn('id', array_merge($presentEmployeeIds, $leaveEmployeeIds));

            if (!empty($companyId)) {
                $dataQuery->where('company_id', $companyId);
            }
            if ($hasPersonalOnly && $loginUserId) {
                $dataQuery->where('id', $loginUserId);
            }
            if (!empty($this->filter_params->filter_company)) {
                $dataQuery->where('company_id', $this->filter_params->filter_company);
            }
            if (!empty($this->filter_params->filter_employee)) {
                $dataQuery->where('id', $this->filter_params->filter_employee);
            }

            return $dataQuery->get();
        }

        // Standard Attendance query
        $query = Attendance::withTrashed()
            ->with(['company', 'shift', 'employee', 'creator'])
            ->where($companyConstraint)
            ->orderBy('attendance_date', 'DESC')
            ->orderBy('id', 'DESC');

        if (!empty($this->filter_params->filter_company)) {
            $query->where('company_id', $this->filter_params->filter_company);
        }
        if (!empty($this->filter_params->filter_employee)) {
            $query->where('employee_id', $this->filter_params->filter_employee);
        }
        if (!empty($this->filter_params->filter_shift)) {
            $query->where('shift_id', $this->filter_params->filter_shift);
        }
        if (!empty($this->filter_params->attendace_type) && $this->filter_params->attendace_type !== 'all') {
            $query->where('attendace_type', $this->filter_params->attendace_type);
        }
        if (!empty($this->filter_params->records_source) && $this->filter_params->records_source !== 'all') {
            $query->where('records_source', $this->filter_params->records_source);
        }

        // Status specific filters (late, early, present)
        if (!empty($this->filter_params->attendance_status)) {
            $status = $this->filter_params->attendance_status;
            if ($status === 'late') {
                $query->where('attendace_type', 'in')
                    ->whereExists(function ($q) {
                        $q->select(DB::raw(1))
                            ->from('employees')
                            ->join('employment_details', 'employees.id', '=', 'employment_details.employee_id')
                            ->join('shifts', 'employment_details.shift', '=', 'shifts.id')
                            ->whereColumn('attendances.employee_id', '=', 'employees.id')
                            ->whereRaw("TIME(attendances.punch_in_time) > ADDTIME(shifts.punch_in_minimum, SEC_TO_TIME(IFNULL(shifts.in_out_grace_period, 0) * 60))");
                    });
            } elseif ($status === 'early') {
                $query->where('attendace_type', 'out')
                    ->whereExists(function ($q) {
                        $q->select(DB::raw(1))
                            ->from('employees')
                            ->join('employment_details', 'employees.id', '=', 'employment_details.employee_id')
                            ->join('shifts', 'employment_details.shift', '=', 'shifts.id')
                            ->whereColumn('attendances.employee_id', '=', 'employees.id')
                            ->whereRaw("TIME(attendances.punch_in_time) < SUBTIME(shifts.punch_out, SEC_TO_TIME(IFNULL(shifts.in_out_grace_period, 0) * 60))");
                    });
            } elseif ($status === 'present') {
                $fDate = $fromDate ?? Carbon::today()->startOfDay();
                $tDate = $toDate ?? Carbon::today()->endOfDay();
                $query->whereIn('attendances.id', function ($q) use ($fDate, $tDate, $companyId, $hasPersonalOnly, $loginUserId) {
                    $q->select(DB::raw('MIN(id)'))
                        ->from('attendances')
                        ->whereBetween('attendance_date', [$fDate->format('Y-m-d'), $tDate->format('Y-m-d')]);
                    if ($companyId) {
                        $q->where('company_id', $companyId);
                    }
                    if ($hasPersonalOnly && $loginUserId) {
                        $q->where('employee_id', $loginUserId);
                    }
                    $q->groupBy('employee_id');
                });
            }
        }

        // Date range
        if ($fromDate && $toDate) {
            $tableColumn = (new Attendance())->getTable() . '.attendance_date';
            $query->whereBetween($tableColumn, [$fromDate->format('Y-m-d'), $toDate->format('Y-m-d')]);
        }

        // Search text
        if (!empty($this->filter_params->search)) {
            $search = $this->filter_params->search;
            $query->where(function ($q) use ($search) {
                $q->where('remark', 'like', "%{$search}%")
                    ->orWhere('records_source', 'like', "%{$search}%")
                    ->orWhereHas('employee', function ($eq) use ($search) {
                        $eq->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('full_name', 'like', "%{$search}%")
                            ->orWhere('employee_code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('shift', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
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
            'Employee Code',
            'Employee Name',
            'Shift',
            'Attendance Date',
            'Punch Time',
            'Punch Type',
            'Source',
            'Entry By',
            'Remark'
        ]);

        return $headings;
    }

    public function map($row): array
    {
        $companyId = $this->modules['company_id'] ?? $this->user?->company_id ?? null;

        // If absentee Employee instance
        if ($row instanceof Employee) {
            $rowData = [$this->srNo++];
            if (empty($companyId)) {
                $rowData[] = $row->company?->company_name ?? '-';
            }
            $rowData[] = $row->employee_code ?? '-';
            $rowData[] = $row->proper_name ?? $row->full_name ?? '-';
            $rowData[] = $row->employmentDetail?->shiftDetail?->name ?? '-';
            $rowData[] = Carbon::today()->format('d-m-Y');
            $rowData[] = '-';
            $rowData[] = 'ABSENT';
            $rowData[] = 'System';
            $rowData[] = '-';
            $rowData[] = 'Absent on date';

            return $rowData;
        }

        // Attendance Record
        $rowData = [$this->srNo++];
        if (empty($companyId)) {
            $rowData[] = $row->company?->company_name ?? '-';
        }

        $empName = $row->employee?->proper_name ?? $row->employee?->full_name ?? ($row->employee ? trim(($row->employee->first_name ?? '') . ' ' . ($row->employee->last_name ?? '')) : '-');
        $shiftName = $row->shift?->name ?? '-';

        $attDate = '-';
        if (!empty($row->attendance_date)) {
            try {
                $attDate = Carbon::parse($row->attendance_date)->format('d-m-Y');
            } catch (\Exception $e) {
                $attDate = $row->attendance_date;
            }
        }

        $punchTime = '-';
        if (!empty($row->punch_in_time)) {
            try {
                $punchTime = Carbon::parse($row->punch_in_time)->format('h:i:s A');
            } catch (\Exception $e) {
                $punchTime = $row->punch_in_time;
            }
        }

        $type = strtoupper($row->attendace_type ?? '-');
        $source = ucfirst($row->records_source ?? 'Manual');
        $creator = $row->creator?->proper_name ?? $row->creator?->first_name ?? '-';
        $remark = $row->remark ?? '-';

        $rowData[] = $row->employee?->employee_code ?? '-';
        $rowData[] = $empName;
        $rowData[] = $shiftName;
        $rowData[] = $attDate;
        $rowData[] = $punchTime;
        $rowData[] = $type;
        $rowData[] = $source;
        $rowData[] = $creator;
        $rowData[] = $remark;

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
        return 'Attendance List';
    }
}
