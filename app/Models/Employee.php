<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use SoftDeletes;

    protected $guard = 'employees';

    protected $table = 'employees';

    protected $fillable = [
        'company_id',
        'branch_id',
        'parent_id',
        'employee_code',
        'biometric_user_id',

        'first_name',
        'middle_name',
        'father_name',
        'full_name',

        'username',
        'password',
        'sp',
        'role_id',

        'email',
        'contact_number',
        'other_number',

        'date_of_birth',
        'gender',
        'blood_group',
        'country_id',
        'state_id',
        'city_id',
        'current_address',
        'permanent_address',


        'aadhar_card_number',
        'pan_card_number',
        'marital_status',
        'date_of_anniversary',
        'grade',
        'bank_name',
        'bank_account_number',
        'ifsc_code',
        'app_version',
        'device_token',
        'device_token_app',

        'profile_image',
        'status',
        'resign_date',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $appends = ['employee_photo_url', 'proper_name', 'has_profile_image'];

    public function getHasProfileImageAttribute()
    {
        return !empty($this->profile_image) && file_exists(public_path($this->profile_image));
    }

    public function getEmployeePhotoUrlAttribute()
    {
        # employee_photo_url
        if (!empty($this->profile_image) && file_exists(public_path($this->profile_image))) {
            return asset($this->profile_image);
        }
        return "https://ui-avatars.com/api/?name=" . urlencode($this->proper_name ?? $this->full_name);
    }

    public function getProperNameAttribute()
    {
        // Preferred format: Surname Firstname Fathername
        // In this system: first_name = Surname, middle_name = Firstname, father_name = Fathername.

        // If full_name is already populated, it's our primary source (usually contains the full format).
        if (!empty($this->full_name)) {
            return trim($this->full_name);
        }

        // Fallback: construct from separate fields if full_name is empty
        $surname = trim($this->first_name ?? '');
        $firstName = trim($this->middle_name ?? '');
        $fatherName = trim($this->father_name ?? '');

        $fullName = trim("{$surname} {$firstName} {$fatherName}");

        return $fullName ?: '-';
    }
    public function parentEmployee()
    {
        return $this->belongsTo(Employee::class, 'parent_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public function country()
    {
        return $this->belongsTo(MasterCountry::class, 'country_id', 'id'); // Belongs to country
    }

    public function state()
    {
        return $this->belongsTo(MasterState::class, 'state_id', 'id'); // Belongs to state
    }

    public function city()
    {
        return $this->belongsTo(MasterCity::class, 'city_id', 'id'); // Belongs to city
    }

    public function current_role()
    {
        return $this->belongsTo(TeamRole::class, 'role_id', 'id'); // Belongs to team role
    }

    public function team_role()
    {
        return $this->belongsTo(TeamRole::class, 'role_id', 'id');
    }

    public function role()
    {
        return $this->belongsTo(TeamRole::class, 'role_id', 'id');
    }

    public function employee_asign_assets()
    {
        // ->where('company_id', $this->id)
        return $this->hasMany(EmployeeAsignAssets::class, 'employee_id', 'id');
    }
    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'employee_id', 'id');
    }

    public function increment_details()
    {
        return $this->hasMany(EmployeeIncrementDetails::class, 'employee_id', 'id');
    }

    public function education_experience_details()
    {
        return $this->hasMany(EmployeeEducationExperienceDetail::class, 'employee_id', 'id');
    }

    public function salary_details()
    {
        return $this->hasMany(EmployeeWiseSalaryDetail::class, 'employee_id', 'id');
    }

    /**
     * Monthly salary records (calculated salary per month).
     */
    public function salaries()
    {
        return $this->hasMany(Salary::class, 'employee_id', 'id');
    }

    public function loans()
    {
        return $this->hasMany(Loan::class, 'employee_id', 'id');
    }

    public function employment_details()
    {
        return $this->hasMany(EmploymentDetail::class, 'employee_id', 'id');
    }

    /**
     * Get the latest/primary employment detail
     */
    public function employmentDetail()
    {
        return $this->hasOne(EmploymentDetail::class, 'employee_id', 'id')->latest();
    }

    /**
     * Get employee's weekly off days as array of capitalized day names (e.g. ['Wednesday', 'Sunday']).
     */
    public function getWeekOffDays()
    {
        $salaryDetail = $this->salary_details()->latest()->first();
        if ($salaryDetail && $salaryDetail->week_off) {
            $decoded = json_decode($salaryDetail->week_off, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }
        return [];
    }

    /**
     * Compute FIFO C-Off matching logic to account for 60-day expiration.
     * Returns an array with:
     * - 'earned': total earned within their active 60-day window as of $asOfDate.
     * - 'used': total approved C-Off leaves taken.
     * - 'available': net usable C-Off leaves remaining as of $asOfDate.
     */
    public function getCoffSummary($asOfDate = null, $excludeLeaveId = null, $startDate = null, $endDate = null)
    {
        if ($asOfDate !== false) {
            if (!$asOfDate) {
                $asOfDate = \Carbon\Carbon::now()->toDateString();
            } else {
                $asOfDate = \Carbon\Carbon::parse($asOfDate)->toDateString();
            }
        }

        $weekOffs = $this->getWeekOffDays();
        if (empty($weekOffs)) {
            return [
                'earned' => 0,
                'used' => 0,
                'available' => 0,
                'consumed_from_earned' => 0
            ];
        }

        // 1. Get all earned C-Offs (attendances on week off days)
        $attendances = $this->attendances()
            ->where('punch_in_time', '!=', '00:00:00')
            ->whereNotNull('punch_in_time')
            ->orderBy('attendance_date', 'asc')
            ->get();

        $earnedCoffs = [];
        $addedDates = [];
        foreach ($attendances as $att) {
            try {
                $dateStr = $att->attendance_date;
                if (in_array($dateStr, $addedDates)) {
                    continue;
                }
                $dayName = \Carbon\Carbon::parse($dateStr)->format('l');
                if (in_array($dayName, $weekOffs)) {
                    // Group and fetch all punch entries for this specific weekly off date
                    $attendancesGrouped = $this->attendances()
                        ->where('attendance_date', $dateStr)
                        ->where('punch_in_time', '!=', '00:00:00')
                        ->whereNotNull('punch_in_time')
                        ->orderBy('punch_in_time', 'asc')
                        ->get();

                    $dailyMinutes = 0;
                    $shiftModel = null;
                    $hasOutPunch = false;
                    $hasUnpairedIn = false;
                    
                    $allPunches = [];
                    foreach ($attendancesGrouped as $r) {
                        $allPunches[] = [
                            'type' => strtolower(trim($r->attendace_type)),
                            'time' => $r->punch_in_time,
                            'shift' => $r->shift
                        ];
                    }

                    // Pair each IN punch with the next subsequent OUT punch chronologically
                    for ($i = 0; $i < count($allPunches); $i++) {
                        if ($allPunches[$i]['shift']) {
                            $shiftModel = $allPunches[$i]['shift'];
                        }
                        
                        if ($allPunches[$i]['type'] === 'in') {
                            $nextOutIndex = -1;
                            for ($j = $i + 1; $j < count($allPunches); $j++) {
                                if ($allPunches[$j]['type'] === 'out') {
                                    $nextOutIndex = $j;
                                    break;
                                }
                            }
                            
                             if ($nextOutIndex !== -1) {
                                $inTime = \Carbon\Carbon::parse($allPunches[$i]['time']);
                                $outTime = \Carbon\Carbon::parse($allPunches[$nextOutIndex]['time']);
                                
                                // PM rollover adjustment (e.g. IN at 09:00:00 and OUT at 01:00:00)
                                if ($outTime->lt($inTime)) {
                                    $outTime->addHours(12);
                                }
                                
                                $dailyMinutes += abs($outTime->diffInMinutes($inTime));
                                $hasOutPunch = true;
                                
                                // Fast-forward outer loop index to the matched OUT punch
                                $i = $nextOutIndex;
                            } else {
                                // IN punch has no subsequent OUT punch (unpaired session)
                                $hasUnpairedIn = true;
                            }
                        }
                    }

                    // Default to 0.0 if they have a punch-in but no out-punch recorded, or an unpaired session
                    $amount = 0.0;
                    if (!$hasUnpairedIn && $hasOutPunch && $dailyMinutes > 0) {
                        $workingMinutes = 480; // Default 8 hours
                        $halfMinutes = 240;    // Default 4 hours

                        if ($shiftModel) {
                            if ($shiftModel->working_hour) {
                                $parts = explode(':', $shiftModel->working_hour);
                                $workingMinutes = ((int)($parts[0] ?? 8) * 60) + (int)($parts[1] ?? 0);
                            }
                            if ($shiftModel->half_day_hour) {
                                $parts = explode(':', $shiftModel->half_day_hour);
                                $halfMinutes = ((int)($parts[0] ?? 4) * 60) + (int)($parts[1] ?? 0);
                            } else {
                                $halfMinutes = $workingMinutes / 2;
                            }
                        }

                        // Cap limits to standard corporate thresholds for Weekly Off / C-Off earnings:
                        // 8 hours (480 mins) for a Full C-Off, and 4 hours (240 mins) for a Half C-Off.
                        if ($workingMinutes > 480) {
                            $workingMinutes = 480;
                        }
                        if ($halfMinutes > 240) {
                            $halfMinutes = 240;
                        }

                        if ($dailyMinutes >= $workingMinutes) {
                            $amount = 1.0;
                        } elseif ($dailyMinutes >= $halfMinutes) {
                            $amount = 0.5;
                        } else {
                            $amount = 0.0;
                        }
                    }

                    if ($amount > 0) {
                        $earnedCoffs[] = [
                            'date' => $dateStr,
                            'amount' => $amount,
                            'consumed' => 0.0
                        ];
                        $addedDates[] = $dateStr;
                    }
                }
            } catch (\Exception $e) {
                // Ignore parsing errors
            }
        }

        // 2. Get all approved C-Off leaves
        $cOffLeaveType = LeaveType::where('company_id', $this->company_id)
            ->where(function($q) {
                $q->where('sort_name', 'C-Off')
                  ->orWhere('sort_name', 'coff')
                  ->orWhere('full_name', 'Compensatory Off')
                  ->orWhere('full_name', 'C-Off');
            })
            ->first();

        $approvedCoffs = [];
        if ($cOffLeaveType) {
            $approvedCoffsQuery = LeaveApplication::where('employee_id', $this->id)
                ->where('leave_type_id', $cOffLeaveType->id)
                ->where('status', 'approved');

            if ($excludeLeaveId) {
                $approvedCoffsQuery->where('id', '!=', $excludeLeaveId);
            }

            $approvedCoffs = $approvedCoffsQuery->orderBy('fromdate_time', 'asc')->get();
        }

        $hasDateRange = ($startDate || $endDate);
        $fifoCutoffDate = ($hasDateRange && $endDate) ? $endDate : null;

        // 3. FIFO Match approved leaves to non-expired earned C-Offs
        $totalUsedCount = 0;
        foreach ($approvedCoffs as $app) {
            $days = 0.5;
            if ($app->halfday_fullday === 'fullday') {
                try {
                    $fromDate = \Carbon\Carbon::parse($app->fromdate_time)->startOfDay();
                    $toDate = $app->todate_time ? \Carbon\Carbon::parse($app->todate_time)->startOfDay() : $fromDate;
                    $days = $fromDate->diffInDays($toDate) + 1;
                } catch (\Exception $e) {
                    $days = 1;
                }
            }

            $leaveDate = \Carbon\Carbon::parse($app->fromdate_time)->toDateString();

            // Monthly reports: only consume balance from leaves taken up to period end
            if ($fifoCutoffDate && $leaveDate > $fifoCutoffDate) {
                continue;
            }

            $totalUsedCount += $days;
            $remainingToMatch = $days;

            foreach ($earnedCoffs as &$ec) {
                if ($remainingToMatch <= 0) {
                    break;
                }

                $earnedDateStr = $ec['date'];
                $earnedDate = \Carbon\Carbon::parse($earnedDateStr);
                $expiryDate = $earnedDate->copy()->addDays(60);

                // Check if earned on or before the leave date, and not expired on the leave date
                if ($earnedDateStr <= $leaveDate && $leaveDate <= $expiryDate->toDateString()) {
                    $availableOnEc = $ec['amount'] - $ec['consumed'];
                    if ($availableOnEc > 0) {
                        $toConsume = min($remainingToMatch, $availableOnEc);
                        $ec['consumed'] += $toConsume;
                        $remainingToMatch -= $toConsume;
                    }
                }
            }
        }
        unset($ec);

        // 3b. Count C-Off leaves taken within the report period (by leave date, not earn date)
        $usedInPeriod = 0;
        if ($hasDateRange) {
            foreach ($approvedCoffs as $app) {
                $days = 0.5;
                if ($app->halfday_fullday === 'fullday') {
                    try {
                        $fromDate = \Carbon\Carbon::parse($app->fromdate_time)->startOfDay();
                        $toDate = $app->todate_time ? \Carbon\Carbon::parse($app->todate_time)->startOfDay() : $fromDate;
                        $days = $fromDate->diffInDays($toDate) + 1;
                    } catch (\Exception $e) {
                        $days = 1;
                    }
                }

                $leaveDate = \Carbon\Carbon::parse($app->fromdate_time)->toDateString();
                $isInLeaveRange = true;
                if ($startDate && $leaveDate < $startDate) {
                    $isInLeaveRange = false;
                }
                if ($endDate && $leaveDate > $endDate) {
                    $isInLeaveRange = false;
                }

                if ($isInLeaveRange) {
                    $usedInPeriod += $days;
                }
            }
        }

        // 4. Calculate earned and available as of the target date
        $earnedCount = 0;
        $availableCount = 0;
        $consumedFromEarnedCount = 0;

        foreach ($earnedCoffs as $ec) {
            $earnedDateStr = $ec['date'];
            $earnedDate = \Carbon\Carbon::parse($earnedDateStr);
            $expiryDate = $earnedDate->copy()->addDays(60);

            // An earned C-Off is counted in the report and list if it is not expired as of $asOfDate
            $isValidAsOf = true;
            if ($asOfDate !== false) {
                $isValidAsOf = ($earnedDateStr <= $asOfDate && $asOfDate <= $expiryDate->toDateString());
            }

            // If date range is provided, check if earnedDate falls within range
            $isInRange = true;
            if ($startDate && $earnedDateStr < $startDate) $isInRange = false;
            if ($endDate && $earnedDateStr > $endDate) $isInRange = false;

            if ($isValidAsOf && $isInRange) {
                $earnedCount += $ec['amount'];
                $availableCount += ($ec['amount'] - $ec['consumed']);
                $consumedFromEarnedCount += $ec['consumed'];
            }
        }

        // Balance still available at period end (includes carried-forward from prior months)
        $pendingAtPeriodEnd = 0;
        if ($hasDateRange && $endDate) {
            foreach ($earnedCoffs as $ec) {
                $earnedDateStr = $ec['date'];
                $expiryDate = \Carbon\Carbon::parse($earnedDateStr)->addDays(60);
                if ($earnedDateStr <= $endDate && $endDate <= $expiryDate->toDateString()) {
                    $pendingAtPeriodEnd += ($ec['amount'] - $ec['consumed']);
                }
            }
        }

        return [
            'earned' => $hasDateRange ? max($earnedCount, $usedInPeriod, $pendingAtPeriodEnd) : $earnedCount,
            'used' => $hasDateRange ? $usedInPeriod : $totalUsedCount,
            'available' => $hasDateRange ? $pendingAtPeriodEnd : $availableCount,
            'consumed_from_earned' => $consumedFromEarnedCount,
        ];
    }

    /**
     * Get total earned C-Offs from attendance records on week off days.
     */
    public function getEarnedCoffCount($asOfDate = null, $excludeLeaveId = null, $startDate = null, $endDate = null)
    {
        return $this->getCoffSummary($asOfDate, $excludeLeaveId, $startDate, $endDate)['earned'];
    }

    /**
     * Get total used C-Off days from approved leave applications.
     * With a date range: leaves taken within that period. Otherwise: FIFO consumed from earned balance.
     */
    public function getUsedCoffCount($asOfDate = null, $excludeLeaveId = null, $startDate = null, $endDate = null)
    {
        $summary = $this->getCoffSummary($asOfDate, $excludeLeaveId, $startDate, $endDate);
        if ($startDate || $endDate) {
            return $summary['used'];
        }

        return $summary['consumed_from_earned'];
    }

    /**
     * Get current pending/available C-Off count.
     */
    public function getAvailableCoffCount($asOfDate = null, $excludeLeaveId = null, $startDate = null, $endDate = null)
    {
        return $this->getCoffSummary($asOfDate, $excludeLeaveId, $startDate, $endDate)['available'];
    }

    /**
     * Available leave balance using the same logic shown on the leave application form.
     */
    public function getAvailableLeaveBalance($leaveTypeId, $excludeLeaveId = null): float
    {
        $leaveType = LeaveType::find($leaveTypeId);
        if (!$leaveType) {
            return 0.0;
        }

        if (strtolower($leaveType->sort_name) === 'c-off' || strtolower($leaveType->sort_name) === 'coff' || strtolower($leaveType->full_name) === 'compensatory off') {
            return (float) $this->getAvailableCoffCount(now()->format('Y-m-d H:i:s'), $excludeLeaveId);
        }

        $monthlyAccrual = (float) $leaveType->count / 12.0;
        $totalCount = $this->resolveAccrualTotal(
            $monthlyAccrual,
            now()->year,
            now()->month,
            now(),
            $leaveType->carry_forward == 1
        );

        $usedQuery = LeaveApplication::where('employee_id', $this->id)
            ->where('leave_type_id', $leaveTypeId)
            ->where('status', '!=', 'rejected');

        if ($excludeLeaveId) {
            $usedQuery->where('id', '!=', $excludeLeaveId);
        }

        if ($leaveType->carry_forward == 0) {
            $usedQuery->whereMonth('fromdate_time', now()->month)
                ->whereYear('fromdate_time', now()->year);
        } else {
            $targetMonth = now()->month;
            $targetYear = now()->year;
            if ($targetMonth >= 4) {
                $fyStart = \Carbon\Carbon::create($targetYear, 4, 1)->startOfDay();
                $fyEnd = \Carbon\Carbon::create($targetYear + 1, 3, 31)->endOfDay();
            } else {
                $fyStart = \Carbon\Carbon::create($targetYear - 1, 4, 1)->startOfDay();
                $fyEnd = \Carbon\Carbon::create($targetYear, 3, 31)->endOfDay();
            }
            $usedQuery->whereBetween('fromdate_time', [$fyStart, $fyEnd]);
        }

        $usedDays = 0.0;
        foreach ($usedQuery->get() as $app) {
            $days = 0.5;
            if ($app->halfday_fullday === 'fullday') {
                try {
                    $fDate = \Carbon\Carbon::parse($app->fromdate_time)->startOfDay();
                    $tDate = $app->todate_time ? \Carbon\Carbon::parse($app->todate_time)->startOfDay() : $fDate;
                    $days = $fDate->diffInDays($tDate) + 1;
                } catch (\Exception $e) {
                    $days = 1.0;
                }
            }
            $usedDays += $days;
        }

        return max(0.0, $totalCount - $usedDays);
    }

    /**
     * Accrued leave count for leave summary report (month-wise carry forward types).
     */
    public function getAccruedLeaveCountForReport($leaveTypeId, $year, $month = null, $ratio = 1.0): float
    {
        $leaveType = LeaveType::find($leaveTypeId);
        if (!$leaveType) {
            return 0.0;
        }

        $monthlyAccrual = (float) $leaveType->count / 12.0 * $ratio;

        if (!$month || $leaveType->carry_forward != 1) {
            if ($month) {
                // Report SL/DL: always monthly count/12 (no DOJ)
                return $this->roundLeaveCount($monthlyAccrual);
            }
            $assigned = (float) $leaveType->count * $ratio;
            return $this->roundLeaveCount($assigned);
        }

        $asOfDate = \Carbon\Carbon::create($year, $month, 1)->endOfMonth()->endOfDay();
        if ($year == now()->year && $month == now()->month) {
            $asOfDate = now()->endOfDay();
        }

        return $this->resolveAccrualTotal($monthlyAccrual, $year, $month, $asOfDate, true);
    }

    /**
     * Use DOJ-based accrual when employee joined after current financial year start.
     */
    private function shouldApplyDojAccrual(int $year, int $month): bool
    {
        $doj = $this->employmentDetail?->date_of_joining;
        if (!$doj) {
            return false;
        }

        try {
            $dojDate = \Carbon\Carbon::parse($doj)->startOfDay();
            $fyStart = $this->getFinancialYearStart($year, $month);
            return $dojDate->gt($fyStart);
        } catch (\Exception $e) {
            return false;
        }
    }

    private function getFinancialYearStart(int $year, int $month): \Carbon\Carbon
    {
        if ($month >= 4) {
            return \Carbon\Carbon::create($year, 4, 1)->startOfDay();
        }

        return \Carbon\Carbon::create($year - 1, 4, 1)->startOfDay();
    }

    /**
     * Standard financial-year accrual (April onward, day >= 15 rule for current month).
     */
    private function calculateFyAccrual(float $monthlyAccrual, int $year, int $month, $asOfDate = null, bool $cumulative = true): float
    {
        $asOf = $asOfDate ? \Carbon\Carbon::parse($asOfDate) : now();
        $referenceDay = $asOf->day;

        if ($cumulative) {
            if ($month >= 4) {
                $completedMonths = $month - 4;
            } else {
                $completedMonths = $month + 8;
            }

            $completedMonthsAccrual = $completedMonths * $monthlyAccrual;

            if ($year == $asOf->year && $month == $asOf->month) {
                $currentMonthAccrual = $referenceDay >= 15 ? $monthlyAccrual : ($monthlyAccrual / 2.0);
            } else {
                $lastDay = \Carbon\Carbon::create($year, $month, 1)->endOfMonth()->day;
                $currentMonthAccrual = $lastDay >= 15 ? $monthlyAccrual : ($monthlyAccrual / 2.0);
            }

            return $this->roundLeaveCount($completedMonthsAccrual + $currentMonthAccrual);
        }

        if ($year == $asOf->year && $month == $asOf->month) {
            $currentMonthAccrual = $referenceDay >= 15 ? $monthlyAccrual : ($monthlyAccrual / 2.0);
        } else {
            $lastDay = \Carbon\Carbon::create($year, $month, 1)->endOfMonth()->day;
            $currentMonthAccrual = $lastDay >= 15 ? $monthlyAccrual : ($monthlyAccrual / 2.0);
        }

        return $this->roundLeaveCount($currentMonthAccrual);
    }

    /**
     * Pick FY or DOJ accrual based on employee joining date within the financial year.
     */
    private function resolveAccrualTotal(float $monthlyAccrual, int $year, int $month, $asOfDate = null, bool $cumulative = true): float
    {
        if ($this->shouldApplyDojAccrual($year, $month)) {
            return $this->calculateDojBasedAccrual($monthlyAccrual, $year, $month, $asOfDate, $cumulative);
        }

        return $this->calculateFyAccrual($monthlyAccrual, $year, $month, $asOfDate, $cumulative);
    }

    /**
     * Calculate accrued leave from Date of Joining.
     * Join month always gets full monthly accrual (count/12); later months full unit each.
     */
    private function calculateDojBasedAccrual(float $monthlyAccrual, int $year, int $month, $asOfDate = null, bool $cumulativeFromDoj = true): float
    {
        $targetMonthStart = \Carbon\Carbon::create($year, $month, 1)->startOfDay();
        $targetMonthEnd = $targetMonthStart->copy()->endOfMonth()->endOfDay();

        if ($asOfDate) {
            $periodEnd = \Carbon\Carbon::parse($asOfDate)->endOfDay();
        } else {
            $periodEnd = now()->endOfDay();
        }

        if ($periodEnd->gt($targetMonthEnd)) {
            $periodEnd = $targetMonthEnd->copy();
        }

        $fyStart = $this->getFinancialYearStart($year, $month);
        $calculationStart = $cumulativeFromDoj ? $fyStart->copy() : $targetMonthStart->copy();

        $doj = $this->employmentDetail?->date_of_joining;
        $dojDate = null;
        if ($doj) {
            try {
                $dojDate = \Carbon\Carbon::parse($doj)->startOfDay();
                if ($dojDate->gt($periodEnd)) {
                    return 0.0;
                }
                if (!$cumulativeFromDoj && $dojDate->format('Y-m') > $targetMonthStart->format('Y-m')) {
                    return 0.0;
                }
                if ($dojDate->gt($calculationStart)) {
                    $calculationStart = $dojDate->copy();
                }
            } catch (\Exception $e) {
                $dojDate = null;
            }
        }

        if ($calculationStart->gt($periodEnd)) {
            return 0.0;
        }

        $totalAccrualUnits = 0.0;
        $current = $calculationStart->copy()->startOfDay();
        $end = $periodEnd->copy()->endOfDay();
        $loopCount = 0;

        while ($current->format('Y-m') <= $end->format('Y-m') && $loopCount < 24) {
            if (!$cumulativeFromDoj && $current->format('Y-m') !== $targetMonthStart->format('Y-m')) {
                break;
            }

            // Join month and every month after: full monthly unit (e.g. 24/12 = 2)
            $totalAccrualUnits += 1.0;

            if (!$cumulativeFromDoj) {
                break;
            }

            $current->addMonth()->startOfMonth();
            $loopCount++;
        }

        return $this->roundLeaveCount($totalAccrualUnits * $monthlyAccrual);
    }

    /**
     * Used leave days from FY start to selected month end (approved only, for report/API).
     */
    public function getUsedLeaveCountForReport($leaveTypeId, $year, $month): float
    {
        if ($month >= 4) {
            $fyStart = \Carbon\Carbon::create($year, 4, 1)->startOfDay();
        } else {
            $fyStart = \Carbon\Carbon::create($year - 1, 4, 1)->startOfDay();
        }
        $monthEnd = \Carbon\Carbon::create($year, $month, 1)->endOfMonth()->endOfDay();

        return $this->sumApprovedLeaveDays($leaveTypeId, $fyStart, $monthEnd);
    }

    /**
     * Used leave days within a single calendar month (approved only, for report/API).
     */
    public function getUsedLeaveCountForMonth($leaveTypeId, $year, $month): float
    {
        $monthStart = \Carbon\Carbon::create($year, $month, 1)->startOfDay();
        $monthEnd = \Carbon\Carbon::create($year, $month, 1)->endOfMonth()->endOfDay();

        return $this->sumApprovedLeaveDays($leaveTypeId, $monthStart, $monthEnd);
    }

    private function sumApprovedLeaveDays($leaveTypeId, $start, $end): float
    {
        $applications = LeaveApplication::where('employee_id', $this->id)
            ->where('leave_type_id', $leaveTypeId)
            ->where('status', 'approved')
            ->whereBetween('fromdate_time', [$start, $end])
            ->get();

        $usedDays = 0.0;
        foreach ($applications as $app) {
            $days = 0.5;
            if ($app->halfday_fullday === 'fullday') {
                try {
                    $fromDate = \Carbon\Carbon::parse($app->fromdate_time)->startOfDay();
                    $toDate = $app->todate_time ? \Carbon\Carbon::parse($app->todate_time)->startOfDay() : $fromDate;
                    $days = $fromDate->diffInDays($toDate) + 1;
                } catch (\Exception $e) {
                    $days = 1.0;
                }
            }
            $usedDays += $days;
        }

        return $usedDays;
    }

    private function roundLeaveCount(float $assigned): float
    {
        $assigned = round($assigned, 3);
        $fraction = $assigned - floor($assigned);
        if ($fraction > 0 && $fraction <= 0.5) {
            return floor($assigned) + 0.5;
        }
        if ($fraction > 0.5) {
            return ceil($assigned);
        }

        return $assigned;
    }
}
