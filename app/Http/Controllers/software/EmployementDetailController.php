<?php
//Employeement Detail Controller
namespace App\Http\Controllers\software;

use App\Exports\EmploymentDetailExport;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeDetailRequest;
use App\Models\EmploymentDetail;
use Illuminate\Http\Request;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class EmployementDetailController extends Controller
{

    public $modules = [];
    public function __construct()
    {
        parent::__construct();

        $this->modules = [
            'title' => 'Employment Details',
            'folder_path' => 'software.modules.employee.employment-details',
            'route' => 'employment-details',
            'table_name' => (new EmploymentDetail())->getTable(),
            'permisstion_prefix' => 'employment-details',
            'module_name' => 'Employment Details',
            'company_id' => ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null,
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = $this->authenticateLoginUserDetails ?? null;
        $modules['company_id'] = $this->authenticateLoginUserDetails?->company_id ?? null;
        $modules['parent_type_id'] = $this->authenticateLoginUserDetails?->parent_type_id ?? null;
        $loginUserId = $modules['authLoginUserDetail']?->id ?? null;

        if (count(config('constants.permissions'))) {
            foreach (config('constants.permissions') as $key => $value) {
                $modules[$value . '_permission'] = (!$modules['company_id'])
                    ? true
                    : Gate::check('hasPermission', [$value, $modules['module_name']]);
            }
        }

        if (!$modules['view_permission']) {
            if ($request->ajax()) {
                return $this->sendError('Unauthorized', [], [], 403);
            }
            abort(403, 'Unauthorized');
        }

        View::share('modules', $modules);

        try {
            $columns = [
                (object) ['data' => 'employee_code', 'name' => 'DT_RowIndex', 'td_label' => 'Employee Code', 'orderable' => false, 'searchable' => false],
                (object) ['data' => "company.company_name", 'name' => 'company_id', 'td_label' => 'Company Name', 'className' => ''],
                (object) ['data' => "employee_name", 'name' => 'employee_id', 'td_label' => 'Employee Name', 'className' => 'text-start'],
                (object) ['data' => "designation_type", 'name' => 'designation_type', 'td_label' => 'Designation Type', 'className' => 'w-10 text-start'],
                (object) ['data' => "designation.name", 'name' => 'designation_id', 'td_label' => 'Designation', 'className' => 'w-10 text-start'],
                (object) ['data' => "department.name", 'name' => 'department_id', 'td_label' => 'Department', 'className' => 'w-10 text-start'],
                (object) ['data' => "subdepartment.sub_department_name", 'name' => 'sub_department_id', 'td_label' => 'Sub Department', 'className' => 'w-10 text-start'],
                (object) ['data' => "process.name", 'name' => 'process_id', 'td_label' => 'Process', 'className' => 'w-10 text-start'],

                (object) ['data' => "date_of_joining", 'name' => 'date_of_joining', 'td_label' => 'Date', 'className' => 'w-10 text-start'],
                // (object)['data' => "status", 'name' => 'status', 'td_label' => 'Status', 'className' =>  'w-5 text-start'],
                (object) ['data' => "action", 'name' => 'action', 'td_label' => 'Action', 'orderable' => false, 'searchable' => false, 'className' => 'w-10 text-start'],
            ];

            if ($modules['company_id']) {
                $columns = array_values(array_filter($columns, fn($col) => $col->name !== 'company_id'));
            }

            View::share("columns", $columns);

            if ($request->ajax()) {

                $data = EmploymentDetail::with(['company', 'employee', 'designation', 'department', 'subdepartment', 'process'])
                    ->when(Auth::guard('employees')->check() || !empty($modules['company_id']), function ($query) use ($modules, $loginUserId) {

                        // If company_id exists in $modules, use that; otherwise use employee's company_id
                        $companyId = $modules['company_id'] ?? Auth::guard('employees')->user()->company_id;

                        $query->where($modules['table_name'] . '.company_id', $companyId);

                        // Personal data permission rule
                        if ($modules['personal_data_permission'] && $modules['all_data_permission'] == false) {
                            $query->where($modules['table_name'] . '.created_by', $loginUserId);
                        }
                    });

                // Show regular employees (exclude contractor types)
                $contractTypeIds = \App\Models\EmployeeType::where('name', 'like', '%contract%')->orWhere('name', 'like', '%contractor%')->pluck('id');
                $data->where(function ($query) use ($contractTypeIds) {
                    $query->whereNotIn('employment_type', $contractTypeIds)
                        ->orWhereNull('employment_type');
                });

                $data->orderBy('id', 'DESC');

                if (!empty($modules['restore_permission'])) {
                    $data->withTrashed();
                }

                $returnData = Datatables::of($data)
                    ->addIndexColumn()
                    ->filter(function ($query) use ($request) {
                        if ($request->filled('filter_company')) {
                            $query->where('company_id', $request->filter_company);
                        }

                        if ($request->filled('filter_employee')) {
                            $query->where('employee_id', $request->filter_employee);
                        }

                        if ($request->filled('status') && $request->status !== 'all') {
                            $query->where('status', $request->status);
                        }

                        if ($request->filled('filter_date')) {
                            $separator = Str::contains($request->filter_date, ' to ') ? ' to ' : ' - ';
                            $dates = array_map('trim', explode($separator, $request->filter_date));

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

                            if ($fromDate && $toDate) {
                                $query->whereBetween('date_of_joining', [$fromDate, $toDate]);
                            } elseif ($fromDate) {
                                $query->where('date_of_joining', $fromDate);
                            }
                        }
                    })


                    ->editColumn('employee_code', function ($row) {
                        return $row->employee?->employee_code ?? '-';
                    })


                    ->editColumn('employee_name', function ($row) {
                        return $row->employee?->full_name ?? '-';
                    })

                    ->editColumn('date_of_joining', function ($row) {
                        return $row->date_of_joining
                            ? Carbon::parse($row->date_of_joining)->format('d-m-Y')
                            : '';
                    })

                    ->editColumn('status', function ($row) use ($modules) {
                        $dropdown = '<ul class="dropdown-menu">';
                        if ($row->status == "active") {
                            $btn = '<button type="button" class="btn btn-success btn-sm dropdown-toggle" data-bs-toggle="dropdown">' . ucfirst($row->status) . '</button>';
                            $dropdown .= '<li><a href="javascript:void(0)" class="dropdown-item update-status" data-url="' . route($modules['route'] . '.status-update') . '" data-id="' . $row["id"] . '" data-update_status="inactive">Inactive</a></li>';
                        } elseif ($row->status == "inactive") {
                            $btn = '<button type="button" class="btn btn-danger btn-sm dropdown-toggle" data-bs-toggle="dropdown">' . ucfirst($row->status) . '</button>';
                            $dropdown .= '<li><a href="javascript:void(0)" class="dropdown-item update-status" data-url="' . route($modules['route'] . '.status-update') . '" data-id="' . $row["id"] . '" data-update_status="active">Active</a></li>';
                        } else {
                            return '-';
                        }
                        $dropdown .= '</ul>';
                        return $btn . $dropdown;
                    })

                    ->addColumn('action', function ($row) use ($modules) {
                        $btn = '';
                        if (!$row->deleted_at) {
                            if ($modules['update_permission']) {
                                $btn .= '<a href="' . route($modules["route"] . ".edit", [$row["id"]]) . '" class="btn btn-light btn-icon mx-1"><i class="fa-solid fa-pen-to-square"></i></a>';
                            }
                            if ($modules['delete_permission']) {
                                $btn .= '<a href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . route($modules["route"] . ".destroy", [$row["id"]]) . '" class="btn btn-danger btn-icon deletebutton mx-1"><i class="fa-solid fa-trash"></i></a>';
                            }
                        } else {
                            $btn .= '<a href="javascript:void(0)" data-restore="' . route($modules["route"] . ".restore", ['id' => $row["id"]]) . '" class="btn btn-light mx-1 record-restore"><i class="ti ti-history"></i> Restore</a>';
                        }
                        return $btn ?: '-';
                    })
                    ->rawColumns(['status', 'action'])
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
            View::share('modules', $modules);

            $employeeId = $request->query('employee_id');
            if ($employeeId) {
                $existing = EmploymentDetail::where('employee_id', $employeeId)
                    ->when(!empty($modules['company_id']), fn($q) => $q->where('company_id', $modules['company_id']))
                    ->orderByDesc('id')
                    ->first();
                if ($existing) {
                    return Redirect::route($modules['route'] . '.edit', [$existing->id]);
                }
                View::share('preselectedEmployeeId', $employeeId);
                // Try to derive company from employee if not already scoped
                $empCompanyId = \App\Models\Employee::where('id', $employeeId)->value('company_id');
                if ($empCompanyId) {
                    View::share('preselectedCompanyId', $empCompanyId);
                }
            }

            return view($modules['folder_path'] . '.form');
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EmployeeDetailRequest $request)
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
            EmploymentDetail::create($validated);

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

            $edQuery = EmploymentDetail::query();
            if (!empty($modules['company_id'])) {
                $edQuery->where('company_id', $modules['company_id']);
            }
            $edit = $edQuery->findOrFail($id);
            View::share('edit', $edit);

            return view($modules['folder_path'] . '.form');
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(EmployeeDetailRequest $request, string $id)
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
            $validated['updated_by'] = $loginUserId;

            $edQuery = EmploymentDetail::query();
            if (!empty($modules['company_id'])) {
                $edQuery->where('company_id', $modules['company_id']);
            }
            $updateData = $edQuery->findOrFail($id);

            $updateData->update($validated);

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

        $edQuery = EmploymentDetail::query();
        if (!empty($modules['company_id'])) {
            $edQuery->where('company_id', $modules['company_id']);
        }
        $dataDelete = $edQuery->findOrFail($id);
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

            $edQuery = EmploymentDetail::withTrashed();
            if (!empty($modules['company_id'])) {
                $edQuery->where('company_id', $modules['company_id']);
            }
            $restore_data = $edQuery->findOrFail($id);
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
            $edQuery = EmploymentDetail::withTrashed();
            if (!empty($modules['company_id'])) {
                $edQuery->where('company_id', $modules['company_id']);
            }
            $country = $edQuery->findOrFail($request?->id);
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
        if (count(config('constants.permissions'))) {
            foreach (config('constants.permissions') as $key => $value) {
                $modules[$value . '_permission'] = (isset($modules['company_id']) && !$modules['company_id'])
                    ? true
                    : Gate::check('hasPermission', [$value, $modules['module_name']]);
            }
        }

        // Unauthorized check
        if (!$modules['view_permission']) {
            if ($request->ajax()) {
                return $this->sendError('Unauthorized', [], [], 403);
            }
            abort(403, 'Unauthorized');
        }

        try {

            $query = EmploymentDetail::select('*')
                ->where(function ($q1) use ($modules, $loginUserId) {
                    if (Auth::guard('employees')->check() || !empty($modules['company_id'])) {
                        // If company_id exists in $modules, use that; otherwise use employee's company_id
                        $companyId = $modules['company_id'] ?? Auth::guard('employees')->user()->company_id;

                        $q1->where($modules['table_name'] . '.company_id', $companyId);

                        // Personal data permission rule
                        if ($modules['personal_data_permission'] && $modules['all_data_permission'] == false) {
                            $q1->where($modules['table_name'] . '.created_by', $loginUserId);
                        }
                    }
                })
                ->with(['company'])
                ->orderBy('id', 'DESC');

            $contractTypeIds = \App\Models\EmployeeType::where('name', 'like', '%contract%')->orWhere('name', 'like', '%contractor%')->pluck('id');
            $query->where(function ($q) use ($contractTypeIds) {
                $q->whereNotIn('employment_type', $contractTypeIds)
                    ->orWhereNull('employment_type');
            });


            $companyId = $request->input('company') ?? $request->input('filter_company');
            if (!empty($companyId)) {
                $query->where('company_id', $companyId);
            }

            if (!$companyId && $modules['company_id']) {
                $query->where('company_id', $modules['company_id']);
            }

            // Employee filter
            $employeeId = $request->input('employee') ?? $request->input('filter_employee');
            if (!empty($employeeId)) {
                $query->where('employee_id', $employeeId);
            }

            // Status filter
            if ($request->filled('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            if ($request->filled('search')) {
                $search = trim($request->search);
                $query->where(function ($q) use ($search) {
                    $q->where('designation_type', 'like', "%{$search}%")
                        ->orWhere('employee_pf_no', 'like', "%{$search}%")
                        ->orWhere('payment_mode', 'like', "%{$search}%")
                        ->orWhereHas('employee', function ($employeeQuery) use ($search) {
                            $employeeQuery->where('full_name', 'like', "%{$search}%")
                                ->orWhere('employee_code', 'like', "%{$search}%");
                        })
                        ->orWhereHas('company', function ($companyQuery) use ($search) {
                            $companyQuery->where('company_name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('department', function ($departmentQuery) use ($search) {
                            $departmentQuery->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('designation', function ($designationQuery) use ($search) {
                            $designationQuery->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('subdepartment', function ($subDepartmentQuery) use ($search) {
                            $subDepartmentQuery->where('sub_department_name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('process', function ($processQuery) use ($search) {
                            $processQuery->where('name', 'like', "%{$search}%");
                        });
                });
            }

            // Date filter
            if ($request->filled('filter_date')) {
                $rawDate = $request->filter_date;
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

                if ($fromDate && $toDate) {
                    $query->whereBetween('date_of_joining', [$fromDate, $toDate]);
                } elseif ($fromDate) {
                    $query->where('date_of_joining', $fromDate);
                }
            }


            $employeeAssets = $query->get();

            return view($modules['folder_path'] . '.print', compact('employeeAssets', 'company_id', 'modules'));
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

        return Excel::download(new EmploymentDetailExport($request->all(), $this->authenticateLoginUserDetails, $modules), 'Employment Details-' . Helper::convert_date("", "Y-m-d H:i:s", "Ymd-His") . '.xlsx');
    }
}
