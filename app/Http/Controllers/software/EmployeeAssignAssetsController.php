<?php

namespace App\Http\Controllers\software;

use App\Exports\EmployeeAsignAssetsExport;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeAsignAssetsRequest;
use App\Models\AssetsAllocationMaster;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeAsignAssets;
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

class EmployeeAssignAssetsController extends Controller
{

    public $modules = [];
    public function __construct()
    {
        parent::__construct();

        $this->modules = [
            'title' => 'Employee Assign Assets',
            'folder_path' => 'software.modules.employee.employee-assign-assets',
            'route' => 'employee-assign-assets',
            'table_name' => (new EmployeeAsignAssets())->getTable(),
            'permisstion_prefix' => 'employee-assign-assets',
            'module_name' => 'Assign Assets',
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
                // (object)[ 'data' => "id", 'name' => 'id', 'td_label' => 'Id' ],
                (object)['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'td_label' => 'Sr No.', 'orderable' => false, 'searchable' => false],

                (object)['data' => "company_name", 'name' => 'company_id', 'td_label' => 'Company Name', 'className' =>  ''],
                (object)['data' => "employee_code", 'name' => 'employee_code', 'td_label' => 'Employee Code', 'className' => 'w-10 text-start'],
                (object)['data' => "employee_full_name", 'name' => 'employee_id', 'td_label' => 'Employee Name', 'className' => 'w-10 text-start'],
                (object)['data' => "assets_name", 'name' => 'assets_id', 'td_label' => 'Assets Name', 'className' => 'w-10 text-start'],
                (object)['data' => "date", 'name' => 'date', 'td_label' => 'Date', 'className' => 'w-10 text-start'],
                (object)['data' => "reference_no", 'name' => 'reference_no', 'td_label' => 'Reference No', 'className' => 'w-15 text-start'],
                (object)['data' => "attachment", 'name' => 'attachment', 'td_label' => 'Attachment', 'className' => 'w-10 text-start'],
                (object)['data' => "descrption", 'name' => 'descrption', 'td_label' => 'Description', 'className' => 'w-20 text-start text-wrap'],
                (object)['data' => "status", 'name' => 'status', 'td_label' => 'Status', 'className' =>  'w-5 text-start'],
                (object)['data' => "action", 'name' => 'action', 'td_label' => 'Action', 'orderable' => false, 'searchable' => false, 'className' =>  'w-10 text-start'],
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
                $data = EmployeeAsignAssets::with(['company', 'assets', 'employee'])
                    ->when(Auth::guard('employees')->check() || !empty($modules['company_id']), function ($query) use ($modules, $loginUserId) {

                        // If company_id exists in $modules, use that; otherwise use employee's company_id
                        $companyId = $modules['company_id'] ?? Auth::guard('employees')->user()->company_id;

                        $query->where($modules['table_name'] . '.company_id', $companyId);

                        // Personal data permission rule
                        if ($modules['personal_data_permission'] && $modules['all_data_permission'] == false) {
                            $query->where($modules['table_name'] . '.created_by', $loginUserId);
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

                        // Filter by Employee Code
                        if ($request->has('filter_employee_code') && $request->filter_employee_code) {
                            $query->whereHas('employee', function ($q) use ($request) {
                                $q->where('employee_code', 'like', '%' . $request->filter_employee_code . '%');
                            });
                        }

                        $query->when($request->filled('search'), function ($q) use ($request) {
                            $q->whereHas('assets', function ($q2) use ($request) {
                                $q2->where('name', 'like', '%' . $request->search . '%');
                            });
                        });
                        if ($request->has('status') && $request->status !== null && $request->status !== 'all') {
                            $query->where((new EmployeeAsignAssets())->getTable() . '.status', $request->status);
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

                            $tableColumn = (new EmployeeAsignAssets())->getTable() . '.date';

                            if ($fromDate && $toDate) {
                                $query->whereBetween($tableColumn, [$fromDate, $toDate]);
                            } elseif ($fromDate) {
                                $query->where($tableColumn, $fromDate);
                            }
                        }
                        // dd(
                        //     'tax-list 163',
                        //     $query->toSql(),
                        //     $query->getBindings()
                        // );
                    })

                    ->addColumn('company_name', function ($row) {
                        if ($row->company) {
                            return $row?->company?->company_name;
                        }
                        return '-';
                    })
                    ->addColumn('employee_code', function ($row) {
                        return $row->employee?->employee_code ?? '-';
                    })
                    ->addColumn('employee_full_name', function ($row) {
                        if ($row->employee) {
                            return $row?->employee?->full_name;
                        }
                        return '-';
                    })
                    ->addColumn('assets_name', function ($row) {
                        if ($row->assets) {
                            return $row?->assets?->name;
                        }
                        return '-';
                    })
                    ->editColumn('attachment', function ($row) {
                        if ($row->attachment) {
                            $url = asset($row->attachment);
                            return '<img src="' . $url . '" alt="Attachment" style="width:80px; height:auto;" />';
                        }
                        return '-';
                    })
                    ->editColumn('date', function ($row) {
                        if ($row->date) {
                            return \Carbon\Carbon::parse($row->date)->format('d/m/Y');
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
    public function store(EmployeeAsignAssetsRequest $request)
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
            if ($request->hasFile('attachment')) {
                $company = Company::find($validated['company_id']);
                $company_name = $company->company_name ?? 'default-company';
                $company_slug = Str::slug($company_name);

                $file = $request->file('attachment');
                $extension = $file->getClientOriginalExtension();
                $image_name = Helper::make_slug($validated['company_id'] . ' ' . str_replace(" ", "_", $validated['employee_id']) . " ") . date('Ymd-His');
                $filename = $image_name . '.' . $extension;


                $year = now()->format('Y');
                $month = now()->format('m');
                $folder = "uploads/" . $validated['company_id'] . "-" . $company_slug . "/EmployeeAssets/{$year}-{$month}" . "/";

                $sub_folder_path = $folder;
                $uploadedPath = public_path($sub_folder_path);
                //return $uploadedPath;
                if ($file->move($uploadedPath, $filename)) {
                    $uploadedImage = $sub_folder_path . $filename;
                    if (in_array($extension, ["jpeg", "png", "jpg"])) {
                        $webP = Helper::existingImageConverToWebp($extension, $uploadedPath, $filename, $image_name, 80, false);
                        if ($webP) {
                            $uploadedImage = $sub_folder_path . $webP;
                        }
                    }
                    $validated['attachment'] = $uploadedImage;
                }
            }
            // return $validated;

            EmployeeAsignAssets::create($validated);

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

            $eaaQuery = EmployeeAsignAssets::query();
            if (!empty($modules['company_id'])) {
                $eaaQuery->where('company_id', $modules['company_id']);
            }
            $edit = $eaaQuery->findOrFail($id);
            View::share('edit', $edit);

            return view($modules['folder_path'] . '.form');
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(EmployeeAsignAssetsRequest $request, string $id)
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

            $eaaQuery = EmployeeAsignAssets::query();
            if (!empty($modules['company_id'])) {
                $eaaQuery->where('company_id', $modules['company_id']);
            }
            $updateData = $eaaQuery->findOrFail($id);

            // Handle attachment upload like in store
            if ($request->hasFile('attachment')) {
                $company = Company::find($validated['company_id']);
                $company_name = $company->company_name ?? 'default-company';
                $company_slug = Str::slug($company_name);

                $file = $request->file('attachment');
                $extension = $file->getClientOriginalExtension();
                $image_name = Helper::make_slug($validated['company_id'] . ' ' . str_replace(" ", "_", $validated['employee_id']) . " ") . date('Ymd-His');
                $filename = $image_name . '.' . $extension;

                $year = now()->format('Y');
                $month = now()->format('m');
                $folder = "uploads/" . $validated['company_id'] . "-" . $company_slug . "/EmployeeAssets/{$year}-{$month}/";

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

                    $validated['attachment'] = $uploadedImage;
                }
            }

            // Update record
            unset($validated['id']);
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

        $eaaQuery = EmployeeAsignAssets::query();
        if (!empty($modules['company_id'])) {
            $eaaQuery->where('company_id', $modules['company_id']);
        }
        $dataDelete = $eaaQuery->findOrFail($id);
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

            $eaaQuery = EmployeeAsignAssets::withTrashed();
            if (!empty($modules['company_id'])) {
                $eaaQuery->where('company_id', $modules['company_id']);
            }
            $restore_data = $eaaQuery->findOrFail($id);
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
            $eaaQuery = EmployeeAsignAssets::withTrashed();
            if (!empty($modules['company_id'])) {
                $eaaQuery->where('company_id', $modules['company_id']);
            }
            $country = $eaaQuery->findOrFail($request?->id);
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

            // Permissions
            $moduleName = $modules['module_name'];
            $query = EmployeeAsignAssets::with(['company', 'assets', 'employee'])->orderByDesc('id');

            $query->where(function ($q1) use ($modules, $loginUserId) {
                if (Auth::guard('employees')->check() || !empty($modules['company_id'])) {
                    // If company_id exists in $modules, use that; otherwise use employee's company_id
                    $companyId = $modules['company_id'] ?? Auth::guard('employees')->user()->company_id;

                    $q1->where($modules['table_name'] . '.company_id', $companyId);

                    // Personal data permission rule
                    if ($modules['personal_data_permission'] && $modules['all_data_permission'] == false) {
                        $q1->where($modules['table_name'] . '.created_by', $loginUserId);
                    }
                }
            });

            $companyId = $request->input('company') ?? $request->input('filter_company');
            if (!empty($companyId)) {
                $query->where('company_id', $companyId);
            } elseif ($modules['company_id']) {
                $query->where('company_id', $modules['company_id']);
            }

            $employeeId = $request->input('employee') ?? $request->input('filter_employee');
            if (!empty($employeeId)) {
                $query->where('employee_id', $employeeId);
            }

            if ($request->filled('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            if ($request->filled('search')) {
                $search = trim($request->search);
                $query->whereHas('assets', function ($assetQuery) use ($search) {
                    $assetQuery->where('name', 'like', "%{$search}%");
                });
            }

            if (!empty($request->filter_date)) {
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
                    $query->whereBetween('date', [$fromDate, $toDate]);
                } elseif ($fromDate) {
                    $query->where('date', $fromDate);
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

        return Excel::download(new EmployeeAsignAssetsExport($request->all(), $this->authenticateLoginUserDetails, $modules), 'Employee Assign Assets-' . Helper::convert_date("", "Y-m-d H:i:s", "Ymd-His") . '.xlsx');
    }
}
