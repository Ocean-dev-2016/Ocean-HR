@php
    $employment = $currentEmployment ?? null;
    $employeeName = $selectedEmployee?->full_name ?? '--';
    $employeeCode = $selectedEmployee?->employee_code ?? '--';
    $designation = $employment?->designation?->name ?? $selectedEmployee?->current_role?->name ?? '--';
    $department = $employment?->department?->name ?? '--';
    $workLocation = $employment?->branch?->name ?? $selectedEmployee?->company?->company_name ?? '--';
    $contactNumber = $selectedEmployee?->contact_number ?? '--';
    $joiningDate = $employment?->date_of_joining
        ? \Carbon\Carbon::parse($employment->date_of_joining)->format('d/m/Y')
        : '--';
    $today = \Carbon\Carbon::now()->format('d/m/Y');
    $headerImage = $headerImage 
        ?? ($selectedEmployee?->company?->order_header_logo_url ?? null)
        ?? ($selectedEmployee?->company?->company_logo_url ?? null)
        ?? asset('software/img/logo.png');
    $watermarkImage = $watermarkImage ?? asset('software/img/ring.png');
@endphp

<style>
    .loan-form {
        color: #111827;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 12px;
        line-height: 1.38;
        background: #fff;
    }

    .loan-page {
        position: relative;
        width: 210mm;
        min-height: 280mm;
        margin: 0 auto 8mm;
        background: #fff;
        border: 1px solid #111827;
        box-sizing: border-box;
        padding: 5mm 7mm 6mm;
        overflow: hidden;
        page-break-after: always;
        break-after: page;
        page-break-inside: avoid;
        break-inside: avoid;
    }

    .loan-page:last-child {
        page-break-after: auto;
        break-after: auto;
    }

    .loan-watermark {
        position: absolute;
        left: 50%;
        top: 55%;
        transform: translate(-50%, -50%);
        width: min(145mm, 82%);
        opacity: 0.10;
        pointer-events: none;
        z-index: 0;
    }

    .loan-header {
        position: relative;
        z-index: 1;
        margin-bottom: 3px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
    }

    .loan-header img {
        display: block;
        max-height: 55px;
        max-width: 260px;
        width: auto;
        height: auto;
        object-fit: contain;
        margin: 0 auto;
    }

    .loan-divider {
        border-top: 1px solid #111827;
        margin: 4px 0 8px;
    }

    .loan-title {
        text-align: center;
        font-size: 14px;
        font-weight: 700;
        margin: 6px 0 12px;
        text-transform: uppercase;
    }

    .loan-body {
        position: relative;
        z-index: 1;
    }

    .loan-top-row {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        gap: 10px;
        margin-bottom: 10px;
        font-size: 12px;
    }

    .loan-top-row .label {
        font-weight: 700;
        font-size: 14px;
        white-space: nowrap;
    }

    .loan-line {
        border-bottom: 1px solid #111827;
        min-height: 16px;
        flex: 1;
    }

    .loan-section-title {
        font-size: 14px;
        font-weight: 700;
        margin: 12px 0 6px;
    }

    .loan-table {
        width: 100%;
        border-collapse: collapse;
    }

    .loan-table td {
        padding: 4px 4px;
        vertical-align: top;
        font-size: 12px;
    }

    .loan-table .label {
        width: 30%;
        font-weight: 700;
        font-size: 14px;
        white-space: nowrap;
    }

    .loan-table .value {
        border-bottom: 1px solid #111827;
        min-height: 16px;
    }

    .loan-options {
        margin-top: 2px;
        display: flex;
        flex-wrap: wrap;
        gap: 8px 10px;
        line-height: 1.35;
        font-size: 12px;
        
    }

    .loan-option {
        white-space: nowrap;
        font-weight: 700;
    }

    .loan-lines {
        padding-top: 2px;
    }

    .loan-lines span {
        display: block;
        border-bottom: 1px solid #111827;
        height: 16px;
        margin-bottom: 8px;
    }

    .loan-lines span:last-child {
        margin-bottom: 0;
    }

    .loan-sign-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    .loan-sign-table td {
        padding: 5px 0;
        vertical-align: top;
        font-size: 12px;
    }

    .loan-sign-table .label {
        width: 34%;
        font-weight: 700;
        font-size: 14px;
        white-space: nowrap;
    }

    .loan-sign-table .value {
        border-bottom: 1px solid #111827;
        min-height: 16px;
    }

    .loan-section-note {
        margin: 8px 0 10px;
        font-size: 12px;
        line-height: 1.45;
        text-align: justify;
    }

    .loan-declaration {
        margin-top: 8px;
        font-size: 12px;
        line-height: 1.45;
        text-align: justify;
    }

    .loan-check-list {
        list-style: none;
        padding-left: 0;
        margin: 8px 0 0;
    }

    .loan-check-list li {
        margin: 0 0 5px;
        font-size: 12px;
    }

    .loan-approval-box {
        margin-top: 12px;
        border-top: 1px solid #111827;
        padding-top: 10px;
    }

    .loan-approval-subtitle {
        font-size: 14px;
        font-weight: 700;
        margin: 10px 0 6px;
    }

    .loan-approval-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 10px;
    }

    @media print {
        @page {
            size: A4;
            margin: 0;
        }

        .loan-page {
            width: auto;
            margin: 8mm auto;
            box-shadow: none;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }
</style>

<div class="loan-form">
    <div class="loan-page">
        <img class="loan-watermark" src="{{ $watermarkImage }}" alt="Watermark">

        <div class="loan-header">
            <img src="{{ $headerImage }}" alt="Header">
        </div>

        <div class="loan-divider"></div>

        <div class="loan-title">EMPLOYEE LOAN APPLICATION FORM</div>

        <div class="loan-body">
            <div class="loan-top-row">
                <div></div>
                <div><span class="label">Date:</span> <span class="loan-line" style="display:inline-block; min-width: 35mm; vertical-align: middle;"></span></div>
            </div>

            <div class="loan-section-title">Employee Details:</div>

            <table class="loan-table">
                <tr>
                    <td class="label">Employee Name:</td>
                    <td class="value">{{ $employeeName }}</td>
                </tr>
                <tr>
                    <td class="label">Employee Code:</td>
                    <td class="value">{{ $employeeCode }}</td>
                </tr>
                <tr>
                    <td class="label">Designation:</td>
                    <td class="value">{{ $designation }}</td>
                </tr>
                <tr>
                    <td class="label">Department:</td>
                    <td class="value">{{ $department }}</td>
                </tr>
                <tr>
                    <td class="label">Work Location:</td>
                    <td class="value">{{ $workLocation }}</td>
                </tr>
                <tr>
                    <td class="label">Contact Number:</td>
                    <td class="value">{{ $contactNumber }}</td>
                </tr>
            </table>

            <div class="loan-section-title">Loan Details:</div>

            <table class="loan-table">
                <tr>
                    <td class="label">Type of Loan Requested:</td>
                    <td class="value">
                        <div class="loan-options">
                            <span class="loan-option">&#9744; Salary Advance</span>
                            <span class="loan-option">&#9744; Emergency Loan</span>
                            <span class="loan-option">&#9744; Medical Loan</span>
                            <span class="loan-option">&#9744; Festival Loan</span>
                            <span class="loan-option">&#9744; Other: <span class="loan-line" style="display:inline-block; min-width: 28mm; vertical-align: middle;"></span></span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="label">Loan Amount Requested (&#8377;):</td>
                    <td class="value">&nbsp;</td>
                </tr>
                <tr>
                    <td class="label">Reason for Loan:</td>
                    <td>
                        <div class="loan-lines">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </td>
                </tr>
            </table>

            <div class="loan-section-title">Repayment Details:</div>

            <table class="loan-table">
                <tr>
                    <td class="label">Preferred No. of Instalments:</td>
                    <td class="value">&nbsp;</td>
                </tr>
                <tr>
                    <td class="label">Proposed EMI Amount (&#8377;):</td>
                    <td class="value">&nbsp;</td>
                </tr>
                <tr>
                    <td class="label">Preferred Start Month of Deduction:</td>
                    <td class="value">&nbsp;</td>
                </tr>
            </table>

            <div class="loan-section-title">Previous Loan Details (if any):</div>

            <table class="loan-table">
                <tr>
                    <td class="label">Previous Loan Taken:</td>
                    <td class="value">
                        <div class="loan-options">
                            <span class="loan-option">&#9744; Yes</span>
                            <span class="loan-option">&#9744; No</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="label">If Yes, Amount (&#8377;):</td>
                    <td class="value">&nbsp;</td>
                </tr>
                <tr>
                    <td class="label">Outstanding Balance (&#8377;):</td>
                    <td class="value">&nbsp;</td>
                </tr>
            </table>

            <div class="loan-section-title">Declaration by Employee:</div>

            <div class="loan-section-note">
                I hereby declare that the above information provided by me is true and correct. I agree to repay the loan
                amount through salary deduction as approved by the management. I also understand that in case of
                resignation/termination, the outstanding loan amount will be recovered from my Full &amp; Final settlement.
            </div>

            <table class="loan-sign-table">
                <tr>
                    <td class="label">Employee Signature:</td>
                    <td class="value">&nbsp;</td>
                </tr>
                <tr>
                    <td class="label">Name:</td>
                    <td class="value">&nbsp;</td>
                </tr>
                <tr>
                    <td class="label">Date:</td>
                    <td class="value">&nbsp;</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="loan-page">
        <img class="loan-watermark" src="{{ $watermarkImage }}" alt="Watermark">

        <div class="loan-header">
            <img src="{{ $headerImage }}" alt="Header">
        </div>

        <!-- <div class="loan-divider"></div> -->

        <div class="loan-body">
           
            <div class="loan-approval-box">
                <div class="loan-approval-subtitle">For HR Department Use Only:</div>

                <table class="loan-table">
                    <tr>
                        <td class="label">Eligibility Verified:</td>
                        <td class="value">
                            <div class="loan-options">
                                <span class="loan-option">&#9744; Yes</span>
                                <span class="loan-option">&#9744; No</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="label">HR Signature:</td>
                        <td class="value">&nbsp;</td>
                    </tr>
                    <tr>
                        <td class="label">Date:</td>
                        <td class="value">&nbsp;</td>
                    </tr>
                </table>
                        <div class="loan-divider" style="margin-top: 12px;"></div>    

                <div class="loan-approval-subtitle">For Accounts Department Use Only:</div>

                <table class="loan-table">
                    <tr>
                        <td class="label">Loan Approved Amount (&#8377;):</td>
                        <td class="value">&nbsp;</td>
                    </tr>
                    <tr>
                        <td class="label">No. of Instalments:</td>
                        <td class="value">&nbsp;</td>
                    </tr>
                    <tr>
                        <td class="label">EMI Amount (&#8377;):</td>
                        <td class="value">&nbsp;</td>
                    </tr>
                    <tr>
                        <td class="label">Recovery Start From:</td>
                        <td class="value">&nbsp;</td>
                    </tr>
                    <tr>
                        <td class="label">Accounts Signature:</td>
                        <td class="value">&nbsp;</td>
                    </tr>
                    <tr>
                        <td class="label">Date:</td>
                        <td class="value">&nbsp;</td>
                    </tr>
                </table>
                        <div class="loan-divider" style="margin-top: 12px;"></div>    

                <div class="loan-approval-subtitle">Management Approval:</div>

                <table class="loan-table">
                    <tr>
                        <td class="label">Approved:</td>
                        <td class="value">
                            <div class="loan-options">
                                <span class="loan-option">&#9744; Yes</span>
                                <span class="loan-option">&#9744; No</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Authorized Signatory Name:</td>
                        <td class="value">&nbsp;</td>
                    </tr>
                    <tr>
                        <td class="label">Signature:</td>
                        <td class="value">&nbsp;</td>
                    </tr>
                    <tr>
                        <td class="label">Date:</td>
                        <td class="value">&nbsp;</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
