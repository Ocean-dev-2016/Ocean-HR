<?php

namespace App\Http\Controllers\software;

use App\Exports\EmployeeWiseSalaryDetailExport;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeWiseSalaryDetailRequest;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeWiseSalaryDetail;
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

use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class EmployeeWiseSalaryDetailController extends Controller
{

    public $modules = [];
    public function __construct()
    {
        parent::__construct();

        $this->modules = [
            'title' => 'Employee Wise Salary Details',
            'folder_path' => 'software.modules.employee.employee-wise-salary-details',
            'route' => 'employee-wise-salary-details',
            'table_name' => (new EmployeeWiseSalaryDetail())->getTable(),
            'permisstion_prefix' => 'employee-wise-salary-details',
            'module_name' => 'Salary Details',
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
        // return $modules;
        if (!$modules['view_permission']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('Unauthorized', [], [], 403);
            }
            abort(403, 'Unauthorized');
        }
        View::share('modules', $modules);

        try {

            $columns = [
                (object) ['data' => 'employee.employee_code', 'name' => 'employee.employee_code', 'td_label' => 'Employee Code', 'orderable' => false, 'searchable' => false],
                (object) ['data' => "company_name", 'name' => 'company_id', 'td_label' => 'Company Name', 'className' => ''],
                (object) ['data' => "employee_full_name", 'name' => 'employee_id', 'td_label' => 'Employee Name', 'className' => 'w-10 text-start'],
                (object) ['data' => "salary_classification", 'name' => 'salary_classification', 'td_label' => 'Salary Classification', 'className' => ''],
                (object) ['data' => "pf_type", 'name' => 'pf_type', 'td_label' => 'PF Type', 'className' => ''],
                (object) ['data' => "salary_calculation_month_count", 'name' => 'salary_calculation_month_count', 'td_label' => 'Salary Calculation Count Month', 'className' => ''],
                // (object)['data' => "status", 'name' => 'status', 'td_label' => 'Status', 'className' =>  'w-5 text-start'],
                (object) ['data' => "action", 'name' => 'action', 'td_label' => 'Action', 'orderable' => false, 'searchable' => false, 'className' => 'w-10 text-start'],
            ];
            if ($modules['company_id']) {
                $columns = array_filter($columns, function ($col) {
                    return $col->name !== 'company_id';
                });
                $columns = array_values($columns);
            }

            View::share("columns", $columns);

            if ($request->ajax()) {
                $data = EmployeeWiseSalaryDetail::with(['company', 'employee'])
                    ->when(Auth::guard('employees')->check() || !empty($modules['company_id']), function ($query) use ($modules, $loginUserId) {
                        // If company_id exists in $modules, use that; otherwise use employee's company_id
                        $companyId = $modules['company_id'] ?? Auth::guard('employees')->user()->company_id;

                        $query->where($modules['table_name'] . '.company_id', $companyId);

                        // Personal data permission rule
                        if ($modules['personal_data_permission'] && $modules['all_data_permission'] == false) {
                            $query->where($modules['table_name'] . '.created_by', $loginUserId);
                        }
                    })
                    ->orderBy($modules['table_name'] . '.id', 'DESC');

                if (isset($modules['restore_permission']) && $modules['restore_permission']) {
                    $data = $data->withTrashed();
                }
                $data = $data->with(['company']);


                $returnData = Datatables::of($data)
                    ->addIndexColumn()
                    ->filter(function ($query) use ($request, $modules) {
                        if ($request->has('filter_company') && $request->filter_company) {
                            $query->where($modules['table_name'] . '.company_id', $request->filter_company);
                        }

                        if ($request->has('filter_employee') && $request->filter_employee) {
                            $query->where($modules['table_name'] . '.employee_id', $request->filter_employee);
                        }

                        // Filter by Employee Code
                        if ($request->has('filter_employee_code') && $request->filter_employee_code) {
                            $query->whereHas('employee', function ($q) use ($request) {
                                $q->where('employee_code', 'like', '%' . $request->filter_employee_code . '%');
                            });
                        }

                        if ($request->has('filter_salary_classification') && $request->filter_salary_classification) {
                            $query->where($modules['table_name'] . '.salary_classification', $request->filter_salary_classification);
                        }

                        if ($request->has('filter_pf_type') && $request->filter_pf_type) {
                            $query->where($modules['table_name'] . '.pf_type', $request->filter_pf_type);
                        }

                        if ($request->has('filter_salary_calculation_month_count') && $request->filter_salary_calculation_month_count) {
                            $query->where($modules['table_name'] . '.salary_calculation_month_count', $request->filter_salary_calculation_month_count);
                        }

                        if ($request->has('status') && $request->status !== null && $request->status !== 'all') {
                            $query->where((new EmployeeWiseSalaryDetail())->getTable() . '.status', $request->status);
                        }
                    })
                    ->addColumn('company_name', function ($row) {
                        if ($row->company) {
                            return $row?->company?->company_name;
                        }
                        return '-';
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
                    ->addColumn('employee_full_name', function ($row) {
                        if ($row->employee) {
                            return $row?->employee?->full_name;
                        }
                        return '-';
                    })
                    ->addColumn('action', function ($row) use ($modules) {
                        $btn = '';
                        if (!$row?->deleted_at) {
                            if ($modules['update_permission']) {
                                $btn .= '<a href="' . route($modules["route"] . ".edit", [$row["id"]]) . '" class="btn btn-light btn-icon mx-1"><i class="fa-solid fa-pen-to-square"></i></a>';
                            }
                            if ($modules['delete_permission']) {
                                $btn .= '<a href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . route($modules["route"] . ".destroy", [$row["id"]]) . '" class="btn btn-danger btn-icon deletebutton mx-1"><i class="fa-solid fa-trash"></i></i></a>';
                            }
                        } else {
                            $btn .= '<a href="javascript:void(0)" data-restore="' . route($modules["route"] . ".restore", ['id' => $row["id"]]) . '" class="btn btn-light mx-1 record-restore" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Restore Data"><i class="ti ti-history"></i> Restore</a>';
                        }
                        if ($btn == '') {
                            $btn = '-';
                        }
                        return $btn;
                    })
                    ->rawColumns(['status', 'action', 'attachment'])
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

            return view($modules['folder_path'] . '.form');
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EmployeeWiseSalaryDetailRequest $request)
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

        try {
            $payload = $this->prepareSalaryPayload($validated, $loginUserId, false);
            $payload['previous_gross_salary'] = $payload['ctc'] ?? 0;
            EmployeeWiseSalaryDetail::create($payload);

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

            $editQuery = EmployeeWiseSalaryDetail::with('company');
            if (!empty($modules['company_id'])) {
                $editQuery->where('company_id', $modules['company_id']);
            }
            $edit = $editQuery->findOrFail($id);

            // Fetch latest active increment for this employee and overlay if exists
            $latestIncrement = \App\Models\EmployeeIncrementDetails::where('employee_id', $edit->employee_id)
                ->where('status', 'active')
                ->get()
                ->sortByDesc(function ($inc) {
                    $monthMap = [
                        'january' => 1,
                        'february' => 2,
                        'march' => 3,
                        'april' => 4,
                        'may' => 5,
                        'june' => 6,
                        'july' => 7,
                        'august' => 8,
                        'september' => 9,
                        'october' => 10,
                        'november' => 11,
                        'december' => 12,
                        'jan' => 1,
                        'feb' => 2,
                        'mar' => 3,
                        'apr' => 4,
                        'jun' => 6,
                        'jul' => 7,
                        'aug' => 8,
                        'sep' => 9,
                        'oct' => 10,
                        'nov' => 11,
                        'dec' => 12
                    ];
                    $incYear = (int) $inc->effective_year;
                    $incMonthName = strtolower(trim($inc->effective_month ?? ''));
                    $incMonth = $monthMap[$incMonthName] ?? 1;
                    return ($incYear * 100) + $incMonth;
                })->first();

            if ($latestIncrement) {
                $edit->basic_da = $latestIncrement->basic_da;
                $edit->hra = $latestIncrement->hra;
                $edit->conveyance_allowance = $latestIncrement->conveyance_allowance;
                $edit->medical_allowance = $latestIncrement->medical_allowance;
                $edit->special_allowance = $latestIncrement->special_allowance;

                $basic = (float) ($latestIncrement->basic_da ?? 0);
                $hra = (float) ($latestIncrement->hra ?? 0);
                $conveyance = (float) ($latestIncrement->conveyance_allowance ?? 0);
                $medical = (float) ($latestIncrement->medical_allowance ?? 0);
                $special = (float) ($latestIncrement->special_allowance ?? 0);
                $edit->ctc = round($basic + $hra + $conveyance + $medical + $special, 2);
            }

            View::share('edit', $edit);

            return view($modules['folder_path'] . '.form');
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EmployeeWiseSalaryDetailRequest $request, string $id)
    {
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = $this->authenticateLoginUserDetails ?? null;
        $modules['company_id'] = $modules['authLoginUserDetail']?->company_id ?? null;
        $modules['parent_type_id'] = $modules['authLoginUserDetail']?->parent_type_id ?? null;
        $loginUserId = $modules['authLoginUserDetail']?->id ?? null;

        if (count(config('constants.permissions'))) {
            foreach (config('constants.permissions') as $value) {
                $modules[$value . '_permission'] = (!$modules['company_id'])
                    ? true
                    : Gate::check('hasPermission', [$value, $modules['module_name']]);
            }
        }

        if (!$modules['update_permission']) {
            if ($request->ajax()) {
                return $this->sendError('Unauthorized', [], [], 403);
            }
            abort(403, 'Unauthorized');
        }

        View::share('modules', $modules);

        $validated = $request->validated();

        try {
            $payload = $this->prepareSalaryPayload($validated, $loginUserId, true);

            $updateQuery = EmployeeWiseSalaryDetail::query();
            if (!empty($modules['company_id'])) {
                $updateQuery->where('company_id', $modules['company_id']);
            }
            $updateData = $updateQuery->findOrFail($id);
            $payload['previous_gross_salary'] = $updateData->ctc ?? ($payload['ctc'] ?? 0);

            // Handle attachment upload like in store
            if ($request->hasFile('attachment')) {
                $company = Company::find($payload['company_id']);
                $company_name = $company->company_name ?? 'default-company';
                $company_slug = Str::slug($company_name);

                $file = $request->file('attachment');
                $extension = $file->getClientOriginalExtension();
                $image_name = Helper::make_slug($payload['company_id'] . ' ' . str_replace(" ", "_", $payload['employee_id']) . " ") . date('Ymd-His');
                $filename = $image_name . '.' . $extension;

                $year = now()->format('Y');
                $month = now()->format('m');
                $folder = "uploads/" . $payload['company_id'] . "-" . $company_slug . "/EmployeeAssets/{$year}-{$month}/";

                $uploadedPath = public_path($folder);
                if ($file->move($uploadedPath, $filename)) {
                    $uploadedImage = $folder . $filename;

                    // Convert image to WebP if applicable
                    if (in_array(strtolower($extension), ["jpeg", "png", "jpg"])) {
                        $webP = Helper::existingImageConverToWebp($extension, $uploadedPath, $filename, $image_name, 80, false);
                        if ($webP) {
                            $uploadedImage = $folder . $webP;
                        }
                    }

                    $payload['attachment'] = $uploadedImage;
                }
            }

            // Update record
            unset($payload['id']);
            $updateData->update($payload);

            return Redirect::route($modules['route'] . '.index')
                ->withSuccess($modules['title'] . ' updated successfully');
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')
                ->withErrors($e->getMessage());
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

        $deleteQuery = EmployeeWiseSalaryDetail::query();
        if (!empty($modules['company_id'])) {
            $deleteQuery->where('company_id', $modules['company_id']);
        }
        $dataDelete = $deleteQuery->findOrFail($id);
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
            $restoreQuery = EmployeeWiseSalaryDetail::withTrashed();
            if (!empty($modules['company_id'])) {
                $restoreQuery->where('company_id', $modules['company_id']);
            }
            $restore_data = $restoreQuery->findOrFail($id);
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
            $statusQuery = EmployeeWiseSalaryDetail::withTrashed();
            if (!empty($modules['company_id'])) {
                $statusQuery->where('company_id', $modules['company_id']);
            }
            $country = $statusQuery->findOrFail($request?->id);
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

            $query = EmployeeWiseSalaryDetail::select('*')
                ->when(Auth::guard('employees')->check() || !empty($modules['company_id']), function ($q) use ($modules, $loginUserId) {
                    // If company_id exists in $modules, use that; otherwise use employee's company_id
                    $companyId = $modules['company_id'] ?? Auth::guard('employees')->user()->company_id;

                    $q->where((new EmployeeWiseSalaryDetail())->getTable() . '.company_id', $companyId);

                    // Personal data permission rule
                    if ($modules['personalDataPermission'] && !$modules['allDataPermission']) {
                        $q->where((new EmployeeWiseSalaryDetail())->getTable() . '.created_by', $loginUserId);
                    }
                })
                ->with(['company', 'employee'])
                ->orderBy('id', 'DESC');


            if ($request->filled('company')) {
                $query->where((new EmployeeWiseSalaryDetail())->getTable() . '.company_id', $request->company);
            }

            // Employee filter
            if ($request->filled('employee')) {
                $query->where((new EmployeeWiseSalaryDetail())->getTable() . '.employee_id', $request->employee);
            }

            // Status filter
            if ($request->filled('status') && $request->status !== 'all') {
                $query->where((new EmployeeWiseSalaryDetail())->getTable() . '.status', $request->status);
            }

            // Asset name search
            if ($request->filled('search')) {
                $query->where('company_name', 'like', '%' . $request->search . '%');
            }



            $EmployeeWiseSalaryDetails = $query->get();

            return view($modules['folder_path'] . '.print', compact('EmployeeWiseSalaryDetails', 'company_id', 'modules'));
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

        return Excel::download(new EmployeeWiseSalaryDetailExport($request->all(), $this->authenticateLoginUserDetails, $modules), 'Employee Wise Salary Details-' . Helper::convert_date("", "Y-m-d H:i:s", "Ymd-His") . '.xlsx');
    }

    private function prepareSalaryPayload(array $data, ?int $actorId, bool $isUpdate): array
    {
        $booleanFields = [
            'overtime',
            'is_welfare_fund_applied',
            'leave_elegiblity',
            'sandwich_rule_flag',
            'is_bonus_applied',
            'pf',
            'pradhanmantri_pf',
            'tds',
            'insurance',
            'pt',
            'is_esi_company_side',
            'esi_employee_side',
            'gratuity_calculation',
        ];

        foreach ($booleanFields as $field) {
            $data[$field] = isset($data[$field]) && (string) $data[$field] === '1' ? '1' : '0';
        }

        $nullableStrings = ['salary_calculation_month_count', 'pf_type'];
        foreach ($nullableStrings as $field) {
            if (array_key_exists($field, $data) && $data[$field] === '') {
                $data[$field] = null;
            }
        }

        if (isset($data['company_id'])) {
            $data['company_id'] = (int) $data['company_id'];
        }

        if (isset($data['employee_id'])) {
            $data['employee_id'] = (int) $data['employee_id'];
        }

        if (isset($data['week_off'])) {
            $weekOff = is_array($data['week_off']) ? $data['week_off'] : [];
            $data['week_off'] = json_encode(array_values($weekOff));
        }

        if ($data['sandwich_rule_flag'] === '0') {
            $data['sandwich_rule_applied_on'] = null;
            $data['sandwich_rule_type'] = null;
        }

        $numericFields = [
            'pf_percentage',
            'pradhanmantri_pf_percentage',
            'tds_percentage',
            'insurance_amount',
            'pt_amount',
            'esi_company_side_percentage',
            'esi_employee_side_percentage',
            'basic_da',
            'conveyance_allowance',
            'medical_allowance',
            'special_allowance',
            'welfare_fund_amount',
        ];

        foreach ($numericFields as $field) {
            if (array_key_exists($field, $data) && $data[$field] !== null && $data[$field] !== '') {
                $data[$field] = (float) $data[$field];
            }
        }

        if ($data['pf'] === '0') {
            $data['pf_percentage'] = null;
        }

        if ($data['pradhanmantri_pf'] === '0') {
            $data['pradhanmantri_pf_percentage'] = null;
        }

        if ($data['tds'] === '0') {
            $data['tds_percentage'] = null;
        }

        if ($data['insurance'] === '0') {
            $data['insurance_amount'] = null;
        }

        if ($data['pt'] === '0') {
            $data['pt_amount'] = null;
        }

        if ($data['is_esi_company_side'] === '0') {
            $data['esi_company_side_percentage'] = null;
        }

        if ($data['esi_employee_side'] === '0') {
            $data['esi_employee_side_percentage'] = null;
        }

        if ($data['is_welfare_fund_applied'] === '0') {
            $data['welfare_fund_amount'] = null;
        }

        $basic = (float) ($data['basic_da'] ?? 0);
        $conveyance = (float) ($data['conveyance_allowance'] ?? 0);
        $medical = (float) ($data['medical_allowance'] ?? 0);
        $special = (float) ($data['special_allowance'] ?? 0);

        // Check if HRA is manually provided
        if (isset($data['hra']) && $data['hra'] !== '' && $data['hra'] !== null) {
            $hraAmount = (float) $data['hra'];
            $data['hra'] = $hraAmount;
        } else {
            // Manual HRA not provided -> 0
            $hraAmount = 0;
            $data['hra'] = $hraAmount;
        }

        $totalCtc = $basic + $hraAmount + $conveyance + $medical + $special;
        $data['ctc'] = round($totalCtc, 2);

        if ($actorId) {
            if ($isUpdate) {
                $data['updated_by'] = $actorId;
            } else {
                $data['created_by'] = $actorId;
            }
        }

        return $data;
    }
}
