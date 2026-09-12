@extends('software.layout.app')

@php
    $i = 0;
    $page_title = isset($modules['title']) ? $modules['title'] : null;
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $company_id = isset($modules['company_id']) ? $modules['company_id'] : null;
@endphp
@section('title', $page_title)

@section('page_leavel_style')
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
                            <label class="form-label">Filter by Team Person / Amount</label>
                            <input type="search" class="form-control search" name="search" placeholder="search..."
                                autofocus>
                        </div>
                        @if (!$company_id)
                            <div class="col-md-3 mb-2 col-sm-12">
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
                        <div class="col-md-3 mb-2 col-sm-12">
                            <div class="form-group">
                                <label class="form-label">Filter by Expense Category</label>
                                <select id="filter_expense_category" name="filter_expense_category"
                                    class="form-control select2 select_filter">
                                    <option value="">Filter by Expense Category</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 mb-2 col-sm-12">
                            <div class="form-group">
                                <label class="form-label">Filter by Expense SubCategory</label>
                                <select id="filter_expense_subcategory" name="filter_expense_subcategory"
                                    class="form-control select2 select_filter">
                                    <option value="">Filter by Expense SubCategory</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 mb-2 col-sm-12">
                            <div class="form-group">
                                <label class="form-label">Filter by Team Person</label>
                                <select id="filter_team_person" name="filter_team_person"
                                    class="form-control select2 select_filter">
                                    <option value="">Filter by Team Person</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 mb-2 col-sm-12">
                            <div class="form-group">
                                <label class="form-label">From Date</label>
                                <input type="date" id="from_date" name="from_date" class="form-control select_filter">
                            </div>
                        </div>
                        <div class="col-md-3 mb-2 col-sm-12">
                            <div class="form-group">
                                <label class="form-label">To Date</label>
                                <input type="date" id="to_date" name="to_date" class="form-control select_filter">
                            </div>
                        </div>
                        <div class="col-md-3 d-flex align-items-end mb-2">
                            <div class="flex-grow-1 me-2">
                                <label for="status_filter" class="form-label">Filter by Status</label>
                                <select id="status_filter" name="status" class="form-select select2">
                                    <option value="all">All</option>
                                    <option value="pending">Pending</option>
                                    <option value="pass">Approved</option>
                                    <option value="reject">Rejected</option>
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

<!-- File View Modal -->
<div class="modal fade" id="fileViewModal" tabindex="-1" aria-labelledby="fileViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">File Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body d-flex justify-content-center align-items-center" style="height: 80vh;">
                <iframe id="fileIframe" src="" frameborder="0" style="width: 100%; height: 100%; display: none;"></iframe>
                <img id="fileImage" src="" alt="Image Preview" style="max-width: 100%; max-height: 100%; display: none;" />
            </div>
        </div>
    </div>
</div>

<!-- Expense Status Modal -->
<div class="modal fade" id="expenseStatusModal" tabindex="-1" aria-labelledby="expenseStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="expenseStatusModalLabel">Approve Expense</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="expense_status_id">
                <input type="hidden" id="expense_status_url">
                <input type="hidden" id="expense_status_update_status">
                <input type="hidden" id="expense_req_amount">

                <div id="pass_amount_div">
                    <label class="form-label">Pass Amount (Requested: <span id="pass_amount_max_label"></span>)</label>
                    <input type="number" id="pass_amount" class="form-control" step="0.01">
                </div>

                <div id="reject_reason_div" style="display: none;">
                    <label class="form-label">Reason for Rejection</label>
                    <textarea id="reject_reason" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btn_save_expense_status">Save Changes</button>
            </div>
        </div>
    </div>
</div>

@section('page_leavel_script')
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="{{ asset('software/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
@endsection

@push('page_scripts')
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
                        data.filter_company = $('select[name="company_id"] option:selected').val();
                        data.filter_expense_category = $('#filter_expense_category').val();
                        data.filter_expense_subcategory = $('#filter_expense_subcategory').val();
                        data.filter_team_person = $('#filter_team_person').val();
                        data.from_date = $('#from_date').val();
                        data.to_date = $('#to_date').val();
                        data.status = $('#status_filter').val();
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
            dtable.draw();
        });
        $('input[name="search"]').keyup(function() {
            dtable.draw();
        });
        $('#status_filter').on('change', function() {
            dtable.draw();
        });

        $(document).ready(function() {
            $("#cilory_filter").click(function() {
                $('.select_filter').val(null).trigger('change');
                $('#status_filter').val('all').trigger('change');
                $('.search').val('');
                $('#from_date').val('');
                $('#to_date').val('');
                dtable.draw();
            });
        });
        $('#export_excel_btn').on('click', function(e) {
            e.preventDefault();
            let search = $('input[name="search"]').val();
            let status = $('#status_filter').val();
            let company = $('#company_id').val();
            let expense_category = $('#filter_expense_category').val();
            let expense_subcategory = $('#filter_expense_subcategory').val();
            let team_person = $('#filter_team_person').val();
            let from_date = $('#from_date').val();
            let to_date = $('#to_date').val();
            let queryParams = $.param({
                search: search,
                status: status,
                company: company,
                expense_category: expense_category,
                expense_subcategory: expense_subcategory,
                team_person: team_person,
                from_date: from_date,
                to_date: to_date
            });
            let url = "{{ route($route . '.export.excel') }}" + "?" + queryParams;
            window.location.href = url;
        });
        $('#print_btn').on('click', function(e) {
            e.preventDefault();
            let search = $('input[name="search"]').val();
            let status = $('#status_filter').val();
            let company = $('#company_id').val();
            let expense_category = $('#filter_expense_category').val();
            let expense_subcategory = $('#filter_expense_subcategory').val();
            let team_person = $('#filter_team_person').val();
            let from_date = $('#from_date').val();
            let to_date = $('#to_date').val();
            let queryParams = $.param({
                search: search,
                status: status,
                company: company,
                expense_category: expense_category,
                expense_subcategory: expense_subcategory,
                team_person: team_person,
                from_date: from_date,
                to_date: to_date
            });
            let url = "{{ route($route . '.print') }}" + "?" + queryParams;
            window.open(url, '_blank');
        });
        $(document).on('click', '.file-preview', function() {
            const url = $(this).data('url');
            if (!url) {
                toastr.error('No file URL found');
                return;
            }

            const isImage = /\.(jpg|jpeg|png|gif|bmp|webp)$/i.test(url);

            if (isImage) {
                $('#fileIframe').hide().attr('src', '');
                $('#fileImage').attr('src', url).show();
            } else {
                $('#fileImage').hide().attr('src', '');
                $('#fileIframe').attr('src', url).show();
            }

            $('#fileViewModal').modal('show');
        });

        $('#fileViewModal').on('hidden.bs.modal', function() {
            $('#fileIframe').attr('src', '').hide();
            $('#fileImage').attr('src', '').hide();
        });

        $(document).on('click', '.expense-status-update', function() {
            var url = $(this).data('url');
            var id = $(this).data('id');
            var status = $(this).data('update_status');
            var reqAmount = $(this).data('req_amount');

            $('#expense_status_id').val(id);
            $('#expense_status_url').val(url);
            $('#expense_status_update_status').val(status);
            $('#expense_req_amount').val(reqAmount);
            $('#pass_amount').val(status === 'pass' ? reqAmount : 0);
            $('#pass_amount_max_label').text(reqAmount);

            if (status === 'reject') {
                $('#expenseStatusModalLabel').text('Reject Expense');
                $('#pass_amount_div').hide();
                $('#reject_reason_div').show();
            } else {
                $('#expenseStatusModalLabel').text('Approve Expense');
                $('#pass_amount_div').show();
                $('#reject_reason_div').hide();
            }

            $('#expenseStatusModal').modal('show');
        });

        $(document).on('click', '#btn_save_expense_status', function() {
            var id = $('#expense_status_id').val();
            var url = $('#expense_status_url').val();
            var status = $('#expense_status_update_status').val();
            var reqAmount = parseFloat($('#expense_req_amount').val());
            var passAmount = parseFloat($('#pass_amount').val());
            var reason = $('#reject_reason').val();

            if (status === 'pass') {
                if (isNaN(passAmount) || passAmount < 0) {
                    toastr.error('Please enter a valid amount');
                    return;
                }
                if (passAmount > reqAmount) {
                    toastr.error('Pass amount cannot be greater than Request amount (' + reqAmount + ')');
                    return;
                }
            }

            $.ajax({
                type: "POST",
                url: url,
                data: {
                    _token: '{{ csrf_token() }}',
                    id: id,
                    update_status: status,
                    pass_amount: passAmount,
                    reason: reason
                },
                success: function(data) {
                    if (data?.status == true) {
                        toastr.success(data?.message);
                        $('#expenseStatusModal').modal('hide');
                        dtable.draw();
                    } else {
                        toastr.error(data?.message);
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Something went wrong');
                }
            });
        });
    </script>

    @if (!$company_id)
        @include('utils.getCompany')
    @endif
    @include('utils.getExpenseCategory')
    @include('utils.getExpenseSubCategory')
    @include('utils.getTeamPerson')

    @include('software.inlcudes.script-delete-record')
    @include('software.inlcudes.script-restore-record')
    @include('software.inlcudes.script-update-status')
@endpush
