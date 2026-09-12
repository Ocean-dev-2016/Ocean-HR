@extends('software.layout.app')

@section('title', 'Daily Attendance Report')

@section('page_leavel_style')
    <style>
        /* ── Sticky filter card ───────────────────────────────────────── */
        #filterCard {
            position: sticky;
            top: 0;
            z-index: 100;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.10);
            border-radius: 0.375rem;
        }

        /* ── Report table container ───────────────────────────────────── */
        #reportContainer {
            overflow-x: auto;
        }
        #reportContainer table {
            margin-bottom: 0;
        }

        /* Sticky column headers inside the table */
        #reportContainer thead th {
            position: sticky;
            top: 0;
            z-index: 10;
            background-color: #eef2f7 !important;
            box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.1);
        }

        .summary-badge {
            font-size: 0.85rem;
        }
    </style>
@endsection

@php
    $company_id = $modules['company_id'] ?? null;
@endphp

@section('content')

    {{-- ── Sticky Filter Card ─────────────────────────────────────────── --}}
    <div class="card mb-3" id="filterCard">
        <div class="card-header border-bottom">
            <h5 class="card-title mb-3">Daily Attendance Report</h5>

            <form id="filterForm">
                @csrf
                <div class="row pb-2 align-items-end g-2">

                    {{-- Company (hidden for company users, select for admin) --}}
                    @if (!$company_id)
                        <div class="col-md-3 mb-2">
                            <label class="form-label small mb-1">Company</label>
                            <select id="company_id" name="company_id" class="form-select select2">
                                <option value="">Select Company</option>
                                @foreach(\App\Models\Company::all() as $company)
                                    <option value="{{ $company->id }}">{{ $company->company_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <input type="hidden" id="company_id" name="company_id" value="{{ $company_id }}" />
                    @endif

                    {{-- Department --}}
                    <div class="col-md-2 mb-2">
                        <label class="form-label small mb-1">Department</label>
                        <select id="department_id" name="department_id" class="form-select select2">
                            <option value="">Select Department</option>
                        </select>
                    </div>

                    {{-- Shift --}}
                    <div class="col-md-2 mb-2">
                        <label class="form-label small mb-1">Shift</label>
                        <select id="shift_id" name="shift_id" class="form-select select2">
                            <option value="">Select Shift</option>
                        </select>
                    </div>

                    {{-- Employee --}}
                    <div class="col-md-2 mb-2">
                        <label class="form-label small mb-1">Employee</label>
                        <select id="employee_id" name="employee_id" class="form-select select2">
                            <option value="">Select Employee</option>
                        </select>
                    </div>

                    {{-- Status --}}
                    <div class="col-md-2 mb-2">
                        <label class="form-label small mb-1">Status</label>
                        <select id="status" name="status" class="form-select select2">
                            <option value="all">All</option>
                            <option value="present" selected>Present</option>
                            <option value="absent">Absent</option>
                        </select>
                    </div>

                    {{-- Date --}}
                    <div class="col-md-2 mb-2">
                        <label class="form-label small mb-1">Date</label>
                        <input type="date" id="date" name="date" class="form-control"
                               value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}">
                    </div>

                    {{-- Buttons --}}
                    <div class="col-md-12 mb-2 d-flex align-items-center gap-2 justify-content-between">

                        {{-- Summary badges (inline with buttons, hidden until report loads) --}}
                        <div id="summaryBar" class="d-none d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge bg-label-primary summary-badge px-3 py-2">
                                Total: <strong id="totalCount">0</strong>
                            </span>
                            <span class="badge bg-label-success summary-badge px-3 py-2">
                                Present: <strong id="presentCount">0</strong>
                            </span>
                            <span class="badge bg-label-danger summary-badge px-3 py-2">
                                Absent: <strong id="absentCount">0</strong>
                            </span>
                        </div>

                        <div class="d-flex align-items-center gap-2 ms-auto">
                            <button type="button" class="btn btn-primary" id="getReportBtn">
                                <i class="ti ti-eye me-1"></i> Show
                            </button>
                            <button type="button" class="btn btn-secondary" id="resetBtn">
                                <i class="ti ti-refresh me-1"></i> Reset
                            </button>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-info waves-effect waves-light text-white" type="button"
                                        data-bs-toggle="dropdown" aria-expanded="false"
                                        style="width: 38px; height: 38px;">
                                    <i class="ti ti-settings"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
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

                </div>
            </form>
        </div>
    </div>

    {{-- ── Report Result Card ──────────────────────────────────────────── --}}
    <div class="card">
        <div class="card-body p-0">
            <div id="reportContainer" class="table-responsive text-nowrap">
                <div class="text-center text-muted p-5">
                    <i class="ti ti-filter fs-1 d-block mb-2"></i>
                    Select filters and click <strong>Show</strong> to load the report.
                </div>
            </div>
        </div>
    </div>

@endsection

@section('page_leavel_script')
    <script>
        $(document).ready(function () {

            // ── Init Select2 ──────────────────────────────────────────────
            $('.select2').select2();

            // ── Company change: load departments, shifts, employees ────────
            $('#company_id').on('change', function () {
                var companyId = $(this).val();
                loadDepartments(companyId);
                loadShifts(companyId);
                loadEmployees(companyId);
            });

            function loadDepartments(companyId) {
                $('#department_id').html('<option value="">Select Department</option>').trigger('change.select2');
                if (!companyId) return;
                $.ajax({
                    url: "{{ url('api/get-department') }}",
                    type: "POST",
                    data: { _token: "{{ csrf_token() }}", company_id: companyId },
                    success: function (response) {
                        var options = '<option value="">Select Department</option>';
                        if (response.status && response.data) {
                            $.each(response.data, function (i, dept) {
                                options += '<option value="' + dept.id + '">' + dept.department_name + '</option>';
                            });
                        }
                        $('#department_id').html(options).trigger('change.select2');
                    }
                });
            }

            function loadShifts(companyId) {
                $('#shift_id').html('<option value="">Select Shift</option>').trigger('change.select2');
                if (!companyId) return;
                $.ajax({
                    url: "{{ url('api/get-shift') }}",
                    type: "POST",
                    data: { _token: "{{ csrf_token() }}", company_id: companyId },
                    success: function (response) {
                        var options = '<option value="">Select Shift</option>';
                        if (response.status && response.data) {
                            $.each(response.data, function (i, shift) {
                                options += '<option value="' + shift.id + '">' + shift.name + '</option>';
                            });
                        }
                        $('#shift_id').html(options).trigger('change.select2');
                    }
                });
            }

            function loadEmployees(companyId) {
                if (!companyId) {
                    $('#employee_id').html('<option value="">Select Employee</option>').trigger('change.select2');
                    return;
                }
                $.ajax({
                    url: "{{ route('salary-calculation.get-employees') }}",
                    type: "POST",
                    data: { _token: "{{ csrf_token() }}", company_id: companyId },
                    success: function (response) {
                        var options = '<option value="">Select Employee</option>';
                        if (response.status && response.data) {
                            $.each(response.data, function (deptName, employees) {
                                options += '<optgroup label="' + deptName + '">';
                                $.each(employees, function (i, emp) {
                                    options += '<option value="' + emp.id + '">'
                                        + emp.full_name + ' (' + emp.employee_code + ')</option>';
                                });
                                options += '</optgroup>';
                            });
                        }
                        $('#employee_id').html(options).trigger('change.select2');
                    }
                });
            }

            // Trigger on load (for company users with pre-filled company_id)
            var initialCompany = $('#company_id').val();
            if (initialCompany) {
                loadDepartments(initialCompany);
                loadShifts(initialCompany);
                loadEmployees(initialCompany);
            }

            // ── Show Report ───────────────────────────────────────────────
            $('#getReportBtn').on('click', function () {
                var companyId = $('#company_id').val();
                if (!companyId) {
                    toastr ? toastr.error('Please select a company.') : alert('Please select a company.');
                    return;
                }
                if (!$('#date').val()) {
                    toastr ? toastr.error('Please select a date.') : alert('Please select a date.');
                    return;
                }

                var btn = $(this);
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Loading...');
                $('#reportContainer').html(
                    '<div class="text-center p-5"><div class="spinner-border text-primary" role="status"></div></div>'
                );
                $('#summaryBar').addClass('d-none');

                $.ajax({
                    url: "{{ route('daily-attendance-report.getReport') }}",
                    type: "POST",
                    data: $('#filterForm').serialize(),
                    success: function (response) {
                        btn.prop('disabled', false).html('<i class="ti ti-eye me-1"></i> Show');
                        if (response.status === 'success') {
                            $('#reportContainer').html(response.html);
                            // Update summary — use .attr() not .data() for dynamically injected HTML
                            var $table = $('#reportContainer').find('table');
                            if ($table.length) {
                                $('#totalCount').text($table.attr('data-total') || 0);
                                $('#presentCount').text($table.attr('data-present') || 0);
                                $('#absentCount').text($table.attr('data-absent') || 0);
                                $('#summaryBar').removeClass('d-none');
                            }
                        } else {
                            $('#reportContainer').html(
                                '<div class="alert alert-danger m-3">' + (response.message || 'Error loading report.') + '</div>'
                            );
                        }
                    },
                    error: function (xhr) {
                        btn.prop('disabled', false).html('<i class="ti ti-eye me-1"></i> Show');
                        var msg = 'An error occurred.';
                        if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                        $('#reportContainer').html('<div class="alert alert-danger m-3">' + msg + '</div>');
                    }
                });
            });

            // ── Reset ─────────────────────────────────────────────────────
            $('#resetBtn').on('click', function () {
                @if (!$company_id)
                    $('#company_id').val('').trigger('change.select2');
                @endif
                $('#department_id').val('').trigger('change.select2');
                $('#shift_id').val('').trigger('change.select2');
                $('#employee_id').val('').trigger('change.select2');
                $('#status').val('present').trigger('change.select2');
                $('#date').val('{{ date("Y-m-d") }}');
                $('#reportContainer').html(
                    '<div class="text-center text-muted p-5"><i class="ti ti-filter fs-1 d-block mb-2"></i>Select filters and click <strong>Show</strong> to load the report.</div>'
                );
                $('#summaryBar').addClass('d-none');
            });

            // ── Print ─────────────────────────────────────────────────────
            $('#printBtn').on('click', function () {
                var companyId = $('#company_id').val();
                if (!companyId) { alert('Please select a company.'); return; }
                if (!$('#date').val()) { alert('Please select a date.'); return; }
                var url = "{{ route('daily-attendance-report.print') }}?" + $('#filterForm').serialize();
                window.open(url, '_blank');
            });

            // ── Export Excel ──────────────────────────────────────────────
            $('#exportBtn').on('click', function () {
                var companyId = $('#company_id').val();
                if (!companyId) { alert('Please select a company.'); return; }
                if (!$('#date').val()) { alert('Please select a date.'); return; }
                var url = "{{ route('daily-attendance-report.export-excel') }}?" + $('#filterForm').serialize();
                window.open(url, '_blank');
            });

            // ── Auto-fill from URL params & auto-load report ─────────────
            (function () {
                var params    = new URLSearchParams(window.location.search);
                var urlCompany = params.get('company_id');
                var urlDate    = params.get('date');
                var urlStatus  = params.get('status');

                // Set date and status first
                if (urlDate)   $('#date').val(urlDate);
                if (urlStatus) $('#status').val(urlStatus).trigger('change.select2');

                // Determine company_id — from URL param OR from hidden input (company user)
                var resolvedCompany = urlCompany || $('#company_id').val();

                if (urlCompany) {
                    // Admin: set the select dropdown
                    $('#company_id').val(urlCompany).trigger('change');
                }

                // Auto-trigger report if we have date + company
                if (resolvedCompany && urlDate) {
                    // Wait for employee dropdown to load (if needed), then click
                    setTimeout(function () {
                        $('#getReportBtn').trigger('click');
                    }, urlCompany ? 700 : 300);
                }
            })();

        });
    </script>
@endsection
