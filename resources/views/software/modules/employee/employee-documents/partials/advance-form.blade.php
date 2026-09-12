@php
    $employment = $currentEmployment ?? null;
    $designation = $employment?->designation?->name ?? $selectedEmployee?->current_role?->name ?? '--';
    $department = $employment?->department?->name ?? '--';
    $joiningDate = $employment?->date_of_joining
        ? \Carbon\Carbon::parse($employment->date_of_joining)->format('d/m/Y')
        : '--';
    $contactNumber = $selectedEmployee?->contact_number ?? '--';
    $employeeName = $selectedEmployee?->full_name ?? '--';
    $employeeCode = $selectedEmployee?->employee_code ?? '--';
@endphp

<style>
    .advance-form {
        position: relative;
        font-size: 13.25px;
        color: #111827;
        line-height: 1.32;
    }

    .advance-form table {
        width: 100%;
        border-collapse: collapse;
    }

    .advance-form .field-table td {
        padding: 3px 3px 8px;
        vertical-align: top;
    }

    .advance-form .field-label {
        width: 34%;
        /* font-weight: 700; */
        white-space: nowrap;
    }

    .advance-form .field-value {
        border-bottom: 1px solid #111827;
        min-height: 18px;
        padding-left: 6px;
        padding-bottom: 2px;
    }

    .advance-form .field-multiline {
        padding-left: 6px;
        padding-bottom: 0;
        border-bottom: none;
    }

    .advance-form .reason-lines {
        padding-top: 2px;
    }

    .advance-form .reason-lines span {
        display: block;
        border-bottom: 1px solid #111827;
        height: 16px;
        margin-bottom: 10px;
    }

    .advance-form .reason-lines span:last-child {
        margin-bottom: 0;
    }

    .advance-form .section-title {
        font-size: 12.5px;
        font-weight: 700;
        margin: 12px 0 6px;
    }

    .advance-form .mode-label {
        font-weight: 700;
        margin: 4px 0 5px;
    }

    .advance-form .option-line {
        margin: 0 0 5px;
        padding-left: 2px;
    }

    .advance-form .signature-table {
        margin-top: 14px;
    }

    .advance-form .signature-table td {
        border: 1px solid #111827;
        text-align: center;
        font-weight: 700;
        font-size: 13.25px;
        padding: 20px 4px;
    }

    .advance-form .signature-row td {
        height: 30px;
    }

    .advance-form .declaration-text {
        margin-bottom: 6px;
    }
</style>

<div class="advance-form">
    <table class="field-table">
        <tr>
            <td class="field-label">Employee Name:</td>
            <td class="field-value">{{ $employeeName }}</td>
        </tr>
        <tr>
            <td class="field-label">Employee ID:</td>
            <td class="field-value">{{ $employeeCode }}</td>
        </tr>
        <tr>
            <td class="field-label">Department:</td>
            <td class="field-value">{{ $department }}</td>
        </tr>
        <tr>
            <td class="field-label">Designation:</td>
            <td class="field-value">{{ $designation }}</td>
        </tr>
        <tr>
            <td class="field-label">Date of Joining:</td>
            <td class="field-value">{{ $joiningDate }}</td>
        </tr>
        <tr>
            <td class="field-label">Contact Number:</td>
            <td class="field-value">{{ $contactNumber }}</td>
        </tr>
    </table>

    <div class="section-title">Advance Request Details</div>
    <table class="field-table">
        <tr>
            <td class="field-label">Date of Request:</td>
            <td class="field-value">____ / ____ / ______</td>
        </tr>
        <tr>
            <td class="field-label">Amount Requested (&#8377;):</td>
            <td class="field-value">&nbsp;</td>
        </tr>
        <tr>
            <td class="field-label">Reason for Salary Advance:</td>
            <td class="field-value field-multiline">
                <div class="reason-lines">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </td>
        </tr>
    </table>

    <div class="section-title">Repayment Details:</div>
    <div class="mode-label">Mode of Deduction (Tick one):</div>
    <div class="option-line">&#9744; Full deduction from next salary</div>
    <div class="option-line">&#9744; Deduction in installments (mention details below)</div>

    <div class="mode-label" style="margin-top: 4px;">If installment:</div>
    <table class="field-table">
        <tr>
            <td class="field-label">Number of Installments:</td>
            <td class="field-value">&nbsp;</td>
        </tr>
        <tr>
            <td class="field-label">Installment Amount (&#8377;):</td>
            <td class="field-value">&nbsp;</td>
        </tr>
        <tr>
            <td class="field-label">Deduction Start Month:</td>
            <td class="field-value">&nbsp;</td>
        </tr>
    </table>

    <div class="section-title" style="margin-top: 12px;">Employee Declaration:</div>
    <div class="option-line declaration-text" style="margin-bottom: 0;font-weight: 700; !important">
        I hereby request a salary advance of the amount mentioned above. I authorize OceanHR to
        deduct the approved advance amount from my salary as per agreed terms.
    </div>

    <table class="signature-table">
        <tr class="signature-row">
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>Employee Signature</td>
            <td>Head - HR Signature</td>
            <td>Sign of Managing Director</td>
        </tr>
    </table>
</div>
