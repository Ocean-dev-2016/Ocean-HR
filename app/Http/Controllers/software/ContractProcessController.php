<?php

namespace App\Http\Controllers\software;

use App\Exports\ContractProcessExport;
use App\Http\Requests\ContractProcessRequest;
use App\Models\ContractProcess;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;


class ContractProcessController extends Controller
{
    public $modules = [];
    public function __construct()
    {
        parent::__construct();

        $this->modules = [
            'title' => 'Contract Process Master',
            'folder_path' => 'software.modules.master.contract_process',
            'route' => 'contract-processes',
            'table_name' => (new ContractProcess())->getTable(),
            'permisstion_prefix' => 'Contract Process Master',
            'module_name' => 'Contract Process Master',
            'company_id' => ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null,
        ];
    }

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
                (object) ['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'td_label' => 'Sr No.', 'className' => 'w-5 text-start', 'orderable' => false, 'searchable' => false],
                (object) ['data' => "company.company_name", 'name' => 'company_id', 'td_label' => 'Company Name', 'className' => ''],
                (object) ['data' => "name", 'name' => 'name', 'td_label' => 'Name', 'className' => ''],
                (object) ['data' => "rate", 'name' => 'rate', 'td_label' => 'Rate', 'className' => ''],
                (object) ['data' => "status", 'name' => 'status', 'td_label' => 'Status', 'className' => 'w-5 text-start'],
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
                $data = ContractProcess::select('*')
                    ->where(function ($query) use ($modules, $loginUserId) {
                        if (Auth::guard('employees')->check() || !empty($modules['company_id'])) {
                            $companyId = $modules['company_id'] ?? Auth::guard('employees')->user()->company_id;
                            $query->where((new ContractProcess())->getTable() . '.company_id', $companyId);

                            if ($modules['personal_data_permission'] && $modules['all_data_permission'] == false) {
                                $query->where((new ContractProcess())->getTable() . '.created_by', $loginUserId);
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
                        if ($request->has('status') && $request->status !== null && $request->status !== 'all') {
                            $query->where('status', $request->status);
                        }
                        if ($request->has('search')) {
                            $query->where('name', 'like', "%" . $request->search . "%");
                        }
                    })
                    ->editColumn('status', function ($row) use ($modules) {
                        $btn = '';
                        $dropdown = '<ul class="dropdown-menu">';
                        if ($row->status == "active") {
                            $btn .= '<button type="button" class="btn btn-success btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">' . ucfirst($row->status) . '</button>';
                            $dropdown .= '<li><a href="javascript:void(0)" class="dropdown-item update-status" data-url="' . route($modules["route"] . '.status-update') . '" data-id="' . $row->id . '" data-update_status="inactive">Inactive</a></li>';
                        } elseif ($row->status == "inactive") {
                            $btn .= '<button type="button" class="btn btn-danger btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">' . ucfirst($row->status) . '</button>';
                            $dropdown .= '<li><a href="javascript:void(0)" class="dropdown-item update-status" data-url="' . route($modules["route"] . '.status-update') . '" data-id="' . $row->id . '" data-update_status="active">Active</a></li>';
                        }
                        $dropdown .= '</ul>';
                        return $btn . $dropdown;
                    })
                    ->addColumn('action', function ($row) use ($modules) {
                        $btn = '';
                        if (!$row?->deleted_at) {
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

    public function create()
    {
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;

        if (count(config('constants.permissions'))) {
            foreach (config('constants.permissions') as $key => $value) {
                $modules[$value . '_permission'] = (isset($modules['company_id']) && !$modules['company_id']) ? true : Gate::check('hasPermission', [$value, $modules['module_name']]);
            }
        }

        if (!$modules['add_permission']) {
            abort(403, 'Unauthorized');
        }

        try {
            View::share('modules', $modules);
            return view($modules['folder_path'] . '.form');
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    public function store(ContractProcessRequest $request)
    {
        $modules = $this->modules;
        $loginUserId = ($this->authenticateLoginUserDetails && $this->authenticateLoginUserDetails?->id) ? $this->authenticateLoginUserDetails?->id : null;

        $validated = $request->validated();

        try {
            $validated['created_by'] = $loginUserId;
            ContractProcess::create($validated);

            return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' created successfully');
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    public function edit(string $id)
    {
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;

        if (count(config('constants.permissions'))) {
            foreach (config('constants.permissions') as $key => $value) {
                $modules[$value . '_permission'] = (isset($modules['company_id']) && !$modules['company_id']) ? true : Gate::check('hasPermission', [$value, $modules['module_name']]);
            }
        }

        if (!$modules['update_permission']) {
            abort(403, 'Unauthorized');
        }

        try {
            View::share('modules', $modules);
            $contractProcessQuery = ContractProcess::query();
            if (!empty($modules['company_id'])) {
                $contractProcessQuery->where('company_id', $modules['company_id']);
            }
            $edit = $contractProcessQuery->findOrFail($id);
            View::share('edit', $edit);

            return view($modules['folder_path'] . '.form');
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    public function update(ContractProcessRequest $request, string $id)
    {
        $modules = $this->modules;
        $loginUserId = ($this->authenticateLoginUserDetails && $this->authenticateLoginUserDetails?->id) ? $this->authenticateLoginUserDetails?->id : null;

        $validated = $request->validated();
        try {
            $validated['updated_by'] = $loginUserId;
            $contractProcessQuery = ContractProcess::query();
            if (!empty($modules['company_id'])) {
                $contractProcessQuery->where('company_id', $modules['company_id']);
            }
            $updateData = $contractProcessQuery->findOrFail($id);
            $updateData->update($validated);

            return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' updated successfully');
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    public function destroy(Request $request, string $id)
    {
        $modules = $this->modules;
        $loginUserId = ($this->authenticateLoginUserDetails && $this->authenticateLoginUserDetails?->id) ? $this->authenticateLoginUserDetails?->id : null;

        $contractProcessQuery = ContractProcess::query();
        if (!empty($modules['company_id'])) {
            $contractProcessQuery->where('company_id', $modules['company_id']);
        }
        $dataDelete = $contractProcessQuery->findOrFail($id);

        try {
            if ($dataDelete) {
                $dataDelete->update(['deleted_by' => $loginUserId]);
                if ($dataDelete->delete()) {
                    if ($request->ajax()) {
                        return $this->sendResponse([], $modules['title'] . ' deleted successfully');
                    }
                    return true;
                }
            }
            return false;
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    public function restore($id)
    {
        $modules = $this->modules;
        try {
            $contractProcessQuery = ContractProcess::withTrashed();
            if (!empty($modules['company_id'])) {
                $contractProcessQuery->where('company_id', $modules['company_id']);
            }
            $restore_data = $contractProcessQuery->findOrFail($id);
            $restore_data->restore();

            return Redirect::back()->withSuccess($modules['title'] . ' restored successfully!');
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    public function status_update(Request $request)
    {
        $modules = $this->modules;
        $validator = Validator::make($request->all(), [
            'id' => ['required', Rule::exists($modules['table_name'], 'id')],
            'update_status' => ['required', 'in:active,inactive']
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->messages()->first(), $validator->messages(), [], 401);
        }

        try {
            $contractProcessQuery = ContractProcess::withTrashed();
            if (!empty($modules['company_id'])) {
                $contractProcessQuery->where('company_id', $modules['company_id']);
            }
            $contractProcess = $contractProcessQuery->findOrFail($request->id);
            $contractProcess->status = $request->update_status;
            $contractProcess->save();

            if ($request->ajax()) {
                return $this->sendResponse($contractProcess, $modules['title'] . ' status updated successfully.');
            }
            return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' status updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), $e->getMessage(), [], 401);
        }
    }

    public function print(Request $request)
    {
        $modules = $this->modules;
        $authUser = $this->authenticateLoginUserDetails;
        $modules['company_id'] = $authUser?->company_id ?? null;
        $loginUserId = $authUser?->id ?? null;

        try {
            $query = ContractProcess::select('*')
                ->where(function ($q) use ($modules, $loginUserId) {
                    if (Auth::guard('employees')->check() || !empty($modules['company_id'])) {
                        $q->where('company_id', $modules['company_id']);
                    }
                })
                ->with(['company'])
                ->orderBy('id', 'DESC');

            $contractProcesses = $query->get();
            return view($modules['folder_path'] . '.print', compact('contractProcesses', 'modules'));
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    public function exportExcel(Request $request)
    {
        $modules = $this->modules;
        return Excel::download(new ContractProcessExport($request->all(), $this->authenticateLoginUserDetails, $modules), 'ContractProcess-' . date("Ymd-His") . '.xlsx');
    }
}
