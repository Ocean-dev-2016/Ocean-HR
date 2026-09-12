@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : 'Company Registration';
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : 'company-registration';
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
            ])
        </div>
    </div>
    <div class="row my-3">
        <div class="col-md-12 mb-3" id="filter_section">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Search</label>
                            <input type="search" class="form-control search" name="search" placeholder="Search by name, email, GST..." autofocus>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label for="status_filter" class="form-label">Filter by Status</label>
                            <select id="status_filter" name="status" class="form-select select2">
                                <option value="all">All</option>
                                <option value="pending" selected>Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end mb-2">
                            <button type="button" title="Clear Filter" id="clear_filter" class="btn btn-outline-danger btn-icon">
                                <i class="ti ti-x"></i> Clear
                            </button>
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
                            <thead>
                                <tr>
                                    <th>Sr No.</th>
                                    <th>ID</th>
                                    <th>GST No</th>
                                    <th>Company Name</th>
                                    <th>Person Name</th>
                                    <th>WhatsApp</th>
                                    <th>Email</th>
                                    <th>Plan Name</th>
                                    <th>Status</th>
                                    <th>Registered Date</th>
                                    <th class="no-sort text-start">Actions</th>
                                </tr>
                            </thead>
                        </table>
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
    <script type="text/javascript">
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var dtable = null;
        $(document).ready(function() {
            dtable = $('#yajra-datatables').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                dom: '<"table-responsive"t><"datatable-footer d-flex justify-content-between align-items-center flex-wrap px-3 py-2"l i p>',
                order: [[0, 'desc']],
                ajax: {
                    url: "{{ route('software.company-registration.index') }}",
                    type: "GET",
                    data: function(data) {
                        data.search = $('input[name="search"]').val();
                        data.status = $('#status_filter').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'id', name: 'id' },
                    { data: 'gst_no', name: 'gst_no', defaultContent: '-' },
                    { data: 'company_name', name: 'company_name' },
                    { data: 'person_name', name: 'person_name' },
                    { data: 'whatsapp_number', name: 'whatsapp_number' },
                    { data: 'email', name: 'email' },
                    { data: 'plan_name', name: 'plan.title', defaultContent: '-' },
                    { data: 'status', name: 'status', className: 'text-center' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-start' }
                ],
                language: {
                    searchPlaceholder: 'Search...'
                }
            });

            $('input[name="search"]').keyup(function() {
                dtable.draw();
            });

            $('#status_filter').on('change', function() {
                dtable.draw();
            });

            $('#clear_filter').click(function() {
                $('#status_filter').val('all').trigger('change');
                $('input[name="search"]').val('');
                dtable.draw();
            });

            $(document).on('click', '.btn-approve-request', function(e) {
                e.preventDefault();
                var url = $(this).data('url');

                if (confirm('Are you sure you want to APPROVE this company registration? This will create an active Company account.')) {
                    $.ajax({
                        url: url,
                        type: 'POST',
                        dataType: 'json',
                        success: function(response) {
                            if (response.status) {
                                alert(response.message);
                                dtable.draw();
                            } else {
                                alert(response.message || 'Error occurred');
                            }
                        },
                        error: function(xhr) {
                            var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Failed to approve registration.';
                            alert(msg);
                        }
                    });
                }
            });

            $(document).on('click', '.btn-reject-request', function(e) {
                e.preventDefault();
                var url = $(this).data('url');

                if (confirm('Are you sure you want to REJECT this company registration?')) {
                    $.ajax({
                        url: url,
                        type: 'POST',
                        dataType: 'json',
                        success: function(response) {
                            if (response.status) {
                                alert(response.message);
                                dtable.draw();
                            } else {
                                alert(response.message || 'Error occurred');
                            }
                        },
                        error: function(xhr) {
                            var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Failed to reject registration.';
                            alert(msg);
                        }
                    });
                }
            });

            $(document).on('click', '.btn-delete-request', function(e) {
                e.preventDefault();
                var url = $(this).data('url');

                if (confirm('Are you sure you want to DELETE this registration record?')) {
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        dataType: 'json',
                        success: function(response) {
                            if (response.status) {
                                alert(response.message);
                                dtable.draw();
                            } else {
                                alert(response.message || 'Error occurred');
                            }
                        },
                        error: function(xhr) {
                            var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Failed to delete registration.';
                            alert(msg);
                        }
                    });
                }
            });
        });
    </script>
@endpush
