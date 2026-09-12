<?php

namespace App\Http\Controllers\software;

use App\Http\Controllers\Controller;
use App\Http\Requests\BiometricMachineRequest;
use App\Models\BiometricMachine;
use App\Models\Company;
use App\Services\Biometric\BiometricServiceFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;

class BiometricMachineController extends Controller
{
    public $modules = [];

    public function __construct()
    {
        parent::__construct();

        $this->modules = [
            'title' => 'Biometric Machine',
            'folder_path' => 'software.modules.master.biometric-machine',
            'route' => 'biometric-machines',
            'table_name' => (new BiometricMachine())->getTable(),
            'permisstion_prefix' => 'biometric-machines',
            'module_name' => 'Biometric Machines',
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
            $columns = [
                (object) ['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'td_label' => 'Sr No.', 'orderable' => false, 'searchable' => false],
                (object) ['data' => 'company_name', 'name' => (new Company())->getTable() . '.company_name', 'td_label' => 'Company Name', 'className' => '', 'orderable' => true, 'searchable' => false],
                (object) ['data' => 'provider_type', 'name' => (new BiometricMachine())->getTable() . '.provider_type', 'td_label' => 'Provider', 'className' => '', 'orderable' => true, 'searchable' => false],
                (object) ['data' => 'machine_name', 'name' => (new BiometricMachine())->getTable() . '.machine_name', 'td_label' => 'Machine Name', 'className' => '', 'orderable' => true, 'searchable' => false],
                (object) ['data' => 'ip_address', 'name' => (new BiometricMachine())->getTable() . '.ip_address', 'td_label' => 'IP Address', 'className' => '', 'orderable' => true, 'searchable' => false],
                (object) ['data' => 'port', 'name' => (new BiometricMachine())->getTable() . '.port', 'td_label' => 'Port', 'className' => '', 'orderable' => true, 'searchable' => false],
                (object) ['data' => 'is_active', 'name' => (new BiometricMachine())->getTable() . '.is_active', 'td_label' => 'Active', 'className' => 'w-5 text-center', 'orderable' => true, 'searchable' => false],
                (object) ['data' => 'sync_status', 'name' => 'sync_status', 'td_label' => 'Sync Status', 'className' => 'w-10 text-center', 'orderable' => false, 'searchable' => false],
                (object) ['data' => 'status', 'name' => (new BiometricMachine())->getTable() . '.status', 'td_label' => 'Status', 'className' => 'w-5 text-start', 'orderable' => true, 'searchable' => false],
                (object) ['data' => 'action', 'name' => 'action', 'td_label' => 'Action', 'className' => 'w-10 text-start', 'orderable' => false, 'searchable' => false],
            ];

            if ($modules['company_id']) {
                $columns = array_values(array_filter($columns, fn($c) => $c->name !== (new Company())->getTable() . '.company_name'));
            }

            View::share("columns", $columns);

            if ($request->ajax()) {
                $data = BiometricMachine::query();

                if (!empty($modules['restore_permission'])) {
                    $data->withTrashed();
                }

                $biometricMachineTable = (new BiometricMachine())->getTable();
                $companyTable = (new Company())->getTable();

                $data->select($biometricMachineTable . '.*', $companyTable . '.company_name as company_name')
                    ->leftJoin($companyTable, $companyTable . '.id', '=', $biometricMachineTable . '.company_id')
                    ->where(function ($q1) use ($modules, $loginUserId, $biometricMachineTable) {
                        if (Auth::guard('employees')->check() || !empty($modules['company_id'])) {
                            // If company_id exists in $modules, use that; otherwise use employee's company_id
                            $companyId = $modules['company_id'] ?? Auth::guard('employees')->user()->company_id;

                            $q1->where($biometricMachineTable . '.company_id', $companyId);

                            // Personal data permission rule
                            if (!empty($modules['personal_data_permission']) && ($modules['all_data_permission'] == false)) {
                                $q1->where($biometricMachineTable . '.created_by', $loginUserId);
                            }
                        }
                    });

                $data = $data->orderBy($biometricMachineTable . '.created_at', 'desc');
                $returnData = Datatables::of($data)
                    ->addIndexColumn()
                    ->filter(function ($query) use ($request) {
                        $biometricMachineTable = (new BiometricMachine())->getTable();

                        if ($request->filled('filter_company')) {
                            $query->where($biometricMachineTable . '.company_id', $request->input('filter_company'));
                        }

                        if ($request->has('status') && $request->status !== null && $request->status !== 'all') {
                            $query->where($biometricMachineTable . '.status', $request->status);
                        }

                        if ($request->has('provider_type') && $request->provider_type !== null && $request->provider_type !== 'all') {
                            $query->where($biometricMachineTable . '.provider_type', $request->provider_type);
                        }

                        if ($request->has('search')) {
                            $query->where(function ($q) use ($request, $biometricMachineTable) {
                                $q->where($biometricMachineTable . '.machine_name', 'like', "%" . $request->search . "%")
                                    ->orWhere($biometricMachineTable . '.ip_address', 'like', "%" . $request->search . "%")
                                    ->orWhere($biometricMachineTable . '.port', 'like', "%" . $request->search . "%");
                            });
                        }
                    });

                foreach ($columns as $column) {
                    if (!empty($column->orderable) && $column->orderable === true) {
                        $returnData->orderColumn($column->data, "{$column->name} \$1");
                    }
                }

                $returnData = $returnData
                    ->editColumn('provider_type', function ($row) {
                        $providerType = $row->provider_type ?? 'minop';
                        $isPullBased = in_array($providerType, ['etimeoffice', 'mintra', 'old_crm']);
                        $badgeClass = $isPullBased ? 'bg-info' : 'bg-primary';
                        $integrationType = $isPullBased ? 'Pull' : 'Push';
                        $providerName = ucfirst($providerType);

                        return '<span class="badge ' . $badgeClass . '">' . $providerName . '</span> ' .
                            '<small class="text-muted">(' . $integrationType . ')</small>';
                    })
                    ->editColumn('is_active', function ($row) use ($modules) {
                        $isActive = $row->is_active ?? false;
                        $btnClass = $isActive ? 'btn-success' : 'btn-secondary';
                        $btnText = $isActive ? 'Active' : 'Inactive';
                        $icon = $isActive ? 'ti-check' : 'ti-x';

                        return '<button type="button" class="btn btn-sm ' . $btnClass . ' toggle-active-btn" ' .
                            'data-machine-id="' . $row->id . '" ' .
                            'data-is-active="' . ($isActive ? '1' : '0') . '" ' .
                            'title="Click to ' . ($isActive ? 'deactivate' : 'activate') . '">' .
                            '<i class="ti ' . $icon . '"></i> ' . $btnText .
                            '</button>';
                    })
                    ->editColumn('sync_status', function ($row) {
                        $providerType = $row->provider_type ?? 'minop';
                        $isPullBased = in_array($providerType, ['etimeoffice', 'mintra', 'old_crm']);

                        if (!$isPullBased) {
                            return '<span class="text-muted">N/A</span>';
                        }

                        $lastSyncAt = $row->last_sync_at ?? null;
                        $lastSyncStatus = $row->last_sync_status ?? null;
                        $lastSyncError = $row->last_sync_error ?? null;

                        $html = '<div class="text-center">';

                        // Sync status badge
                        if ($lastSyncStatus === 'success') {
                            $html .= '<span class="badge bg-success mb-1">Success</span><br>';
                        } elseif ($lastSyncStatus === 'failed') {
                            $html .= '<span class="badge bg-danger mb-1">Failed</span><br>';
                        } elseif ($lastSyncStatus === 'pending') {
                            $html .= '<span class="badge bg-warning mb-1">Pending</span><br>';
                        } else {
                            $html .= '<span class="badge bg-secondary mb-1">Never</span><br>';
                        }

                        // Last sync time
                        if ($lastSyncAt) {
                            $syncTime = \Carbon\Carbon::parse($lastSyncAt);
                            $html .= '<small class="text-muted" title="' . $syncTime->format('Y-m-d H:i:s') . '">' .
                                $syncTime->diffForHumans() . '</small>';
                        } else {
                            $html .= '<small class="text-muted">Never synced</small>';
                        }

                        // Error message tooltip
                        if ($lastSyncError) {
                            $html .= '<br><small class="text-danger" title="' . htmlspecialchars($lastSyncError) . '">' .
                                '<i class="ti ti-alert-circle"></i> Error</small>';
                        }

                        $html .= '</div>';

                        return $html;
                    })
                    ->editColumn('status', function ($row) use ($modules) {
                        $btn = '';
                        $dropdown = "";
                        $dropdown .= '<ul class="dropdown-menu" style="">';

                        if ($row->status == "active") {
                            $btn .= '<button type="button" class="btn btn-success btn-sm  dropdown-toggle waves-effect waves-light" data-bs-toggle="dropdown" aria-expanded="false">' . ucfirst($row->status) . '</button>';
                            $dropdown .= '<li><a href="javascript:void(0)" class="dropdown-item waves-effect btn-label-danger update-status" data-url="' . route($modules['route'] . '.status-update') . '" data-id="' . $row["id"] . '"  data-update_status="inactive">Inactive</a></li>';
                        } elseif ($row->status == "inactive") {
                            $btn .= '<button type="button" class="btn btn-danger btn-sm dropdown-toggle waves-effect waves-light" data-bs-toggle="dropdown" aria-expanded="false">' . ucfirst($row->status) . '</button>';
                            $dropdown .= '<li><a href="javascript:void(0)" class="dropdown-item waves-effect btn-label-success update-status" data-url="' . route($modules['route'] . '.status-update') . '" data-id="' . $row["id"] . '"  data-update_status="active">Active</a></li>';
                        } else {
                            return $btn;
                        }
                        $dropdown .= '</ul>';
                        $btn .= $dropdown;
                        return $btn;
                    })
                    ->editColumn('action', function ($row) use ($modules) {
                        $btn = '';
                        $providerType = $row->provider_type ?? 'minop';
                        $isPullBased = in_array($providerType, ['etimeoffice', 'mintra', 'old_crm']);
                        $isActive = $row->is_active ?? false;

                        // Check credentials based on auth type
                        $hasCredentials = false;
                        if ($isPullBased) {
                            $machine = BiometricMachine::find($row->id);
                            if ($machine) {
                                $hasCredentials = $machine->hasRequiredCredentials();
                            }
                        }

                        if (!$row?->deleted_at) {
                            // Sync button (only for pull-based providers)
                            if ($isPullBased && $isActive && $hasCredentials) {
                                $btn .= '<button type="button" class="btn btn-info btn-sm sync-attendance-btn mx-1" ' .
                                    'data-machine-id="' . $row->id . '" ' .
                                    'title="Sync Attendance Now">' .
                                    '<i class="ti ti-refresh"></i></button>';
                            }

                            // Test connection button (only for pull-based providers)
                            if ($isPullBased) {
                                $btn .= '<button type="button" class="btn btn-warning btn-sm test-connection-btn mx-1" ' .
                                    'data-machine-id="' . $row->id . '" ' .
                                    'title="Test Connection">' .
                                    '<i class="ti ti-plug"></i></button>';
                            }

                            // Edit button
                            if (!empty($modules['update_permission'])) {
                                $btn .= '<a href="' . route($modules["route"] . ".edit", [$row->id]) . '" class="btn btn-light btn-icon mx-1" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>';
                            }

                            // Delete button
                            if (!empty($modules['delete_permission'])) {
                                $btn .= '<a href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . route($modules["route"] . ".destroy", [$row->id]) . '" class="btn btn-danger btn-icon deletebutton mx-1" title="Delete"><i class="fa-solid fa-trash"></i></a>';
                            }
                        } else {
                            $btn .= '<a href="javascript:void(0)" data-restore="' . route($modules["route"] . ".restore", ['id' => $row->id]) . '" class="btn btn-light mx-1 record-restore"><i class="ti ti-history"></i> Restore</a>';
                        }
                        return $btn ?: '-';
                    })
                    ->rawColumns(['action', 'status', 'provider_type', 'is_active', 'sync_status']);

                return $returnData->make(true);
            }

            return view($modules['folder_path'] . '.index');
        } catch (\Exception $e) {
            return Redirect::back()->withErrors($e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
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
        return view($modules['folder_path'] . '.form');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BiometricMachineRequest $request)
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
            $data = $request->validated();
            $data['created_by'] = $loginUserId;

            // Set default provider_type if not provided
            if (empty($data['provider_type'])) {
                $data['provider_type'] = 'minop';
            }

            // Set default auth_type if not provided (for pull-based providers)
            $isPullBased = in_array($data['provider_type'] ?? 'minop', ['etimeoffice', 'mintra', 'old_crm']);
            if ($isPullBased && empty($data['auth_type'])) {
                $data['auth_type'] = 'basic';
            }


            // Handle empty password/credentials - remove from data if empty
            if (empty($data['api_password'])) {
                unset($data['api_password']);
            }
            if (empty($data['bearer_token'])) {
                unset($data['bearer_token']);
            }
            if (empty($data['api_key_value'])) {
                unset($data['api_key_value']);
            }

            BiometricMachine::create($data);
            return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' created successfully');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
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
            abort(403, 'Unauthorized');
        }

        View::share('modules', $modules);
        $machineQuery = BiometricMachine::query();
        if (!empty($modules['company_id'])) {
            $machineQuery->where('company_id', $modules['company_id']);
        }
        $edit = $machineQuery->findOrFail($id);
        View::share('edit', $edit);
        return view($modules['folder_path'] . '.form');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BiometricMachineRequest $request, string $id)
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
            abort(403, 'Unauthorized');
        }

        try {
            $machineQuery = BiometricMachine::query();
            if (!empty($modules['company_id'])) {
                $machineQuery->where('company_id', $modules['company_id']);
            }
            $updateData = $machineQuery->findOrFail($id);
            $data = $request->validated();
            $data['updated_by'] = $loginUserId;


            // Handle empty credentials - don't update if empty (keep existing encrypted values)
            if (empty($data['api_password'])) {
                unset($data['api_password']);
            }
            if (empty($data['bearer_token'])) {
                unset($data['bearer_token']);
            }
            if (empty($data['api_key_value'])) {
                unset($data['api_key_value']);
            }

            $updateData->update($data);

            return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' updated successfully');
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

        $machineQuery = BiometricMachine::query();
        if (!empty($modules['company_id'])) {
            $machineQuery->where('company_id', $modules['company_id']);
        }
        $dataDelete = $machineQuery->findOrFail($id);
        $isAjax = ($request->ajax()) ? true : false;

        try {
            if ($dataDelete) {
                $dataDelete->update(['deleted_by' => $loginUserId]);
                if ($dataDelete->delete()) {
                    if ($isAjax) {
                        return $this->sendResponse([], $modules['title'] . ' deleted successfully');
                    }
                    return true;
                }
            }
            if ($isAjax) {
                return $this->sendResponse([], "Something went wrong please try again later");
            }
            return false;
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
        $loginUserId = ($modules['authLoginUserDetail'] && $modules['authLoginUserDetail']?->id) ? $modules['authLoginUserDetail']?->id : null;

        if (count(config('constants.permissions'))) {
            foreach (config('constants.permissions') as $key => $value) {
                $modules[$value . '_permission'] = (isset($modules['company_id']) && !$modules['company_id']) ? true : Gate::check('hasPermission', [$value, $modules['module_name']]);
            }
        }

        if (!$modules['update_permission']) {
            if ($isAjax) {
                return $this->sendError('Unauthorized', [], [], 403);
            }
            abort(403, 'Unauthorized');
        }

        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required',
                'update_status' => 'required|in:active,inactive',
            ]);

            if ($validator->fails()) {
                if ($isAjax) {
                    return $this->sendError($validator->messages()->first(), $validator->messages(), [], 422);
                }
                return Redirect::back()->withErrors($validator);
            }

            $machineQuery = BiometricMachine::query();
            if (!empty($modules['company_id'])) {
                $machineQuery->where('company_id', $modules['company_id']);
            }
            $data = $machineQuery->findOrFail($request->id);
            $data->status = $request->update_status;
            $data->updated_by = $loginUserId;
            $data->save();

            if ($isAjax) {
                return $this->sendResponse([], $modules['title'] . ' status updated successfully');
            }
            return Redirect::back()->withSuccess($modules['title'] . ' status updated successfully');
        } catch (\Exception $e) {
            if ($isAjax) {
                return $this->sendError($e->getMessage(), [], [], 500);
            }
            return Redirect::back()->withErrors($e->getMessage());
        }
    }

    /**
     * Test connection to biometric provider API
     */
    public function testConnection(Request $request, $id)
    {
        $modules = $this->modules;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;

        try {
            $machineQuery = BiometricMachine::query();
            if (!empty($modules['company_id'])) {
                $machineQuery->where('company_id', $modules['company_id']);
            }

            // If id is 0, this is a test from form (before save)
            if ($id == 0 || $id == '0') {
                // Validate request data based on auth type
                $authType = $request->input('auth_type', 'basic');
                $rules = [
                    'provider_type' => 'required|in:etimeoffice,mintra,old_crm',
                    'api_url' => 'required|url',
                    'auth_type' => 'required|in:basic,bearer_token,api_key,custom',
                ];

                if ($authType === 'basic') {
                    $rules['api_username'] = 'required|string';
                    $rules['api_password'] = 'nullable|string'; // Optional
                    if ($request->provider_type === 'etimeoffice') {
                        $rules['corporate_id'] = 'required|string';
                    }
                } elseif ($authType === 'bearer_token') {
                    $rules['bearer_token'] = 'required|string';
                } elseif ($authType === 'api_key') {
                    $rules['api_key_name'] = 'required|string';
                    $rules['api_key_value'] = 'required|string';
                } elseif ($authType === 'custom') {
                    $rules['custom_headers'] = 'required|string';
                }

                $validator = Validator::make($request->all(), $rules);

                if ($validator->fails()) {
                    return $this->sendError($validator->messages()->first(), $validator->messages(), [], 422);
                }

                // Create temporary machine object for testing
                $machine = new BiometricMachine();
                $machine->provider_type = $request->provider_type;
                $machine->api_url = $request->api_url;
                $machine->auth_type = $authType;

                if ($authType === 'basic') {
                    $machine->api_username = $request->api_username;
                    $machine->api_password = $request->api_password ?? ''; // Will be encrypted by mutator
                    $machine->corporate_id = $request->corporate_id;
                } elseif ($authType === 'bearer_token') {
                    $machine->bearer_token = $request->bearer_token;
                } elseif ($authType === 'api_key') {
                    $machine->api_key_name = $request->api_key_name;
                    $machine->api_key_value = $request->api_key_value; // Will be encrypted by mutator
                } elseif ($authType === 'custom') {
                    $machine->custom_headers = $request->custom_headers;
                }
            } else {
                $machine = $machineQuery->findOrFail($id);
            }

            // Check if provider is pull-based
            if (!$machine->isPullBasedProvider()) {
                return $this->sendError('Test connection is only available for pull-based providers (eTimeOffice, Mintra)', [], [], 400);
            }

            // Get service and test connection
            $service = BiometricServiceFactory::make($machine->provider_type, $machine);
            $result = $service->testConnection();

            if ($result['success']) {
                return $this->sendResponse([], $result['message']);
            } else {
                return $this->sendError($result['message'], [], [], 400);
            }
        } catch (\Exception $e) {
            Log::error('Test connection failed', [
                'machine_id' => $id,
                'error' => $e->getMessage(),
            ]);
            return $this->sendError('Connection test failed: ' . $e->getMessage(), [], [], 500);
        }
    }

    /**
     * Sync attendance from biometric provider
     */
    public function syncAttendance(Request $request, $id)
    {
        $modules = $this->modules;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;

        try {
            $machineQuery = BiometricMachine::query();
            if (!empty($modules['company_id'])) {
                $machineQuery->where('company_id', $modules['company_id']);
            }
            $machine = $machineQuery->findOrFail($id);

            // Check if provider is pull-based
            if (!$machine->isPullBasedProvider()) {
                return $this->sendError('Sync is only available for pull-based providers (eTimeOffice, Mintra)', [], [], 400);
            }

            // Check if machine is active
            if (!$machine->is_active) {
                return $this->sendError('Machine must be active to sync attendance', [], [], 400);
            }

            // Check if credentials are set based on auth type
            $authType = $machine->auth_type ?? 'basic';
            $hasCredentials = false;

            if ($authType === 'basic') {
                $hasCredentials = !empty($machine->api_url) && !empty($machine->api_username);
            } elseif ($authType === 'bearer_token') {
                $hasCredentials = !empty($machine->api_url) && !empty($machine->bearer_token);
            } elseif ($authType === 'api_key') {
                $hasCredentials = !empty($machine->api_url) && !empty($machine->api_key_name) && !empty($machine->getRawApiKeyValueAttribute());
            } elseif ($authType === 'custom') {
                $hasCredentials = !empty($machine->api_url) && !empty($machine->custom_headers);
            }

            if (!$hasCredentials) {
                return $this->sendError('API credentials are required for sync', [], [], 400);
            }

            // Update sync status to pending
            $machine->update([
                'last_sync_status' => 'pending',
                'last_sync_error' => null,
            ]);

            // Get service and fetch attendance
            $service = BiometricServiceFactory::make($machine->provider_type, $machine);

            // Fetch attendance for last 7 days or since last sync
            $endDate = Carbon::now();
            $startDate = $machine->last_sync_at
                ? Carbon::parse($machine->last_sync_at)->subDay() // Start from day before last sync
                : Carbon::now()->subDays(7); // Default to last 7 days

            $attendanceRecords = $service->fetchAttendance($startDate, $endDate);

            // Process and store attendance records
            // TODO: Implement attendance processing logic (similar to MinopAttendanceController)
            // For now, just update sync status
            $successCount = count($attendanceRecords);

            $machine->update([
                'last_sync_at' => now(),
                'last_sync_status' => 'success',
                'last_sync_error' => null,
            ]);

            return $this->sendResponse([
                'records_fetched' => $successCount,
            ], "Sync completed successfully. Fetched {$successCount} attendance records.");

        } catch (\Exception $e) {
            Log::error('Sync attendance failed', [
                'machine_id' => $id,
                'error' => $e->getMessage(),
            ]);

            // Update sync status to failed
            if (isset($machine)) {
                $machine->update([
                    'last_sync_status' => 'failed',
                    'last_sync_error' => $e->getMessage(),
                ]);
            }

            return $this->sendError('Sync failed: ' . $e->getMessage(), [], [], 500);
        }
    }

    /**
     * Toggle active status of machine
     */
    public function toggleActive(Request $request, $id)
    {
        $modules = $this->modules;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;

        try {
            $machineQuery = BiometricMachine::query();
            if (!empty($modules['company_id'])) {
                $machineQuery->where('company_id', $modules['company_id']);
            }
            $machine = $machineQuery->findOrFail($id);

            $newActiveStatus = !($machine->is_active ?? false);


            $machine->update(['is_active' => $newActiveStatus]);

            $statusText = $newActiveStatus ? 'activated' : 'deactivated';
            return $this->sendResponse([], "Machine {$statusText} successfully");

        } catch (\Exception $e) {
            Log::error('Toggle active failed', [
                'machine_id' => $id,
                'error' => $e->getMessage(),
            ]);
            return $this->sendError('Failed to update status: ' . $e->getMessage(), [], [], 500);
        }
    }
}

