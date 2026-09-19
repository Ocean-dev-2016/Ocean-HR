@extends('software.layout.app')

@php
    $page_title = $modules['title'] ?? 'Employee Onboarding';
    $folder_path = $modules['folder_path'] ?? 'software.modules.onboarding';
    $route = $modules['route'] ?? 'onboarding';
@endphp

@section('title', $page_title)

@section('page_leavel_style')
    <link rel="stylesheet" href="{{ asset('software/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('software/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('software/vendor/libs/select2/select2.css') }}" />
    <style>
        .kpi-card {
            border-radius: 12px;
            transition: all 0.25s ease;
            border: 1px solid rgba(75, 70, 92, 0.08);
        }
        .kpi-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 16px rgba(75, 70, 92, 0.08);
        }
        .avatar-icon-box {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }
        .onboarding-stepper-badge {
            font-size: 0.75rem;
            padding: 4px 8px;
            border-radius: 6px;
        }
    </style>
@endsection

@section('content')
<div class="px-1">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        @include('software.inlcudes.breadcrumb', [
            'breadcrumbArray' => [['title' => $page_title, 'url' => '']],
            'route' => $route,
            'show_add_btn' => isset($modules['add_permission']) ? $modules['add_permission'] : true,
            'show_filter_btn' => false,
            'show_back_btn' => false,
            'show_export_btn' => false,
            'show_excal_btn' => false,
            'show_print_btn' => false,
        ])
    </div>

    <!-- Header Description -->
    <div class="mb-3">
        <p class="text-muted mb-0">Manage complete new hire onboarding, documents, company orientation, induction training & asset provisioning.</p>
    </div>

    <!-- KPI Summary Cards -->
    <div class="row g-3 mb-3">
        <div class="col-sm-6 col-xl-3">
            <div class="card kpi-card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fw-semibold d-block mb-1">Total Onboardings</span>
                            <h3 class="card-title mb-0 fw-bold text-heading">{{ $stats['total'] }}</h3>
                        </div>
                        <div class="avatar-icon-box bg-label-primary">
                            <i class="ti ti-users"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card kpi-card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fw-semibold d-block mb-1">In Progress</span>
                            <h3 class="card-title mb-0 fw-bold text-primary">{{ $stats['in_progress'] }}</h3>
                        </div>
                        <div class="avatar-icon-box bg-label-warning">
                            <i class="ti ti-loader"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card kpi-card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fw-semibold d-block mb-1">Pending Documents</span>
                            <h3 class="card-title mb-0 fw-bold text-warning">{{ $stats['pending_docs'] }}</h3>
                        </div>
                        <div class="avatar-icon-box bg-label-danger">
                            <i class="ti ti-file-alert"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card kpi-card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fw-semibold d-block mb-1">Completed</span>
                            <h3 class="card-title mb-0 fw-bold text-success">{{ $stats['completed'] }}</h3>
                        </div>
                        <div class="avatar-icon-box bg-label-success">
                            <i class="ti ti-circle-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Main Table -->
    <div class="card shadow-sm border-0">
        <div class="card-header border-bottom py-3">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label text-muted small fw-semibold">Filter by Status</label>
                    <select id="filter-status" class="form-select select2">
                        <option value="">All Statuses</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="draft">Draft</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small fw-semibold">Filter by Department</label>
                    <select id="filter-department" class="form-select select2">
                        <option value="">All Departments</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small fw-semibold">Filter by Branch</label>
                    <select id="filter-branch" class="form-select select2">
                        <option value="">All Branches</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="button" id="btn-reset-filters" class="btn btn-outline-secondary w-100 waves-effect">
                        <i class="ti ti-refresh me-1"></i> Reset Filters
                    </button>
                </div>
            </div>
        </div>

        <div class="card-datatable table-responsive">
            <table class="table table-hover border-top" id="onboarding-table">
                <thead class="table-light">
                    <tr>
                        <th width="5%">#</th>
                        <th width="25%">Candidate / New Hire</th>
                        <th width="20%">Designation & Dept</th>
                        <th width="15%">Reporting Manager</th>
                        <th width="15%">Progress</th>
                        <th width="12%">Status</th>
                        <th width="8%">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@endsection

@section('page_leavel_script')
    <script src="{{ asset('software/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script src="{{ asset('software/vendor/libs/select2/select2.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            $('.select2').select2({ width: '100%' });

            let table = $('#onboarding-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('onboarding.index') }}",
                    data: function(d) {
                        d.status = $('#filter-status').val();
                        d.department_id = $('#filter-department').val();
                        d.branch_id = $('#filter-branch').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'candidate_info', name: 'full_name' },
                    { data: 'role_dept', name: 'department.name' },
                    { data: 'manager', name: 'reportingManager.full_name' },
                    { data: 'progress_bar', name: 'progress_percentage', orderable: false, searchable: false },
                    { data: 'status_badge', name: 'status' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                order: [[0, 'desc']],
                language: {
                    search: "",
                    searchPlaceholder: "Search Candidate, Email, Phone..."
                }
            });

            $('#filter-status, #filter-department, #filter-branch').on('change', function() {
                table.draw();
            });

            $('#btn-reset-filters').on('click', function() {
                $('#filter-status').val('').trigger('change');
                $('#filter-department').val('').trigger('change');
                $('#filter-branch').val('').trigger('change');
                table.draw();
            });

            // Delete Record
            $(document).on('click', '.delete-record', function() {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This will remove the onboarding profile.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    customClass: {
                        confirmButton: 'btn btn-danger me-3',
                        cancelButton: 'btn btn-label-secondary'
                    },
                    buttonsStyling: false
                }).then(function(result) {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('software/onboarding') }}/" + id,
                            type: 'DELETE',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                if (response.status) {
                                    Swal.fire({ icon: 'success', title: 'Deleted!', text: response.message, timer: 1500, showConfirmButton: false });
                                    table.draw();
                                } else {
                                    Swal.fire({ icon: 'error', title: 'Error', text: response.message });
                                }
                            },
                            error: function(err) {
                                Swal.fire({ icon: 'error', title: 'Error', text: 'Something went wrong.' });
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
