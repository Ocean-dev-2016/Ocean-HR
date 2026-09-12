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
            /* Border slightly darker orange */

        }

        .marker-cluster-small div {

            background-color: orange !important;

        }
    </style>
@endpush


@section('content')
    <div class="row">
        <div class="mb-0">
            <div class="card-header py-2 px-4 d-flex flex-wrap justify-content-between align-items-center">
                <h5 class="card-title mb-2 mb-md-0">Statistics</h5>
                <div class="d-flex flex-wrap gap-3">
                    <div class="d-flex flex-wrap gap-3 align-items-end flex-grow-1">
                        @if (!$company_id)
                            <div class="form-group flex-grow-1" style="min-width: 200px;">
                                <label class="form-label">Filter by Company</label>
                                <select id="company_id"
                                    class="form-control select2 search_by_company @error('company_id') is-invalid @enderror"
                                    name="company_id"
                                    data-selectedCompanyId="{{ old('company_id') ?? ($edit->company_id ?? '') }}">
                                </select>
                            </div>
                        @endif
                        <div class="form-group mb-0 flex-grow-1" style="min-width: 250px;">
                            <label class="form-label">Filter by Date Range</label>
                            <input type="text" name="filter_by_date_range" id="filter_by_date_range"
                                class="form-control my_daterangepicker table_filter" value="{{ date('d/m/Y') }}"
                                placeholder="Filter by date range">
                        </div>
                        <div class="form-group mb-0 d-flex align-items-end">
                            <button type="button" id="refresh-dashboard-btn" class="btn btn-primary">
                                <i class="ti ti-refresh me-1"></i> Refresh Dashboard
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 mb-3">
            <div class=" h-100">
                <div class="mt-3" id="inquiryStatistics">
                    <div class="p-4 text-center">
                        <p class="mb-0 fs-5">No data available. Please apply filters to see statistics.</p>
                    </div>
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
    </script>

    @include('utils.getCompany')
@endpush