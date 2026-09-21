@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : 'Website Company Registration';
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : 'website-company-registration';
@endphp
@section('title', $page_title)

@section('page_leavel_style')
    <link rel="stylesheet" href="{{ asset('software/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('software/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
    <style>
        .login-details-wrapper {
            text-align: left;
            white-space: normal;
            min-width: 180px;
        }
        .login-details-wrapper .text-muted {
            font-size: 11px;
        }
        .login-details-wrapper strong {
            font-size: 12px;
            color: #333;
        }
        .copy-login-details {
            font-size: 11px;
            padding: 2px 8px;
        }
        .copy-login-details i {
            margin-right: 3px;
        }
    </style>
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
        <div class="col-md-12 mb-5" id="filter_section">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <label class="form-label">Filter by Name</label>
                            <input type="search" class="form-control search" name="search" placeholder="search..."
                                autofocus>
                        </div>

                        <div class="col-md-9 d-flex align-items-end mb-2">
                            <div class="flex-grow-1 me-2">
                                <label class="form-label">Filter by Plan</label>
                                <select id="plan_id" name="plan_id"
                                    class="form-control search_by_plan select2 select_filter" data-append="search_by_plan">
                                    <option value="">Filter by Plan</option>
                                </select>
                            </div>
                            <div class="flex-grow-1 me-2">
                                <label for="register_type_filter" class="form-label">Register Type</label>
                                <select id="register_type_filter" name="register_type" class="form-select select2 select_filter">
                                    <option value="all">All Types</option>
                                    <option value="manual">Manual</option>
                                    <option value="google">Google</option>
                                </select>
                            </div>
                            <div class="flex-grow-1 me-2">
                                <label for="status_filter" class="form-label">Filter by Status</label>
                                <select id="status_filter" name="status" class="form-select select2 select_filter">
                                    <option value="all">All</option>
                                    <option value="active" selected>Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="expired">Expired</option>
                                </select>
                            </div>
                            <div>
                                <button type="button" title="Clear Filter" id="cilory_filter"
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
                            <thead>
                                <tr>
                                    <th>Sr No.</th>
                                    <th>Company Details</th>
                                    <th>Register Type</th>
                                    <th>Contact Info</th>
                                    <th>Login Details</th>
                                    <th>Plan Info</th>
                                    <th>Created Date</th>
                                    <th>Status</th>
                                    <th class="no-sort text-start">Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View Detail Modal -->
    <div class="modal fade" id="companyRegistrationDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title"><i class="ti ti-building me-2"></i>Company Registration Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4" id="modalDetailContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page_leavel_script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="{{ asset('software/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
@endsection

@push('page_scripts')
    @include('utils.getPlans')

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
                order: [
                    [0, 'ASC']
                ],
                ajax: {
                    url: "{{ route($route . '.index') }}",
                    type: "GET",
                    data: function(data) {
                        data.search = $('input[name="search"]').val();
                        data.filter_plan = $('select[name="plan_id"] option:selected').val();
                        data.register_type = $('#register_type_filter').val();
                        data.status = $('#status_filter').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'company_details',
                        name: 'company_details'
                    },
                    {
                        data: 'register_type',
                        name: 'register_type',
                        className: 'text-center'
                    },
                    {
                        data: 'contact_info',
                        name: 'contact_info'
                    },
                    {
                        data: 'login_details',
                        name: 'login_details'
                    },
                    {
                        data: 'plan_info',
                        name: 'plan_info'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-start'
                    }
                ],
                language: {
                    searchPlaceholder: 'Search...'
                }
            });

            $(document).on('change', '.select_filter', function(event) {
                event.preventDefault();
                dtable?.draw();
            });

            $('input[name="search"]').keyup(function() {
                dtable?.draw();
            });

            $('#status_filter').on('change', function() {
                dtable?.draw();
            });

            $("#cilory_filter, #clear_filter").click(function() {
                $('.select_filter').val(null).trigger('change');
                $('#status_filter').val('all').trigger('change');
                $('.search').val('');
                dtable?.draw();
            });

            $(document).on('click', '.view-company-modal-btn', function() {
                var id = $(this).data('id');
                $('#modalDetailContent').html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
                $('#companyRegistrationDetailModal').modal('show');

                $.ajax({
                    url: "{{ url('software/website-company-registration') }}/" + id,
                    type: 'GET',
                    success: function(res) {
                        if (res.status && res.data) {
                            var regType = (d.register_type || 'manual').toLowerCase();
                            var regTypeBadge = regType === 'google' 
                                ? '<span class="badge bg-label-danger"><i class="fab fa-google me-1"></i> Google</span>' 
                                : '<span class="badge bg-label-info"><i class="fa fa-user-pen me-1"></i> Manual</span>';

                            var html = '<div class="row g-3">';
                            html += '<div class="col-md-6"><div class="border rounded p-3 bg-light-subtle"><small class="text-muted d-block mb-1">Company Name</small><strong class="fs-6">' + (d.company_name || '-') + '</strong></div></div>';
                            html += '<div class="col-md-6"><div class="border rounded p-3 bg-light-subtle"><small class="text-muted d-block mb-1">Register Type</small>' + regTypeBadge + '</div></div>';
                            html += '<div class="col-md-6"><div class="border rounded p-3 bg-light-subtle"><small class="text-muted d-block mb-1">GST Number</small><strong>' + (d.gst_no || 'N/A') + '</strong></div></div>';
                            html += '<div class="col-md-6"><div class="border rounded p-3 bg-light-subtle"><small class="text-muted d-block mb-1">Person Name</small><strong>' + (d.person_name || '-') + '</strong></div></div>';
                            html += '<div class="col-md-6"><div class="border rounded p-3 bg-light-subtle"><small class="text-muted d-block mb-1">WhatsApp Number</small><strong>' + (d.whatsapp_number || '-') + '</strong></div></div>';
                            html += '<div class="col-md-6"><div class="border rounded p-3 bg-light-subtle"><small class="text-muted d-block mb-1">Email Address</small><strong>' + (d.email || '-') + '</strong></div></div>';
                            html += '<div class="col-md-6"><div class="border rounded p-3 bg-light-subtle"><small class="text-muted d-block mb-1">Plan</small><span class="badge bg-label-primary">' + planTitle + '</span></div></div>';
                            html += '<div class="col-md-6"><div class="border rounded p-3 bg-light-subtle"><small class="text-muted d-block mb-1">Registered Date</small><strong>' + (d.created_at ? new Date(d.created_at).toLocaleString() : '-') + '</strong></div></div>';
                            html += '<div class="col-md-6"><div class="border rounded p-3 bg-light-subtle"><small class="text-muted d-block mb-1">Status</small><span class="badge bg-label-success text-capitalize">' + (d.status || 'active') + '</span></div></div>';
                            html += '</div>';
                            $('#modalDetailContent').html(html);
                        } else {
                            $('#modalDetailContent').html('<div class="text-danger p-3">Failed to load details.</div>');
                        }
                    },
                    error: function() {
                        $('#modalDetailContent').html('<div class="text-danger p-3">Error fetching company registration detail.</div>');
                    }
                });
            });
        });
    </script>

    @include('software.inlcudes.script-delete-record')
    @include('software.inlcudes.script-update-status')

    {{-- Copy Login Details Script --}}
    <script>
        $(document).on('click', '.copy-login-details', function() {
            var appKey = $(this).data('app-key');
            var username = $(this).data('username');
            var password = $(this).data('password');
            
            var loginDetails = "Login Details:\n" +
                "App Key: " + appKey + "\n" +
                "Username: " + username + "\n" +
                "Password: " + password;
            
            navigator.clipboard.writeText(loginDetails).then(function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Copied!',
                    text: 'Login details copied to clipboard. You can now share it!',
                    timer: 2000,
                    showConfirmButton: false
                });
            }).catch(function(err) {
                var textArea = document.createElement("textarea");
                textArea.value = loginDetails;
                textArea.style.position = "fixed";
                textArea.style.left = "-999999px";
                textArea.style.top = "-999999px";
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                
                try {
                    document.execCommand('copy');
                    Swal.fire({
                        icon: 'success',
                        title: 'Copied!',
                        text: 'Login details copied to clipboard. You can now share it!',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } catch (err) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to copy. Please try again.',
                    });
                }
                
                document.body.removeChild(textArea);
            });
        });
    </script>
@endpush
