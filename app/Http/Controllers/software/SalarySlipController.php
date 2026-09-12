<?php

namespace App\Http\Controllers\software;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Salary;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Barryvdh\DomPDF\Facade\Pdf as DomPdf;

class SalarySlipController extends Controller
{
    public $modules = [];

    public function __construct()
    {
        parent::__construct();

        $this->modules = [
            'title' => 'Salary Slip',
            'folder_path' => 'software.modules.employee.salary-slip',
            'route' => 'salary-slip',
            'permisstion_prefix' => 'salary-slip',
            'module_name' => 'Salary Details',
            'company_id' => ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null,
        ];
    }

    /**
     * Display salary slip listing/filters.
     */
    public function index(Request $request)
    {
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = $this->authenticateLoginUserDetails;
        $modules['company_id'] = $this->authenticateLoginUserDetails?->company_id ?? null;
        $modules['parent_type_id'] = $this->authenticateLoginUserDetails?->parent_type_id ?? null;

        if (count(config('constants.permissions', []))) {
            foreach (config('constants.permissions') as $key => $value) {
                $modules[$value . '_permission'] = (isset($modules['company_id']) && !$modules['company_id'])
                    ? true
                    : Gate::check('hasPermission', [$value, $modules['module_name']]);
            }
        }
        if (!($modules['view_permission'] ?? true)) {
            if ($request->ajax()) {
                return $this->sendError('Unauthorized', [], [], 403);
            }
            abort(403, 'Unauthorized');
        }

        View::share('modules', $modules);
        View::share('columns', []);

        return view($modules['folder_path'] . '.index');
    }

    /**
     * Get salary slip report (AJAX) – returns HTML of slip(s) for given filters.
     */
    public function getSlipReport(Request $request)
    {
        $request->validate([
            'company_id' => 'required|integer',
            'followup_date' => 'required|string',
        ]);

        $companyId = (int) $request->company_id;
        if ($this->authenticateLoginUserDetails?->company_id) {
            $companyId = (int) $this->authenticateLoginUserDetails->company_id;
        }

        $employeeId = $request->employee_id ? (int) $request->employee_id : null;
        $fromToDate = $request->followup_date;

        [$year, $month] = $this->parseDateRangeToMonthYear($fromToDate);
        if (!$year || !$month) {
            return $this->sendError('Invalid date range. Use format: dd/mm/yyyy - dd/mm/yyyy', [], [], 422);
        }

        $salaries = $this->getSalariesForSlip($companyId, $year, $month, $employeeId);
        if ($salaries->isEmpty()) {
            return $this->sendResponse([
                'html' => '<div class="alert alert-warning">No salary record found for the selected period.</div>',
                'count' => 0,
            ], 'No records');
        }

        $html = '';
        foreach ($salaries as $salary) {
            $html .= view('software.modules.employee.salary-slip.partials.slip-content', [
                'salary' => $salary,
                'company' => $salary->company,
                'employee' => $salary->employee,
                'employment' => $salary->employee?->employmentDetail,
                'netPayInWords' => Helper::convertToRupees($salary->net_bank_pay ?? 0),
            ])->render();
        }

        return $this->sendResponse([
            'html' => $html,
            'count' => $salaries->count(),
        ], 'OK');
    }

    /**
     * Print view – company-wise or employee-wise, HTML (print-ready).
     */
    public function print(Request $request)
    {
        $companyId = $request->company_id ?: $this->authenticateLoginUserDetails?->company_id;
        if (!$companyId) {
            return Redirect::route('salary-slip.index')->withErrors('Please select a company.');
        }
        $companyId = (int) $companyId;
        $employeeId = $request->employee_id ? (int) $request->employee_id : null;
        $fromToDate = $request->followup_date ?? $request->get('date_range');

        [$year, $month] = $this->parseDateRangeToMonthYear($fromToDate);
        if (!$year || !$month) {
            $year = (int) date('Y');
            $month = (int) date('m');
        }

        $salaries = $this->getSalariesForSlip($companyId, $year, $month, $employeeId);
        $company = Company::find($companyId);

        if ($salaries->isEmpty()) {
            return redirect()->route('salary-slip.index')->withErrors('No salary record found for the selected period.');
        }

        $slips = $salaries->map(function ($salary) {
            return [
                'salary' => $salary,
                'company' => $salary->company,
                'employee' => $salary->employee,
                'employment' => $salary->employee?->employmentDetail,
                'netPayInWords' => Helper::convertToRupees($salary->net_bank_pay ?? 0),
            ];
        });

        $monthName = \Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y');

        return view('software.modules.employee.salary-slip.print', [
            'salaries' => $salaries,
            'slips' => $slips,
            'company' => $company ?? $salaries->first()->company,
            'year' => $year,
            'month' => $month,
            'monthName' => $monthName,
            'modules' => $this->modules,
        ]);
    }

    /**
     * Download salary slip as PDF (single or bulk).
     */
    public function pdf(Request $request)
    {
        $companyId = $request->company_id ?: $this->authenticateLoginUserDetails?->company_id;
        if (!$companyId) {
            return Redirect::route('salary-slip.index')->withErrors('Please select a company.');
        }
        $companyId = (int) $companyId;
        $employeeId = $request->employee_id ? (int) $request->employee_id : null;
        $fromToDate = $request->followup_date ?? $request->get('date_range');

        [$year, $month] = $this->parseDateRangeToMonthYear($fromToDate);
        if (!$year || !$month) {
            $year = (int) date('Y');
            $month = (int) date('m');
        }

        $salaries = $this->getSalariesForSlip($companyId, $year, $month, $employeeId);
        $company = Company::find($companyId);

        if ($salaries->isEmpty()) {
            return Redirect::route('salary-slip.index')->withErrors('No salary record found for the selected period.');
        }

        $slips = $salaries->map(function ($salary) {
            return [
                'salary' => $salary,
                'company' => $salary->company,
                'employee' => $salary->employee,
                'employment' => $salary->employee?->employmentDetail,
                'netPayInWords' => Helper::convertToRupees($salary->net_bank_pay ?? 0),
            ];
        });

        $monthName = Carbon::createFromDate($year, $month, 1)->format('F Y');
        $pdf = DomPdf::loadView('software.modules.employee.salary-slip.pdf', [
            'slips' => $slips,
            'company' => $company ?? $salaries->first()->company,
            'year' => $year,
            'month' => $month,
            'monthName' => $monthName,
        ])->setPaper('a4', 'portrait');

        $filename = 'salary-slip-' . ($employeeId ? 'employee-' . $employeeId : 'company-' . $companyId) . '-' . $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Export salary slip report to Excel (optional stub).
     */
    public function exportExcel(Request $request)
    {
        return Redirect::route('salary-slip.index')->with('info', 'Excel export can be added here.');
    }

    private function parseDateRangeToMonthYear(?string $fromToDate): array
    {
        if (!$fromToDate) {
            return [null, null];
        }
        $fromToDate = str_replace(' to ', ' - ', $fromToDate);
        if (!str_contains($fromToDate, ' - ')) {
            return [null, null];
        }
        $parts = explode(' - ', $fromToDate);
        $start = trim($parts[0] ?? '');
        if (!$start) {
            return [null, null];
        }
        try {
            $dt = Carbon::createFromFormat('d/m/Y', $start);
            return [$dt->year, $dt->month];
        } catch (\Exception $e) {
            return [null, null];
        }
    }

    private function getSalariesForSlip(int $companyId, int $year, int $month, ?int $employeeId = null)
    {
        $query = Salary::with([
            'employee.employmentDetail.designation',
            'employee.employmentDetail.department',
            'company',
            'department',
            'branch',
        ])
            ->where('company_id', $companyId)
            ->where('year', $year)
            ->where('month', $month);

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        return $query->orderBy('employee_id')->get();
    }

    public function create() {}
    public function store(Request $request) {}
    public function show(string $id) {}
    public function edit(string $id) {}
    public function update(Request $request, string $id) {}
    public function destroy(string $id) {}
}
