<?php

namespace App\Http\Controllers\software;

use App\Http\Controllers\Controller;
use App\Models\AccountLedger;
use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Redirect;

use const Adminer\DB;

class OutstandingReportController extends Controller
{
    public $modules = [];
    public function __construct()
    {
        parent::__construct();

        $this->modules = [
            'title' => 'Outstanding Report',
            'folder_path' => 'software.modules.account.outstanding-report',
            'route' => 'outstanding-report',
            // 'table_name' => (new ())->getTable(),
            'permisstion_prefix' => 'outstanding-report',
            'module_name' => 'Outstanding Report',
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
        try {

            View::share('modules', $modules);

            $columns = [
                // (object)['data' => "company", 'name' => 'company_id', 'td_label' => 'Company Name', 'className' =>  ''],
                // (object)['data' => "dispatch_no", 'name' => 'dispatch_no', 'td_label' => 'Dispatch No', 'className' =>  ''],
                // (object)['data' => "order_no", 'name' => 'order_no', 'td_label' => 'Order No', 'className' =>  ''],
                // (object)['data' => "dispatch_date", 'name' => 'dispatch_date', 'td_label' => 'Dispatch Date', 'className' =>  ''],
                // (object)['data' => "customer_name", 'name' => 'customer_name', 'td_label' => 'Customer Name', 'className' =>  ''],
                // (object)['data' => "dispatch_qty", 'name' => 'dispatch_qty', 'td_label' => 'Dispatch Qty', 'className' =>  ''],
                // (object)['data' => "action", 'name' => 'action', 'td_label' => 'Action', 'orderable' => false, 'searchable' => false, 'className' =>  'w-10 text-center'],
            ];

            if ($modules['company_id']) {
                // array_unshift($columns, (object)['data' => "company_name", 'name' => 'company_name', 'td_label' => 'Company Name', 'className' =>  '']);
                $columns = array_filter($columns, function ($col) {
                    return $col->name !== 'company_id';
                });
                $columns = array_values($columns);
            }
            View::share("columns", $columns);



            return view($modules['folder_path'] . '.index');
        } catch (\Exception $e) {
            return Redirect::route('software.dashboard')->withErrors($e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function fetch(Request $request)
    {
        $dateRange = $request->input('fromdate_time');
        $companyId = $request->input('company_id');
        $employeeId = $request->input('employee_id');

        // Security: Enforce company_id for restricted users
        if ($this->authenticateLoginUserDetails && $this->authenticateLoginUserDetails->company_id) {
            $companyId = $this->authenticateLoginUserDetails->company_id;
        }

        if (!$companyId) {
            return response()->json([
                'success' => false,
                'html' => '<div class="alert alert-danger">Please select a company.</div>',
            ]);
        }

        // 🗓 Default Financial Year
        $today = Carbon::now();
        $fyStart = Carbon::parse(($today->month < 4 ? $today->year - 1 : $today->year) . '-04-01')->startOfDay();
        $fyEnd   = Carbon::parse(($today->month < 4 ? $today->year : $today->year + 1) . '-03-31')->endOfDay();
        [$startDate, $endDate] = [$fyStart, $fyEnd];


        if ($dateRange && str_contains($dateRange, ' - ')) {
            [$start, $end] = explode(' - ', $dateRange);
            try {
                $startDate = Carbon::createFromFormat('d/m/Y', trim($start))->startOfDay();
                $endDate   = Carbon::createFromFormat('d/m/Y', trim($end))->endOfDay();
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'html' => '<div class="alert alert-danger">Invalid date format.</div>',
                ]);
            }
        }

        // 👨‍💼 Fetch employees of selected company (or one employee if selected)
        $employees = DB::table('employees as e')
            ->leftJoin('companies as c', 'e.company_id', '=', 'c.id')
            ->select(
                'e.id',
                'e.full_name as employee_name',
                'e.contact_number as mobile_number',
                'c.company_name'
            )
            ->where('e.company_id', $companyId)
            ->when($employeeId, fn($q) => $q->where('e.id', $employeeId))
            ->get();

        if ($employees->isEmpty()) {
            return response()->json([
                'success' => false,
                'html' => '<div class="alert alert-warning">No employees found for selected filters.</div>',
            ]);
        }

        // 🧾 Build HTML output
        $html = '
    <div class="card mt-3 border-0 shadow-sm">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-12"><strong>From:</strong> ' . $startDate->format('d-m-Y') . ' <strong>To:</strong> ' . $endDate->format('d-m-Y') . '</div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Employee</th>
                            <th>Phone</th>
                            <th class="text-end">Closing Balance (₹)</th>

                        </tr>
                    </thead>
                    <tbody>';

        $rowAdded = false;

        // 🔁 Calculate employee-wise closing balances
        foreach ($employees as $employee) {
            $ledger = AccountLedger::where('company_id', $companyId)
                ->whereBetween('entry_date', [$startDate, $endDate])
                ->where('employee_id', $employee->id)
                ->selectRaw("
                SUM(CASE WHEN payment_mode = 'debit' THEN amount ELSE 0 END) as total_debit,
                SUM(CASE WHEN payment_mode = 'credit' THEN amount ELSE 0 END) as total_credit
            ")
                ->first();

            $totalDebit  = (float)($ledger->total_debit ?? 0);
            $totalCredit = (float)($ledger->total_credit ?? 0);

            if ($totalDebit == 0 && $totalCredit == 0) continue;

            $closingBalance = $totalDebit - $totalCredit;

            $html .= '
        <tr>
            <td>' . e($employee->employee_name) . '</td>
            <td>' . e($employee->mobile_number ?? '-') . '</td>
            <td class="text-end fw-bold">' . number_format($closingBalance, 2) . '</td>

        </tr>';

            $rowAdded = true;
        }

        $html .= '
                    </tbody>
                </table>
                <div id="employee_receipt_details" class="mt-4"></div>
            </div>
        </div>
    </div>';

        // 📋 No transactions
        if (!$rowAdded) {
            return response()->json([
                'success' => false,
                'html' => '<div class="alert alert-info">No transactions found for selected employees.</div>',
            ]);
        }

        // ✅ Success Response
        return response()->json([
            'success' => true,
            'html' => $html,
        ]);
    }

    public function print(Request $request)
    {
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;
        $modules['parent_type_id'] = ($this->authenticateLoginUserDetails?->parent_type_id) ? $this->authenticateLoginUserDetails?->parent_type_id : null;
        View::share('modules', $modules);
        $companyId = $request->input('company_id');
        if ($modules['company_id']) {
            $companyId = $modules['company_id'];
        }
        $employeeId = $request->input('employee_id');
        $dateRange = $request->input('fromdate_time');
        if ($dateRange && strpos($dateRange, ' to ') !== false) {
            $dateRange = str_replace(' to ', ' - ', $dateRange);
        }
        $today = Carbon::now();
        $fyStart = Carbon::parse(($today->month < 4 ? $today->year - 1 : $today->year) . '-04-01')->startOfDay();
        $fyEnd   = Carbon::parse(($today->month < 4 ? $today->year : $today->year + 1) . '-03-31')->endOfDay();
        [$startDate, $endDate] = [$fyStart, $fyEnd];
        if ($dateRange && str_contains($dateRange, ' - ')) {
            [$start, $end] = explode(' - ', $dateRange);
            $startDate = Carbon::createFromFormat('d/m/Y', trim($start))->startOfDay();
            $endDate   = Carbon::createFromFormat('d/m/Y', trim($end))->endOfDay();
        }
        $employees = DB::table('employees as e')
            ->leftJoin('companies as c', 'e.company_id', '=', 'c.id')
            ->select(
                'e.id',
                'e.full_name as employee_name',
                'e.contact_number as mobile_number',
                'c.company_name'
            )
            ->where('e.company_id', $companyId)
            ->when($employeeId, fn($q) => $q->where('e.id', $employeeId))
            ->get();
        $rows = [];
        foreach ($employees as $employee) {
            $ledger = AccountLedger::where('company_id', $companyId)
                ->whereBetween('entry_date', [$startDate, $endDate])
                ->where('employee_id', $employee->id)
                ->selectRaw("
                    SUM(CASE WHEN payment_mode = 'debit' THEN amount ELSE 0 END) as total_debit,
                    SUM(CASE WHEN payment_mode = 'credit' THEN amount ELSE 0 END) as total_credit
                ")
                ->first();
            $totalDebit  = (float)($ledger->total_debit ?? 0);
            $totalCredit = (float)($ledger->total_credit ?? 0);
            if ($totalDebit == 0 && $totalCredit == 0) {
                continue;
            }
            $closingBalance = $totalDebit - $totalCredit;
            $rows[] = [
                'employee_name' => $employee->employee_name,
                'mobile_number' => $employee->mobile_number ?? '-',
                'closing_balance' => $closingBalance,
            ];
        }
        return view($modules['folder_path'] . '.print', [
            'rows' => $rows,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'company_id' => $companyId,
        ]);
    }



    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
