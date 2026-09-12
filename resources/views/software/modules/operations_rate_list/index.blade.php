@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : null;
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
                'show_export_btn' => true,
                'show_excal_btn' => true,
                'show_print_btn' => true,
            ])
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="row my-3" id="filter_card" style="display: none;">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        @if (!$company_id)
                            <div class="col-md-3 mb-2">
                                <label class="form-label">Company</label>
                                <select id="filter_company" class="form-select select2">
                                    <option value="">All Companies</option>
                                    @foreach (\App\Models\Company::all() as $comp)
                                        <option value="{{ $comp->id }}">{{ $comp->company_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="col-md-3 mb-2">
                            <label class="form-label">Operation</label>
                            <select id="filter_operation" class="form-select select2">
                                <option value="">All Operations</option>
                                @foreach ($operations as $op)
                                    <option value="{{ $op }}" {{ request()->operation == $op ? 'selected' : '' }}>{{ $op }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label">Employee</label>
                            <select id="filter_employee" class="form-select select2">
                                <option value="">All Employees</option>
                                @if(isset($contractEmployees))
                                    @foreach ($contractEmployees as $emp)
                                        <option value="{{ $emp->id }}">{{ $emp->employee_code }} - {{ $emp->proper_name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="form-label">Month</label>
                            <select id="filter_month" class="form-select select2">
                                <option value="">All Months</option>
                                @for ($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}">{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="form-label">Year</label>
                            <select id="filter_year" class="form-select select2">
                                <option value="">All Years</option>
                                @for ($y = date('Y') - 5; $y <= date('Y') + 5; $y++)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end mb-2">
                            <button id="apply_filter" class="btn btn-primary w-100">Apply Filter</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row my-3">
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
            @if(request()->operation)
                $('#filter_card').show();
            @endif
            dtable = $('#yajra-datatables').DataTable({
                processing: true,
                serverSide: true,
                dom: '<"table-responsive"t><"datatable-footer d-flex justify-content-between align-items-center flex-wrap px-3 py-2"l i p>',
                order: [[0, 'desc']],
                ajax: {
                    url: "{{ route($route . '.index') }}",
                    type: "GET",
                    data: function(d) {
                        d.filter_company = $('#filter_company').val();
                        d.operation = $('#filter_operation').val();
                        d.employee_id = $('#filter_employee').val();
                        d.month = $('#filter_month').val();
                        d.year = $('#filter_year').val();
                    }
                },
                columns: {!! isset($columns) ? json_encode($columns) : [] !!},
            });

            $('#apply_filter').click(function() {
                dtable.draw();
            });

            $('#show_filter').click(function() {
                $('#filter_card').slideToggle();
            });

            $('#export_excel_btn').click(function() {
                let params = $.param({
                    filter_company: $('#filter_company').val(),
                    operation: $('#filter_operation').val(),
                    employee_id: $('#filter_employee').val(),
                    month: $('#filter_month').val(),
                    year: $('#filter_year').val()
                });
                window.location.href = "{{ route($route . '.export.excel') }}?" + params;
            });

            $('#print_btn').click(function() {
                let params = $.param({
                    filter_company: $('#filter_company').val(),
                    operation: $('#filter_operation').val(),
                    employee_id: $('#filter_employee').val(),
                    month: $('#filter_month').val(),
                    year: $('#filter_year').val()
                });
                window.open("{{ route($route . '.print') }}?" + params, '_blank');
            });

            // Open employee selection modal, then generate salary
            $(document).on('click', '.generate-salary', function() {
                var id = $(this).data('id');
                if (!id) return;
                $('#generateSalaryModalLabel').text('Select Employees to Generate Salary');
                $('#generateSalaryList').html('<div class="text-center py-3">Loading...</div>');
                $('#generateSalaryModal').data('group-id', id).modal('show');

                var grpUrl = "{{ route('operations-rate-list.group-employees', ['id' => ':id']) }}".replace(':id', id);
                $.get(grpUrl, function(res) {
                    var html = '';
                    if (res && res.employees && res.employees.length) {
                        html += '<div class="form-check mb-2"><input class="form-check-input" type="checkbox" id="select_all_emps"><label class="form-check-label" for="select_all_emps">Select All</label></div>';
                        res.employees.forEach(function(emp) {
                            var checked = emp.already_generated ? 'checked' : '';
                            var badge = emp.already_generated ? ' <span class="badge bg-success ms-2">Generated</span>' : '';
                            html += '<div class="form-check"><input class="form-check-input emp-checkbox" type="checkbox" value="' + emp.id + '" id="emp_' + emp.id + '" ' + checked + '><label class="form-check-label" for="emp_' + emp.id + '">' + emp.name + badge + '</label></div>';
                        });
                    } else {
                        html = '<div class="text-muted">No employees found for this group.</div>';
                    }
                    $('#generateSalaryList').html(html);
                }).fail(function() {
                    $('#generateSalaryList').html('<div class="text-danger">Failed to load employees.</div>');
                });
            });

            // Select all toggle
            $(document).on('change', '#select_all_emps', function() {
                var checked = $(this).is(':checked');
                $('.emp-checkbox').prop('checked', checked);
            });

            // Submit generate salary
            $(document).on('click', '#generateSalarySubmit', function() {
                var id = $('#generateSalaryModal').data('group-id');
                if (!id) return;
                var selected = $('.emp-checkbox:checked').map(function() { return $(this).val(); }).get();
                if (!selected.length) {
                    if (!confirm('No employee selected. Generate salary for none?')) return;
                }

                $.post("{{ route($route . '.generate-salary') }}", { id: id, employee_ids: selected }, function(res) {
                    if (res && (res.success || res.data)) {
                        alert(res.message || 'Salary Generated Successfully.');
                    } else {
                        alert(res.message || 'Salary generation completed.');
                    }
                    $('#generateSalaryModal').modal('hide');
                    dtable.draw();
                }).fail(function(xhr) {
                    var msg = (xhr.responseJSON && (xhr.responseJSON.message || xhr.responseJSON.error)) ? (xhr.responseJSON.message || xhr.responseJSON.error) : 'Error generating salary';
                    alert(msg);
                });
            });
        });
    </script>
    <!-- Generate Salary Modal -->
    <div class="modal fade" id="generateSalaryModal" tabindex="-1" aria-labelledby="generateSalaryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="generateSalaryModalLabel">Select Employees</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="generateSalaryList"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" id="generateSalarySubmit" class="btn btn-primary">Generate Salary</button>
                </div>
            </div>
        </div>
    </div>
    @include('software.inlcudes.script-delete-record')
    @include('software.inlcudes.script-update-status')
@endpush
