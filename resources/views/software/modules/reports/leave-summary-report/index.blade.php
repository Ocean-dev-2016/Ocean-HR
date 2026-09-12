@extends('software.layout.app')

@section('title', 'Leave Summary Report')

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
            border-collapse: separate !important;
            border-spacing: 0;
        }
        
        /* When using border-collapse: separate, we manually add borders to prevent disappearing */
        #reportContainer th, #reportContainer td {
            border-bottom: 1px solid #dee2e6 !important;
            border-right: 1px solid #dee2e6 !important;
            border-top: none !important;
            border-left: none !important;
        }
        #reportContainer th:first-child, #reportContainer td:first-child {
            border-left: 1px solid #dee2e6 !important;
        }
        #reportContainer thead tr:first-child th {
            border-top: 1px solid #dee2e6 !important;
        }

        /* FREEZE THE ENTIRE THEAD VERTICALLY */
        #reportContainer thead {
            position: sticky;
            top: 0;
            z-index: 20;
        }
        #reportContainer thead th {
            background-color: #eef2f7 !important;
        }

        /* Freeze Employee Name column horizontally */
        .frozen-col {
            position: sticky !important;
            left: 0;
            z-index: 10 !important;
            background-color: #f7f9fc !important;
        }
        
        /* Top Left Corner stays above all */
        #reportContainer thead th.frozen-col {
            z-index: 25 !important;
            background-color: #eef2f7 !important;
        }
    </style>
@endsection

@section('content')
    @php
        $company_id = isset($modules['company_id']) ? $modules['company_id'] : null;

        if (!$company_id) {
            $companyCol = 'col-md-2 col-sm-12';
            $deptCol = 'col-md-2 col-sm-12';
            $desigCol = 'col-md-2 col-sm-12';
            $yearCol = 'col-md-1 col-sm-12';
            $monthCol = 'col-md-1 col-sm-12';
            $employeeCol = 'col-md-2 col-sm-12';
            $buttonCol = 'col-md-2 col-sm-12';
        } else {
            $deptCol = 'col-md-2 col-sm-12';
            $desigCol = 'col-md-2 col-sm-12';
            $yearCol = 'col-md-2 col-sm-12';
            $monthCol = 'col-md-2 col-sm-12';
            $employeeCol = 'col-md-2 col-sm-12';
            $buttonCol = 'col-md-2 col-sm-12';
        }
    @endphp
    <div class="card">
        <div class="card-header border-bottom">
            <h5 class="card-title mb-3">Employee Leave Summary Report</h5>
            <form id="filterForm">
                @csrf
                <div class="row align-items-end pb-2">
                    @if (!$company_id)
                        <div class="{{ $companyCol }} mb-2">
                            <label class="form-label">Company</label>
                            <select id="company_id" name="company_id" class="form-select select2">
                                <option value="">Select Company</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->company_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <input type="hidden" id="company_id" name="company_id" value="{{ $company_id }}" />
                    @endif
                    <div class="{{ $yearCol }} mb-2">
                        <label class="form-label">Year</label>
                        <select id="year" name="year" class="form-select select2">
                            @for($y = date('Y'); $y >= 2020; $y--)
                                <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="{{ $monthCol }} mb-2">
                        <label class="form-label">Month</label>
                        <select id="month" name="month" class="form-select select2">
                            <option value="">All Months</option>
                            @foreach(config('constants.months') ?? [
                                1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                                5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                                9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                            ] as $mNum => $mName)
                                @if($mNum !== '')
                                    <option value="{{ $mNum }}">{{ $mName }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="{{ $employeeCol }} mb-2">
                        <label class="form-label">Employee</label>
                        <select id="employee_id" name="employee_id" class="form-select select2">
                            <option value="">All Employees</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->employee_code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="{{ $deptCol }} mb-2">
                        <label class="form-label">Department</label>
                        <select id="department_id" name="department_id" class="form-select select2">
                            <option value="">All Departments</option>
                            @foreach(\App\Models\Department::all() as $dept)
                                <option value="{{ $dept->id }}" data-company-id="{{ $dept->company_id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="{{ $desigCol }} mb-2">
                        <label class="form-label">Designation</label>
                        <select id="designation_id" name="designation_id" class="form-select select2">
                            <option value="">All Designations</option>
                            @foreach(\App\Models\Designation::all() as $desig)
                                <option value="{{ $desig->id }}" data-company-id="{{ $desig->company_id }}">{{ $desig->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="{{ $buttonCol }} mb-2 d-flex gap-2">
                        <button type="button" class="btn btn-primary flex-grow-1" id="getReportBtn" style="height: 38px;">Get Report</button>
                        
                        <div class="dropdown">
                            <button class="btn btn-sm btn-info waves-effect waves-light text-white" type="button"
                                id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false" style="width: 38px; height: 38px;">
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
    $(document).ready(function() {
        $('.select2').select2();

        // Load employees when company changes
        $('#company_id').change(function() {
            var companyId = $(this).val();

            if ($('#department_id').length) {
                $('#department_id option').each(function() {
                    if ($(this).val() == '') {
                        $(this).show();
                    } else if (!companyId || $(this).data('company-id') == companyId) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
                $('#department_id').val('').trigger('change.select2');
            }

            if ($('#designation_id').length) {
                $('#designation_id option').each(function() {
                    if ($(this).val() == '') {
                        $(this).show();
                    } else if (!companyId || $(this).data('company-id') == companyId) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
                $('#designation_id').val('').trigger('change.select2');
            }

            if (companyId) {
                $.ajax({
                    url: "{{ route('salary-calculation.get-employees') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        company_id: companyId,
                        exclude_contractor: 1
                    },
                    success: function(response) {
                        var options = '<option value="">All Employees</option>';
                        if (response.status && response.data) {
                            $.each(response.data, function(deptName, employees) {
                                options += '<optgroup label="' + deptName + '">';
                                $.each(employees, function(index, emp) {
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
        });

        // Trigger change on load if company is pre-selected
        if ($('#company_id').val()) {
            $('#company_id').trigger('change');
        }

        $('#getReportBtn').click(function() {
            var form = $('#filterForm');
            var btn = $(this);
            
            if (!$('#company_id').val()) {
                alert('Please select a company');
                return;
            }

            btn.prop('disabled', true).text('Loading...');
            $('#reportContainer').html('<div class="text-center p-5"><div class="spinner-border text-primary" role="status"></div></div>');

            $.ajax({
                url: "{{ route('leave-summary-report.getReport') }}",
                type: "POST",
                data: form.serialize(),
                success: function(response) {
                    btn.prop('disabled', false).text('Get Report');
                    if (response.status === 'success') {
                        $('#reportContainer').html(response.html);
                    } else {
                        $('#reportContainer').html('<div class="alert alert-danger">' + (response.message || 'Error loading report') + '</div>');
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).text('Get Report');
                    var msg = 'An error occurred';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    $('#reportContainer').html('<div class="alert alert-danger">' + msg + '</div>');
                }
            });
        });

        $('#printBtn').click(function() {
            var form = $('#filterForm');
            if (!$('#company_id').val()) { alert('Please select a company'); return; }
            
            var url = "{{ route('leave-summary-report.print') }}?" + form.serialize();
            window.open(url, '_blank');
        });

        $('#exportBtn').click(function() {
             var form = $('#filterForm');
            if (!$('#company_id').val()) { alert('Please select a company'); return; }
            
            var url = "{{ route('leave-summary-report.export-excel') }}?" + form.serialize();
            window.open(url, '_blank');
        });
    });
</script>
@endsection
