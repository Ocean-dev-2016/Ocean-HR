@php
    $employment = $currentEmployment ?? null;
    $increment = $latestIncrement ?? null;
    $currentSalary = $latestSalary ?? null;

    $employeeName = $selectedEmployee?->full_name ?? '--';
    $gender = strtolower(trim((string) ($selectedEmployee?->gender ?? '')));
    $salutation = $gender === 'female' ? 'Mrs.' : 'Mr.';
    $designation = $employment?->designation?->name ?? $selectedEmployee?->current_role?->name ?? '--';
    $department = $employment?->department?->name ?? '--';
    $joiningDate = $employment?->date_of_joining
        ? \Carbon\Carbon::parse($employment->date_of_joining)->format('j-n-Y')
        : '--';
    $incrementDate = $increment?->icrement_date
        ? \Carbon\Carbon::parse($increment->icrement_date)->format('d/m/Y')
        : \Carbon\Carbon::now()->format('d/m/Y');

    $effectiveFrom = '--';
    if (!empty($increment?->effective_month) || !empty($increment?->effective_year)) {
        $effectiveFrom = trim(($increment?->effective_month ?? '') . ' ' . ($increment?->effective_year ?? ''));
    }

    $nextReview = '--';
    if (!empty($employment?->date_of_joining)) {
        $joiningYear = \Carbon\Carbon::parse($employment->date_of_joining)->year;
        $nextReview = \Carbon\Carbon::create($joiningYear + 1, 3, 1)->format('F-Y');
    } elseif (!empty($increment?->icrement_date)) {
        $nextReview = \Carbon\Carbon::parse($increment->icrement_date)
            ->addYear()
            ->month(3)
            ->day(1)
            ->format('F-Y');
    }

    $companyName = $selectedEmployee?->company?->company_name ?? 'OceanHR';

    $formatAmount = static function ($value): string {
        $numeric = (float) ($value ?? 0);
        return '&#8377; ' . number_format($numeric, 2);
    };

    $previousGrossSalary = (float) (
        $currentSalary?->previous_gross_salary
        ?? $currentSalary?->ctc
        ?? 0
    );

    $revisedGrossSalary = $increment
        ? (float) ($currentSalary?->ctc ?? 0)
        : null;
@endphp

<style>
    .increment-letter {
        font-size: 14px;
        line-height: 1.42;
        color: #111827;
    }

    .increment-letter .top-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-top: 2px;
        margin-bottom: 6px;
        font-size: 11.5px;
    }

    .increment-letter .intro-gap {
        height: 8px;
    }

    .increment-letter .top-row .to-block {
        width: 72%;
    }

    .increment-letter .top-row .date-block {
        width: 26%;
        text-align: right;
        padding-top: 10px;
    }

    .increment-letter .label-line {
        display: flex;
        gap: 6px;
        margin: 2px 0;
        align-items: baseline;
    }

    .increment-letter .label-line .label {
        width: 92px;
        font-weight: 700;
        flex: 0 0 auto;
    }

    .increment-letter .label-line .value {
        font-weight: 700;
        flex: 1;
    }

    .increment-letter p {
        margin: 10px 0 8px;
        text-align: justify;
    }

    .increment-letter .comp-box {
        margin: 10px 0 8px;
    }

    .increment-letter .comp-grid {
        width: 100%;
        border-collapse: collapse;
        margin-top: 4px;
    }

    .increment-letter .comp-grid td {
        padding: 2px 0;
        vertical-align: top;
        font-size: 12px;
    }

    .increment-letter .comp-grid .comp-label {
        width: 44%;
        font-weight: 700;
    }

    .increment-letter .comp-grid .comp-value {
        width: 56%;
        font-weight: 700;
    }

    .increment-letter .sign-block {
        margin-top: 45px;
    }

    .increment-letter .sign-line {
        border-bottom: 1px solid #111827;
        height: 35px;
        margin: 6px 0 10px;
    }

    .increment-letter .ack-title {
        text-align: center;
        font-weight: 700;
        margin: 6px 0 10px;
    }

    .increment-letter .ack-box {
        margin-top: 8px;
    }

    .increment-letter .ack-row {
        display: flex;
        gap: 8px;
        align-items: baseline;
        margin: 4px 0;
    }

    .increment-letter .ack-row .ack-label {
        width: 130px;
        font-weight: 700;
        flex: 0 0 auto;
    }

    .increment-letter .ack-row .ack-value {
        flex: 1;
        border-bottom: 1px solid #111827;
        min-height: 16px;
    }
</style>

<div class="increment-letter">
    <div class="top-row">
        <div class="to-block">
            <div class="label-line">
                <span class="label">To,</span>
                <span class="value">&nbsp;</span>
            </div>
            <div class="label-line">
                <span class="label">Mr./Ms.</span>
                <span class="value">{{ $employeeName }}</span>
            </div>
            <div class="label-line">
                <span class="label">Department:</span>
                <span class="value">{{ $department }}</span>
            </div>
            <div class="label-line">
                <span class="label">Designation:</span>
                <span class="value">{{ $designation }}</span>
            </div>
            <div class="label-line">
                <span class="label">Subject:</span>
                <span class="value">Revision of Salary</span>
            </div>
        </div>
        <div class="date-block">
            <div><strong>Date:</strong> {{ $incrementDate }}</div>
        </div>
    </div>

    <div class="intro-gap"></div>

    <p>Dear <strong>{{ $salutation }} {{ $employeeName }}</strong>,</p>

    <p>
        We are pleased to inform you that based on your performance, contribution to the organization, and management
        review, your salary has been revised.
    </p>

    <p><strong>Your revised compensation details are as follows:</strong></p>

    <div class="comp-box">
        <table class="comp-grid">
          
            <tr>
                <td class="comp-label">Previous Gross Salary:</td>
                <td class="comp-value">{!! $formatAmount($previousGrossSalary) !!} per month</td>
            </tr>
            <tr>
                <td class="comp-label">Revised Gross Salary:</td>
                <td class="comp-value">{!! $revisedGrossSalary !== null ? $formatAmount($revisedGrossSalary) . ' per month' : '-' !!}</td>
            </tr>
            <tr>
                <td class="comp-label">Effective From:</td>
                <td class="comp-value">{{ $effectiveFrom }}</td>
            </tr>
            <tr>
                <td class="comp-label">Next Performance Review:</td>
                <td class="comp-value">{{ $nextReview }}</td>
            </tr>
        </table>
    </div>

    <p>
        This revision reflects the organization's appreciation of your efforts and commitment towards your
        responsibilities. We expect your continued dedication and valuable contribution to the growth of the company.
    </p>

    <p>
        All other terms and conditions of your employment remain unchanged.
    </p>

    <p>
        We wish you continued success in your role and look forward to your ongoing support in achieving
        organizational goals.
    </p>

    <p><strong>For, {{ $companyName }}.</strong></p>

    <div class="sign-block">
        <div><strong>Authorized Signatory</strong></div>
        <div class="sign-line"></div>
    </div>

    <div class="ack-box">
        <div class="ack-title">Employee Acknowledgement:</div>
        <p>I accept the revised compensation structure as stated above.</p>

        <div class="ack-row">
            <div class="ack-label">Employee Signature:</div>
            <div class="ack-value"></div>
        </div>
        <div class="ack-row">
            <div class="ack-label">Date:</div>
            <div class="ack-value" style="max-width: 150px;"></div>
        </div>
    </div>
</div>
