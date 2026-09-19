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

        /* View Toggle Switcher Styling */
        .view-switcher-group .view-toggle-btn {
            width: 38px;
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-color: #dbdade;
            color: #8592a3;
            background: #fff;
            transition: all 0.2s ease-in-out;
            font-size: 1.15rem;
            border-radius: 6px;
        }
        .view-switcher-group .view-toggle-btn:first-child {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }
        .view-switcher-group .view-toggle-btn:last-child {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }
        .view-switcher-group .view-toggle-btn.active {
            background-color: #7367f0 !important;
            border-color: #7367f0 !important;
            color: #fff !important;
            box-shadow: 0 2px 6px rgba(115, 103, 240, 0.4);
        }
        .view-switcher-group .view-toggle-btn:hover:not(.active) {
            background-color: #f8f7fa;
            color: #7367f0;
            border-color: #7367f0;
        }

        /* Employee Grid Card Styling */
        .employee-card {
            border-radius: 12px;
            transition: all 0.25s cubic-bezier(0.165, 0.84, 0.44, 1);
            background: #fff;
            border: 1px solid rgba(75, 70, 92, 0.08) !important;
        }
        .employee-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(75, 70, 92, 0.08) !important;
            border-color: rgba(115, 103, 240, 0.25) !important;
        }
        .avatar-initials-circle {
            transition: transform 0.25s ease;
        }
        .employee-card:hover .avatar-initials-circle {
            transform: scale(1.06);
        }
        .bg-lighter {
            background-color: #f8f7fa !important;
        }
        .font-size-xs {
            font-size: 0.72rem !important;
        }
        .font-size-sm {
            font-size: 0.82rem !important;
        }
        .text-hover-primary {
            transition: color 0.15s ease-in-out;
        }
        .text-hover-primary:hover {
            color: #7367f0 !important;
        }
        .cursor-pointer {
            cursor: pointer;
        }
        .employee-card-details {
            background-color: #f8f9fa !important;
            border: 1px solid rgba(75, 70, 92, 0.08);
            border-radius: 8px;
            padding: 10px 12px;
        }
        .employee-detail-item {
            font-size: 0.8125rem;
            line-height: 1.4;
        }
        .employee-detail-icon {
            width: 22px;
            height: 22px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            flex-shrink: 0;
            margin-right: 12px;
        }
        .gap-1\.5 {
            gap: 0.375rem !important;
        }
        .grid-loading-overlay {
            min-height: 220px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }
    </style>
@endsection

@section('content')
    <div class="px-1">
        <div class="d-lg-flex justify-content-lg-between flex-column flex-lg-row">
            @include('software.inlcudes.breadcrumb', [
                'breadcrumbArray' => [['title' => $page_title, 'url' => '']],
                'route' => $route,
                'show_add_btn' => isset($modules['add_permission']) ? $modules['add_permission'] : false,
                'show_grid_toggle' => true,
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
        <div class="col-md-12 mb-4" id="filter_section" style="display: none;">
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

                        <div class="col-md-3 col-sm-12 branchDiv" style="display:none;">
                            <div class="form-group">
                                <label class="form-label">Filter by Branch</label>
                                <select id="branch_id" name="branch_id"
                                    class="form-control search_by_branch select2 select_filter"
                                    data-append="search_by_branch">
                                    <option value="">Filter by Branch</option>
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

        {{-- Grid View Container --}}
        <div class="col-md-12 mb-4" id="grid_view_container">
            {{-- Grid Loader --}}
            <div id="grid_loader" class="grid-loading-overlay card border-0 shadow-sm p-5 text-center my-3" style="display: none;">
                <div class="spinner-border text-primary mb-2" role="status" style="width: 2.5rem; height: 2.5rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <span class="text-muted fw-medium small">Loading employee cards...</span>
            </div>

            {{-- Grid Cards Content --}}
            <div id="grid_cards_content">
                @if (isset($initialEmployees))
                    @include('software.modules.employee.partials.grid-view', ['employees' => $initialEmployees, 'modules' => $modules])
                @endif
            </div>

            {{-- Grid Pagination Content --}}
            <div id="grid_pagination_content">
                @if (isset($initialEmployees))
                    @include('software.modules.employee.partials.grid-pagination', ['employees' => $initialEmployees])
                @endif
            </div>
        </div>

        {{-- Table (List) View Container --}}
        <div class="col-md-12 mb-4" id="table_view_container" style="display: none;">
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
    <!-- Data tables -->
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
        var currentGridPage = 1;
        var gridPerPage = 12;
        var currentViewMode = localStorage.getItem('employee_view_mode') || 'grid';

        // Load Grid Cards Function
        function loadEmployeeGrid(page) {
            page = page || 1;
            currentGridPage = page;
            gridPerPage = $('#grid_per_page').val() || gridPerPage || 12;

            $('#grid_loader').show();
            $('#grid_cards_content').hide();
            $('#grid_pagination_content').hide();

            $.ajax({
                url: "{{ route($route . '.index') }}",
                type: "GET",
                data: {
                    view_type: 'grid',
                    page: page,
                    per_page: gridPerPage,
                    search: $('input[name="search"]').val(),
                    filter_company: $('select[name="company_id"] option:selected').val(),
                    filter_branch: $('select[name="branch_id"] option:selected').val(),
                    filter_parent: $('select[name="parent_id"] option:selected').val(),
                    status: $('#status_filter').val()
                },
                success: function(response) {
                    if (response && response.status) {
                        $('#grid_cards_content').html(response.html).show();
                        $('#grid_pagination_content').html(response.pagination).show();
                        $('#grid_total_count').text(response.total || 0);
                    }
                },
                error: function(xhr) {
                    console.error("Failed to load employee grid", xhr);
                    $('#grid_cards_content').html('<div class="alert alert-danger my-3">Failed to load employees. Please try again.</div>').show();
                },
                complete: function() {
                    $('#grid_loader').hide();
                }
            });
        }

        // View Mode Switcher Function
        function setViewMode(mode, triggerFetch) {
            currentViewMode = mode;
            localStorage.setItem('employee_view_mode', mode);

            $('.view-toggle-btn').removeClass('active');
            if (mode === 'grid') {
                $('#btn_grid_view').addClass('active');
                $('#table_view_container').hide();
                $('#grid_view_container').show();
                if (triggerFetch) {
                    loadEmployeeGrid(1);
                }
            } else {
                $('#btn_list_view').addClass('active');
                $('#grid_view_container').hide();
                $('#table_view_container').show();
                if (dtable) {
                    dtable.draw();
                }
            }
        }

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

            // Initialize DataTable
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
                        request.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
                    },
                    type: "GET",
                    data: function(data) {
                        data.search = $('input[name="search"]').val();
                        data.filter_company = $('select[name="company_id"] option:selected').val();
                        data.filter_branch = $('select[name="branch_id"] option:selected').val();
                        data.filter_parent = $('select[name="parent_id"] option:selected').val();
                        data.status = $('#status_filter').val();
                    },
                },
                columns: {!! isset($columns) ? json_encode($columns) : [] !!},
                language: {
                    searchPlaceholder: 'Search...',
                }
            });

            // Initial view mode setup
            if (currentViewMode === 'list') {
                setViewMode('list');
            } else {
                $('#btn_grid_view').addClass('active');
                $('#btn_list_view').removeClass('active');
                $('#table_view_container').hide();
                $('#grid_view_container').show();
            }
        });

        // Toggle buttons click
        $(document).on('click', '.view-toggle-btn', function(e) {
            e.preventDefault();
            var mode = $(this).data('view');
            setViewMode(mode, true);
        });

        // Grid Pagination click
        $(document).on('click', '.grid-page-link', function(e) {
            e.preventDefault();
            var page = $(this).data('page');
            if (page) {
                loadEmployeeGrid(page);
                $('html, body').animate({
                    scrollTop: 0
                }, 150);
            }
        });

        // Per page dropdown change
        $(document).on('change', '#grid_per_page', function() {
            loadEmployeeGrid(1);
        });

        // Filters triggers
        var searchDebounce = null;
        $('input[name="search"]').on('input keyup', function() {
            clearTimeout(searchDebounce);
            searchDebounce = setTimeout(function() {
                if (currentViewMode === 'grid') {
                    loadEmployeeGrid(1);
                } else {
                    dtable?.draw();
                }
            }, 300);
        });

        $(document).on('change', '.select_filter, #status_filter', function(event) {
            event.preventDefault();
            if (currentViewMode === 'grid') {
                loadEmployeeGrid(1);
            } else {
                dtable?.draw();
            }
        });

        // Clear filter
        $("#cilory_filter, #grid_clear_filter").click(function() {
            $('.select_filter').val(null).trigger('change');
            $('#status_filter').val('all').trigger('change');
            $('.search').val('');
            if (currentViewMode === 'grid') {
                loadEmployeeGrid(1);
            } else {
                dtable?.draw();
            }
        });

        // Export Excel
        $('#export_excel_btn').on('click', function(e) {
            e.preventDefault();

            let search = $('input[name="search"]').val();
            let status = $('#status_filter').val();
            let company = $('#company_id').val();
            let branch = $('#branch_id').val();
            let filter_parent = $('#parent_id').val();
            let queryParams = $.param({
                search: search,
                status: status,
                company: company,
                branch: branch,
                filter_parent: filter_parent
            });

            let url = "{{ route($route . '.export.excel') }}" + "?" + queryParams;
            window.location.href = url;
        });

        // Print
        $('#print_btn').on('click', function(e) {
            e.preventDefault();

            let search = $('input[name="search"]').val();
            let status = $('#status_filter').val();
            let filter_company = $('#company_id').val();
            let filter_branch = $('#branch_id').val();
            let filter_parent = $('#parent_id').val();

            let queryParams = $.param({
                search: search,
                status: status,
                filter_company: filter_company,
                filter_branch: filter_branch,
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
                url: "{{ route('employees.update-resign-date') }}",
                type: 'POST',
                data: {
                    id: id,
                    resign_date: resign_date
                },
                success: function(response) {
                    $('#resignDateModal').modal('hide');
                    toastr.success(response.message || 'Resign date updated successfully.');
                    if (currentViewMode === 'grid') {
                        loadEmployeeGrid(currentGridPage);
                    } else {
                        dtable?.draw(false);
                    }
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

        // Global listeners
        $(document).ajaxSuccess(function(event, xhr, settings) {
            if (settings.url && (
                settings.url.indexOf('employees-update-status') !== -1 ||
                settings.url.indexOf('employees/restore') !== -1 ||
                (settings.type === 'POST' && settings.data && settings.data.indexOf('_method=DELETE') !== -1)
            )) {
                if (currentViewMode === 'grid') {
                    loadEmployeeGrid(currentGridPage);
                }
            }
        });
    </script>

    @if (!$company_id)
        @include('utils.getCompany')
    @endif
    @include('utils.getEmployee')

    @include('software.inlcudes.script-delete-record')
    @include('software.inlcudes.script-restore-record')
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
