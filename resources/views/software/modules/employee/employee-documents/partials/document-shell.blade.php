@php
    $documentTitle = $documentTitle ?? 'Document';
    $previewMode = $previewMode ?? false;
    $bodyView = $bodyView ?? null;
    $bodyData = $bodyData ?? [];
    $headerImage = $headerImage ?? asset('software/img/header.jpg');
    $watermarkImage = $watermarkImage ?? null;
@endphp

<style>
    .employee-document-paper {
        width: 100%;
        max-width: 210mm;
        margin: 0 auto;
        background: #fff;
        color: #111827;
        border: 1px solid #111827;
        box-shadow: 0 18px 45px rgba(15, 23, 42, 0.12);
        padding: 12mm 10mm 10mm;
        position: relative;
        overflow: hidden;
    }

    .employee-document-header {
        width: 100%;
        margin-bottom: 6px;
        padding-bottom: 6px;
        border-bottom: 1px solid #111827;
    }

    .employee-document-header img {
        display: block;
        width: 100%;
        height: auto;
        object-fit: cover;
        opacity: 0.56;
        filter: grayscale(0.1) contrast(0.96);
    }

    .employee-document-title {
        text-align: center;
        font-size: 19px;
        font-weight: 700;
        margin: 24px 0 24px;
        text-transform: none;
        letter-spacing: 0;
    }

    .employee-document-watermark {
        position: absolute;
        left: 50%;
        top: 52%;
        transform: translate(-50%, -50%);
        width: min(140mm, 82%);
        height: auto;
        opacity: 0.10;
        pointer-events: none;
        z-index: 0;
    }

    .employee-document-body {
        position: relative;
        z-index: 1;
    }

    .doc-section-title {
        font-size: 13px;
        font-weight: 700;
        margin: 12px 0 5px;
    }

    .doc-grid {
        width: 100%;
        border-collapse: collapse;
    }

    .doc-grid td {
        padding: 4px 4px;
        vertical-align: top;
        font-size: 12px;
    }

    .doc-label {
        width: 26%;
        font-weight: 700;
        white-space: nowrap;
    }

    .doc-line {
        border-bottom: 1px solid #111827;
        min-height: 15px;
    }

    .doc-paragraph {
        font-size: 12px;
        line-height: 1.45;
        margin: 6px 0;
        text-align: justify;
    }

    .doc-sign-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 12px;
    }

    .doc-sign-table td {
        border: 1px solid #111827;
        height: 38px;
        text-align: center;
        font-size: 12px;
        font-weight: 700;
        padding: 6px;
    }

    @media print {
        @page {
            size: A4;
            margin: 0;
        }

        .employee-document-paper {
            width: auto;
            margin: 8mm auto;
            background: #fff;
            color: #111827;
            border: 1px solid #111827;
            box-shadow: none;
            padding: 7mm 7mm 6mm;
            max-width: none;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .no-print {
            display: none !important;
        }
    }
</style>

@if (in_array($bodyView, [
    'software.modules.employee.employee-documents.partials.appointment',
    'software.modules.employee.employee-documents.partials.offer',
    'software.modules.employee.employee-documents.partials.job-rotation',
    'software.modules.employee.employee-documents.partials.job-application-form',
    'software.modules.employee.employee-documents.partials.no-due-clearance',
    'software.modules.employee.employee-documents.partials.loan-form',
    'software.modules.employee.employee-documents.partials.full-final-form',
    'software.modules.employee.employee-documents.partials.salary-certificate',
    'software.modules.employee.employee-documents.partials.relieving-letter',
], true))
    @if ($bodyView)
        @include($bodyView, $bodyData)
    @endif
@else
    <div class="employee-document-paper">
        @if ($watermarkImage)
            <img class="employee-document-watermark" src="{{ $watermarkImage }}" alt="Watermark">
        @endif

        <div class="employee-document-header">
            <img src="{{ $headerImage }}" alt="Document Header">
        </div>

        <div class="employee-document-title">{{ $documentTitle }}</div>

        <div class="employee-document-body">
            @if ($bodyView)
                @include($bodyView, $bodyData)
            @endif
        </div>
    </div>
@endif
