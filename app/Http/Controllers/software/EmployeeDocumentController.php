<?php

namespace App\Http\Controllers\software;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;

class EmployeeDocumentController extends Controller
{
    public array $modules = [];

    public function __construct()
    {
        parent::__construct();

        $this->modules = [
            'title' => 'Employee Documents',
            'folder_path' => 'software.modules.employee.employee-documents',
            'route' => 'employee-documents',
            'table_name' => (new Employee())->getTable(),
            'permisstion_prefix' => 'employee-documents',
            'module_name' => 'Employee Documents',
            'company_id' => ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null,
        ];
    }

    private function documentTypes(): array
    {
        return [
            'advance-form' => 'Advance Form',
            'appointment' => 'Appointment',
            'experience' => 'Experience',
            'increment' => 'Increment',
            'offer' => 'Offer',
            'job-rotation' => 'Job Rotation Form',
            'job-application-form' => 'Job Application Form',
            'no-due-clearance' => 'No Due Clearance',
            'loan-form' => 'Loan Form',
            'full-final-form' => 'Full & Final Form',
            'salary-certificate' => 'Salary Certificate',
            'relieving-letter' => 'Relieving Letter',
        ];
    }

    private function documentMeta(?string $documentType): array
    {
        $documentType = trim((string) $documentType);
        $documentTypes = $this->documentTypes();

        return match ($documentType) {
            'advance-form' => ['title' => 'Salary Advance Request Form', 'requires_employee' => true],
            'increment' => ['title' => 'SALARY INCREMENT LETTER', 'requires_employee' => true],
            'appointment' => ['title' => 'Appointment Letter', 'requires_employee' => true],
            'experience' => ['title' => 'EXPERIENCE LETTER', 'requires_employee' => true],
            'offer' => ['title' => 'OFFER-LETTER', 'requires_employee' => true],
            'job-rotation' => ['title' => 'JOB ROTATION FORM', 'requires_employee' => true],
            'job-application-form' => ['title' => 'Job Application Form', 'requires_employee' => true],
            'no-due-clearance' => ['title' => 'NO DUE CLEARANCE FORM', 'requires_employee' => true],
            'loan-form' => ['title' => 'EMPLOYEE LOAN APPLICATION FORM', 'requires_employee' => true],
            'full-final-form' => ['title' => 'FULL & FINAL SETTLEMENT STATEMENT', 'requires_employee' => true],
            'salary-certificate' => ['title' => 'SALARY CERTIFICATE', 'requires_employee' => true],
            'relieving-letter' => ['title' => 'RELIEVING LETTER', 'requires_employee' => true],
            default => [
                'title' => $documentTypes[$documentType] ?? 'Employee Document',
                'requires_employee' => true,
            ],
        };
    }

    private function resolveRouteOrInput(Request $request, string $key): mixed
    {
        $routeValue = $request->route($key);
        if ($routeValue !== null && $routeValue !== '') {
            return $routeValue;
        }

        $inputValue = $request->input($key);
        if ($inputValue !== null && $inputValue !== '') {
            return $inputValue;
        }

        return null;
    }

    private function resolveSelectedCompanyId(Request $request): ?int
    {
        $companyId = $this->resolveRouteOrInput($request, 'company_id');

        if ($companyId === null && !empty($this->authenticateLoginUserDetails?->company_id)) {
            $companyId = $this->authenticateLoginUserDetails?->company_id;
        }

        return $companyId !== null && $companyId !== '' ? (int) $companyId : null;
    }

    private function resolveSelectedEmployee(Request $request, ?int $selectedCompanyId = null): ?Employee
    {
        $employeeId = $this->resolveRouteOrInput($request, 'employee_id');
        if (!$employeeId) {
            return null;
        }

        $query = Employee::with([
            'company',
            'branch',
            'parentEmployee',
            'parentEmployee.employmentDetail.designation',
            'country',
            'state',
            'city',
            'current_role',
            'employmentDetail.designation',
            'employmentDetail.department',
            'employmentDetail.subdepartment',
            'employmentDetail.process',
            'employmentDetail.employee_type',
            'employmentDetail.shiftDetail',
            'education_experience_details',
            'loans.loan_type',
            'increment_details.designation',
            'salary_details',
            'salaries',
        ]);

        if (!empty($this->authenticateLoginUserDetails?->company_id)) {
            $query->where('company_id', $this->authenticateLoginUserDetails->company_id);
        } elseif (!empty($selectedCompanyId)) {
            $query->where('company_id', $selectedCompanyId);
        }

        return $query->find($employeeId);
    }

    private function resolveDocumentType(Request $request): string
    {
        $documentType = trim((string) $this->resolveRouteOrInput($request, 'document_type'));
        if ($documentType === '') {
            return '';
        }

        if (!array_key_exists($documentType, $this->documentTypes())) {
            return '';
        }

        return $documentType;
    }

    public function index(Request $request)
    {
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['currentGuard'] = ($this->currentGuard) ? $this->currentGuard : null;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;
        $modules['parent_type_id'] = ($this->authenticateLoginUserDetails?->parent_type_id) ? $this->authenticateLoginUserDetails?->parent_type_id : null;
        $loginUserId = ($modules['authLoginUserDetail'] && $modules['authLoginUserDetail']?->id) ? $modules['authLoginUserDetail']?->id : null;

        if (count(config('constants.permissions'))) {
            foreach (config('constants.permissions') as $key => $value) {
                $modules[$value . '_permission'] = (isset($modules['company_id']) && !$modules['company_id']) ? true : Gate::check('hasPermission', [$value, $modules['module_name']]);
            }
        }

        if (!$modules['view_permission']) {
            if ($request->ajax()) {
                return $this->sendError('Unauthorized', [], [], 403);
            }
            abort(403, 'Unauthorized');
        }

        try {
            View::share('modules', $modules);

            $selectedCompanyId = $this->resolveSelectedCompanyId($request);
            $selectedEmployee = $this->resolveSelectedEmployee($request, $selectedCompanyId);
            $selectedDocumentType = $this->resolveDocumentType($request);
            $documentMeta = $this->documentMeta($selectedDocumentType);
            $latestSalary = $selectedEmployee?->salary_details?->sortByDesc('id')->first();
            $latestMonthlySalary = $selectedEmployee?->salaries?->sortByDesc('id')->first();
            $previousSalary = $selectedEmployee?->salary_details?->sortByDesc('id')->skip(1)->first();
            $latestLoan = $selectedEmployee?->loans?->sortByDesc('id')->first();
            $backRouteParams = array_filter([
                'company_id' => $selectedCompanyId,
                'employee_id' => $selectedEmployee?->id,
                'document_type' => $selectedDocumentType ?: null,
            ], static fn($value) => $value !== null && $value !== '');

            if (!$documentMeta['requires_employee']) {
                $backRouteParams['employee_id'] = 0;
            }

            if (
                !$request->route('company_id')
                && !$request->route('employee_id')
                && !$request->route('document_type')
                && $selectedCompanyId
                && ($selectedEmployee || !$documentMeta['requires_employee'])
                && $selectedDocumentType
                && $request->getQueryString()
            ) {
                return Redirect::route($modules['route'] . '.index', $backRouteParams);
            }

            return view($modules['folder_path'] . '.index', [
                'documentTypes' => $this->documentTypes(),
                'documentMeta' => $documentMeta,
                'selectedCompanyId' => $selectedCompanyId,
                'selectedEmployeeId' => $selectedEmployee?->id,
                'selectedEmployee' => $selectedEmployee,
                'selectedDocumentType' => $selectedDocumentType,
                'latestSalary' => $latestSalary,
                'latestMonthlySalary' => $latestMonthlySalary,
                'previousSalary' => $previousSalary,
                'latestLoan' => $latestLoan,
                'backRouteParams' => $backRouteParams,
            ]);
        } catch (\Exception $e) {
            return Redirect::route('software.dashboard')->withErrors($e->getMessage());
        }
    }

    public function print(Request $request)
    {
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['currentGuard'] = ($this->currentGuard) ? $this->currentGuard : null;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;
        $modules['parent_type_id'] = ($this->authenticateLoginUserDetails?->parent_type_id) ? $this->authenticateLoginUserDetails?->parent_type_id : null;
        $loginUserId = ($modules['authLoginUserDetail'] && $modules['authLoginUserDetail']?->id) ? $modules['authLoginUserDetail']?->id : null;

        if (count(config('constants.permissions'))) {
            foreach (config('constants.permissions') as $key => $value) {
                $modules[$value . '_permission'] = (isset($modules['company_id']) && !$modules['company_id']) ? true : Gate::check('hasPermission', [$value, $modules['module_name']]);
            }
        }

        if (!$modules['view_permission']) {
            if ($request->ajax()) {
                return $this->sendError('Unauthorized', [], [], 403);
            }
            abort(403, 'Unauthorized');
        }

        try {
            View::share('modules', $modules);

            $selectedCompanyId = $this->resolveSelectedCompanyId($request);
            $selectedEmployee = $this->resolveSelectedEmployee($request, $selectedCompanyId);
            $selectedDocumentType = $this->resolveDocumentType($request);
            $documentMeta = $this->documentMeta($selectedDocumentType);

            if (!$selectedEmployee && $documentMeta['requires_employee']) {
                abort(404, 'Employee not found');
            }

            if (!$selectedDocumentType) {
                return Redirect::route($modules['route'] . '.index', array_filter([
                    'company_id' => $selectedCompanyId,
                    'employee_id' => $selectedEmployee?->id,
                ], static fn($value) => $value !== null && $value !== ''))
                    ->withErrors('Please select a document type.');
            }
            $documentTypes = $this->documentTypes();
            $backRouteParams = array_filter([
                'company_id' => $selectedCompanyId,
                'employee_id' => $selectedEmployee?->id,
                'document_type' => $selectedDocumentType,
            ], static fn($value) => $value !== null && $value !== '');

            if (!$documentMeta['requires_employee']) {
                $backRouteParams['employee_id'] = 0;
            }

            if (
                !$request->route('company_id')
                && !$request->route('employee_id')
                && !$request->route('document_type')
                && $request->getQueryString()
            ) {
                return Redirect::route($modules['route'] . '.print', $backRouteParams);
            }

            $currentEmployment = $selectedEmployee?->employmentDetail
                ?? $selectedEmployee?->employment_details?->sortByDesc('id')->first();
            $latestIncrement = $selectedEmployee?->increment_details?->sortByDesc('id')->first();
            $latestSalary = $selectedEmployee?->salary_details?->sortByDesc('id')->first();
            $latestMonthlySalary = $selectedEmployee?->salaries?->sortByDesc('id')->first();
            $previousSalary = $selectedEmployee?->salary_details?->sortByDesc('id')->skip(1)->first();
            $latestLoan = $selectedEmployee?->loans?->sortByDesc('id')->first();

            return view($modules['folder_path'] . '.print', [
                'documentTypes' => $documentTypes,
                'documentMeta' => $documentMeta,
                'selectedEmployee' => $selectedEmployee,
                'selectedDocumentType' => $selectedDocumentType,
                'currentEmployment' => $currentEmployment,
                'latestIncrement' => $latestIncrement,
                'latestSalary' => $latestSalary,
                'latestMonthlySalary' => $latestMonthlySalary,
                'previousSalary' => $previousSalary,
                'latestLoan' => $latestLoan,
                'backRouteParams' => $backRouteParams,
            ]);
        } catch (\Exception $e) {
            return Redirect::route('software.dashboard')->withErrors($e->getMessage());
        }
    }
}
