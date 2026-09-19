@php
    $employment = $currentEmployment ?? null;
    $employeeName = $selectedEmployee?->full_name ?? '--';
    $designation = $employment?->designation?->name ?? $selectedEmployee?->current_role?->name ?? '--';
    $department = $employment?->department?->name ?? '--';
    $joiningDate = $employment?->date_of_joining
        ? \Carbon\Carbon::parse($employment->date_of_joining)->format('d/m/Y')
        : '--';
    $relievingDate = $employment?->end_date
        ? \Carbon\Carbon::parse($employment->end_date)->format('d/m/Y')
        : \Carbon\Carbon::now()->format('d/m/Y');
    $companyName = $selectedEmployee?->company?->company_name ?? 'OceanHR';
    $headerImage = $headerImage 
        ?? ($selectedEmployee?->company?->order_header_logo_url ?? null)
        ?? ($selectedEmployee?->company?->company_logo_url ?? null)
        ?? asset('software/img/logo.png');
    $watermarkImage = $watermarkImage ?? asset('software/img/Ocean_HR.png');
@endphp

<style>
    .experience-letter {
        position: relative;
        z-index: 1;
        min-height: 200mm;
        padding: 0;
        color: #111827;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 13px;
        line-height: 1.45;
    }

    .experience-letter__title {
        text-align: center;
        font-size: 18px;
        font-weight: 700;
        margin: 24px 0 24px;
        letter-spacing: 0;
    }

    .experience-letter__subtitle {
        text-align: center;
        font-size: 14px;
        font-weight: 700;
        margin-bottom: 22px;
    }

    .experience-letter__body {
        padding: 0 4px;
    }

    .experience-letter__para {
        margin: 0 0 16px;
        text-align: justify;
        line-height: 1.55;
    }

    .experience-letter__closing {
        margin-top: 18px;
        font-weight: 700;
    }

    .experience-letter__sign {
        margin-top: 34px;
        display: block;
    }

    .experience-letter__sign-left {
        width: 100%;
    }

    .experience-letter__sign-line {
        width: 190px;
        border-bottom: 1px solid #111827;
        height: 18px;
        margin-left: 8px;
        flex: 0 0 auto;
    }

    .experience-letter__sign-label {
        font-weight: 700;
    }

    .experience-letter__hr {
        margin-top: 8px;
        font-weight: 700;
    }
</style>

<div class="experience-letter">
    
    <div class="experience-letter__subtitle">TO WHOMSOEVER IT MAY CONCERN</div>

    <div class="experience-letter__body">
        <p class="experience-letter__para">
            This is to certify that <strong>Mr. {{ $employeeName }}</strong> was employed with <strong>{{ $companyName }}</strong>
            from <strong>{{ $joiningDate }}</strong> to <strong>{{ $relievingDate }}</strong> as <strong>{{ $designation }}</strong>
            in <strong>{{ $department }}</strong> department.
        </p>

        <p class="experience-letter__para">
            During his/her tenure with us, we found him/her to be sincere, hardworking and professional in their conduct.
        </p>

        <p class="experience-letter__para">
            We wish him/her success in their future assignments.
        </p>

        <div class="experience-letter__closing">For, {{ $companyName }}</div>

        <div class="experience-letter__sign">
            <div class="experience-letter__sign-left">
                <div style="display:flex; align-items:flex-end; gap:0;">
                    <div class="experience-letter__sign-label">Authorized Signatory</div>
                    <div class="experience-letter__sign-line"></div>
                </div>
                <div class="experience-letter__hr">HR Department</div>
            </div>
        </div>
    </div>
</div>
