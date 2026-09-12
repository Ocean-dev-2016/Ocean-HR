<?php

namespace App\Http\Controllers\software;

use App\Exports\EmployeeIncrementDetailsExport;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeIncrementDetailsRequest;
use App\Models\Company;
use App\Models\EmployeeIncrementDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class EmployeeIncrementDetailsController extends Controller
{

    private const DEFAULT_WORKING_DAYS = 26;
    private const DEFAULT_HOURS_PER_DAY = 8;

    public $modules = [];

    /**
     * Derive per day and per hour salary values from the entered earnings so the
     * saved figures always stay consistent.
     */
    private function applySalaryBreakdown(array $payload): array
    {
        $earningsKeys = [
            'basic_da',
            'hra',
            'conveyance_allowance',
            'medical_allowance',
            'special_allowance',
        ];

        $monthlyTotal = 0.0;
        foreach ($earningsKeys as $key) {
            $monthlyTotal += (float) ($payload[$key] ?? 0);
        }

        $workingDays = self::DEFAULT_WORKING_DAYS ?: 1;
        $hoursPerDay = self::DEFAULT_HOURS_PER_DAY ?: 1;

        $perDay = $monthlyTotal / $workingDays;
        $perHour = $perDay / $hoursPerDay;

        $payload['per_day_salary'] = number_format($perDay, 2, '.', '');
        $payload['per_hour_salary'] = number_format($perHour, 2, '.', '');

        return $payload;
    }

    private function resolveCompanyHra(?int $companyId = null): ?float
    {
        $targetCompanyId = $companyId ?: ($this->authenticateLoginUserDetails?->company_id ?? null);
        if (!$targetCompanyId) {
            return null;
        }

        $company = Company::find($targetCompanyId);
        if (!$company || $company->hra_percentage === null) {
            return null;
        }

        return (float) $company->hra_percentage;
    }

    private function applyCompanyHra(array $payload): array
    {
        $companyId = isset($payload['company_id']) ? (int) $payload['company_id'] : null;
        $companyHra = $this->resolveCompanyHra($companyId);

        if ($companyHra === null) {
            return $payload;
        }

        $basic = (float) ($payload['basic_da'] ?? 0);
        $hraValue = $basic * ($companyHra / 100);

        $payload['hra'] = number_format($hraValue, 2, '.', '');

        return $payload;
    }

    public function getEmployeeSalaryDetails(Request $request)
    {
        $employeeId = $request->employee_id;
        $salaryDetail = \App\Models\EmployeeWiseSalaryDetail::where('employee_id', $employeeId)->first();
        if ($salaryDetail) {
            return response()->json([
                'success' => true,
                'data' => $salaryDetail
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => 'No salary details found'
        ]);
    }

    public function __construct()
    {
        parent::__construct();

        $this->modules = [
            'title' => 'Employee Increment Details',
            'folder_path' => 'software.modules.master.employee-increment-details',
            'route' => 'employee-increment-details',
            'table_name' => (new EmployeeIncrementDetails())->getTable(),
            'permisstion_prefix' => 'employee-increment-details',
            'module_name' => 'Increment Details',
            'company_id' => ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null,


        ];
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

        try {

            $columns = [
                // (object)[ 'data' => "id", 'name' => 'id', 'td_label' => 'Id' ],
                (object) ['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'td_label' => 'Sr No.', 'className' => 'w-5 text-start', 'orderable' => false, 'searchable' => false],
                (object) ['data' => "company.company_name", 'name' => 'company_id', 'td_label' => 'Company Name', 'className' => ''],
                (object) ['data' => "employee_code", 'name' => 'employee_code', 'td_label' => 'Employee Code', 'className' => 'w-10 text-start'],
                // (object)['data' => "employee.middle_name", 'name' => 'employee_id', 'td_label' => 'Employee Name', 'className' => 'w-10 text-start'],
                (object) ['data' => "employee_name", 'name' => 'employee_id', 'td_label' => 'Employee Name', 'className' => 'w-15 text-start'],

                (object) ['data' => "icrement_date", 'name' => 'icrement_date', 'td_label' => 'Increment Date', 'className' => 'w-10 text-start'],
                // (object)['data' => "basic_da", 'name' => 'basic_da', 'td_label' => 'Basic + D.A', 'className' => 'w-10 text-start'],
                // (object)['data' => "hra", 'name' => 'hra', 'td_label' => 'HRA', 'className' => 'w-10 text-start'],
                // (object)['data' => "conveyance_allowance", 'name' => 'conveyance_allowance', 'td_label' => 'Conveyance Allowance', 'className' => 'w-10 text-start'],
                // (object)['data' => "medical_allowance", 'name' => 'medical_allowance', 'td_label' => 'Medical Allowance', 'className' => 'w-10 text-start'],
                // (object)['data' => "special_allowance", 'name' => 'special_allowance', 'td_label' => 'Special Allowance', 'className' => 'w-10 text-start'],
                // (object)['data' => "pf", 'name' => 'pf', 'td_label' => 'PF', 'className' => 'w-10 text-start'],
                // (object)['data' => "effective_month", 'name' => 'effective_month', 'td_label' => 'Effective Month', 'className' => 'w-10 text-start'],
                // (object)['data' => "effective_year", 'name' => 'effective_year', 'td_label' => 'Effective Year', 'className' => 'w-10 text-start'],
                (object) [
                    'data' => "effective_month_year",
                    'name' => 'effective_month_year',
                    'td_label' => 'Effective Month / Year',
                    'className' => 'w-15 text-start'
                ],

                (object) ['data' => "designation.name", 'name' => 'designation_id', 'td_label' => 'Designation', 'className' => 'w-10 text-start'],
                // (object)['data' => "per_day_salary", 'name' => 'per_day_salary', 'td_label' => 'Per Day Salary', 'className' => 'w-10 text-start'],
                // (object)['data' => "per_hour_salary", 'name' => 'per_hour_salary', 'td_label' => 'Per Hour Salary', 'className' => 'w-10 text-start'],
                // (object)['data' => "remark", 'name' => 'remark', 'td_label' => 'Remark', 'className' => 'w-15 text-start text-wrap'],
                (object) ['data' => "status", 'name' => 'status', 'td_label' => 'Status', 'className' => 'w-5 text-start'],
                (object) ['data' => "action", 'name' => 'action', 'td_label' => 'Action', 'orderable' => false, 'searchable' => false, 'className' => 'w-5 text-start'],
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


            if ($request->ajax()) {
                // dd($request->all());
                $data = EmployeeIncrementDetails::with(['company', 'employee', 'designation'])
                    ->where(function ($query) use ($modules, $loginUserId) {
                        if (Auth::guard('employees')->check()) {
                            $teamPersonCompanyId = Auth::guard('employees')->user()->company_id;
                            $query->where('company_id', $teamPersonCompanyId);

                            if ($modules['personal_data_permission'] && $modules['all_data_permission'] == false) {
                                $query->where('created_by', $loginUserId);
                            }
                        }
                    })
                    ->orderBy('id', 'DESC');

                if (isset($modules['restore_permission']) && $modules['restore_permission']) {
                    $data = $data->withTrashed();
                }
                $data = $data->with(['company']);


                $returnData = Datatables::of($data)
                    ->addIndexColumn()
                    ->filter(function ($query) use ($request) {

                        if ($request->has('filter_company') && $request->filter_company) {
                            $query->where('company_id', $request->filter_company);
                        }

                        if ($request->has('filter_employee') && $request->filter_employee) {
                            $query->where('employee_id', $request->filter_employee);
                        }
                        if ($request->has('filter_designation') && $request->filter_designation) {
                            $query->where('designation_id', $request->filter_designation);
                        }

                        // Filter by Employee Code
                        if ($request->has('filter_employee_code') && $request->filter_employee_code) {
                            $query->whereHas('employee', function ($q) use ($request) {
                                $q->where('employee_code', 'like', '%' . $request->filter_employee_code . '%');
                            });
                        }

                        $query->when($request->filled('search'), function ($q) use ($request) {
                            $search = $request->search;

                            $q->whereHas('employee', function ($q2) use ($search) {
                                $q2->where('middle_name', 'like', "%{$search}%")
                                    ->orWhere('employee_code', 'like', "%{$search}%");
                            });
                        });


                        if ($request->has('status') && $request->status !== null && $request->status !== 'all') {
                            $query->where((new EmployeeIncrementDetails())->getTable() . '.status', $request->status);
                        }
                        // Date filter
                        if ($request->filled('filter_date')) {
                            $dates = explode(' to ', $request->filter_date);

                            try {
                                $fromDate = Carbon::createFromFormat('d/m/Y', $dates[0])->startOfDay();
                                $toDate = Carbon::createFromFormat('d/m/Y', $dates[1])->endOfDay();
                            } catch (\Exception $e) {
                                $fromDate = $toDate = null; // fallback if parsing fails
                            }

                            $tableColumn = (new EmployeeIncrementDetails())->getTable() . '.icrement_date';

                            if ($fromDate && $toDate) {
                                $query->whereBetween($tableColumn, [$fromDate, $toDate]);
                            } elseif ($fromDate) {
                                $query->whereDate($tableColumn, $fromDate);
                            }
                        }
                    })


                    ->editColumn('icrement_date', function ($row) {
                        if ($row->icrement_date) {
                            return \Carbon\Carbon::parse($row->icrement_date)->format('d/m/Y');
                        }
                        return '-';
                    })
                    ->addColumn('employee_code', function ($row) {
                        return $row->employee?->employee_code ?? '-';
                    })
                    ->addColumn('employee_name', function ($row) {
                        // Combine full_name + middle_name (or whatever fields you want)
                        if ($row->employee) {
                            return $row->employee->proper_name;
                        }
                        return '-';
                    })

                    ->addColumn('effective_month_year', function ($row) {
                        $output = '';

                        if ($row->effective_month) {
                            $output .= '<strong>Month :</strong> ' . $row->effective_month;
                        }

                        if ($row->effective_year) {

                            $output .= ($output ? '<br>' : '') . '<strong>Year :</strong> ' . $row->effective_year;
                        }

                        return $output ?: '-';
                    })

                    ->editColumn('status', function ($row) use ($modules) {
                        $btn = '';

                        $dropdown = "";
                        $dropdown .= '<ul class="dropdown-menu" style="">';

                        if ($row->status == "active") {
                            $btn .= '<button type="button" class="btn btn-success btn-sm  dropdown-toggle waves-effect waves-light" data-bs-toggle="dropdown" aria-expanded="false">' . ucfirst($row->status) . '</button>';
                            $dropdown .= '<li><a href="javascript:void(0)" class="dropdown-item waves-effect btn-label-danger update-status" data-url="' . route($modules['route'] . '.status-update') . '" data-id="' . $row["id"] . '"  data-update_status="inactive">Inactive</a></li>';
                        } elseif ($row->status == "inactive") {
                            $btn .= '<button type="button" class="btn btn-danger btn-sm  dropdown-toggle waves-effect waves-light" data-bs-toggle="dropdown" aria-expanded="false">' . ucfirst($row->status) . '</button>';
                            $dropdown .= '<li><a href="javascript:void(0)" class="dropdown-item waves-effect btn-label-success update-status" data-url="' . route($modules['route'] . '.status-update') . '" data-id="' . $row["id"] . '"  data-update_status="active">Active</a></li>';
                        } else {
                            return $btn;
                        }
                        $dropdown .= '</ul>';
                        $btn .= $dropdown;
                        return $btn;
                    })
                    ->addColumn('action', function ($row) use ($modules) {
                        $btn = '';
                        if (!$row?->deleted_at) {
                            // $btn .= '<a href="javascript:void(0)" class="edit btn btn-primary btn-sm">View</a>';
                            if ($modules['update_permission']) {
                                $btn .= '<a href="' . route($modules["route"] . ".edit", [$row["id"]]) . '" class="btn btn-light btn-icon mx-1"><i class="fa-solid fa-pen-to-square"></i></a>';
                            }
                            if ($modules['delete_permission']) {
                                $btn .= '<a href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . route($modules["route"] . ".destroy", [$row["id"]]) . '" class="btn btn-danger btn-icon deletebutton mx-1"><i class="fa-solid fa-trash"></i></i></a>';
                            }
                        } else {
                            $btn .= '<a href="javascript:void(0)" data-restore="' . route($modules["route"] . ".restore", ['id' => $row["id"]]) . '" class="btn btn-light mx-1 record-restore" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Restore Data"><i class="ti ti-history"></i> Restore</a>';
                        }
                        // $btn .= '<a href="javascript:void(0)" class="btn btn-info btn-icon mr-2"><i class="fa-solid fa-key"></i></a>';
                        if ($btn == '') {
                            $btn = '-';
                        }
                        return $btn;
                    })
                    ->rawColumns(['status', 'action', 'effective_month_year'])
                    ->make(true);
                return $returnData;
            }

            return view($modules['folder_path'] . '.index');
        } catch (\Exception $e) {
            return Redirect::route('software.dashboard')->withErrors($e->getMessage());
        }
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $modules = $this->modules;
        $modules['addPermission'] = Gate::check('hasPermission', ['add', $modules['module_name']]);
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
            // Get employee_id from query parameter if provided
            $employeeId = $request->query('employee_id');
            View::share('modules', $modules);
            View::share('preselectedEmployeeId', $employeeId);
            $companyHraPercentage = $this->resolveCompanyHra($modules['company_id']);
            View::share('companyHraPercentage', $companyHraPercentage);

            return view($modules['folder_path'] . '.form');
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(EmployeeIncrementDetailsRequest $request)
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
        $validated = $request->validated();
        $validated = $this->applyCompanyHra($validated);
        $validated = $this->applySalaryBreakdown($validated);

        try {
            // return $this->authenticateLoginUserDetails;

            $validated['created_by'] = $loginUserId;
            // return $validated;
            EmployeeIncrementDetails::create($validated);

            // Propagate increment effects to employee wise salary details
            $salaryDetail = \App\Models\EmployeeWiseSalaryDetail::where('employee_id', $validated['employee_id'])->first();
            if ($salaryDetail) {
                $basic = (float) ($validated['basic_da'] ?? 0);
                $hra = (float) ($validated['hra'] ?? 0);
                $conveyance = (float) ($validated['conveyance_allowance'] ?? 0);
                $medical = (float) ($validated['medical_allowance'] ?? 0);
                $special = (float) ($validated['special_allowance'] ?? 0);
                $totalCtc = round($basic + $hra + $conveyance + $medical + $special, 2);

                $salaryDetail->update([
                    'basic_da' => $basic,
                    'hra' => $hra,
                    'conveyance_allowance' => $conveyance,
                    'medical_allowance' => $medical,
                    'special_allowance' => $special,
                    'ctc' => $totalCtc,
                ]);
            }

            // Propagate designation changes to employment details
            if (!empty($validated['designation_id'])) {
                $employmentDetail = \App\Models\EmploymentDetail::where('employee_id', $validated['employee_id'])->first();
                if ($employmentDetail) {
                    $employmentDetail->update([
                        'designation_id' => $validated['designation_id']
                    ]);
                }
            }

            return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' create successfully');
        } catch (\Exception $e) {
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

            $edit = EmployeeIncrementDetails::findOrFail($id);
            View::share('edit', $edit);
            $companyHraPercentage = $this->resolveCompanyHra($edit->company_id);
            View::share('companyHraPercentage', $companyHraPercentage);

            return view($modules['folder_path'] . '.form');
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(EmployeeIncrementDetailsRequest $request, string $id)
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

        $validated = $request->validated();
        $validated = $this->applyCompanyHra($validated);
        $validated = $this->applySalaryBreakdown($validated);
        try {

            $validated['updated_by'] = $loginUserId;
            // return $validated;
            $updateData = EmployeeIncrementDetails::findOrFail($id);
            if ($updateData) {
                unset($validated['id']);
                $updateData->update($validated);

                // Propagate increment effects to employee wise salary details
                $salaryDetail = \App\Models\EmployeeWiseSalaryDetail::where('employee_id', $validated['employee_id'])->first();
                if ($salaryDetail) {
                    $basic = (float) ($validated['basic_da'] ?? 0);
                    $hra = (float) ($validated['hra'] ?? 0);
                    $conveyance = (float) ($validated['conveyance_allowance'] ?? 0);
                    $medical = (float) ($validated['medical_allowance'] ?? 0);
                    $special = (float) ($validated['special_allowance'] ?? 0);
                    $totalCtc = round($basic + $hra + $conveyance + $medical + $special, 2);

                    $salaryDetail->update([
                        'basic_da' => $basic,
                        'hra' => $hra,
                        'conveyance_allowance' => $conveyance,
                        'medical_allowance' => $medical,
                        'special_allowance' => $special,
                        'ctc' => $totalCtc,
                    ]);
                }

                // Propagate designation changes to employment details
                if (!empty($validated['designation_id'])) {
                    $employmentDetail = \App\Models\EmploymentDetail::where('employee_id', $validated['employee_id'])->first();
                    if ($employmentDetail) {
                        $employmentDetail->update([
                            'designation_id' => $validated['designation_id']
                        ]);
                    }
                }

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

        $dataDelete = EmployeeIncrementDetails::findOrFail($id);
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

            $restore_data = EmployeeIncrementDetails::withTrashed()->findOrFail($id);
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
            $country = EmployeeIncrementDetails::withTrashed()->findOrFail($request?->id);
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

        // Authenticated user details
        $authUser = $this->authenticateLoginUserDetails;
        $modules['authLoginUserDetail'] = $authUser;
        $modules['company_id'] = $authUser?->company_id ?? null;
        $company_id = $modules['company_id'];
        $loginUserId = $authUser?->id ?? null;

        try {
            // Permissions
            $moduleName = $modules['module_name'];
            $modules['viewPermission'] = Gate::check('hasPermission', ['view', $moduleName]);
            $modules['addPermission'] = Gate::check('hasPermission', ['add', $moduleName]);
            $modules['editPermission'] = Gate::check('hasPermission', ['update', $moduleName]);
            $modules['deletePermission'] = Gate::check('hasPermission', ['delete', $moduleName]);
            $modules['personalDataPermission'] = Gate::check('hasPermission', ['personal_data', $moduleName]);
            $modules['allDataPermission'] = Gate::check('hasPermission', ['all_data', $moduleName]);

            // Unauthorized check
            if ($request->ajax()) {
                if (!$modules['viewPermission']) {
                    return $this->sendError('Unauthorized', [], [], 403);
                }
            } elseif (!$modules['viewPermission']) {
                abort(403, 'Unauthorized');
            }

            $query = EmployeeIncrementDetails::select('*')
                ->where(function ($q) use ($modules, $loginUserId) {
                    if (Auth::guard('employees')->check()) {
                        $teamPersonCompanyId = Auth::guard('employees')->user()->company_id;
                        $q->where('company_id', $teamPersonCompanyId);

                        if ($modules['personalDataPermission'] && !$modules['allDataPermission']) {
                            $q->where('created_by', $loginUserId);
                        }
                    }
                })
                ->with(['company', 'employee', 'designation'])
                ->orderBy('id', 'DESC');


            if ($request->filled('company')) {
                $query->whereHas('company', function ($q) use ($request) {
                    $q->where('company_id', $request->company);
                });
            }

            // Employee filter
            if ($request->filled('employee')) {
                $query->where('employee_id', $request->employee);
            }
            // Designation filter
            if ($request->filled('designation')) {
                $query->where('designation_id', $request->designation);
            }
            // Status filter
            if ($request->filled('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            // Asset name search
            $query->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;

                $q->whereHas('employee', function ($q2) use ($search) {
                    $q2->where('middle_name', 'like', "%{$search}%");
                });
            });

            // Date filter

            if ($request->filled('filter_date')) {
                $dates = explode(' to ', $request->filter_date);

                try {
                    $fromDate = isset($dates[0]) && $dates[0] != ''
                        ? Carbon::createFromFormat('d/m/Y', trim($dates[0]))->startOfDay()
                        : null;

                    $toDate = isset($dates[1]) && $dates[1] != ''
                        ? Carbon::createFromFormat('d/m/Y', trim($dates[1]))->endOfDay()
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



            $assetsallocation = $query->get();

            return view($modules['folder_path'] . '.print', compact('assetsallocation', 'company_id', 'modules'));
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

        return Excel::download(new EmployeeIncrementDetailsExport($request->all(), $this->authenticateLoginUserDetails, $modules), 'Employee Increment Details-' . Helper::convert_date("", "Y-m-d H:i:s", "Ymd-His") . '.xlsx');
    }
}
