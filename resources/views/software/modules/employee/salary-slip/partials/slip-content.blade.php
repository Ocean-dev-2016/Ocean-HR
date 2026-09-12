@php
    $s = $salary;
    $emp = $employee ?? $s->employee;
    $company = $company ?? $s->company;
    $employment = $employment ?? $emp?->employmentDetail;
    $monthName = \Carbon\Carbon::createFromDate($s->year, $s->month, 1)->format('F Y');
    $fmt = function ($n) {
        return number_format((float) ($n ?? 0), 2, '.', ',');
    };
    $joiningDate = !empty($employment?->date_of_joining)
        ? \Carbon\Carbon::parse($employment->date_of_joining)->format('d-m-Y')
        : '--';

    $earnings = array_filter([
        'Basic' => $s->fxs_basic ?? 0,
        'HRA' => $s->fxs_hra ?? 0,
        'Conveyance Allowance' => $s->conveyance_allowance ?? 0,
        'Medical Allowance' => $s->medical_allowance ?? 0,
        'Special Allowance' => $s->special_allowance ?? 0,
        'Other (FXS)' => $s->fxs_other ?? 0,
        'Present Days Amount' => $s->present_day_amount ?? 0,
        'Week Off Amount' => $s->employee_weekoff_amount ?? 0,
        'Working Week Off Amount' => $s->working_weekoff_amount ?? 0,
        'Company Pay Leave' => $s->company_pay_leave_amount ?? 0,
        'Employee Pay Leave' => $s->employee_pay_leave_amount ?? 0,
        'OT Payable' => $s->earn_ot_payable_amt ?? 0,
        'Performance Incentive' => $s->earn_performation_incentive ?? 0,
        'Bonus' => $s->bonus_amount ?? 0,
    ], fn($v) => (float) $v != 0);

    $deductions = array_filter([
        'PF (Employee)' => $s->ded_employee_pf ?? 0,
        'PMRPF' => $s->ded_pradhan_mantri_pf ?? 0,
        'ESI (Employee)' => $s->ded_esi_employee ?? 0,
        'ESI (Employer)' => $s->ded_esi_company ?? 0,
        'Professional Tax' => $s->ded_pt ?? 0,
        'Insurance' => $s->ded_insurance ?? 0,
        'Advance' => $s->ded_advance ?? 0,
        'TDS' => $s->ded_tds ?? 0,
        'Welfare Fund' => $s->ded_wf ?? 0,
        'Loan' => $s->ded_loan_amount ?? 0,
        'Other' => $s->ded_other ?? 0,
    ], fn($v) => (float) $v != 0);

    $totalEarning = (float) ($s->total_earning ?? 0);
    $totalDeduction = (float) ($s->total_deduction ?? 0);
    $netPay = (float) ($s->net_bank_pay ?? 0);
@endphp
<div class="salary-slip-single mb-4">
    <div class="salary-header">
        <div class="left-block">
            @if($company?->company_logo_url)
                @php
                    $logoPath = public_path(ltrim($company->company_logo, '/'));
                    $logoSrc = $company->company_logo_url;
                    if (file_exists($logoPath)) {
                        $ext = pathinfo($logoPath, PATHINFO_EXTENSION);
                        $mime = 'image/' . ($ext == 'jpg' ? 'jpeg' : $ext);
                        $data = base64_encode(file_get_contents($logoPath));
                        $logoSrc = 'data:' . $mime . ';base64,' . $data;
                    }
                @endphp
                <img src="{{ $logoSrc }}" alt="Logo" style="max-height: 70px;">
            @else
                <h2>{{ $company?->company_name ?? 'Company' }}</h2>
            @endif
        </div>
        <div class="center-block">
            <h4>SALARY SLIP</h4>
            <div class="divider"></div>
            <small>{{ $monthName }}</small>
        </div>
        <div class="right-block confidential">CONFIDENTIAL</div>
    </div>

    <div class="employee-info">
        <div class="col">
            <p><strong>Name:</strong> {{ $emp?->full_name ?? '--' }}</p>
            <p><strong>Employee ID:</strong> {{ $emp?->employee_code ?? '--' }}</p>
            <p><strong>PAN:</strong> {{ $emp?->pan_card_number ?? '--' }}</p>
        </div>
        <div class="col">
            <p><strong>Designation:</strong> {{ $employment?->designation?->name ?? '--' }}</p>
            <p><strong>Joining Date:</strong> {{ $joiningDate }}</p>
            <p><strong>Department:</strong> {{ $employment?->department?->name ?? '--' }}</p>
            <p><strong>Bank:</strong>
                {{ $emp?->bank_name ?? '--' }}
                @if ($emp?->bank_account_number && !empty($emp?->bank_account_number))
                    | A/c: {{ $emp?->bank_account_number }}
                @endif
                @if ($emp?->ifsc_code && !empty($emp?->ifsc_code))
                    | IFSC: {{ $emp?->ifsc_code }}
                @endif
            </p>
        </div>
    </div>

    {{-- Attendance summary (Indian HR practice) --}}
    <div class="attendance-summary">
        <table class="slip-table">
            <thead>
                <tr>
                    <th colspan="6">Attendance Summary</th>
                </tr>
                <tr>
                    <th>Total Days</th>
                    <th>Present</th>
                    <th>Absent</th>
                    <th>Leave</th>
                    <th>Week Off</th>
                    <th>Holiday</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $s->total_day ?? 0 }}</td>
                    <td>{{ $s->total_present_day ?? 0 }}</td>
                    <td>{{ $s->total_absent ?? 0 }}</td>
                    <td>{{ ($s->total_leave ?? 0) }}</td>
                    <td>{{ $s->total_week_off ?? 0 }}</td>
                    <td>{{ $s->holiday ?? 0 }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <table class="slip-table">
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-end">Earnings (₹)</th>
                <th class="text-end">Deductions (₹)</th>
            </tr>
        </thead>
        <tbody>
            @php $maxRows = max(count($earnings), count($deductions), 1); @endphp
            @for($i = 0; $i < $maxRows; $i++)
                <tr>
                    <td>{{ array_keys($earnings)[$i] ?? '' }}</td>
                    <td class="text-end">{{ isset(array_values($earnings)[$i]) ? $fmt(array_values($earnings)[$i]) : '' }}
                    </td>
                    <td class="text-end">
                        {{ isset(array_values($deductions)[$i]) ? $fmt(array_values($deductions)[$i]) : '' }}
                    </td>
                </tr>
            @endfor
        </tbody>
        <tfoot>
            <tr class="row-total">
                <th class="text-end">Total</th>
                <th class="text-end">₹ {{ $fmt($totalEarning) }}</th>
                <th class="text-end">₹ {{ $fmt($totalDeduction) }}</th>
            </tr>
            <tr class="row-net">
                <td colspan="2">
                    <strong>Net Pay in Words:</strong>
                    {{ $netPayInWords ?? \App\Helpers\Helper::convertToRupees($netPay) }}
                </td>
                <td class="text-end"><strong>Net Pay: ₹ {{ $fmt($netPay) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <div class="slip-footer">
        <small>Generated on {{ now()->setTimezone('Asia/Kolkata')->format('d M Y h:i A') }} | This is a system-generated
            slip.</small>
    </div>
</div>