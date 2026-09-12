<?php

namespace App\Http\Controllers\software;

use App\Exports\PaymentReceiptExport;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentReceiptRequest;
use App\Models\AccountLedger;
use App\Models\Company;
use App\Models\Employee;
use App\Models\PaymentReceipt;
use App\Models\Quotation;
use App\Models\SalesOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf as DomPdf;

use const Adminer\DB;

class PaymentReceiptController extends Controller
{
    public $modules = [];
    public function __construct()
    {
        parent::__construct();

        $this->modules = [
            'title' => 'Payment Receipt',
            'folder_path' => 'software.modules.account.payment-receipt',
            'route' => 'payment-receipt',
            'table_name' => (new PaymentReceipt())->getTable(),
            'permisstion_prefix' => 'payment-receipt',
            'module_name' => 'Payment Receipt',
            'company_id' => ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null,

        ];
        View::share("payment_mode", PaymentReceipt::$payment_mode);

        View::share("payment_type", PaymentReceipt::$payment_type);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $modules = $this->modules;

        // Authenticated User Details
        $modules['authLoginUserDetail'] = $this->authenticateLoginUserDetails ?? null;
        $modules['company_id'] = $modules['authLoginUserDetail']?->company_id ?? null;
        $modules['parent_type_id'] = $modules['authLoginUserDetail']?->parent_type_id ?? null;
        $loginUserId = $modules['authLoginUserDetail']?->id ?? null;

        // Permissions
        if (count(config('constants.permissions'))) {
            foreach (config('constants.permissions') as $key => $permission) {
                $modules[$permission . '_permission'] = (!$modules['company_id'])
                    ? true
                    : Gate::check('hasPermission', [$permission, $modules['module_name']]);
            }
        }

        // View Permission Check
        if (!$modules['view_permission']) {
            if ($request->ajax()) {
                return $this->sendError('Unauthorized', [], [], 403);
            }
            abort(403, 'Unauthorized');
        }

        View::share('modules', $modules);

        try {
            // Define Columns for DataTables
            $columns = [
                (object)['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'td_label' => 'Sr No.', 'orderable' => false, 'searchable' => false],
                (object)['data' => 'company.company_name', 'name' => 'company_id', 'td_label' => 'Company'],
                (object)['data' => 'branch_department_employee', 'name' => 'branch_department_employee', 'td_label' => 'Branch / Department / Employee'],
                (object)['data' => 'effect_month_year', 'name' => 'effect_month_year', 'td_label' => 'Effect Month & Year'],
                (object)['data' => 'date', 'name' => 'date', 'td_label' => 'Date'],
                (object)['data' => 'payment_mode', 'name' => 'payment_mode', 'td_label' => 'Payment Mode'],
                (object)['data' => 'amount', 'name' => 'amount', 'td_label' => 'Amount'],
                (object)['data' => 'payment_type', 'name' => 'payment_type', 'td_label' => 'Payment Type'],
                (object)['data' => 'receipt_no', 'name' => 'receipt_no', 'td_label' => 'Receipt No.'],
                (object)['data' => 'status', 'name' => 'status', 'td_label' => 'Status', 'className' => 'text-start'],
                (object)['data' => 'action', 'name' => 'action', 'td_label' => 'Action', 'orderable' => false, 'searchable' => false, 'className' => 'text-start'],
            ];


            if ($modules['company_id']) {
                $columns = array_filter($columns, fn($col) => $col->name !== 'company_id');
                $columns = array_values($columns);
            }

            View::share('columns', $columns);
            View::share('route', $modules['route']);


            if ($request->ajax()) {
                $data = PaymentReceipt::with(['company', 'departments', 'employee', 'branch'])
                    ->when(Auth::guard('employees')->check() || !empty($modules['company_id']), function ($query) use ($modules, $loginUserId) {
                        $companyId = $modules['company_id'] ?? Auth::guard('employees')->user()->company_id;
                        $query->where('company_id', $companyId);

                        if (!empty($modules['personal_data_permission']) && empty($modules['all_data_permission'])) {
                            $query->where('created_by', $loginUserId);
                        }
                    })
                    ->orderBy('id', 'DESC');

                if (isset($modules['restore_permission']) && $modules['restore_permission']) {
                    $data = $data->withTrashed();
                }

                return DataTables::of($data)
                    ->addIndexColumn()


                    ->filter(function ($query) use ($request) {
                        if ($request->filled('filter_company')) {
                            $query->where('company_id', $request->filter_company);
                        }
                        if ($request->filled('filter_branch')) {
                            $query->where('branch_id', $request->filter_branch);
                        }
                        if ($request->filled('filter_employee')) {
                            $query->where('employee_id', $request->filter_employee);
                        }

                        if ($request->filled('filter_effect_on_month')) {
                            $query->where('effect_on_month', $request->filter_effect_on_month);
                        }

                        if ($request->filled('filter_effect_of_year')) {
                            $query->where('effect_of_year', $request->filter_effect_of_year);
                        }


                        if ($request->filled('filter_date')) {
                            $dates = explode(' to ', $request->filter_date);
                            if (count($dates) === 2) {
                                $start = Carbon::createFromFormat('d/m/Y', trim($dates[0]))->startOfDay();
                                $end   = Carbon::createFromFormat('d/m/Y', trim($dates[1]))->endOfDay();
                                $query->whereBetween('date', [$start, $end]);
                            }
                        }
                        if ($request->filled('status') && $request->status !== 'all') {
                            $query->where('status', $request->status);
                        }
                        if ($request->filled('search')) {
                            $search = $request->search;
                            $query->where(function ($q) use ($search) {
                                $q->where('receipt_no', 'like', "%{$search}%")
                                    ->orWhere('amount', 'like', "%{$search}%")
                                    ->orWhere('remark', 'like', "%{$search}%");
                            });
                        }
                    })


                    ->editColumn('status', function ($row) use ($modules) {
                        $status = strtolower($row->status);
                        if ($status === 'pending') {
                            return '<button class="btn btn-warning btn-sm change-status-btn" data-id="' . $row->id . '" data-company_id="' . $row->company_id . '">Pending</button>';
                        } elseif ($status === 'approve') {
                            return '<span class="btn btn-success btn-sm">Approve</span>';
                        }
                        return '<span class="btn btn-secondary btn-sm">' . ucfirst($status) . '</span>';
                    })

                    ->editColumn('date', function ($row) {
                        return $row->date ? Carbon::parse($row->date)->format('d/m/Y') : '-';
                    })
                    ->editColumn('branch_department_employee', function ($row) {
                        $employee = $row->employee ? "{$row->employee->employee_code} - {$row->employee->full_name}" : '-';
                        $branch = $row->branch->name ?? '-';
                        $department = $row->departments->name ?? '-';

                        return "<b>Employee:</b> {$employee}<br><b>Branch:</b> {$branch} <br><b>Department:</b> {$department} ";
                    })
                    ->editColumn('effect_month_year', function ($row) {
                        $month = $row->effect_on_month ?? '-';
                        $year = $row->effect_of_year ?? '-';
                        // return "<b>Effect:</b> {$month} - {$year}";
                        return "<b>Month:</b> {$month} <br><b>Year:</b> {$year}";
                    })

                    // ->addColumn('action', function ($row) use ($modules) {
                    //     $btn = '';
                    //     if (!$row?->deleted_at) {
                    //         if ($modules['update_permission']) {
                    //             $btn .= '<a href="' . route($modules["route"] . ".edit", [$row->id]) . '" class="btn btn-light btn-icon mx-1"><i class="fa-solid fa-pen-to-square"></i></a>';
                    //         }
                    //         if ($modules['delete_permission']) {
                    //             $btn .= '<a href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . route($modules["route"] . ".destroy", [$row->id]) . '" class="btn btn-danger btn-icon deletebutton mx-1"><i class="fa-solid fa-trash"></i></a>';
                    //         }
                    //     } else {
                    //         $btn .= '<a href="javascript:void(0)" data-restore="' . route($modules["route"] . ".restore", ['id' => $row->id]) . '" class="btn btn-light mx-1 record-restore"><i class="ti ti-history"></i> Restore</a>';
                    //     }
                    //     return $btn ?: '-';
                    // })
                    ->addColumn('action', function ($row) use ($modules) {
                        $btn = '';

                        if (!$row?->deleted_at) {
                            $status = strtolower($row->status ?? '');
                            $btn .= '<a href="' . route($modules["route"] . ".show", [$row->id]) . '" class="btn btn-primary btn-icon mx-1" title="View"><i class="fa-solid fa-eye"></i></a>';
                            if ($status === 'pending') {
                                if ($modules['update_permission']) {
                                    $btn .= '<a href="' . route($modules["route"] . ".edit", [$row->id]) . '" class="btn btn-light btn-icon mx-1"><i class="fa-solid fa-pen-to-square"></i></a>';
                                }
                                if ($modules['delete_permission']) {
                                    $btn .= '<a href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . route($modules["route"] . ".destroy", [$row->id]) . '" class="btn btn-danger btn-icon deletebutton mx-1"><i class="fa-solid fa-trash"></i></a>';
                                }
                            } elseif ($status === 'approve') {
                                if ($modules['delete_permission']) {
                                    $btn .= '<a href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . route($modules["route"] . ".destroy", [$row->id]) . '" class="btn btn-danger btn-icon deletebutton mx-1"><i class="fa-solid fa-trash"></i></a>';
                                }
                            } else {
                                if ($modules['delete_permission']) {
                                    $btn .= '<a href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . route($modules["route"] . ".destroy", [$row->id]) . '" class="btn btn-danger btn-icon deletebutton mx-1"><i class="fa-solid fa-trash"></i></a>';
                                }
                            }
                        } else {
                            $btn .= '<a href="javascript:void(0)" data-restore="' . route($modules["route"] . ".restore", ['id' => $row->id]) . '" class="btn btn-light mx-1 record-restore"><i class="ti ti-history"></i> Restore</a>';
                        }

                        return $btn ?: '-';
                    })

                    ->rawColumns(['status', 'action', 'branch_department_employee', 'effect_month_year'])
                    ->make(true);
            }

            return view($modules['folder_path'] . '.index');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
            return redirect()->route('software.dashboard')->withErrors($e->getMessage());
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
            $company_id = $modules['company_id'];


            return view($modules['folder_path'] . '.form', compact('modules'));
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PaymentReceiptRequest $request)
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
            $validated['status'] = 'pending';
            // $validated['date'] = \Carbon\Carbon::createFromFormat('d-m-Y', $request->date)->format('Y-m-d');
            $validated['created_by'] = $loginUserId;
            // dd($validated);
            // return $validated;
            PaymentReceipt::create($validated);


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
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = $this->authenticateLoginUserDetails ?? null;
        $modules['company_id'] = $modules['authLoginUserDetail']?->company_id ?? null;
        $modules['parent_type_id'] = $modules['authLoginUserDetail']?->parent_type_id ?? null;

        if (count(config('constants.permissions'))) {
            foreach (config('constants.permissions') as $key => $permission) {
                $modules[$permission . '_permission'] = (!$modules['company_id'])
                    ? true
                    : Gate::check('hasPermission', [$permission, $modules['module_name']]);
            }
        }
        $modules['view_permission'] = $modules['view_permission'] ?? true;
        if (!$modules['view_permission']) abort(403, 'Unauthorized');

        try {
            View::share('modules', $modules);
            $query = PaymentReceipt::with(['company', 'employee', 'branch', 'departments']);
            if (!empty($modules['company_id'])) {
                $query->where('company_id', $modules['company_id']);
            }
            $receipt = $query->findOrFail($id);
            return view($modules['folder_path'] . '.show', compact('receipt'));
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
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
            if (isset($request) && $request->ajax()) {
                return $this->sendError('Unauthorized', [], [], 403);
            }
            abort(403, 'Unauthorized');
        }
        try {

            View::share('modules', $modules);

            $paymentReceiptQuery = PaymentReceipt::query();
            if (!empty($modules['company_id'])) {
                $paymentReceiptQuery->where('company_id', $modules['company_id']);
            }
            $edit = $paymentReceiptQuery->findOrFail($id);
            View::share('edit', $edit);

            return view($modules['folder_path'] . '.form');
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(PaymentReceiptRequest $request, string $id)
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
            $paymentReceiptQuery = PaymentReceipt::query();
            if (!empty($modules['company_id'])) {
                $paymentReceiptQuery->where('company_id', $modules['company_id']);
            }
            $updateData = $paymentReceiptQuery->findOrFail($id);
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

        $paymentReceiptQuery = PaymentReceipt::query();
        if (!empty($modules['company_id'])) {
            $paymentReceiptQuery->where('company_id', $modules['company_id']);
        }
        $dataDelete = $paymentReceiptQuery->findOrFail($id);
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

            $paymentReceiptQuery = PaymentReceipt::withTrashed();
            if (!empty($modules['company_id'])) {
                $paymentReceiptQuery->where('company_id', $modules['company_id']);
            }
            $restore_data = $paymentReceiptQuery->findOrFail($id);
            $restore_data->restore();

            return Redirect::back()->withSuccess($modules['title'] . ' restored successfully!');
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    public function generateReceiptNo(Request $request)
    {
        // dd("-------");
        try {
            $companyId = $request->company_id;
            // Security: Enforce company_id for restricted users
            if ($this->authenticateLoginUserDetails && $this->authenticateLoginUserDetails->company_id) {
                $companyId = $this->authenticateLoginUserDetails->company_id;
            }

            // Get last receipt for this company (including soft-deleted)
            $latest = PaymentReceipt::withTrashed()
                ->where('company_id', $companyId)
                ->orderByDesc('id')
                ->first();

            $number = 1;
            if ($latest && $latest->receipt_no) {
                if (preg_match('/(\d+)$/', $latest->receipt_no, $matches)) {
                    $number = (int)$matches[1] + 1;
                }
            }

            $receiptNo = 'REC-' . str_pad($number, 5, '0', STR_PAD_LEFT);

            return response()->json(['receipt_no' => $receiptNo]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Server Error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function status_update(Request $request)
    {
        $isAjax = $request->ajax();

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

        if (!$modules['update_permission']) {
            if ($isAjax) {
                return $this->sendError('Unauthorized', [], [], 403);
            }
            abort(403, 'Unauthorized');
        }

        try {
            $paymentReceiptQuery = PaymentReceipt::withTrashed();
            if (!empty($modules['company_id'])) {
                $paymentReceiptQuery->where('company_id', $modules['company_id']);
            }
            $receipt = $paymentReceiptQuery->findOrFail($request->id);


            $receipt->status = 'Approve';
            $receipt->updated_by = $loginUserId;
            $receipt->save();

            $message = 'Receipt approved successfully.';

            return $isAjax
                ? response()->json([
                    'success' => true,
                    'message' => $message,
                    'id' => $receipt->id,
                    'new_status' => $receipt->status
                ])
                : redirect()->back()->withSuccess($message);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            return $isAjax
                ? response()->json(['success' => false, 'message' => $message], 500)
                : redirect()->back()->withErrors($message);
        }
    }

    public function exportExcel(Request $request)
    {
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = $this->authenticateLoginUserDetails ?? null;
        $modules['company_id'] = $this->authenticateLoginUserDetails?->company_id ?? null;
        $modules['parent_type_id'] = $this->authenticateLoginUserDetails?->parent_type_id ?? null;

        $loginUserId = $modules['authLoginUserDetail']?->id ?? null;

        if (count(config('constants.permissions'))) {
            foreach (config('constants.permissions') as $key => $value) {
                $hasPermission = (!isset($modules['company_id']) || !$modules['company_id'])
                    ? true
                    : Gate::check('hasPermission', [$value, $modules['module_name']]);

                $modules[$value . '_permission'] = $hasPermission;

                if ($value === 'personal_data') {
                    $modules['personal_data_permission'] = $hasPermission;
                }
                if ($value === 'all_data') {
                    $modules['all_data_permission'] = $hasPermission;
                }
            }
        }

        if (empty($modules['excel_permission'])) {
            if ($request->ajax()) {
                return $this->sendError('Unauthorized', [], [], 403);
            }
            abort(403, 'Unauthorized');
        }

        return Excel::download(new PaymentReceiptExport($request->all(), $this->authenticateLoginUserDetails, $modules, $loginUserId), 'PaymentReceipt-' . Helper::convert_date("", "Y-m-d H:i:s", "Ymd-His") . '.xlsx');
    }
    public function print(Request $request)
    {
        $modules = $this->modules ?? [];
        $modules['authLoginUserDetail'] = $this->authenticateLoginUserDetails ?? null;
        $loginUserId = $modules['authLoginUserDetail']?->id ?? null;
        $company_id = $modules['authLoginUserDetail']?->company_id ?? null;
        $modules['company_id'] = $company_id;
        $modules['parent_type_id'] = $modules['authLoginUserDetail']?->parent_type_id ?? null;

        // Permission check
        if (count(config('constants.permissions'))) {
            foreach (config('constants.permissions') as $perm) {
                $modules[$perm . '_permission'] = (!$company_id)
                    ? true
                    : Gate::check('hasPermission', [$perm, $modules['module_name'] ?? '']);
            }
        }

        if (!($modules['print_permission'] ?? false)) {
            if ($request->ajax()) {
                return $this->sendError('Unauthorized', [], [], 403);
            }
            abort(403, 'Unauthorized');
        }

        try {
            $query = PaymentReceipt::with(['company', 'employee', 'branch', 'departments'])
                ->orderBy('id', 'DESC')
                ->whereNull('deleted_at');

            // Apply company restrictions
            if ($company_id) {
                $query->where('company_id', $company_id);
                if (!empty($modules['personal_data_permission']) && empty($modules['all_data_permission'])) {
                    $query->where('created_by', $loginUserId);
                }
            }

            // Apply filters from request
            if ($request->filled('filter_company')) {
                $query->where('company_id', $request->filter_company);
            }

            if ($request->filled('filter_branch')) {
                $query->where('branch_id', $request->filter_branch);
            }

            if ($request->filled('filter_employee')) {
                $query->where('employee_id', $request->filter_employee);
            }

            if ($request->filled('filter_effect_on_month')) {
                $query->where('effect_on_month', $request->filter_effect_on_month);
            }

            if ($request->filled('filter_effect_of_year')) {
                $query->where('effect_of_year', $request->filter_effect_of_year);
            }

            if ($request->filled('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            if ($request->filled('filter_date')) {
                $dates = explode(' to ', $request->filter_date);
                if (count($dates) === 2) {
                    $start = Carbon::createFromFormat('d/m/Y', trim($dates[0]))->startOfDay();
                    $end   = Carbon::createFromFormat('d/m/Y', trim($dates[1]))->endOfDay();
                    $query->whereBetween('date', [$start, $end]);
                }
            }
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('receipt_no', 'like', "%{$search}%")
                        ->orWhere('amount', 'like', "%{$search}%")
                        ->orWhere('remark', 'like', "%{$search}%");
                });
            }
            // dd('L-702', $request->all());
            $receipt_list = $query->get();

            return view($modules['folder_path'] . '.print', compact('receipt_list', 'company_id', 'modules'));
        } catch (\Exception $e) {
            return redirect()->route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    public function pdf(Request $request, string $id)
    {
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = $this->authenticateLoginUserDetails ?? null;
        $modules['company_id'] = $modules['authLoginUserDetail']?->company_id ?? null;

        if (count(config('constants.permissions'))) {
            foreach (config('constants.permissions') as $key => $permission) {
                $modules[$permission . '_permission'] = (!$modules['company_id'])
                    ? true
                    : Gate::check('hasPermission', [$permission, $modules['module_name']]);
            }
        }
        $modules['view_permission'] = $modules['view_permission'] ?? true;
        if (!$modules['view_permission']) abort(403, 'Unauthorized');

        try {
            $query = PaymentReceipt::with(['company', 'employee', 'branch', 'departments']);
            if (!empty($modules['company_id'])) {
                $query->where('company_id', $modules['company_id']);
            }
            $receipt = $query->findOrFail($id);

            $pdf = DomPdf::loadView($modules['folder_path'] . '.pdf', [
                'receipt' => $receipt,
                'modules' => $modules,
            ])->setPaper('a5', 'landscape');

            $filename = 'payment-receipt-' . ($receipt->receipt_no ?? 'REC') . '.pdf';
            return $pdf->download($filename);
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }
}
