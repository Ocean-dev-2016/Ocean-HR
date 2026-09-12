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

@php
    use Carbon\Carbon;

    $i = 0;
    $page_title = 'Daily Attendance Report';

    // Use passed variables or fallback to defaults
    $employees = $employees ?? [];
    $monthYear = $monthYear ?? Carbon::now();
    $daysInMonth = $daysInMonth ?? $monthYear->daysInMonth;
@endphp



@section('title', $page_title)

@section('page_leavel_style')

    <link rel="stylesheet" href="{{ asset('software/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('software/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
    <style>
        .attendance-wrapper {
            border: 1px solid #d0d7e2;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
        }

        /* Header */
        .attendance-header {
            background: linear-gradient(90deg, #2a8dff, #0056b3);
            color: #fff;
            padding: 12px 16px;
            font-weight: 600;
            font-size: 16px;
            text-align: center;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        /* Layout */
        .attendance-table-container {
            max-height: 65vh;
            overflow: auto;
        }

        .attendance-container {
            display: flex;
            width: max-content;
            min-width: 100%;
        }

        .fixed-columns {
            flex: 0 0 auto;
            background-color: #f7f9fc;
            z-index: 10;
        }

        .fixed-columns:first-child {
            position: sticky;
            left: 0;
            box-shadow: 2px 0 5px rgba(0,0,0,0.05);
        }

        .fixed-columns:last-child {
            position: sticky;
            right: 0;
            box-shadow: -2px 0 5px rgba(0,0,0,0.05);
        }

        .scrollable-section {
            overflow-x: visible;
            flex: 1;
            z-index: 1;
        }

        /* Table */
        .attendance-table {
            border-collapse: collapse;
            width: max-content;
        }

        .attendance-table th,
        .attendance-table td {
            border: 1px solid #e0e0e0;
            text-align: center;
            padding: 6px 4px;
            font-size: 12px;
            min-width: 50px;
            height: 70px;
            vertical-align: middle;
        }

        .attendance-table thead th {
            background-color: #eef2f7;
            font-weight: bold;
            position: sticky;
            top: 0;
            z-index: 6;
        }
        /* Fixed left/right column headers need higher z-index */
        .fixed-columns thead th {
            z-index: 15 !important;
            background-color: #e9eff5 !important;
        }

        /* Fixed columns */
        .employee-col {
            min-width: 180px;
            text-align: left;
            font-weight: 600;
            background-color: #e9eff5;
        }

        .working-hours-col {
            min-width: 130px;
            background-color: #e9eff5;
            font-weight: 600;
        }

        /* Status Colors */
        .present-cell {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .absent-cell {
            background-color: #f7a4b0;
            color: #c62828;
        }

        .leave-cell {
            background-color: #fff3e0;
            color: #ef6c00;
        }

        .holiday-cell {
            background-color: #e3f2fd;
            color: #1565c0;
        }

        .weekend {
            background-color: #f0f0f0;
            color: #777;
        }

        .present {
            font-weight: 600;
            color: #2e7d32;
        }

        .absent {
            font-weight: bold;
            color: #c62828;
        }

        .leave {
            font-weight: 600;
            color: #ef6c00;
        }

        .holiday {
            font-weight: 600;
            color: #1565c0;
        }

        .time-in {
            color: #43a047 !important;
            font-size: 11px;
            display: inline-block;
        }

        .time-out {
            color: #e53935;
            font-size: 11px;
            display: inline-block;
        }

        .working-hours {
            color: #000;
            font-size: 11px;
            display: inline-block;
        }

        .purple-cell {
            background-color: #f4d9f9 !important;
            color: #4a148c !important;
            font-weight: 600;
        }
    </style>
@endsection

@section('content')
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.inlcudes.breadcrumb', [
            'breadcrumbArray' => [['title' => $page_title, 'url' => '']],
        ])
    </div>

    <div class="row my-3">
        <div class="col-md-12 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="filter-card p-3">
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

                            <div class="col-md-2 col-sm-12 branchDiv" style="display:none;">
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
                                        <input type="hidden" id="month_picker_value" value="{{ $monthYear->format('Y-m') }}">
                                        <div class="custom-month-picker-dropdown" id="month_picker_dropdown">
                                            <div class="month-picker-header">
                                                <button type="button" class="month-picker-year-btn"
                                                    id="year_display">{{ $monthYear->year }}</button>
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
                                                <a href="javascript:void(0)" class="month-picker-link"
                                                    id="this_month">This month</a>
                                            </div>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted d-none" id="month_range_info"></small>
                                </div>
                            </div>

                            <div class="col-md-2 d-flex align-items-end gap-2">
                                <button type="button" class="btn btn-primary btn-report" id="view_report_btn">
                                    <i class="fa fa-eye me-1"></i> Show
                                </button>
                                <button type="button" class="btn btn-warning btn-report" id="print_report_btn">
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
        <div class="col-md-12">
            <div class="attendance-wrapper shadow-sm">
                <div class="attendance-header">
                    ATTENDANCE REPORT <span id="report_month">{{ $monthYear->format('F Y') }}</span>
                </div>

                <div class="attendance-table-container">
                <div class="attendance-container">
                    <!-- LEFT FIXED (Employee Sr. & Name) -->
                    <div class="fixed-columns">
                        <table class="attendance-table">
                            <thead>
                                <tr>
                                    <th style="width:50px;">#</th>
                                    <th class="employee-col">Employee Name</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($employees as $index => $employee)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td class="employee-col">{!! $employee->name !!}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- SCROLLABLE MIDDLE (Days 1–31) -->
                    <div class="scrollable-section">
                        <table class="attendance-table">
                            <thead>
                                <tr id="days_head">
                                    @for ($day = 1; $day <= $daysInMonth; $day++)
                                        <th>{{ str_pad($day, 2, '0', STR_PAD_LEFT) }}</th>
                                    @endfor
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($employees as $employee)
                                    <tr>
                                        @for ($day = 1; $day <= $daysInMonth; $day++)
                                            @php
                                                $dateStr = sprintf('%04d-%02d-%02d', $monthYear->year, $monthYear->month, $day);
                                                $attendance = collect($employee->attendances)->firstWhere('attendance_date', $dateStr);
                                                $dayDate = Carbon::parse($dateStr);
                                                $cellClass = $dayDate->isWeekend() ? 'weekend-cell' : '';

                                                if ($attendance) {
                                                    if ($attendance->attendance_type === 'present' || $attendance->attendance_type === 'Present') {
                                                        [$h, $m] = explode(':', $attendance->working_hours);
                                                        $totalMinutes = (int) $h * 60 + (int) $m;
                                                        $isLessThan9 = $totalMinutes < 9 * 60;
                                                        $cellClass = $isLessThan9 ? 'purple-cell' : 'present-cell';
                                                    } elseif (in_array(strtolower($attendance->attendance_type), ['absent'])) {
                                                        $cellClass = 'absent-cell';
                                                    } elseif (in_array(strtolower($attendance->attendance_type), ['leave', 'half_day'])) {
                                                        $cellClass = 'leave-cell';
                                                    } elseif (in_array(strtolower($attendance->attendance_type), ['holiday'])) {
                                                        $cellClass = 'holiday-cell';
                                                    } elseif (in_array(strtolower($attendance->attendance_type), ['week_off'])) {
                                                        $cellClass = 'weekend-cell';
                                                    }
                                                }
                                            @endphp

                                            <td class="{{ $cellClass }}">
                                                @if ($attendance)
                                                    @php
                                                        $type = strtolower($attendance->attendance_type);
                                                    @endphp
                                                    @if ($type === 'present')
                                                        <span class="present">
                                                            P<br>
                                                            <span class="time-in">{{ $attendance->time_in ?? '' }}</span> -
                                                            <span class="time-out">{{ $attendance->time_out ?? '' }}</span><br>
                                                            <span class="working-hours">(Total: {{ $attendance->working_hours ?? '00:00' }})</span>
                                                        </span>
                                                    @elseif ($type === 'absent')
                                                        <span class="absent">A</span>
                                                    @elseif ($type === 'leave')
                                                        <span class="leave">L</span>
                                                    @elseif ($type === 'holiday')
                                                        <span class="holiday">H</span>
                                                    @elseif ($type === 'week_off')
                                                        <span class="holiday">WO</span>
                                                    @endif
                                                @else
                                                    <span class="weekend">-</span>
                                                @endif
                                            </td>
                                        @endfor
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- RIGHT FIXED (Total Hours) -->
                    <div class="fixed-columns">
                        <table class="attendance-table">
                            <thead>
                                <tr>
                                    <th class="working-hours-col" style="min-width: 60px;">Present</th>
                                    <th class="working-hours-col" style="min-width: 70px;">Week Off</th>
                                    <th class="working-hours-col" style="min-width: 70px;">Half Days</th>
                                    <th class="working-hours-col" style="min-width: 60px;">Leave</th>
                                    <th class="working-hours-col" style="min-width: 60px;">Absent</th>
                                    <th class="working-hours-col" style="min-width: 60px;">Holiday</th>
                                    <th class="working-hours-col" style="min-width: 110px;">Days Compensated</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($employees as $employee)
                                    @php
                                        $totalHours = $employee->total_working_hours ?? '00:00';
                                        [$h, $m] = explode(':', $totalHours);
                                        $totalMinutes = (int) $h * 60 + (int) $m;
                                        $isLessThan9 = $totalMinutes < 9 * 60;
                                    @endphp
                                    <tr>
                                        <td class="working-hours-col {{ $isLessThan9 ? 'purple-cell' : 'present-cell' }}">
                                            {{ $totalHours }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                </div>
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
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(function() {
            var picker = $('.my_daterangepicker').data('daterangepicker');
            if (picker) {
                picker.maxDate = false;
                var today = moment();
                var financialYearStart = moment().month() < 3 ?
                    moment().subtract(1, 'year').startOf('year').month(3).startOf('month') :
                    moment().startOf('year').month(3).startOf('month');
                picker.setStartDate(financialYearStart);
                picker.setEndDate(today);
                picker.callback(financialYearStart, today, 'Financial Year Range');
            }
        });
    </script>

    @if (!$company_id)
        @include('utils.getCompany')
    @endif
    @include('utils.getEmployee')
    @include('utils.getDepartment')
    @include('utils.getBranch')

    <script>
        // Custom Month Picker Implementation
        $(function() {
            var currentDate = moment();
            var currentMonth = currentDate.format('YYYY-MM');
            var currentYear = currentDate.year();
            var currentMonthNum = currentDate.month() + 1;

            // Calculate restrictions
            var minYear = currentYear - 2;
            var maxYear = currentYear;
            var maxMonth = currentMonthNum;

            var selectedYear = parseInt(moment($('#month_picker_value').val()).format('YYYY'));
            var selectedMonth = parseInt(moment($('#month_picker_value').val()).format('M'));

            // Initialize display
            updateMonthDisplay();
            updateMonthPicker(); // Initialize picker state

            // Toggle dropdown
            $('#month_picker').on('click', function(e) {
                e.stopPropagation();
                $('#month_picker_dropdown').toggleClass('show');
                updateMonthPicker();
            });

            // Close dropdown when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.custom-month-picker-wrapper').length) {
                    $('#month_picker_dropdown').removeClass('show');
                }
            });

            // Year navigation - cycle through available years
            $('#year_display').on('click', function() {
                selectedYear++;
                if (selectedYear > maxYear) {
                    selectedYear = minYear;
                }
                // Reset selected month when year changes
                selectedMonth = null;
                updateMonthPicker();
            });

            // Month selection
            $(document).on('click', '.month-btn:not(.disabled)', function() {
                selectedMonth = parseInt($(this).data('month'));
                updateMonthDisplay();
                updateMonthPicker(); // Update to highlight selected
                $('#month_picker_dropdown').removeClass('show');
            });

            // Clear button
            $('#clear_month').on('click', function() {
                $('#month_picker').val('');
                $('#month_picker_value').val('');
                selectedYear = currentYear;
                selectedMonth = currentMonthNum;
                updateMonthPicker();
            });

            // This month button
            $('#this_month').on('click', function() {
                selectedYear = currentYear;
                selectedMonth = currentMonthNum;
                updateMonthDisplay();
                $('#month_picker_dropdown').removeClass('show');
            });

            // Update month picker display
            function updateMonthPicker() {
                $('#year_display').text(selectedYear);

                $('.month-btn').each(function() {
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

        $('#view_report_btn').click(function() {
            let company_id = $('#company_id').val();
            let employee_id = $('#employee_id').val();
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
            // Check if InlineLoader exists (it's custom), otherwise use simple text
            if(typeof InlineLoader !== 'undefined') {
                 InlineLoader.show($attendanceWrapper);
                 InlineLoader.showButton('#view_report_btn', '<i class="fa fa-eye me-1"></i> Show');
            } else {
                 $attendanceWrapper.css('opacity', '0.5');
                 $('#view_report_btn').prop('disabled', true).text('Loading...');
            }

            $.ajax({
                url: "{{ route('attendance-report.getReport') }}",
                type: "POST",
                data: {
                    company_id,
                    employee_id,
                    followup_date
                },
                success: function(res) {
                    if(typeof InlineLoader !== 'undefined') {
                        InlineLoader.hide($attendanceWrapper);
                        InlineLoader.hideButton('#view_report_btn');
                    } else {
                         $attendanceWrapper.css('opacity', '1');
                         $('#view_report_btn').prop('disabled', false).html('<i class="fa fa-eye me-1"></i> Show');
                    }

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
                error: function(xhr, status, error) {
                     if(typeof InlineLoader !== 'undefined') {
                        InlineLoader.hide($attendanceWrapper);
                        InlineLoader.hideButton('#view_report_btn');
                    } else {
                         $attendanceWrapper.css('opacity', '1');
                         $('#view_report_btn').prop('disabled', false).html('<i class="fa fa-eye me-1"></i> Show');
                    }
                    
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
                console.log('Rendering table from controller HTML');

                // Update table headers
                if (html.daysHead) {
                    $('#days_head').html(html.daysHead);
                }
                
                // Update Right Header
                if (html.rightHead) {
                    $('.fixed-columns:last thead').html(html.rightHead);
                } else if (!html.rightHead && html.rightBody) {
                    // Fallback if rightHead not sent but rightBody expects it, or keep existing if matching
                    // Ideally controller sends it.
                }

                // Update table bodies
                if (html.leftBody) {
                    $('.fixed-columns:first tbody').html(html.leftBody);
                }

                if (html.scrollableBody) {
                    $('.scrollable-section tbody').html(html.scrollableBody);
                }

                if (html.rightBody) {
                    $('.fixed-columns:last tbody').html(html.rightBody);
                }

                // Update month year
                if (html.monthYear) {
                    $('#report_month').text(' - ' + html.monthYear);
                }

                console.log('Table rendered successfully from controller HTML');
            } catch (error) {
                console.error('Error rendering table from HTML:', error);
                alert('Error rendering attendance table. Please check console for details.');
            }
        }
    </script>@endpush
