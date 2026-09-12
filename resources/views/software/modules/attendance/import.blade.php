@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : null;
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $i = 0;
    $company_id = isset($modules['company_id']) ? $modules['company_id'] : null;
@endphp
@section('title', $page_title . ' Import')

@section('content')
    <div class="px-1">
        <div class="d-flex flex-column flex-lg-row justify-content-lg-between align-items-start align-items-lg-center">
            @include('software.inlcudes.breadcrumb', [
                'breadcrumbArray' => [
                    ['title' => $page_title, 'url' => route($route . '.index')],
                    ['title' => 'Import ' . $page_title, 'url' => ''],
                    
                ],
                'show_back_btn' => true,
            ])
            <a class="btn btn-info waves-effect waves-light text-white ms-2" href="{{ route('attendance-import-history') }}">
                <i class="menu-icon ti ti-history"></i> Import History
            </a>

        </div>
    </div>
    <div class="card my-3 mb-4">
        <h5 class="card-header bg-primary text-white">
            <i class="ti ti-file-import"></i> Import Attendance - Bulk Upload
        </h5>
        <div class="card-body">
            <!-- Import Instructions -->
            <div class="alert alert-info alert-dismissible fade show mt-3" role="alert">
                <h6 class="alert-heading"><i class="ti ti-info-circle"></i> <strong>How to Import Attendance</strong></h6>
                <ol class="mb-0 ps-3">
                    <li><strong>Download</strong> the sample Excel template below (or use PDF format)</li>
                    <li><strong>Fill in</strong> attendance data (minimum: employee identifier, attendance_date, punch_in_time)</li>
                    <li><strong>Employee Matching:</strong> Use <code>biometric_user_id</code>, <code>employee_code</code>, or <code>employee_name</code></li>
                    <li><strong>Upload</strong> your completed file (Excel or PDF)</li>
                    <li><strong>Large files</strong> (>5MB) will be processed in background queue</li>
                    <li><strong>Review</strong> the import summary and fix any errors</li>
                </ol>
                <hr>
                <p class="mb-0">
                    <i class="ti ti-file-text"></i> <strong>Supported Formats:</strong> Excel (.xls, .xlsx) and PDF (.pdf)
                </p>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

            <form action="{{ route('import-attendance.store') }}" method="POST" enctype="multipart/form-data" id="import-form">
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
                                    data-selectedCompanyId="{{ old('company_id') ?? '' }}">
                                    <option value="">Select Company</option>
                                </select>
                                @error('company_id')
                                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>
                    @else
                        <input type="hidden" class="form-control search_by_company" name="company_id" value="{{ $company_id }}" />
                    @endif
                    
                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <label class="form-label"> Import File <span class="text-danger">*</span> </label>
                            <input id="import_file" type="file"
                                class="form-control @error('import_file') is-invalid @enderror" 
                                name="import_file"
                                accept=".xls,.xlsx,.pdf"
                                value="{{ old('import_file') }}">
                            <small class="text-muted">Supported: Excel (.xls, .xlsx) or PDF (.pdf)</small>
                            @error('import_file')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    {{-- Sample File Download --}}
                    <div class="col-md-4 d-flex align-items-end mb-3" id="sampleFileDiv" style="{{ $company_id ? '' : 'display: none;' }}">
                        <div class="w-100">
                            <small class="text-muted d-block mt-1">
                                <i class="ti ti-file-spreadsheet"></i> .xlsx format with all column headers
                            </small>
                            <a href="{{ $import_file ?? asset('sample-file/ocean-hrms-attendance-import-sample.xlsx') }}"
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
                        <a href="{{ route($route . '.index') }}" class="btn btn-danger mt-1 mb-1">Cancel</a>
                    </div>
                </div>
            </form>

            <!-- Queue Status (for large files) -->
            @if (session('import_file_id'))
                <div class="alert alert-info mt-3" id="queueStatusAlert">
                    <h6 class="alert-heading"><i class="ti ti-clock"></i> Processing in Background</h6>
                    <p class="mb-2">Your file is being processed in the background. Status will update automatically.</p>
                    <div id="queueStatusContent">
                        <div class="spinner-border spinner-border-sm" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <span class="ms-2">Checking status...</span>
                    </div>
                </div>
            @endif

            <!-- Import Results -->
            @if (session('attendance_summary'))
                @php $summary = session('attendance_summary'); @endphp
                <div class="alert alert-{{ $summary['total_failed'] > 0 ? 'warning' : 'success' }} mt-3">
                    <h6 class="alert-heading"><i class="ti ti-chart-bar"></i> Import Summary</h6>
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Total Rows:</strong>
                            <span class="badge bg-info">{{ $summary['total_rows'] }}</span>
                        </div>
                        <div class="col-md-3">
                            <strong>Successfully Imported:</strong>
                            <span class="badge bg-success">{{ $summary['total_success'] }}</span>
                        </div>
                        <div class="col-md-3">
                            <strong>Failed:</strong>
                            <span class="badge bg-danger">{{ $summary['total_failed'] }}</span>
                        </div>
                        <div class="col-md-3">
                            <strong>Duplicates Skipped:</strong>
                            <span class="badge bg-warning">{{ $summary['total_duplicates'] }}</span>
                        </div>
                    </div>
                </div>
            @endif

            @if (session('attendanceImportFileMessages'))
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
                                @foreach (session('attendanceImportFileMessages') as $row => $message)
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
                                <i class="ti ti-file-check"></i> Required Columns (Excel)
                            </h6>
                            <ul class="mb-0">
                                <li><code>employee_code</code> OR <code>biometric_user_id</code> OR <code>employee_name</code></li>
                                <li><code>attendance_date</code> - Format: YYYY-MM-DD or DD/MM/YYYY</li>
                                <li><code>punch_in_time</code> - Format: HH:MM:SS or HH:MM</li>
                                <li><code>attendace_type</code> - Values: 'in' or 'out' (optional, auto-detected)</li>
                            </ul>
                            <hr>
                            <small class="text-muted">
                                <strong>PDF Format:</strong> Supports "Daily Basic Attendance Report" format
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
                                <li>Employee not found (check identifier)</li>
                                <li>Invalid date/time formats</li>
                                <li>Duplicate entries (auto-skipped)</li>
                                <li>Missing required fields</li>
                                <li>Shift not found (if provided)</li>
                            </ul>
                            <hr>
                            <small class="text-muted">
                                <strong>Tip:</strong> Duplicates are automatically detected and skipped
                            </small>
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
                $(this).prop('disabled', true).html('<i class="ti ti-loader"></i> Processing...');
                $('#import-form').submit();
            });

            // Queue status polling
            @if (session('import_file_id'))
                var importFileId = {{ session('import_file_id') }};
                var pollInterval = setInterval(function() {
                    $.ajax({
                        url: '{{ route("import-attendance.status", ":id") }}'.replace(':id', importFileId),
                        method: 'GET',
                        success: function(response) {
                            if (response.success && response.data) {
                                var status = response.data.status;
                                var html = '<div class="row">';
                                html += '<div class="col-md-3"><strong>Status:</strong> <span class="badge bg-' + (status === 'completed' ? 'success' : (status === 'failed' ? 'danger' : 'warning')) + '">' + status.toUpperCase() + '</span></div>';
                                html += '<div class="col-md-3"><strong>Total Rows:</strong> ' + (response.data.total_rows || 0) + '</div>';
                                html += '<div class="col-md-3"><strong>Success:</strong> <span class="badge bg-success">' + (response.data.total_success || 0) + '</span></div>';
                                html += '<div class="col-md-3"><strong>Failed:</strong> <span class="badge bg-danger">' + (response.data.total_failed || 0) + '</span></div>';
                                html += '</div>';
                                
                                if (response.data.total_duplicates > 0) {
                                    html += '<div class="mt-2"><strong>Duplicates Skipped:</strong> <span class="badge bg-warning">' + response.data.total_duplicates + '</span></div>';
                                }
                                
                                $('#queueStatusContent').html(html);
                                
                                if (status === 'completed' || status === 'failed') {
                                    clearInterval(pollInterval);
                                    if (status === 'completed') {
                                        setTimeout(function() {
                                            location.reload();
                                        }, 2000);
                                    }
                                }
                            }
                        },
                        error: function() {
                            $('#queueStatusContent').html('<span class="text-danger">Error checking status</span>');
                        }
                    });
                }, 3000); // Poll every 3 seconds
            @endif

            function toggleSampleDiv() {
                if ($('#company_id').val()) {
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

