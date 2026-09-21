@php
    $employment = $currentEmployment ?? null;
    $employeeName = $selectedEmployee?->full_name ?? '--';
    $designation = $employment?->designation?->name ?? $selectedEmployee?->current_role?->name ?? '--';
    $department = $employment?->department?->name ?? '--';
    $employeeCode = $selectedEmployee?->employee_code ?? '--';
    $shiftTime = $employment?->shiftDetail?->working_hour ?? '--';
    $relievingDate = $selectedEmployee?->resign_date
        ? \Carbon\Carbon::parse($selectedEmployee->resign_date)->format('d/m/Y')
        : ($employment?->end_date ? \Carbon\Carbon::parse($employment->end_date)->format('d/m/Y') : '--');
    $companyName = $selectedEmployee?->company?->company_name ?? 'OceanHR';
    $headerImage = $headerImage 
        ?? ($selectedEmployee?->company?->order_header_logo_url ?? null)
        ?? ($selectedEmployee?->company?->company_logo_url ?? null)
        ?? asset('software/img/logo.png');
    $watermarkImage = $watermarkImage 
        ?? ($selectedEmployee?->company?->watermark_logo_url ?? null)
        ?? ($selectedEmployee?->company?->company_favicon_url ?? null)
        ?? ($selectedEmployee?->company?->company_logo_url ?? null)
        ?? asset('software/img/ring.png');
    $today = \Carbon\Carbon::now()->format('d/m/Y');
@endphp

<style>
    .relieving-letter {
        position: relative;
        width: 210mm;
        min-height: 297mm;
        margin: 0 auto;
        border: 1px solid #111827;
        box-sizing: border-box;
        padding: 4mm 6.5mm 6mm;
        color: #111827;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 11.6px;
        line-height: 1.38;
        background: #fff;
        overflow: hidden;
    }

    .relieving-letter__watermark {
        position: absolute;
        left: 50%;
        top: 55%;
        transform: translate(-50%, -50%);
        width: min(145mm, 82%);
        opacity: 0.11;
        pointer-events: none;
        z-index: 0;
    }

    .relieving-letter__header {
        position: relative;
        z-index: 1;
        margin-bottom: 2px;
    }

    .relieving-letter__header img {
        display: block;
        width: 100%;
        height: auto;
        opacity: 0.62;
        filter: grayscale(0.08) contrast(0.96);
    }

    .relieving-letter__divider {
        border-top: 1px solid #111827;
        margin: 4px 0 10px;
    }

    .relieving-letter__title {
        text-align: center;
        font-size: 20px;
        font-weight: 700;
        margin: 24px 0 24px;
        letter-spacing: 0.3px;
    }

    .relieving-letter__date {
        text-align: right;
        font-size: 12.4px;
        margin-bottom: 14px;
        font-weight: 700;
    }

    .relieving-letter__details {
        margin-bottom: 18px;
        font-size: 13.8px;
        line-height: 1.55;
    }

    .relieving-letter__detail-row {
        display: flex;
        gap: 10px;
        margin-bottom: 6px;
    }

    .relieving-letter__detail-label {
        width: 32mm;
        font-weight: 700;
        white-space: nowrap;
    }

    .relieving-letter__detail-value {
        flex: 1;
        padding-left: 2px;
        word-break: break-word;
    }

    .relieving-letter__subject {
        text-align: center;
        font-size: 18px;
        margin: 16px 0 80px;
    }

    .relieving-letter__subject strong {
        font-weight: 700;
    }

    .relieving-letter__body {
        position: relative;
        z-index: 1;
        padding: 0 2px;
    }

    .relieving-letter__para {
        margin: 0 0 12px;
        text-align: justify;
        line-height: 1.5;
        font-size: 15.8px;
    }

    .relieving-letter__closing {
        margin-top: 18px;
        font-weight: 700;
        font-size: 14.2px;
    }

    .relieving-letter__sign-block {
        margin-top: 30px;
    }

    .relieving-letter__sign-line {
        width: 48%;
        border-bottom: 1px solid #111827;
        height: 20px;
        margin-top: 8px;
        margin-bottom: 5px;
    }

    .relieving-letter__sign-label {
        font-weight: 700;
        font-size: 12.4px;
    }

    .relieving-letter__stamp {
        position: absolute;
        right: 10mm;
        bottom: 10mm;
        width: 38mm;
        border: 1px solid #3457d5;
        color: #3457d5;
        font-size: 8px;
        line-height: 1.1;
        padding: 2px 4px;
        text-align: center;
        background: rgba(255, 255, 255, 0.92);
        z-index: 2;
    }

    .relieving-letter__stamp strong {
        display: block;
        font-size: 10px;
        letter-spacing: 0.4px;
        margin-bottom: 1px;
    }

    @media print {
        @page {
            size: A4;
            margin: 0;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            background: #fff;
        }

        .relieving-letter {
            width: calc(100% - 10mm);
            max-width: calc(100% - 10mm);
            min-height: auto;
            margin: 4mm auto 0;
            padding: 4mm 6mm 6mm;
            box-shadow: none;
        }
    }
</style>

<div class="relieving-letter">
    <img class="relieving-letter__watermark" src="{{ $watermarkImage }}" alt="Watermark">

    <div class="relieving-letter__header">
        <img src="{{ $headerImage }}" alt="Header">
    </div>

    <div class="relieving-letter__divider"></div>

    <div class="relieving-letter__title">RELIEVING LETTER</div>

    <div class="relieving-letter__date">Date: {{ $today }}</div>

    <div class="relieving-letter__body">
        <div class="relieving-letter__details">
            <div class="relieving-letter__detail-row">
                <div class="relieving-letter__detail-label">Employee Name:</div>
                <div class="relieving-letter__detail-value">{{ $employeeName }}</div>
            </div>
            <div class="relieving-letter__detail-row">
                <div class="relieving-letter__detail-label">Designation:</div>
                <div class="relieving-letter__detail-value">{{ $designation }}</div>
            </div>
            <div class="relieving-letter__detail-row">
                <div class="relieving-letter__detail-label">Department:</div>
                <div class="relieving-letter__detail-value">{{ $department }}</div>
            </div>
        </div>

        <div class="relieving-letter__subject">
            <strong>Subject:</strong> Relieving from Services
        </div>

        <p class="relieving-letter__para">
            Dear <strong>Mr./Ms. {{ $employeeName }}</strong> ,
        </p>

        <p class="relieving-letter__para">
            This is to inform you that your resignation has been accepted by the management of {{ $companyName }}
             and you are hereby relieved from your duties effective from the close of working hours on <strong>{{ $today }}</strong>.
        </p>

        <p class="relieving-letter__para">
            We confirm that you have completed the necessary handover of your responsibilities and company assets as per company policy.
        </p>

        <p class="relieving-letter__para">
            We appreciate your services during your tenure with the organization and wish you success in your future endeavors.
        </p>

        <div class="relieving-letter__closing">
            For, {{ $companyName }}
        </div>

        <div class="relieving-letter__sign-block">
            <div class="relieving-letter__sign-line"></div>
            <div class="relieving-letter__sign-label">Authorized Signatory</div>
        </div>
    </div>

</div>
