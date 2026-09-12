@extends('software.layout.app')
@php
    $i = 0;
    $page_title = $modules['title'] ?? 'Attendance Muster Report';
    $folder_path = $modules['folder_path'] ?? null;
    $route = $modules['route'] ?? null;
    $company_id = $modules['company_id'] ?? null;
    $authLoginUserDetail = $modules['authLoginUserDetail'] ?? null;
    $loginUserId = $authLoginUserDetail?->id ?? null;
@endphp
@section('title', 'Attendance Muster Report')

@section('page_leavel_style')
    <link rel="stylesheet" href="{{ asset('software/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('software/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('software/custom/custom.css') }}">
@endsection

@section('content')
    <div class="row my-3">
        <div class="col-md-12 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="filter-card">
                        <div class="row g-3 align-items-end">
                            @if (!$company_id)
                                <div class="col-md-2 col-sm-12">
                                    <div class="form-group">
                                        <label class="form-label">Filter by Company</label>
                                        <select id="company_id" name="company_id"
                                            class="form-control search_by_company select2 select_filter"
                                            data-append="search_by_company" showBranch="branchDiv">
                                            <option value="">Filter by Company</option>
                                        </select>
                                    </div>
                                </div>
                            @else
                                <input type="hidden" id="company_id" value="{{ $company_id }}">
                            @endif

                            <div class="col-md-2 col-sm-12 branchDiv" style="display:none;">
                                <div class="form-group">
                                    <label class="form-label">Filter by Branch</label>
                                    <select id="branch" name="branch"
                                        class="form-control search_by_branch select2 select_filter"
                                        data-append="search_by_branch">
                                        <option value="">Filter by Branch</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-2 col-sm-12">
                                <div class="form-group">
                                    <label class="form-label">Filter by Department</label>
                                    <select id="department" name="department"
                                        class="form-control search_by_department select2 select_filter"
                                        data-append="search_by_department">
                                        <option value="">Filter by Department</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-2 col-sm-12">
                                <div class="form-group">
                                    <label class="form-label">Filter by Employee</label>
                                    <select id="employee_id" name="employee_id"
                                        class="form-control search_by_employee select2 select_filter"
                                        data-append="search_by_employee">
                                        <option value="">Filter by Employee</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="form-label">Filter by Month</label>
                                    <div class="custom-month-picker-wrapper">
                                        <input type="text" name="followup_date" id="month_picker"
                                            class="form-control table_filter month-picker-input" readonly
                                            placeholder="Select Month">
                                        <input type="hidden" id="month_picker_value" value="{{ date('Y-m') }}">
                                        <div class="custom-month-picker-dropdown" id="month_picker_dropdown">
                                            <div class="month-picker-header">
                                                <button type="button" class="month-picker-year-btn"
                                                    id="year_display">2025</button>
                                            </div>
                                            <div class="month-picker-grid" id="month_grid">
                                                <button type="button" class="month-btn" data-month="01">Jan</button>
                                                <button type="button" class="month-btn" data-month="02">Feb</button>
                                                <button type="button" class="month-btn" data-month="03">Mar</button>
                                                <button type="button" class="month-btn" data-month="04">Apr</button>
                                                <button type="button" class="month-btn" data-month="05">May</button>
                                                <button type="button" class="month-btn" data-month="06">Jun</button>
                                                <button type="button" class="month-btn" data-month="07">Jul</button>
                                                <button type="button" class="month-btn" data-month="08">Aug</button>
                                                <button type="button" class="month-btn" data-month="09">Sep</button>
                                                <button type="button" class="month-btn" data-month="10">Oct</button>
                                                <button type="button" class="month-btn" data-month="11">Nov</button>
                                                <button type="button" class="month-btn" data-month="12">Dec</button>
                                            </div>
                                            <div class="month-picker-footer">
                                                <a href="javascript:void(0)" class="month-picker-link"
                                                    id="clear_month">Clear</a>
                                                <a href="javascript:void(0)" class="month-picker-link" id="this_month">This
                                                    month</a>
                                            </div>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted d-none" id="month_range_info"></small>
                                </div>
                            </div>

                            <div class="col-md-6 d-flex align-items-end gap-2">
                                <button type="button" class="btn btn-primary btn-report" id="view_report_btn">
                                    <i class="fa fa-eye me-1"></i> Show
                                </button>
                                <a href="javascript:void(0)" class="btn btn-secondary btn-report"
                                    id="view_detail_report_btn">
                                    <i class="fa fa-arrow-left me-1"></i> Detailed Report
                                </a>
                                <button type="button" class="btn btn-success btn-report" id="export_excel_btn">
                                    <i class="fa fa-file-excel-o me-1"></i> Export
                                </button>
                                <button type="button" class="btn btn-warning btn-report" id="print_btn">
                                    <i class="fa fa-print me-1"></i> Print
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row my-3">
        <div class="col-md-12 mb-5">
            <div class="attendance-wrapper shadow-sm">
                <div class="attendance-header">MUSTER REPORT <span id="report_month"></span></div>

                <div id="att_scroll_container">

                    {{-- FROZEN HEADER --}}
                    <div id="header_container">
                        <div class="att-fixed-left" id="left_header_col">
                            <table class="attendance-table">
                                <thead>
                                    <tr>
                                        <th class="att-col-sr">Sr No</th>
                                        <th class="att-col-name">Employee Name</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                        <div class="att-middle" id="middle_header_col">
                            <table class="attendance-table">
                                <thead>
                                    <tr id="days_head">
                                        <th colspan="31" style="min-width:75px;">-</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                        <div class="att-fixed-right" id="right_header_col">
                            <table class="attendance-table">
                                <thead>
                                    <tr id="right_head_row">
                                        <th class="att-col-summary">P</th>
                                        <th class="att-col-summary">A</th>
                                        <th class="att-col-summary">PL</th>
                                        <th class="att-col-summary">SL</th>
                                        <th class="att-col-summary">DL</th>
                                        <th class="att-col-summary">C-off</th>
                                        <th class="att-col-summary">LWP</th>
                                        <th class="att-col-summary">H</th>
                                        <th class="att-col-summary">WO</th>
                                        <th class="att-col-summary">Total</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>

                    {{-- BODY ROWS --}}
                    <div id="body_container">
                        <div class="att-fixed-left" id="left_body_col">
                            <table class="attendance-table">
                                <tbody id="left_tbody"></tbody>
                            </table>
                        </div>
                        <div class="att-middle" id="middle_body_col">
                            <table class="attendance-table">
                                <tbody id="middle_tbody"></tbody>
                            </table>
                        </div>
                        <div class="att-fixed-right" id="right_body_col">
                            <table class="attendance-table">
                                <tbody id="right_tbody"></tbody>
                            </table>
                        </div>
                    </div>

                </div>{{-- end att_scroll_container --}}
            </div>
        </div>
    </div>

@endsection

@section('page_leavel_script')
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="{{ asset('software/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endsection
@push('page_scripts')
    <script>
        // Custom Month Picker Implementation
        $(function () {
            var currentDate = moment();
            var currentMonth = currentDate.format('YYYY-MM');
            var currentYear = currentDate.year();
            var currentMonthNum = currentDate.month() + 1;

            // Calculate restrictions
            var minYear = currentYear - 2;
            var maxYear = currentYear;
            var maxMonth = currentMonthNum;

            var selectedYear = currentYear;
            var selectedMonth = currentMonthNum;

            // Initialize display
            updateMonthDisplay();
            updateMonthPicker(); // Initialize picker state

            // Toggle dropdown
            $('#month_picker').on('click', function (e) {
                e.stopPropagation();
                $('#month_picker_dropdown').toggleClass('show');
                updateMonthPicker();
            });

            // Close dropdown when clicking outside
            $(document).on('click', function (e) {
                if (!$(e.target).closest('.custom-month-picker-wrapper').length) {
                    $('#month_picker_dropdown').removeClass('show');
                }
            });

            // Year navigation - cycle through available years
            $('#year_display').on('click', function () {
                selectedYear++;
                if (selectedYear > maxYear) {
                    selectedYear = minYear;
                }
                // Reset selected month when year changes
                selectedMonth = null;
                updateMonthPicker();
            });

            // Month selection
            $(document).on('click', '.month-btn:not(.disabled)', function () {
                selectedMonth = parseInt($(this).data('month'));
                updateMonthDisplay();
                updateMonthPicker(); // Update to highlight selected
                $('#month_picker_dropdown').removeClass('show');
            });

            // Clear button
            $('#clear_month').on('click', function () {
                $('#month_picker').val('');
                $('#month_picker_value').val('');
                selectedYear = currentYear;
                selectedMonth = currentMonthNum;
                updateMonthPicker();
            });

            // This month button
            $('#this_month').on('click', function () {
                selectedYear = currentYear;
                selectedMonth = currentMonthNum;
                updateMonthDisplay();
                $('#month_picker_dropdown').removeClass('show');
            });

            // Update month picker display
            function updateMonthPicker() {
                $('#year_display').text(selectedYear);

                $('.month-btn').each(function () {
                    var monthNum = parseInt($(this).data('month'));
                    var isDisabled = false;

                    // Check if month is in the past (more than 2 years ago)
                    if (selectedYear < minYear) {
                        isDisabled = true;
                    } else if (selectedYear === minYear) {
                        // Check if it's before the allowed start month
                        var minMonth = moment().subtract(2, 'years').month() + 1;
                        if (monthNum < minMonth) {
                            isDisabled = true;
                        }
                    }

                    // Check if month is in the future
                    if (selectedYear > maxYear) {
                        isDisabled = true;
                    } else if (selectedYear === maxYear) {
                        if (monthNum > maxMonth) {
                            isDisabled = true;
                        }
                    }

                    if (selectedYear > maxYear) {
                        isDisabled = true;
                    }

                    if (isDisabled) {
                        $(this).addClass('disabled');
                    } else {
                        $(this).removeClass('disabled');
                    }

                    // Mark selected month (check against stored value)
                    var storedValue = $('#month_picker_value').val();
                    if (storedValue) {
                        var storedMoment = moment(storedValue + '-01');
                        if (storedMoment.year() === selectedYear && storedMoment.month() + 1 === monthNum) {
                            $(this).addClass('selected');
                        } else {
                            $(this).removeClass('selected');
                        }
                    } else {
                        $(this).removeClass('selected');
                    }
                });
            }

            // Update input display
            function updateMonthDisplay() {
                if (selectedYear && selectedMonth) {
                    var monthMoment = moment(selectedYear + '-' + String(selectedMonth).padStart(2, '0') + '-01');
                    var displayText = monthMoment.format('MMMM, YYYY');
                    $('#month_picker').val(displayText);
                    $('#month_picker_value').val(monthMoment.format('YYYY-MM'));
                    updateMonthRangeInfo();
                }
            }

            // Initialize on load
            updateMonthPicker();
        });

        // Function to update month range info display
        function updateMonthRangeInfo() {
            var monthValue = $('#month_picker_value').val();
            if (monthValue) {
                var startDate = moment(monthValue + '-01').format('DD/MM/YYYY');
                var endDate = moment(monthValue + '-01').endOf('month').format('DD/MM/YYYY');
                var monthName = moment(monthValue + '-01').format('MMMM YYYY');
                $('#month_range_info').text('Range: ' + startDate + ' to ' + endDate + ' (' + monthName + ')');
            } else {
                $('#month_range_info').text('');
            }
        }


        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
        });

        $('#view_report_btn').click(function () {
            let company_id = $('#company_id').val();
            let employee_id = $('#employee_id').val();
            let department_id = $('#department').val();
            let monthValue = $('#month_picker_value').val();

            // Validate month selection
            if (!monthValue) {
                alert('Please select a month to view the report.');
                return;
            }

            // Validate against restrictions
            var selectedDate = moment(monthValue + '-01');
            var minDate = moment().subtract(2, 'years');
            var maxYear = parseInt(moment().format('YYYY'));
            var currentDate = moment();
            var maxDate = currentDate;

            if (selectedDate.isBefore(minDate, 'month')) {
                alert('You can only select months from the past 2 years.');
                $('#month_picker_value').val(moment().format('YYYY-MM'));
                return;
            }

            if (selectedDate.isAfter(maxDate, 'month')) {
                alert('You cannot select future months. Maximum allowed: ' + maxDate.format('MMMM YYYY'));
                $('#month_picker_value').val(maxDate.format('YYYY-MM'));
                return;
            }

            // Convert YYYY-MM format to date range (first day to last day of month)
            var startDate = moment(monthValue + '-01').format('DD/MM/YYYY');
            var endDate = moment(monthValue + '-01').endOf('month').format('DD/MM/YYYY');
            let followup_date = startDate + ' to ' + endDate;

            // Validate company_id
            if (!company_id) {
                toastr.error('Please first select the company', 'Validation Error');
                return;
            }

            // Show inline loader on attendance wrapper
            var $attendanceWrapper = $('.attendance-wrapper');
            InlineLoader.show($attendanceWrapper);

            // Show button loader
            InlineLoader.showButton('#view_report_btn', '<i class="fa fa-eye me-1"></i> Show');

            $.ajax({
                url: "{{ route('attendance-report.getMusterReport') }}",
                type: "POST",
                data: {
                    company_id,
                    employee_id,
                    department_id,
                    followup_date
                },
                success: function (res) {
                    // Hide inline loaders
                    InlineLoader.hide($attendanceWrapper);
                    InlineLoader.hideButton('#view_report_btn');

                    if (res.html) {
                        // Render using server-side HTML
                        renderAttendanceTableFromHTML(res.html);
                    } else if (res.data && res.data.html) {
                        // Check nested data structure if wrapper exists
                        renderAttendanceTableFromHTML(res.data.html);
                    } else {
                        // Fallback or error if HTML not provided
                        console.error('Server did not return HTML');
                        alert('Server response missing HTML data.');
                    }
                },
                error: function (xhr, status, error) {
                    // Hide inline loaders
                    InlineLoader.hide($attendanceWrapper);
                    InlineLoader.hideButton('#view_report_btn');

                    console.error('AJAX Error:', xhr);
                    let errorMessage = 'Something went wrong!';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    alert(errorMessage);
                }
            });
        });

        // Render table from controller-generated HTML
        function renderAttendanceTableFromHTML(html) {
            try {
                if (html.daysHead) $('#days_head').html(html.daysHead);
                if (html.rightHead) $('#right_head_row').closest('thead').html(html.rightHead);
                if (html.leftBody) $('#left_tbody').html(html.leftBody);
                if (html.scrollableBody) $('#middle_tbody').html(html.scrollableBody);
                if (html.rightBody) $('#right_tbody').html(html.rightBody);
                if (html.monthYear) $('#report_month').text(' - ' + html.monthYear);

                syncColumnWidths();
            } catch (error) {
                console.error('Error rendering table:', error);
                alert('Error rendering attendance table.');
            }
        }

        // Match header column widths to body column widths
        function syncColumnWidths() {
            setTimeout(function () {
                var maxW;

                // Left columns
                $('#left_header_col thead th').each(function (i) {
                    var hdrW = $(this).outerWidth();
                    var bodyW = $('#left_tbody tr:first td').eq(i).outerWidth();
                    maxW = Math.max(hdrW || 0, bodyW || 0);
                    if (maxW > 0) {
                        $(this).css({ 'min-width': maxW + 'px', 'width': maxW + 'px' });
                        $('#left_tbody td:nth-child(' + (i + 1) + ')').css({ 'min-width': maxW + 'px', 'width': maxW + 'px' });
                    }
                });

                // Right columns
                $('#right_header_col thead th').each(function (i) {
                    var hdrW = $(this).outerWidth();
                    var bodyW = $('#right_tbody tr:first td').eq(i).outerWidth();
                    maxW = Math.max(hdrW || 0, bodyW || 0);
                    if (maxW > 0) {
                        $(this).css({ 'min-width': maxW + 'px', 'width': maxW + 'px' });
                        $('#right_tbody td:nth-child(' + (i + 1) + ')').css({ 'min-width': maxW + 'px', 'width': maxW + 'px' });
                    }
                });

                // Middle columns
                $('#middle_header_col thead th').each(function (i) {
                    var hdrW = $(this).outerWidth();
                    var bodyW = $('#middle_tbody tr:first td').eq(i).outerWidth();
                    maxW = Math.max(hdrW || 0, bodyW || 0);
                    if (maxW > 0) {
                        $(this).css({ 'min-width': maxW + 'px', 'width': maxW + 'px' });
                        $('#middle_tbody td:nth-child(' + (i + 1) + ')').css({ 'min-width': maxW + 'px', 'width': maxW + 'px' });
                    }
                });
            }, 250);
        }

        // Export Excel
        $('#export_excel_btn').click(function () {
            let company_id = $('#company_id').val();
            let employee_id = $('#employee_id').val();
            let monthValue = $('#month_picker_value').val();
            let branch_id = $('#branch').val();
            let department_id = $('#department').val();

            if (!company_id) {
                alert('Please select a company');
                return;
            }

            if (!monthValue) {
                alert('Please select a month');
                return;
            }

            // Convert YYYY-MM format to date range (first day to last day of month)
            var startDate = moment(monthValue + '-01').format('DD/MM/YYYY');
            var endDate = moment(monthValue + '-01').endOf('month').format('DD/MM/YYYY');
            let followup_date = startDate + ' to ' + endDate;

            // Build query string
            let params = new URLSearchParams({
                company_id: company_id,
                followup_date: followup_date,
                is_muster: true // IMPORTANT: Flag for muster export
            });

            if (employee_id) params.append('employee_id', employee_id);
            if (branch_id) params.append('branch_id', branch_id);
            if (department_id) params.append('department_id', department_id);

            window.location.href = "{{ route('attendance-report.export.excel') }}?" + params.toString();
        });

        // Export Print
        $('#print_btn').click(function () {
            let company_id = $('#company_id').val();
            let employee_id = $('#employee_id').val();
            let monthValue = $('#month_picker_value').val();
            let branch_id = $('#branch').val();
            let department_id = $('#department').val();

            if (!company_id) {
                alert('Please select a company');
                return;
            }

            if (!monthValue) {
                alert('Please select a month');
                return;
            }

            // Convert YYYY-MM format to date range (first day to last day of month)
            var startDate = moment(monthValue + '-01').format('DD/MM/YYYY');
            var endDate = moment(monthValue + '-01').endOf('month').format('DD/MM/YYYY');
            let followup_date = startDate + ' to ' + endDate;

            // Build query string
            let params = new URLSearchParams({
                company_id: company_id,
                followup_date: followup_date,
                is_muster: true // IMPORTANT: Flag for muster print
            });

            if (employee_id) params.append('employee_id', employee_id);
            if (branch_id) params.append('branch_id', branch_id);
            if (department_id) params.append('department_id', department_id);

            window.open("{{ route('attendance-report.export.print') }}?" + params.toString(), '_blank');
        });


        // View Detail Report
        $('#view_detail_report_btn').click(function () {
            let company_id = $('#company_id').val();
            let employee_id = $('#employee_id').val();
            let monthValue = $('#month_picker_value').val();
            // Optional: Branch/Dept if needed

            // Redirect with params
            let params = new URLSearchParams();
            if (company_id) params.append('company_id', company_id);
            if (employee_id) params.append('employee_id', employee_id);
            if (monthValue) params.append('month', monthValue);

            window.location.href = "{{ route('attendance-report.index') }}?" + params.toString();
        });

        // Auto-fill filters from URL params with robust waiting
        $(function () {
            const urlParams = new URLSearchParams(window.location.search);
            const companyId = urlParams.get('company_id');
            const employeeId = urlParams.get('employee_id');
            const month = urlParams.get('month');

            if (month) {
                $('#month_picker_value').val(month);
                if (typeof moment !== 'undefined') {
                    $('#month_picker').val(moment(month + '-01').format('MMMM, YYYY'));
                }
            }

            if (companyId) {
                // Set company and trigger change to load dependents
                $('#company_id').val(companyId).trigger('change');

                // Poll for employee dropdown population
                // We wait until option count > 1 (assuming default "Filter by Employee" is present)
                let attempts = 0;
                let maxAttempts = 50; // 5 seconds max

                let checkExist = setInterval(function () {
                    attempts++;
                    // Check if employee dropdown has loaded options
                    // Or if we decided we don't need employee ID (just company report?)
                    // Usually dependents load via AJAX.

                    if ($('#employee_id option').length > 1) {
                        clearInterval(checkExist);
                        if (employeeId) {
                            $('#employee_id').val(employeeId).trigger('change');
                        }
                        // Click show
                        if (month) {
                            $('#view_report_btn').click();
                        }
                    } else if (attempts >= maxAttempts) {
                        clearInterval(checkExist);
                        console.log("Employee dropdown load timeout");
                    }
                }, 100); // check every 100ms
            }
        });
    </script>
    @if (!$company_id)
        @include('utils.getCompany')
    @endif
    @include('utils.getEmployee')
    @include('utils.getDepartment')
    @include('utils.getBranch')

@endpush