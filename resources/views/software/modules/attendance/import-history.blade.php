@extends('software.layout.app')

@php
    $i = 0;
    $page_title = 'Attendance Import Files';
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = 'attendance-import-files';
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
                'show_add_btn' => false,
                'show_filter_btn' => true,
                'show_back_btn' => false,
                'show_export_btn' => false,
                'show_excal_btn' => false,
                'show_print_btn' => false,
            ])
            <div class="d-flex gap-2">
                <a class="btn btn-success waves-effect waves-light text-white mx-1" href="{{ route('import-attendance.index') }}">
                    <i class="menu-icon ti ti-file-import"></i> Import Attendance
                </a>
            </div>
        </div>
    </div>
    <div class="row my-3">
        <div class="col-md-12 mb-5" id="filter_section">
            <div class="card">
                <div class="card-body">
                    <div class="row">
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

                        <div class="col-md-3 col-sm-12">
                            <div class="form-group">
                                <label class="form-label">Filter by Status</label>
                                <select id="status" name="status"
                                    class="form-select select2 select_filter">
                                    <option value="">All Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="processing">Processing</option>
                                    <option value="completed">Completed</option>
                                    <option value="failed">Failed</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3 d-flex align-items-end mb-2">
                            <div class="flex-grow-1 me-2">
                                <label class="form-label">Filter by File Type</label>
                                <select id="file_type" name="file_type"
                                    class="form-select select2 select_filter">
                                    <option value="">All Types</option>
                                    <option value="excel">Excel</option>
                                    <option value="pdf">PDF</option>
                                </select>
                            </div>
                            <div>
                                <button type="button" title="Clear Filter" id="clear_filter"
                                    class="btn btn-outline-danger btn-icon mt-2">
                                    <i class="ti ti-x"></i>
                                </button>
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

    <!-- View Details Modal -->
    <div class="modal fade" id="viewImportDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Import File Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="importDetailsContent">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View Errors Modal -->
    <div class="modal fade" id="viewImportErrorsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Import Errors</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="importErrorsContent">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
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
@endsection

@push('page_scripts')
    <script>
        $(document).ready(function() {
            $("#clear_filter").click(function() {
                $('.select_filter').val('').trigger('change');
                if (typeof dtable !== 'undefined') {
                    dtable.draw();
                }
            });

            $('.table_filter, .select_filter').on('change', function() {
                if (typeof dtable !== 'undefined') {
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
                dom: '<"table-responsive"t><"datatable-footer d-flex justify-content-between align-items-center flex-wrap px-3 py-2"l i p>',
                order: [[0, 'ASC']],
                ajax: {
                    url: "{{ route('attendance-import-history') }}",
                    type: "GET",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: function(d) {
                        d.filter_company = $('#company_id').val();
                        d.filter_status = $('#status').val();
                        d.filter_file_type = $('#file_type').val();
                    },
                },
                columns: {!! isset($columns) ? json_encode($columns) : [] !!},
            });
        });

        $(document).on('change', '.select_filter', function(event) {
            event.preventDefault();
            if (typeof dtable !== 'undefined') {
                dtable.draw();
            }
        });

        // View import details
        $(document).on('click', '.view-import-details', function() {
            var importId = $(this).data('id');
            $('#viewImportDetailsModal').modal('show');
            $('#importDetailsContent').html('<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');

            $.ajax({
                url: "{{ route('import-attendance.status', ':id') }}".replace(':id', importId),
                method: 'GET',
                success: function(response) {
                    if (response.success && response.data) {
                        var data = response.data;
                        var html = '<div class="row">';
                        html += '<div class="col-md-6 mb-3"><strong>Status:</strong> <span class="badge bg-' + (data.status === 'completed' ? 'success' : (data.status === 'failed' ? 'danger' : 'warning')) + '">' + data.status.toUpperCase() + '</span></div>';
                        html += '<div class="col-md-6 mb-3"><strong>File Type:</strong> <span class="badge bg-info">' + (data.file_type ? data.file_type.toUpperCase() : 'N/A') + '</span></div>';
                        html += '<div class="col-md-6 mb-3"><strong>Total Rows:</strong> ' + (data.total_rows || 0) + '</div>';
                        html += '<div class="col-md-6 mb-3"><strong>Success:</strong> <span class="badge bg-success">' + (data.total_success || 0) + '</span></div>';
                        html += '<div class="col-md-6 mb-3"><strong>Failed:</strong> <span class="badge bg-danger">' + (data.total_failed || 0) + '</span></div>';
                        html += '<div class="col-md-6 mb-3"><strong>Duplicates:</strong> <span class="badge bg-warning">' + (data.total_duplicates || 0) + '</span></div>';
                        if (data.started_at) {
                            html += '<div class="col-md-6 mb-3"><strong>Started At:</strong> ' + new Date(data.started_at).toLocaleString() + '</div>';
                        }
                        if (data.completed_at) {
                            html += '<div class="col-md-6 mb-3"><strong>Completed At:</strong> ' + new Date(data.completed_at).toLocaleString() + '</div>';
                        }
                        html += '</div>';
                        $('#importDetailsContent').html(html);
                    } else {
                        $('#importDetailsContent').html('<div class="alert alert-danger">Failed to load details</div>');
                    }
                },
                error: function() {
                    $('#importDetailsContent').html('<div class="alert alert-danger">Error loading details</div>');
                }
            });
        });

        // View import errors
        $(document).on('click', '.view-import-errors', function() {
            var importId = $(this).data('id');
            $('#viewImportErrorsModal').modal('show');
            $('#importErrorsContent').html('<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');

            $.ajax({
                url: "{{ route('import-attendance.status', ':id') }}".replace(':id', importId),
                method: 'GET',
                success: function(response) {
                    if (response.success && response.data && response.data.errors) {
                        var errors = response.data.errors;
                        var html = '<div class="table-responsive"><table class="table table-sm table-bordered">';
                        html += '<thead><tr><th width="100">Row #</th><th>Error Message</th></tr></thead><tbody>';
                        
                        if (Array.isArray(errors)) {
                            errors.forEach(function(error, index) {
                                html += '<tr><td><strong>' + (index + 1) + '</strong></td><td>' + error + '</td></tr>';
                            });
                        } else if (typeof errors === 'object') {
                            Object.keys(errors).forEach(function(key) {
                                html += '<tr><td><strong>' + key + '</strong></td><td>' + errors[key] + '</td></tr>';
                            });
                        } else {
                            html += '<tr><td colspan="2">' + errors + '</td></tr>';
                        }
                        
                        html += '</tbody></table></div>';
                        $('#importErrorsContent').html(html);
                    } else {
                        $('#importErrorsContent').html('<div class="alert alert-info">No errors found</div>');
                    }
                },
                error: function() {
                    $('#importErrorsContent').html('<div class="alert alert-danger">Error loading errors</div>');
                }
            });
        });
    </script>

    @if (!$company_id)
        @include('utils.getCompany')
    @endif
@endpush

