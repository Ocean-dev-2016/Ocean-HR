@extends('software.layout.app')

@php
    $i = 0;
    $page_title = isset($modules['title']) ? $modules['title'] : null;
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $company_id = isset($modules['company_id']) ? $modules['company_id'] : null;
    $authLoginUserDetail = isset($modules['authLoginUserDetail']) ? $modules['authLoginUserDetail'] : null;
    $loginUserId = isset($authLoginUserDetail?->id) ? $authLoginUserDetail?->id : null;
    $parent_type_id = isset($modules['parent_type_id']) ? $modules['parent_type_id'] : null;
@endphp

@section('title', 'Dashboard')

@section('page_leavel_style')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
@endsection

@push('push_style')
    <style>
        .nav-pills .nav-link.active {
            background-color: #5c6bc0;
            color: white;
        }

        canvas {
            max-height: 400px;
        }

        .marker-cluster-small {
            background-color: orange !important;
            color: white !important;
            border: 2px solid #FF9900;
        }

        .marker-cluster-small div {
            background-color: orange !important;
        }

        /* Circular Rounder Punch Button & Rotating Border Line Animation */
        .punch-circle-container {
            position: relative;
            width: 104px;
            height: 104px;
            display: flex;
            align-items: center;
            justify-content: center;
            user-select: none;
            -webkit-user-select: none;
            margin-left: 8px;
        }

        /* 360 Degree Rotating Line SVG around Circle */
        .punch-rotating-svg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 10;
            transform: rotate(-90deg);
        }

        .punch-track {
            fill: none;
            stroke: rgba(0, 0, 0, 0.08);
            stroke-width: 4;
        }

        .punch-rotating-line {
            fill: none;
            stroke-width: 4.5;
            stroke-linecap: round;
            stroke-dasharray: 276.46;
            stroke-dashoffset: 276.46;
            transition: stroke-dashoffset 0.3s ease-out, filter 0.3s ease;
            filter: drop-shadow(0 0 4px currentColor);
        }

        /* Hold 2 Seconds (360 Degree Fill & Pulse while holding down) */
        .punch-circle-container.is-holding .punch-rotating-line {
            transition: stroke-dashoffset 2000ms linear, filter 0.3s ease !important;
            stroke-dashoffset: 0 !important;
            filter: drop-shadow(0 0 10px currentColor) !important;
        }

        .punch-circle-container.is-holding .punch-circle-btn {
            transform: scale(0.92) !important;
            box-shadow: 0 0 18px rgba(16, 185, 129, 0.45) !important;
        }

        .punch-circle-container.is-animating .punch-rotating-svg {
            animation: punchSvgSuccessSpin 0.6s ease-out forwards;
        }

        @keyframes punchSvgSuccessSpin {
            0% { transform: rotate(-90deg) scale(1); }
            50% { transform: rotate(180deg) scale(1.08); }
            100% { transform: rotate(270deg) scale(1); }
        }

        /* Rounder Button Style */
        .punch-circle-btn {
            position: relative;
            width: 90px;
            height: 90px;
            border-radius: 50%;
            border: none;
            padding: 0;
            background: transparent;
            cursor: pointer;
            outline: none !important;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s ease;
        }

        .punch-circle-btn:hover {
            transform: scale(1.05);
        }

        .punch-circle-btn:active, .punch-circle-btn.is-pressing {
            transform: scale(0.93);
        }

        /* Subtle radar wave pulse behind button */
        .punch-pulse-ring {
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            pointer-events: none;
            z-index: 1;
            animation: punchPulse 2.8s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
        }

        @keyframes punchPulse {
            0% { transform: scale(0.85); opacity: 0.8; }
            70% { transform: scale(1.3); opacity: 0; }
            100% { transform: scale(1.3); opacity: 0; }
        }

        .punch-circle-btn.punch-btn-in .punch-pulse-ring {
            background: rgba(16, 185, 129, 0.35);
        }

        .punch-circle-btn.punch-btn-out .punch-pulse-ring {
            background: rgba(239, 68, 68, 0.35);
        }

        /* Halo Rings */
        .punch-halo-outer {
            position: absolute;
            width: 90px;
            height: 90px;
            border-radius: 50%;
            z-index: 2;
            transition: all 0.35s ease;
        }

        .punch-circle-btn.punch-btn-in .punch-halo-outer {
            background: radial-gradient(circle, rgba(16, 185, 129, 0.28) 0%, rgba(16, 185, 129, 0.06) 70%, transparent 100%);
            box-shadow: 0 0 18px rgba(16, 185, 129, 0.3);
        }

        .punch-circle-btn.punch-btn-out .punch-halo-outer {
            background: radial-gradient(circle, rgba(239, 68, 68, 0.28) 0%, rgba(239, 68, 68, 0.06) 70%, transparent 100%);
            box-shadow: 0 0 18px rgba(239, 68, 68, 0.3);
        }

        .punch-halo-inner {
            position: absolute;
            width: 76px;
            height: 76px;
            border-radius: 50%;
            z-index: 3;
            transition: all 0.35s ease;
        }

        .punch-circle-btn.punch-btn-in .punch-halo-inner {
            background: rgba(16, 185, 129, 0.25);
        }

        .punch-circle-btn.punch-btn-out .punch-halo-inner {
            background: rgba(239, 68, 68, 0.25);
        }

        /* Core Center */
        .punch-core {
            position: relative;
            width: 66px;
            height: 66px;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            z-index: 4;
            transition: all 0.35s ease;
            border: 2px solid rgba(255, 255, 255, 0.45);
        }

        .punch-circle-btn.punch-btn-in .punch-core {
            background: linear-gradient(135deg, #10b981 0%, #059669 60%, #047857 100%);
            box-shadow: 0 6px 18px rgba(16, 185, 129, 0.45), inset 0 2px 4px rgba(255, 255, 255, 0.35);
        }

        .punch-circle-btn.punch-btn-out .punch-core {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 60%, #b91c1c 100%);
            box-shadow: 0 6px 18px rgba(239, 68, 68, 0.45), inset 0 2px 4px rgba(255, 255, 255, 0.35);
        }

        .punch-icon-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .punch-tap-icon {
            width: 21px;
            height: 21px;
            margin-bottom: 2px;
            filter: drop-shadow(0 1px 2px rgba(0,0,0,0.25));
        }

        .punch-text {
            font-size: 0.68rem;
            font-weight: 900;
            letter-spacing: 0.7px;
            line-height: 1;
            color: #ffffff;
            text-transform: uppercase;
            text-shadow: 0 1px 3px rgba(0,0,0,0.35);
        }

        .punch-subtext {
            font-size: 0.50rem;
            font-weight: 600;
            letter-spacing: 0.2px;
            color: rgba(255, 255, 255, 0.9);
            margin-top: 1px;
        }
    </style>
@endpush


@section('content')
    <div class="row">
        {{-- Top Filter Toolbar: Only shown if Super Admin needs to pick a company --}}
        @if (!$company_id)
            <div class="col-12 mb-3">
                <div class="card border-0 shadow-sm" style="border-radius: 14px; background: #ffffff; border: 1px solid #edf2f7 !important;">
                    <div class="card-body py-2 px-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-label-primary p-1.5 rounded-3">
                                <i class="ti ti-building fs-5"></i>
                            </span>
                            <div>
                                <h6 class="card-title mb-0 fw-bold text-dark" style="font-size: 0.95rem;">Select Company</h6>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <div class="form-group mb-0" style="min-width: 240px;">
                                <select id="company_id"
                                    class="form-control select2 search_by_company @error('company_id') is-invalid @enderror"
                                    name="company_id"
                                    data-selectedCompanyId="{{ old('company_id') ?? ($edit->company_id ?? '') }}">
                                </select>
                            </div>
                            <input type="hidden" name="filter_by_date_range" id="filter_by_date_range" value="{{ date('d/m/Y') }}">
                            <button type="button" id="refresh-dashboard-btn" class="btn btn-sm btn-primary rounded-3">
                                <i class="ti ti-refresh me-1"></i> Refresh
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <input type="hidden" name="filter_by_date_range" id="filter_by_date_range" value="{{ date('d/m/Y') }}">
            <button type="button" id="refresh-dashboard-btn" style="display:none;"></button>
        @endif

        <div class="col-12 mb-3">
            <div id="inquiryStatistics">
                <div class="p-5 text-center bg-white rounded-4 shadow-sm border">
                    <div class="spinner-border text-primary mb-2" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mb-0 text-muted fw-semibold" style="font-size: 0.95rem;">Loading dashboard statistics...</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page_leavel_script')

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
@endsection

@push('page_scripts')
    @php
        $dateRangeArray = request('date_range') ? explode(' to ', request('date_range')) : null;
    @endphp

    <script>
        $(document).ready(function () {
            // Initialize date range picker
            if (typeof moment !== 'undefined' && typeof $.fn.daterangepicker !== 'undefined') {
                var today = moment();
                var todayStr = today.format('DD/MM/YYYY');

                $('#filter_by_date_range').val(todayStr + ' to ' + todayStr);

                $('#filter_by_date_range').daterangepicker({
                    singleDatePicker: false,  // Enable date range selection
                    autoUpdateInput: true,
                    locale: {
                        format: 'DD/MM/YYYY',
                        separator: ' to ',  // Separator between dates
                        cancelLabel: 'Clear'
                    }
                });
            }

            // Load statistics on page load
            setTimeout(function () {
                loadStatistics();
            }, 500);

            // Load statistics when filters change
            $(document).on('change', '#company_id', function () {
                loadStatistics();
            });

            $(document).on('apply.daterangepicker', '#filter_by_date_range', function () {
                loadStatistics();
            });

            // Sync attendance button handler
            $(document).on('click', '#refresh-dashboard-btn', function () {
                loadStatistics();
            });
        });

        function syncAttendance() {
            var $btn = $('#sync-attendance-btn');
            var originalHtml = $btn.html();
            var isSyncing = $btn.data('syncing');

            if (isSyncing) {
                return false; // Prevent multiple clicks
            }

            $btn.data('syncing', true);
            $btn.prop('disabled', true);
            $btn.html('<i class="ti ti-loader me-1"></i> Syncing...');

            $.ajax({
                url: '{{ route("attendance.sync-biometric") }}',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    if (response.status) {
                        // Show success message
                        if (typeof toastr !== 'undefined') {
                            var successMsg = response.message || 'Attendance synced successfully';
                            if (response.data) {
                                var details = [];
                                if (response.data.records_fetched !== undefined) {
                                    details.push('Fetched: ' + response.data.records_fetched);
                                }
                                if (response.data.records_stored !== undefined) {
                                    details.push('Stored: ' + response.data.records_stored);
                                }
                                if (details.length > 0) {
                                    successMsg += '<br><small>' + details.join(' | ') + '</small>';
                                }
                            }
                            toastr.success(successMsg, 'Sync Completed', {
                                timeOut: 5000,
                                extendedTimeOut: 2000
                            });
                        } else {
                            alert(response.message || 'Attendance synced successfully');
                        }

                        // Reload statistics after sync
                        setTimeout(function () {
                            loadStatistics();
                        }, 1000);
                    } else {
                        if (typeof toastr !== 'undefined') {
                            toastr.error(response.message || 'Sync failed');
                        } else {
                            alert(response.message || 'Sync failed');
                        }
                    }
                },
                error: function (xhr) {
                    var errorMsg = 'Sync failed. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    if (typeof toastr !== 'undefined') {
                        toastr.error(errorMsg);
                    } else {
                        alert(errorMsg);
                    }
                },
                complete: function () {
                    $btn.data('syncing', false);
                    $btn.prop('disabled', false);
                    $btn.html(originalHtml);
                }
            });
        }

        function updatePunchUI(state) {
            const $btn = $('#dashboard-punch-btn');
            if (!$btn.length) return;

            $btn.attr('data-punch-state', state);
            const isPunchedIn = (state === 'in');

            // Update SVG circular line color: Red when currently In (next is Out), Green when currently Out (next is In)
            $('#punch-rotating-line').attr('stroke', isPunchedIn ? '#ef4444' : '#10b981');

            if (isPunchedIn) {
                // Currently Punched IN -> Button shows Punch Out in RED
                $btn.removeClass('punch-btn-in').addClass('punch-btn-out');
                $('#punch-btn-sublabel').text('Punch Out');
                $('#punch-icon-wrap').html(`
                    <svg class="punch-tap-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="width: 22px; height: 22px;">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                `);
                $('#punch-status-badge').text('IN').css({ background: '#dcfce7', color: '#15803d' });
                $('#punch-status-desc').text('Hold 2 sec to punch out and end your day');
                // Update live clock dot in navbar to Green
                $('#navbar-live-clock-dot').removeClass('status-out').addClass('status-in').attr('title', 'Status: Punched In');
            } else {
                // Currently NOT Punched IN -> Button shows Punch In in GREEN
                $btn.removeClass('punch-btn-out').addClass('punch-btn-in');
                $('#punch-btn-sublabel').text('Punch In');
                $('#punch-icon-wrap').html(`
                    <svg class="punch-tap-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="width: 22px; height: 22px;">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                        <polyline points="10 17 15 12 10 7"></polyline>
                        <line x1="15" y1="12" x2="3" y2="12"></line>
                    </svg>
                `);
                $('#punch-status-badge').text('OUT').css({ background: '#fee2e2', color: '#b91c1c' });
                $('#punch-status-desc').text('Hold 2 sec to punch in and start your day');
                // Update live clock dot in navbar to Red (Not Green)
                $('#navbar-live-clock-dot').removeClass('status-in').addClass('status-out').attr('title', 'Status: Punched Out / Not In');
            }
        }

        function loadStatistics() {
            var companyId = $('#company_id').val() || '';
            var dateRange = $('#filter_by_date_range').val() || '';

            $.ajax({
                url: '{{ route("software.dashboard") }}',
                method: 'GET',
                data: {
                    company_id: companyId,
                    filter_by_daterange: dateRange
                },
                success: function (response) {
                    if (response.status && response.data) {
                        if (response.data.inquiryStatistics) {
                            $('#inquiryStatistics').html(response.data.inquiryStatistics);

                            // Inject active filter parameters into the href of statistics tiles
                            updateCardUrls(companyId, dateRange);
                        }

                        // Sync punch button and live clock status from employee statistics
                        if (response.data.employeeStats && response.data.employeeStats.punch_state) {
                            updatePunchUI(response.data.employeeStats.punch_state);
                        }
                    }
                },
                error: function (xhr) {
                    console.error('Error loading statistics:', xhr);
                }
            });
        }

        function updateCardUrls(companyId, dateRange) {
            if (!dateRange) return;

            // Parse start date for year/month
            var startDateStr = dateRange.split(' to ')[0];
            var startParts = startDateStr.split('/');
            var yearVal = '';
            var monthVal = '';
            if (startParts.length === 3) {
                yearVal = startParts[2];
                monthVal = parseInt(startParts[1], 10);
            }

            $('#inquiryStatistics a').each(function () {
                var $link = $(this);
                var href = $link.attr('href');
                if (!href) return;

                try {
                    // Determine target route or page by analyzing href
                    if (href.indexOf('salary-calculation') !== -1) {
                        // Salary calculation (Company Payroll)
                        var url = new URL(href, window.location.origin);
                        if (companyId) url.searchParams.set('filter_company', companyId);
                        if (yearVal) url.searchParams.set('filter_year', yearVal);
                        if (monthVal) url.searchParams.set('filter_month', monthVal);
                        $link.attr('href', url.pathname + url.search);
                    }
                    else if (href.indexOf('operations-rate-list') !== -1) {
                        // Operations rate list (Contractor Salary & Operation-wise)
                        var url = new URL(href, window.location.origin);
                        if (companyId) url.searchParams.set('filter_company', companyId);
                        if (yearVal) url.searchParams.set('year', yearVal);
                        if (monthVal) url.searchParams.set('month', monthVal);
                        $link.attr('href', url.pathname + url.search);
                    }
                    else if (href.indexOf('late-punch-report') !== -1 || href.indexOf('early-going-report') !== -1) {
                        // Late Punch and Early Going reports
                        var url = new URL(href, window.location.origin);
                        if (companyId) url.searchParams.set('company_id', companyId);

                        if (dateRange) {
                            var dates = dateRange.split(' to ');
                            var firstDateStr = dates[0].trim();
                            var parts = firstDateStr.split('/');
                            if (parts.length === 3) {
                                var formattedDate = parts[2] + '-' + parts[1] + '-' + parts[0];
                                url.searchParams.set('date', formattedDate);
                            }
                        }
                        $link.attr('href', url.pathname + url.search);
                    }
                    else if (href.indexOf('employees') !== -1) {
                        // Employee and Contractor employee lists
                        var url = new URL(href, window.location.origin);
                        if (companyId) url.searchParams.set('filter_company', companyId);
                        url.searchParams.set('status', 'active');
                        $link.attr('href', url.pathname + url.search);
                    }
                    else if (href.indexOf('daily-attendance-report') !== -1) {
                        // Daily Attendance Report (Present / Absent stat cards)
                        var url = new URL(href, window.location.origin);
                        if (companyId) url.searchParams.set('company_id', companyId);

                        // Convert DD/MM/YYYY → YYYY-MM-DD for the date input
                        if (startParts.length === 3) {
                            var isoDate = startParts[2] + '-' + startParts[1] + '-' + startParts[0];
                            url.searchParams.set('date', isoDate);
                        }
                        // status is already set in the blade href (present / absent)
                        $link.attr('href', url.pathname + url.search);
                    }
                    else if (href.indexOf('attendance') !== -1) {
                        // Attendance (Present, Absent, Late, Early, View Attendance buttons)
                        var url = new URL(href, window.location.origin);

                        // Today's attendance statistics are always calculated for the START date of the selected range.
                        // We must filter the target page to this exact single day to match the dashboard count.
                        var singleDayRange = startDateStr + ' to ' + startDateStr;

                        // For general tiles (not the single employee "View Attendance" button inside the absent table):
                        if (!$link.hasClass('btn-sm')) {
                            if (companyId) url.searchParams.set('filter_company', companyId);
                            url.searchParams.set('filter_date', singleDayRange);

                            // Determine which tile it is by its title text to assign proper attendance_status
                            var cardTitle = $link.find('.card-title').text().trim().toLowerCase();
                            if (cardTitle === 'present') {
                                url.searchParams.set('attendance_status', 'present');
                            } else if (cardTitle === 'absent') {
                                url.searchParams.set('attendance_status', 'absent');
                            } else if (cardTitle === 'late punch') {
                                url.searchParams.set('attendance_status', 'late');
                            } else if (cardTitle === 'early go') {
                                url.searchParams.set('attendance_status', 'early');
                            }
                        } else {
                            // This is the individual employee "View Attendance" button in the Absent Employees table.
                            url.searchParams.set('filter_date', singleDayRange);
                        }
                        $link.attr('href', url.pathname + url.search);
                    }
                    else if (href.indexOf('leave-application') !== -1) {
                        // Leave Application
                        var url = new URL(href, window.location.origin);
                        if (companyId) url.searchParams.set('company_id', companyId);
                        var leaveSingleDayRange = startDateStr + ' - ' + startDateStr;
                        url.searchParams.set('filter_date', leaveSingleDayRange);
                        $link.attr('href', url.pathname + url.search);
                    }
                } catch (e) {
                    console.error('Error updating link href:', e);
                }
            });
        }

        $(document).on('click', '.inquiry-status-link', function () {
            var statusId = $(this).data('selectedinquirystatusid');
            var selected_inquiry_date = $('input[name="filter_by_date_range"]').val();
            localStorage.setItem('selectedInquiryStatusId', statusId);
            localStorage.setItem('selected_inquiry_date', selected_inquiry_date);
        });

        $(document).on('click', '#totalInquiryCard', function () {
            var selected_inquiry_date = $('input[name="filter_by_date_range"]').val();
            localStorage.setItem('filterInquiryByDateRange', selected_inquiry_date);
        });

        // Press & Hold 2 Seconds to Punch In / Out Handler
        let holdTimer = null;
        let isHolding = false;
        let isPunchProcessing = false;

        function startHold(e) {
            if (isPunchProcessing) return;
            if (e.type === 'mousedown' && e.button !== 0) return;

            e.preventDefault();
            const $btn = $('#dashboard-punch-btn');
            const $container = $('#punch-container');
            if (!$btn.length) return;

            isHolding = true;
            $btn.addClass('is-pressing');
            $container.addClass('is-holding').removeClass('is-animating');

            if (navigator.vibrate) navigator.vibrate(35);

            clearTimeout(holdTimer);
            holdTimer = setTimeout(function () {
                if (isHolding && !isPunchProcessing) {
                    completePunchHold();
                }
            }, 2000); // Exactly 2 seconds
        }

        function cancelHold(e) {
            if (!isHolding || isPunchProcessing) return;
            isHolding = false;
            clearTimeout(holdTimer);

            const $btn = $('#dashboard-punch-btn');
            const $container = $('#punch-container');
            $btn.removeClass('is-pressing');
            $container.removeClass('is-holding');
        }

        function completePunchHold() {
            isHolding = false;
            isPunchProcessing = true;

            const $btn = $('#dashboard-punch-btn');
            const $container = $('#punch-container');
            const currentState = $btn.attr('data-punch-state') || 'out';
            const targetAction = currentState === 'in' ? 'out' : 'in';

            $container.addClass('is-animating');
            if (navigator.vibrate) navigator.vibrate([80, 40, 80]);

            // Execute Punch AJAX
            $.ajax({
                url: "{{ route('software.dashboard.punch-action') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    punch_type: targetAction
                },
                success: function (res) {
                    setTimeout(() => {
                        $container.removeClass('is-animating is-holding');
                        $btn.removeClass('is-pressing');
                        isPunchProcessing = false;
                    }, 400);

                    if (res.success) {
                        const newState = res.punch_state;
                        updatePunchUI(newState);

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: res.message,
                                html: `<div class="mt-2 text-muted fs-6">Recorded at: <strong>${res.punch_time || new Date().toLocaleTimeString()}</strong></div>`,
                                timer: 2500,
                                showConfirmButton: false,
                                customClass: { popup: 'rounded-4 shadow' }
                            });
                        }

                        // Trigger dashboard statistics refresh
                        if (typeof loadStatistics === 'function') {
                            loadStatistics();
                        }
                    } else {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Punch Failed',
                                text: res.message || 'Error occurred'
                            });
                        }
                    }
                },
                error: function (xhr) {
                    $container.removeClass('is-animating is-holding');
                    $btn.removeClass('is-pressing');
                    isPunchProcessing = false;

                    let errMsg = 'Something went wrong while recording punch.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errMsg = xhr.responseJSON.message;
                    }
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errMsg
                        });
                    }
                }
            });
        }

        // Attach mouse & touch hold events
        $(document).on('mousedown touchstart', '#dashboard-punch-btn', startHold);
        $(document).on('mouseup mouseleave touchend touchcancel', '#dashboard-punch-btn', cancelHold);
        $(document).on('click contextmenu', '#dashboard-punch-btn', function(e) {
            e.preventDefault();
        });
    </script>

    @include('utils.getCompany')
@endpush