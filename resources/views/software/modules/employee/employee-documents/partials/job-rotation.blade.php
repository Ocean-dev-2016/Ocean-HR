@php
    $employment = $currentEmployment ?? null;
    $employeeName = $selectedEmployee?->full_name ?? '--';
    $processName = $employment?->process?->name
        ?? $employment?->department?->name
        ?? $selectedEmployee?->current_role?->name
        ?? '--';
    $joiningDate = $employment?->date_of_joining
        ? \Carbon\Carbon::parse($employment->date_of_joining)->format('d/m/Y')
        : \Carbon\Carbon::now()->format('d/m/Y');
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
    .job-rotation-letter {
        position: relative;
        width: 210mm;
        min-height: 297mm;
        margin: 0 auto;
        border: 1px solid #111827;
        box-sizing: border-box;
        padding: 4mm 6.5mm 6mm;
        color: #111827;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 11.5px;
        line-height: 1.38;
        background: #fff;
        overflow: hidden;
    }

    .job-rotation-letter__content {
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
        min-height: calc(297mm - 16mm);
    }

    .job-rotation-letter__watermark {
        position: absolute;
        left: 50%;
        top: 56%;
        transform: translate(-50%, -50%);
        width: min(138mm, 78%);
        opacity: 0.09;
        pointer-events: none;
        z-index: 0;
    }

    .job-rotation-letter__header {
        position: relative;
        z-index: 1;
        margin-bottom: 2px;
    }

    .job-rotation-letter__header img {
        display: block;
        width: 100%;
        height: auto;
        opacity: 0.62;
        filter: grayscale(0.08) contrast(0.96);
    }

    .job-rotation-letter__divider {
        border-top: 1px solid #111827;
        margin: 4px 0 10px;
    }

    .job-rotation-letter__date {
        text-align: right;
        font-size: 11.2px;
        margin-bottom: 8px;
        font-weight: 700;
    }

    .job-rotation-letter__subject {
        font-size: 12.4px;
        line-height: 1.42;
        margin-bottom: 8px;
    }

    .job-rotation-letter__subject strong {
        font-weight: 700;
        font-size: 13.6px;
    }

    .job-rotation-letter__body {
        position: relative;
        z-index: 1;
        padding: 0 2px;
    }

    .job-rotation-letter__para {
        margin: 0 0 12px;
        text-align: justify;
        line-height: 1.44;
        font-size: 12.3px;
    }

    .job-rotation-letter__signature-note {
        margin-top: 10px;
        font-weight: 700;
    }

    .job-rotation-letter__bottom-line {
        margin-top: 12px;
        border-top: 2px solid #111827;
        border-bottom: 2px solid #111827;
        height: 4px;
    }

    .job-rotation-letter__acceptance {
        margin-top: 10px;
        font-weight: 700;
    }

    .job-rotation-letter__acceptance-para {
        margin: 6px 0 10px;
        font-size: 11.2px;
        line-height: 1.42;
    }

    .job-rotation-letter__sign {
        margin-top: 12px;
        display: flex;
        justify-content: space-between;
        align-items: end;
        gap: 24px;
    }

    .job-rotation-letter__sign-left {
        width: 52%;
    }

    .job-rotation-letter__sign-right {
        width: 38%;
        text-align: center;
    }

    .job-rotation-letter__sign-row {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .job-rotation-letter__sign-line {
        border-bottom: 1px solid #111827;
        height: 18px;
        margin: 0;
        flex: 1 1 auto;
    }

    .job-rotation-letter__sign-label {
        font-weight: 700;
        white-space: nowrap;
    }

    .job-rotation-letter__stamp {
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

    .job-rotation-letter__stamp strong {
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
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .job-rotation-letter {
            width: calc(100% - 10mm);
            max-width: calc(100% - 10mm);
            min-height: auto;
            margin: 4mm auto 0;
            padding: 4mm 6mm 6mm;
            box-shadow: none;
        }

        .job-rotation-letter__content {
            min-height: auto;
        }

        .job-rotation-letter__body {
            position: relative;
            z-index: 1;
            padding: 0 1px;
        }
    }
</style>

<div class="job-rotation-letter">
    <img class="job-rotation-letter__watermark" src="{{ $watermarkImage }}" alt="Watermark">

    <div class="job-rotation-letter__content">
        <div class="job-rotation-letter__header">
            <img src="{{ $headerImage }}" alt="Header">
        </div>

        <div class="job-rotation-letter__divider"></div>

        <div class="job-rotation-letter__date">Date: {{ $today }}</div>

        <div class="job-rotation-letter__body">
            <div class="job-rotation-letter__subject">
                <strong>To,</strong><br><br>
                <strong>Mr./Ms. {{ $employeeName }}</strong><br><br>
                <strong>Subject:</strong> Internal Job Rotation (IJR) - Transition from {{ $processName }} Process to {{ $processName }} Process effective {{ $joiningDate }}
            </div>

            <p class="job-rotation-letter__para">
                Dear <strong>Mr./Ms. {{ $employeeName }}</strong>,
            </p>

            <p class="job-rotation-letter__para">
                As per management decision, you are hereby transitioned from {{ $processName }} Process to {{ $processName }} Process effective {{ $joiningDate }}.
            </p>

            <p class="job-rotation-letter__para">
                Your base location will be Ahmedabad, Gujarat, India.
            </p>

            <p class="job-rotation-letter__para">
                You will be responsible for handling sales activities, customer development, business expansion and other assigned responsibilities as per the approved KRA and territory plan.
            </p>

            <p class="job-rotation-letter__para">
                All company policies, reporting structures and operational guidelines applicable to the Sales Department shall be applicable to you with immediate effect.
            </p>

            <p class="job-rotation-letter__para">
                We wish you success in your new role and responsibilities.
            </p>

            <p class="job-rotation-letter__para" style="margin-bottom: 22px;">
                Regards,
            </p>

            <p class="job-rotation-letter__para">
               <strong>  For,  {{ $companyName }}</strong>
            </p>

            <div class="job-rotation-letter__signature-note">Authorized Signatory</div>

            <div class="job-rotation-letter__bottom-line"></div>

            <div class="job-rotation-letter__acceptance">Employee Acceptance</div>

            <p class="job-rotation-letter__acceptance-para">
                I, {{ $employeeName }}, hereby acknowledge and accept the above transition and responsibilities.
            </p>

            <div class="job-rotation-letter__sign">
                <div class="job-rotation-letter__sign-left">
                    <div class="job-rotation-letter__sign-row">
                        <span class="job-rotation-letter__sign-label">Employee Signature:</span>
                        <span class="job-rotation-letter__sign-line" style="max-width: 55%;"></span>
                    </div>

                    <div class="job-rotation-letter__sign-row" style="margin-top: 10px;">
                        <span class="job-rotation-letter__sign-label">Date:</span>
                        <span class="job-rotation-letter__sign-line" style="max-width: 42%;"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
