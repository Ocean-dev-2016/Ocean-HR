@php
    $employment = $currentEmployment ?? null;

    $employeeName = $selectedEmployee?->proper_name ?? $selectedEmployee?->full_name ?? '--';
    $fatherName = $selectedEmployee?->father_name ?? '--';
    $dateOfBirth = $selectedEmployee?->date_of_birth
        ? \Carbon\Carbon::parse($selectedEmployee->date_of_birth)->format('d/m/Y')
        : '--';
    $age = $selectedEmployee?->date_of_birth
        ? \Carbon\Carbon::parse($selectedEmployee->date_of_birth)->age
        : '--';
    $genderValue = strtolower(trim((string) ($selectedEmployee?->gender ?? '')));
    $maritalStatusValue = strtolower(trim((string) ($selectedEmployee?->marital_status ?? '')));
    $mobileNumber = $selectedEmployee?->contact_number ?? '--';
    $alternateContactNumber = $selectedEmployee?->other_number ?? '--';
    $emailId = $selectedEmployee?->email ?? '--';
    $aadharNumber = $selectedEmployee?->aadhar_card_number ?? '--';
    $bloodGroup = $selectedEmployee?->blood_group ?? '--';
    $currentAddress = trim((string) ($selectedEmployee?->current_address ?? '--'));
    $permanentAddress = trim((string) ($selectedEmployee?->permanent_address ?? '--'));

    $designation = $employment?->designation?->name ?? $selectedEmployee?->current_role?->name ?? '--';
    $department = $employment?->department?->name ?? '--';
    $positionApplied = $designation;

    $expectedSalary = $latestMonthlySalary?->gross_salary !== null
        ? number_format((float) $latestMonthlySalary->gross_salary, 2)
        : ($latestSalary?->ctc !== null ? number_format((float) $latestSalary->ctc, 2) : '--');

    $joiningTimeRequired = '';

    $pfNumber = $employment?->employee_pf_no ?? '--';
    $esicNumber = '--';
    $uanNumber = $employment?->uan_no ?? '--';

    $educationRows = collect($selectedEmployee?->education_experience_details ?? [])
        ->filter(fn($row) => filled($row?->degree))
        ->sortBy('id')
        ->values();

    $experienceRows = collect($selectedEmployee?->education_experience_details ?? [])
        ->filter(fn($row) => filled($row?->company_name) || filled($row?->designation) || filled($row?->joining_date) || filled($row?->left_date))
        ->sortBy('id')
        ->values();

    $referenceName = $selectedEmployee?->parentEmployee?->full_name ?? '--';
    $referenceNumber = $selectedEmployee?->parentEmployee?->contact_number ?? '--';
    $referenceRelationship = $selectedEmployee?->parentEmployee ? 'Reporting Manager' : '--';

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

    $maleChecked = $genderValue === 'male';
    $femaleChecked = $genderValue === 'female';
    $otherChecked = in_array($genderValue, ['other', 'others'], true);
    $marriedChecked = $maritalStatusValue === 'married';
    $unmarriedChecked = in_array($maritalStatusValue, ['unmarried', 'single'], true);

    $minEducationRows = max(4, $educationRows->count());
    $minExperienceRows = max(6, $experienceRows->count());
@endphp

<style>
    .job-application-form {
        color: #111827;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 11.4px;
        line-height: 1.36;
        background: #fff;
    }

    .job-application-page {
        position: relative;
        width: 210mm;
        min-height: 285mm;
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

    .job-application-page:last-child {
        page-break-after: auto;
        break-after: auto;
    }

    .job-application-watermark {
        position: absolute;
        left: 50%;
        top: 53%;
        transform: translate(-50%, -50%);
        width: min(145mm, 82%);
        opacity: 0.10;
        pointer-events: none;
        z-index: 0;
    }

    .job-application-header {
        position: relative;
        z-index: 1;
        margin-bottom: 3px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
    }

    .job-application-header img {
        display: block;
        max-height: 55px;
        max-width: 260px;
        width: auto;
        height: auto;
        object-fit: contain;
        margin: 0 auto;
    }

    .job-application-divider {
        border-top: 1px solid #111827;
        margin: 4px 0 10px;
    }

    .job-application-title {
        text-align: center;
        font-size: 18px;
        font-weight: 700;
        margin: 18px 0 14px;
        letter-spacing: 0;
        text-transform: uppercase;
    }

    .job-application-section-title {
        font-size: 13.5px;
        font-weight: 700;
        margin: 14px 0 8px;
    }

    .job-application-subtitle {
        font-size: 12px;
        font-weight: 700;
        margin: 0 0 18px;
    }

    .job-application-table {
        width: 100%;
        border-collapse: collapse;
    }

    .job-application-table td {
        padding: 10px 4px;
        vertical-align: top;
        font-size: 12px;
    }

    .job-application-label {
        width: 34%;
        font-weight: 700;
        white-space: nowrap;
    }

    .job-application-value-block {
        border-bottom: 1px solid #111827;
        min-height: 16px;
        padding: 1px 4px 2px 6px;
        white-space: pre-line;
        line-height: 1.32;
    }

    .job-application-value-block--lines {
        padding: 0;
        border-bottom: none;
    }

    .job-application-line {
        border-bottom: 1px solid #111827;
        min-height: 16px;
        margin: 0 0 7px;
        padding: 1px 4px 2px 2px;
        white-space: pre-line;
    }

    .job-application-line:last-child {
        margin-bottom: 0;
    }

    .job-application-options {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
        min-height: 16px;
        padding-top: 2px;
        font-size: 12px;
    }

    .job-application-option {
        white-space: nowrap;
    }

    .job-application-grid-table,
    .job-application-ref-table,
    .job-application-sign-table {
        width: 100%;
        border-collapse: collapse;
    }

    .job-application-grid-table th,
    .job-application-grid-table td,
    .job-application-ref-table th,
    .job-application-ref-table td {
        border: 1px solid #111827;
        padding: 15px 6px;
        vertical-align: top;
        font-size: 11.2px;
    }

    .job-application-grid-table th,
    .job-application-ref-table th {
        font-weight: 700;
        text-align: left;
    }

    .job-application-declaration {
        margin-top: 8px;
        font-size: 12px;
        line-height: 1.45;
        text-align: justify;
    }

    .job-application-sign-table {
        margin-top: 14px;
    }

    .job-application-sign-table td {
        border: 1px solid #111827;
        text-align: center;
        font-weight: 700;
        font-size: 11px;
        padding: 16px 6px;
        height: 40px;
    }

    .job-application-footer-line {
        display: flex;
        align-items: baseline;
        gap: 6px;
        margin-top: 14px;
    }

    .job-application-footer-line .job-application-label {
        width: auto;
    }

    .job-application-footer-line .job-application-value-block {
        flex: 1;
        width: auto;
        margin-left: 0;
    }

    .job-application-muted {
        color: #4b5563;
        font-size: 10.5px;
        margin-top: 8px;
    }

    .job-application-spacer-lines {
        margin: 0 0 8px;
    }

    .job-application-spacer-lines .job-application-line {
        margin-bottom: 6px;
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

        .job-application-page {
            width: calc(100% - 10mm);
            max-width: calc(100% - 10mm);
            margin: 4mm auto 0;
            padding: 5mm 6mm 6mm;
            box-shadow: none;
        }
    }
</style>

<div class="job-application-form">
    <div class="job-application-page">
        <img class="job-application-watermark" src="{{ $watermarkImage }}" alt="Watermark">

        <div class="job-application-header">
            <img src="{{ $headerImage }}" alt="Header">
        </div>

        <div class="job-application-divider"></div>

        <div class="job-application-title">JOB APPLICATION FORM</div>

        <div class="job-application-section-title">1. Personal Details (વ્યક્તિગત માહિતી)</div>

        <table class="job-application-table">
            <tr>
                <td class="job-application-label">Full Name (પૂર્ણ નામ):</td>
                <td class="job-application-value-block">{{ $employeeName }}</td>
            </tr>
            <tr>
                <td class="job-application-label">Father's Name (પિતાનું નામ):</td>
                <td class="job-application-value-block">{{ $fatherName }}</td>
            </tr>
            <tr>
                <td class="job-application-label">Date of Birth (જન્મ તારીખ):</td>
                <td class="job-application-value-block">{{ $dateOfBirth }}</td>
            </tr>
            <tr>
                <td class="job-application-label">Age (ઉમર):</td>
                <td class="job-application-value-block">{{ $age }}</td>
            </tr>
            <tr>
                <td class="job-application-label">Gender (લિંગ):</td>
                <td class="job-application-value-block">
                    <div class="job-application-options">
                        <span class="job-application-option">{{ $maleChecked ? '☑' : '☐' }} Male</span>
                        <span class="job-application-option">{{ $femaleChecked ? '☑' : '☐' }} Female</span>
                        <span class="job-application-option">{{ $otherChecked ? '☑' : '☐' }} Other</span>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="job-application-label">Marital Status (વૈવાહિક સ્થિતિ):</td>
                <td class="job-application-value-block">
                    <div class="job-application-options">
                        <span class="job-application-option">{{ $marriedChecked ? '☑' : '☐' }} Married</span>
                        <span class="job-application-option">{{ $unmarriedChecked ? '☑' : '☐' }} Unmarried</span>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="job-application-label">Mobile Number (મોબાઈલ નંબર):</td>
                <td class="job-application-value-block">{{ $mobileNumber }}</td>
            </tr>
            <tr>
                <td class="job-application-label">Alternate Contact Number:</td>
                <td class="job-application-value-block">{{ $alternateContactNumber }}</td>
            </tr>
            <tr>
                <td class="job-application-label">Email ID:</td>
                <td class="job-application-value-block">{{ $emailId }}</td>
            </tr>
            <tr>
                <td class="job-application-label">Aadhar Number:</td>
                <td class="job-application-value-block">{{ $aadharNumber }}</td>
            </tr>
            <tr>
                <td class="job-application-label">Blood Group:</td>
                <td class="job-application-value-block">{{ $bloodGroup }}</td>
            </tr>
        </table>

        <div class="job-application-section-title">2. Address Details (સરનામું)</div>
        <div class="job-application-subtitle">Current Address (હાલનું સરનામું): </div>
        <div class="job-application-spacer-lines">
            <div class="job-application-line">{{ $currentAddress }}</div>
            <div class="job-application-line">&nbsp;</div>
            <div class="job-application-line">&nbsp;</div>
            <div class="job-application-line">&nbsp;</div>
        </div>
        <div class="job-application-subtitle" style="margin-top: 16px;">Permanent Address (કાયમી સરનામું): </div>
        <div class="job-application-line">&nbsp;</div>
    </div>

    <div class="job-application-page">
        <img class="job-application-watermark" src="{{ $watermarkImage }}" alt="Watermark">

        <div class="job-application-header">
            <img src="{{ $headerImage }}" alt="Header">
        </div>

        <div class="job-application-divider"></div>

        <div class="job-application-spacer-lines" style="margin-top: 2px; margin-bottom: 8px;">
            <div class="job-application-line">&nbsp;</div>
            <div class="job-application-line">&nbsp;</div>
            <div class="job-application-line">&nbsp;</div>
            <div class="job-application-line">&nbsp;</div>
        </div>

        <div class="job-application-section-title">3. Education Details (શૈક્ષણિક માહિતી)</div>

        <table class="job-application-grid-table">
            <thead>
                <tr>
                    <th style="width: 36%;">Qualification</th>
                    <th style="width: 24%;">Board / University</th>
                    <th style="width: 18%;">Year</th>
                    <th style="width: 22%;">Percentage</th>
                </tr>
            </thead>
            <tbody>
                @for ($i = 0; $i < $minEducationRows; $i++)
                    @php $educationRow = $educationRows->get($i); @endphp
                    <tr>
                        <td>{{ $educationRow?->degree ?? '' }}</td>
                        <td>{{ $educationRow?->institution_name ?? '' }}</td>
                        <td>{{ $educationRow?->month_of_passing_year ?? '' }}</td>
                        <td>{{ $educationRow?->class_or_mark ?? '' }}</td>
                    </tr>
                @endfor
            </tbody>
        </table>

        <div class="job-application-section-title" style="margin-top: 16px;">4. Work Experience Details (અનુભવની માહિતી)</div>

        <table class="job-application-grid-table">
            <thead>
                <tr>
                    <th style="width: 36%;">Company Name</th>
                    <th style="width: 20%;">Designation</th>
                    <th style="width: 14%;">From</th>
                    <th style="width: 14%;">To</th>
                    <th style="width: 16%;">Salary</th>
                </tr>
            </thead>
            <tbody>
                @for ($i = 0; $i < $minExperienceRows; $i++)
                    @php $experienceRow = $experienceRows->get($i); @endphp
                    <tr>
                        <td>{{ $experienceRow?->company_name ?? '' }}</td>
                        <td>{{ $experienceRow?->designation ?? '' }}</td>
                        <td>{{ $experienceRow?->joining_date ?? '' }}</td>
                        <td>{{ $experienceRow?->left_date ?? '' }}</td>
                        <td>{{ $experienceRow?->ctc_salary ?? '' }}</td>
                    </tr>
                @endfor
            </tbody>
        </table>

        <div class="job-application-section-title" style="margin-top: 16px;">5. Job Applied For (અરજી કરેલ હોદો)</div>

        <table class="job-application-table">
            <tr>
                <td class="job-application-label">Position Applied:</td>
                <td class="job-application-value-block">{{ $positionApplied }}</td>
            </tr>
            <tr>
                <td class="job-application-label">Department:</td>
                <td class="job-application-value-block">{{ $department }}</td>
            </tr>
        </table>
    </div>

    <div class="job-application-page">
        <img class="job-application-watermark" src="{{ $watermarkImage }}" alt="Watermark">

        <div class="job-application-header">
            <img src="{{ $headerImage }}" alt="Header">
        </div>

        <div class="job-application-divider"></div>

        <table class="job-application-table" style="margin-top: 2px;">
            <tr>
                <td class="job-application-label">Expected Salary:</td>
                <td class="job-application-value-block">{{ $expectedSalary }}</td>
            </tr>
            <tr>
                <td class="job-application-label">Joining Time Required:</td>
                <td class="job-application-value-block">{{ $joiningTimeRequired }}</td>
            </tr>
        </table>

        <div class="job-application-section-title" style="margin-top: 16px;">6. Statutory Details (કાનૂની વિગતો)</div>

        <table class="job-application-table">
            <tr>
                <td class="job-application-label">PF Number (if available):</td>
                <td class="job-application-value-block">{{ $pfNumber }}</td>
            </tr>
            <tr>
                <td class="job-application-label">ESIC Number (if available):</td>
                <td class="job-application-value-block">{{ $esicNumber }}</td>
            </tr>
            <tr>
                <td class="job-application-label">UAN Number:</td>
                <td class="job-application-value-block">{{ $uanNumber }}</td>
            </tr>
        </table>

        <div class="job-application-section-title" style="margin-top: 16px;">7. Reference Details (સંદર્ભ માહિતી)</div>

        <table class="job-application-ref-table">
            <thead>
                <tr>
                    <th style="width: 44%;">Name</th>
                    <th style="width: 32%;">Contact Number</th>
                    <th style="width: 24%;">Relationship</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $referenceName }}</td>
                    <td>{{ $referenceNumber }}</td>
                    <td>{{ $referenceRelationship }}</td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            </tbody>
        </table>

        <div class="job-application-section-title" style="margin-top: 16px;">8. Declaration (ઘોષણા)</div>

        <div class="job-application-declaration">
            I hereby declare that the above information provided by me is true and correct to the best of my knowledge.
            If any information is found incorrect, the company has the right to cancel my employment.
        </div>

        <div class="job-application-declaration" style="margin-top: 10px;">
           હું ખાતરી આપું છું કે ઉપર આપેલી માહિતી મારી જાણ મુજબ સાચી છે. કોઈ માહિતી ખોટી સાબિત થાય તો કંપની મારી નોકરી રદ કરી શકે છે.
        </div>

        <div class="job-application-footer-line" style="margin-top: 16px;">
            <span class="job-application-label">Applicant Signature (અરજદારની સહી):</span>
            <span class="job-application-value-block">&nbsp;</span>
        </div>

        <div class="job-application-footer-line" style="margin-top: 14px;">
            <span class="job-application-label">Date (તારીખ):</span>
            <span class="job-application-value-block">{{ $today }}</span>
        </div>

      
    </div>
</div>
