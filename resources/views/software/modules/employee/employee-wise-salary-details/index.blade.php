@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : null;
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $i = 0;
    $company_id = isset($modules['company_id']) ? $modules['company_id'] : null;

    // dd($modules);

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
    <div class="row my-3">
        <div class="col-md-12 mb-5" id="filter_section">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="form-label">Filter by Employee Code</label>
                            <input type="search" class="form-control table_filter" name="employee_code" placeholder="Enter Employee Code...">
                        </div>
                        @if (!$company_id)
                            <div class="col-md-3 col-sm-12">
                                <div class="form-group">
                                    <label class="form-label">Filter by Company</label>
                                    <select id="company_id" name="company_id"
                                        class="form-control search_by_company select2 select_filter"
                                        data-append="search_by_company">
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
                                    data-append="search_by_employee">
                                    <option value="">Filter by Employee</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-12">
                            <div class="form-group">
                                <label class="form-label">Filter by Salary Classification</label>
                                <select id="salary_classification" name="salary_classification"
                                    class="form-control select2 select_filter">
                                    <option value="">Filter by Salary Classification</option>
                                    <option value="PHS">Per Hours Salary</option>
                                    <option value="PDS">Per Day Salary</option>
                                    <option value="PMS">Per Month Salary</option>
                                    <option value="PWS">Per Work Salary</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-3 col-sm-12">
                            <div class="form-group">
                                <label class="form-label">Filter by PF Type</label>
                                <select id="pf_type" name="pf_type"
                                    class="form-control select2 select_filter">
                                    <option value="">Filter by PF Type</option>
                                    <option value="NO-PF">NO-PF</option>
                                    <option value="PF-ABRY">PF-ABRY</option>
                                    <option value="COMPANY GIVE BOTH SIDE PF">COMPANY GIVE BOTH SIDE PF</option>
                                    <option value="EMPLOYEE">EMPLOYEE</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-12">
                            <div class="form-group">
                                <label class="form-label">Filter by Salary Calculation Month Count</label>
                                <select id="salary_calculation_month_count" name="salary_calculation_month_count"
                                    class="form-control select2 select_filter">
                                    <option value="">Filter by Salary Calculation Month Count</option>
                                    <option value="Fix 30 Days">Fix 30 Days</option>
                                    <option value="Per Month Total Days">Per Month Total Days</option>
                                    <option value="Per Month Total Days - Week Off">Per Month Total Days - Week Off</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3 d-flex align-items-end mb-2">
                            <div class="flex-grow-1 me-2">
                                <label for="status_filter" class="form-label">Filter by Status</label>
                                <select id="status_filter" name="status" class="form-select select2">
                                    <option value="all">All</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
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
@endsection

@section('page_leavel_script')
    <!-- Data tables -->
    {{-- <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.12.4/js/dataTables.bootstrap5.min.js"></script> --}}
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="{{ asset('software/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
@endsection

@push('page_scripts')
    <script>
        $(document).ready(function() {

            // Initialize date range picker
            function initDateRangePicker(selector) {
                if ($(selector).length > 0) {
                    $(selector).daterangepicker({
                        autoUpdateInput: false,
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
                    }, function(start, end) {
                        $(selector).val(start.format('DD/MM/YYYY') + ' to ' + end.format('DD/MM/YYYY'));
                        dtable.draw(); // redraw table immediately when date is selected
                    });

                    $(selector).on('cancel.daterangepicker', function() {
                        $(this).val('');
                        dtable.draw(); // redraw table on clear
                    });
                }
            }

            initDateRangePicker('.employee_daterangepicker');

            // Reset filters
            $("#cilory_filter").click(function() {
                $('.select_filter').val(null).trigger('change');
                // $('#status_filter').val('all').trigger('change');
                $('.search').val('');
                $('input[name="employee_code"]').val('');
                $('.employee_daterangepicker').val('');
                dtable.draw();
            });

            // Redraw table on other filter change
            $('.table_filter').on('change', function() {
                if (dtable) {
                    dtable.draw();
                }
            });
            
            $('input[name="employee_code"]').keyup(function() {
                if (dtable) {
                    dtable.draw();
                }
            });

        });
    </script>

    <script type="text/javascript">
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });



        var dtable = null;
        $(document).ready(function() {
            dtable = $('#yajra-datatables').DataTable({
                processing: true,
                serverSide: true,
                // dom: '<"table-responsive"t><"d-flex justify-content-between align-items-center"<"ps-3"l>i<"pe-4"p>>',
                dom: '<"table-responsive"t><"datatable-footer d-flex justify-content-between align-items-center flex-wrap px-3 py-2"l i p>',

                order: [
                    [0, 'ASC']
                ],
                ajax: {
                    "url": "{{ route($route . '.index') }}",
                    'beforeSend': function(request) {
                        request.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr(
                            'content'));
                    },
                    type: "GET",
                    data: function(data) {
                        data.search = $('input[name="search"]').val();
                        data.filter_employee_code = $('input[name="employee_code"]').val();
                        data.filter_company = $('select[name="company_id"] option:selected').val();
                        data.filter_employee = $('select[name="employee_id"] option:selected').val();
                        data.filter_department_name = $('select[name="department_name"] option:selected').val();
                        data.filter_designation_name = $('select[name="designation_name"] option:selected').val();
                        data.filter_document_type = $('select[name="document_type"] option:selected').val();
                        data.filter_salary_classification = $('select[name="salary_classification"] option:selected').val();
                        data.filter_pf_type = $('select[name="pf_type"] option:selected').val();
                        data.filter_salary_calculation_month_count = $('select[name="salary_calculation_month_count"] option:selected').val();
                        data.filter_date = $('input[name="employee_date"]').val();
                        data.status = $('#status_filter').val();


                        // console.log("LN-234", data);
                    },
                },

                columns: {!! isset($columns) ? json_encode($columns) : [] !!},
                language: {
                    searchPlaceholder: 'Search...',
                }
            });
        });

        $(document).on('change', '.select_filter', function(event) {
            event.preventDefault();
            if (dtable) {
                dtable.draw();
            }
        });
        $('input[name="search"]').keyup(function() {
            if (dtable) {
                dtable.draw();
            }
        });
        $('#status_filter').on('change', function() {
            if (dtable) {
                dtable.draw();
            }
        });

        $('#filter_employee').change(function() {
            if (dtable) {
                dtable.draw();
            }
        });

        $('#document_type').change(function() {
            if (dtable) {
                dtable.draw();
            }
        });
        $(document).ready(function() {
            $("#cilory_filter").click(function() {
                $('.select_filter').val(null).trigger('change');
                $('#status_filter').val('all').trigger('change');
                $('.search').val('');
                $('.employee_daterangepicker').val('');



                dtable.draw();
            });
        });

        $('#export_excel_btn').on('click', function(e) {
            e.preventDefault();

            // Collect filter values
            let search = $('input[name="search"]').val();
            let status = $('#status_filter').val();
            let company = $('#company_id').val();
            let employee = $('#employee_id').val();
            let filter_date = $('input[name="employee_date"]').val().replace(' - ', ' to ');

            // Build query string matching backend filter names
            let queryParams = $.param({
                search: search,
                status: status,
                filter_company: company,
                filter_employee: employee,
                filter_date: filter_date
            });

            // Navigate to export route with filters applied
            let url = "{{ route($route . '.export.excel') }}" + "?" + queryParams;
            window.location.href = url;
        });
        
        $('#print_btn').on('click', function(e) {
            e.preventDefault();

            // Collect filter values
            let search = $('input[name="search"]').val();
            let status = $('#status_filter').val();
            let company = $('select[name="company_id"]').val();
            let employee = $('#employee_id').val();
            let employee_date = $('input[name="employee_date"]').val().replace(' - ', ' to ');

            // Build query string
            let queryParams = $.param({
                search: search,
                status: status,
                company: company,
                employee: employee,
                filter_date: employee_date, // matches backend param name
            });

            // Open print page in a new tab/window
            let url = "{{ route($route . '.print') }}" + "?" + queryParams;
            window.open(url, '_blank');
        });
    </script>
    @if (!$company_id)
        @include('utils.getCompany')
    @endif
    @include('utils.getEmployee')
    @include('software.inlcudes.script-delete-record')
    @include('software.inlcudes.script-restore-record')
    @include('software.inlcudes.script-update-status')
@endpush
