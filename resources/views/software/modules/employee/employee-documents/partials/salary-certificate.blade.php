@php
    $employment = $currentEmployment ?? null;
    $employeeName = $selectedEmployee?->full_name ?? '--';
    $employeeCode = $selectedEmployee?->employee_code ?? '--';
    $panNumber = $selectedEmployee?->pan_card_number ?? '--';
    $designation = $employment?->designation?->name ?? $selectedEmployee?->current_role?->name ?? '--';
    $department = $employment?->department?->name ?? '--';
    $joiningDate = $employment?->date_of_joining
        ? \Carbon\Carbon::parse($employment->date_of_joining)->format('d/m/Y')
        : '--';
    $salarySource = $latestSalary?->gross_salary
        ?? $latestSalary?->given_calculate_salary
        ?? $latestSalary?->ctc
        ?? null;
    $salaryText = $salarySource !== null && $salarySource !== ''
        ? number_format((float) $salarySource, 2)
        : '--';
    $salaryWords = static function ($amount): string {
        $amount = (float) $amount;

        if ($amount <= 0) {
            return 'Zero';
        }

        $ones = [
            0 => 'Zero',
            1 => 'One',
            2 => 'Two',
            3 => 'Three',
            4 => 'Four',
            5 => 'Five',
            6 => 'Six',
            7 => 'Seven',
            8 => 'Eight',
            9 => 'Nine',
            10 => 'Ten',
            11 => 'Eleven',
            12 => 'Twelve',
            13 => 'Thirteen',
            14 => 'Fourteen',
            15 => 'Fifteen',
            16 => 'Sixteen',
            17 => 'Seventeen',
            18 => 'Eighteen',
            19 => 'Nineteen',
        ];

        $tens = [
            2 => 'Twenty',
            3 => 'Thirty',
            4 => 'Forty',
            5 => 'Fifty',
            6 => 'Sixty',
            7 => 'Seventy',
            8 => 'Eighty',
            9 => 'Ninety',
        ];

        $convertUnderThousand = static function (int $number) use (&$ones, &$tens): string {
            $parts = [];

            if ($number >= 100) {
                $parts[] = $ones[intdiv($number, 100)] . ' Hundred';
                $number %= 100;
            }

            if ($number >= 20) {
                $parts[] = $tens[intdiv($number, 10)];
                $number %= 10;
            }

            if ($number > 0) {
                $parts[] = $ones[$number];
            }

            return implode(' ', $parts);
        };

        $rupees = (int) floor($amount);
        $paise = (int) round(($amount - $rupees) * 100);
        if ($paise === 100) {
            $rupees++;
            $paise = 0;
        }

        $segments = [];
        $crore = intdiv($rupees, 10000000);
        $rupees %= 10000000;
        $lakh = intdiv($rupees, 100000);
        $rupees %= 100000;
        $thousand = intdiv($rupees, 1000);
        $rupees %= 1000;

        if ($crore > 0) {
            $segments[] = $convertUnderThousand($crore) . ' Crore';
        }

        if ($lakh > 0) {
            $segments[] = $convertUnderThousand($lakh) . ' Lakh';
        }

        if ($thousand > 0) {
            $segments[] = $convertUnderThousand($thousand) . ' Thousand';
        }

        if ($rupees > 0) {
            $segments[] = $convertUnderThousand($rupees);
        }

        $result = trim(implode(' ', $segments));

        if ($result === '') {
            $result = 'Zero';
        }

        if ($paise > 0) {
            $result .= ' and ' . $convertUnderThousand($paise) . ' Paise';
        }

        return $result;
    };
    $salaryWordsText = $salarySource !== null && $salarySource !== ''
        ? $salaryWords((float) $salarySource)
        : '--';
    $companyName = $selectedEmployee?->company?->company_name ?? 'OceanHR';
    $headerImage = $headerImage ?? asset('software/img/header.jpg');
    $watermarkImage = $watermarkImage ?? asset('software/img/Ocean_HR.png');
    $today = \Carbon\Carbon::now()->format('d/m/Y');
@endphp

<style>
    .salary-certificate {
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

    .salary-certificate__watermark {
        position: absolute;
        left: 50%;
        top: 55%;
        transform: translate(-50%, -50%);
        width: min(145mm, 82%);
        opacity: 0.11;
        pointer-events: none;
        z-index: 0;
    }

    .salary-certificate__header {
        position: relative;
        z-index: 1;
        margin-bottom: 2px;
    }

    .salary-certificate__header img {
        display: block;
        width: 100%;
        height: auto;
        opacity: 0.62;
        filter: grayscale(0.08) contrast(0.96);
    }

    .salary-certificate__divider {
        border-top: 1px solid #111827;
        margin: 4px 0 10px;
    }

    .salary-certificate__date {
        text-align: right;
        font-size: 11.4px;
        margin-bottom: 26px;
        font-weight: 700;
    }

    .salary-certificate__title {
        text-align: center;
        font-size: 20px;
        font-weight: 700;
        margin: 0 0 28px;
        text-decoration: underline;
        letter-spacing: 0.3px;
    }

    .salary-certificate__body {
        position: relative;
        z-index: 1;
        padding: 0 2px;
        font-size: 13.8px;
        line-height: 1.6;
    }

    .salary-certificate__para {
        margin: 0 0 12px;
        text-align: justify;
    }

    .salary-certificate__closing {
        margin-top: 18px;
    }

    .salary-certificate__sign-block {
        margin-top: 26px;
        display: flex;
        justify-content: space-between;
        align-items: end;
        gap: 18px;
    }

    .salary-certificate__sign-left {
        width: 54%;
    }

    .salary-certificate__sign-right {
        width: 38%;
        text-align: center;
    }

    .salary-certificate__sign-line {
        border-bottom: 1px solid #111827;
        height: 22px;
        margin: 0 0 5px;
        width: 100%;
    }

    .salary-certificate__sign-label {
        font-weight: 700;
    }

    .salary-certificate__stamp {
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

    .salary-certificate__stamp strong {
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

        .salary-certificate {
            width: calc(100% - 10mm);
            max-width: calc(100% - 10mm);
            min-height: calc(297mm - 10mm);
            margin: 5mm auto;
            box-shadow: none;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }
</style>

<div class="salary-certificate">
    <img class="salary-certificate__watermark" src="{{ $watermarkImage }}" alt="Watermark">

    <div class="salary-certificate__header">
        <img src="{{ $headerImage }}" alt="Header">
    </div>

    <div class="salary-certificate__divider"></div>

    <div class="salary-certificate__date">Date: {{ $today }}</div>

    <div class="salary-certificate__title">SALARY CERTIFICATE</div>

    <div class="salary-certificate__body">
        <p class="salary-certificate__para">
            This is to certify that Mr. <strong>{{ $employeeName }}</strong>, PAN No. <strong>{{ $panNumber }}</strong>,
            is working with our organization <strong>{{ $companyName }}</strong> as a
            <strong>{{ $designation }}</strong> since <strong>{{ $joiningDate }}</strong>.
        </p>

        <p class="salary-certificate__para">
            His gross monthly salary is Rs. <strong>{{ $salaryText }}/-</strong> (Rupees
            <strong>{{ $salaryWordsText }}</strong> Only).
        </p>

        <p class="salary-certificate__para">
            Any salary deduction, if applicable, shall be made by the HR Department on a monthly basis as per company policy.
        </p>

        <p class="salary-certificate__para">
            This certificate is issued upon the employee's request for his loan purpose.
        </p>

        <div class="salary-certificate__closing">
          <strong>  Regards,<br><br>
            For {{ strtoupper($companyName) }}<br><br><br>
            Authorized Signatory,</strong>
        </div>

        
    </div>

   
</div>
