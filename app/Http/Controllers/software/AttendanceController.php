<?php

namespace App\Http\Controllers\software;

use App\Exports\AttendanceExport;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceRequest;
use App\Imports\AttendanceImport;
use App\Jobs\ProcessAttendanceImport;
use App\Models\Attendance;
use App\Models\AttendanceImportFile;
use App\Models\Company;
use App\Models\BiometricMachine;
use App\Models\Employee;
use App\Models\LeaveApplication;
use App\Models\Shift;
use App\Services\Biometric\BiometricServiceFactory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceController extends Controller
{
    public $modules = [];
    public function __construct()
    {
        parent::__construct();

        $this->modules = [
            'title' => 'Attendance',
            'folder_path' => 'software.modules.attendance',
            'route' => 'attendance',
            'table_name' => (new Attendance())->getTable(),
            'permisstion_prefix' => 'attendance',
            'module_name' => 'Attendance',
            'company_id' => ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null,
        ];
        View::share("AttendaceType", Attendance::$AttendaceType);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $modules = $this->modules;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;
        $modules['parent_type_id'] = ($this->authenticateLoginUserDetails?->parent_type_id) ? $this->authenticateLoginUserDetails?->parent_type_id : null;
        $loginUserId = ($modules['authLoginUserDetail'] && $modules['authLoginUserDetail']?->id) ? $modules['authLoginUserDetail']?->id : null;
        if (count(config('constants.permissions'))) {
            foreach (config('constants.permissions') as $key => $value) {
                $modules[$value . '_permission'] = (isset($modules['company_id']) && !$modules['company_id']) ? true : Gate::check('hasPermission', [$value, $modules['module_name']]);
            }
        }
        if (!$modules['view_permission']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('Unauthorized', [], [], 403);
            }
            abort(403, 'Unauthorized');
        }
        View::share('modules', $modules);
        // dd("LN-60",$modules);
        try {

            $columns = [
                (object) ['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'td_label' => 'Sr No.', 'className' => 'w-10 text-start', 'orderable' => false, 'searchable' => false],
                (object) ['data' => "company.company_name", 'name' => 'company_id', 'td_label' => 'Company Name', 'className' => 'w-25 text-wrap'],
                // (object)['data' => "employee.middle_name", 'name' => 'employee_id', 'td_label' => 'Employee Name', 'className' => 'w-10 text-start'],
                (object) ['data' => "employee_name", 'name' => 'employee_id', 'td_label' => 'Employee Name', 'className' => 'w-25 text-wrap'],
                (object) ['data' => "shift.name", 'name' => 'shift_id', 'td_label' => 'Shift', 'className' => 'text-start'],
                (object) ['data' => "attendance_date", 'name' => 'attendance_date', 'td_label' => 'Attendance Date', 'className' => 'text-start'],
                // (object)['data' => "create_date", 'name' => 'create_date', 'td_label' => 'Create Date', 'className' => 'text-start'],
                // (object)['data' => "punch_in_time", 'name' => 'punch_in_time', 'td_label' => 'Punch In/Out Time', 'className' => 'text-start'],
                (object) ['data' => "show_punch", 'name' => 'punch_in_time', 'td_label' => 'Show Punch In/Out', 'className' => 'text-start', 'orderable' => false, 'searchable' => false],
                // (object) ['data' => "records_source", 'name' => 'records_source', 'td_label' => 'Source', 'className' => 'text-start'],
                (object) ['data' => "entry_by", 'name' => 'created_by', 'td_label' => 'Entry By', 'className' => 'text-start'],
                (object) ['data' => "action", 'name' => 'action', 'td_label' => 'Action', 'orderable' => false, 'searchable' => false, 'className' => 'w-10 text-start'],
            ];
            if ($modules['company_id']) {
                // array_unshift($columns, (object)['data' => "company_name", 'name' => 'company_name', 'td_label' => 'Company Name', 'className' =>  '']);
                $columns = array_filter($columns, function ($col) {
                    return $col->name !== 'company_id';
                });


                $columns = array_values($columns);
            }
            // dd(!$modules['company_id'], $columns);
            View::share("columns", $columns);
            //   dd("LN-60",$columns);


            if ($request->ajax() || $request->has('draw')) {
                //   dd("LN-92",$request->all());

                file_put_contents(storage_path('logs/debug_request.txt'), print_r($request->all(), true), FILE_APPEND);
                file_put_contents(storage_path('logs/debug_request.txt'), "Auth User Company: " . (Auth::guard('employees')->check() ? Auth::guard('employees')->user()->company_id : 'Admin') . PHP_EOL, FILE_APPEND);

                $fromDate = null;
                $toDate = null;
                if ($request->filled('filter_date')) {
                    if (strpos($request->filter_date, ' to ') !== false) {
                        $dates = explode(' to ', $request->filter_date);
                    } else {
                        $dates = [$request->filter_date, $request->filter_date];
                    }
                    try {
                        $fromDate = Carbon::createFromFormat('d/m/Y', trim($dates[0]))->startOfDay();
                        $toDate = Carbon::createFromFormat('d/m/Y', trim($dates[1]))->endOfDay();
                    } catch (\Exception $e) {
                        // fallback
                    }
                }
                if (!$fromDate) {
                    $fromDate = Carbon::today()->startOfDay();
                    $toDate = Carbon::today()->endOfDay();
                }

                $companyConstraint = function ($q1) use ($modules) {
                    if (Auth::guard('employees')->check() || !empty($modules['company_id'])) {
                        $companyId = $modules['company_id'] ?? Auth::guard('employees')->user()->company_id;
                        $q1->where('company_id', $companyId);
                    }
                };

                if ($request->filled('attendance_status') && $request->attendance_status == 'absent') {
                    // Get IDs of employees who have attendance for the filtered date range
                    $presentQuery = Attendance::whereBetween('attendance_date', [$fromDate->format('Y-m-d'), $toDate->format('Y-m-d')])
                        ->where($companyConstraint);
                    if ($request->filled('filter_company')) {
                        $presentQuery->where('company_id', $request->filter_company);
                    }
                    $presentEmployeeIds = $presentQuery->pluck('employee_id')->toArray();

                    // Get approved leaves for the filtered date range (overlap condition)
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
                    $contractTypeIds = \App\Models\EmployeeType::where('name', 'like', '%contract%')->pluck('id');
                    if ($contractTypeIds->isNotEmpty()) {
                        $leaveQuery->whereDoesntHave('employee.employmentDetail', function ($q) use ($contractTypeIds) {
                            $q->whereIn('employment_type', $contractTypeIds);
                        });
                    }
                    if ($request->filled('filter_company')) {
                        $leaveQuery->where('company_id', $request->filter_company);
                    }
                    $leaveEmployeeIds = $leaveQuery->pluck('employee_id')->toArray();

                    $data = Employee::with(['company', 'employmentDetail.shiftDetail'])
                        ->where('status', 'active')
                        ->whereNotIn('id', array_merge($presentEmployeeIds, $leaveEmployeeIds))
                        ->where(function ($q1) use ($modules) {
                            if (Auth::guard('employees')->check() || !empty($modules['company_id'])) {
                                $companyId = $modules['company_id'] ?? Auth::guard('employees')->user()->company_id;
                                $q1->where('company_id', $companyId);
                            }
                        });

                    if ($request->filled('filter_company')) {
                        $data->where('company_id', $request->filter_company);
                    }
                    if ($request->filled('filter_employee')) {
                        $data->where('id', $request->filter_employee);
                    }
                } else {
                    $data = Attendance::with(['company', 'employee', 'shift', 'creator'])
                        ->where($companyConstraint)
                        ->orderBy('created_at', 'DESC')  // New records first
                        ->orderBy('attendance_date', 'DESC');  // Then by attendance date

                    if ($request->filled('attendance_status') && $request->attendance_status == 'late') {
                        $data->where('attendace_type', 'in')
                            ->whereExists(function ($q) {
                                $q->select(DB::raw(1))
                                    ->from('employees')
                                    ->join('employment_details', 'employees.id', '=', 'employment_details.employee_id')
                                    ->join('shifts', 'employment_details.shift', '=', 'shifts.id')
                                    ->whereColumn('attendances.employee_id', '=', 'employees.id')
                                    ->whereRaw("TIME(attendances.punch_in_time) > ADDTIME(shifts.punch_in_minimum, SEC_TO_TIME(IFNULL(shifts.in_out_grace_period, 0) * 60))");
                            });
                    } else if ($request->filled('attendance_status') && $request->attendance_status == 'early') {
                        $data->where('attendace_type', 'out')
                            ->whereExists(function ($q) {
                                $q->select(DB::raw(1))
                                    ->from('employees')
                                    ->join('employment_details', 'employees.id', '=', 'employment_details.employee_id')
                                    ->join('shifts', 'employment_details.shift', '=', 'shifts.id')
                                    ->whereColumn('attendances.employee_id', '=', 'employees.id')
                                    ->whereRaw("TIME(attendances.punch_in_time) < SUBTIME(shifts.punch_out, SEC_TO_TIME(IFNULL(shifts.in_out_grace_period, 0) * 60))");
                            });
                    } else if ($request->filled('attendance_status') && $request->attendance_status == 'present') {
                        $data->whereIn('attendances.id', function ($q) use ($fromDate, $toDate, $modules, $request) {
                            $q->select(DB::raw('MIN(id)'))
                                ->from('attendances')
                                ->whereBetween('attendance_date', [$fromDate->format('Y-m-d'), $toDate->format('Y-m-d')]);

                            if (Auth::guard('employees')->check() || !empty($modules['company_id'])) {
                                $companyId = $modules['company_id'] ?? Auth::guard('employees')->user()->company_id;
                                $q->where('company_id', $companyId);
                            }
                            if ($request->filled('filter_company')) {
                                $q->where('company_id', $request->filter_company);
                            }
                            $q->groupBy('employee_id');
                        });
                    }
                }

                if (!empty($modules['restore_permission']) && !($request->filled('attendance_status') && $request->attendance_status == 'absent')) {
                    $data = $data->withTrashed();
                }

                $dataTable = Datatables::of($data)
                    ->addIndexColumn()
                    ->filter(function ($query) use ($request) {
                        if ($request->filled('attendance_status') && $request->attendance_status == 'absent') {
                            // Employee query is already fully built and filtered above, no standard attendance filters needed
                            return;
                        }

                        if ($request->has('filter_company') && $request->filter_company) {
                            $query->where('company_id', $request->filter_company);
                        }

                        if ($request->has('filter_employee') && $request->filter_employee) {
                            $query->where('employee_id', $request->filter_employee);
                        }
                        if ($request->has('filter_shift') && $request->filter_shift) {
                            $query->where('shift_id', $request->filter_shift);
                        }
                        if ($request->filled('attendace_type') && $request->attendace_type !== 'all') {
                            $query->where('attendace_type', $request->attendace_type);
                        }

                        if ($request->filled('records_source') && $request->records_source !== 'all') {
                            $query->where('records_source', $request->records_source);
                        }

                        // Date filter
                        if ($request->filled('filter_date')) {
                            if (strpos($request->filter_date, ' to ') !== false) {
                                $dates = explode(' to ', $request->filter_date);
                            } else {
                                $dates = [$request->filter_date, $request->filter_date];
                            }

                            try {
                                $fromDate = Carbon::createFromFormat('d/m/Y', trim($dates[0]))->startOfDay();
                                $toDate = Carbon::createFromFormat('d/m/Y', trim($dates[1]))->endOfDay();
                            } catch (\Exception $e) {
                                $fromDate = $toDate = null; // fallback if parsing fails
                            }

                            $tableColumn = (new Attendance())->getTable() . '.attendance_date';

                            if ($fromDate && $toDate) {
                                $query->whereBetween($tableColumn, [$fromDate->format('Y-m-d'), $toDate->format('Y-m-d')]);
                            } elseif ($fromDate) {
                                $query->whereDate($tableColumn, $fromDate->format('Y-m-d'));
                            }
                        }
                    })
                    ->addColumn('company.company_name', function ($row) {
                        if ($row instanceof Employee) {
                            return $row->company?->company_name ?? '-';
                        }
                        return $row->company?->company_name ?? '-';
                    })
                    ->addColumn('shift.name', function ($row) {
                        if ($row instanceof Employee) {
                            return $row->employmentDetail?->shiftDetail?->name ?? '-';
                        }
                        return $row->shift?->name ?? '-';
                    })
                    ->addColumn('employee_name', function ($row) {
                        if ($row instanceof Employee) {
                            return $row->employee_code . ' - ' . $row->proper_name;
                        }
                        if ($row->employee) {
                            return $row->employee->employee_code . ' - ' . $row->employee->proper_name;
                        }
                        return '-';
                    })

                    ->editColumn('attendance_date', function ($row) use ($fromDate) {
                        if ($row instanceof Employee) {
                            return $fromDate ? $fromDate->format('d/m/Y') : '-';
                        }
                        return $row->attendance_date ? \Carbon\Carbon::parse($row->attendance_date)->format('d/m/Y') : '-';
                    })
                    ->editColumn('create_date', function ($row) {
                        if ($row instanceof Employee) {
                            return '-';
                        }
                        return $row->create_date ? \Carbon\Carbon::parse($row->create_date)->format('d/m/Y') : '-';
                    })
                    ->editColumn('records_source', function ($row) {
                        if ($row instanceof Employee) {
                            return '-';
                        }
                        if ($row->records_source === 'old_crm') {
                            return '<span class="badge bg-primary">Old CRM</span>';
                        } elseif ($row->records_source === 'manually') {
                            return '<span class="badge bg-secondary">Manual</span>';
                        } elseif ($row->records_source === 'biometric') {
                            return '<span class="badge bg-info">Biometric</span>';
                        }
                        return $row->records_source ? ucfirst($row->records_source) : '-';
                    })
                    ->addColumn('entry_by', function ($row) {
                        if ($row instanceof Employee) {
                            return '-';
                        }
                        if ($row->records_source === 'manually') {
                            return $row->creator?->proper_name ?? $row->creator?->full_name ?? '-';
                        }
                        return '-';
                    })
                    ->addColumn('show_punch', function ($row) {
                        if ($row instanceof Employee) {
                            return '<span class="badge bg-danger">Absent</span>';
                        }
                        $show_punch = '';
                        if ($row->punch_in_time) {
                            $show_punch .= \Carbon\Carbon::parse($row->punch_in_time)->format('h:i A');
                        }
                        if ($row->attendace_type) {
                            $type = strtolower(trim($row->attendace_type));
                            if ($type == 'in') {
                                $show_punch .= ' <span class="badge bg-success">In</span>';
                            } elseif ($type == 'out') {
                                $show_punch .= ' <span class="badge bg-warning">Out</span>';
                            } else {
                                $show_punch .= ' <span class="badge bg-secondary">' . ucfirst($row->attendace_type) . '</span>';
                            }
                        }
                        return $show_punch;
                    })
                    ->addColumn('action', function ($row) use ($modules) {
                        if ($row instanceof Employee) {
                            return '-';
                        }
                        $btn = '';
                        $isManual = false;
                        if ($row?->records_source == 'manually') {
                            $isManual = true;
                        }

                        if (!$row?->deleted_at) {
                            if ($modules['update_permission']) {
                                $btn .= '<a href="' . route($modules["route"] . ".edit", [$row["id"]]) . '" class="btn btn-light btn-icon mx-1"><i class="fa-solid fa-pen-to-square"></i></a>';
                            }

                            if ($modules['delete_permission'] && $isManual) {
                                $btn .= '<a href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . route($modules["route"] . ".destroy", [$row["id"]]) . '" class="btn btn-danger btn-icon deletebutton mx-1"><i class="fa-solid fa-trash"></i></a>';
                            }
                        } else {
                            $btn .= '<a href="javascript:void(0)" data-restore="' . route($modules["route"] . ".restore", ['id' => $row["id"]]) . '" class="btn btn-light mx-1 record-restore"><i class="ti ti-history"></i> Restore</a>';
                        }
                        return $btn ?: '-';
                    });

                if ($request->filled('attendance_status') && $request->attendance_status == 'absent') {
                    $dataTable->order(function ($query) {
                        $query->orderBy('employees.id', 'DESC');
                    });
                }

                $returnData = $dataTable->rawColumns(['show_punch', 'status', 'action', 'records_source'])
                    ->make(true);

                // Log Query
                $queries = DB::getQueryLog();
                file_put_contents(storage_path('logs/debug_sql.txt'), "Full Query Log: " . print_r($queries, true), FILE_APPEND);

                return $returnData;
            }

            // Check if company has active pull-based biometric machine
            $activeBiometricMachine = null;
            if ($modules['company_id']) {
                $activeBiometricMachine = BiometricMachine::query()
                    ->where('company_id', $modules['company_id'])
                    ->where('is_active', true)
                    ->pullBased()
                    ->whereNotNull('api_url')
                    ->first();
            }

            View::share('activeBiometricMachine', $activeBiometricMachine);

            return view($modules['folder_path'] . '.index');
        } catch (\Exception $e) {
            Log::error("Attendance Index Error: " . $e->getMessage());
            if (isset($request) && ($request->ajax() || $request->wantsJson())) {
                return response()->json([
                    'error' => 'Server Error: ' . $e->getMessage()
                ], 500);
            }
            return Redirect::route('software.dashboard')->withErrors($e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;
        $modules['parent_type_id'] = ($this->authenticateLoginUserDetails?->parent_type_id) ? $this->authenticateLoginUserDetails?->parent_type_id : null;
        $loginUserId = ($modules['authLoginUserDetail'] && $modules['authLoginUserDetail']?->id) ? $modules['authLoginUserDetail']?->id : null;
        if (count(config('constants.permissions'))) {
            foreach (config('constants.permissions') as $key => $value) {
                $modules[$value . '_permission'] = (isset($modules['company_id']) && !$modules['company_id']) ? true : Gate::check('hasPermission', [$value, $modules['module_name']]);
            }
        }
        if (!$modules['add_permission']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('Unauthorized', [], [], 403);
            }
            abort(403, 'Unauthorized');
        }
        try {


            View::share('modules', $modules);

            return view($modules['folder_path'] . '.form');
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AttendanceRequest $request)
    {
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;
        $modules['parent_type_id'] = ($this->authenticateLoginUserDetails?->parent_type_id) ? $this->authenticateLoginUserDetails?->parent_type_id : null;
        $loginUserId = ($modules['authLoginUserDetail'] && $modules['authLoginUserDetail']?->id) ? $modules['authLoginUserDetail']?->id : null;
        if (count(config('constants.permissions'))) {
            foreach (config('constants.permissions') as $key => $value) {
                $modules[$value . '_permission'] = (isset($modules['company_id']) && !$modules['company_id']) ? true : Gate::check('hasPermission', [$value, $modules['module_name']]);
            }
        }
        if (!$modules['add_permission']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('Unauthorized', [], [], 403);
            }
            abort(403, 'Unauthorized');
        }

        View::share('modules', $modules);
        // return $request->all();
        $validated = $request->all();

        try {
            // return $this->authenticateLoginUserDetails;

            $validated['created_by'] = $loginUserId;
            // Set records_source to 'manually' for manual entries
            if (!isset($validated['records_source'])) {
                $validated['records_source'] = 'manually';
            }
            // Store request data if not already set
            if (!isset($validated['requested_data'])) {
                $validated['requested_data'] = json_encode($request->all());
            }
            // return $validated;
            Attendance::create($validated);

            if ($request->ajax()) {
                session()->flash('success', $modules['title'] . ' create successfully');
                return response()->json(['success' => true, 'message' => $modules['title'] . ' create successfully']);
            }

            return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' create successfully');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;
        $modules['parent_type_id'] = ($this->authenticateLoginUserDetails?->parent_type_id) ? $this->authenticateLoginUserDetails?->parent_type_id : null;
        $loginUserId = ($modules['authLoginUserDetail'] && $modules['authLoginUserDetail']?->id) ? $modules['authLoginUserDetail']?->id : null;
        if (count(config('constants.permissions'))) {
            foreach (config('constants.permissions') as $key => $value) {
                $modules[$value . '_permission'] = (isset($modules['company_id']) && !$modules['company_id']) ? true : Gate::check('hasPermission', [$value, $modules['module_name']]);
            }
        }
        if (!$modules['update_permission']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('Unauthorized', [], [], 403);
            }
            abort(403, 'Unauthorized');
        }
        try {

            View::share('modules', $modules);



            $attQuery = Attendance::query();
            if (!empty($modules['company_id'])) {
                $attQuery->where('company_id', $modules['company_id']);
            }
            $edit = $attQuery->findOrFail($id);
            View::share('edit', $edit);

            return view($modules['folder_path'] . '.form');
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AttendanceRequest $request, string $id)
    {
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;
        $modules['parent_type_id'] = ($this->authenticateLoginUserDetails?->parent_type_id) ? $this->authenticateLoginUserDetails?->parent_type_id : null;
        $loginUserId = ($modules['authLoginUserDetail'] && $modules['authLoginUserDetail']?->id) ? $modules['authLoginUserDetail']?->id : null;
        if (count(config('constants.permissions'))) {
            foreach (config('constants.permissions') as $key => $value) {
                $modules[$value . '_permission'] = (isset($modules['company_id']) && !$modules['company_id']) ? true : Gate::check('hasPermission', [$value, $modules['module_name']]);
            }
        }
        if (!$modules['update_permission']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('Unauthorized', [], [], 403);
            }
            abort(403, 'Unauthorized');
        }
        View::share('modules', $modules);

        $validated = $request->all();
        try {

            $validated['updated_by'] = $loginUserId;

            // If records_source is not provided and this is a manual update, set it to 'manually'
            if (!isset($validated['records_source'])) {
                $validated['records_source'] = 'manually';
            }
            // Store request data if not already set
            if (!isset($validated['requested_data'])) {
                $validated['requested_data'] = json_encode($request->all());
            }

            // return $validated;
            $attQuery = Attendance::query();
            if (!empty($modules['company_id'])) {
                $attQuery->where('company_id', $modules['company_id']);
            }
            $updateData = $attQuery->findOrFail($id);
            if ($updateData) {
                unset($validated['id']);
                $updateData->update($validated);

                return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' update successfully');
            }
            return Redirect::back()->withErrors('something went wrong please try again later')->withInput();
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id, Request $request)
    {
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;
        $modules['parent_type_id'] = ($this->authenticateLoginUserDetails?->parent_type_id) ? $this->authenticateLoginUserDetails?->parent_type_id : null;
        $loginUserId = ($modules['authLoginUserDetail'] && $modules['authLoginUserDetail']?->id) ? $modules['authLoginUserDetail']?->id : null;
        if (count(config('constants.permissions'))) {
            foreach (config('constants.permissions') as $key => $value) {
                $modules[$value . '_permission'] = (isset($modules['company_id']) && !$modules['company_id']) ? true : Gate::check('hasPermission', [$value, $modules['module_name']]);
            }
        }
        if (!$modules['delete_permission']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('Unauthorized', [], [], 403);
            }
            abort(403, 'Unauthorized');
        }

        $attQuery = Attendance::query();
        if (!empty($modules['company_id'])) {
            $attQuery->where('company_id', $modules['company_id']);
        }
        $dataDelete = $attQuery->findOrFail($id);
        $isAjax = ($request->ajax()) ? true : false;
        try {
            if ($dataDelete) {

                $validated['deleted_by'] = $loginUserId;
                $dataDelete->update($validated);

                if ($dataDelete->delete()) {

                    if ($isAjax) {
                        return $this->sendResponse([], $modules['title'] . ' delete successfully');
                    }
                    return true;
                }
            }
            if ($isAjax) {
                return $this->sendResponse([], "something went wrong please try again later");
            }
            return false;
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    public function restore($id)
    {

        $modules = $this->modules;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;
        $modules['parent_type_id'] = ($this->authenticateLoginUserDetails?->parent_type_id) ? $this->authenticateLoginUserDetails?->parent_type_id : null;
        $loginUserId = ($modules['authLoginUserDetail'] && $modules['authLoginUserDetail']?->id) ? $modules['authLoginUserDetail']?->id : null;
        if (count(config('constants.permissions'))) {
            foreach (config('constants.permissions') as $key => $value) {
                $modules[$value . '_permission'] = (isset($modules['company_id']) && !$modules['company_id']) ? true : Gate::check('hasPermission', [$value, $modules['module_name']]);
            }
        }
        if (!$modules['restore_permission']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('Unauthorized', [], [], 403);
            }
            abort(403, 'Unauthorized');
        }


        try {
            $attQuery = Attendance::withTrashed();
            if (!empty($modules['company_id'])) {
                $attQuery->where('company_id', $modules['company_id']);
            }
            $restore_data = $attQuery->findOrFail($id);
            $restore_data->restore();

            return Redirect::back()->withSuccess($modules['title'] . ' restored successfully!');
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }
    public function status_update(Request $request)
    {
        $isAjax = ($request->ajax()) ? true : false;

        $modules = $this->modules;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;
        $modules['parent_type_id'] = ($this->authenticateLoginUserDetails?->parent_type_id) ? $this->authenticateLoginUserDetails?->parent_type_id : null;
        $loginUserId = ($modules['authLoginUserDetail'] && $modules['authLoginUserDetail']?->id) ? $modules['authLoginUserDetail']?->id : null;
        if (count(config('constants.permissions'))) {
            foreach (config('constants.permissions') as $key => $value) {
                $modules[$value . '_permission'] = (isset($modules['company_id']) && !$modules['company_id']) ? true : Gate::check('hasPermission', [$value, $modules['module_name']]);
            }
        }
        if (!$modules['update_permission']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('Unauthorized', [], [], 403);
            }
            abort(403, 'Unauthorized');
        }

        $validator = Validator::make($request->all(), [
            'id' => ['required', Rule::exists($modules['table_name'], 'id')],
            'update_status' => ['required', 'in:active,inactive']
        ]);

        if ($validator->fails() && $isAjax) {
            return $this->sendError($validator->messages()->first(), $validator->messages(), [], 401);
        }


        try {
            $attQuery = Attendance::withTrashed();
            if (!empty($modules['company_id'])) {
                $attQuery->where('company_id', $modules['company_id']);
            }
            $country = $attQuery->findOrFail($request?->id);
            if ($country) {
                $country->status = $request->update_status;
                $country->save();
                if ($isAjax) {
                    return $this->sendResponse($country, $modules['title'] . ' status update successfully.');
                }
                return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' status update successfully.');
            }
            if ($isAjax) {
                return $this->sendError('something went wrong please try again later');
            }
            return Redirect::back()->withErrors('something went wrong please try again later')->withInput();
        } catch (\Exception $e) {
            if ($isAjax) {
                return $this->sendError($e->getMessage(), $e->getMessage(), [], 401);
            }
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }
    public function print(Request $request)
    {
        $modules = $this->modules;

        $authUser = $this->authenticateLoginUserDetails;
        $modules['authLoginUserDetail'] = $authUser;
        $modules['company_id'] = $authUser?->company_id ?? null;
        $company_id = $modules['company_id'];
        $loginUserId = $authUser?->id ?? null;

        if (count(config('constants.permissions'))) {
            foreach (config('constants.permissions') as $key => $value) {
                $modules[$value . '_permission'] = (isset($modules['company_id']) && !$modules['company_id'])
                    ? true
                    : Gate::check('hasPermission', [$value, $modules['module_name']]);
            }
        }
        // Unauthorized check
        if ($request->ajax()) {
            if (!$modules['view_permission']) {
                return $this->sendError('Unauthorized', [], [], 403);
            }
        } elseif (!$modules['view_permission']) {
            abort(403, 'Unauthorized');
        }

        try {
            // Permissions
            $moduleName = $modules['module_name'];

            $query = Attendance::withTrashed()
                ->with(['company'])
                ->orderBy('id', 'DESC');

            // Employee guard-based access
            $query->where(function ($q1) use ($modules, $loginUserId) {
                if (Auth::guard('employees')->check() || !empty($modules['company_id'])) {
                    // If company_id exists in $modules, use that; otherwise use employee's company_id
                    $companyId = $modules['company_id'] ?? Auth::guard('employees')->user()->company_id;

                    $q1->where($modules['table_name'] . '.company_id', $companyId);

                    // Personal data permission rule
                    if (!empty($modules['personal_data_permission']) && ($modules['all_data_permission'] == false)) {
                        $q1->where($modules['table_name'] . '.created_by', $loginUserId);
                    }
                }
            });

            if ($request->filled('filter_company')) {
                $query->where('company_id', $request->filter_company);
            }
            if ($request->filled('filter_employee')) {
                $query->where('employee_id', $request->filter_employee);
            }
            if ($request->filled('filter_shift')) {
                $query->where('shift_id', $request->filter_shift);
            }
            if ($request->filled('attendace_type') && $request->attendace_type !== 'all') {
                $query->where('attendace_type', $request->attendace_type);
            }
            if ($request->filled('employee_date')) {
                $dates = explode(' to ', str_replace('-', '/', $request->employee_date));

                $tableColumn = (new Attendance())->getTable() . '.attendance_date';

                // Handle range
                if (count($dates) === 2) {
                    $from = Carbon::createFromFormat('d/m/Y', trim($dates[0]))->startOfDay()->format('Y-m-d H:i:s');
                    $to = Carbon::createFromFormat('d/m/Y', trim($dates[1]))->endOfDay()->format('Y-m-d H:i:s');

                    $query->whereBetween($tableColumn, [$from, $to]);
                }
                // Handle single date
                elseif (count($dates) === 1 && !empty($dates[0])) {
                    $singleDate = Carbon::createFromFormat('d/m/Y', trim($dates[0]))->format('Y-m-d');

                    $query->whereDate($tableColumn, $singleDate);
                }
            }


            // dd("LN-921", $request->all(), $query);
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('leave_reason', 'like', "%{$search}%")
                        ->orWhere('rejection_reason', 'like', "%{$search}%");
                });
            }

            $attendance = $query->get();

            return view($modules['folder_path'] . '.print', compact('attendance', 'company_id', 'modules'));
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }
    public function exportExcel(Request $request)
    {
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;
        $modules['parent_type_id'] = ($this->authenticateLoginUserDetails?->parent_type_id) ? $this->authenticateLoginUserDetails?->parent_type_id : null;
        $loginUserId = ($modules['authLoginUserDetail'] && $modules['authLoginUserDetail']?->id) ? $modules['authLoginUserDetail']?->id : null;
        if (count(config('constants.permissions'))) {
            foreach (config('constants.permissions') as $key => $value) {
                $modules[$value . '_permission'] = (isset($modules['company_id']) && !$modules['company_id']) ? true : Gate::check('hasPermission', [$value, $modules['module_name']]);
            }
        }
        if (!$modules['excel_permission']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('Unauthorized', [], [], 403);
            }
            abort(403, 'Unauthorized');
        }

        return Excel::download(new AttendanceExport($request->all(), $this->authenticateLoginUserDetails, $modules), 'Attendance-' . Helper::convert_date("", "Y-m-d H:i:s", "Ymd-His") . '.xlsx');
    }

    /**
     * Display the attendance import form
     */
    public function import()
    {
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = $this->authenticateLoginUserDetails ?? null;
        $modules['company_id'] = $modules['authLoginUserDetail']?->company_id ?? null;
        $modules['parent_type_id'] = $modules['authLoginUserDetail']?->parent_type_id ?? null;
        $loginUserId = $modules['authLoginUserDetail']?->id ?? null;

        if (count(config('constants.permissions'))) {
            foreach (config('constants.permissions') as $key => $value) {
                $modules[$value . '_permission'] = (isset($modules['company_id']) && !$modules['company_id']) ? true : Gate::check('hasPermission', [$value, $modules['module_name']]);
            }
        }

        if (!$modules['excel_permission']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('Unauthorized', [], [], 403);
            }
            abort(403, 'Unauthorized');
        }

        View::share('modules', $modules);

        $import_file = asset('sample-file/ocean-hrms-attendance-import-sample.xlsx');

        return view($modules['folder_path'] . '.import', compact('import_file'));
    }

    /**
     * Store attendance import file
     */
    public function import_store(Request $request)
    {
        $modules = $this->modules;
        $modules['currentGuard'] = $this->currentGuard ?? null;
        $authLoginUserDetail = $modules['authLoginUserDetail'] = $this->authenticateLoginUserDetails ?? null;
        $company_id = $modules['company_id'] = $authLoginUserDetail?->company_id ?? null;
        $modules['parent_type_id'] = $authLoginUserDetail?->parent_type_id ?? null;
        $loginUserId = $authLoginUserDetail?->id ?? null;

        View::share('modules', $modules);
        View::share('company_id', $company_id);

        $validator = Validator::make($request->all(), [
            'company_id' => ['required', Rule::exists((new Company())->getTable(), 'id')],
            'import_file' => 'required|mimes:xls,xlsx,pdf|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator)->withInput();
        }

        try {
            if (!empty($modules['company_id']) && $request->company_id != $modules['company_id']) {
                return Redirect::back()->withErrors(['company_id' => 'Unauthorized Company Access'])->withInput();
            }

            $company = Company::findOrFail($request->company_id);
            $company_name = $company->company_name;
            $company_slug = Str::slug($company_name);

            $file = $request->file('import_file');
            $extension = $file->getClientOriginalExtension();
            $fileType = in_array(strtolower($extension), ['pdf']) ? 'pdf' : 'excel';
            $filename = now()->format('dmYHis') . rand() . '.' . $extension;

            // Get file size BEFORE moving the file
            $fileSize = $file->getSize();
            $shouldQueue = ($fileSize > 5 * 1024 * 1024); // 5MB threshold

            $year = now()->format('Y');
            $month = now()->format('m');
            $folder = "uploads/{$request->company_id}-{$company_slug}/attendance/{$year}-{$month}/import-attendance-file";
            $folderPath = public_path($folder);
            if (!file_exists($folderPath)) {
                mkdir($folderPath, 0777, true);
            }
            $file->move($folderPath, $filename);
            $path = "{$folder}/{$filename}";

            $filename_path = "{$folder}/{$filename}";

            // Create import file record
            $attendanceImportFileId = AttendanceImportFile::insertGetId([
                'company_id' => $request->company_id,
                'filename' => $filename_path,
                'file_type' => $fileType,
                'status' => $shouldQueue ? 'pending' : 'processing',
                'total_rows' => 0,
                'total_success' => 0,
                'total_failed' => 0,
                'total_duplicates' => 0,
                'errors' => '',
                'created_by' => $loginUserId,
                'created_at' => now(),
            ]);

            if ($shouldQueue) {
                // Dispatch to queue for large files
                ProcessAttendanceImport::dispatch(
                    $attendanceImportFileId,
                    $filename_path,
                    $request->company_id,
                    $loginUserId,
                    $fileType,
                    $authLoginUserDetail
                );

                return Redirect::back()
                    ->with('info', 'Large file queued for processing. You will be notified when complete.')
                    ->with('import_file_id', $attendanceImportFileId);
            } else {
                // Process immediately for small files
                if ($fileType === 'pdf') {
                    // For PDF, dispatch to queue but process immediately
                    $job = new ProcessAttendanceImport(
                        $attendanceImportFileId,
                        $filename_path,
                        $request->company_id,
                        $loginUserId,
                        $fileType,
                        $authLoginUserDetail
                    );
                    $job->handle();
                } else {
                    // Process Excel immediately - use the moved file path
                    $fullFilePath = public_path($filename_path);
                    if (!file_exists($fullFilePath)) {
                        throw new \Exception("File not found at: {$fullFilePath}");
                    }
                    Excel::import(
                        new AttendanceImport($request->company_id, $loginUserId, $attendanceImportFileId, $authLoginUserDetail),
                        $fullFilePath
                    );
                }

                $attendanceImportFile = AttendanceImportFile::find($attendanceImportFileId);

                session()->flash('attendance_summary', [
                    'total_rows' => $attendanceImportFile->total_rows,
                    'total_success' => $attendanceImportFile->total_success,
                    'total_failed' => $attendanceImportFile->total_failed,
                    'total_duplicates' => $attendanceImportFile->total_duplicates,
                ]);

                if (!empty($attendanceImportFile->errors)) {
                    $messages = json_decode($attendanceImportFile->errors, true);
                    if ($attendanceImportFile->total_success > 0) {
                        return Redirect::back()
                            ->with('attendanceImportFileMessages', $messages)
                            ->with('success', 'Attendance imported successfully with some errors!');
                    } else {
                        return Redirect::back()
                            ->with('attendanceImportFileMessages', $messages)
                            ->with('error', 'Import completed with errors!');
                    }
                }

                return Redirect::back()->with('success', 'Attendance imported successfully!');
            }
        } catch (\Exception $e) {
            Log::error('Attendance Import Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return Redirect::back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Check import status (for queued imports)
     */
    public function import_status($importId)
    {
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = $this->authenticateLoginUserDetails ?? null;
        $modules['company_id'] = $modules['authLoginUserDetail']?->company_id ?? null;

        if (count(config('constants.permissions'))) {
            foreach (config('constants.permissions') as $key => $value) {
                $modules[$value . '_permission'] = (isset($modules['company_id']) && !$modules['company_id']) ? true : Gate::check('hasPermission', [$value, $modules['module_name']]);
            }
        }

        if (!$modules['excel_permission']) {
            return $this->sendError('Unauthorized', [], [], 403);
        }

        $importFile = AttendanceImportFile::find($importId);

        if (!$importFile) {
            return $this->sendError('Import file not found', [], [], 404);
        }

        // Check company access
        if ($modules['company_id'] && $importFile->company_id != $modules['company_id']) {
            return $this->sendError('Unauthorized', [], [], 403);
        }

        return $this->sendResponse([
            'status' => $importFile->status,
            'total_rows' => $importFile->total_rows,
            'total_success' => $importFile->total_success,
            'total_failed' => $importFile->total_failed,
            'total_duplicates' => $importFile->total_duplicates,
            'errors' => $importFile->errors ? json_decode($importFile->errors, true) : null,
            'started_at' => $importFile->started_at,
            'completed_at' => $importFile->completed_at,
        ], 'Import status retrieved successfully');
    }

    /**
     * Display attendance import files listing (only for admin_software guard)
     */
    public function attendance_import_files_index(Request $request)
    {
        // Only allow admin_software guard
        if (!Auth::guard('admin_software')->check()) {
            if ($request->ajax()) {
                return $this->sendError('Unauthorized', [], [], 403);
            }
            abort(403, 'Unauthorized. Only admin_software users can access this page.');
        }

        $modules = $this->modules;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;
        $modules['parent_type_id'] = ($this->authenticateLoginUserDetails?->parent_type_id) ? $this->authenticateLoginUserDetails?->parent_type_id : null;
        $loginUserId = ($modules['authLoginUserDetail'] && $modules['authLoginUserDetail']?->id) ? $modules['authLoginUserDetail']?->id : null;

        if (count(config('constants.permissions'))) {
            foreach (config('constants.permissions') as $key => $value) {
                $modules[$value . '_permission'] = (isset($modules['company_id']) && !$modules['company_id']) ? true : Gate::check('hasPermission', [$value, $modules['module_name']]);
            }
        }

        View::share('modules', $modules);

        try {
            $columns = [
                (object) ['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'td_label' => 'Sr No.', 'className' => 'w-5 text-start', 'orderable' => false, 'searchable' => false],
                (object) ['data' => "company.company_name", 'name' => 'company_id', 'td_label' => 'Company Name', 'className' => 'w-25 text-wrap'],
                (object) ['data' => "filename", 'name' => 'filename', 'td_label' => 'File Name', 'className' => 'w-20 text-wrap', 'orderable' => false, 'searchable' => false],
                (object) ['data' => "file_type", 'name' => 'file_type', 'td_label' => 'File Type', 'className' => 'text-start'],
                (object) ['data' => "status", 'name' => 'status', 'td_label' => 'Status', 'className' => 'text-start'],
                (object) ['data' => "total_rows", 'name' => 'total_rows', 'td_label' => 'Total Rows', 'className' => 'text-center'],
                (object) ['data' => "total_success", 'name' => 'total_success', 'td_label' => 'Success', 'className' => 'text-center'],
                (object) ['data' => "total_failed", 'name' => 'total_failed', 'td_label' => 'Failed', 'className' => 'text-center'],
                (object) ['data' => "total_duplicates", 'name' => 'total_duplicates', 'td_label' => 'Duplicates', 'className' => 'text-center'],
                (object) ['data' => "created_at", 'name' => 'created_at', 'td_label' => 'Created At', 'className' => 'text-start'],
                (object) ['data' => "action", 'name' => 'action', 'td_label' => 'Action', 'orderable' => false, 'searchable' => false, 'className' => 'w-10 text-center'],
            ];

            if ($modules['company_id']) {
                $columns = array_filter($columns, function ($col) {
                    return $col->name !== 'company_id';
                });
                $columns = array_values($columns);
            }

            View::share("columns", $columns);

            if ($request->ajax()) {
                $data = AttendanceImportFile::with(['company'])
                    ->where(function ($q1) use ($modules) {
                        if (!empty($modules['company_id'])) {
                            $q1->where('company_id', $modules['company_id']);
                        }
                    })
                    ->orderBy('id', 'DESC');

                $returnData = Datatables::of($data)
                    ->addIndexColumn()
                    ->filter(function ($query) use ($request, $modules) {
                        if ($request->has('filter_company') && $request->filter_company) {
                            $query->where('company_id', $request->filter_company);
                        }
                        if ($request->has('filter_status') && $request->filter_status) {
                            $query->where('status', $request->filter_status);
                        }
                        if ($request->has('filter_file_type') && $request->filter_file_type) {
                            $query->where('file_type', $request->filter_file_type);
                        }
                    })
                    ->editColumn('status', function ($row) {
                        $badgeClass = match ($row->status) {
                            'completed' => 'bg-success',
                            'processing' => 'bg-warning',
                            'failed' => 'bg-danger',
                            default => 'bg-secondary',
                        };
                        return '<span class="badge ' . $badgeClass . '">' . ucfirst($row->status) . '</span>';
                    })
                    ->editColumn('file_type', function ($row) {
                        return '<span class="badge bg-info">' . strtoupper($row->file_type ?? 'N/A') . '</span>';
                    })
                    ->editColumn('created_at', function ($row) {
                        return $row->created_at ? \Carbon\Carbon::parse($row->created_at)->format('d/m/Y h:i A') : '-';
                    })
                    ->addColumn('action', function ($row) use ($modules) {
                        $btn = '';
                        $btn .= '<a href="javascript:void(0)" data-id="' . $row->id . '" class="btn btn-info btn-icon mx-1 view-import-details" title="View Details"><i class="fa-solid fa-eye"></i></a>';
                        if ($row->errors) {
                            $btn .= '<a href="javascript:void(0)" data-id="' . $row->id . '" class="btn btn-warning btn-icon mx-1 view-import-errors" title="View Errors"><i class="fa-solid fa-exclamation-triangle"></i></a>';
                        }
                        return $btn ?: '-';
                    })
                    ->rawColumns(['status', 'file_type', 'action'])
                    ->make(true);
                return $returnData;
            }

            // return $modules; 

            return view($modules['folder_path'] . '.import-history');
        } catch (\Exception $e) {
            return Redirect::route('software.dashboard')->withErrors($e->getMessage());
        }
    }

    /**
     * Sync attendance from active biometric machine
     */
    public function syncBiometric(Request $request)
    {
        $modules = $this->modules;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;

        if (!$modules['company_id']) {
            return $this->sendError('Company ID is required', [], [], 400);
        }

        try {
            // Find active pull-based biometric machine for company
            $machine = BiometricMachine::query()
                ->where('company_id', $modules['company_id'])
                ->where('is_active', true)
                ->pullBased()
                ->whereNotNull('api_url')
                ->first();

            if (!$machine) {
                return $this->sendError('No active pull-based biometric machine found for this company', [], [], 404);
            }

            // Check if machine has required credentials
            if (!$machine->shouldShowSyncButton()) {
                return $this->sendError('Biometric machine is not properly configured. Please check API credentials.', [], [], 400);
            }

            // Use BiometricMachineController's syncAttendance method logic
            $machine->update([
                'last_sync_status' => 'pending',
                'last_sync_error' => null,
            ]);

            // Get service and fetch attendance
            $service = \App\Services\Biometric\BiometricServiceFactory::make($machine->provider_type, $machine);

            // Fetch attendance for last 7 days or since last sync
            $endDate = Carbon::now();
            $startDate = $machine->last_sync_at
                ? Carbon::parse($machine->last_sync_at)->subDay()
                : Carbon::now()->subDays(7);

            $attendanceRecords = $service->fetchAttendance($startDate, $endDate);

            // Extract response data
            $recordsFetched = $attendanceRecords['records_fetched'] ?? 0;
            $recordsTransformed = $attendanceRecords['records_transformed'] ?? 0;
            $recordsStored = $attendanceRecords['records_stored'] ?? 0;
            $data = $attendanceRecords['data'] ?? [];

            // Find the latest punch time from the data
            $lastPunchTime = null;
            if (!empty($data)) {
                // Sort by timestamp descending to get the latest
                usort($data, function ($a, $b) {
                    $timeA = strtotime($a['timestamp'] ?? '1970-01-01 00:00:00');
                    $timeB = strtotime($b['timestamp'] ?? '1970-01-01 00:00:00');
                    return $timeB <=> $timeA; // Descending order
                });
                $lastPunchTime = $data[0]['timestamp'] ?? null;
            }

            $machine->update([
                'last_sync_at' => now(),
                'last_sync_status' => 'success',
                'last_sync_error' => null,
            ]);

            $message = "Sync completed successfully. ";
            $message .= "Fetched: {$recordsFetched}, ";
            $message .= "Transformed: {$recordsTransformed}, ";
            $message .= "Stored: {$recordsStored} attendance records.";

            return $this->sendResponse([
                'records_fetched' => $recordsFetched,
                'records_transformed' => $recordsTransformed,
                'records_stored' => $recordsStored,
                'last_punch_time' => $lastPunchTime,
                'last_sync_at' => $machine->last_sync_at->format('Y-m-d H:i:s'),
                'last_sync_time_ago' => $machine->getLastSyncTimeAgo(),
                'refresh_datatable' => true, // Flag to indicate datatable should refresh
            ], $message);

        } catch (\Exception $e) {
            Log::error('Sync biometric attendance failed', [
                'company_id' => $modules['company_id'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Update machine sync status to failed
            if (isset($machine)) {
                $machine->update([
                    'last_sync_status' => 'failed',
                    'last_sync_error' => $e->getMessage(),
                ]);
            }

            return $this->sendError('Sync failed: ' . $e->getMessage(), [], [], 500);
        }
    }
}
