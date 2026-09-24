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
    // dd($modules);
@endphp
@section('title', $page_title)

@section('page_leavel_style')
    {{-- <link rel="stylesheet" href="https://cdn.datatables.net/1.12.4/css/dataTables.bootstrap5.min.css" /> --}}
    {{-- <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css"> --}}
    <link rel="stylesheet" href="{{ asset('software/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('software/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
    <div class="row my-3">
        <div class="col-md-12 mb-5" id="filter_section">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <!-- Search Input -->
                        <div class="col-md-3 col-sm-12">
                            <label class="form-label">Search</label>
                            <input type="search" class="form-control search" name="search" placeholder="search..."
                                autofocus>
                        </div>
                        @if (!$company_id)
                            <!-- Company Filter -->
                            <div class="col-md-3 col-sm-12">
                                <label class="form-label">Filter by Company</label>
                                <select id="company_id" name="company_id"
                                    class="form-control search_by_company select2 select_filter"
                                    data-append="search_by_company" data-append-leaveType="#filter_leave_type"
                                    data-selectedcompanyid="{{ request('company_id') }}">
                                    <option value="">Filter by Company</option>
                                    <!-- Options for Company will be dynamically loaded here -->
                                </select>
                            </div>
                        @endif
                        <div class="col-md-3 col-sm-12">
                            <div class="form-group">
                                <label class="form-label">Filter by Employee</label>
                                <select id="employee_id" name="employee_id"
                                    class="form-control search_by_employee select2 select_filter"
                                    data-append="search_by_employee"
                                    data-exclude-contractor="1">
                                    <option value="">Filter by Employee</option>
                                </select>
                            </div>
                        </div>




                        {{-- Leave Type --}}
                        <div class="col-md-3 col-sm-12">
                            <label class="form-label">Filter by Leave Type</label>
                            <select id="filter_leave_type" name="filter_leave_type"
                                class="form-control search_by_leave_type select2 select_filter"
                                data-append="data-append-leaveType">
                                <option value="">Select Leave Type</option>

                            </select>
                        </div>

                        {{-- Filter by Halfday / Fullday --}}
                        <div class="col-md-3 col-sm-12">
                            <label class="form-label">Filter by Day</label>
                            <select id="filter_halfday_fullday" name="halfday_fullday"
                                class="form-control select2 mb-2 select_filter" style="width: 100%;">
                                <option value="">Select Day Type</option>
                                @foreach ($leaveForDay as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </select>

                        </div>



                        <div class="col-md-3 mb-2">
                            <label class="form-label">Filter by Date</label>
                            <input type="text" name="fromdate_time" class="form-control my_daterangepicker table_filter"
                                value="{{ request('filter_date') }}" placeholder="Filter by date range">
                        </div>




                        <div class="col-md-3 d-flex align-items-end mb-2">
                            <div class="flex-grow-1 me-2">
                                <label for="status_filter" class="form-label">Filter by Status</label>
                                <select id="status_filter" name="status" class="form-select select2">
                                    <option value="all">All</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Approved">Approve</option>
                                    <option value="Rejected">Reject</option>
                                </select>
                            </div>
                            <div>
                                <button type="button" title="Clear Filter" id="cilory_filter"
                                    class="btn btn-outline-danger btn-icon mt-2"><i class="ti ti-x"></i></button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="card">
                <div class="card-datatable text-nowrap mt-3">
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

    {{-- <div class="modal fade show" id="leaveApplicationAcutionModel" tabindex="-1" aria-hidden="true" style="display: block"> --}}
    <!-- Leave Application Action Modal -->
    <div class="modal fade" id="leaveApplicationAcutionModel" tabindex="-1" aria-labelledby="leaveModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: visible;">

                <!-- Modal Header -->
                <div class="modal-header bg-white py-3 px-4 position-relative border-bottom" style="border-top-left-radius: 16px; border-top-right-radius: 16px; border-color: #e2e8f0 !important;">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md bg-label-primary rounded-circle p-2 me-3 d-flex align-items-center justify-content-center shadow-xs">
                            <i class="ti ti-file-certificate fs-3 text-primary"></i>
                        </div>
                        <div>
                            <h5 class="modal-title text-dark mb-0 fw-bold" id="leaveModalLabel">Leave Application Decision</h5>
                            <small class="text-muted fw-semibold" id="modal_stage_label">Review details and approve or reject request</small>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-icon btn-light text-secondary" data-bs-dismiss="modal" aria-label="Close"
                        style="border-radius: 50%; width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border: 1px solid #e2e8f0; background: #f8fafc;">
                        <i class="ti ti-x fs-5"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body p-4" style="background-color: #f8f9fa;">

                    <!-- Leave Details Grid Card -->
                    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; background: #ffffff;">
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <!-- Employee Info -->
                                <div class="{{ !$company_id ? 'col-md-6' : 'col-md-12' }}">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm bg-label-primary rounded p-2 me-3 d-flex align-items-center justify-content-center">
                                            <i class="ti ti-user fs-4 text-primary"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted text-uppercase fw-semibold d-block" style="font-size: 11px;">Employee</small>
                                            <span id="team_person_name" class="fw-bold text-dark fs-6">-</span>
                                            <span id="employee_code_badge" class="badge bg-label-secondary ms-1"></span>
                                        </div>
                                    </div>
                                </div>

                                @if (!$company_id)
                                    <!-- Company Info (Only for Master Admin) -->
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm bg-label-info rounded p-2 me-3 d-flex align-items-center justify-content-center">
                                                <i class="ti ti-building fs-4 text-info"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted text-uppercase fw-semibold d-block" style="font-size: 11px;">Company</small>
                                                <span id="compay_name" class="fw-bold text-dark fs-6">-</span>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div class="col-12"><hr class="my-1" style="border-color: #f0f2f5;"></div>

                                <!-- Leave Type -->
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm bg-label-success rounded p-2 me-3 d-flex align-items-center justify-content-center">
                                            <i class="ti ti-tag fs-4 text-success"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted text-uppercase fw-semibold d-block" style="font-size: 11px;">Leave Type</small>
                                            <span id="leave_type" class="badge bg-label-primary fs-6 fw-bold">-</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Day Type -->
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm bg-label-warning rounded p-2 me-3 d-flex align-items-center justify-content-center">
                                            <i class="ti ti-clock fs-4 text-warning"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted text-uppercase fw-semibold d-block" style="font-size: 11px;">Day Type</small>
                                            <span id="leave_day_type" class="fw-bold text-dark">-</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- From & To Dates -->
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm bg-label-danger rounded p-2 me-3 d-flex align-items-center justify-content-center">
                                            <i class="ti ti-calendar-event fs-4 text-danger"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted text-uppercase fw-semibold d-block" style="font-size: 11px;">From Date</small>
                                            <span id="fromdate_time" class="fw-bold text-dark fs-6">-</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm bg-label-secondary rounded p-2 me-3 d-flex align-items-center justify-content-center">
                                            <i class="ti ti-calendar-check fs-4 text-secondary"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted text-uppercase fw-semibold d-block" style="font-size: 11px;">To Date</small>
                                            <span id="todate_time" class="fw-bold text-dark fs-6">-</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Reason -->
                                <div class="col-12 mt-2">
                                    <div class="p-3 rounded border" style="background-color: #f8fafc; border-color: #e2e8f0 !important;">
                                        <div class="d-flex align-items-center mb-1">
                                            <i class="ti ti-message-2 text-primary me-2 fs-5"></i>
                                            <strong class="text-dark">Reason for Leave:</strong>
                                        </div>
                                        <p id="leave_reason" class="mb-0 text-muted ps-4 fst-italic fs-6">-</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hidden Inputs -->
                    <input type="hidden" id="leave_application_id" value="" />
                    <input type="hidden" id="company_id" value="" />
                    <input type="hidden" id="selected_decision_status" value="" />

                    <!-- Action Decision Card -->
                    <div class="card border-0 shadow-sm" style="border-radius: 12px; background: #ffffff;">
                        <div class="card-body p-4">
                            <label class="form-label fw-bold text-dark mb-3 fs-6 d-block">
                                <i class="ti ti-gavel text-primary me-1"></i> Select Decision <span class="text-danger">*</span>
                            </label>

                            <!-- Decision Action Cards -->
                            <div class="row g-3 mb-3">
                                <div class="col-6">
                                    <div class="decision-card p-3 rounded-3 border text-center cursor-pointer transition-all"
                                        id="card-approve" onclick="selectDecision('approved')"
                                        style="cursor: pointer; border: 2px solid #e2e8f0; background: #f8fafc; transition: all 0.2s;">
                                        <div class="fs-2 mb-1">✅</div>
                                        <div class="fw-bold text-success fs-6">Approve</div>
                                        <small class="text-muted d-block">Proceed to next stage / final</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="decision-card p-3 rounded-3 border text-center cursor-pointer transition-all"
                                        id="card-reject" onclick="selectDecision('rejected')"
                                        style="cursor: pointer; border: 2px solid #e2e8f0; background: #f8fafc; transition: all 0.2s;">
                                        <div class="fs-2 mb-1">❌</div>
                                        <div class="fw-bold text-danger fs-6">Reject</div>
                                        <small class="text-muted d-block">Decline this leave application</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Rejection Reason Container -->
                            <div id="rejectBoxContainer" class="mt-3" style="display: none;">
                                <label for="rejectTextarea" class="form-label fw-bold text-dark">
                                    Reason for Rejection <span class="text-danger">*</span>
                                </label>
                                <textarea id="rejectTextarea" name="reject" class="form-control" rows="3"
                                    placeholder="Please provide the reason for rejection..."></textarea>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Modal Footer -->
                <div class="modal-footer bg-white border-top px-4 py-3 d-flex justify-content-end" style="border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
                    <button type="button" class="btn btn-outline-secondary px-4 me-2" data-bs-dismiss="modal">
                        <i class="ti ti-x me-1"></i> Cancel
                    </button>
                    <button type="button" id="submitBtn" class="btn btn-primary px-4 shadow-sm">
                        <i class="ti ti-send me-1"></i> Submit Decision
                    </button>
                </div>

            </div>
        </div>
    </div>



@endsection


@section('page_leavel_script')
    <!-- Data tables -->
    {{-- <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.12.4/js/dataTables.bootstrap5.min.js"></script> --}}
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="{{ asset('software/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
@endsection

@push('page_scripts')
    @if (!$company_id)
        @include('utils.getCompany')
    @endif
    @if (!$parent_type_id)
        @include('utils.getEmployee')
    @endif
    @include('utils.getLeaveType')

    <script type="text/javascript">
        let filterParams = {};

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });


        $(function() {
            var picker = $('.my_daterangepicker').data('daterangepicker');
            if (picker) {
                picker.maxDate = false;

                var requestDate = '{{ request("filter_date") }}';
                var startDate = moment().startOf('month');
                let endDate = moment().endOf('year');

                if (requestDate) {
                    var dates = requestDate.split(' - ');
                    if (dates.length === 2) {
                        var parsedStart = moment(dates[0].trim(), 'DD/MM/YYYY');
                        var parsedEnd = moment(dates[1].trim(), 'DD/MM/YYYY');
                        if (parsedStart.isValid() && parsedEnd.isValid()) {
                            startDate = parsedStart;
                            endDate = parsedEnd;
                        }
                    }
                }

                picker.setStartDate(startDate);
                picker.setEndDate(endDate);
                picker.callback(startDate, endDate, 'Initial range');
            }
        });


        let dtable = null;

        $(document).ready(function() {
            dtable = $('#yajra-datatables').DataTable({
                processing: true,
                serverSide: true,
                order: [
                    [0, 'ASC']
                ],
                // dom: '<"table-responsive"t><"d-flex justify-content-between align-items-center"<"ps-3"l>i<"pe-4"p>>',
                dom: '<"table-responsive"t><"datatable-footer d-flex justify-content-between align-items-center flex-wrap px-3 py-2"l i p>',

                ajax: {
                    url: "{{ route($route . '.index') }}",
                    type: "GET",
                    data: function(data) {
                        var urlParams = new URLSearchParams(window.location.search);
                        if (urlParams.get('company_id') || urlParams.get('filter_date') || urlParams.get('employee_id')) {
                            $('#filter_section').show();
                        }
                        data.search = $('input[name="search"]').val();
                        data.company_id = $('#company_id').val() || urlParams.get('company_id');
                        data.employee_id = $('#employee_id').val() || urlParams.get('employee_id'); // add employee filter
                        data.filter_leave_type = $('#filter_leave_type').val();

                        data.halfday_fullday = $('#filter_halfday_fullday').val();
                        data.status = $('#status_filter').val();
                        
                        var fromDateVal = $('input[name="fromdate_time"]').val() || urlParams.get('filter_date') || '';
                        data.filter_quotation_date = fromDateVal.replace(' - ', ' to ');
                    }
                },
                columns: {!! isset($columns) ? json_encode($columns) : [] !!},
                language: {
                    searchPlaceholder: 'Search...'
                }
            });

            $('.select_filter').on('change', function(event) {
                event.preventDefault();
                dtable.draw();
            });

            $('input[name="fromdate_time"]').on('apply.daterangepicker', function() {
                dtable.draw();
            });

            $('#status_filter').on('change', function() {
                dtable.ajax.reload();
            });

            $('input[name="search"]').keyup(function() {
                filterParams.search = $(this).val();
                dtable.draw();
            });

            $('#cilory_filter').on('click', function() {
                const defaultStartDate = moment().startOf('month');
                // const defaultEndDate = moment().add(1, 'month').endOf('month');
                const defaultEndDate = moment().endOf('year');
                // console.log("L-340", moment().add(1, 'month').endOf('month'), moment().endOf('year'));

                const picker = $('input[name="fromdate_time"]').data('daterangepicker');

                picker.setStartDate(defaultStartDate);
                picker.setEndDate(defaultEndDate);

                $('input[name="fromdate_time"]').val(
                    defaultStartDate.format('DD/MM/YYYY') + ' - ' + defaultEndDate.format('DD/MM/YYYY')
                );

                $('.select_filter').val(null).trigger('change');
                $('#status_filter').val('all').trigger('change');
                $('.search').val('');

                dtable.draw();
            });

            function getDateRange() {
                let dateRangeVal = $('input[name="fromdate_time"]').val();
                if (!dateRangeVal) return [null, null];
                let [from_date, to_date] = dateRangeVal.split(' - ');
                return [from_date, to_date];
            }

            // $('#export_excel_btn').on('click', function(e) {
            //     e.preventDefault();

            //     const [from_date, to_date] = getDateRange();

            //     let queryParams = $.param({
            //         search: $('input[name="search"]').val(),
            //         status: $('#status_filter').val(),
            //         company: $('#company_id').val(),
            //         leave_type: $('#filter_leave_type').val(),
            //         halfday_fullday: $('#filter_halfday_fullday').val(),
            //         from_date: from_date,
            //         to_date: to_date
            //     });

            //     let url = "{{ route($route . '.export.excel') }}" + "?" + queryParams;
            //     window.location.href = url;
            // });
            $('#export_excel_btn').on('click', function(e) {
                e.preventDefault();
                let filterQuotationDate = $('input[name="fromdate_time"]').val() || '';
                if (filterQuotationDate.includes(' - ')) {
                    filterQuotationDate = filterQuotationDate.replace(' - ', ' to ');
                }
                let queryParams = $.param({
                    search: $('input[name="search"]').val() || '',
                    status: $('#status_filter').val() || '',
                    company_id: $('#company_id').val() || '',
                    employee_id: $('#employee_id').val() || '',
                    filter_leave_type: $('#filter_leave_type').val() || '',
                    halfday_fullday: $('#filter_halfday_fullday').val() || '',
                    filter_quotation_date: filterQuotationDate
                });

                let url = "{{ route($route . '.export.excel') }}" + "?" + queryParams;

                window.location.href = url;
            });

            $('#print_btn').on('click', function(e) {
                e.preventDefault();


                let filterQuotationDate = $('input[name="fromdate_time"]').val() || '';

                if (filterQuotationDate.includes(' - ')) {
                    filterQuotationDate = filterQuotationDate.replace(' - ', ' to ');
                }

                console.log("Filter Quotation Date:", filterQuotationDate);

                // Build query parameters
                let queryParams = $.param({
                    search: $('input[name="search"]').val() || '',
                    status: $('#status_filter').val() || '',
                    company_id: $('#company_id').val() || '',
                    employee_id: $('#employee_id').val() || '',
                    filter_leave_type: $('#filter_leave_type').val() || '',
                    halfday_fullday: $('#filter_halfday_fullday').val() || '',
                    filter_quotation_date: filterQuotationDate
                });

                // Debugging: check the full URL
                let url = "{{ route($route . '.print') }}" + "?" + queryParams;
                console.log("Print URL:", url);

                // Open the print page
                window.open(url, '_blank');
            });

            // Interactive Decision Selection Function
            window.selectDecision = function(decision) {
                $('#selected_decision_status').val(decision);
                if (decision === 'approved') {
                    $('#card-approve').css({
                        'border': '2px solid #28c76f',
                        'background-color': '#e8fadf',
                        'box-shadow': '0 4px 12px rgba(40, 199, 111, 0.25)'
                    });
                    $('#card-reject').css({
                        'border': '2px solid #e2e8f0',
                        'background-color': '#f8fafc',
                        'box-shadow': 'none'
                    });
                    $('#rejectBoxContainer').slideUp(200);
                    $('#rejectTextarea').prop('required', false).val('');
                } else if (decision === 'rejected') {
                    $('#card-reject').css({
                        'border': '2px solid #ea5455',
                        'background-color': '#fceaea',
                        'box-shadow': '0 4px 12px rgba(234, 84, 85, 0.25)'
                    });
                    $('#card-approve').css({
                        'border': '2px solid #e2e8f0',
                        'background-color': '#f8fafc',
                        'box-shadow': 'none'
                    });
                    $('#rejectBoxContainer').slideDown(200);
                    $('#rejectTextarea').prop('required', true).focus();
                }
            };

            // Leave Application Modal Handling
            $(document).on("click", ".leaveApplicationAcutionModel", function() {
                const $target = $(this);
                const modal = $("#leaveApplicationAcutionModel");
                
                const id = $target.attr("data-id") || '';
                const company_id = $target.attr("data-company_id") || '';
                const company_name = $target.attr("data-company_name") || '-';
                const team_person_name = $target.attr("data-team_person_name") || '-';
                const employee_code = $target.attr("data-employee_code") || '';
                const leave_type = $target.attr("data-leave_type") || '-';
                const from_date = $target.attr("data-from_date") || '-';
                const to_date = $target.attr("data-to_date") || '-';
                const day_type = $target.attr("data-day_type") || '-';
                const day_detail = $target.attr("data-day_detail") || '';
                const leave_reason = $target.attr("data-leave_reason") || '-';
                const stage_label = $target.attr("data-stage_label") || '';

                modal.find('#leave_application_id').val(id);
                modal.find('#company_id').val(company_id);
                modal.find('#compay_name').text(company_name);
                modal.find('#team_person_name').text(team_person_name);
                if (employee_code) {
                    modal.find('#employee_code_badge').text(employee_code).show();
                } else {
                    modal.find('#employee_code_badge').hide();
                }
                modal.find('#leave_type').text(leave_type);
                modal.find('#leave_day_type').text(day_type + (day_detail && day_detail !== '-' ? ' (' + day_detail + ')' : ''));
                modal.find('#fromdate_time').text(from_date);
                modal.find('#todate_time').text(to_date);
                modal.find('#leave_reason').text(leave_reason);
                if (stage_label) {
                    modal.find('#modal_stage_label').text('Current: ' + stage_label);
                } else {
                    modal.find('#modal_stage_label').text('Review details and approve or reject request');
                }

                // Reset decision cards
                $('#selected_decision_status').val('');
                $('#card-approve').css({'border': '2px solid #e2e8f0', 'background-color': '#f8fafc', 'box-shadow': 'none'});
                $('#card-reject').css({'border': '2px solid #e2e8f0', 'background-color': '#f8fafc', 'box-shadow': 'none'});
                $('#rejectBoxContainer').hide();
                $('#rejectTextarea').val('');

                modal.modal('show');
            });

            $('#submitBtn').on('click', function() {
                const $btn = $(this);
                const modal = $("#leaveApplicationAcutionModel");
                const leave_application_id = modal.find("#leave_application_id").val();
                const status = $('#selected_decision_status').val();
                const rejectReason = modal.find('#rejectTextarea').val();
                const isReject = (status === 'rejected');

                if (!leave_application_id) return toastr.error("Leave Application ID is missing.");
                if (!status) return toastr.warning("Please click Approve or Reject to select your decision.");
                if (isReject && (!rejectReason || !rejectReason.trim())) {
                    toastr.error("Rejection reason is required.");
                    $('#rejectTextarea').focus();
                    return;
                }

                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('id', leave_application_id);
                formData.append('status', status);
                formData.append('reject', rejectReason);

                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Processing...');

                $.ajax({
                    url: "{{ route('submit-leavel-application') }}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(data) {
                        $btn.prop('disabled', false).html('<i class="ti ti-send me-1"></i> Submit Decision');
                        if (data.success) {
                            modal.modal('hide');
                            toastr.success(data.message || "Decision submitted successfully!");
                            dtable.ajax.reload(null, false);
                        } else {
                            toastr.error(data.message || 'Error submitting decision.');
                        }
                    },
                    error: function(xhr, status, error) {
                        $btn.prop('disabled', false).html('<i class="ti ti-send me-1"></i> Submit Decision');
                        console.error('Error:', error);
                        toastr.error('An error occurred. Please try again later.');
                    }
                });
            });

            $(document).on('click', '.file-preview', function() {
                const url = $(this).data('url');
                if (!url) return toastr.error('No file URL found');

                const isImage = /\.(jpg|jpeg|png|gif|bmp|webp)$/i.test(url);
                $('#fileIframe, #fileImage').hide().attr('src', '');

                if (isImage) {
                    $('#fileImage').attr('src', url).show();
                } else {
                    $('#fileIframe').attr('src', url).show();
                }

                $('#fileViewModal').modal('show');
            });

            $('#fileViewModal').on('hidden.bs.modal', function() {
                $('#fileIframe, #fileImage').hide().attr('src', '');
            });
        });
    </script>

    <!-- File View Modal -->
    <div class="modal fade" id="fileViewModal" tabindex="-1" aria-labelledby="fileViewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">File Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body d-flex justify-content-center align-items-center" style="height: 80vh;">
                    <iframe id="fileIframe" src="" frameborder="0"
                        style="width: 100%; height: 100%; display: none;"></iframe>
                    <img id="fileImage" src="" alt="Image Preview"
                        style="max-width: 100%; max-height: 100%; display: none;" />
                </div>
            </div>
        </div>
    </div>

    @include('software.inlcudes.script-delete-record')
    @include('software.inlcudes.script-restore-record')
    @include('software.inlcudes.script-update-status')
@endpush
