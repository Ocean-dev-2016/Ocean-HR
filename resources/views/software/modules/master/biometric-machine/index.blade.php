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
            ])
        </div>
    </div>
    <div class="row my-3">
        <div class="col-md-12 mb-5" id="filter_section">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="form-label">Search</label>
                            <input type="search" class="form-control search" name="search" placeholder="Search by machine name, IP, or port..."
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
                        <div class="col-md-2 d-flex align-items-end mb-2">
                            <div class="flex-grow-1 me-2">
                                <label for="status_filter" class="form-label">Filter by Status</label>
                                <select id="status_filter" name="status" class="form-select select2">
                                    <option value="all">All</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end mb-2">
                            <div class="flex-grow-1 me-2">
                                <label for="provider_filter" class="form-label">Filter by Provider</label>
                                <select id="provider_filter" name="provider_type" class="form-select select2">
                                    <option value="all">All</option>
                                    <option value="minop">Minop</option>
                                    <option value="etimeoffice">eTimeOffice</option>
                                    <option value="mintra">Mintra</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-1 d-flex align-items-end mb-2">
                            <button type="button" title="Clear Filter" id="clear_filter"
                                class="btn btn-outline-danger btn-icon mt-2"><i class="ti ti-x"></i></button>
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
                                                {{ ucfirst($item?->td_label ?? $item?->name) ?? '' }}
                                            </th>
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
                
                // Enable column ordering by clicking on headers
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
                        data.status = $('#status_filter').val();
                        data.provider_type = $('#provider_filter').val();
                    },
                },

                columns: {!! isset($columns) ? json_encode($columns) : [] !!},
                
                language: {
                    searchPlaceholder: 'Search...',
                },
                
                // Enable column ordering
                orderable: true,
                ordering: true
            });
        });

        // Filter by company change
        $(document).on('change', '.select_filter', function(event) {
            event.preventDefault();
            dtable.draw();
        });
        
        // Search input keyup
        $('input[name="search"]').on('keyup', function() {
            dtable.draw();
        });
        
        // Status filter change
        $('#status_filter').on('change', function() {
            dtable.draw();
        });

        // Provider filter change
        $('#provider_filter').on('change', function() {
            dtable.draw();
        });

        // Clear all filters
        $(document).ready(function() {
            $("#clear_filter").click(function() {
                $('.select_filter').val(null).trigger('change');
                $('#status_filter').val('all').trigger('change');
                $('#provider_filter').val('all').trigger('change');
                $('.search').val('');
                dtable.draw();
            });
        });

        // Sync button click handler
        $(document).on('click', '.sync-attendance-btn', function(e) {
            e.preventDefault();
            const machineId = $(this).data('machine-id');
            const btn = $(this);
            const originalHtml = btn.html();
            
            // Disable button and show loading
            btn.prop('disabled', true).html('<i class="ti ti-loader-2 spin"></i> Syncing...');
            
            $.ajax({
                url: '{{ route("biometric-machines.sync-attendance", ":id") }}'.replace(':id', machineId),
                method: 'POST',
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message || 'Sync completed successfully');
                        // Refresh table
                        dtable.draw();
                    } else {
                        toastr.error(response.message || 'Sync failed');
                    }
                },
                error: function(xhr) {
                    const errorMsg = xhr.responseJSON?.message || 'Sync failed. Please try again.';
                    toastr.error(errorMsg);
                },
                complete: function() {
                    btn.prop('disabled', false).html(originalHtml);
                }
            });
        });

        // Test connection button click handler
        $(document).on('click', '.test-connection-btn', function(e) {
            e.preventDefault();
            const machineId = $(this).data('machine-id');
            const btn = $(this);
            const originalHtml = btn.html();
            
            btn.prop('disabled', true).html('<i class="ti ti-loader-2 spin"></i>');
            
            $.ajax({
                url: '{{ route("biometric-machines.test-connection", ":id") }}'.replace(':id', machineId),
                method: 'POST',
                success: function(response) {
                    if (response.status) {
                        toastr.success(response.message || 'Connection successful');
                    } else {
                        toastr.error(response.message || 'Connection failed');
                    }
                },
                error: function(xhr) {
                    const errorMsg = xhr.responseJSON?.message || 'Connection test failed';
                    toastr.error(errorMsg);
                },
                complete: function() {
                    btn.prop('disabled', false).html(originalHtml);
                }
            });
        });

        // Toggle active button click handler
        $(document).on('click', '.toggle-active-btn', function(e) {
            e.preventDefault();
            const machineId = $(this).data('machine-id');
            const isActive = $(this).data('is-active');
            const btn = $(this);
            
            if (confirm('Are you sure you want to ' + (isActive ? 'deactivate' : 'activate') + ' this machine?')) {
                $.ajax({
                    url: '{{ route("biometric-machines.toggle-active", ":id") }}'.replace(':id', machineId),
                    method: 'POST',
                    success: function(response) {
                        if (response.status) {
                            toastr.success(response.message || 'Status updated successfully');
                            dtable.draw();
                        } else {
                            toastr.error(response.message || 'Failed to update status');
                        }
                    },
                    error: function(xhr) {
                        const errorMsg = xhr.responseJSON?.message || 'Failed to update status';
                        toastr.error(errorMsg);
                    }
                });
            }
        });
        
        // Include delete, restore, and status update scripts
    </script>

    @if (!$company_id)
        @include('utils.getCompany')
    @endif

    @include('software.inlcudes.script-delete-record')
    @include('software.inlcudes.script-restore-record')
    @include('software.inlcudes.script-update-status')
@endpush

