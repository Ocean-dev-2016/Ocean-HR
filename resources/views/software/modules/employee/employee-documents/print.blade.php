<!DOCTYPE html>
<html>

<head>
    @php
        $documentMeta = $documentMeta ?? ['title' => $documentTypes[$selectedDocumentType] ?? 'Employee Document', 'requires_employee' => true];
        $printDocumentTitle = $documentMeta['title'] ?? ($documentTypes[$selectedDocumentType] ?? 'Employee Document');
    @endphp
    <meta charset="UTF-8">
    <title>{{ $printDocumentTitle }} - Print</title>
    <style>
        html,
        body {
            margin: 10px;
            padding: 0;
            background: #fff;
            font-family: Arial, Helvetica, sans-serif;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .print-page {
            padding: 0;
            background: #fff;
        }
    </style>
</head>

<body onload="printAndRedirect();">
    <div class="print-page">
        @include('software.modules.employee.employee-documents.partials.document-shell', [
            'documentTitle' => $printDocumentTitle,
            'previewMode' => false,
            'headerImage' => $selectedEmployee?->company?->order_header_logo_url 
                ?? $selectedEmployee?->company?->company_logo_url 
                ?? asset('software/img/logo.png'),
            @php
                $docCompany = $selectedEmployee?->company 
                    ?? (isset($selectedCompanyId) && $selectedCompanyId ? \App\Models\Company::find($selectedCompanyId) : null);

                $companyWatermark = ($docCompany && $docCompany->company_favicon && file_exists(public_path($docCompany->company_favicon)))
                    ? asset($docCompany->company_favicon)
                    : (($docCompany && $docCompany->company_logo && file_exists(public_path($docCompany->company_logo)))
                        ? asset($docCompany->company_logo)
                        : asset('software/img/ring.png'));

                $watermark = in_array($selectedDocumentType, ['advance-form', 'increment', 'appointment', 'experience', 'offer', 'job-rotation', 'job-application-form', 'no-due-clearance', 'loan-form', 'full-final-form', 'salary-certificate', 'relieving-letter'], true)
                    ? $companyWatermark
                    : null;
            @endphp
            'watermarkImage' => $watermark,
            'bodyView' => 'software.modules.employee.employee-documents.partials.' . $selectedDocumentType,
            'bodyData' => [
                'selectedEmployee' => $selectedEmployee,
                'currentEmployment' => $currentEmployment,
                'latestIncrement' => $latestIncrement,
                'latestSalary' => $latestSalary,
                'latestMonthlySalary' => $latestMonthlySalary,
                'latestLoan' => $latestLoan,
                'previousSalary' => $previousSalary ?? null,
                'documentTypes' => $documentTypes,
            ],
        ])
    </div>

    <script>
        function printAndRedirect() {
            window.print();

            window.onafterprint = function() {
                setTimeout(function() {
                    window.location.href = "{{ route($modules['route'] . '.index', $backRouteParams ?? []) }}";
                }, 1);
            };
        }
    </script>
</body>

</html>
