@php
    $employment = $currentEmployment ?? null;
    $designation = $employment?->designation?->name ?? $selectedEmployee?->current_role?->name ?? '--';
    $department = $employment?->department?->name ?? '--';
    $joiningDate = $employment?->date_of_joining
        ? \Carbon\Carbon::parse($employment->date_of_joining)->format('d/m/Y')
        : '--';
    $employeeName = $selectedEmployee?->full_name ?? '--';
    $headerImage = $headerImage 
        ?? ($selectedEmployee?->company?->order_header_logo_url ?? null)
        ?? ($selectedEmployee?->company?->company_logo_url ?? null)
        ?? asset('software/img/logo.png');
    $watermarkImage = $watermarkImage ?? asset('software/img/Ocean_HR.png');
    $referenceNo = 'ASIPL_Offer_26-27_xxxxxxxx';
    $today = \Carbon\Carbon::now()->format('d-m-Y');
@endphp

<style>
    .offer-letter {
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
        overflow: hidden;
        background: #fff;
    }

    .offer-letter__watermark {
        position: absolute;
        left: 50%;
        top: 55%;
        transform: translate(-50%, -50%);
        width: min(145mm, 82%);
        opacity: 0.11;
        pointer-events: none;
        z-index: 0;
    }

    .offer-letter__header {
        position: relative;
        z-index: 1;
        margin-bottom: 2px;
    }

    .offer-letter__header img {
        display: block;
        width: 100%;
        height: auto;
        opacity: 0.62;
        filter: grayscale(0.08) contrast(0.96);
    }

    .offer-letter__divider {
        border-top: 1px solid #111827;
        margin: 4px 0 10px;
    }

    .offer-letter__meta {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        font-weight: 700;
        margin-bottom: 14px;
        font-size: 11.4px;
    }

    .offer-letter__title {
        text-align: center;
        font-size: 17px;
        font-weight: 700;
        margin: 8px 0 16px;
        text-decoration: underline;
        letter-spacing: 0.3px;
    }

    .offer-letter__body {
        position: relative;
        z-index: 1;
        padding: 0 2px;
    }

    .offer-letter__paragraph {
        margin: 0 0 12px;
        text-align: justify;
        line-height: 1.48;
        font-size: 11.8px;
    }

    .offer-letter__list-title {
        margin: 8px 0 6px;
        font-weight: 700;
        text-decoration: underline;
    }

    .offer-letter__list {
        margin: 0 0 10px 16px;
        padding: 0;
    }

    .offer-letter__list li {
        margin: 2px 0;
        line-height: 1.34;
    }

    .offer-letter__footer-note {
        margin: 10px 0 0;
        line-height: 1.45;
    }

    .offer-letter__sign-row {
        margin-top: 24px;
        display: flex;
        justify-content: space-between;
        align-items: end;
        gap: 24px;
    }

    .offer-letter__sign-left {
        width: 48%;
    }

    .offer-letter__sign-right {
        width: 42%;
        text-align: center;
    }

    .offer-letter__sign-line {
        border-bottom: 1px solid #111827;
        height: 22px;
        margin: 0 0 5px;
        width: 100%;
    }

    .offer-letter__sign-label {
        font-weight: 700;
    }

    .offer-letter__small {
        font-size: 11px;
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

        .offer-letter {
            page-break-inside: avoid;
            break-inside: avoid;
            width: calc(100% - 10mm);
            max-width: calc(100% - 10mm);
            min-height: calc(297mm - 10mm);
            margin: 5mm auto;
        }
    }
</style>

<div class="offer-letter">
    <img class="offer-letter__watermark" src="{{ $watermarkImage }}" alt="Watermark">

    <div class="offer-letter__header">
        <img src="{{ $headerImage }}" alt="Header">
    </div>

    <div class="offer-letter__divider"></div>

    <div class="offer-letter__meta">
        <div>Ref: {{ $referenceNo }}</div>
        <div>Date:- {{ $today }}</div>
    </div>

    <div class="offer-letter__title">OFFER-LETTER</div>

    <div class="offer-letter__body">
        <p class="offer-letter__paragraph" style="margin-bottom: 18px;">
            To,
        </p>

        <p class="offer-letter__paragraph" style="margin-bottom: 12px;">
            <strong>Mr. {{ $employeeName }}</strong>
        </p>

        <p class="offer-letter__paragraph">
            We are pleased to offer you the position of <strong>{{ $designation }}</strong> in <strong>{{ $department }}</strong>
            department at <strong>RAJKOT - GUJARAT Region.</strong>
        </p>

        <p class="offer-letter__paragraph">
            Your joining date at the company will be <strong>{{ $joiningDate }}</strong> at <strong>OceanHR, Rajkot, Gujarat.</strong>
        </p>

        <p class="offer-letter__paragraph">
            Your salary remuneration will be <strong>as discussed.</strong>
        </p>

        <p class="offer-letter__paragraph">
            You will be on probation for <strong>3 months</strong> from your joining.
        </p>

        <p class="offer-letter__paragraph">
            Company will review your performance after 3 months according to your performance, the company has all rights to continue or terminate your service.
        </p>

        <p class="offer-letter__paragraph">
            We warmly welcome you to our OceanHR family and hope it will be the beginning of a long and mutually beneficial future ahead.
        </p>

        <p class="offer-letter__paragraph">
            Kindly acknowledge the mail and acceptance of this Letter of Intent.
        </p>

        <div class="offer-letter__list-title">You are requested to submit</div>
        <ol class="offer-letter__list">
            <li>Aadhar Card</li>
            <li>PAN Card</li>
            <li>Education Certificate</li>
            <li>Bank Details</li>
            <li>5 Passport Size Photos</li>
            <li>Experience Letter of last company</li>
            <li>Salary Proof of last 3 months at the time of Joining</li>
        </ol>

        <p class="offer-letter__paragraph">
            If you wish to resign from your designation for any specific reason you are bound to submit 1-month prior
            notice to the company and submit the resignation letter to the respected authority.
        </p>

        <p class="offer-letter__paragraph">
            For any query feel free to contact undersigned.
        </p>

        <p class="offer-letter__paragraph">
            Sincerely,
        </p>

        <div class="offer-letter__sign-row">
            <div class="offer-letter__sign-left">
                <div class="offer-letter__sign-label">HR Department</div>
                <div class="offer-letter__sign-label">OceanHR</div>
            </div>
            <div class="offer-letter__sign-right">
                <div class="offer-letter__sign-line"></div>
                <div class="offer-letter__sign-label">Candidate Signature</div>
            </div>
        </div>
    </div>
</div>
