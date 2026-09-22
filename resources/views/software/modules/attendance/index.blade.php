@extends('software.layout.app')

@php
    $i = 0;
    $page_title = isset($modules['title']) ? $modules['title'] : null;
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $company_id = isset($modules['company_id']) ? $modules['company_id'] : null;
    $authLoginUserDetail = isset($modules['authLoginUserDetail']) ? $modules['authLoginUserDetail'] : null;
    $loginUserId = isset($authLoginUserDetail?->id) ? $authLoginUserDetail?->id : null;
    $hasPersonalOnly = !empty($modules['personal_data_permission']) && empty($modules['all_data_permission']);
@endphp
@section('title', $page_title)

@section('page_leavel_style')
    {{-- <link rel="stylesheet" href="https://cdn.datatables.net/1.12.4/css/dataTables.bootstrap5.min.css" /> --}}
    {{-- <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css"> --}}
    <link rel="stylesheet" href="{{ asset('software/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('software/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
@endsection

@section('content')
    <div class="px-1">
        <div class="d-lg-flex justify-content-lg-between flex-column flex-lg-row">
            @include('software.inlcudes.breadcrumb', [
                'breadcrumbArray' => [['title' => $page_title, 'url' => '']],
                'route' => $route,
                'show_add_btn' => isset($modules['add_permission']) ? $modules['add_permission'] : false,
                'show_filter_btn' => true,
                'show_back_btn' => false,
                'show_export_btn' =>
                    isset($modules['excel_permission']) &&
                    isset($modules['print_permission']) &&
                    ($modules['excel_permission'] || $modules['print_permission'])
                        ? true
                        : false,
                'show_excal_btn' => isset($modules['excel_permission']) ? $modules['excel_permission'] : false,
                'show_print_btn' => isset($modules['print_permission']) ? $modules['print_permission'] : false,
            ])
            
        </div>
    </div>

    <!-- Attendance Correction Modal -->
    {{-- <div class="modal fade" id="attendanceCorrectionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Attendance Correction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="attendanceCorrectionForm" action="{{ route('attendance.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="records_source" value="manually">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="correction_company_id" class="form-label">Company <span class="text-danger">*</span></label>
                                <select id="correction_company_id" name="company_id" class="form-select select2" required>
                                    <option value="">Select Company</option>
                                </select>
                            </div>
                             <div class="col-md-6 mb-3">
                                <label for="correction_employee_id" class="form-label">Employee Name <span class="text-danger">*</span></label>
                                <select id="correction_employee_id" name="employee_id" class="form-select select2" required>
                                    <option value="">Select Employee</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="correction_shift_id" class="form-label">Shift Name <span class="text-danger">*</span></label>
                                <select id="correction_shift_id" name="shift_id" class="form-select select2" required>
                                    <option value="">Select Shift</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="correction_attendance_date" class="form-label">Attendance Date <span class="text-danger">*</span></label>
                                <input type="date" id="correction_attendance_date" name="attendance_date" class="form-control" required value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="correction_create_date" class="form-label">Entry Date</label>
                                <input type="date" id="correction_create_date" name="create_date" class="form-control" value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="correction_punch_in_time" class="form-label">Punch In/Out Time <span class="text-danger">*</span></label>
                                <input type="time" id="correction_punch_in_time" name="punch_in_time" class="form-control" step="1" required value="{{ date('H:i:s') }}">
                            </div>
                             <div class="col-md-6 mb-3">
                                <label for="correction_attendace_type" class="form-label">Punch Type <span class="text-danger">*</span></label>
                                <select id="correction_attendace_type" name="attendace_type" class="form-select select2" required>
                                    <option value="">Select Type</option>
                                    @if(isset($AttendaceType))
                                        @foreach ($AttendaceType as $key => $type)
                                            <option value="{{ $key }}">{{ $type }}</option>
                                        @endforeach
                                    @else
                                         <option value="in">In</option>
                                         <option value="out">Out</option>
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="correction_remark" class="form-label">Remark</label>
                                <textarea id="correction_remark" name="remark" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                         <div class="alert alert-danger d-none" id="correctionErrorMsg"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="btnCorrectionSubmit">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div> --}}
    <div class="row my-3">
        <div class="col-md-12 mb-5" id="filter_section">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        {{-- <div class="col-md-3">
                            <label class="form-label">Filter by Year / Month / Amount</label>
                            <input type="search" class="form-control search" name="search" placeholder="search..."
                                autofocus>
                        </div> --}}
                        @if (!$company_id)
                            <div class="col-md-3 mb-2 col-sm-12">
                                <div class="form-group">
                                    <label class="form-label">Filter by Company</label>
                                    <select id="company_id" name="company_id"
                                        class="form-control search_by_company select2 select_filter"
                                        data-append="search_by_company"
                                        data-selectedcompanyid="{{ request('filter_company') }}">
                                        <option value="">Filter by Company</option>
                                    </select>
                                </div>
                            </div>
                        @endif

                        <div class="col-md-3 col-sm-12">
                            <div class="form-group">
                                <label class="form-label">Filter by Employee</label>
                                <select id="employee_id" name="employee_id"
                                    class="form-control search_by_employee select2 select_filter"
                                    data-append="search_by_employee"
                                    {{ $hasPersonalOnly ? 'disabled' : '' }}
                                    data-selectedemployeeid="{{ $hasPersonalOnly ? $loginUserId : request('filter_employee') }}">
                                    <option value="">Filter by Employee</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 mb-2 col-sm-12">
                            <div class="form-group">
                                <label class="form-label">Filter by Shift</label>
                                <select id="shift_id" name="shift_id"
                                    class="form-control search_by_shift select2 select_filter"
                                    data-append="search_by_shift">
                                    <option value="">Filter by shift</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label">Filter by Date</label>
                            <input type="text" name="employee_date" id="employee_date"
                                class="form-control employee_daterangepicker table_filter"
                                placeholder="DD/MM/YYYY to DD/MM/YYYY"
                                value="{{ request('filter_date') }}">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label for="attendance_status" class="form-label">Filter by Status</label>
                            <select id="attendance_status" name="attendance_status" class="form-select select2 select_filter">
                                <option value="all" {{ request('attendance_status') == 'all' ? 'selected' : '' }}>All Statuses</option>
                                <option value="present" {{ request('attendance_status') == 'present' ? 'selected' : '' }}>Present</option>
                                <option value="absent" {{ request('attendance_status') == 'absent' ? 'selected' : '' }}>Absent</option>
                                <option value="late" {{ request('attendance_status') == 'late' ? 'selected' : '' }}>Late Punch</option>
                                <option value="early" {{ request('attendance_status') == 'early' ? 'selected' : '' }}>Early Go</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label for="records_source" class="form-label">Filter by Source</label>
                            <select id="records_source" name="records_source" class="form-select select2 select_filter">
                                <option value="all">All Sources</option>
                                <option value="old_crm">Old CRM</option>
                                <option value="manually">Manual</option>
                                <option value="biometric">Biometric</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <div class="flex-grow-1">
                                <label for="attendace_type" class="form-label">Filter by Attendance Type</label>
                                <select id="attendace_type" name="attendace_type"
                                    class="form-select select2 select_filter">
                                    <option value="all">All Types</option>
                                    @foreach ($AttendaceType as $type)
                                        <option value="{{ $type }}">{{ $type }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 d-flex align-items-end mb-2">
                            <div>
                                <button type="button" title="Clear Filter" id="cilory_filter"
                                    class="btn btn-outline-danger btn-icon">
                                    <i class="ti ti-x"></i>
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        
        @if(isset($activeBiometricMachine) && $activeBiometricMachine)
        <div class="col-md-12 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center">
                            <i class="ti ti-clock me-2 text-primary" style="font-size: 1.25rem;"></i>
                            <div>
                                <label class="form-label mb-0 text-muted" style="font-size: 0.875rem;">Last Sync</label>
                                <div id="last-sync-time" class="fw-semibold">
                                    @if($activeBiometricMachine->last_sync_at)
                                        {{ $activeBiometricMachine->last_sync_at->format('d/m/Y, h:i A') }}
                                        <small class="text-muted">({{ $activeBiometricMachine->getLastSyncTimeAgo() }})</small>
                                    @else
                                        <span class="text-warning">Never synced</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary" id="sync-biometric-btn" 
                            data-machine-id="{{ $activeBiometricMachine->id }}"
                            data-provider="{{ ucfirst($activeBiometricMachine->provider_type) }}">
                            <i class="ti ti-refresh me-1"></i> Sync Now
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif
        
        <div class="col-md-12">
            <div class="card p-0 m-0">
                <div class="card-datatable text-nowrap m-0 p-0">
                    <div class="card-datatable table-responsive">
                        <table id="yajra-datatables" class="dt-responsive table table-hover">
                            @if (isset($columns))
                                <thead>
                                    <tr>
                                        @foreach ($columns as $item)
                                            <th class="{{ $item?->className ?? '' }}">
                                                {{ ucfirst($item?->td_label ?? $item?->name) ?? '' }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page_leavel_script')
    <!-- Data tables -->
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables.net-bs5/1.13.8/dataTables.bootstrap5.min.js" referrerpolicy="no-referrer"></script> --}}
    
    <!-- Data tables -->
    <script>
        // Disable AMD/RequireJS detection to force DataTables to attach to window.jQuery
        if (window.define) {
            window.oldDefine = window.define;
            window.define = null;
        }
    </script>
    
    <!-- Official DataTables CDN (Core + BS5) -->
    <script src="https://cdn.datatables.net/v/bs5/dt-1.13.8/datatables.min.js"></script>

    <script>
        // Restore AMD
        if (window.oldDefine) {
            window.define = window.oldDefine;
            window.oldDefine = null;
        }
    </script>

    <script type="text/javascript">
        console.log("Attendance Page Script Starting Execution (Section Yeild)...");

        // Use window.addEventListener('load') to ensure ALL scripts are fully loaded and executed
        window.addEventListener('load', function() {
            console.log("Attendance Page Window Load Triggered.");
            console.log("jQuery Version:", typeof jQuery !== 'undefined' ? jQuery.fn.jquery : 'Not Defined');
            console.log("DataTable Function:", typeof jQuery !== 'undefined' && jQuery.fn.DataTable ? 'Defined' : 'Not Defined');

            if (typeof jQuery === 'undefined') {
                console.error("CRITICAL: jQuery is NOT defined!");
                return;
            }
            if (!jQuery.fn.DataTable) {
                 console.error("CRITICAL: DataTable is NOT defined on jQuery object!");
                 // Attempt to find if it's attached to a different jQuery instance if possible, though unlikely in this setup
                 return;
            }

            // Explicitly use the global jQuery instance
            var $ = jQuery;

            initAttendancePage($);
        });

        // Define the init function to be called on load
        function initAttendancePage($) {
            console.log("Initializing Attendance Page Logic...");
            
            // Set Moment.js locale
            // moment.locale('en'); // Ensure moment is available or check for it
            
            // Safely destroy existing DataTable if it exists
             if ($.fn.DataTable.isDataTable('#yajra-datatables')) {
                  console.warn("DataTable already initialized. Destroying...");
                  $('#yajra-datatables').DataTable().destroy();
             }

            // AJAX Setup
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Initialize date range picker
            function initDateRangePicker(selector) {
                if ($(selector).length > 0) {
                    var inputVal = $(selector).val();
                    var startDate = moment().startOf('day');
                    var endDate = moment().endOf('day');

                    if (inputVal && inputVal.includes(' to ')) {
                        var dates = inputVal.split(' to ');
                        if (dates.length === 2) {
                            startDate = moment(dates[0], 'DD/MM/YYYY');
                            endDate = moment(dates[1], 'DD/MM/YYYY');
                        }
                    }

                    $(selector).daterangepicker({
                        autoUpdateInput: false,
                        startDate: startDate,
                        endDate: endDate,
                        locale: {
                            format: 'DD/MM/YYYY',
                            cancelLabel: 'Clear'
                        },
                        ranges: {
                            'Today': [moment(), moment()],
                            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                            'This Month': [moment().startOf('month'), moment().endOf('month')],
                            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment()
                                .subtract(1, 'month').endOf('month')
                            ]
                        }
                    });

                    $(selector).on('apply.daterangepicker', function(ev, picker) {
                        $(this).val(picker.startDate.format('DD/MM/YYYY') + ' to ' + picker.endDate.format('DD/MM/YYYY'));
                        if (window.dtable) window.dtable.draw();
                    });

                    $(selector).on('cancel.daterangepicker', function() {
                        $(this).val('');
                        if (window.dtable) window.dtable.draw();
                    });
                    
                    // Force update on any range click
                    $(document).on('click', '.ranges li', function() {
                        setTimeout(function() {
                            var picker = $(selector).data('daterangepicker');
                            if (picker && $(this).hasClass('active')) {
                                $(selector).val(picker.startDate.format('DD/MM/YYYY') + ' to ' + picker.endDate.format('DD/MM/YYYY'));
                                 if (window.dtable) window.dtable.draw();
                            }
                        }.bind(this), 50);
                    });

                    // Ensure the input field value is NOT cleared if it was already set
                    if (inputVal) {
                        $(selector).val(inputVal);
                    }
                }
            }

            var urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('attendance_status') || urlParams.get('filter_company') || urlParams.get('filter_date') || urlParams.get('filter_employee')) {
                $('#filter_section').show();
            }

            initDateRangePicker('.employee_daterangepicker');

            // Initialize DataTable
            var attendanceDateIndex = 4;
            @if ($company_id)
                attendanceDateIndex = 3;
            @endif

            try {
                if (window.dtable) {
                    console.log("dtable already initialized, skipping.");
                    return;
                }
                
                window.dtable = $('#yajra-datatables').DataTable({
                    processing: true,
                    serverSide: true,
                    dom: '<"table-responsive"t><"datatable-footer d-flex justify-content-between align-items-center flex-wrap px-3 py-2"l i p>',
                    order: [
                        [attendanceDateIndex, 'desc']
                    ],
                    ajax: {
                        url: window.location.href.split('?')[0],
                        type: "GET",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            'Accept': 'application/json'
                        },
                        data: function(d) {
                            var innerParams = new URLSearchParams(window.location.search);
                            d.search = $('input[name="search"]').val();
                            d.filter_company = $('#company_id').val() || innerParams.get('filter_company');
                            d.filter_employee = {!! $hasPersonalOnly ? "'$loginUserId'" : "($('#employee_id').val() || innerParams.get('filter_employee'))" !!};
                            d.filter_shift = $('#shift_id').val() || innerParams.get('filter_shift');
                            d.attendace_type = $('#attendace_type').val();
                            d.records_source = $('#records_source').val();
                            d.attendance_status = $('#attendance_status').val() || innerParams.get('attendance_status');
                            d.filter_date = $('input[name="employee_date"]').val() || innerParams.get('filter_date');
                        },
                         error: function (xhr, error, thrown) {
                            console.error("DataTable Ajax Error Details:", xhr, error, thrown);
                        }
                    },
                    columns: {!! isset($columns) ? json_encode($columns) : '[]' !!},
                });
                console.log("DataTable Initialized Successfully", window.dtable);
            } catch (e) {
                console.error("DataTable Init Failed:", e);
            }

            // Handlers
            $(document).on('change', '.select_filter', function(event) {
                event.preventDefault();
                if (window.dtable) window.dtable.draw();
            });

            $('input[name="search"]').keyup(function() {
                if (window.dtable) window.dtable.draw();
            });

            $("#cilory_filter").click(function() {
                $('.select_filter').val(null).trigger('change');
                $('#attendace_type').val('all').trigger('change');
                $('#records_source').val('all').trigger('change');
                $('#attendance_status').val('all').trigger('change');
                $('input[name="employee_date"]').val('');
                $('.search').val('');
                if (window.dtable) window.dtable.draw();
            });

            // Attendance Correction Modal Logic
            /*
             $('#attendanceCorrectionModal .select2').select2({
                dropdownParent: $('#attendanceCorrectionModal'),
                width: '100%'
            });
            */

            // Biometric Sync Button Handler
            $(document).on('click', '#sync-biometric-btn', function() {
                var $btn = $(this);
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
                    success: function(response) {
                        if (response.status) {
                            // Update last sync time
                            if (response.data && response.data.last_sync_at) {
                                var syncDate = new Date(response.data.last_sync_at);
                                // Format: dd/mm/yyyy, hh:mm AM/PM
                                var day = String(syncDate.getDate()).padStart(2, '0');
                                var month = String(syncDate.getMonth() + 1).padStart(2, '0');
                                var year = syncDate.getFullYear();
                                var hours = syncDate.getHours();
                                var minutes = String(syncDate.getMinutes()).padStart(2, '0');
                                var ampm = hours >= 12 ? 'pm' : 'am';
                                hours = hours % 12;
                                hours = hours ? hours : 12;
                                hours = String(hours).padStart(2, '0');
                                
                                var formattedDate = day + '/' + month + '/' + year + ', ' + hours + ':' + minutes + ' ' + ampm;
                                var syncTimeHtml = formattedDate;
                                
                                // Add last sync time ago if available
                                if (response.data.last_sync_time_ago) {
                                    syncTimeHtml += ' <small class="text-muted">(' + response.data.last_sync_time_ago + ')</small>';
                                }
                                
                                // Add last punch time if available
                                if (response.data.last_punch_time) {
                                    var punchDate = new Date(response.data.last_punch_time);
                                    var punchDay = String(punchDate.getDate()).padStart(2, '0');
                                    var punchMonth = String(punchDate.getMonth() + 1).padStart(2, '0');
                                    var punchYear = punchDate.getFullYear();
                                    var punchHours = punchDate.getHours();
                                    var punchMinutes = String(punchDate.getMinutes()).padStart(2, '0');
                                    var punchAmpm = punchHours >= 12 ? 'pm' : 'am';
                                    punchHours = punchHours % 12;
                                    punchHours = punchHours ? punchHours : 12;
                                    punchHours = String(punchHours).padStart(2, '0');
                                    var formattedPunchTime = punchDay + '/' + punchMonth + '/' + punchYear + ', ' + punchHours + ':' + punchMinutes + ' ' + punchAmpm;
                                    syncTimeHtml += '<br><small class="text-info"><i class="ti ti-clock me-1"></i>Last Punch: ' + formattedPunchTime + '</small>';
                                }
                                
                                $('#last-sync-time').html(syncTimeHtml);
                            }
                            
                            // Show success message with detailed counts
                            var successMsg = response.message || 'Sync completed successfully';
                            if (response.data) {
                                var details = [];
                                if (response.data.records_fetched !== undefined) {
                                    details.push('Fetched: ' + response.data.records_fetched);
                                }
                                if (response.data.records_transformed !== undefined) {
                                    details.push('Transformed: ' + response.data.records_transformed);
                                }
                                if (response.data.records_stored !== undefined) {
                                    details.push('Stored: ' + response.data.records_stored);
                                }
                                if (details.length > 0) {
                                    successMsg += '<br><small>' + details.join(' | ') + '</small>';
                                }
                            }
                            
                            if (typeof toastr !== 'undefined') {
                                toastr.success(successMsg, 'Sync Completed', {
                                    timeOut: 5000,
                                    extendedTimeOut: 2000
                                });
                            } else {
                                alert(successMsg);
                            }
                            
                            // Refresh DataTable if flag is set or if records were stored
                            if (response.data && (response.data.refresh_datatable || response.data.records_stored > 0)) {
                                if (window.dtable) {
                                    window.dtable.ajax.reload(null, false); // false = don't reset paging
                                    console.log('DataTable refreshed after sync');
                                }
                            }
                        } else {
                            if (typeof toastr !== 'undefined') {
                                toastr.error(response.message || 'Sync failed');
                            } else {
                                alert(response.message || 'Sync failed');
                            }
                        }
                    },
                    error: function(xhr) {
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
                    complete: function() {
                        $btn.data('syncing', false);
                        $btn.prop('disabled', false);
                        $btn.html(originalHtml);
                    }
                });
            });
        }

        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            initAttendancePage();
        } else {
            document.addEventListener('DOMContentLoaded', initAttendancePage);
        }

    </script>



    @if (!$company_id)
        @include('utils.getCompany')
    @endif
    @include('utils.getEmployee')
    @include('utils.getShift')
    @include('software.inlcudes.script-delete-record')
    @include('software.inlcudes.script-restore-record')
    @include('software.inlcudes.script-update-status')

@endsection
