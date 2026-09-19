@php
    $employment = $currentEmployment ?? null;
    $employeeName = $selectedEmployee?->full_name ?? '--';
    $employeeCode = $selectedEmployee?->employee_code ?? '--';
    $designation = $employment?->designation?->name ?? $selectedEmployee?->current_role?->name ?? '--';
    $department = $employment?->department?->name ?? '--';
    $joiningDate = $employment?->date_of_joining
        ? \Carbon\Carbon::parse($employment->date_of_joining)->format('d/m/Y')
        : '--';
    $lastWorkingDate = $selectedEmployee?->resign_date
        ? \Carbon\Carbon::parse($selectedEmployee->resign_date)->format('d/m/Y')
        : ($employment?->end_date ? \Carbon\Carbon::parse($employment->end_date)->format('d/m/Y') : '--');
    $companyName = $selectedEmployee?->company?->company_name ?? 'OceanHR';
    $headerImage = $headerImage 
        ?? ($selectedEmployee?->company?->order_header_logo_url ?? null)
        ?? ($selectedEmployee?->company?->company_logo_url ?? null)
        ?? asset('software/img/logo.png');
    $watermarkImage = $watermarkImage ?? asset('software/img/Ocean_HR.png');
    $today = \Carbon\Carbon::now()->format('d/m/Y');
@endphp

<style>
    .no-due-clearance-form {
        color: #111827;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 12px;
        line-height: 1.38;
        background: #fff;
    }

    .no-due-page {
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

    .no-due-page:last-child {
        page-break-after: auto;
        break-after: auto;
    }

    .no-due-watermark {
        position: absolute;
        left: 50%;
        top: 55%;
        transform: translate(-50%, -50%);
        width: min(145mm, 82%);
        opacity: 0.10;
        pointer-events: none;
        z-index: 0;
    }

    .no-due-header {
        position: relative;
        z-index: 1;
        margin-bottom: 3px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
    }

    .no-due-header img {
        display: block;
        max-height: 55px;
        max-width: 260px;
        width: auto;
        height: auto;
        object-fit: contain;
        margin: 0 auto;
    }

    .no-due-divider {
        border-top: 1px solid #111827;
        margin: 4px 0 8px;
    }

    .no-due-title {
        text-align: center;
        font-size: 14px;
        font-weight: 700;
        margin: 6px 0 14px;
        text-transform: uppercase;
    }

    .no-due-body {
        position: relative;
        z-index: 1;
    }

    .no-due-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: baseline;
        margin: 0 0 8px;
        font-size: 12px;
        line-height: 1.45;
    }

    .no-due-row--intro {
        margin-bottom: 10px;
        text-align: justify;
    }

    .no-due-label {
        font-weight: 700;
        white-space: nowrap;
        font-size: 14px;
    }

    .no-due-line {
        border-bottom: 1px solid #111827;
        min-width: 42mm;
        flex: 1 1 42mm;
        height: 16px;
    }

    .no-due-inline-fill {
        flex: 1 1 auto;
        border-bottom: 1px solid #111827;
        min-width: 24mm;
        height: 16px;
    }

    .no-due-note {
        margin: 30px 0 25px;
        font-size: 14px;
        line-height: 1.4;
        text-align: justify;
    }

    .no-due-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        margin-top: 6px;
    }

    .no-due-table th,
    .no-due-table td {
        border: 1px solid #111827;
        padding: 12px 5px;
        vertical-align: top;
        font-size: 13px;
    }

    .no-due-table th {
        background: #c00;
        color: #fff;
        text-align: left;
        font-weight: 700;
        font-size: 14px;
    }

    .no-due-table .center {
        text-align: center;
    }

    .no-due-table .dept {
        min-height: 24px;
    }

    .no-due-sign-block {
        margin-top: 10px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px 20px;
    }

    .no-due-sign-row {
        display: flex;
        align-items: baseline;
        gap: 6px;
        margin-top: 10px;
        font-size: 12px;
    }

    .no-due-sign-row .no-due-label {
        white-space: nowrap;
    }

    .no-due-sign-line {
        flex: 1;
        border-bottom: 1px solid #111827;
        height: 16px;
        min-width: 34mm;
    }

    .no-due-section-title {
        font-size: 14px;
        font-weight: 700;
        margin: 10px 0 6px;
    }

    .no-due-checkbox-list {
        margin: 8px 0 6px;
        padding-left: 0;
        list-style: none;
        columns: 2;
        column-gap: 18px;
    }

    .no-due-checkbox-list li {
        break-inside: avoid;
        margin: 0 0 4px;
        font-size: 12px;
    }

    .no-due-hr-box {
        margin-top: 14px;
        border-top: 1px solid #111827;
        padding-top: 10px;
    }

    .no-due-note-strong {
        font-weight: 700;
    }

    .no-due-footer-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 25px;
    }

    .no-due-footer-table td {
        padding: 10px 0;
        vertical-align: top;
        font-size: 12px;
    }

    .no-due-footer-table .label {
        width: 34%;
        font-weight: 700;
        white-space: nowrap;
        font-size: 14px;
    }

    .no-due-footer-table .value {
        border-bottom: 1px solid #111827;
        min-height: 16px;
    }

    @media print {
        @page {
            size: A4;
            margin: 0;
        }

        .no-due-page {
            width: auto;
            margin: 8mm auto;
            box-shadow: none;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }
</style>

<div class="no-due-clearance-form">
    <div class="no-due-page">
        <img class="no-due-watermark" src="{{ $watermarkImage }}" alt="Watermark">

        <div class="no-due-header">
            <img src="{{ $headerImage }}" alt="Header">
        </div>

        <div class="no-due-divider"></div>

        <div class="no-due-title">NO DUE CLEARANCE FORM</div>

        <div class="no-due-body">
            <div class="no-due-row no-due-row--intro">
                This is to certify that Mr./Ms. <strong>{{ $employeeName }}</strong>,
                Employee ID <strong>{{ $employeeCode }}</strong>,
                Designation <strong>{{ $designation }}</strong>,
                Department <strong>{{ $department }}</strong>,
                has applied for separation from the services of the company.
            </div>

            <table class="no-due-footer-table">
                <tr>
                    <td class="label">Date of Joining:</td>
                    <td class="value">{{ $joiningDate }}</td>
                </tr>
                <tr>
                    <td class="label">Last Working Date:</td>
                    <td class="value">{{ $lastWorkingDate }}</td>
                </tr>
                <tr>
                    <td class="label">Reason for Separation:</td>
                    <td class="value"><strong>Resignation / Termination / Retirement / Contract Completion</strong></td>
                </tr>
            </table>

            <div class="no-due-note">
                The employee is required to obtain clearance from the following departments before Full &amp; Final
                Settlement processing. Mention "N/A" where Not applicable.
            </div>

            <div class="no-due-section-title">Department Clearance Status:</div>

            <table class="no-due-table">
                <thead>
                    <tr>
                        <th style="width: 6%;" class="center">Sr. No.</th>
                        <th style="width: 28%;">Department</th>
                        <th style="width: 28%;">Clearance Status (No Due / Pending)</th>
                        <th style="width: 18%;">Remarks</th>
                        <th style="width: 13%;">Authorized Signature</th>
                        <th style="width: 10%;">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $departments = [
                            'Reporting Manager',
                            'HR Department',
                            'Administration',
                            'IT Department',
                            'Accounts Department',
                            'Store / Inventory',
                            'Security',
                            'Production Department (if applicable)',
                            'Purchase Department (if applicable)',
                            'Other (Specify)',
                        ];
                    @endphp
                    @foreach ($departments as $index => $departmentName)
                        <tr>
                            <td class="center">{{ $index + 1 }}</td>
                            <td class="dept">{{ $departmentName }}</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="no-due-page">
        <img class="no-due-watermark" src="{{ $watermarkImage }}" alt="Watermark">

        <div class="no-due-header">
            <img src="{{ $headerImage }}" alt="Header">
        </div>

        <div class="no-due-divider"></div>

        <div class="no-due-body">
            <div class="no-due-section-title">Asset Return Declaration</div>

            <div class="no-due-note" style="margin-top: 4px;">
           I confirm that I have returned all company assets issued to me including:
            </div>

            <ul class="no-due-checkbox-list">
                <li>&#9744; ID Card</li>
                <li>&#9744; Laptop / Desktop</li>
                <li>&#9744; Mobile Phone</li>
                <li>&#9744; Uniform / PPE</li>
                <li>&#9744; Documents / Files</li>
                <li>&#9744; Tools / Equipment</li>
                <li>&#9744; Other: <span class="no-due-line" style="display:inline-block; min-width: 45mm; vertical-align: middle;"></span></li>
            </ul>

            <table class="no-due-footer-table" style="margin-top: 12px;">
                <tr>
                    <td class="label">Employee Signature:</td>
                    <td class="value">&nbsp;</td>
                </tr>
                <tr>
                    <td class="label">Date:</td>
                    <td class="value">&nbsp;</td>
                </tr>
            </table>

            <div class="no-due-hr-box">
                <div class="no-due-section-title" style="margin-top: 0;">HR Final Confirmation</div>

                <div class="no-due-note">
                    All departmental clearances have been completed and there are <strong> no dues pending </strong> against the employee.
                </div>

                <table class="no-due-footer-table">
                    <tr>
                        <td class="label">HR Name:</td>
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

                <div class="no-due-section-title" style="margin-top: 14px;">Approval for Full &amp; Final Settlement Processing</div>

                <table class="no-due-footer-table">
                    <tr>
                        <td class="label">Accounts Executive Name:</td>
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
