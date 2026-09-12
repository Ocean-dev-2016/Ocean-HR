@extends('software.layout.app')
@php
    $i = 0;
    $page_title = $modules['title'] ?? 'Attendance Report';
    $folder_path = $modules['folder_path'] ?? null;
    $route = $modules['route'] ?? null;
    $company_id = $modules['company_id'] ?? null;
    $authLoginUserDetail = $modules['authLoginUserDetail'] ?? null;
    $loginUserId = $authLoginUserDetail?->id ?? null;
@endphp
@section('title', 'Daily Attendance Report')

@section('page_leavel_style')
    <link rel="stylesheet" href="{{ asset('software/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('software/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('software/custom/custom.css') }}">
    <link rel="stylesheet" href="{{ asset('software/vendor/libs/flatpickr/flatpickr.css') }}" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
    <style>
        .flatpickr-monthSelect-theme-light {
            background: #fff;
        }

        .flatpickr-container-custom .flatpickr-calendar {
            width: 260px !important;
        }

        .flatpickr-footer {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            border-top: 1px solid #eee;
        }

        .flatpickr-footer a {
            font-size: 13px;
            text-decoration: none;
            color: #0d6efd;
            cursor: pointer;
        }

        .flatpickr-footer a:hover {
            text-decoration: underline;
        }
    </style>
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
                                    <div class="flatpickr-container-custom">
                                        <input type="text" id="month_picker" class="form-control" readonly
                                            placeholder="Select Month" style="background-color: #fff;">
                                        <input type="hidden" name="followup_date" id="month_picker_value"
                                            value="{{ date('Y-m') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 d-flex align-items-end gap-2">
                                <button type="button" class="btn btn-primary btn-report" id="view_report_btn">
                                    <i class="fa fa-eye me-1"></i> Show
                                </button>
                                <a href="javascript:void(0)" class="btn btn-info btn-report" id="view_muster_report_btn">
                                    <i class="fa fa-list-alt me-1"></i> Muster Report
                                </a>
                                <button type="button" class="btn btn-warning btn-report " id="export_excel_btn">
                                    <i class="fa fa-download me-1"></i> Export Excel
                                </button>
                                <button type="button" class="btn btn-warning btn-report" id="export_print_btn">
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
                <div class="attendance-header">ATTENDANCE REPORT <span id="report_month"></span></div>

                {{-- One container: scrolls both X and Y --}}
                <div id="att_scroll_container">

                    {{-- FROZEN HEADER (sticky top inside scroll container) --}}
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
                                        <th class="att-col-summary">Present</th>
                                        <th class="att-col-summary">Week Off</th>
                                        <th class="att-col-summary">Half Days</th>
                                        <th class="att-col-summary">Leave</th>
                                        <th class="att-col-summary">Absent</th>
                                        <th class="att-col-summary">Holiday</th>
                                        <th class="att-col-summary" style="min-width:120px;">Days Compensated</th>
                                        <th class="att-col-summary" style="min-width:130px;">Total Working Hour</th>
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
    <!-- Modal -->

    {{-- <div id="attendanceModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>In–Out Details</h3>
                <span class="close">&times;</span>
            </div>
            <div id="modalBody"></div>
        </div>
    </div> --}}
    <div id="attendanceModal" class="modal fade" tabindex="-1" aria-labelledby="attendanceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content shadow-sm border-0">
                <!-- Modal Header -->
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">In–Out Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <!-- Modal Body -->
                <div class="modal-body p-4" id="modalBody"></div>
            </div>
        </div>
    </div>

@endsection

@section('page_leavel_script')
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="{{ asset('software/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset('software/vendor/libs/flatpickr/flatpickr.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>
@endsection
@push('page_scripts')
    <script>
        // Flatpickr Month Picker Implementation
        $(function () {
            var defaultDate = "{{ date('Y-m') }}";

            $("#month_picker").flatpickr({
                plugins: [
                    new monthSelectPlugin({
                        shorthand: true, // defaults to false
                        dateFormat: "F, Y", // defaults to "F Y"
                        altFormat: "F, Y", // defaults to "F Y"
                        theme: "light" // defaults to "light"
                    })
                ],
                defaultDate: defaultDate,
                disableMobile: "true",
                onReady: function (d, dObj, fp) {
                    // Inject Year Select
                    const yearInput = fp.calendarContainer.querySelector(".flatpickr-monthSelect-month"); // Locate header area (adjust selector if needed, but standard is .flatpickr-current-month or .flatpickr-monthSelect-month depending on plugin)
                    // For monthSelect plugin, the year is usually inside .flatpickr-monthSelect-month or similar container
                    // Let's find the current year element or append next to month navigation

                    // Actually, simpler approach: Modify the year input/container provided by flatpickr or monthSelect plugin
                    // The monthSelect plugin usually renders "Month Year" or similar.
                    // Let's create a select and prepend/append it.

                    const flatpickrYearContainer = fp.calendarContainer.querySelector(".numInputWrapper");
                    if (flatpickrYearContainer) {
                        flatpickrYearContainer.style.display = 'none'; // Hide default year spinner

                        const yearSelect = document.createElement("select");
                        yearSelect.className = "flatpickr-year-select-custom form-select form-select-sm my-2 mx-auto";
                        yearSelect.style.width = "auto";
                        yearSelect.style.display = "inline-block";

                        const currentYear = new Date().getFullYear();
                        for (let i = currentYear; i >= 1970; i--) {
                            const option = document.createElement("option");
                            option.value = i;
                            option.text = i;
                            if (i === parseInt(fp.currentYear)) {
                                option.selected = true;
                            }
                            yearSelect.appendChild(option);
                        }

                        yearSelect.addEventListener("change", function (e) {
                            fp.changeYear(parseInt(e.target.value));
                        });

                        // Insert before the calendar grid or replacing the year section
                        const rContainer = fp.calendarContainer.querySelector(".flatpickr-rContainer") || fp.calendarContainer;
                        rContainer.insertBefore(yearSelect, rContainer.firstChild);

                        // Keep select synced if user somehow changes year via other means 
                        fp.config.onYearChange = [function (selectedDates, dateStr, instance) {
                            yearSelect.value = instance.currentYear;
                        }];
                    }

                    // Add Custom Footer
                    const footer = document.createElement("div");
                    footer.classList.add("flatpickr-footer");
                    footer.innerHTML = '<a href="javascript:void(0)" id="fp-clear">Clear</a><a href="javascript:void(0)" id="fp-today">This month</a>';
                    // Check if footer already exists
                    if (!fp.calendarContainer.querySelector('.flatpickr-footer')) {
                        fp.calendarContainer.appendChild(footer);
                    }

                    // Clear Button
                    footer.querySelector("#fp-clear").addEventListener("click", function () {
                        fp.clear();
                        $('#month_picker_value').val('');
                        $('#month_range_info').text('');
                    });

                    // This Month Button
                    footer.querySelector("#fp-today").addEventListener("click", function () {
                        const today = new Date();
                        fp.setDate(today);
                        // Also update year select if needed
                        const yearSelect = fp.calendarContainer.querySelector(".flatpickr-year-select-custom");
                        if (yearSelect) yearSelect.value = today.getFullYear();

                        updateHiddenValue(fp.selectedDates[0], fp);
                    });

                    // Initial Value Set
                    if (defaultDate) {
                        $('#month_picker_value').val(moment(defaultDate).format('YYYY-MM'));
                    }
                },
                onChange: function (selectedDates, dateStr, instance) {
                    updateHiddenValue(selectedDates[0], instance);
                }
            });

            function updateHiddenValue(dateObj, instance) {
                if (dateObj) {
                    let formattedDate = instance.formatDate(dateObj, "Y-m");
                    $('#month_picker_value').val(formattedDate);

                    // Update Range Info if needed (optional based on existing feature)
                    var monthValue = formattedDate;
                    var startDate = moment(monthValue + '-01').format('DD/MM/YYYY');
                    var endDate = moment(monthValue + '-01').endOf('month').format('DD/MM/YYYY');
                    var monthName = moment(monthValue + '-01').format('MMMM YYYY');
                    $('#month_range_info').text('Range: ' + startDate + ' to ' + endDate + ' (' + monthName + ')');
                } else {
                    $('#month_picker_value').val('');
                    $('#month_range_info').text('');
                }
            }
        });
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

            // DEBUG: Log the values being sent
            console.log('=== ATTENDANCE REPORT DEBUG ===');
            console.log('Company ID (from dropdown):', company_id);
            console.log('Company Name:', $('#company_id option:selected').text());
            console.log('Employee ID:', employee_id);
            console.log('Month Value:', monthValue);
            console.log('==============================');

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
            // Show inline loader if available
            var $attendanceWrapper = $('.attendance-wrapper');
            if (typeof InlineLoader !== 'undefined') {
                InlineLoader.show($attendanceWrapper);
                InlineLoader.showButton('#view_report_btn', '<i class="fa fa-eye me-1"></i> Show');
            } else {
                $attendanceWrapper.css('opacity', '0.5');
                $('#view_report_btn').prop('disabled', true).text('Loading...');
            }

            // Clear existing table data to avoid confusion
            $('#left_tbody').html('<tr><td colspan="2" class="text-center p-3">Loading...</td></tr>');
            $('#middle_tbody').html('<tr><td colspan="31" class="text-center p-3">Loading...</td></tr>');
            $('#right_tbody').html('<tr><td colspan="8" class="text-center p-3">Loading...</td></tr>');
            $.ajax({
                url: "{{ route('attendance-report.getReport') }}",
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

        // Sync horizontal scroll: not needed - single container
        function syncHorizontalScroll() { }

        // Match header column widths to body column widths
        function syncColumnWidths() {
            setTimeout(function () {

                // ── LEFT: set both header th AND body td to same max width ──
                var maxW;
                $('#left_header_col thead th').each(function (i) {
                    var hdrW = $(this).outerWidth();
                    var bodyW = $('#left_tbody tr:first td').eq(i).outerWidth();
                    maxW = Math.max(hdrW, bodyW);
                    if (maxW > 0) {
                        $(this).css({ 'min-width': maxW + 'px', 'width': maxW + 'px' });
                        $('#left_tbody td:nth-child(' + (i + 1) + ')').css({ 'min-width': maxW + 'px', 'width': maxW + 'px' });
                    }
                });

                // ── RIGHT: same ──
                $('#right_header_col thead th').each(function (i) {
                    var hdrW = $(this).outerWidth();
                    var bodyW = $('#right_tbody tr:first td').eq(i).outerWidth();
                    maxW = Math.max(hdrW, bodyW);
                    if (maxW > 0) {
                        $(this).css({ 'min-width': maxW + 'px', 'width': maxW + 'px' });
                        $('#right_tbody td:nth-child(' + (i + 1) + ')').css({ 'min-width': maxW + 'px', 'width': maxW + 'px' });
                    }
                });

                // ── MIDDLE: same ──
                $('#middle_header_col thead th').each(function (i) {
                    var hdrW = $(this).outerWidth();
                    var bodyW = $('#middle_tbody tr:first td').eq(i).outerWidth();
                    maxW = Math.max(hdrW, bodyW);
                    if (maxW > 0) {
                        $(this).css({ 'min-width': maxW + 'px', 'width': maxW + 'px' });
                        $('#middle_tbody td:nth-child(' + (i + 1) + ')').css({ 'min-width': maxW + 'px', 'width': maxW + 'px' });
                    }
                });

                // ── Sync row heights: make header row same height as body first row ──
                var bodyRowH = $('#left_tbody tr:first').outerHeight();
                if (bodyRowH > 0) {
                    $('#header_container table thead tr').css('height', bodyRowH + 'px');
                }

            }, 250);
        }

        // Function to show punch details modal
        function showPunchDetails(employeeId, date) {
            if (!employeeId) {
                alert('Employee ID not found. Please try again.');
                return;
            }

            // Show loading state
            $('#modalBody').html(
                '<div style="text-align: center; padding: 20px;"><i class="fa fa-spinner fa-spin"></i> Loading...</div>'
            );

            // Show Bootstrap modal
            var modal = new bootstrap.Modal(document.getElementById('attendanceModal'));
            modal.show();

            // Fetch HTML from controller
            $.ajax({
                url: "{{ route('attendance-report.getPunchDetails') }}",
                type: "POST",
                data: {
                    employee_id: employeeId,
                    attendance_date: date
                },
                success: function (res) {
                    if (res.status && res.html) {
                        $('#modalBody').html(res.html);
                    } else {
                        $('#modalBody').html('<div style="padding: 20px; text-align: center;">' + (res
                            .message || 'No punch details found.') + '</div>');
                    }
                },
                error: function (xhr) {
                    let errorMessage = 'Something went wrong while loading punch details.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    $('#modalBody').html(
                        '<div style="padding: 20px; text-align: center; color: red;">' +
                        errorMessage + '</div>');
                }
            });
        }

        // Modal for all punches - Fetch HTML from controller (existing click handler for .more-btn)
        $(document).on('click', '.more-btn', function (e) {
            e.preventDefault();
            let employeeId = $(this).attr('data-employee-id');
            let date = $(this).attr('data-date') || '';
            showPunchDetails(employeeId, date);
        });

        // Helper functions
        function getMinutesDiff(inTime, outTime) {
            if (!inTime || !outTime) return 0;
            let start = new Date(`1970-01-01T${inTime}`);
            let end = new Date(`1970-01-01T${outTime}`);
            if (end < start) end.setDate(end.getDate() + 1); // overnight
            return Math.floor((end - start) / 60000);
        }

        function minutesToHHMMSS(minutes) {
            let h = Math.floor(minutes / 60);
            let m = minutes % 60;
            let s = 0;
            return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
        }

        // Export Excel
        $('#export_excel_btn').click(function () {
            let company_id = $('#company_id').val();
            let employee_id = $('#employee_id').val();
            let monthValue = $('#month_picker_value').val();
            let branch_id = $('#branch').val();
            let department_id = $('#department').val();

            if (!company_id) {
                toastr.error('Please select a company');
                return;
            }

            if (!monthValue) {
                toastr.error('Please select a month');
                return;
            }

            // Convert YYYY-MM format to date range
            var startDate = moment(monthValue + '-01').format('DD/MM/YYYY');
            var endDate = moment(monthValue + '-01').endOf('month').format('DD/MM/YYYY');
            let followup_date = startDate + ' to ' + endDate;

            // Build query string
            let params = new URLSearchParams({
                company_id: company_id,
                followup_date: followup_date
            });

            if (employee_id) params.append('employee_id', employee_id);
            if (branch_id) params.append('branch_id', branch_id);
            if (department_id) params.append('department_id', department_id);

            window.location.href = "{{ route('attendance-report.export.excel') }}?" + params.toString();
        });

        // Export Print
        $('#export_print_btn').click(function () {
            let company_id = $('#company_id').val();
            let employee_id = $('#employee_id').val();
            let monthValue = $('#month_picker_value').val();
            let branch_id = $('#branch').val();
            let department_id = $('#department').val();

            if (!company_id) {
                toastr.error('Please select a company');
                return;
            }

            if (!monthValue) {
                toastr.error('Please select a month');
                return;
            }

            // Convert YYYY-MM format to date range
            var startDate = moment(monthValue + '-01').format('DD/MM/YYYY');
            var endDate = moment(monthValue + '-01').endOf('month').format('DD/MM/YYYY');
            let followup_date = startDate + ' to ' + endDate;

            // Build query string
            let params = new URLSearchParams({
                company_id: company_id,
                followup_date: followup_date
            });

            if (employee_id) params.append('employee_id', employee_id);
            if (branch_id) params.append('branch_id', branch_id);
            if (department_id) params.append('department_id', department_id);

            window.open("{{ route('attendance-report.export.print') }}?" + params.toString(), '_blank');
        });

        // View Muster Report
        $('#view_muster_report_btn').click(function () {
            let company_id = $('#company_id').val();
            let employee_id = $('#employee_id').val();
            let monthValue = $('#month_picker_value').val();
            // Optional: Branch/Dept if needed

            // Redirect with params
            let params = new URLSearchParams();
            if (company_id) params.append('company_id', company_id);
            if (employee_id) params.append('employee_id', employee_id);
            if (monthValue) params.append('month', monthValue);


            window.location.href = "{{ route('attendance-report.muster') }}?" + params.toString();
        });

        // Auto-fill filters from URL params with robust waiting (for matching Muster Report logic)
        $(function () {
            const urlParams = new URLSearchParams(window.location.search);
            const companyId = urlParams.get('company_id');
            const employeeId = urlParams.get('employee_id');
            const month = urlParams.get('month');

            // Track if we've done initial auto-fill to prevent re-triggering
            let initialAutoFillDone = false;

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
                let attempts = 0;
                let maxAttempts = 50; // 5 seconds max

                let checkExist = setInterval(function () {
                    attempts++;
                    if ($('#employee_id option').length > 1) {
                        clearInterval(checkExist);
                        if (employeeId) {
                            $('#employee_id').val(employeeId).trigger('change');
                        }
                        // Click show
                        if (month) {
                            // Wait slightly for employee selection to register if needed, but usually trigger is enough
                            $('#view_report_btn').click();
                        }
                    } else if (attempts >= maxAttempts) {
                        clearInterval(checkExist);
                        console.log("Employee dropdown load timeout in Index");
                    }
                }, 100);
            }
        });
    </script>

    <script>
        // Sync global company selector (top-right corner) with filter dropdown
        $(document).ready(function () {
            // Check if meta tag has company_id and sync it to filter dropdown
            function syncCompanyFromGlobal() {
                const globalCompanyId = $('meta[name="company_id"]').attr('value');
                if (globalCompanyId && $('#company_id').length) {
                    const currentValue = $('#company_id').val();
                    if (currentValue !== globalCompanyId) {
                        console.log('Auto-filling company filter from global selector:', globalCompanyId);
                        $('#company_id').val(globalCompanyId).trigger('change');
                    }
                }
            }

            // Run on page load after a short delay to let things initialize
            setTimeout(syncCompanyFromGlobal, 800);

            // Watch for changes in meta tag
            if ($('meta[name="company_id"]').length) {
                const metaObserver = new MutationObserver(syncCompanyFromGlobal);
                metaObserver.observe(document.querySelector('meta[name="company_id"]'), {
                    attributes: true,
                    attributeFilter: ['value']
                });
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