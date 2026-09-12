<?php

namespace App\Http\Controllers\software;

use App\Http\Controllers\Controller;
use App\Http\Requests\OperationsRateListRequest;
use App\Models\OperationsRateList;
use App\Models\Product;
use App\Models\Grade;
use App\Models\Company;
use App\Models\ContractProcess;
use App\Models\Employee;
use App\Models\EmploymentDetail;
use App\Models\EmployeeType;
use App\Models\AccountLedger;
use App\Models\Salary;
use App\Helpers\Helper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Exports\OperationsRateListExport;
use App\Exports\OperationsRateListRowExport;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\DataTables;

class OperationsRateListController extends Controller
{
    public $modules = [];
    private array $globalEmployeeOps = ['Lathe Employee wise', 'GRINDING', 'CNC', 'ARGON WELDING', 'BUFF', 'COATING', 'ASS-1', 'ASS-2', 'RRL', 'BUTTERFLY', 'FLEXIBLE', 'CORE', 'CLEANING', 'FOUNDRY'];

    private function applyGroupScope($query, OperationsRateList $record)
    {
        $query->where([
            'company_id' => $record->company_id,
            'operation' => $record->operation,
            'month' => $record->month,
            'year' => $record->year,
        ]);

        if (!is_null($record->employee_id)) {
            $query->where('employee_id', $record->employee_id);
        }

        return $query;
    }

    private function isGlobalEmployeeOperation(?string $operation): bool
    {
        return in_array($operation, $this->globalEmployeeOps, true);
    }

    private function salaryExistsForPeriod(OperationsRateList $record, int $employeeId): bool
    {
        return Salary::where('company_id', $record->company_id)
            ->where('employee_id', $employeeId)
            ->where('year', $record->year)
            ->where('month', $record->month)
            ->exists();
    }

    private function upsertGeneratedSalaryRecord(OperationsRateList $record, Employee $employee, float $amount, ?int $loginUserId): array
    {
        $salary = Salary::withTrashed()
            ->where('company_id', $record->company_id)
            ->where('employee_id', $employee->id)
            ->where('year', $record->year)
            ->where('month', $record->month)
            ->first();

        if ($salary && !empty($salary->is_locked)) {
            return ['status' => 'locked', 'salary' => $salary];
        }

        $salaryData = [
            'company_id' => $record->company_id,
            'branch_id' => $employee->branch_id,
            'department_id' => $employee->employmentDetail?->department_id,
            'employee_id' => $employee->id,
            'year' => $record->year,
            'month' => $record->month,
            'added_date' => now()->format('Y-m-d'),
            'given_calculate_salary' => $amount,
            'earn_sub_total' => $amount,
            'total_earning' => $amount,
            'total_deduction' => 0,
            'net_bank_pay' => $amount,
            'status' => 'active',
            'calculation_date' => now(),
        ];

        if ($salary) {
            if ($salary->trashed()) {
                $salary->restore();
            }

            $salary->update(array_merge($salaryData, [
                'updated_by' => $loginUserId,
            ]));

            return ['status' => 'updated', 'salary' => $salary->fresh()];
        }

        $salary = Salary::create(array_merge($salaryData, [
            'created_by' => $loginUserId,
        ]));

        return ['status' => 'created', 'salary' => $salary];
    }

    private function upsertGeneratedSalaryLedger(OperationsRateList $record, Employee $employee, float $amount, ?Salary $salary = null): void
    {
        $periodLabel = Carbon::createFromDate($record->year, $record->month, 1)->format('M Y');
        $entryDate = Carbon::create($record->year, $record->month, 1)->endOfMonth()->toDateString();
        $description = ($employee->employee_code ?? '-') . ' - ' . ($employee->full_name ?? $employee->proper_name ?? '-') . ' Salary Net Pay (' . $periodLabel . ')';
        $receiptId = $salary ? 'salary-' . $salary->id : 'operation-rate-' . $record->id . '-' . $employee->id;

        $ledgerData = [
            'company_id' => $record->company_id,
            'employee_id' => $employee->id,
            'entry_date' => $entryDate,
            'receipt_id' => $receiptId,
            'payment_mode' => 'credit',
            'amount' => $amount,
            'debit_amount' => 0,
            'credit_amount' => $amount,
            'description' => $description,
            'closing_balance' => 0,
        ];

        $existingLedger = AccountLedger::where('company_id', $record->company_id)
            ->where('employee_id', $employee->id)
            ->whereRaw('YEAR(entry_date) = ? AND MONTH(entry_date) = ?', [$record->year, $record->month])
            ->where(function ($query) use ($receiptId) {
                $query->where('receipt_id', $receiptId)
                    ->orWhere('description', 'like', '%Salary Net Pay%');
            })
            ->latest('id')
            ->first();

        if ($existingLedger) {
            $existingLedger->update($ledgerData);
            return;
        }

        AccountLedger::create($ledgerData);
    }

    public function __construct()
    {
        parent::__construct();

        $this->modules = [
            'title' => 'Operations Rate List',
            'folder_path' => 'software.modules.operations_rate_list',
            'route' => 'operations-rate-list',
            'table_name' => (new OperationsRateList())->getTable(),
            'permisstion_prefix' => 'operations_rate_list',
            'module_name' => 'Operations Rate List',
            'company_id' => ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null,
        ];

        View::share('operations', OperationsRateList::$statuses);
    }

    public function index(Request $request)
    {
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;

        if (count(config('constants.permissions'))) {
            foreach (config('constants.permissions') as $key => $value) {
                $modules[$value . '_permission'] = (isset($modules['company_id']) && !$modules['company_id'])
                    ? true
                    : Gate::check('hasPermission', [$value, $modules['module_name']]);
            }
        }

        if (!$modules['view_permission']) {
            abort(403, 'Unauthorized');
        }

        View::share('modules', $modules);

        try {
            $columns = [
                (object) ['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'td_label' => 'Sr No.', 'className' => 'w-5 text-start', 'orderable' => false, 'searchable' => false],
                (object) ['data' => "company.company_name", 'name' => 'company_id', 'td_label' => 'Company Name', 'className' => ''],
                (object) ['data' => "operation", 'name' => 'operation', 'td_label' => 'Operation', 'className' => ''],
                (object) ['data' => "employee.full_name", 'name' => 'employee_id', 'td_label' => 'Name', 'className' => '', 'defaultContent' => '-'],
                (object) ['data' => "month", 'name' => 'month', 'td_label' => 'Month', 'className' => ''],
                (object) ['data' => "year", 'name' => 'year', 'td_label' => 'Year', 'className' => ''],
                (object) ['data' => "total_qty", 'name' => 'total_qty', 'td_label' => 'Group Total Qty', 'className' => ''],
                (object) ['data' => "total_amount", 'name' => 'total_amount', 'td_label' => 'Group Total Amount', 'className' => ''],
                (object) ['data' => "action", 'name' => 'action', 'td_label' => 'Action', 'orderable' => false, 'searchable' => false, 'className' => 'w-10 text-start'],
            ];

            if ($modules['company_id']) {
                $columns = array_filter($columns, fn($col) => $col->name !== 'company_id');
                $columns = array_values($columns);
            }

            View::share("columns", $columns);

            if ($request->ajax()) {
                $data = OperationsRateList::with('company')->leftJoin('employees', 'employees.id', '=', 'operations_rate_lists.employee_id')
                    ->selectRaw('MIN(operations_rate_lists.id) as id, 
                                 operations_rate_lists.company_id, 
                                 operations_rate_lists.operation, 
                                 MIN(operations_rate_lists.employee_id) as employee_id,
                                 operations_rate_lists.month, 
                                 operations_rate_lists.year, 
                                 SUM(operations_rate_lists.total_qty) as total_qty, 
                                 SUM(operations_rate_lists.total_amount) as total_amount, 
                                 operations_rate_lists.status, 
                                 GROUP_CONCAT(DISTINCT CONCAT(employees.employee_code, " - ", employees.full_name) SEPARATOR ", ") as multi_names')
                    ->where(function ($query) use ($modules, $request) {
                        $qTable = 'operations_rate_lists';
                        if (Auth::guard('employees')->check() || !empty($modules['company_id'])) {
                            $companyId = $modules['company_id'] ?? Auth::guard('employees')->user()->company_id;
                            $query->where($qTable . '.company_id', $companyId);
                        } elseif ($request->filter_company) {
                            $query->where($qTable . '.company_id', $request->filter_company);
                        }

                        if ($request->operation) {
                            $query->where($qTable . '.operation', $request->operation);
                        }
                        if ($request->employee_id) {
                            $query->where($qTable . '.employee_id', $request->employee_id);
                        }
                        if ($request->month) {
                            $query->where($qTable . '.month', $request->month);
                        }
                        if ($request->year) {
                            $query->where($qTable . '.year', $request->year);
                        }
                    })
                    ->groupBy(['company_id', 'operation', 'employee_id', 'month', 'year', 'status'])
                    ->orderBy('id', 'DESC');

                return Datatables::of($data)
                    ->addIndexColumn()
                    ->editColumn('month', function ($row) {
                        return date('F', mktime(0, 0, 0, $row->month, 10));
                    })
                    ->editColumn('employee.full_name', function ($row) {
                        if ($row->multi_names) {
                            $names = explode(', ', $row->multi_names);
                            if (count($names) > 1) {
                                return 'ALL EMPLOYEE';
                            }
                            return $row->multi_names;
                        }
                        return '-';
                    })
                    ->addColumn('action', function ($row) use ($modules) {
                        $btn = '<div class="d-inline-flex">';
                        $btn .= '<div class="dropdown">';
                        $btn .= '<a href="javascript:;" class="btn btn-sm btn-icon btn-light hide-arrow" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></a>';
                        $btn .= '<div class="dropdown-menu dropdown-menu-end m-0">';

                        // Edit
                        if ($modules['update_permission']) {
                            $btn .= '<a href="' . route($modules["route"] . ".edit", [$row->id]) . '" class="dropdown-item"><i class="ti ti-pencil me-1"></i> Edit</a>';
                        }

                        // Export Row Excel
                        $btn .= '<a href="' . route($modules["route"] . ".export-row-excel", [$row->id]) . '" class="dropdown-item"><i class="ti ti-file-spreadsheet me-1"></i> Export Excel</a>';

                        // Generate Salary
                        if ($this->isGlobalEmployeeOperation($row->operation)) {
                            $isGenerated = false;
                            if ($row->employee_id) {
                                $isGenerated = \App\Models\Salary::where('company_id', $row->company_id)
                                    ->where('employee_id', $row->employee_id)
                                    ->where('year', $row->year)
                                    ->where('month', $row->month)
                                    ->exists();
                            }

                            if ($isGenerated) {
                                $btn .= '<a href="javascript:void(0)" class="dropdown-item text-muted disabled" style="pointer-events: none;"><i class="ti ti-currency-dollar me-1"></i> Salary Generated</a>';
                            } else {
                                $btn .= '<a href="javascript:void(0)" data-id="' . $row->id . '" data-operation="' . htmlspecialchars($row->operation, ENT_QUOTES) . '" class="dropdown-item generate-salary"><i class="ti ti-currency-dollar me-1"></i> Generate Salary</a>';
                            }
                        }

                        $btn .= '<div class="dropdown-divider"></div>';

                        // Delete
                        if ($modules['delete_permission']) {
                            $btn .= '<a href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . route($modules["route"] . ".destroy", [$row->id]) . '" class="dropdown-item deletebutton text-danger"><i class="ti ti-trash me-1"></i> Delete</a>';
                        }

                        $btn .= '</div></div></div>';
                        return $btn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
            }

            $company_id = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;
            $contractEmployees = $this->getContractEmployees($company_id);

            return view($modules['folder_path'] . '.index', compact('contractEmployees'));
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
                $modules[$value . '_permission'] = (isset($modules['company_id']) && !$modules['company_id'])
                    ? true
                    : Gate::check('hasPermission', [$value, $modules['module_name']]);
            }
        }

        if (!$modules['add_permission']) {
            abort(403, 'Unauthorized');
        }

        View::share('modules', $modules);

        $company_id = old('company_id') ?? $modules['company_id'];
        $operation = old('operation');

        $products = Product::where('status', 'active')
            ->when($company_id, function ($query) use ($company_id) {
                return $query->where('company_id', $company_id);
            })
            ->when($operation, function ($query) use ($operation) {
                return $query->where('operation', $operation);
            })
            ->get();

        $grades = Grade::where('status', 'active')
            ->when($company_id, function ($query) use ($company_id) {
                return $query->where('company_id', $company_id);
            })
            ->get();

        // Contract employees for Lathe Employee wise
        $contractEmployees = $this->getContractEmployees($company_id);

        // Contract processes filtered by operation's products
        $productsForProc = Product::where('status', 'active')
            ->where('operation', $operation)
            ->when($company_id, function ($query) use ($company_id) {
                return $query->where('company_id', $company_id);
            })
            ->whereNotNull('process')
            ->get(['process', 'rate', 'rejection_rate', 'ot_text']);

        $processNames = $productsForProc->pluck('process')->unique();

        $contractProcessesQuery = ContractProcess::where('status', 'active')
            ->when($company_id, function ($query) use ($company_id) {
                return $query->where('company_id', $company_id);
            });

        if ($processNames->isNotEmpty()) {
            $contractProcessesQuery->whereIn('name', $processNames);
        }

        $rawProcesses = $contractProcessesQuery->get();

        // Map Product Master rates to Contract Processes
        $contractProcesses = $rawProcesses->map(function ($cp) use ($productsForProc, $operation) {
            // Case-insensitive match for process name
            $pData = $productsForProc->first(function ($p) use ($cp) {
                return strtolower(trim($p->process)) === strtolower(trim($cp->name));
            });

            if ($pData) {
                $cp->rate = $pData->rate;
                $cp->rejection_rate = $pData->rejection_rate;
                $cp->ot_rate = $pData->ot_text; // Temporary property for JS
            }
            return $cp;
        });

        // SPECIAL: For Lathe Employee wise, we use Products as the source for processes
        if ($operation === 'Lathe Employee wise') {
            $contractProcesses = $productsForProc->filter(fn($p) => !empty($p->process))
                ->map(fn($p) => (object) [
                    'id' => $p->id,
                    'name' => $p->process,
                    'rate' => $p->rate,
                    'rejection_rate' => $p->rejection_rate,
                    'ot_text' => $p->ot_text,
                    'ot_rate' => $p->ot_text
                ]);
        }

        return view($modules['folder_path'] . '.form', compact('products', 'grades', 'contractEmployees', 'contractProcesses'));
    }

    public function store(OperationsRateListRequest $request)
    {
        $modules = $this->modules;
        $loginUserId = Auth::id();
        $items = $request->input('items', []);

        $company_id = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : $request->company_id;
        $operation = $request->operation;
        $month = $request->month;
        $year = $request->year;

        try {
            // Pre-check: detect duplicate entries for same employee + product + company + operation + month + year
            $duplicateDetails = [];
            $seenInRequest = [];

            foreach ($items as $index => $item) {
                $itemEmployeeId = $item['employee_id'] ?? ($request->global_employee_id ?? null);
                $itemProductId = $item['product_id'] ?? null;
                $itemGradeId = $item['grade_id'] ?? null;
                $itemProcessId = $item['contract_process_id'] ?? null;

                if (!$itemEmployeeId)
                    continue;

                // 1. Check for duplicates WITHIN the request itself (same employee/product/process in same form)
                $key = "{$itemEmployeeId}-" . ($itemProductId ?? '0') . "-" . ($itemGradeId ?? '0') . "-" . ($itemProcessId ?? '0');
                if (isset($seenInRequest[$key])) {
                    $empName = Employee::find($itemEmployeeId)?->proper_name ?? 'Unknown';
                    return Redirect::back()->withErrors("Duplicate row found in your form for employee: {$empName}. Each unique product/process for an employee should be in one row.")->withInput();
                }
                $seenInRequest[$key] = true;

                // 2. Check against Database
                $exists = OperationsRateList::where([
                    'company_id' => $company_id,
                    'operation' => $operation,
                    'month' => $month,
                    'year' => $year,
                    'employee_id' => $itemEmployeeId,
                ])
                    ->when($itemProductId, fn($q) => $q->where('product_id', $itemProductId))
                    ->when($itemGradeId, fn($q) => $q->where('grade_id', $itemGradeId))
                    ->when($itemProcessId, fn($q) => $q->where('contract_process_id', $itemProcessId))
                    ->exists();

                if ($exists) {
                    $duplicateDetails[] = Employee::find($itemEmployeeId)?->proper_name ?? 'Unknown';
                }
            }

            if (!empty($duplicateDetails)) {
                $names = array_values(array_unique($duplicateDetails));
                $msg = 'Entries already exist in the system for: ' . implode(', ', $names) . ' for the same Operation/Month/Year. Please edit existing records instead of creating new ones.';
                return Redirect::back()->withErrors($msg)->withInput();
            }

            foreach ($items as $item) {
                $data = $item;
                $data['company_id'] = $company_id;
                $data['operation'] = $operation;
                $data['month'] = $month;
                $data['year'] = $year;
                $data['created_by'] = $loginUserId;
                $data['status'] = 'active';

                if (in_array($operation, ['CLEANING', 'Lathe Employee wise', 'GRINDING', 'CNC', 'ARGON WELDING', 'BUFF', 'COATING', 'ASS-1', 'ASS-2', 'RRL', 'BUTTERFLY', 'FLEXIBLE', 'CORE', 'FOUNDRY'])) {
                    $data['employee_id'] = $item['employee_id'] ?? ($request->global_employee_id ?? null);
                    $data['contract_process_id'] = ($operation === 'Lathe Employee wise') ? ($item['contract_process_id'] ?? null) : null;
                    $data['product_id'] = $item['product_id'] ?? null;
                    $data['grade_id'] = $item['grade_id'] ?? null;
                } else {
                    $data['employee_id'] = null;
                    $data['contract_process_id'] = null;
                    $data['product_id'] = $item['product_id'] ?? null;
                    $data['grade_id'] = $item['grade_id'] ?? null;
                }

                // Calculate totals
                $total_qty = 0;
                $line_amount = 0;
                $stdRate = floatval($item['rate'] ?? 0);

                // Get Product specific rates
                $otRate = 0;
                $rejRate = 0;
                if (!empty($item['product_id'])) {
                    $prod = Product::find($item['product_id']);
                    $otRate = floatval($prod?->ot_text ?? 0);
                    $rejRate = floatval($prod?->rejection_rate ?? 0);
                }

                for ($i = 1; $i <= 31; $i++) {
                    // Normal day value
                    $val = floatval($item['day_' . $i] ?? 0);
                    $total_qty += $val;
                    $data['day_' . $i] = $val;
                    $line_amount += ($val * $stdRate);

                    // Rate/Rejection (R) value
                    $rVal = floatval($item['day_' . $i . '_r'] ?? 0);
                    $total_qty += $rVal;
                    $data['day_' . $i . '_r'] = $rVal;
                    $line_amount += ($rVal * ($operation === 'FOUNDRY' ? $stdRate : $rejRate));

                    // Overtime (OT) value
                    $otVal = floatval($item['day_' . $i . '_ot'] ?? 0);
                    $total_qty += $otVal;
                    $data['day_' . $i . '_ot'] = $otVal;
                    $line_amount += ($otVal * $otRate);
                }
                $data['total_qty'] = $total_qty;
                $data['total_amount'] = $line_amount;

                OperationsRateList::create($data);
            }

            return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' records created successfully');
        } catch (\Exception $e) {
            return Redirect::back()->withErrors($e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;

        if (count(config('constants.permissions'))) {
            foreach (config('constants.permissions') as $key => $value) {
                $modules[$value . '_permission'] = (isset($modules['company_id']) && !$modules['company_id'])
                    ? true
                    : Gate::check('hasPermission', [$value, $modules['module_name']]);
            }
        }

        if (!$modules['update_permission']) {
            abort(403, 'Unauthorized');
        }

        $base_edit = OperationsRateList::findOrFail($id);

        if ($modules['company_id'] && $base_edit->company_id != $modules['company_id']) {
            abort(403, 'Unauthorized');
        }

        // Always keep edit scoped to the same employee group when employee-wise data is stored.
        $edits = $this->applyGroupScope(OperationsRateList::query(), $base_edit)->get();

        View::share('modules', $modules);
        View::share('edit', $base_edit);
        View::share('edits', $edits);

        $products = Product::where('status', 'active')
            ->when($base_edit->company_id, function ($query) use ($base_edit) {
                return $query->where('company_id', $base_edit->company_id);
            })
            ->where('operation', $base_edit->operation)
            ->get();
        $grades = Grade::where('status', 'active')
            ->when($modules['company_id'], function ($query) use ($modules) {
                return $query->where('company_id', $modules['company_id']);
            })
            ->get();

        // Contract employees for Lathe Employee wise
        $contractEmployees = $this->getContractEmployees($base_edit->company_id);

        // Contract processes filtered by operation's products
        $processNames = $products->pluck('process')->filter()->unique()->values();

        $contractProcessesQuery = ContractProcess::where('status', 'active')
            ->when($base_edit->company_id, function ($query) use ($base_edit) {
                return $query->where('company_id', $base_edit->company_id);
            });

        if ($processNames->isNotEmpty()) {
            $contractProcessesQuery->whereIn('name', $processNames);
        }

        $rawProcesses = $contractProcessesQuery->get();

        // Map Product Master rates to Contract Processes
        $contractProcesses = $rawProcesses->map(function ($cp) use ($products) {
            // Case-insensitive match
            $pData = $products->first(function ($p) use ($cp) {
                return strtolower(trim($p->process)) === strtolower(trim($cp->name));
            });

            if ($pData) {
                $cp->rate = $pData->rate;
                $cp->rejection_rate = $pData->rejection_rate;
                $cp->ot_rate = $pData->ot_text;
            }
            return $cp;
        });

        // SPECIAL: For Lathe Employee wise, we use Products as the source for processes
        if ($base_edit->operation === 'Lathe Employee wise') {
            $contractProcesses = $products->filter(fn($p) => !empty($p->process))
                ->map(fn($p) => (object) [
                    'id' => $p->id,
                    'name' => $p->process,
                    'rate' => $p->rate,
                    'rejection_rate' => $p->rejection_rate,
                    'ot_text' => $p->ot_text,
                    'ot_rate' => $p->ot_text
                ]);
        }

        return view($modules['folder_path'] . '.form', compact('products', 'grades', 'contractEmployees', 'contractProcesses'));
    }

    public function update(OperationsRateListRequest $request, $id)
    {
        $modules = $this->modules;
        $loginUserId = Auth::id();
        $baseRecord = OperationsRateList::findOrFail($id);

        $company_id = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : $request->company_id;
        $operation = $request->operation;
        $month = $request->month;
        $year = $request->year;

        $items = $request->input('items', []);

        try {
            // Replace only the current employee group, not every employee for the same month/year.
            $this->applyGroupScope(OperationsRateList::query(), $baseRecord)->delete();

            // Re-insert items
            foreach ($items as $item) {
                $data = $item;
                $data['company_id'] = $company_id;
                $data['operation'] = $operation;
                $data['month'] = $month;
                $data['year'] = $year;
                $data['created_by'] = $loginUserId;
                $data['status'] = 'active';

                if (in_array($operation, ['CLEANING', 'Lathe Employee wise', 'GRINDING', 'CNC', 'ARGON WELDING', 'BUFF', 'COATING', 'ASS-1', 'ASS-2', 'RRL', 'BUTTERFLY', 'FLEXIBLE', 'CORE', 'FOUNDRY'])) {
                    $data['employee_id'] = $item['employee_id'] ?? ($request->global_employee_id ?? null);
                    $data['contract_process_id'] = ($operation === 'Lathe Employee wise') ? ($item['contract_process_id'] ?? null) : null;
                    $data['product_id'] = $item['product_id'] ?? null;
                    $data['grade_id'] = $item['grade_id'] ?? null;
                } else {
                    $data['employee_id'] = null;
                    $data['contract_process_id'] = null;
                    $data['product_id'] = $item['product_id'] ?? null;
                    $data['grade_id'] = $item['grade_id'] ?? null;
                }

                // Re-calculate totals
                $total_qty = 0;
                $line_amount = 0;
                $stdRate = floatval($item['rate'] ?? 0);

                // Get Product specific rates
                $otRate = 0;
                $rejRate = 0;
                if (!empty($item['product_id'])) {
                    $prod = Product::find($item['product_id']);
                    $otRate = floatval($prod?->ot_text ?? 0);
                    $rejRate = floatval($prod?->rejection_rate ?? 0);
                }

                for ($i = 1; $i <= 31; $i++) {
                    // Normal day value
                    $val = floatval($item['day_' . $i] ?? 0);
                    $total_qty += $val;
                    $data['day_' . $i] = $val;
                    $line_amount += ($val * $stdRate);

                    // Rate/Rejection (R) value
                    $rVal = floatval($item['day_' . $i . '_r'] ?? 0);
                    $total_qty += $rVal;
                    $data['day_' . $i . '_r'] = $rVal;
                    $line_amount += ($rVal * ($operation === 'FOUNDRY' ? $stdRate : $rejRate));

                    // Overtime (OT) value
                    $otVal = floatval($item['day_' . $i . '_ot'] ?? 0);
                    $total_qty += $otVal;
                    $data['day_' . $i . '_ot'] = $otVal;
                    $line_amount += ($otVal * $otRate);
                }
                $data['total_qty'] = $total_qty;
                $data['total_amount'] = $line_amount;

                OperationsRateList::create($data);
            }

            return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' updated successfully');
        } catch (\Exception $e) {
            return Redirect::back()->withErrors($e->getMessage())->withInput();
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
            $record = OperationsRateList::findOrFail($request->id);

            $this->applyGroupScope(OperationsRateList::query(), $record)
                ->update(['status' => $request->update_status]);

            if ($request->ajax()) {
                return $this->sendResponse($record, $modules['title'] . ' status updated successfully.');
            }
            return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' status updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), $e->getMessage(), [], 401);
        }
    }

    public function destroy(Request $request, $id)
    {
        $modules = $this->modules;
        $baseRecord = OperationsRateList::findOrFail($id);

        try {
            $this->applyGroupScope(OperationsRateList::query(), $baseRecord)->delete();

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => $modules['title'] . ' group deleted successfully']);
            }
            return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' group deleted successfully');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()]);
            }
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    public function exportExcel(Request $request)
    {
        $modules = $this->modules;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;
        $user = Auth::user();

        return Excel::download(new OperationsRateListExport($request->all(), $user, $modules), $modules['title'] . '-' . date('d-m-Y') . '.xlsx');
    }

    public function print(Request $request)
    {
        $modules = $this->modules;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;
        $user = Auth::user();

        $data = (new OperationsRateListExport($request->all(), $user, $modules))->collection();

        return view($modules['folder_path'] . '.print', compact('data', 'modules', 'user'));
    }

    public function get_products_grades(Request $request)
    {
        try {
            $user_company_id = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;
            $company_id = $request->company_id ?: $user_company_id;
            $operation = $request->operation;

            $productsQuery = Product::where('status', 'active');
            if ($company_id) {
                $productsQuery->where('company_id', $company_id);
            }
            if ($operation) {
                $productsQuery->where('operation', $operation);
            }
            $products = $productsQuery->get(['id', 'name', 'rate', 'process', 'ot_text', 'rejection_rate']);

            $grades = Grade::where('status', 'active')
                ->when($company_id, function ($query) use ($company_id) {
                    return $query->where('company_id', $company_id);
                })
                ->get(['id', 'name']);

            // Filter Contract Processes based on Product Master's process field
            $processNames = $products->pluck('process')->filter()->unique()->values();

            $contractProcessesQuery = ContractProcess::where('status', 'active')
                ->when($company_id, function ($query) use ($company_id) {
                    return $query->where('company_id', $company_id);
                });

            if ($processNames->isNotEmpty()) {
                $contractProcessesQuery->whereIn('name', $processNames);
            }

            $rawProcesses = $contractProcessesQuery->get(['id', 'name', 'rate', 'rejection_rate']);

            // Map Product Master rates to Contract Processes
            $contractProcesses = $rawProcesses->map(function ($cp) use ($products) {
                // Case-insensitive match
                $pData = $products->first(function ($p) use ($cp) {
                    return strtolower(trim($p->process)) === strtolower(trim($cp->name));
                });

                return [
                    'id' => $cp->id,
                    'name' => $cp->name,
                    'rate' => $pData ? $pData->rate : $cp->rate,
                    'ot_rate' => $pData ? $pData->ot_text : null,
                    'rejection_rate' => $pData ? $pData->rejection_rate : $cp->rejection_rate
                ];
            });

            // SPECIAL: For Lathe Employee wise, we return Products as processes
            if ($operation === 'Lathe Employee wise') {
                $contractProcesses = $products->filter(fn($p) => !empty($p->process))
                    ->map(fn($p) => [
                        'id' => $p->id,
                        'name' => $p->process,
                        'rate' => $p->rate,
                        'ot_rate' => $p->ot_text,
                        'rejection_rate' => $p->rejection_rate
                    ])->values();
            }

            $contractEmployees = $this->getContractEmployees($company_id);

            return response()->json([
                'products' => $products->map(fn($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'rate' => $p->rate,
                    'process' => $p->process,
                    'ot_rate' => $p->ot_text,
                    'rejection_rate' => $p->rejection_rate
                ]),
                'grades' => $grades,
                'contractProcesses' => $contractProcesses,
                'contractEmployees' => $contractEmployees->map(fn($e) => ['id' => $e->id, 'name' => $e->employee_code . ' - ' . $e->proper_name]),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
        }
    }

    /**
     * Get week-off days (Wednesdays) for the given month & year
     */
    public function getWeekOffDays(Request $request)
    {
        $month = (int) $request->month;
        $year = (int) $request->year;

        if (!$month || !$year) {
            return response()->json(['week_off_days' => [], 'days_in_month' => 31]);
        }

        $weekOffDays = [];
        $date = \Carbon\Carbon::createFromDate($year, $month, 1);
        $daysInMonth = $date->daysInMonth;

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dow = $date->copy()->day($d)->dayOfWeek; // 0=Sun, 1=Mon, ..., 3=Wed
            if ($dow == \Carbon\Carbon::WEDNESDAY) {
                $weekOffDays[] = $d;
            }
        }

        return response()->json([
            'week_off_days' => $weekOffDays,
            'days_in_month' => $daysInMonth
        ]);
    }

    /**
     * Export a single group row Excel (with all 31 day columns)
     */
    public function exportRowExcel(Request $request, $id)
    {
        $modules = $this->modules;
        $baseRecord = OperationsRateList::findOrFail($id);
        $rowsQuery = OperationsRateList::with(['product', 'grade', 'employee', 'contractProcess', 'company'])
            ->where([
                'company_id' => $baseRecord->company_id,
                'operation' => $baseRecord->operation,
                'month' => $baseRecord->month,
                'year' => $baseRecord->year,
            ]);

        // Check if it is a global employee operation
        if ($this->isGlobalEmployeeOperation($baseRecord->operation)) {
            $rowsQuery->where('employee_id', $baseRecord->employee_id);
        }

        $rows = $rowsQuery->get();

        $filename = $modules['title'] . '-' . $baseRecord->operation . '-' .
            date('F', mktime(0, 0, 0, $baseRecord->month, 1)) . '-' . $baseRecord->year . '.xlsx';

        return Excel::download(new OperationsRateListRowExport($rows, $baseRecord), $filename);
    }

    /**
     * Generate salary ledger entries for a group (company, operation, month, year).
     * If employee_ids provided, only generate for those employees; otherwise all employees in group.
     */
    public function generateSalary(Request $request)
    {
        $modules = $this->modules;

        $request->validate([
            'id' => ['required', 'integer'],
        ]);

        try {
            $baseRecord = OperationsRateList::findOrFail($request->id);

            $rowsQuery = OperationsRateList::where([
                'company_id' => $baseRecord->company_id,
                'month' => $baseRecord->month,
                'year' => $baseRecord->year,
            ]);

            if ($this->isGlobalEmployeeOperation($baseRecord->operation)) {
                $rowsQuery->where('employee_id', $baseRecord->employee_id);
            }

            $rows = $rowsQuery->get();
            $loginUserId = Auth::id() ?: ($this->authenticateLoginUserDetails?->id);

            $employeeIds = $request->employee_ids ?? null;

            // Derive employees from related rows (preferred) to avoid relying on employee_id values in DB rows
            $rowsWithEmployee = $rows->load(['employee.employmentDetail']);
            $employees = $rowsWithEmployee->pluck('employee')->filter()->unique('id')->values();

            if ($employees->isEmpty()) {
                // no employees linked in this group
                // \Log::info('generateSalary: no employees found for group', ['group_id' => $baseRecord->id, 'operation' => $baseRecord->operation]);
                return $this->sendError('No employees found for this group.', 'No employees found for this group.', [], 400);
            }

            $created = 0;
            $updated = 0;
            $skipped = [];
            $details = [];

            foreach ($employees as $emp) {
                $empId = $emp->id;
                if ($employeeIds && !in_array($empId, (array) $employeeIds)) {
                    $skipped[] = $empId;
                    continue;
                }

                $items = $rows->where('employee_id', $empId);
                $amount = round((float) $items->sum('total_amount'), 2);

                if ($amount <= 0) {
                    $skipped[] = $empId;
                    $details[$empId] = 'zero_amount';
                    continue;
                }

                DB::beginTransaction();

                try {
                    $salaryResult = $this->upsertGeneratedSalaryRecord($baseRecord, $emp, $amount, $loginUserId);

                    if ($salaryResult['status'] === 'locked') {
                        DB::rollBack();
                        $skipped[] = $empId;
                        $details[$empId] = 'salary_locked';
                        continue;
                    }

                    $this->upsertGeneratedSalaryLedger($baseRecord, $emp, $amount, $salaryResult['salary']);

                    DB::commit();

                    if ($salaryResult['status'] === 'created') {
                        $created++;
                    } else {
                        $updated++;
                    }

                    $details[$empId] = $salaryResult['status'];
                } catch (\Exception $employeeException) {
                    DB::rollBack();
                    $skipped[] = $empId;
                    $details[$empId] = 'error: ' . $employeeException->getMessage();
                }
            }

            // \Log::info('generateSalary result', ['group_id' => $baseRecord->id, 'created' => $created, 'skipped' => $skipped, 'details' => $details]);

            return $this->sendResponse([
                'created' => $created,
                'updated' => $updated,
                'skipped' => $skipped,
                'details' => $details
            ], 'Salary Generated Successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), $e->getMessage(), [], 401);
        }
    }

    /**
     * Return employees present in a group (company, operation, month, year)
     */
    public function getGroupEmployees(Request $request, $id)
    {
        $modules = $this->modules;

        try {
            $baseRecord = OperationsRateList::findOrFail($id);

            $rowsQuery = OperationsRateList::with('employee')
                ->where([
                    'company_id' => $baseRecord->company_id,
                    'operation' => $baseRecord->operation,
                    'month' => $baseRecord->month,
                    'year' => $baseRecord->year,
                ]);

            if ($this->isGlobalEmployeeOperation($baseRecord->operation)) {
                $rowsQuery->where('employee_id', $baseRecord->employee_id);
            }

            $rows = $rowsQuery->get();

            $employees = $rows->pluck('employee')->filter()->unique('id')->values()->map(function ($e) use ($baseRecord) {
                $already = $this->salaryExistsForPeriod($baseRecord, $e->id);
                return ['id' => $e->id, 'name' => $e->proper_name, 'already_generated' => $already];
            });

            return response()->json(['employees' => $employees, 'operation' => $baseRecord->operation]);
        } catch (\Exception $e) {
            return response()->json(['employees' => [], 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * AJAX: Check if entries already exist for given company, operation, month and year.
     */
    public function checkDuplicate(Request $request)
    {
        $data = $request->validate([
            'company_id' => 'required|integer',
            'operation' => 'required|string',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer',
            'employee_id' => 'nullable|integer',
            'global_employee_id' => 'nullable|integer',
        ]);

        try {
            $employee_id = $data['employee_id'] ?? ($data['global_employee_id'] ?? null);

            $query = OperationsRateList::where([
                'company_id' => $data['company_id'],
                'operation' => $data['operation'],
                'month' => $data['month'],
                'year' => $data['year'],
            ]);

            if ($request->id) {
                $editRecord = OperationsRateList::find($request->id);
                if (
                    $editRecord &&
                    $editRecord->operation == $request->operation &&
                    $editRecord->month == $request->month &&
                    $editRecord->year == $request->year
                ) {
                    // If it's the same group being edited, it doesn't count as a duplicate
                    // For Global Employee operations, also check if employee matches
                    if ($this->isGlobalEmployeeOperation($request->operation)) {
                        if ($editRecord->employee_id == $employee_id) {
                            return response()->json(['exists' => false]);
                        }
                    } else {
                        return response()->json(['exists' => false]);
                    }
                }
            }

            if ($employee_id) {
                $query->where('employee_id', $employee_id);
            }

            if ($request->exclude_id) {
                $excludeRecord = OperationsRateList::find($request->exclude_id);
                if ($excludeRecord) {
                    $query->whereNot(function ($q) use ($excludeRecord) {
                        $q->where('company_id', $excludeRecord->company_id)
                            ->where('operation', $excludeRecord->operation)
                            ->where('month', $excludeRecord->month)
                            ->where('year', $excludeRecord->year);
                        if ($excludeRecord->employee_id) {
                            $q->where('employee_id', $excludeRecord->employee_id);
                        }
                    });
                }
            }

            $record = $query->first();
            $exists = ($record !== null);
            $foundName = $exists ? ($record->employee?->proper_name ?? 'an employee') : null;

            return response()->json([
                'exists' => $exists,
                'name' => $foundName
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => true, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get contract-type employees.
     * Uses EmployeeType names containing 'contract'.
     * Falls back to ALL active employees if none found (data not yet configured).
     */
    private function getContractEmployees(?int $companyId = null)
    {
        // Find contractor employee type IDs
        $contractTypeIds = EmployeeType::where('name', 'like', '%contract%')->pluck('id');

        $query = Employee::where('status', 'active')
            ->when($companyId, fn($q) => $q->where('company_id', $companyId));

        if ($contractTypeIds->isNotEmpty()) {
            // Filter by contractor employment type
            $filteredQuery = (clone $query)->whereHas('employmentDetail', function ($q) use ($contractTypeIds) {
                $q->whereIn('employment_type', $contractTypeIds);
            });

            $employees = $filteredQuery->get();
            if ($employees->isEmpty()) {
                $employees = $query->get();
            }
        } else {
            $employees = $query->get();
        }

        return $employees;
    }
}
