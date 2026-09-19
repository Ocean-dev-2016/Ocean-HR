@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : 'Employee Documents';
    $route = isset($modules['route']) ? $modules['route'] : 'employee-documents';
    $company_id = isset($modules['company_id']) ? $modules['company_id'] : null;
    $selectedCompanyId = $selectedCompanyId ?? request()->route('company_id') ?? request('company_id') ?? $company_id;
    $selectedEmployeeId = $selectedEmployeeId ?? request()->route('employee_id') ?? request('employee_id') ?? optional($selectedEmployee)->id;
    $documentMeta = $documentMeta ?? ['title' => $documentTypes[$selectedDocumentType] ?? 'Employee Document', 'requires_employee' => true];
    $previewDocumentTitle = $documentMeta['title'] ?? ($documentTypes[$selectedDocumentType] ?? 'Employee Document');
    $previewWatermarkImage = in_array(($selectedDocumentType ?? ''), ['advance-form', 'increment', 'appointment', 'experience', 'offer', 'job-rotation', 'job-application-form', 'no-due-clearance', 'loan-form', 'full-final-form', 'salary-certificate', 'relieving-letter'], true)
        ? asset('software/img/Ocean_HR.png')
        : null;
@endphp

@section('title', $page_title)

@section('page_leavel_style')
    <style>
        .doc-preview-shell {
            background: linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%);
            border: 1px solid #e5e7eb;
            border-radius: 18px;
            padding: 20px;
        }
    </style>
@endsection

@section('content')
    <div class="px-1">
        <div class="d-lg-flex justify-content-lg-between flex-column flex-lg-row">
            @include('software.inlcudes.breadcrumb', [
                'breadcrumbArray' => [['title' => $page_title, 'url' => '']],
                'route' => $route,
                'show_back_btn' => true,
            ])
        </div>
    </div>

    <div class="row my-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Select Employee and Document Type</h5>
                </div>
                <div class="card-body">
                    <form id="employeeDocumentForm" method="GET" action="{{ route($route . '.index') }}">
                        <div class="row g-3 align-items-end">
                            @if (!$company_id)
                                <div class="col-md-4 col-sm-12">
                                    <label class="form-label">Select Company</label>
                                    <select id="company_id" name="company_id"
                                        class="form-control select2 search_by_company"
                                        data-append="search_by_company"
                                        data-selectedcompanyid="{{ $selectedCompanyId }}">
                                        <option value="">Select Company</option>
                                    </select>
                                </div>
                            @else
                                <input type="hidden" name="company_id" class="search_by_company"
                                    value="{{ $company_id }}">
                            @endif

                            <div class="col-md-4 col-sm-12">
                                <label class="form-label">Select Employee</label>
                                <select id="employee_id" name="employee_id"
                                    class="form-control select2 search_by_employee"
                                    data-selectedemployeeid="{{ $selectedEmployeeId }}">
                                    <option value="">Select Employee</option>
                                </select>
                            </div>

                            <div class="col-md-4 col-sm-12">
                                <label class="form-label">Select Document Type</label>
                                <select id="document_type" name="document_type" class="form-control select2">
                                    <option value="">Select Document Type</option>
                                    @foreach ($documentTypes as $key => $label)
                                        <option value="{{ $key }}" @selected(($selectedDocumentType ?? '') === $key)>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 d-flex gap-2 justify-content-end mt-2">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="ti ti-eye me-1"></i> Preview
                                </button>
                                <button type="button" id="printDocumentBtn" class="btn btn-primary">
                                    <i class="ti ti-printer me-1"></i> Print
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 mt-3">
            @if ($selectedDocumentType && ($selectedEmployee || !($documentMeta['requires_employee'] ?? true)))
                <div class="doc-preview-shell">
                    @include('software.modules.employee.employee-documents.partials.document-shell', [
                        'documentTitle' => $previewDocumentTitle,
                        'previewMode' => true,
                        'headerImage' => $selectedEmployee?->company?->order_header_logo_url 
                            ?? $selectedEmployee?->company?->company_logo_url 
                            ?? asset('software/img/logo.png'),
                        'watermarkImage' => $previewWatermarkImage,
                        'bodyView' => 'software.modules.employee.employee-documents.partials.' . $selectedDocumentType,
                        'bodyData' => [
                            'selectedEmployee' => $selectedEmployee,
                            'currentEmployment' => $selectedEmployee?->employmentDetail
                                ?? $selectedEmployee?->employment_details?->sortByDesc('id')->first(),
                            'latestIncrement' => $selectedEmployee?->increment_details?->sortByDesc('id')->first(),
                            'latestSalary' => $selectedEmployee?->salary_details?->sortByDesc('id')->first(),
                            'latestMonthlySalary' => $selectedEmployee?->salaries?->sortByDesc('id')->first(),
                            'latestLoan' => $selectedEmployee?->loans?->sortByDesc('id')->first(),
                            'documentTypes' => $documentTypes,
                        ],
                    ])
                </div>
            @else
                <div class="alert alert-info mb-0">
                    Please select a sales person and document type to preview the printable layout.
                </div>
            @endif
        </div>
    </div>
@endsection

@push('page_scripts')
    @if (!$company_id)
        @include('utils.getCompany')
    @endif
    @include('utils.getEmployee')

    <script>
        $(document).ready(function() {
            const employeeDocumentIndexBase = @json(url('/software/employee-documents'));
            const employeeDocumentPrintBase = @json(url('/software/employee-documents/print'));

            function buildDocumentUrl(baseUrl, companyId, employeeId, documentType) {
                const segments = [companyId, employeeId, documentType]
                    .filter(function(value) {
                        return value !== null && value !== undefined && String(value).trim() !== '';
                    })
                    .map(function(value) {
                        return encodeURIComponent(value);
                    });

                return segments.length ? baseUrl + '/' + segments.join('/') : baseUrl;
            }

            $('#employeeDocumentForm').on('submit', function(event) {
                event.preventDefault();

                const companyId = $('#company_id').val() || $('input[name="company_id"]').val();
                const documentType = $('#document_type').val();
                const employeeId = $('#employee_id').val();

                if (!companyId) {
                    alert('Please select a company.');
                    return;
                }

                if (!employeeId) {
                    alert('Please select an employee.');
                    return;
                }

                if (!documentType) {
                    alert('Please select a document type.');
                    return;
                }

                window.location.href = buildDocumentUrl(employeeDocumentIndexBase, companyId, employeeId, documentType);
            });

            $('#printDocumentBtn').on('click', function() {
                const companyId = $('#company_id').val() || $('input[name="company_id"]').val();
                const documentType = $('#document_type').val();
                const employeeId = $('#employee_id').val();

                if (!companyId) {
                    alert('Please select a company.');
                    return;
                }

                if (!employeeId) {
                    alert('Please select an employee.');
                    return;
                }

                if (!documentType) {
                    alert('Please select a document type.');
                    return;
                }

                const url = buildDocumentUrl(employeeDocumentPrintBase, companyId, employeeId, documentType);
                window.open(url, '_blank');
            });
        });
    </script>
@endpush
