@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : null;
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $i = 0;
    $company_id = isset($modules['company_id']) ? $modules['company_id'] : null;

    // dd($modules);

@endphp
@section('title', $page_title)


@section('content')
    <div class="px-1">
        <div class="d-flex flex-column flex-lg-row justify-content-lg-between align-items-start align-items-lg-center">
            @include('software.inlcudes.breadcrumb', [
                'breadcrumbArray' => [
                    ['title' => $page_title, 'url' => route($route . '.index')],
                    [
                        'title' => isset($edit) && $edit?->id ? 'Edit ' . $page_title : 'Create ' . $page_title,
                        'url' => '',
                    ],
                ],
            ])
            <a class="btn btn-primary waves-effect waves-light text-white" href="{{ url()->previous() }}">
                <i class="menu-icon ti ti-chevrons-left"></i> Back
            </a>
        </div>
    </div>
    <div class="card my-3 mb-4">
        <h5 class="card-header bg-primary text-white">
            <i class="ti ti-file-import"></i> Import Employees - Bulk Upload
        </h5>
        <div class="card-body">
            <!-- Import Instructions -->
            <div class="alert alert-info alert-dismissible fade show mt-3 " role="alert">
                <h6 class="alert-heading"><i class="ti ti-info-circle"></i> <strong>How to Import Employees</strong></h6>
                <ol class="mb-0 ps-3">
                    <li><strong>Download</strong> the sample Excel template below</li>
                    <li><strong>Fill in</strong> employee data (minimum: employee_code, employee_full_name, date_of_birth,
                        date_of_joining)</li>
                    <li><strong>Ensure</strong> all lookup values (Branch, Department, Designation, etc.) exist in the
                        system</li>
                    <li><strong>Upload</strong> your completed file</li>
                    <li><strong>Review</strong> the import summary and fix any errors</li>
                </ol>
                <hr>
                <p class="mb-0">
                    <i class="ti ti-file-text"></i> <strong>Documentation:</strong>
                    <a href="{{ asset('sample-file/EMPLOYEE-IMPORT-README.md') }}" target="_blank" class="alert-link">Quick
                        Start Guide</a> |
                    <a href="{{ asset('sample-file/Employee-Import-Template-Documentation.md') }}" target="_blank"
                        class="alert-link">Full Documentation</a>
                </p>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

            <form action="{{ route('import-employee.store') }}" method="POST" enctype="multipart/form-data"
                id="import-form">
                @csrf
                <div class="row">
                    @if (!$company_id)
                        {{-- Company --}}
                        <div class="col-md-4 col-sm-12 mb-2">
                            <div class="form-group">
                                <label class="form-label">Select Company <span class="text-danger">*</span></label>
                                <select id="company_id"
                                    class="form-control select2 search_by_company @error('company_id') is-invalid @enderror"
                                    name="company_id"
                                    data-selectedCompanyId="{{ old('company_id') ?? ($edit->company_id ?? '') }}">
                                    <option value="">Select Company</option>
                                </select>

                                @error('company_id')
                                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>
                    @else
                        <input type="hidden" class="form-control search_by_company" name="company_id"
                            value="{{ $company_id }}" />
                    @endif
                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <label class="form-label"> Import File <span class="text-danger">*</span> </label>
                            <input id="import_file" type="file"
                                class="form-control @error('import_file') is-invalid @enderror" name="import_file"
                                value="{{ isset($edit?->import_file) ? $edit?->import_file : old('import_file') }}">

                            @error('import_file')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>


                    {{-- Sample File Download --}}
                    <div class="col-md-4 d-flex align-items-end mb-3" id="sampleFileDiv"
                        style="{{ $company_id ? '' : 'display: none;' }}">
                        <div class="w-100">
                            <small class="text-muted d-block mt-1">
                                <i class="ti ti-file-spreadsheet"></i> .xlsx format with all column headers
                            </small>
                            <a href="{{ $import_file ?? asset('sample-file/ocean-hrms-employee-import-sample.xlsx') }}"
                                class="btn btn-outline-primary w-100" download>
                                <i class="ti ti-download"></i> Download Sample Excel Template
                            </a>
                        </div>
                    </div>



                    <div class="divider">
                        <hr />
                    </div>
                    <div class="col-md-12 text-center">
                        <button type="button" id="submitBtn" class="btn btn-success mt-1 mb-1">
                            Submit
                        </button>
                        <a href="{{ route('employees.index') }}" class="btn btn-danger mt-1 mb-1">Cancel</a>
                    </div>
                </div>
            </form>

            <!-- Import Results -->
            @if (session('employee_summary'))
                @php $summary = session('employee_summary'); @endphp
                <div class="alert alert-{{ $summary['total_failed_employees'] > 0 ? 'warning' : 'success' }} mt-3">
                    <h6 class="alert-heading"><i class="ti ti-chart-bar"></i> Import Summary</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <strong>Total Rows:</strong>
                            <span class="badge bg-info">{{ $summary['total_employees'] }}</span>
                        </div>
                        <div class="col-md-4">
                            <strong>Successfully Imported:</strong>
                            <span class="badge bg-success">{{ $summary['total_success_employees'] }}</span>
                        </div>
                        <div class="col-md-4">
                            <strong>Failed:</strong>
                            <span class="badge bg-danger">{{ $summary['total_failed_employees'] }}</span>
                        </div>
                    </div>
                </div>
            @endif

            @if (session('employeeImportFileMessages'))
                <div class="alert alert-danger mt-3">
                    <h6 class="alert-heading"><i class="ti ti-alert-circle"></i> Import Errors</h6>
                    <p class="mb-2">The following rows could not be imported. Please fix the errors and try again:</p>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th width="100">Row #</th>
                                    <th>Error Message</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach (session('employeeImportFileMessages') as $row => $message)
                                    <tr>
                                        <td><strong>{{ $row }}</strong></td>
                                        <td>{{ $message }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Help Section -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card border border-primary">
                        <div class="card-body">
                            <h6 class="card-title text-primary">
                                <i class="ti ti-file-check"></i> Required Columns
                            </h6>
                            <ul class="mb-0">
                                <li><code>employee_code</code> - Must be unique</li>
                                <li><code>employee_full_name</code> - Full name</li>
                                <li><code>date_of_birth</code> - Format: DD/MM/YYYY</li>
                                <li><code>date_of_joining</code> - Format: DD/MM/YYYY</li>
                            </ul>
                            <hr>
                            <small class="text-muted">
                                <strong>Tip:</strong> All other columns are optional but recommended
                            </small>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card border border-warning">
                        <div class="card-body">
                            <h6 class="card-title text-warning">
                                <i class="ti ti-alert-triangle"></i> Common Errors
                            </h6>
                            <ul class="mb-0">
                                <li>Duplicate employee codes</li>
                                <li>Invalid date formats (use DD/MM/YYYY)</li>
                                <li>Non-existent branch/department names</li>
                                <li>Missing required fields</li>
                                <li>Parent employee code not found</li>
                            </ul>
                            <hr>
                            <small class="text-muted">
                                <strong>Tip:</strong> Ensure master data exists before importing
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- What Gets Created Section -->
            <div class="card border border-success mt-3">
                <div class="card-body">
                    <h6 class="card-title text-success">
                        <i class="ti ti-checklist"></i> What Gets Created for Each Employee?
                    </h6>
                    <div class="row">
                        <div class="col-md-3">
                            <ul class="mb-0">
                                <li><i class="ti ti-check text-success"></i> Employee Record</li>
                                <li><i class="ti ti-check text-success"></i> Employment Detail</li>
                            </ul>
                        </div>
                        <div class="col-md-3">
                            <ul class="mb-0">
                                <li><i class="ti ti-check text-success"></i> Increment Detail</li>
                                <li><i class="ti ti-check text-success"></i> Salary Configuration</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="mb-0">
                                <li><i class="ti ti-check text-info"></i> Salary Record (Optional - only if salary_month
                                    and salary_year provided)</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>




    </div>
@endsection
@push('page_scripts')
    @if (!$company_id)
        @include('utils.getCompany')
    @endif

    <script>
        $(document).ready(function() {
            $('#submitBtn').click(function() {
                $(this).prop('disabled', true).html('Processing...');

                $('#import-form').submit();
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            function toggleSampleDiv() {
                if ($('#company_id').val()) {
                    console.log($('#company_id').val());

                    $('#sampleFileDiv').show();
                } else {
                    $('#sampleFileDiv').hide();
                }
            }
            toggleSampleDiv();
            $('#company_id').on('change', toggleSampleDiv);
        });
    </script>
@endpush
