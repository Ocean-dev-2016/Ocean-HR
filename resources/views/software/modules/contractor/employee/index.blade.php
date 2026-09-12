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
    @if($company_id)
        <meta name="company_id" value="{{ $company_id }}">
        <meta name="branch_type" value="multiple">
    @endif
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
                            <label class="form-label">Filter by Employee</label>
                            <input type="search" class="form-control search" name="search" placeholder="search..."
                                autofocus>
                        </div>
                        @if (!$company_id)
                            <div class="col-md-3 mb-2 col-sm-12">
                                <div class="form-group">
                                    <label class="form-label">Filter by Company</label>
                                    <select id="company_id" name="company_id"
                                        class="form-control search_by_company select2 select_filter"
                                        data-append="search_by_company" showBranch="branchDiv">
                                        <option value="">Filter by Company</option>
                                    </select>
                                </div>
                            </div>
                        @endif

                        <div class="col-md-3 col-sm-12 branchDiv" @if(!$company_id) style="display:none;" @endif>
                            <div class="form-group">
                                <label class="form-label">Filter by Branch</label>
                                <select id="branch_id" name="branch_id"
                                    class="form-control search_by_branch select2 select_filter"
                                    data-append="search_by_branch">
                                    <option value="">Filter by Branch</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-12 departmentDiv" @if(!$company_id) style="display:none;" @endif>
                            <div class="form-group">
                                <label class="form-label">Filter by Department</label>
                                <select id="department_id" name="department_id"
                                    class="form-control search_by_department select2 select_filter"
                                    data-append="search_by_department">
                                    <option value="">Filter by Department</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-12">
                            <div class="form-group">
                                <label class="form-label">Filter by Parent</label>
                                <select id="parent_id" name="parent_id"
                                    class="form-control search_by_employee select2 select_filter"
                                    data-append="search_by_employee">
                                    <option value="">Filter by Employee</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 d-flex align-items-end mb-2">
                            <div class="flex-grow-1 me-2">
                                <label for="status_filter" class="form-label">Filter by Status</label>
                                <select id="status_filter" name="status" class="form-select select2">
                                    <option value="all">All</option>
                                    <option value="active" selected>Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="resigned">Resigned</option>
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

    <!-- Resign Date Modal -->
    <div class="modal fade" id="resignDateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Resign Date</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="resignDateForm">
                        <input type="hidden" name="employee_id" id="resign_employee_id">
                        <div class="mb-3">
                            <label for="resign_date_input" class="form-label">Resign Date</label>
                            <input type="date" class="form-control" id="resign_date_input" name="resign_date" required>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary" id="saveResignDateBtn">Save</button>
                        </div>
                    </form>
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
            // Parse URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('status')) {
                const statusVal = urlParams.get('status');
                $('#status_filter').val(statusVal).trigger('change');
            }
            if (urlParams.has('filter_company')) {
                const urlCompanyId = urlParams.get('filter_company');
                $('.search_by_company').attr('data-selectedcompanyid', urlCompanyId);
                $('.search_by_company').val(urlCompanyId);
                
                var checkCompanyOption = setInterval(function() {
                    if ($('.search_by_company option[value="' + urlCompanyId + '"]').length) {
                        $('.search_by_company').val(urlCompanyId).trigger('change');
                        clearInterval(checkCompanyOption);
                    }
                }, 100);
            }

            dtable = $('#yajra-datatables').DataTable({
                processing: true,
                serverSide: true,
                dom: '<"table-responsive"t><"datatable-footer d-flex justify-content-between align-items-center flex-wrap px-3 py-2"l i p>',
                order: [
                    [0, 'ASC']
                ],
                ajax: {
                    "url": "{{ route($route . '.index') }}",
                    type: "GET",
                    data: function(data) {
                        data.search = $('input[name="search"]').val();
                        data.filter_company = $('select[name="company_id"] option:selected').val();
                        data.filter_branch = $('select[name="branch_id"] option:selected').val();
                        data.filter_department = $('select[name="department_id"] option:selected').val();
                        data.filter_parent = $('select[name="parent_id"] option:selected').val();
                        data.status = $('#status_filter').val();
                    },
                },
                columns: {!! isset($columns) ? json_encode($columns) : [] !!},
                language: {
                    searchPlaceholder: 'Search...',
                }
            });
        });

        $(document).on('change', '.search_by_company', function() {
            if ($(this).val()) {
                $(".departmentDiv").show();
            } else {
                $(".departmentDiv").hide();
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

        $(document).ready(function() {
            $("#cilory_filter").click(function() {
                $('.select_filter').val(null).trigger('change');
                $('#status_filter').val('all').trigger('change');
                $('.search').val('');
                dtable?.draw();
            });
        });
        $('#export_excel_btn').on('click', function(e) {
            e.preventDefault();
            let search = $('input[name="search"]').val();
            let status = $('#status_filter').val();
            let company = $('#company_id').val();
            let branch = $('#branch_id').val();
            let department = $('#department_id').val();
            let filter_parent = $('#parent_id').val();
            let queryParams = $.param({
                search: search,
                status: status,
                company: company,
                branch: branch,
                department: department,
                filter_parent: filter_parent
            });
            let url = "{{ route($route . '.export.excel') }}" + "?" + queryParams;
            window.location.href = url;
        });
        $('#print_btn').on('click', function(e) {
            e.preventDefault();
            let search = $('input[name="search"]').val();
            let status = $('#status_filter').val();
            let filter_company = $('#company_id').val();
            let filter_branch = $('#branch_id').val();
            let filter_department = $('#department_id').val();
            let filter_parent = $('#parent_id').val();
            let queryParams = $.param({
                search: search,
                status: status,
                filter_company: filter_company,
                filter_branch: filter_branch,
                filter_department: filter_department,
                filter_parent: filter_parent
            });
            let url = "{{ route($route . '.print') }}" + "?" + queryParams;
            window.location.href = url;
        });

        // Resign Date Logic
        $(document).on('click', '.resign-date-btn', function() {
            let id = $(this).data('id');
            let resign_date = $(this).data('resign-date');
            $('#resign_employee_id').val(id);
            $('#resign_date_input').val(resign_date);
        });

        $('#resignDateForm').on('submit', function(e) {
            e.preventDefault();
            let id = $('#resign_employee_id').val();
            let resign_date = $('#resign_date_input').val();
            let btn = $('#saveResignDateBtn');

            if (!resign_date) {
                toastr.error('Please select a resign date.');
                return false;
            }

            let originalText = btn.html();
            btn.html('<i class="fa fa-spinner fa-spin"></i> Saving...').prop('disabled', true);

            $.ajax({
                url: "{{ route($route . '.update-resign-date') }}",
                type: 'POST',
                data: {
                    id: id,
                    resign_date: resign_date
                },
                success: function(response) {
                    $('#resignDateModal').modal('hide');
                    toastr.success(response.message || 'Resign date updated successfully.');
                    dtable?.draw(false);
                },
                error: function(xhr) {
                    let errorMessage = 'Something went wrong.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    toastr.error(errorMessage);
                },
                complete: function() {
                    btn.html(originalText).prop('disabled', false);
                }
            });
        });
    </script>

    @if (!$company_id)
        @include('utils.getCompany')
    @endif
    @include('utils.getEmployee')
    @include('utils.getBranch')
    @include('utils.getDepartment')

    @include('software.inlcudes.script-delete-record')
    @include('software.inlcudes.script-restore-record')
    @include('software.inlcudes.script-update-status')

    <script>
        $(document).on('click', '.copy-login-details', function() {
            var appKey = $(this).data('app-key');
            var username = $(this).data('username');
            var password = $(this).data('password');
            var loginDetails = "Login Details:\nApp Key: " + appKey + "\nUsername: " + username + "\nPassword: " + password;
            navigator.clipboard.writeText(loginDetails).then(function() {
                Swal.fire({ icon: 'success', title: 'Copied!', text: 'Login details copied to clipboard.', timer: 2000, showConfirmButton: false });
            }).catch(function(err) {
                var textArea = document.createElement("textarea");
                textArea.value = loginDetails;
                document.body.appendChild(textArea);
                textArea.select();
                try {
                    document.execCommand('copy');
                    Swal.fire({ icon: 'success', title: 'Copied!', timer: 2000, showConfirmButton: false });
                } catch (err) {}
                document.body.removeChild(textArea);
            });
        });
    </script>
@endpush
