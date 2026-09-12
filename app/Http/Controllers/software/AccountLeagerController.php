<?php

namespace App\Http\Controllers\software;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\AccountLedger;
use App\Models\Company;
use App\Models\Customer;
use App\Models\PaymentReceipt;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;

use const Adminer\DB;

class AccountLeagerController extends Controller
{
    public $modules = [];
    public function __construct()
    {
        parent::__construct();

        $this->modules = [
            'title' => 'Account Ledger',
            'folder_path' => 'software.modules.account.account-ledger',
            'route' => 'account-ledger',
            // 'table_name' => (new ())->getTable(),
            'permisstion_prefix' => 'account-ledger',
            'module_name' => 'Account Ledger',
            'company_id' => ($this->authenticateLoginUserDetails?->company_id) ? $this->authenticateLoginUserDetails?->company_id : null,

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

            $columns = [];

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

    public function getLedgerReport(Request $request)
    {
        try {
            $request->validate([
                'company_id'    => 'required|integer',
                'employee_id'   => 'required|integer',
                'followup_date' => 'required|string',
            ]);

            $company_id = $request->company_id;
            if (Auth::guard('employees')->check()) {
                $company_id = Auth::guard('employees')->user()->company_id;
            } elseif (!empty($this->authenticateLoginUserDetails?->company_id)) {
                $company_id = $this->authenticateLoginUserDetails->company_id;
            }
            $employee_id = $request->employee_id;
            $fromToDate = $request->followup_date;

            // Determine start and end date
            if ($fromToDate && str_contains($fromToDate, ' - ')) {
                [$start, $end] = explode(' - ', $fromToDate);
                $startDate = Carbon::createFromFormat('d/m/Y', trim($start))->format('Y-m-d');
                $endDate = Carbon::createFromFormat('d/m/Y', trim($end))->format('Y-m-d');
            } else {
                $startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
                $endDate = Carbon::now()->format('Y-m-d');
            }

            $receipts = DB::table('payment_receipts as pr')
                ->join('employees as e', 'pr.employee_id', '=', 'e.id')
                ->select(
                    'pr.id as source_id',
                    'pr.date as entry_date',
                    'pr.amount',
                    'pr.payment_mode',
                    'pr.payment_type',
                    'pr.receipt_no',
                    'pr.cheque_no',
                    'pr.upi_no',
                    'e.employee_code',
                    'e.full_name',
                    DB::raw("'receipt' as source")
                )
                ->where('pr.company_id', $company_id)
                ->where('pr.employee_id', $employee_id)
                ->where('pr.status', 'Approve')
                ->whereBetween('pr.date', [$startDate, $endDate])
                ->get();

            $salaries = DB::table('salaries as s')
                ->join('employees as e', 's.employee_id', '=', 'e.id')
                ->select(
                    's.id as source_id',
                    's.calculation_date as entry_date',
                    's.net_bank_pay as amount',
                    'e.employee_code',
                    'e.full_name',
                    's.year',
                    's.month',
                    DB::raw("'salary' as source")
                )
                ->where('s.company_id', $company_id)
                ->where('s.employee_id', $employee_id)
                ->whereBetween('s.calculation_date', [$startDate, $endDate])
                ->get();

            // Include manual account_ledgers entries (e.g. generated salary ledger rows)
            $manualLedgers = DB::table('account_ledgers as al')
                ->join('employees as e', 'al.employee_id', '=', 'e.id')
                ->select(
                    'al.id as source_id',
                    'al.entry_date as entry_date',
                    DB::raw("COALESCE(al.debit_amount, 0) as debit_amount"),
                    DB::raw("COALESCE(al.credit_amount, 0) as credit_amount"),
                    DB::raw("(COALESCE(al.debit_amount,0) + COALESCE(al.credit_amount,0)) as amount"),
                    'al.payment_mode',
                    'al.receipt_id',
                    'e.employee_code',
                    'e.full_name',
                    'al.description',
                    DB::raw("'ledger' as source")
                )
                ->where('al.company_id', $company_id)
                ->where('al.employee_id', $employee_id)
                ->whereBetween('al.entry_date', [$startDate, $endDate])
                ->get();

            $rows = [];
            foreach ($receipts as $r) {
                $rows[] = (array) $r;
            }
            foreach ($salaries as $s) {
                $rows[] = (array) $s;
            }
                foreach ($manualLedgers as $m) {
                    $rows[] = (array) $m;
                }

            usort($rows, function ($a, $b) {
                return strcmp($a['entry_date'], $b['entry_date']);
            });

            $ledger = [];
            $runningBalance = 0;

            foreach ($rows as $row) {
                // Explicit handling for manual ledger rows
                if (!empty($row['source']) && $row['source'] === 'ledger') {
                    $debit = floatval($row['debit_amount'] ?? 0);
                    $credit = floatval($row['credit_amount'] ?? 0);

                    $ledgerRow = [
                        'company_id'      => $company_id,
                        'employee_id'     => $employee_id,
                        'entry_date'      => Carbon::parse($row['entry_date'])->format('Y-m-d'),
                        'receipt_id'      => $row['receipt_id'] ?? $row['source_id'],
                        'payment_mode'    => $row['payment_mode'] ?? null,
                        'amount'          => $debit + $credit,
                        'debit_amount'    => $debit,
                        'credit_amount'   => $credit,
                        'closing_balance' => 0,
                        'description'     => $row['description'] ?? '-',
                        'updated_at'      => now(),
                    ];

                    $runningBalance += ($debit - $credit);
                    $ledgerRow['closing_balance'] = $runningBalance;

                    // sanitize numeric fields and ensure strings where expected
                    $ledgerRow['company_id'] = (string)($ledgerRow['company_id'] ?? '');
                    $ledgerRow['employee_id'] = (string)($ledgerRow['employee_id'] ?? '');
                    $ledgerRow['receipt_id'] = (string)($ledgerRow['receipt_id'] ?? '');
                    $ledgerRow['amount'] = floatval($ledgerRow['amount'] ?? 0);
                    $ledgerRow['debit_amount'] = floatval($ledgerRow['debit_amount'] ?? 0);
                    $ledgerRow['credit_amount'] = floatval($ledgerRow['credit_amount'] ?? 0);
                    $ledgerRow['closing_balance'] = floatval($ledgerRow['closing_balance'] ?? 0);

                    // For salary entries, prefer to ensure one row per employee per month
                    if (str_starts_with($ledgerRow['receipt_id'], 'salary-')) {
                        try {
                            $dt = Carbon::parse($ledgerRow['entry_date']);
                            $year = $dt->year;
                            $month = $dt->month;

                            $existingSalaryRow = DB::table('account_ledgers')
                                ->where('company_id', $ledgerRow['company_id'])
                                ->where('employee_id', $ledgerRow['employee_id'])
                                ->whereRaw('YEAR(entry_date) = ? AND MONTH(entry_date) = ?', [$year, $month])
                                ->where('description', 'like', '%Salary Net Pay%')
                                ->first();

                            if ($existingSalaryRow) {
                                DB::table('account_ledgers')->where('id', $existingSalaryRow->id)->update(array_merge($ledgerRow, ['updated_at' => now()]));
                            } else {
                                DB::table('account_ledgers')->insert(array_merge($ledgerRow, ['created_at' => now(), 'updated_at' => now()]));
                            }
                            // continue to next loop iteration handled by caller
                            // (we still add ledger display row below)
                        } catch (\Exception $ex) {
                            Log::error('Ledger salary upsert failed: ' . $ex->getMessage());
                        }
                    } else {
                        DB::table('account_ledgers')->updateOrInsert(
                            ['company_id' => $ledgerRow['company_id'], 'receipt_id' => $ledgerRow['receipt_id']],
                            array_merge($ledgerRow, ['updated_at' => now(), 'created_at' => now()])
                        );
                    }

                    $ledger[] = [
                        'date'          => Carbon::parse($row['entry_date'])->format('d-m-Y'),
                        'employee_code' => $row['employee_code'] ?? '',
                        'full_name'     => $row['full_name'] ?? '',
                        'debit'         => $debit,
                        'credit'        => $credit,
                        'description'   => $ledgerRow['description'],
                    ];

                    continue;
                }
                if ($row['source'] === 'receipt') {
                    $mode = strtolower($row['payment_mode'] ?? 'credit');
                    $debit = in_array($mode, ['debit', 'dr', 'out']) ? floatval($row['amount']) : 0;
                    $credit = in_array($mode, ['credit', 'cr', 'in']) ? floatval($row['amount']) : 0;
                    $type = strtolower($row['payment_type'] ?? '');
                    if ($type === 'cheque') {
                        $extraInfo = 'Cheque No. (' . ($row['cheque_no'] ?? '-') . ')';
                    } elseif ($type === 'upi') {
                        $extraInfo = 'UPI No. (' . ($row['upi_no'] ?? '-') . ')';
                    } elseif ($type === 'cash') {
                        $extraInfo = 'Cash';
                    } elseif ($type === 'bank_transfer') {
                        $extraInfo = 'Bank Transfer';
                    } else {
                        $extraInfo = ucfirst($type ?: '-');
                    }

                        // If this row comes from manual ledger, override debit/credit using provided fields
                        if (!empty($row['source']) && $row['source'] === 'ledger') {
                            $debit = floatval($row['debit_amount'] ?? ($row['debit'] ?? 0));
                            $credit = floatval($row['credit_amount'] ?? ($row['credit'] ?? 0));
                            $ledgerRow['debit_amount'] = $debit;
                            $ledgerRow['credit_amount'] = $credit;
                            $ledgerRow['amount'] = $debit + $credit;
                            $ledgerRow['description'] = $row['description'] ?? $ledgerRow['description'];
                            $ledgerRow['receipt_id'] = $row['receipt_id'] ?? $ledgerRow['receipt_id'];
                        }
                    $description = ($row['employee_code'] ?? '-') . ' - ' . ($row['full_name'] ?? '-') . ' By ' . $extraInfo . ' with Receipt No - ' . ($row['receipt_no'] ?? '-');

                    $ledgerRow = [
                        'company_id'      => $company_id,
                        'employee_id'     => $employee_id,
                        'entry_date'      => Carbon::parse($row['entry_date'])->format('Y-m-d'),
                        'receipt_id'      => $row['source_id'],
                        'payment_mode'    => $row['payment_mode'],
                        'amount'          => $row['amount'],
                        'debit_amount'    => $debit,
                        'credit_amount'   => $credit,
                        'closing_balance' => 0,
                        'description'     => $description,
                        'updated_at'      => now(),
                    ];
                } else {
                    $debit = 0;
                    $credit = floatval($row['amount']);
                    $monthName = [
                        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
                        5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug',
                        9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec'
                    ][$row['month'] ?? null] ?? $row['month'] ?? '';
                    $periodText = trim(($monthName ? $monthName : '') . ' ' . ($row['year'] ?? ''));
                    $description = ($row['employee_code'] ?? '-') . ' - ' . ($row['full_name'] ?? '-') . ' Salary Net Pay (' . $periodText . ')';
                    $ledgerRow = [
                        'company_id'      => $company_id,
                        'employee_id'     => $employee_id,
                        'entry_date'      => Carbon::parse($row['entry_date'])->format('Y-m-d'),
                        'receipt_id'      => 'salary-' . $row['source_id'],
                        'payment_mode'    => 'credit',
                        'amount'          => $row['amount'],
                        'debit_amount'    => 0,
                        'credit_amount'   => $credit,
                        'closing_balance' => 0,
                        'description'     => $description,
                        'updated_at'      => now(),
                    ];
                }

                $runningBalance += ($debit - $credit);
                $ledgerRow['closing_balance'] = $runningBalance;

                // sanitize numeric fields and ensure strings where expected
                $ledgerRow['company_id'] = (string)($ledgerRow['company_id'] ?? '');
                $ledgerRow['employee_id'] = (string)($ledgerRow['employee_id'] ?? '');
                $ledgerRow['receipt_id'] = (string)($ledgerRow['receipt_id'] ?? '');
                $ledgerRow['amount'] = floatval($ledgerRow['amount'] ?? 0);
                $ledgerRow['debit_amount'] = floatval($ledgerRow['debit_amount'] ?? 0);
                $ledgerRow['credit_amount'] = floatval($ledgerRow['credit_amount'] ?? 0);
                $ledgerRow['closing_balance'] = floatval($ledgerRow['closing_balance'] ?? 0);

                if (str_starts_with($ledgerRow['receipt_id'], 'salary-')) {
                    try {
                        $dt = Carbon::parse($ledgerRow['entry_date']);
                        $year = $dt->year;
                        $month = $dt->month;

                        $existingSalaryRow = DB::table('account_ledgers')
                            ->where('company_id', $ledgerRow['company_id'])
                            ->where('employee_id', $ledgerRow['employee_id'])
                            ->whereRaw('YEAR(entry_date) = ? AND MONTH(entry_date) = ?', [$year, $month])
                            ->where('description', 'like', '%Salary Net Pay%')
                            ->first();

                        if ($existingSalaryRow) {
                            DB::table('account_ledgers')->where('id', $existingSalaryRow->id)->update(array_merge($ledgerRow, ['updated_at' => now()]));
                        } else {
                            DB::table('account_ledgers')->insert(array_merge($ledgerRow, ['created_at' => now(), 'updated_at' => now()]));
                        }
                    } catch (\Exception $ex) {
                        Log::error('Ledger salary upsert failed: ' . $ex->getMessage());
                    }
                } else {
                    DB::table('account_ledgers')->updateOrInsert(
                        ['company_id' => $ledgerRow['company_id'], 'receipt_id' => $ledgerRow['receipt_id']],
                        array_merge($ledgerRow, ['updated_at' => now(), 'created_at' => now()])
                    );
                }

                $ledger[] = [
                    'date'          => Carbon::parse($row['entry_date'])->format('d-m-Y'),
                    'employee_code' => $row['employee_code'] ?? '',
                    'full_name'     => $row['full_name'] ?? '',
                    'debit'         => $debit,
                    'credit'        => $credit,
                    'description'   => $ledgerRow['description'],
                ];
            }

            return response()->json([
                'ledger'         => $ledger,
                'total_debit'    => collect($ledger)->sum('debit'),
                'total_credit'   => collect($ledger)->sum('credit'),
                'closing_balance' => $runningBalance,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => true,
                'message' => 'Failed to generate ledger report.',
                'debug'   => $e->getMessage()
            ], 500);
        }
    }







    public function print(Request $request)
    {
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = $this->authenticateLoginUserDetails ?? null;
        $company_id = $modules['authLoginUserDetail']?->company_id ?? $request->company_id;
        if (Auth::guard('employees')->check()) {
            $company_id = Auth::guard('employees')->user()->company_id;
        } elseif (!empty($this->authenticateLoginUserDetails?->company_id)) {
            $company_id = $this->authenticateLoginUserDetails->company_id;
        }

        // Allow print
        $modules['print_permission'] = true;

        try {
            $employee_id  = $request->input('employee_id');
            $followupDate = $request->input('followup_date');
            $startDate = null;
            $endDate   = null;

            if (!empty($followupDate)) {
                if (strpos($followupDate, ' to ') !== false) {
                    $followupDate = str_replace(' to ', ' - ', $followupDate);
                }
                if (strpos($followupDate, ' - ') !== false) {
                    [$start, $end] = explode(' - ', $followupDate);
                } else {
                    $start = $end = $followupDate;
                }
                $startDate = Carbon::createFromFormat('d/m/Y', trim($start))->startOfDay();
                $endDate   = Carbon::createFromFormat('d/m/Y', trim($end))->endOfDay();
            } else {
                $startDate = Carbon::now()->startOfMonth();
                $endDate   = Carbon::now()->endOfDay();
            }

            $receipts = DB::table('payment_receipts as pr')
                ->join('employees as e', 'pr.employee_id', '=', 'e.id')
                ->select(
                    'pr.id as source_id',
                    'pr.date as entry_date',
                    'pr.amount',
                    'pr.payment_type',
                    'pr.payment_mode',
                    'pr.cheque_no',
                    'pr.upi_no',
                    'pr.receipt_no',
                    'e.employee_code',
                    'e.full_name',
                    DB::raw("'receipt' as source")
                )
                ->where('pr.company_id', $company_id)
                ->when($employee_id, function ($query) use ($employee_id) {
                    return $query->where('pr.employee_id', $employee_id);
                })
                ->whereBetween('pr.date', [$startDate, $endDate])
                ->get();

            $salaries = DB::table('salaries as s')
                ->join('employees as e', 's.employee_id', '=', 'e.id')
                ->select(
                    's.id as source_id',
                    's.calculation_date as entry_date',
                    's.net_bank_pay as amount',
                    'e.employee_code',
                    'e.full_name',
                    's.year',
                    's.month',
                    DB::raw("'salary' as source")
                )
                ->where('s.company_id', $company_id)
                ->when($employee_id, function ($query) use ($employee_id) {
                    return $query->where('s.employee_id', $employee_id);
                })
                ->whereBetween('s.calculation_date', [$startDate, $endDate])
                ->get();

            $rows = [];
            foreach ($receipts as $r) {
                $rows[] = (array) $r;
            }
            foreach ($salaries as $s) {
                $rows[] = (array) $s;
            }

            usort($rows, function ($a, $b) {
                return strcmp($a['entry_date'], $b['entry_date']);
            });

            $ledger = [];
            $runningBalance = 0;
            $total_debit = 0;
            $total_credit = 0;

            foreach ($rows as $row) {
                if ($row['source'] === 'receipt') {
                    $payment_mode = $row['payment_mode'] ?? '';
                    $payment_type = $row['payment_type'] ?? '';
                    if (in_array($payment_mode, ['debit', 'dr', 'out'])) {
                        $debit  = floatval($row['amount']);
                        $credit = 0;
                    } elseif (in_array($payment_mode, ['credit', 'cr', 'in'])) {
                        $debit  = 0;
                        $credit = floatval($row['amount']);
                    } else {
                        if (in_array($payment_type, ['cash', 'cheque', 'upi', 'bank_transfer'])) {
                            $debit  = 0;
                            $credit = floatval($row['amount']);
                        } else {
                            $debit  = floatval($row['amount']);
                            $credit = 0;
                        }
                    }
                    $description = Carbon::parse($row['entry_date'])->format('d-m-Y');
                } else {
                    $debit = 0;
                    $credit = floatval($row['amount']);
                    $monthName = [
                        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
                        5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug',
                        9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec'
                    ][$row['month'] ?? null] ?? $row['month'] ?? '';
                    $periodText = trim(($monthName ? $monthName : '') . ' ' . ($row['year'] ?? ''));
                    $description = 'Salary Net Pay (' . $periodText . ')';
                }

                $runningBalance += ($debit - $credit);
                $total_debit    += $debit;
                $total_credit   += $credit;

                $ledger[] = [
                    'date'          => Carbon::parse($row['entry_date'])->format('d-m-Y'),
                    'employee_code' => $row['employee_code'] ?? '',
                    'full_name'     => $row['full_name'] ?? '',
                    'payment_type'  => $row['source'] === 'receipt' ? ($row['payment_type'] ?? '') : '',
                    'payment_mode'  => $row['source'] === 'receipt' ? ($row['payment_mode'] ?? '') : 'credit',
                    'cheque_no'     => $row['source'] === 'receipt' ? ($row['cheque_no'] ?? null) : null,
                    'upi_no'        => $row['source'] === 'receipt' ? ($row['upi_no'] ?? null) : null,
                    'receipt_no'    => $row['source'] === 'receipt' ? ($row['receipt_no'] ?? null) : null,
                    'debit'         => $debit,
                    'credit'        => $credit,
                    'closing_balance' => $runningBalance,
                ];
            }

            // dd($ledger);
            $employee = null;
            if ($employee_id) {
                $employee = DB::table('employees')->where('id', $employee_id)->first();
            }

            return view($modules['folder_path'] . '.print', compact(
                'ledger',
                'company_id',
                'modules',
                'employee',
                'total_debit',
                'total_credit',
                'runningBalance'
            ));
        } catch (\Exception $e) {
            return redirect()->route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
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
