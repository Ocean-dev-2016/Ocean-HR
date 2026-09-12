<?php

namespace App\Http\Controllers\software;

use App\Exports\BonusExport;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\BonusRequest;
use App\Models\Bonus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class BonusController extends Controller
{
    public $modules = [];
    public function __construct()
    {
        parent::__construct();

        $this->modules = [
            'title' => 'Bonus',
            'folder_path' => 'software.modules.master.bonus',
            'route' => 'bonus',
            'table_name' => (new Bonus())->getTable(),
            'permisstion_prefix' => 'bonus',
            'module_name' => 'Bonus',
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
                $modules[$value . '_permission'] = (isset($modules['company_id']) && !$modules['company_id'])
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
            // define columns
            $columns = [
                (object)['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'td_label' => 'Sr No.', 'orderable' => false, 'searchable' => false],
                (object)['data' => 'company_name', 'name' => (new Company())->getTable() . '.company_name', 'td_label' => 'Company Name', 'className' => '', 'orderable' => true, 'searchable' => false],
                (object)['data' => 'year', 'name' => (new Bonus())->getTable() . '.year', 'td_label' => 'Year', 'className' => '', 'orderable' => true, 'searchable' => false],
                (object)['data' => 'month', 'name' => (new Bonus())->getTable() . '.month', 'td_label' => 'Month', 'className' => '', 'orderable' => true, 'searchable' => false],
                (object)['data' => 'amount', 'name' => (new Bonus())->getTable() . '.amount', 'td_label' => 'Amount', 'className' => '', 'orderable' => true, 'searchable' => false],
                (object)['data' => 'branch_name', 'name' => (new Branch())->getTable() . '.name', 'td_label' => 'Branch Name', 'className' => '', 'orderable' => true, 'searchable' => false],

                (object)[
                    'data' => 'employee',
                    'name' => (new Employee())->getTable() . '.employee',
                    'td_label' => 'Employee Name',
                    'className' => '',
                    'orderable' => true,
                    'searchable' => false
                ],


                (object)['data' => 'status', 'name' => (new Bonus())->getTable() . '.status', 'td_label' => 'Status', 'className' => 'w-5 text-start', 'orderable' => true, 'searchable' => false],
                (object)['data' => 'action', 'name' => 'action', 'td_label' => 'Action', 'className' => 'w-10 text-start', 'orderable' => false, 'searchable' => false],
            ];

            if ($modules['company_id']) {
                $columns = array_filter($columns, fn($col) => $col->name !== (new Company())->getTable() . '.company_name');
                $columns = array_values($columns);
            }

            View::share("columns", $columns);

            if ($request->ajax()) {
                // build query with join for sorting/search
                $data = Bonus::select(
                    (new Bonus())->getTable() . '.*',
                    (new Company())->getTable() . '.company_name',
                    (new Branch())->getTable() . '.name as branch_name'
                )
                    ->leftJoin('companies', 'companies.id', '=', 'bonuses.company_id')
                    ->leftJoin('branches', 'branches.id', '=', 'bonuses.branch')
                    ->where(function ($q1) use ($modules, $loginUserId) {
                        if (Auth::guard('employees')->check() || !empty($modules['company_id'])) {
                            // If company_id exists in $modules, use that; otherwise use employee's company_id
                            $companyId = $modules['company_id'] ?? Auth::guard('employees')->user()->company_id;

                            $q1->where((new Bonus())->getTable() . '.company_id', $companyId);

                            // Personal data permission rule
                            if (!empty($modules['personal_data_permission']) && ($modules['all_data_permission'] == false)) {
                                $q1->where((new Bonus())->getTable() . '.created_by', $loginUserId);
                            }
                        }
                    });

                if (isset($modules['restore_permission']) && $modules['restore_permission']) {
                    $data = $data->withTrashed();
                }
                $data = $data->orderBy((new Bonus())->getTable() . '.id', 'DESC');

                $returnData = Datatables::of($data)
                    ->addIndexColumn()
                    ->filter(function ($query) use ($request) {
                        if ($request->has('filter_company') && $request->filter_company) {
                            $query->where((new Bonus())->getTable() . '.company_id', $request->filter_company);
                        }
                        if ($request->has('filter_branch') && $request->filter_branch) {
                            $query->where((new Bonus())->getTable() . '.branch', $request->filter_branch);
                        }
                          if ($request->has('filter_employee') && $request->filter_employee) {
                            $query->where((new Bonus())->getTable() . '.employee', $request->filter_employee);
                        }
                        if ($request->has('status') && $request->status !== null && $request->status !== 'all') {
                            $query->where((new Bonus())->getTable() . '.status', $request->status);
                        }
                        if ($request->has('search') && $request->search != '') {
                            $search = $request->search;
                            $query->where(function ($q) use ($search) {
                                $q->where((new Bonus())->getTable() . '.year', 'like', "%{$search}%")
                                    ->orWhere((new Bonus())->getTable() . '.month', 'like', "%{$search}%")
                                    ->orWhere((new Bonus())->getTable() . '.amount', 'like', "%{$search}%");
                            });
                        }
                    });

                foreach ($columns as $column) {
                    if (!empty($column->orderable) && $column->orderable === true) {
                        $returnData->orderColumn($column->data, "{$column->name} \$1");
                    }
                }

                $returnData = $returnData->editColumn('status', function ($row) use ($modules) {
                    $btn = '';
                    $dropdown = '<ul class="dropdown-menu">';
                    if ($row->status == "active") {
                        $btn .= '<button type="button" class="btn btn-success btn-sm  dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">' . ucfirst($row->status) . '</button>';
                        $dropdown .= '<li><a href="javascript:void(0)" class="dropdown-item update-status" data-url="' . route($modules["route"] . '.status-update') . '" data-id="' . $row->id . '" data-update_status="inactive">Inactive</a></li>';
                    } elseif ($row->status == "inactive") {
                        $btn .= '<button type="button" class="btn btn-danger btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">' . ucfirst($row->status) . '</button>';
                        $dropdown .= '<li><a href="javascript:void(0)" class="dropdown-item update-status" data-url="' . route($modules["route"] . '.status-update') . '" data-id="' . $row->id . '" data-update_status="active">Active</a></li>';
                    } else {
                        return $btn;
                    }
                    $dropdown .= '</ul>';
                    return $btn . $dropdown;
                })
                    ->editColumn('branch_name', function ($row) {
                        return $row->branch_name ?? '-';
                    })
                    ->addColumn('employee', function ($row) {
                        if ($row->employeeRelation) {
                            return $row->employeeRelation->employee_code . ' - ' . $row->employeeRelation->full_name;
                        }
                        return '-';
                    })

                    ->addColumn('action', function ($row) use ($modules) {
                        $btn = '';
                        if (!$row?->deleted_at) {
                            if ($modules['update_permission']) {
                                $btn .= '<a href="' . route($modules["route"] . ".edit", [$row->id]) . '" class="btn btn-light btn-icon mx-1"><i class="fa-solid fa-pen-to-square"></i></a>';
                            }
                            if ($modules['delete_permission']) {
                                $btn .= '<a href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . route($modules["route"] . ".destroy", [$row->id]) . '" class="btn btn-danger btn-icon deletebutton mx-1"><i class="fa-solid fa-trash"></i></a>';
                            }
                        } else {
                            $btn .= '<a href="javascript:void(0)" data-restore="' . route($modules["route"] . ".restore", ['id' => $row->id]) . '" class="btn btn-light mx-1 record-restore"><i class="ti ti-history"></i> Restore</a>';
                        }
                        return $btn ?: '-';
                    })
                    ->rawColumns(['status', 'action', 'employee'])
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
    public function create()
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

            return view($modules['folder_path'] . '.form');
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BonusRequest $request)
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
            // return $validated;
            Bonus::create($validated);

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

            $bonusQuery = Bonus::query();
            if (!empty($modules['company_id'])) {
                $bonusQuery->where('company_id', $modules['company_id']);
            }
            $edit = $bonusQuery->findOrFail($id);
            View::share('edit', $edit);

            return view($modules['folder_path'] . '.form');
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BonusRequest $request, string $id)
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
        try {

            $validated['updated_by'] = $loginUserId;
            // return $validated;
            $bonusQuery = Bonus::query();
            if (!empty($modules['company_id'])) {
                $bonusQuery->where('company_id', $modules['company_id']);
            }
            $updateData = $bonusQuery->findOrFail($id);
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

        $bonusQuery = Bonus::query();
        if (!empty($modules['company_id'])) {
            $bonusQuery->where('company_id', $modules['company_id']);
        }
        $dataDelete = $bonusQuery->findOrFail($id);
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

            $bonusQuery = Bonus::withTrashed();
            if (!empty($modules['company_id'])) {
                $bonusQuery->where('company_id', $modules['company_id']);
            }
            $restore_data = $bonusQuery->findOrFail($id);
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
            $bonusQuery = Bonus::withTrashed();
            if (!empty($modules['company_id'])) {
                $bonusQuery->where('company_id', $modules['company_id']);
            }
            $country = $bonusQuery->findOrFail($request?->id);
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
            if (!$modules['viewPermission']) {
                return $this->sendError('Unauthorized', [], [], 403);
            }
        } elseif (!$modules['viewPermission']) {
            abort(403, 'Unauthorized');
        }

        try {
            // Permissions
            $moduleName = $modules['module_name'];


            $query = Bonus::select(
                (new Bonus())->getTable() . '.*',
                (new Company())->getTable() . '.company_name',
                (new Branch())->getTable() . '.name as branch_name'
            )
                ->leftJoin('companies', 'companies.id', '=', 'bonuses.company_id')
                ->leftJoin('branches', 'branches.id', '=', 'bonuses.branch')
                ->orderBy('bonuses.id', 'DESC')
                ->where(function ($q1) use ($modules, $loginUserId) {
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


            // Company
            if ($request->has('company') && $request->company) {
                $query->where((new Bonus())->getTable() . '.company_id', $request->company);
            }
            if ($request->has('branch') && $request->branch) {
                $query->where((new Bonus())->getTable() . '.branch', $request->branch);
            }
              if ($request->has('employee_id') && $request->employee_id) {
                $query->where((new Bonus())->getTable() . '.employee', $request->employee_id);
            }
            if ($request->has('status') && $request->status !== null && $request->status !== 'all') {
                $query->where((new Bonus())->getTable() . '.status', $request->status);
            }
            if ($request->has('search') && $request->search != '') {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where((new Bonus())->getTable() . '.year', 'like', "%{$search}%")
                        ->orWhere((new Bonus())->getTable() . '.month', 'like', "%{$search}%")
                        ->orWhere((new Bonus())->getTable() . '.amount', 'like', "%{$search}%");
                });
            }


            $bonus = $query->get();


            return view($modules['folder_path'] . '.print', compact('bonus', 'company_id', 'modules'));
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

        return Excel::download(new BonusExport($request->all(), $this->authenticateLoginUserDetails, $modules), 'Bonus-' . Helper::convert_date("", "Y-m-d H:i:s", "Ymd-His") . '.xlsx');
    }
}
