<?php

namespace App\Http\Controllers\software;

use App\Exports\LoanExport;
use App\Helpers\Helper;
use App\Models\Loan;
use App\Models\Company;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoanRequest;
use App\Models\Employee;
use App\Models\LoanRepayments;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\DataTables;
use Maatwebsite\Excel\Facades\Excel;

class LoanController extends Controller
{

    public $modules = [];
    public function __construct()
    {
        parent::__construct();

        $this->modules = [
            'title' => 'Loan',
            'folder_path' => 'software.modules.loan',
            'route' => 'loan',
            'table_name' => (new Loan())->getTable(),
            'permisstion_prefix' => 'loan',
            'module_name' => 'Loan',
            'company_id' => ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null,
        ];

        View::share('loanStatus', Loan::$loanStatus);
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
                (object)['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'td_label' => 'Sr No.', 'className' => 'w-15 text-start', 'orderable' => false, 'searchable' => false],
                (object)['data' => 'company_name', 'name' => 'company_id', 'td_label' => 'Company Name', 'className' => '', 'orderable' => true, 'searchable' => false],
                (object)['data' => "employee_full_name", 'name' => 'employee_id', 'td_label' => 'Employee Name', 'className' =>  '', 'orderable' => true, 'searchable' => false],
                (object)['data' => "loan_type", 'name' => 'loan_type_id', 'td_label' => 'Loan Type', 'className' =>  '', 'orderable' => true, 'searchable' => false],
                (object)['data' => "loan_amount", 'name' => 'loan_amount', 'td_label' => 'Loan Type', 'className' =>  '', 'orderable' => true, 'searchable' => false],
                (object)['data' => "total_installments", 'name' => 'total_installments', 'td_label' => 'Total Installments', 'className' =>  '', 'orderable' => true, 'searchable' => false],
                (object)['data' => "remaining_installments", 'name' => 'remaining_installments', 'td_label' => 'Remaining Installments', 'className' =>  '', 'orderable' => true, 'searchable' => false],
                (object)['data' => "next_emi", 'name' => 'next_emi', 'td_label' => 'Next EMI', 'orderable' => false, 'searchable' => false, 'className' =>  'w-10 text-start'],
                (object)['data' => "status", 'name' => 'status', 'td_label' => 'Status', 'className' =>  'w-5 text-start', 'orderable' => true, 'searchable' => false],
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
                $data = Loan::with(['company', 'employee'])
                    ->where(function ($query) use ($modules, $loginUserId) {
                        if (Auth::guard('employees')->check() || !empty($modules['company_id'])) {
                            $companyId = $modules['company_id'] ?? Auth::guard('employees')->user()->company_id;
                            $query->where('company_id', $companyId);

                            if (!empty($modules['personal_data_permission']) && empty($modules['all_data_permission'])) {
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
                        $query->when($request->filled('search'), function ($q) use ($request) {
                            $search = $request->search;

                            $q->where(function ($q2) use ($search) {
                                $q2->where('loan_amount', 'like', '%' . $search . '%');
                                $q2->orWhere('remark', 'like', '%' . $search . '%');
                            });
                        });

                        if ($request->has('status') && $request->status !== null && $request->status !== 'all') {
                            $query->where((new Loan())->getTable() . '.status', $request->status);
                        }
                    })
                    ->addColumn('company_name', function ($row) use ($modules) {
                        return $row?->company?->company_name ?? '--';
                    })
                    ->addColumn('employee_full_name', function ($row) use ($modules) {
                        return $row?->employee?->employee_code . ' / ' . $row?->employee?->full_name ?? '--';
                    })
                    ->addColumn('loan_type', function ($row) use ($modules) {
                        return $row?->loan_type?->name ?? '--';
                    })
                    ->addColumn('next_emi', function ($row) use ($modules) {
                        // return $row?->next_pending_emi ?? '--';
                        $next_emi = '--';
                        if ($row?->next_pending_emi) {
                            $next_emi = '';
                            $next_emi .= '<b>Installment Amount : </b>' . $row?->next_pending_emi?->installment_amount;
                            $next_emi .= '<br><b>Due Date : </b>' . Helper::convert_date($row?->next_pending_emi?->due_date, "Y-m-d h:i:s", "d-M-Y");
                            $routePayInstallment = route("loan.pay.installment", [$row?->next_pending_emi?->loan_id, $row?->next_pending_emi?->id]);
                            $next_emi .= "<br><a href='javascript:void(0)' class='ajaxCall btn btn-info btn-sm' data-dtable='true' data-url='" . $routePayInstallment . "' data-confirm_message='You will pay this installment!' data-icon='warning'>Pay Now</a>";
                        }
                        return $next_emi;
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
                            $btn = ucfirst($row->status);
                            return $btn;
                        }
                        $dropdown .= '</ul>';
                        $btn .= $dropdown;
                        return $btn;
                    })
                    ->addColumn('action', function ($row) use ($modules) {
                        $btn = '';
                        if (!$row?->deleted_at) {
                            // $btn .= '<a href="' . route($modules["route"] . ".show", [$row["id"]]) . '" class="edit btn btn-primary btn-icon mx-1"><i class="fa-solid fa-eye"></i></a>';
                            if ($row?->repayments->where('status', 'paid')->count() == 0) {
                                // $btn .= '<a href="javascript:void(0)" class="edit btn btn-primary btn-sm">View</a>';
                                if ($modules['update_permission']) {
                                    $btn .= '<a href="' . route($modules["route"] . ".edit", [$row["id"]]) . '" class="btn btn-light btn-icon mx-1"><i class="fa-solid fa-pen-to-square"></i></a>';
                                }
                                if ($modules['delete_permission']) {
                                    $btn .= '<a href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . route($modules["route"] . ".destroy", [$row["id"]]) . '" class="btn btn-danger btn-icon deletebutton mx-1"><i class="fa-solid fa-trash"></i></i></a>';
                                }
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
                    ->rawColumns(['next_emi', 'status', 'action'])
                    ->make(true);
                return $returnData;
            }

            return view($modules['folder_path'] . '.index');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return Redirect::route('software.dashboard')->withErrors($e->getMessage());
        }
    }

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

    public function store(LoanRequest $request)
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

        DB::beginTransaction();
        try {
            // return $this->authenticateLoginUserDetails;
            $validated['created_by'] = $loginUserId;

            // 1️⃣ Create loan
            $loan = Loan::create([
                'employee_id' => $request?->employee_id,
                'company_id'  => $request?->company_id,
                'loan_type_id' => $request?->loan_type_id ?? null,
                'loan_amount' => $request?->loan_amount,
                'total_installments' => $request?->total_installments,
                'balance_amount' => $request?->loan_amount,
                'remaining_installments' => $request?->total_installments,
                'interest_rate' => $request->interest_rate ?? 0,
                'interest_type' => $request->interest_type ?? 'flat',
                'loan_date' => $request->loan_date ?? now(),
                'status' => $request?->status,
                'remark' => $request?->remark ?? '',
            ]);

            // 2️⃣ Generate repayment schedule
            $schedule = Loan::calculateSchedule(
                $loan->loan_amount,
                $loan->interest_rate,
                $loan->total_installments,
                $loan->interest_type
            );

            // 3️⃣ Store repayment schedule in LoanRepayments
            foreach ($schedule as $installment) {
                $loan->repayments()->create([
                    'loan_amount' => $installment['principal'],
                    'interest' => $installment['interest'],
                    'installment_amount' => $installment['installment_amount'],
                    'due_date' => $installment['due_date'],
                    'status' => 'pending',
                ]);
            }
            DB::commit();
            return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' create successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->withErrors($e->getMessage())->withInput();
        }
    }

    public function show(string $id)
    {
        //
    }

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

            $loanQuery = Loan::query();
            if (!empty($modules['company_id'])) {
                $loanQuery->where('company_id', $modules['company_id']);
            }
            $edit = $loanQuery->findOrFail($id);
            View::share('edit', $edit);

            return view($modules['folder_path'] . '.form');
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    public function update(LoanRequest $request, string $id)
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

        DB::beginTransaction();
        try {
            $validated['updated_by'] = $loginUserId;

            $loanQuery = Loan::with('repayments');
            if (!empty($modules['company_id'])) {
                $loanQuery->where('company_id', $modules['company_id']);
            }
            $loan = $loanQuery->findOrFail($id);

            // Check if any repayment is already paid
            $anyPaid = $loan->repayments->where('status', 'paid')->count() > 0;

            if ($anyPaid) {
                DB::rollBack();
                return Redirect::route($modules['route'] . '.index')
                    ->withErrors("Loan cannot be updated because one or more installments have already been paid.")->withInput();
            }

            // Update loan
            $loan->update([
                'loan_amount' => $request?->loan_amount,
                'total_installments' => $request?->total_installments,
                'balance_amount' => $request?->loan_amount,
                'remaining_installments' => $request->total_installments,
                'interest_rate' => $request->interest_rate ?? $loan->interest_rate,
                'interest_type' => $request->interest_type ?? $loan->interest_type,
                'loan_date' => $request->loan_date ?? $loan->loan_date,
                'status' => $request->status ?? $loan->status,
                'remark' => $request->remark ?? $loan->remark,
            ]);

            // Delete old repayment schedule and regenerate
            $loan->repayments()->delete();
            $schedule = Loan::calculateSchedule(
                $loan->loan_amount,
                $loan->interest_rate,
                $loan->total_installments,
                $loan->interest_type
            );

            foreach ($schedule as $installment) {
                $loan->repayments()->create([
                    'loan_amount' => $installment['principal'],
                    'interest' => $installment['interest'],
                    'installment_amount' => $installment['installment_amount'],
                    'due_date' => $installment['due_date'],
                    'status' => 'pending',
                ]);
            }

            DB::commit();
            return Redirect::route($modules['route'] . '.index')
                ->withSuccess($modules['title'] . ' updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()
                ->withErrors($e->getMessage())->withInput();
        }
    }

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

        $loanQuery = Loan::query();
        if (!empty($modules['company_id'])) {
            $loanQuery->where('company_id', $modules['company_id']);
        }
        $dataDelete = $loanQuery->findOrFail($id);
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

            $loanQuery = Loan::withTrashed();
            if (!empty($modules['company_id'])) {
                $loanQuery->where('company_id', $modules['company_id']);
            }
            $restore_data = $loanQuery->findOrFail($id);
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
            $loanQuery = Loan::withTrashed();
            if (!empty($modules['company_id'])) {
                $loanQuery->where('company_id', $modules['company_id']);
            }
            $country = $loanQuery->findOrFail($request?->id);
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

    public function payInstallment(Request $request, $loanId, $repaymentId)
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

        DB::beginTransaction();
        try {

            $repayment = LoanRepayments::where('loan_id', $loanId)->findOrFail($repaymentId);

            if ($repayment->isPaid()) {
                return $this->sendError('This installment is already paid and cannot be modified.', [], [], 400);
            }

            // Mark as paid
            $repayment->update([
                'status' => 'paid',
                'paid_date' => now(),
            ]);

            // Update loan balance
            $loan = $repayment->loan;
            $loan->balance_amount -= $repayment->loan_amount;
            $loan->remaining_installments -= 1;

            if ($loan->remaining_installments <= 0) {
                $loan->status = 'closed';
            }

            $loan->save();
            // return $loan;
            DB::commit();
            return $this->sendResponse([], 'Installment paid successfully');
        } catch (\Exception $e) {
            DB::rollBack();
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

            $query = Loan::select('*')
                ->where(function ($q) use ($modules, $loginUserId) {
                    if (Auth::guard('employees')->check() || !empty($modules['company_id'])) {
                        $companyId = $modules['company_id'] ?? Auth::guard('employees')->user()->company_id;
                        $q->where('company_id', $companyId);

                        if (!empty($modules['personalDataPermission']) && empty($modules['allDataPermission'])) {
                            $q->where('created_by', $loginUserId);
                        }
                    }
                })
                ->with(['company'])
                ->orderBy('id', 'DESC');

            // Company filter
            if ($request->has('company') && !empty($request->company)) {
                $query->whereHas('company', function ($subQuery) use ($request) {
                    $subQuery->where('company_id', 'like', '%' . $request->company . '%');
                });
            }
            // Employee filter
            if ($request->has('employee_id') && !empty($request->employee_id)) {
                $query->where('employee_id', $request->employee_id);
            }
            // Status filter
            if ($request->has('status') && $request->status !== null && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            // Search filter
            if ($request->has('search') && !empty($request->search)) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            $loans = $query->get();

            return view($modules['folder_path'] . '.print', compact('loans', 'company_id', 'modules'));
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

        return Excel::download(new LoanExport($request->all(), $this->authenticateLoginUserDetails, $modules), 'Loan Master-' . Helper::convert_date("", "Y-m-d H:i:s", "Ymd-His") . '.xlsx');
    }
}
