@php
    $employment = $currentEmployment ?? null;
    $employeeName = $selectedEmployee?->full_name ?? '--';
    $designation = trim((string) ($employment?->designation?->name ?? ''));
    $department = $employment?->department?->name ?? '--';
    $parentName = $selectedEmployee?->parentEmployee?->full_name ?? '--';
    $parentDesignation = trim((string) ($selectedEmployee?->parentEmployee?->employmentDetail?->designation?->name ?? ''));
    $joiningDate = $employment?->date_of_joining
        ? \Carbon\Carbon::parse($employment->date_of_joining)->format('d/m/Y')
        : '--';
    $mobile = $selectedEmployee?->contact_number ?? '--';
    $email = $selectedEmployee?->email ?? '--';
    $shiftStart = $employment?->shiftDetail?->punch_in_minimum ?? null;
    $shiftEnd = $employment?->shiftDetail?->punch_out ?? null;
    $shiftTime = ($shiftStart && $shiftEnd)
        ? \Carbon\Carbon::parse($shiftStart)->format('h:i A') . ' to ' . \Carbon\Carbon::parse($shiftEnd)->format('h:i A')
        : ($employment?->shiftDetail?->working_hour ?? '--');
    $gender = strtolower(trim((string) ($selectedEmployee?->gender ?? '')));
    $salutation = $gender === 'female' ? 'Mrs.' : 'Mr.';
    $companyName = $selectedEmployee?->company?->company_name ?? 'OceanHR';
    $today = \Carbon\Carbon::now()->format('d/m/Y');
    $headerImage = $headerImage 
        ?? ($selectedEmployee?->company?->order_header_logo_url ?? null)
        ?? ($selectedEmployee?->company?->company_logo_url ?? null)
        ?? asset('software/img/logo.png');
    $watermarkImage = $watermarkImage ?? asset('software/img/Ocean_HR.png');
@endphp

<style>
    .appointment-doc {
        color: #111827;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 11.4px;
        line-height: 1.38;
        background: #fff;
    }

    .appointment-page {
        width: 210mm;
        min-height: 288mm;
        margin: 0 auto 8mm;
        background: #fff;
        border: 1px solid #111827;
        position: relative;
        overflow: hidden;
        padding: 5mm 7mm 6mm;
        box-sizing: border-box;
        page-break-after: always;
        break-after: page;
        page-break-inside: avoid;
        break-inside: avoid;
    }

    .appointment-page--plain {
        padding-top: 4.5mm;
    }

    .appointment-page--cover {
        padding-top: 4mm;
        padding-bottom: 4.5mm;
    }

    .appointment-page:last-child {
        page-break-after: auto;
        break-after: auto;
    }

    .appointment-watermark {
        position: absolute;
        left: 50%;
        top: 53%;
        transform: translate(-50%, -50%);
        width: min(145mm, 82%);
        opacity: 0.11;
        pointer-events: none;
        z-index: 0;
    }

    .appointment-header {
        position: relative;
        z-index: 1;
        margin-bottom: 3px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
    }

    .appointment-header img {
        display: block;
        max-height: 55px;
        max-width: 260px;
        width: auto;
        height: auto;
        object-fit: contain;
        margin: 0 auto;
    }

    .appointment-divider {
        border-top: 1px solid #111827;
        margin: 4px 0 5px;
    }

    .appointment-title {
        text-align: center;
        font-size: 16px;
        font-weight: 700;
        margin: 8px 0 8px;
        text-transform: uppercase;
    }

    .appointment-subject {
        text-align: center;
        font-size: 13px;
        font-weight: 700;
        margin: 9px 0 10px;
    }

    .appointment-intro-top {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 3px;
        margin-top: 25px;
        align-items: flex-start;
    }

    .appointment-to {
        width: 70%;
        font-size: 14.8px;
    }

    .appointment-date {
        width: 30%;
        text-align: right;
        padding-top: 0;
        font-size: 14.8px;
        font-weight: 700;
        white-space: nowrap;
    }

    .appointment-recipient {
        width: 100%;
        margin-top: 1px;
        margin-bottom: 14px;
        font-size: 14.8px;
        line-height: 1.28;
    }

    .appointment-recipient-line {
        margin: 1px 0;
    }



    .appointment-contact-row {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        gap: 16px;
        margin: 14px 0 16px;
        font-size: 14.8px;
    }

    .appointment-contact-item {
        display: flex;
        align-items: baseline;
        gap: 4px;
        min-width: 0;
        flex: 1 1 0;
    }

    .appointment-contact-item--right {
        justify-content: flex-end;
    }

    .appointment-contact-label {
        font-weight: 700;
        white-space: nowrap;
    }

    .appointment-contact-value {
        font-weight: 400;
        white-space: nowrap;
    }

    .appointment-paragraph {
        margin: 35px 0 7px;
        text-align: justify;
        line-height: 1.42;
        font-size: 14.8px;
    }

    .appointment-section {
        margin: 6px 0 4px;
    }

    .appointment-section .heading {
        font-weight: 700;
        margin-bottom: 2px;
        font-size: 12px;
    }

    .appointment-section .text {
        text-align: justify;
        line-height: 1.38;
    }

    .appointment-note {
        margin: 4px 0 0;
        font-size: 13.8px;
    }

    .appointment-signatures {
        margin-top: 34px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 36px;
        align-items: start;
    }

    .appointment-sign-block {
        font-size: 14.8px;
    }

    .appointment-sign-block--left,
    .appointment-sign-block--right {
        width: 100%;
    }

    .appointment-sign-line {
        border-bottom: 1px solid #111827;
        height: 22px;
        margin-top: 0;
        margin-bottom: 5px;
        width: 100%;
    }

    .appointment-sign-block strong {
        display: block;
        margin-bottom: 2px;
    }

    .appointment-page-number {
        position: absolute;
        right: 8mm;
        bottom: 6mm;
        font-size: 9px;
        color: #555;
        z-index: 1;
    }

    .appointment-stamp {
        width: 36mm;
        border: 1px solid #9ca3af;
        color: #6b7280;
        font-size: 8px;
        line-height: 1.1;
        padding: 2px 4px;
        text-align: center;
        background: rgba(255, 255, 255, 0.92);
    }

    .appointment-stamp strong {
        display: block;
        font-size: 10px;
        letter-spacing: 0.4px;
        color: #6b7280;
        margin-bottom: 1px;
    }

    .appointment-stamp--top {
        position: absolute;
        right: 11mm;
        top: 34mm;
        z-index: 1;
    }

    .appointment-stamp--bottom {
        position: absolute;
        right: 11mm;
        bottom: 14mm;
        z-index: 1;
    }

    .appointment-page .section-block {
        margin-bottom: 50px;
    }

    .appointment-page .section-block .heading {
        font-weight: 700;
        margin-bottom: 2px;
        font-size: 17px;
    }

    .appointment-page .section-block .text {
        text-align: justify;
        font-size: 16px;
    }

    .appointment-clause-list {
        margin: 0;
        padding-left: 16px;
    }

    .appointment-clause-list li {
        margin: 2px 0;
    }

    .appointment-clause-list li::before {
        content: "7." counter(item) " ";
        position: absolute;
        left: -30px;
        font-weight: bold;
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

        .appointment-page {
            width: calc(100% - 10mm);
            max-width: calc(100% - 10mm);
            margin: 4mm auto 0;
            padding: 5mm 6mm 6mm;
            box-shadow: none;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .appointment-page--plain {
            padding-top: 4mm;
        }

        .appointment-page--cover {
            padding-top: 4mm;
            padding-bottom: 4.5mm;
        }
    }
</style>

<div class="appointment-doc">
    <div class="appointment-page appointment-page--cover">
        <img class="appointment-watermark" src="{{ $watermarkImage }}" alt="Watermark">

        <div class="appointment-header">
            <img src="{{ $headerImage }}" alt="Header">
        </div>

        <div class="appointment-divider"></div>
        <div class="appointment-intro-top">
            <div class="appointment-to">To,</div>
            <div class="appointment-date">
                <strong>Date:</strong>{{ $today }}
            </div>
        </div>


        <div class="appointment-recipient">
            <div class="appointment-recipient-line"><strong>Mr.</strong> {{ $employeeName }}</div>
            <div class="appointment-recipient-line">{{ $designation }}</div>
            <div class="appointment-recipient-line">{{ $department }}</div>
            <div class="appointment-recipient-line">{{ $companyName }}</div>
        </div>

        <div class="appointment-contact-row">
            <div class="appointment-contact-item">
                <span class="appointment-contact-label">Mobile:</span>
                <span class="appointment-contact-value">{{ $mobile }}</span>
            </div>
            <div class="appointment-contact-item appointment-contact-item--right">
                <span class="appointment-contact-label">E Mail:</span>
                <span class="appointment-contact-value">{{ $email }}</span>
            </div>
        </div>

        <div class="appointment-title">Subject : <u>Appointment Letter </u></div>


        <p class="appointment-paragraph">
            Dear <strong>{{ $salutation }} {{ $employeeName }},</strong><br><br>
            We are pleased to offer you, the position of <strong>{{ $designation }}</strong> with <strong>{{ $companyName }}</strong>
            on the following terms and conditions:
        </p>

        <div class="section-block">
            <div class="heading">1. Commencement of Employment</div>
            <div class="text">
                Your employment will be effective, as of <strong>{{ $joiningDate }}</strong>
            </div>
        </div>

        <div class="section-block">
            <div class="heading">2. Job Title</div>
            <div class="text">
                Your job title will be
                @if ($designation !== '')
                    <strong>{{ $designation }}</strong>
                @endif
                and you will report to
                <strong>{{ $parentName }}@if ($parentDesignation !== '') - {{ $parentDesignation }}@endif - {{ $companyName }}</strong>
            </div>
        </div>

        <div class="section-block">
            <div class="heading">3. Salary</div>
            <div class="text">
                Your salary and other benefits will be as set out in <strong>Schedule I</strong>, hereto.
            </div>
        </div>

        <div class="section-block">
            <div class="heading">4. Place of Posting</div>
            <div class="text">
                You will be posted at <strong>Rajkot, Gujarat</strong>. You may however be
                required to work at any place of business at where Company has, or may later acquire.
            </div>
        </div>

        <div class="section-block">
            <div class="heading">5. Probation Period</div>
            <div class="text">
                Your probation period will be 3 month and in this probation period either the
                employer or employee can <strong>end the employment without notice.</strong>
            </div>
        </div>




    </div>

    <div class="appointment-page appointment-page--plain">
        <img class="appointment-watermark" src="{{ $watermarkImage }}" alt="Watermark">

        <div class="section-block">
            <div class="heading">Extension option</div>
            <div class="text">
                If performance needs improvement, the company may <strong>extend the probation period</strong>.
            </div>
        </div>

        <div class="section-block">
            <div class="heading">6. Working Hours</div>
            <div class="text">
                You will be required to work for such hours as necessary for the proper discharge of your duties
                to the Company. The normal working hours are from <strong>{{ $shiftTime }}</strong>.
            </div>
        </div>

        <div class="section-block">
            <div class="heading">7. Leave / Holidays</div>
            <div class="text">
                <ol class="appointment-clause-list">
                    <li>Week off will be on Sunday.</li>
                    <li>You are entitled to <strong>AS PER LEAVE POLICY</strong> working days of paid Leave.</li>
                    <li>The Company shall notify a list of declared holidays in the beginning of each year.</li>
                    <li>For Extra leaves Candidate has to Inform Company before taking leave.</li>
                </ol>
            </div>
            <div class="appointment-note">
                <strong>NOTE: <u>Candidate Must have to attain calls and responses from Clientele or Company Side
                on any kind of Leaves. </u></strong>
            </div>
        </div>

        <div class="section-block">
            <div class="heading">8. Nature of Duties</div>
            <div class="text">
                You will perform to the best of your ability all the duties as are inherent in your post and such
                additional duties as the company may call upon you to perform, from time to time.
            </div>
        </div>

        <div class="section-block">
            <div class="heading">9. Company Property</div>
            <div class="text">
                You will always maintain in good condition Company property, which may be entrusted to you for
                official use during the course of your employment and shall return all such property to the Company
                prior to relinquishment of your charge, failing which the cost of the same will be recovered from you
                by the Company.
            </div>
        </div>

        <div class="section-block">
            <div class="heading">10. Borrowing / Accepting Gifts</div>
            <div class="text">
                You will not borrow or accept any money, gift, reward or compensation for your personal gains from or
                otherwise place yourself under pecuniary obligation to any person/client with whom you may be having
                official dealings.
            </div>
        </div>

        <div class="section-block">
            <div class="heading">11. Termination</div>
            <div class="text">
                <ol class="appointment-clause-list">
                    <li>If performance is not satisfactory, the company can terminate the job without notice period.
                    </li>
                    <li>The company can terminate employment without notice period for misconduct or damage to the
                        company.</li>
                    <li>If the company employe does not follow the given target or visit schedule, the company can
                        terminate the job without any notice.</li>
                    <li>An employee can terminate employment by giving a notice period of 1 month.</li>
                </ol>
            </div>
        </div>


    </div>

    <div class="appointment-page appointment-page--plain">

        <img class="appointment-watermark" src="{{ $watermarkImage }}" alt="Watermark"
            style="position: absolute; top: 50%; left: 50%; width: 420px; opacity: 0.08; transform: translate(-50%, -50%); z-index: 0;">

        <!-- 11.5 -->
        <div style="position: relative; z-index: 1; display: flex; align-items: flex-start; margin-bottom: 18px;">
            <div style="width: 55px; font-weight: 700; font-size: 18px;">
                11.5
            </div>

            <div style="flex: 1; text-align: justify; font-size: 15px;">
                On the termination of your employment for whatever reason, you will return to the
                Company all property; documents and paper, both original and copies thereof, including any
                samples, literature, contracts, records, lists, drawings, blueprints, letters, notes, data
                and the like; and Confidential Information, in your possession or under your control
                relating to your employment or to clients’ business affairs.
            </div>
        </div>

        <!-- 11.6 -->
        <div style="position: relative; z-index: 1; display: flex; align-items: flex-start; margin-bottom: 24px;">
            <div style="width: 55px; font-weight: 700; font-size: 18px;">
                11.6
            </div>

            <div style="flex: 1; text-align: justify; font-size: 15px;">
                The notice period will be valid in circumstances where the employee and the company
                agree. Otherwise, the notice period will not be valid in any other work.
            </div>
        </div>

        <!-- 12 Heading -->
        <div style="position: relative; z-index: 1; margin-bottom: 18px;">
            <div style="font-size: 22px; font-weight: 700; margin-bottom: 14px;">
                12. Confidential Information
            </div>

            <!-- 12.1 -->
            <div style="display: flex; align-items: flex-start; margin-bottom: 14px;">
                <div style="width: 55px; font-weight: 700; font-size: 18px;">
                    12.1
                </div>

                <div style="flex: 1; text-align: justify; font-size: 15px;">
                    During your employment with the Company you will devote your whole time,
                    attention and skill to the best of your ability for its business. You shall not,
                    directly or indirectly, engage or associate yourself with, be connected with,
                    concerned, employed or engaged in any other business or activities or any other
                    post or work part time or pursue any course of study whatsoever, without the
                    prior permission of the Company.
                </div>
            </div>

            <!-- 12.2 -->
            <div style="display: flex; align-items: flex-start; margin-bottom: 14px;">
                <div style="width: 55px; font-weight: 700; font-size: 18px;">
                    12.2
                </div>

                <div style="flex: 1; text-align: justify; font-size: 15px;">
                    You must always maintain the highest degree of confidentiality and keep as
                    confidential the records, documents and other Confidential Information relating
                    to the business of the Company which may be known to you or confided in you by
                    any means and you will use such records, documents and information only in a duly
                    authorized manner in the interest of the Company. For the purposes of this clause
                    ‘Confidential Information’ means information about the Company’s business and that
                    of its customers which is not available to the general public and which may be
                    learnt by you in the course of your employment. This includes, but is not limited to,
                    information relating to the organization, its customer lists, employment policies,
                    personnel, and information about the Company’s products, processes including ideas,
                    concepts, projections, technology, manuals, drawing, designs, specifications, and all
                    papers, resumes, records and other documents containing such Confidential Information.
                </div>
            </div>

            <!-- 12.3 -->
            <div style="display: flex; align-items: flex-start; margin-bottom: 14px;">
                <div style="width: 55px; font-weight: 700; font-size: 18px;">
                    12.3
                </div>

                <div style="flex: 1; text-align: justify; font-size: 15px;">
                    At no time, will you remove any Confidential Information from the office without
                    permission.
                </div>
            </div>

            <!-- 12.4 -->
            <div style="display: flex; align-items: flex-start; margin-bottom: 14px;">
                <div style="width: 55px; font-weight: 700; font-size: 18px;">
                    12.4
                </div>

                <div style="flex: 1; text-align: justify; font-size: 15px;">
                    Your duty to safeguard and not disclose Confidential Information will survive the
                    expiration or termination of this Agreement and/or your employment with the Company.
                </div>
            </div>

            <!-- 12.5 -->
            <div style="display: flex; align-items: flex-start; margin-bottom: 24px;">
                <div style="width: 55px; font-weight: 700; font-size: 18px;">
                    12.5
                </div>

                <div style="flex: 1; text-align: justify; font-size: 15px;">
                    Breach of the conditions of this clause will render you liable to summary dismissal
                    under clause above in addition to any other remedy the Company may have against
                    you in law.
                </div>
            </div>
        </div>

        <!-- 13 -->
        <div style="position: relative; z-index: 1; margin-bottom: 26px;">

            <div style="font-size: 22px; font-weight: 700; margin-bottom: 14px;">
                13. Notices
            </div>

            <div style="text-align: justify; font-size: 15px;">
                Notices may be given by you to the Company at its registered office address.
                Notices may be given by the Company to you at the address intimated by you
                in the official records.
            </div>
        </div>

        <!-- 14 -->
        <div style="position: relative; z-index: 1;">

            <div style="font-size: 22px; font-weight: 700; margin-bottom: 14px;">
                14. Applicability of Company Policy
            </div>

            <div style="text-align: justify; font-size: 15px;">
                The Company shall be entitled to make policy declarations from time to time
                pertaining to matters like leave entitlement, maternity leave, employees’
                benefits, working hours, transfer policies, etc., and may alter the same from
                time to time at its sole discretion. All such policy decisions of the Company
                shall be binding on you and shall override this Agreement to that extent.
            </div>
        </div>

    </div>

    <div class="appointment-page appointment-page--plain">
        <img class="appointment-watermark" src="{{ $watermarkImage }}" alt="Watermark">

        <div class="section-block">
            <div class="heading">15. Governing Law / Jurisdiction</div>
            <div class="text">
                Your employment with the Company is subject to Indian laws. All disputes shall be subject to the
                jurisdiction of Rajkot, Gujarat only.
            </div>
        </div>

        <div class="section-block">
            <div class="heading">16. Acceptance of our offer</div>
            <div class="text">
                Please confirm your acceptance of this Contract of Employment by signing and returning the duplicate
                copy.
            </div><br>
            <div class="text">
            We welcome you, and look forward to receiving your acceptance and to working with you</div>
        </div>



        <div class="appointment-signatures">
            <div class="appointment-sign-block appointment-sign-block--left">
                <div class="appointment-sign-line"></div>
                <strong>Management</strong>
                <div><strong>{{ $companyName }}</strong></div>
                <div><strong>Rajkot, Gujarat</strong></div>
            </div>
            <div class="appointment-sign-block appointment-sign-block--right" style="text-align:right;">
                <div class="appointment-sign-line"></div>
                <strong style="display:block; text-align:right;">Employee Signature</strong>
            </div>
        </div>


    </div>
</div>
