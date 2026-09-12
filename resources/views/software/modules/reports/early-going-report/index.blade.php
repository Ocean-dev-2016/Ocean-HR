@extends('software.layout.app')

@section('title', $modules['title'])

@section('page_leavel_style')
    <style>
        /* Report-specific Scroll Layout */
        #reportContainer {
            max-height: 65vh;
            overflow-y: auto;
            overflow-x: auto;
        }
        #reportContainer table {
            margin-bottom: 0;
        }
        #reportContainer thead th {
            position: sticky;
            top: 0;
            z-index: 10;
            background-color: #eef2f7 !important;
            box-shadow: 0 2px 2px -1px rgba(0,0,0,0.1);
        }
    </style>
@endsection

@php
    $company_id = isset($modules['company_id']) ? $modules['company_id'] : null;
@endphp

@section('content')
    <div class="card">
        <div class="card-header border-bottom">
            <h5 class="card-title mb-3">Early Going Report</h5>
            <form id="filterForm">
                @csrf
                <div class="row pb-2 align-items-center">
                    @if (!$company_id)
                        <div class="col-md-3 mb-2">
                            <select id="company_id" name="company_id" class="form-select select2">
                                <option value="">Select Company</option>
                                {{-- Populate companies if admin --}}
                                @foreach(\App\Models\Company::all() as $company)
                                    <option value="{{ $company->id }}">{{ $company->company_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <input type="hidden" id="company_id" name="company_id" value="{{ $company_id }}" />
                    @endif
                    <div class="col-md-2 mb-2">
                        <select id="department_id" name="department_id" class="form-select select2">
                            <option value="">All Departments</option>
                            @foreach(\App\Models\Department::all() as $dept)
                                <option value="{{ $dept->id }}" data-company-id="{{ $dept->company_id }}">{{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <input type="date" id="start_date" name="start_date" class="form-control"
                            title="Start Date (Overrides Month/Year)" placeholder="Start Date">
                    </div>
                    <div class="col-md-2 mb-2">
                        <input type="date" id="end_date" name="end_date" class="form-control"
                            title="End Date (Overrides Month/Year)" placeholder="End Date">
                    </div>
                    <div class="col-md-2 mb-2">
                        <select id="year" name="year" class="form-select select2">
                            @for($y = date('Y'); $y >= 2020; $y--)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <select id="month" name="month" class="form-select select2">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" {{ date('n') == $m ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <select id="employee_id" name="employee_id" class="form-select select2">
                            <option value="">All Employees</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2 d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-primary flex-grow-1" id="getReportBtn">Get Report</button>

                        <div class="dropdown">
                            <button class="btn btn-sm btn-info waves-effect waves-light text-white" type="button"
                                id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false"
                                style="width: 38px; height: 38px;">
                                <i class="ti ti-settings"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="dropdownMenuButton">
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0);" id="exportBtn">
                                        <i class="fa fa-file-excel me-2 text-success"></i>Export Excel
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0);" id="printBtn">
                                        <i class="fa fa-print me-2 text-primary"></i>Print
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-body">
            <div id="reportContainer" class="table-responsive text-nowrap mt-3">
                <div class="text-center text-muted p-5">Select filters and click "Get Report"</div>
            </div>
        </div>
    </div>
@endsection

@section('page_leavel_script')
    <script>
        $(document).ready(function () {
            $('.select2').select2();

            var originalDepartments = $('#department_id').html();

            $('#company_id').change(function () {
                var companyId = $(this).val();

                // Filter departments
                $('#department_id').html(originalDepartments);
                if (companyId) {
                    $('#department_id option').each(function () {
                        if ($(this).val() !== '' && $(this).data('company-id') != companyId) {
                            $(this).remove();
                        }
                    });
                }
                $('#department_id').val('').trigger('change.select2');

                if (companyId) {
                    $.ajax({
                        url: "{{ route('salary-calculation.get-employees') }}", // Reuse existing endpoint
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            company_id: companyId
                        },
                        success: function (response) {
                            var options = '<option value="">All Employees</option>';
                            if (response.status && response.data) {
                                $.each(response.data, function (deptName, employees) {
                                    options += '<optgroup label="' + deptName + '">';
                                    $.each(employees, function (index, emp) {
                                        options += '<option value="' + emp.id + '">' + emp.full_name + ' (' + emp.employee_code + ')</option>';
                                    });
                                    options += '</optgroup>';
                                });
                            }
                            $('#employee_id').html(options);
                        }
                    });
                } else {
                    $('#employee_id').html('<option value="">All Employees</option>');
                }
            }).trigger('change');

            $('#getReportBtn').click(function () {
                var form = $('#filterForm');
                var btn = $(this);

                if (!$('#company_id').val()) {
                    alert('Please select a company');
                    return;
                }

                btn.prop('disabled', true).text('Loading...');
                $('#reportContainer').html('<div class="text-center p-5"><div class="spinner-border text-primary" role="status"></div></div>');

                $.ajax({
                    url: "{{ route('early-going-report.getReport') }}",
                    type: "POST",
                    data: form.serialize(),
                    success: function (response) {
                        btn.prop('disabled', false).text('Get Report');
                        if (response.status === 'success') {
                            $('#reportContainer').html(response.html);
                        } else {
                            $('#reportContainer').html('<div class="alert alert-danger">' + (response.message || 'Error loading report') + '</div>');
                        }
                    },
                    error: function (xhr) {
                        btn.prop('disabled', false).text('Get Report');
                        var msg = 'An error occurred';
                        if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                        $('#reportContainer').html('<div class="alert alert-danger">' + msg + '</div>');
                    }
                });
            });

            $('#printBtn').click(function () {
                var form = $('#filterForm');
                if (!$('#company_id').val()) { alert('Please select a company'); return; }

                var url = "{{ route('early-going-report.print') }}?" + form.serialize();
                window.open(url, '_blank');
            });

            $('#exportBtn').click(function () {
                var form = $('#filterForm');
                if (!$('#company_id').val()) { alert('Please select a company'); return; }

                var url = "{{ route('early-going-report.export-excel') }}?" + form.serialize();
                window.open(url, '_blank');
            });

            // Auto-populate filters from URL parameters and auto-trigger search
            var urlParams = new URLSearchParams(window.location.search);
            var urlCompanyId = urlParams.get('company_id');
            var urlDate = urlParams.get('date');
            var urlStartDate = urlParams.get('start_date');
            var urlEndDate = urlParams.get('end_date');
            var urlMonth = urlParams.get('month');
            var urlYear = urlParams.get('year');

            if (urlCompanyId) {
                $('#company_id').val(urlCompanyId).trigger('change.select2');
            }
            if (urlStartDate) {
                $('#start_date').val(urlStartDate);
            }
            if (urlEndDate) {
                $('#end_date').val(urlEndDate);
            }
            if (urlDate) {
                $('#start_date').val(urlDate);
                $('#end_date').val(urlDate);
            }
            if (urlMonth) {
                $('#month').val(urlMonth).trigger('change.select2');
            }
            if (urlYear) {
                $('#year').val(urlYear).trigger('change.select2');
            }

            if (urlCompanyId || urlDate || urlStartDate || urlEndDate) {
                setTimeout(function() {
                    $('#getReportBtn').click();
                }, 500);
            }
        });
    </script>
@endsection