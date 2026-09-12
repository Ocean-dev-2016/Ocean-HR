<?php

namespace App\Imports;

use App\Helpers\Helper;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeeImportFile;
use App\Models\EmploymentDetail;
use App\Models\EmployeeIncrementDetails;
use App\Models\EmployeeWiseSalaryDetail;
use App\Models\Salary;
use App\Models\SubDepartment;
use App\Models\Process;
use App\Models\MasterCountry;
use App\Models\MasterState;
use App\Models\MasterCity;
use App\Models\Role;
use App\Models\TeamRole;
use App\Models\EmployeeType;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EmployeeImport implements ToCollection, WithHeadingRow
{
    protected $companyId;
    protected $userId;
    protected $guard;
    protected $employeeImportFileId;
    protected $authLoginUserDetail;

    protected $totalEmployees = 0;
    protected $successCount = 0;
    protected $failedCount = 0;
    protected $errors = [];

    public function __construct($companyId, $userId, $guard, $employeeImportFileId, $authLoginUserDetail)
    {
        $this->companyId = $companyId;
        $this->userId = $userId;
        $this->guard = $guard;
        $this->employeeImportFileId = $employeeImportFileId;
        $this->authLoginUserDetail = $authLoginUserDetail;
    }

    public function collection(Collection $rows)
    {
        Log::info('Employee Import Started. Total Rows: ' . $rows->count());

        DB::beginTransaction();
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;
            if ($row->filter()->isEmpty()) continue;

            $this->totalEmployees++;
            $validationErrors = [];

            // Basic fields - Required fields only
            $employeeCode = trim($row['employee_code'] ?? '');
            $fullName     = trim($row['employee_full_name'] ?? '');
            $firstName    = trim($row['first_name'] ?? '');
            $middleName   = trim($row['middle_name'] ?? '');
            $fatherName   = trim($row['father_name'] ?? '');
            $username     = trim($row['username'] ?? '');
            $password     = trim($row['password'] ?? '');
            $gender       = trim($row['gender'] ?? '');
            $currentAddress   = trim($row['current_address'] ?? '');
            $permanentAddress = trim($row['permanent_address'] ?? '');
            $aadhar           = trim($row['aadhar_card_number'] ?? '');
            $pan              = trim($row['pan_card_number'] ?? '');
            
            // Optional fields
            $email        = trim($row['email'] ?? '');
            $mobile       = trim($row['mobile_number'] ?? '');
            $otherNumber  = trim($row['other_number'] ?? '');
            $dob = $this->parseDate($row['date_of_birth'] ?? null);
            $doj = $this->parseDate($row['date_of_joining'] ?? null);

            // Required field validations
            if (!$employeeCode) $validationErrors[] = 'Employee Code is missing';
            if (!$fullName) $validationErrors[] = 'Employee Full Name is missing';
            if (!$firstName) $validationErrors[] = 'First Name is missing';
            if (!$middleName) $validationErrors[] = 'Middle Name is missing';
            if (!$fatherName) $validationErrors[] = 'Father Name is missing';
            if (!$username) $validationErrors[] = 'Username is missing';
            if (!$password) $validationErrors[] = 'Password is missing';
            if (!$gender) {
                $validationErrors[] = 'Gender is missing';
            } elseif (!in_array(strtolower($gender), array_map('strtolower', array_keys(config('constants.genders'))))) {
                $validationErrors[] = "Gender must be one of: " . implode(', ', array_keys(config('constants.genders')));
            }
            if (!$currentAddress) $validationErrors[] = 'Current Address is missing';
            if (!$permanentAddress) $validationErrors[] = 'Permanent Address is missing';
            if (!$aadhar) $validationErrors[] = 'Aadhar Card Number is missing';
            if (!$pan) $validationErrors[] = 'PAN Card Number is missing';

            // Check if employee code already exists
            $existingEmployee = Employee::where('employee_code', $employeeCode)
                ->where('company_id', $this->companyId)
                ->first();
            if ($existingEmployee) {
                $validationErrors[] = "Employee Code '{$employeeCode}' already exists";
            }

            // Required related models
            $country     = $this->getModel(MasterCountry::class, $row['country'] ?? '', 'Country', $validationErrors);
            $state       = $this->getModel(MasterState::class, $row['state'] ?? '', 'State', $validationErrors);
            $city        = $this->getModel(MasterCity::class, $row['city'] ?? '', 'City', $validationErrors);
            $branch      = $this->getModel(Branch::class, $row['branch_name'] ?? '', 'Branch', $validationErrors);
            $designation = $this->getModel(Designation::class, $row['designation'] ?? '', 'Designation', $validationErrors);
            $department  = $this->getModel(Department::class, $row['department'] ?? '', 'Department', $validationErrors);
            $subDepartment = $this->getModel(
                SubDepartment::class,
                $row['sub_department'] ?? '',
                'Sub Department',
                $validationErrors,
                true, // Required -> changed to false (nullable)
                'sub_department_name'
            );
            $process     = $this->getModel(Process::class, $row['process'] ?? '', 'Process', $validationErrors, true); // Required -> changed to false (nullable)
            
            // Employment details required fields
            $designationType = trim($row['designation_type'] ?? '');
            if (!$designationType) {
                $validationErrors[] = 'Designation Type is missing';
            } elseif (!in_array(strtolower($designationType), ['worker', 'employee'])) {
                $validationErrors[] = "Designation Type must be 'worker' or 'employee'";
            }
            
            // Handle typo in field name (employment_tzype -> employment_type)
            $employmentTypeValue = trim($row['employment_type'] ?? $row['employment_type'] ?? '');
            $employmentType = $this->getModel(EmployeeType::class, $employmentTypeValue, 'Employment Type', $validationErrors, false);
            $shift = $this->getModel(Shift::class, $row['shift'] ?? '', 'Shift', $validationErrors, false);
            
            $outdoorAttendance = trim($row['outdoor_attendance'] ?? '');
            if (!$outdoorAttendance) {
                $validationErrors[] = 'Outdoor Attendance is missing';
            } else {
                $outdoorAttendanceLower = strtolower(str_replace(' ', '', $outdoorAttendance));
                if (!in_array($outdoorAttendanceLower, ['permitted', 'notpermitted'])) {
                    $validationErrors[] = "Outdoor Attendance must be 'permitted' or 'not Permitted'";
                }
            }
            
            // Increment details required fields
            $incrementDate = $this->parseDate($row['increment_date'] ?? null);
            if (!$incrementDate) {
                $validationErrors[] = 'Increment Date is missing or invalid';
            }
            
            $basicDa = $row['basic_da'] ?? null;
            if ($basicDa === null || $basicDa === '') {
                $validationErrors[] = 'Basic DA is missing';
            } elseif (!is_numeric($basicDa)) {
                $validationErrors[] = 'Basic DA must be a number';
            }
            
            $effectiveMonth = trim($row['effective_month'] ?? '');
            if (!$effectiveMonth) {
                $validationErrors[] = 'Effective Month is missing';
            }
            
            $effectiveYear = trim($row['effective_year'] ?? '');
            if (!$effectiveYear) {
                $validationErrors[] = 'Effective Year is missing';
            } elseif (!is_numeric($effectiveYear)) {
                $validationErrors[] = 'Effective Year must be a number';
            }
            
            // Salary classification required field
            $salaryClassification = trim($row['salary_classification'] ?? '');
            if (!$salaryClassification) {
                $validationErrors[] = 'Salary Classification is missing';
            } elseif (!in_array(strtolower($salaryClassification), array_map('strtolower', array_values(config('constants.salary_classification'))))) {
                $validationErrors[] = "Salary Classification must be one of: " . implode(', ', array_values(config('constants.salary_classification')));
            }

            $roleId = $row['role_id'] ?? null;
            if (!$roleId && !empty($row['role'])) {
                $role = TeamRole::whereRaw('LOWER(name) = ?', [strtolower($row['role'])])->first();
                $roleId = $role?->id;
                if (!$roleId) $validationErrors[] = "Role '{$row['role']}' not found";
            }

            // Parent Employee
            $parentId = null;
            if (!empty($row['parent_employee_code'])) {
                $parentEmployee = Employee::where('employee_code', trim($row['parent_employee_code']))
                    ->where('company_id', $this->companyId)
                    ->first();
                if ($parentEmployee) {
                    $parentId = $parentEmployee->id;
                } else {
                    $validationErrors[] = "Parent Employee Code '{$row['parent_employee_code']}' not found";
                }
            }

            if (!empty($validationErrors)) {
                $this->failedCount++;
                $this->errors[$rowNumber] = implode(', ', $validationErrors);
                continue;
            }

            try {
                $employee = Employee::create([
                    'company_id'            => $this->companyId,
                    'branch_id'             => $branch?->id,
                    'parent_id'             => $parentId,
                    'employee_code'         => $employeeCode,
                    'first_name'            => $firstName,
                    'middle_name'           => $middleName,
                    'father_name'           => $fatherName,
                    'full_name'             => $fullName,
                    'username'              => $username,
                    'password'              => Hash::make($password),
                    'sp'                    => Helper::generateSP($password),
                    'gender'                => strtolower($gender),
                    'country_id'            => $country?->id,
                    'state_id'              => $state?->id,
                    'city_id'               => $city?->id,
                    'current_address'       => $currentAddress,
                    'permanent_address'     => $permanentAddress,
                    'aadhar_card_number'    => $aadhar,
                    'pan_card_number'       => $pan,
                    // Optional fields
                    'email'                 => $email ?: null,
                    'contact_number'        => $mobile ?: null,
                    'role_id'               => $roleId,
                    'other_number'          => $otherNumber ?: null,
                    'date_of_birth'         => $dob,
                    'marital_status'        => strtolower(trim($row['marital_status'] ?? 'single')),
                    'date_of_anniversary'   => $this->parseDate($row['anniversary_date'] ?? null),
                    'grade'                 => $row['grade'] ?? null,
                    'bank_name'             => $row['bank_name'] ?? null,
                    'bank_account_number'   => $row['bank_account_number'] ?? null,
                    'ifsc_code'             => $row['ifsc_code'] ?? null,
                    'created_by'            => $this->userId,
                    'status'                => 'active',
                ]);

                // EMPLOYMENT DETAILS
                EmploymentDetail::create([
                    'company_id'                    => $this->companyId,
                    'employee_id'                   => $employee->id,
                    'designation_type'              => strtolower($designationType),
                    'designation_id'                => $designation?->id,
                    'department_id'                 => $department?->id,
                    'sub_department_id'             => $subDepartment?->id,
                    'process_id'                    => $process?->id,
                    'date_of_joining'               => $doj ?? now(), // Use current date if not provided
                    'employment_confirmation_date'  => $this->parseDate($row['confirmation_date'] ?? null),
                    'employee_pf_no'                => $row['employee_pf_number'] ?? null,
                    'uan_no'                        => $row['uan_no'] ?? null,
                    'payment_mode'                  => $row['payment_mode'] ?? null,
                    'employment_type'               => $employmentType?->id,
                    'shift'                         => $shift?->id,
                    'outdoor_attendance'            => str_replace(' ', '', strtolower($outdoorAttendance)) === 'notpermitted' ? 'not Permitted' : 'permitted',
                    'status'                        => 'active',
                    'created_by'                    => $this->userId,
                ]);

                // INCREMENT DETAILS
                EmployeeIncrementDetails::create([
                    'company_id'            => $this->companyId,
                    'employee_id'           => $employee->id,
                    'icrement_date'         => $incrementDate,
                    'basic_da'              => (float)$basicDa,
                    'hra'                   => $row['hra'] ?? 0,
                    'conveyance_allowance'  => $row['conveyance_allowance'] ?? 0,
                    'medical_allowance'     => $row['medical_allowance'] ?? 0,
                    'special_allowance'     => $row['special_allowance'] ?? 0,
                    'pf'                    => $row['pf'] ?? 0,
                    'per_day_salary'        => $row['per_day_salary'] ?? 0,
                    'per_hour_salary'       => $row['per_hour_salary'] ?? 0,
                    'designation_id'        => $designation?->id,
                    'effective_month'       => $effectiveMonth,
                    'effective_year'        => $effectiveYear,
                    'remark'                => $row['increment_remark'] ?? null,
                    'status'                => 'active',
                    'created_by'            => $this->userId,
                ]);

                // SALARY DETAIL (Employee Wise Salary Configuration)
                EmployeeWiseSalaryDetail::create([
                    'company_id'                        => $this->companyId,
                    'employee_id'                       => $employee->id,
                    'salary_classification'             => strtoupper($salaryClassification),
                    'week_off'                          => $row['week_off'] ?? 'sunday',
                    'overtime'                          => $row['overtime'] ?? 'no',
                    'is_welfare_fund_applied'           => $row['is_welfare_fund_applied'] ?? 'no',
                    'leave_elegiblity'                  => $row['leave_elegiblity'] ?? null,
                    'sandwich_rule_flag'                => $row['sandwich_rule_flag'] ?? 'no',
                    'sandwich_rule_applied_on'          => $row['sandwich_rule_applied_on'] ?? null,
                    'sandwich_rule_type'                => $row['sandwich_rule_type'] ?? null,
                    'is_bonus_applied'                  => $row['is_bonus_applied'] ?? 'no',
                    'salary_calculation_month_count'    => $row['salary_calculation_month_count'] ?? 12,
                    'pf_type'                           => $row['pf_type'] ?? null,
                    'pf'                                => $row['salary_pf'] ?? 'no',
                    'pf_percentage'                     => $row['pf_percentage'] ?? 0,
                    'pradhanmantri_pf'                  => $row['pradhanmantri_pf'] ?? 'no',
                    'pradhanmantri_pf_percentage'       => $row['pradhanmantri_pf_percentage'] ?? 0,
                    'tds'                               => $row['tds'] ?? 'no',
                    'tds_percentage'                    => $row['tds_percentage'] ?? 0,
                    'insurance'                         => $row['insurance'] ?? 'no',
                    'insurance_amount'                  => $row['insurance_amount'] ?? 0,
                    'pt'                                => $row['pt'] ?? 'no',
                    'pt_amount'                         => $row['pt_amount'] ?? 0,
                    'is_esi_company_side'               => $row['is_esi_company_side'] ?? 'no',
                    'esi_company_side_percentage'       => $row['esi_company_side_percentage'] ?? 0,
                    'esi_employee_side'                 => $row['esi_employee_side'] ?? 'no',
                    'esi_employee_side_percentage'      => $row['esi_employee_side_percentage'] ?? 0,
                    'gratuity_calculation'              => $row['gratuity_calculation'] ?? 'no',
                    'basic_da'                          => $row['salary_basic_da'] ?? 0,
                    'hra'                               => $row['salary_hra'] ?? 0,
                    'conveyance_allowance'              => $row['salary_conveyance_allowance'] ?? 0,
                    'medical_allowance'                 => $row['salary_medical_allowance'] ?? 0,
                    'special_allowance'                 => $row['salary_special_allowance'] ?? 0,
                    'ctc'                               => $row['ctc'] ?? 0,
                    'status'                            => 'active',
                    'created_by'                        => $this->userId,
                ]);

                // SALARY RECORD (Optional - Only if salary month and year are provided)
                /*
                if (!empty($row['salary_month']) && !empty($row['salary_year'])) {
                    $salaryMonth = (int) $row['salary_month'];
                    $salaryYear = (int) $row['salary_year'];

                    // Check if salary already exists for this employee, month, and year
                    $existingSalary = Salary::where('employee_id', $employee->id)
                        ->where('company_id', $this->companyId)
                        ->where('month', $salaryMonth)
                        ->where('year', $salaryYear)
                        ->first();

                    if (!$existingSalary) {
                        Salary::create([
                            'company_id'                    => $this->companyId,
                            'branch_id'                     => $branch?->id,
                            'department_id'                 => $department?->id,
                            'employee_id'                   => $employee->id,
                            'year'                          => $salaryYear,
                            'month'                         => $salaryMonth,
                            'added_date'                    => $row['salary_added_date'] ? $this->parseDate($row['salary_added_date']) : now(),
                            'calculate_days'                => $row['calculate_days'] ?? 0,
                            'total_present_day'             => $row['total_present_day'] ?? 0,
                            'adjustment_days'               => $row['adjustment_days'] ?? 0,
                            'adjustment_remark'             => $row['adjustment_remark'] ?? null,
                            'half_day'                      => $row['half_day'] ?? 0,
                            'holiday'                       => $row['holiday'] ?? 0,
                            'compensation_hour'             => $row['compensation_hour'] ?? 0,
                            'compensation_day'              => $row['compensation_day'] ?? 0,
                            'total_week_off'                => $row['total_week_off'] ?? 0,
                            'total_sandwich_leave'          => $row['total_sandwich_leave'] ?? 0,
                            'total_leave'                   => $row['total_leave'] ?? 0,
                            'total_company_pay_leave'       => $row['total_company_pay_leave'] ?? 0,
                            'total_employee_pay_leave'      => $row['total_employee_pay_leave'] ?? 0,
                            'total_absent'                  => $row['total_absent'] ?? 0,
                            'total_day'                     => $row['total_day'] ?? 0,
                            'working_hour'                  => $row['salary_working_hour'] ?? 0,
                            'ctc'                           => $row['salary_ctc'] ?? 0,
                            'gross_salary'                  => $row['gross_salary'] ?? 0,
                            'given_calculate_salary'        => $row['given_calculate_salary'] ?? 0,
                            'per_day_salary'                => $row['salary_per_day_salary'] ?? 0,
                            'conveyance_allowance'          => $row['salary_conveyance_allowance_amt'] ?? 0,
                            'medical_allowance'             => $row['salary_medical_allowance_amt'] ?? 0,
                            'special_allowance'             => $row['salary_special_allowance_amt'] ?? 0,
                            'present_day_amount'            => $row['present_day_amount'] ?? 0,
                            'employee_weekoff_amount'       => $row['employee_weekoff_amount'] ?? 0,
                            'company_pay_leave_amount'      => $row['company_pay_leave_amount'] ?? 0,
                            'employee_pay_leave_amount'     => $row['employee_pay_leave_amount'] ?? 0,
                            'fxs_basic'                     => $row['fxs_basic'] ?? 0,
                            'fxs_hra'                       => $row['fxs_hra'] ?? 0,
                            'fxs_other'                     => $row['fxs_other'] ?? 0,
                            'fxs_total_earning'             => $row['fxs_total_earning'] ?? 0,
                            'dws_basic'                     => $row['dws_basic'] ?? 0,
                            'dws_da'                        => $row['dws_da'] ?? 0,
                            'dws_other'                     => $row['dws_other'] ?? 0,
                            'dws_total_earning'             => $row['dws_total_earning'] ?? 0,
                            'actual_total_ot_hours'         => $row['actual_total_ot_hours'] ?? 0,
                            'earn_ot_hours'                 => $row['earn_ot_hours'] ?? 0,
                            'earn_ot_payable_amt'           => $row['earn_ot_payable_amt'] ?? 0,
                            'earn_ot_days'                  => $row['earn_ot_days'] ?? 0,
                            'earn_performation_incentive'   => $row['earn_performation_incentive'] ?? 0,
                            'bonus_amount'                  => $row['bonus_amount'] ?? 0,
                            'bonus_temp_amount'             => $row['bonus_temp_amount'] ?? 0,
                            'bonus_amount_adjustment'       => $row['bonus_amount_adjustment'] ?? 0,
                            'earn_sub_total'                => $row['earn_sub_total'] ?? 0,
                            'total_earning'                 => $row['total_earning'] ?? 0,
                            'ded_employee_pf'               => $row['ded_employee_pf'] ?? 0,
                            'ded_pradhan_mantri_pf'         => $row['ded_pradhan_mantri_pf'] ?? 0,
                            'ded_esi_employee'              => $row['ded_esi_employee'] ?? 0,
                            'ded_esi_company'               => $row['ded_esi_company'] ?? 0,
                            'ded_pt'                        => $row['ded_pt'] ?? 0,
                            'ded_insurance'                 => $row['ded_insurance'] ?? 0,
                            'ded_advance'                   => $row['ded_advance'] ?? 0,
                            'advance_amount_adjustment'     => $row['advance_amount_adjustment'] ?? 0,
                            'ded_tds'                       => $row['ded_tds'] ?? 0,
                            'tds_amount_adjustment'         => $row['tds_amount_adjustment'] ?? 0,
                            'ded_wf'                        => $row['ded_wf'] ?? 0,
                            'ded_loan_amount'               => $row['ded_loan_amount'] ?? 0,
                            'loan_amount_adjustment'        => $row['loan_amount_adjustment'] ?? 0,
                            'ded_other'                     => $row['ded_other'] ?? 0,
                            'total_deduction'               => $row['total_deduction'] ?? 0,
                            'ded_other_remark'              => $row['ded_other_remark'] ?? null,
                            'net_bank_pay'                  => $row['net_bank_pay'] ?? 0,
                            'additional_ot_hour'            => $row['additional_ot_hour'] ?? 0,
                            'total_acc_ot_plus_add_ot'      => $row['total_acc_ot_plus_add_ot'] ?? 0,
                            'status'                        => 'pending',
                            'created_by'                    => $this->userId,
                        ]);
                    }
                }
                */
                DB::commit();
                $this->successCount++;
            } catch (\Exception $e) {
                DB::rollBack();
                $this->failedCount++;
                $this->errors[$rowNumber] = $e->getMessage();
                Log::error("Employee Import Error at Row {$rowNumber}: " . $e->getMessage());
            }
        }

        EmployeeImportFile::where('id', $this->employeeImportFileId)->update([
            'total_employees' => $this->totalEmployees,
            'total_success_employees' => $this->successCount,
            'total_failed_employees' => $this->failedCount,
            'errors' => json_encode($this->errors),
        ]);

        Log::info("Employee Import Completed: Total {$this->totalEmployees}, Success {$this->successCount}, Failed {$this->failedCount}");
    }

    private function parseDate($value)
    {
        if (!$value) return null;
        try {
            // If it's Excel date value
            if (is_numeric($value)) {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value));
            }
            return Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Fetch a model record by matching a specific column.
     *
     * @param string $model            Eloquent model class
     * @param string $value            Value coming from CSV/Excel
     * @param string $label            Label for error messages
     * @param array  $validationErrors Reference to validation error array
     * @param bool   $nullable         Allow empty values
     * @param string $column           Column to search (default: 'name')
     * @return mixed|null
     */
    public function getModel(
        $model,
        $value,
        $label,
        &$validationErrors,
        $nullable = false,
        $column = 'name'
    ) {
        // If allowed to be null and value is empty → skip
        if ($nullable && empty($value)) {
            return null;
        }

        // Protect column name from SQL injection
        $column = preg_replace('/[^a-zA-Z0-9_]/', '', $column);

        // Normalize search value
        $searchValue = trim(strtolower($value));

        // Build query with company_id filter for company-specific models
        $query = $model::whereRaw("LOWER($column) = ?", [$searchValue]);
        
        // Add company_id filter for models that have it
        $modelsWithCompanyId = [
            Branch::class,
            Designation::class,
            Department::class,
            SubDepartment::class,
            Process::class,
            EmployeeType::class,
            Shift::class,
        ];
        
        if (in_array($model, $modelsWithCompanyId)) {
            $query->where('company_id', $this->companyId);
        }

        // Try to fetch record safely
        $record = $query->first();

        if (!$record) {
            $validationErrors[] = "$label '$value' not found.";
            return null;
        }

        return $record;
    }
}
