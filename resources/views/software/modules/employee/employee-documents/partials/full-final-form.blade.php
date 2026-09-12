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
    $headerImage = $headerImage ?? asset('software/img/header.jpg');
    $watermarkImage = $watermarkImage ?? asset('software/img/Ocean_HR.png');
@endphp

<style>
    .full-final-form {
        color: #111827;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 12px;
        line-height: 1.38;
        background: #fff;
    }

    .full-final-page {
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

    .full-final-page:last-child {
        page-break-after: auto;
        break-after: auto;
    }

    .full-final-watermark {
        position: absolute;
        left: 50%;
        top: 55%;
        transform: translate(-50%, -50%);
        width: min(145mm, 82%);
        opacity: 0.10;
        pointer-events: none;
        z-index: 0;
    }

    .full-final-header {
        position: relative;
        z-index: 1;
        margin-bottom: 3px;
    }

    .full-final-header img {
        display: block;
        width: 100%;
        height: auto;
        opacity: 0.64;
        filter: grayscale(0.08) contrast(0.96);
    }

    .full-final-divider {
        border-top: 1px solid #111827;
        margin: 4px 0 8px;
    }

    .full-final-title {
        text-align: center;
        font-size: 17px;
        font-weight: 700;
        margin: 6px 0 14px;
        text-transform: uppercase;
    }

    .full-final-body {
        position: relative;
        z-index: 1;
    }

    .full-final-table {
        width: 100%;
        border-collapse: collapse;
    }

    .full-final-table td {
        padding: 10px 4px;
        vertical-align: top;
        font-size: 12px;
    }

    .full-final-label {
        width: 30%;
        font-weight: 700;
        font-size: 14px;
        white-space: nowrap;
    }

    .full-final-line {
        border-bottom: 1px solid #111827;
        min-height: 16px;
    }

    .full-final-section-title {
        font-size: 14px;
        font-weight: 700;
        margin: 12px 0 6px;
    }

    .full-final-note {
        margin: 8px 0 10px;
        font-size: 12px;
        line-height: 1.45;
        text-align: justify;
    }

    .full-final-subnote {
        margin: 6px 0 0;
        font-size: 11px;
        line-height: 1.35;
    }

    .full-final-grid {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        margin-top: 6px;
    }

    .full-final-grid th,
    .full-final-grid td {
        border: 1px solid #111827;
        padding: 13px 5px;
        vertical-align: top;
        font-size: 14px;
    }

    .full-final-grid th {
        background: #c00;
        color: #fff;
        text-align: left;
        font-weight: 700;
        font-size: 14px;
    }

    .full-final-grid .center {
        text-align: center;
    }

    .full-final-grid .fill-row {
        height: 28px;
    }

    .full-final-total-row {
        margin-top: 10px;
        font-size: 13px;
        line-height: 1.5;
    }

    .full-final-total-row .amount-line {
        display: inline-block;
        min-width: 32mm;
        border-bottom: 1px solid #111827;
        height: 16px;
        vertical-align: middle;
    }

    .full-final-long-line {
        display: inline-block;
        min-width: 72mm;
        border-bottom: 1px solid #111827;
        height: 16px;
        vertical-align: middle;
    }

    .full-final-asset-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        margin-top: 8px;
    }

    .full-final-asset-table th,
    .full-final-asset-table td {
        border: 1px solid #111827;
        padding: 8px 5px;
        vertical-align: top;
        font-size: 14px;
    }

    .full-final-asset-table th {
        background: #c00;
        color: #fff;
        text-align: left;
        font-weight: 700;
        font-size: 14px;
    }

    .full-final-checklist {
        list-style: none;
        padding-left: 0;
        margin: 8px 0 0;
        columns: 2;
        column-gap: 18px;
    }

    .full-final-checklist li {
        break-inside: avoid;
        margin: 0 0 5px;
        font-size: 12px;
    }

    .full-final-sign-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    .full-final-sign-table td {
        padding: 4px 0;
        vertical-align: top;
        font-size: 12px;
    }

    .full-final-sign-table .label {
        width: 34%;
        /* font-weight: 700; */
        font-size: 14px;
        white-space: nowrap;
    }

    .full-final-sign-table .value {
        border-bottom: 1px solid #111827;
        min-height: 16px;
    }

    .full-final-master-copy {
        position: absolute;
        right: 10mm;
        bottom: 8mm;
        width: 40mm;
        border: 1px solid #3457d5;
        color: #3457d5;
        font-size: 8px;
        line-height: 1.1;
        padding: 2px 4px;
        text-align: center;
        background: rgba(255, 255, 255, 0.92);
        z-index: 2;
    }

    .full-final-master-copy strong {
        display: block;
        font-size: 10px;
        letter-spacing: 0.4px;
        margin-bottom: 1px;
    }

    .full-final-footer-space {
        height: 22px;
    }

    @media print {
        @page {
            size: A4;
            margin: 0;
        }

        .full-final-page {
            width: auto;
            margin: 8mm auto;
            box-shadow: none;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }
</style>

<div class="full-final-form">
    <div class="full-final-page">
        <div class="full-final-header">
            <img src="{{ $headerImage }}" alt="Header">
        </div>

        <div class="full-final-divider"></div>

        <div class="full-final-title">FULL &amp; FINAL SETTLEMENT STATEMENT</div>

        <div class="full-final-body">
            <table class="full-final-table">
                <tr>
                    <td class="full-final-label">Employee Name:</td>
                    <td class="full-final-line">{{ $employeeName }}</td>
                </tr>
                <tr>
                    <td class="full-final-label">Employee ID:</td>
                    <td class="full-final-line">{{ $employeeCode }}</td>
                </tr>
                <tr>
                    <td class="full-final-label">Designation:</td>
                    <td class="full-final-line">{{ $designation }}</td>
                </tr>
                <tr>
                    <td class="full-final-label">Department:</td>
                    <td class="full-final-line">{{ $department }}</td>
                </tr>
                <tr>
                    <td class="full-final-label">Date of Joining:</td>
                    <td class="full-final-line">{{ $joiningDate }}</td>
                </tr>
                <tr>
                    <td class="full-final-label">Last Working Date:</td>
                    <td class="full-final-line">{{ $lastWorkingDate }}</td>
                </tr>
                <tr>
                    <td class="full-final-label">Reason for Separation:</td>
                    <td class="full-final-line"><strong>Resignation / Termination / Absconding / Retirement / Contract Completion</strong></td>
                </tr>
            </table>

            <div class="full-final-section-title">Earnings Payable:</div>
            <table class="full-final-grid">
                <thead>
                    <tr>
                        <th style="width: 9%;" class="center">Sr. No.</th>
                        <th style="width: 56%;">Particulars</th>
                        <th style="width: 35%;">Amount (&#8377;)</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $earnings = [
                            'Salary Payable (Last Working Month)',
                            'Notice Period Salary (If applicable)',
                            'Leave Encashment',
                            'Incentive / Bonus (If applicable)',
                            'Overtime Payable (If applicable)',
                            'Gratuity (If applicable)',
                            'Other Payables',
                        ];
                    @endphp
                    @foreach ($earnings as $index => $item)
                        <tr>
                            <td class="center">{{ $index + 1 }}</td>
                            <td>{{ $item }}</td>
                            <td class="fill-row">&nbsp;</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="full-final-total-row">
                <strong>Total Earnings (A) = &#8377; </strong><span class="amount-line">&nbsp;</span>
            </div>
        </div>
    </div>

    <div class="full-final-page">
        <div class="full-final-header">
            <img src="{{ $headerImage }}" alt="Header">
        </div>

        <div class="full-final-divider"></div>

        <div class="full-final-body">
            <div class="full-final-section-title">Deductions</div>
            <table class="full-final-grid">
                <thead>
                    <tr>
                        <th style="width: 9%;" class="center">Sr. No.</th>
                        <th style="width: 56%;">Particulars</th>
                        <th style="width: 35%;">Amount (&#8377;)</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $deductions = [
                            'Notice Pay Recovery (If applicable)',
                            'Salary Advance Recovery',
                            'Loan Recovery',
                            'Asset Recovery / Damage Charges',
                            'PF / ESIC Adjustment (If applicable)',
                            'Other Deductions',
                        ];
                    @endphp
                    @foreach ($deductions as $index => $item)
                        <tr>
                            <td class="center">{{ $index + 1 }}</td>
                            <td>{{ $item }}</td>
                            <td class="fill-row">&nbsp;</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="full-final-total-row">
                <div><strong>Total Deductions (B) = &#8377; </strong><span class="amount-line">&nbsp;</span></div>
                <div style="margin-top: 8px;"><strong>Net Payable Amount:</strong> <span class="full-final-long-line">&nbsp;</span></div>
                <div style="margin-top: 8px;"><strong>Net Payable (A - B) = &#8377; </strong><span class="amount-line">&nbsp;</span></div>
                <div style="margin-top: 8px;">(Rupees <span class="full-final-long-line">&nbsp;</span> Only)</div>
                <div style="margin-top: 8px;"><strong>Payment Mode: Bank Transfer / Cheque / Cash </strong></div>
                <div style="margin-top: 8px;"><strong>Payment Date:</strong> <span class="amount-line">&nbsp;</span></div>
            </div>

            <div class="full-final-section-title" style="margin-top: 14px;">Asset Clearance Status</div>
            <table class="full-final-asset-table">
                <thead>
                    <tr>
                        <th style="width: 50%;">Item</th>
                        <th style="width: 50%;">Status (Returned / Not Returned)</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $assets = ['ID Card', 'Laptop', 'Mobile Phone', 'Uniform', 'Documents', 'Other Assets'];
                    @endphp
                    @foreach ($assets as $asset)
                        <tr>
                            <td>{{ $asset }}</td>
                            <td>&nbsp;</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        
    </div>

    <div class="full-final-page">
        <div class="full-final-header">
            <img src="{{ $headerImage }}" alt="Header">
        </div>

        <div class="full-final-divider"></div>

        <div class="full-final-body">
            <div class="full-final-section-title">Authorized Signatory</div>
            <div class="full-final-note">For, <Strong>  {{ $companyName }} </Strong></div>

            <table class="full-final-sign-table" style="margin-top: 12px;">
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
