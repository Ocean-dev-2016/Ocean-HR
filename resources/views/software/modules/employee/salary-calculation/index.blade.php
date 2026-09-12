@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : null;
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $i = 0;
    $company_id = isset($modules['company_id']) ? $modules['company_id'] : null;

    $months = config('constants.months');

    // Auto-select current year and month
    $currentYear = date('Y');
    $currentMonth = date('n');
@endphp

@section('title', $page_title)

@section('page_leavel_style')
    <link rel="stylesheet" href="{{ asset('software/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('software/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
    <style>
        .summary-card {
            border-radius: 10px;
            padding: 15px;
            color: white;
            margin-bottom: 15px;
        }

        .summary-card.total-employees {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .summary-card.total-earning {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }

        .summary-card.total-deduction {
            background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
        }

        .summary-card.net-pay {
            background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%);
        }

        .summary-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
        }

        .summary-card p {
            margin: 0;
            opacity: 0.9;
            font-size: 0.85rem;
        }

        /* Hide date grid, keep month/year selector */
        .ui-datepicker-calendar {
            display: none !important;
        }
    </style>
@endsection

@section('content')
    <div id="filter_show" data-filter="true"></div>
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
                'show_bank_transfer_excel_btn' => isset($modules['excel_permission']) ? $modules['excel_permission'] : false,
            ])
        </div>
    </div>

    <div class="row my-3">
        {{-- Filter Section --}}
        <div class="col-md-12 mb-3" id="filter_section">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        @if (!$company_id)
                            <div class="col-md-3 col-sm-12 mb-2">
                                <div class="form-group">
                                    <label class="form-label">Filter by Company</label>
                                    <select id="company_id" name="company_id"
                                        class="form-control search_by_company select2 select_filter"
                                        data-append="search_by_company" showBranch="branchDiv"
                                        data-selectedcompanyid="{{ request('filter_company') }}">
                                        <option value="">All Companies</option>
                                    </select>
                                </div>
                            </div>
                        @else
                            <input type="hidden" class="form-control search_by_company" name="company_id" id="company_id"
                                value="{{ $company_id }}" showBranch="branchDiv" />
                        @endif

                        <div class="col-md-3 col-sm-12 mb-2 branchDiv" style="display:none;">
                            <div class="form-group">
                                <label class="form-label">Filter by Branch</label>
                                <select id="branch_id" name="branch_id"
                                    class="form-control search_by_branch select2 select_filter"
                                    data-append="search_by_branch">
                                    <option value="">All Branches</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-12 mb-2">
                            <div class="form-group">
                                <label class="form-label">Filter by Department</label>
                                <select id="department_id" name="department_id"
                                    class="form-control search_by_department select2 select_filter"
                                    data-append="search_by_department">
                                    <option value="">All Departments</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-12 mb-2">
                            <div class="form-group">
                                <label class="form-label">Filter by Employee</label>
                                <select id="employee_id" name="employee_id"
                                    class="form-control search_by_employee select2 select_filter"
                                    data-append="search_by_employee">
                                    <option value="">All Employees</option>
                                </select>
                            </div>
                        </div>

                        {{-- Filter by Month & Year (same as salary-slip) --}}
                        <div class="col-md-3 col-sm-12 mb-2">
                            <div class="form-group">
                                <label class="form-label">Filter by Month & Year</label>
                                <input type="text" id="salary_calc_month_year" name="month_year_display"
                                    class="form-control salary-calc-month-year-picker table_filter" readonly
                                    placeholder="Select Month & Year" autocomplete="off">
                                <input type="hidden" name="filter_year" id="filter_year" value="{{ request('filter_year', $currentYear) }}">
                                <input type="hidden" name="filter_month" id="filter_month" value="{{ request('filter_month', $currentMonth) }}">
                            </div>
                        </div>

                        <div class="col-md-2 col-sm-12 d-flex align-items-end mb-2">
                            <div class="flex-grow-1 me-2">
                                <label for="status_filter" class="form-label">Filter by Status</label>
                                <select id="status_filter" name="status" class="form-select select2">
                                    <option value="all">All</option>
                                    <option value="active" selected>Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div>
                                <button type="button" title="Clear Filter" id="clear_filter"
                                    class="btn btn-outline-danger btn-icon mt-2"><i class="ti ti-x"></i></button>
                            </div>
                        </div>

                        <div class="col-md-12 col-sm-12 mb-2">
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" id="bulk_calculate_btn" class="btn btn-primary">
                                    <i class="fa fa-calculator me-1"></i> Calculate Salary for All Employees
                                </button>
                                <a href="#" id="generate_salary_slip_btn" class="btn btn-success"
                                    title="Generate salary slips for filtered employees">
                                    <i class="fa fa-file-invoice me-1"></i> Generate Salary Slip
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Data Table --}}
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
        // Function to get CSRF token (if not in custom.js)
        if (typeof getCsrfToken === 'undefined') {
            function getCsrfToken() {
                let token = $('meta[name="csrf-token"]').attr('content');
                if (!token) {
                    token = '{{ csrf_token() }}';
                }
                return token;
            }
        }

        // Setup AJAX defaults with dynamic CSRF token
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': getCsrfToken()
            },
            beforeSend: function(xhr, settings) {
                // Update CSRF token before each request
                if (!/^(GET|HEAD|OPTIONS|TRACE)$/i.test(settings.type) && !this.crossDomain) {
                    xhr.setRequestHeader('X-CSRF-TOKEN', getCsrfToken());
                }
            }
        });

        var dtable = null;

        // Refresh CSRF token from meta tag
        function refreshCsrfToken() {
            let token = $('meta[name="csrf-token"]').attr('content');
            if (token) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': token
                    }
                });
            }
        }

        $(document).ready(function() {
            // Refresh CSRF token on page load
            refreshCsrfToken();
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
                        var urlParams = new URLSearchParams(window.location.search);
                        data.search = $('input[name="search"]').val();
                        data.filter_company = $('select[name="company_id"] option:selected').val() || urlParams.get('filter_company');
                        data.filter_branch = $('select[name="branch_id"] option:selected').val() || urlParams.get('filter_branch');
                        data.filter_department = $('select[name="department_id"] option:selected').val() || urlParams.get('filter_department');
                        data.filter_employee = $('select[name="employee_id"] option:selected').val() || urlParams.get('filter_employee');
                        data.filter_year = $('#filter_year').val() || urlParams.get('filter_year');
                        data.filter_month = $('#filter_month').val() || urlParams.get('filter_month');
                        data.status = $('#status_filter').val();
                    },
                },
                columns: {!! isset($columns) ? json_encode($columns) : [] !!},
                language: {
                    searchPlaceholder: 'Search...',
                }
            });

            // Filter change events
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

            // Clear filters - reset to All
            $("#clear_filter").click(function() {
                // Clear all select filters (Company, Branch, Dept, Employee)
                $('.select_filter').val('').trigger('change');

                // Reset status to All
                $('#status_filter').val('all').trigger('change');

                $('.search').val('');
                // Reset Month & Year to current
                var now = new Date();
                $('#salary_calc_month_year').datepicker('setDate', now);
                $('#filter_year').val(now.getFullYear());
                $('#filter_month').val(now.getMonth() + 1);
                dtable.draw();
            });

            var $monthYearInput = $('#salary_calc_month_year');
            var $filterYear = $('#filter_year');
            var $filterMonth = $('#filter_month');

            var now = new Date();
            var currentYear = now.getFullYear();
            var currentMonth = now.getMonth(); // 0-based

            $monthYearInput.datepicker({
                changeMonth: true,
                changeYear: true,
                showButtonPanel: true,
                dateFormat: 'MM yy',

                minDate: new Date(2024, 0, 1),
                maxDate: new Date(currentYear, currentMonth, 1),
                yearRange: "2024:" + currentYear,

                beforeShow: function() {
                    $('.ui-datepicker-calendar').hide();
                },

                onClose: function(dateText, inst) {
                    if (inst.selectedYear === undefined) return;

                    let year = inst.selectedYear;
                    let month = inst.selectedMonth + 1;

                    $(this).datepicker('setDate', new Date(year, inst.selectedMonth, 1));

                    $filterYear.val(year);
                    $filterMonth.val(month);

                    dtable.draw();
                }
            });

            // Default current/request month
            var requestYear = parseInt('{{ request("filter_year") }}', 10);
            var requestMonth = parseInt('{{ request("filter_month") }}', 10);
            var initYear = !isNaN(requestYear) ? requestYear : currentYear;
            var initMonth = !isNaN(requestMonth) ? (requestMonth - 1) : currentMonth;

            $monthYearInput.datepicker('setDate', new Date(initYear, initMonth, 1));
            $filterYear.val(initYear);
            $filterMonth.val(initMonth + 1);

            // Ensure open on click (readonly-safe)
            $monthYearInput.on('click', function() {
                $(this).datepicker('show');
            });

            // Dynamic calculation button text
            function updateCalculateButtonText() {
                let empName = $('#employee_id option:selected').val() ? $('#employee_id option:selected').text() : '';
                let deptName = $('#department_id option:selected').val() ? $('#department_id option:selected').text() : '';
                let branchName = $('#branch_id option:selected').val() ? $('#branch_id option:selected').text() : '';
                
                let btnText = 'All Employees';
                if (empName) {
                    btnText = empName;
                } else if (deptName) {
                    btnText = deptName + ' Department';
                } else if (branchName) {
                    btnText = branchName + ' Branch';
                }
                
                $('#bulk_calculate_btn').html('<i class="fa fa-calculator me-1"></i> Calculate Salary for ' + btnText);
            }

            $('.select_filter').on('change', function() {
                updateCalculateButtonText();
            });
            // Initial call
            updateCalculateButtonText();

        });

        // Export Excel
        $('#export_excel_btn').on('click', function(e) {
            e.preventDefault();

            let queryParams = $.param({
                search: $('input[name="search"]').val(),
                status: $('#status_filter').val(),
                filter_company: $('#company_id').val(),
                filter_branch: $('#branch_id').val(),
                filter_employee: $('#employee_id').val(),
                filter_year: $('#filter_year').val(),
                filter_month: $('#filter_month').val()
            });

            let url = "{{ route($route . '.export.excel') }}" + "?" + queryParams;
            window.location.href = url;
        });

        $('#export_bank_transfer_excel_btn').on('click', function(e) {
            e.preventDefault();

            let months = @json(config('constants.months'));
            let currentFilterYear = $('#filter_year').val() || new Date().getFullYear();
            let currentFilterMonth = parseInt($('#filter_month').val() || (new Date().getMonth() + 1), 10);
            let currentYear = new Date().getFullYear();
            let startYear = currentYear - 5;
            let endYear = currentYear + 5;
            let yearOptions = [];

            for (let y = startYear; y <= endYear; y++) {
                yearOptions.push(y);
            }

            if (currentFilterYear && !yearOptions.includes(parseInt(currentFilterYear, 10))) {
                yearOptions.push(parseInt(currentFilterYear, 10));
                yearOptions.sort(function(a, b) {
                    return a - b;
                });
            }

            let monthOptions = Object.entries(months).map(function(entry) {
                let value = parseInt(entry[0], 10);
                let label = entry[1];
                let selected = value === currentFilterMonth ? 'selected' : '';
                return '<option value="' + value + '" ' + selected + '>' + label + '</option>';
            }).join('');

            let yearOptionsHtml = yearOptions.map(function(year) {
                let selected = parseInt(currentFilterYear, 10) === parseInt(year, 10) ? 'selected' : '';
                return '<option value="' + year + '" ' + selected + '>' + year + '</option>';
            }).join('');

            Swal.fire({
                title: 'Export Bank Transfer Excel',
                html: `
                    <div class="text-start">
                        <div class="mb-3">
                            <label for="bank_transfer_month" class="form-label">Month</label>
                            <select id="bank_transfer_month" class="form-control select2 w-100">
                                <option value="">Select Month</option>
                                ${monthOptions}
                            </select>
                        </div>
                        <div class="mb-1">
                            <label for="bank_transfer_year" class="form-label">Year</label>
                            <select id="bank_transfer_year" class="form-control select2 w-100">
                                <option value="">Select Year</option>
                                ${yearOptionsHtml}
                            </select>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                reverseButtons: true,
                buttonsStyling: false,
                confirmButtonText: '<i class="fa fa-save me-1"></i> Save',
                cancelButtonText: '<i class="fa fa-times me-1"></i> Cancel',
                customClass: {
                    confirmButton: 'swal2-confirm btn btn-primary fw-bold mx-2 px-4',
                    cancelButton: 'swal2-cancel btn btn-secondary fw-bold mx-2 px-4',
                    popup: 'text-start'
                },
                width: '480px',
                focusConfirm: false,
                didOpen: () => {
                    const popup = Swal.getPopup();
                    const monthField = popup.querySelector('#bank_transfer_month');
                    const yearField = popup.querySelector('#bank_transfer_year');

                    if ($ && $.fn && $.fn.select2) {
                        $(monthField).select2({
                            dropdownParent: $(popup),
                            width: '100%',
                            placeholder: 'Select Month',
                            allowClear: true
                        });

                        $(yearField).select2({
                            dropdownParent: $(popup),
                            width: '100%',
                            placeholder: 'Select Year',
                            allowClear: true
                        });
                    }

                    if (monthField) {
                        monthField.focus();
                    } else if (yearField) {
                        yearField.focus();
                    }
                },
                preConfirm: () => {
                    const popup = Swal.getPopup();
                    const month = popup.querySelector('#bank_transfer_month')?.value;
                    const year = popup.querySelector('#bank_transfer_year')?.value;

                    if (!month) {
                        Swal.showValidationMessage('Please select a month');
                        return false;
                    }

                    if (!year) {
                        Swal.showValidationMessage('Please enter a year');
                        return false;
                    }

                    return { month, year };
                }
            }).then((result) => {
                if (!result.isConfirmed || !result.value) {
                    return;
                }

                let queryParams = $.param({
                    search: $('input[name="search"]').val(),
                    status: $('#status_filter').val(),
                    filter_company: $('#company_id').val(),
                    filter_branch: $('#branch_id').val(),
                    filter_department: $('#department_id').val(),
                    filter_employee: $('#employee_id').val(),
                    filter_year: result.value.year,
                    filter_month: result.value.month
                });

                let url = "{{ route($route . '.export.bank-transfer-excel') }}" + "?" + queryParams;
                window.location.href = url;
            });
        });

        // Print
        $('#print_btn').on('click', function(e) {
            e.preventDefault();

            let queryParams = $.param({
                search: $('input[name="search"]').val(),
                status: $('#status_filter').val(),
                company: $('select[name="company_id"]').val(),
                branch: $('select[name="branch_id"]').val(),
                employee: $('#employee_id').val(),
            });

            let url = "{{ route($route . '.print') }}" + "?" + queryParams;
            window.open(url, '_blank');
        });

        // Generate Salary Slip - open salary slip print with current filters (company, year, month, optional employee)
        $('#generate_salary_slip_btn').on('click', function(e) {
            e.preventDefault();
            let companyId = $('#company_id').val();
            let year = $('#filter_year').val();
            let month = $('#filter_month').val();

            if (!companyId) {
                toastr.warning('Please select a Company.');
                return;
            }
            if (!year || !month) {
                toastr.warning('Please select Year and Month.');
                return;
            }

            let pad = (n) => String(n).padStart(2, '0');
            let lastDay = new Date(parseInt(year), parseInt(month), 0).getDate();
            let followupDate = '01/' + pad(month) + '/' + year + ' - ' + pad(lastDay) + '/' + pad(month) + '/' +
                year;
            let employeeId = $('#employee_id').val();

            let params = new URLSearchParams();
            params.append('company_id', companyId);
            params.append('followup_date', followupDate);
            if (employeeId) {
                params.append('employee_id', employeeId);
            }
            let url = "{{ route('salary-slip.print') }}?" + params.toString();
            window.open(url, '_blank');
        });
    </script>

    @if (!$company_id)
        @include('utils.getCompany')
    @endif
    @include('utils.getDepartment')
    @include('utils.getEmployee')
    @include('software.inlcudes.script-delete-record')
    @include('software.inlcudes.script-restore-record')
    @include('software.inlcudes.script-update-status')

    <script>
        // Check Data Integrity
        $(document).on('click', '.check-integrity-btn', function(e) {
            e.preventDefault();
            let salaryId = $(this).data('salary-id');
            let $btn = $(this);

            InlineLoader.showButton($btn, '<i class="fa fa-shield-alt"></i>');

            $.ajax({
                url: "{{ route('salary-calculation.check-integrity') }}",
                type: 'POST',
                data: {
                    salary_id: salaryId
                },
                success: function(response) {
                    InlineLoader.hideButton($btn);

                    if (response.success) {
                        let message = 'Data integrity check completed.\n';
                        if (response.data.has_modifications) {
                            message += '\n⚠️ WARNING: Source data has been modified!\n\n';
                            let mods = response.data.modifications;
                            if (mods.attendance && mods.attendance.modified) {
                                message += '• Attendance data was modified\n';
                            }
                            if (mods.leave && mods.leave.modified) {
                                message += '• Leave data was modified\n';
                            }
                            if (mods.holiday && mods.holiday.modified) {
                                message += '• Holiday data was modified\n';
                            }
                            if (mods.salary_detail && mods.salary_detail.modified) {
                                message += '• Salary detail was modified\n';
                            }
                            message += '\nPlease recalculate the salary to reflect the changes.';
                            toastr.warning(message, 'Data Modified');
                        } else {
                            message += '\n✓ All source data is unchanged.';
                            toastr.success(message, 'Data Valid');
                        }
                        // Refresh table to show updated status
                        dtable.draw();
                    } else {
                        toastr.error(response.message || 'Error checking data integrity', 'Error');
                    }
                },
                error: function(xhr) {
                    InlineLoader.hideButton($btn);
                    let errorMessage = 'Something went wrong!';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    toastr.error(errorMessage, 'Error');
                }
            });
        });

        // Lock Salary
        $(document).on('click', '.lock-salary-btn', function(e) {
            e.preventDefault();
            let salaryId = $(this).data('salary-id');
            let $btn = $(this);

            Swal.fire({
                title: 'Lock Salary?',
                text: 'Locked salaries cannot be recalculated or modified.',
                icon: 'warning',
                showCancelButton: true,
                reverseButtons: true,
                buttonsStyling: false,
                confirmButtonText: '<i class="fa fa-lock me-1"></i> Yes, Lock',
                cancelButtonText: '<i class="fa fa-times me-1"></i> Cancel',
                customClass: {
                    confirmButton: 'swal2-confirm btn btn-warning fw-bold mx-2 px-4',
                    cancelButton: 'swal2-cancel btn btn-secondary fw-bold mx-2 px-4'
                }
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                InlineLoader.showButton($btn, '<i class="fa fa-lock"></i>');

                $.ajax({
                    url: "{{ route('salary-calculation.lock') }}",
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    data: {
                        _token: getCsrfToken(),
                        salary_id: salaryId
                    },
                    success: function(response) {
                        InlineLoader.hideButton($btn);

                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Locked!',
                                text: response.message || 'Salary locked successfully',
                                buttonsStyling: false,
                                confirmButtonText: 'OK',
                                customClass: {
                                    confirmButton: 'btn btn-success fw-bold px-4'
                                }
                            });
                            dtable.draw();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Error locking salary',
                                buttonsStyling: false,
                                confirmButtonText: 'OK',
                                customClass: {
                                    confirmButton: 'btn btn-danger fw-bold px-4'
                                }
                            });
                        }
                    },
                    error: function(xhr) {
                        InlineLoader.hideButton($btn);
                        let errorMessage = 'Something went wrong!';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMessage,
                            buttonsStyling: false,
                            confirmButtonText: 'OK',
                            customClass: {
                                confirmButton: 'btn btn-danger fw-bold px-4'
                            }
                        });
                    }
                });
            });
        });

        // Unlock Salary
        $(document).on('click', '.unlock-salary-btn', function(e) {
            e.preventDefault();
            let salaryId = $(this).data('salary-id');
            let $btn = $(this);

            Swal.fire({
                title: 'Unlock Salary?',
                text: 'This will allow recalculation and modification.',
                icon: 'question',
                showCancelButton: true,
                reverseButtons: true,
                buttonsStyling: false,
                confirmButtonText: '<i class="fa fa-unlock me-1"></i> Yes, Unlock',
                cancelButtonText: '<i class="fa fa-times me-1"></i> Cancel',
                customClass: {
                    confirmButton: 'swal2-confirm btn btn-primary fw-bold mx-2 px-4',
                    cancelButton: 'swal2-cancel btn btn-secondary fw-bold mx-2 px-4'
                }
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                InlineLoader.showButton($btn, '<i class="fa fa-unlock"></i>');

                $.ajax({
                    url: "{{ route('salary-calculation.unlock') }}",
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    data: {
                        _token: getCsrfToken(),
                        salary_id: salaryId
                    },
                    success: function(response) {
                        InlineLoader.hideButton($btn);

                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Unlocked!',
                                text: response.message ||
                                    'Salary unlocked successfully',
                                buttonsStyling: false,
                                confirmButtonText: 'OK',
                                customClass: {
                                    confirmButton: 'btn btn-success fw-bold px-4'
                                }
                            });
                            dtable.draw();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Error unlocking salary',
                                buttonsStyling: false,
                                confirmButtonText: 'OK',
                                customClass: {
                                    confirmButton: 'btn btn-danger fw-bold px-4'
                                }
                            });
                        }
                    },
                    error: function(xhr) {
                        InlineLoader.hideButton($btn);
                        let errorMessage = 'Something went wrong!';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMessage,
                            buttonsStyling: false,
                            confirmButtonText: 'OK',
                            customClass: {
                                confirmButton: 'btn btn-danger fw-bold px-4'
                            }
                        });
                    }
                });
            });
        });

        // Initialize tooltips
        $(document).ready(function() {
            $('[data-bs-toggle="tooltip"]').tooltip();
        });

        // Bulk Calculate Salary for All Employees
        $('#bulk_calculate_btn').on('click', function(e) {
            e.preventDefault();

            let companyId = $('#company_id').val();
            let branchId = $('#branch_id').val();
            let departmentId = $('#department_id').val();
            let employeeId = $('#employee_id').val();
            let year = $('#filter_year').val();
            let month = $('#filter_month').val();

            // Validation
            if (!companyId) {
                toastr.error('Please select a company', 'Validation Error');
                return;
            }

            if (!year) {
                toastr.error('Please select a year', 'Validation Error');
                return;
            }

            if (!month) {
                toastr.error('Please select a month', 'Validation Error');
                return;
            }

            // Build confirmation message details (month name from month number)
            var monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September',
                'October', 'November', 'December'
            ];
            let periodText = (monthNames[parseInt(month, 10) - 1] || month) + ' ' + year;
            let detailsHtml = '<div class="text-start">';
            detailsHtml += '<p class="mb-2"><strong>Period:</strong> ' + periodText + '</p>';
            if (branchId) {
                detailsHtml += '<p class="mb-2"><strong>Branch:</strong> ' + $('#branch_id option:selected')
                    .text() + '</p>';
            }
            if (departmentId) {
                detailsHtml += '<p class="mb-2"><strong>Department:</strong> ' + $('#department_id option:selected')
                    .text() + '</p>';
            }
            if (employeeId) {
                detailsHtml += '<p class="mb-2"><strong>Employee:</strong> ' + $('#employee_id option:selected')
                    .text() + '</p>';
            }
            detailsHtml +=
                '<p class="mb-0 text-warning"><i class="fa fa-exclamation-triangle me-1"></i> This may take a few minutes.</p>';
            detailsHtml += '</div>';

            let titleText = 'Calculate Salary for ' + (employeeId ? $('#employee_id option:selected').text() : (departmentId ? $('#department_id option:selected').text() + ' Department' : (branchId ? $('#branch_id option:selected').text() + ' Branch' : 'All Employees'))) + '?';

            // Show SweetAlert confirmation
            Swal.fire({
                title: titleText,
                html: detailsHtml,
                icon: 'question',
                showCancelButton: true,
                reverseButtons: true,
                buttonsStyling: false,
                confirmButtonText: '<i class="fa fa-calculator me-1"></i> Yes, Calculate',
                cancelButtonText: '<i class="fa fa-times me-1"></i> Cancel',
                customClass: {
                    confirmButton: 'swal2-confirm btn btn-primary fw-bold mx-2 px-4',
                    cancelButton: 'swal2-cancel btn btn-secondary fw-bold mx-2 px-4',
                    popup: 'text-start'
                },
                width: '500px'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                let $btn = $('#bulk_calculate_btn');
                InlineLoader.showButton($btn, '<i class="fa fa-calculator me-1"></i> Calculating...');
                $btn.prop('disabled', true);

                $.ajax({
                    url: "{{ route('salary-calculation.bulk-calculate') }}",
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    data: {
                        _token: getCsrfToken(),
                        company_id: companyId,
                        branch_id: branchId || null,
                        department_id: departmentId || null,
                        employee_id: employeeId || null,
                        year: year,
                        month: month
                    },
                    success: function(response) {
                        InlineLoader.hideButton($btn);
                        $btn.prop('disabled', false);

                        if (response.success) {
                            let data = response.data;
                            let resultHtml = '<div class="text-start">';
                            resultHtml += '<p class="mb-2"><strong>Total Employees:</strong> ' +
                                data.total + '</p>';
                            resultHtml +=
                                '<p class="mb-2 text-success"><strong>Successful:</strong> ' +
                                data.successful + '</p>';

                            if (data.failed > 0) {
                                resultHtml +=
                                    '<p class="mb-2 text-danger"><strong>Failed:</strong> ' +
                                    data.failed + '</p>';
                            }

                            if (data.skipped > 0) {
                                resultHtml +=
                                    '<p class="mb-2 text-warning"><strong>Skipped (Locked):</strong> ' +
                                    data.skipped + '</p>';
                            }

                            if (data.errors && data.errors.length > 0) {
                                resultHtml += '<hr class="my-3">';
                                resultHtml += '<p class="mb-2"><strong>Errors:</strong></p>';
                                resultHtml +=
                                    '<ul class="list-unstyled mb-0" style="max-height: 200px; overflow-y: auto;">';
                                data.errors.slice(0, 10).forEach(function(error) {
                                    resultHtml += '<li class="mb-1"><small>• <strong>' +
                                        error.employee + ':</strong> ' + error.message +
                                        '</small></li>';
                                });
                                if (data.errors.length > 10) {
                                    resultHtml +=
                                        '<li class="mb-1"><small class="text-muted">... and ' +
                                        (data.errors.length - 10) + ' more errors</small></li>';
                                }
                                resultHtml += '</ul>';
                            }
                            resultHtml += '</div>';

                            // Show result in SweetAlert
                            Swal.fire({
                                icon: data.failed > 0 || data.errors.length > 0 ?
                                    'warning' : 'success',
                                title: data.failed > 0 || data.errors.length > 0 ?
                                    'Calculation Completed with Errors' :
                                    'Calculation Completed Successfully',
                                html: resultHtml,
                                buttonsStyling: false,
                                confirmButtonText: 'OK',
                                customClass: {
                                    confirmButton: 'btn btn-primary fw-bold px-4',
                                    popup: 'text-start'
                                },
                                width: '600px'
                            });

                            // Refresh table to show new calculations
                            dtable.draw();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Error in bulk calculation',
                                buttonsStyling: false,
                                confirmButtonText: 'OK',
                                customClass: {
                                    confirmButton: 'btn btn-danger fw-bold px-4'
                                }
                            });
                        }
                    },
                    error: function(xhr) {
                        InlineLoader.hideButton($btn);
                        $btn.prop('disabled', false);

                        let errorMessage = 'Something went wrong!';
                        let errorTitle = 'Error';

                        // Handle CSRF token mismatch
                        if (xhr.status === 419 || (xhr.responseJSON && xhr.responseJSON
                                .message && (xhr.responseJSON.message.includes('CSRF') || xhr
                                    .responseJSON.message.includes('token')))) {
                            // Try to refresh the token and retry once
                            refreshCsrfToken();
                            errorMessage =
                                'Session expired. Please refresh the page and try again.';
                            errorTitle = 'Session Expired';

                            Swal.fire({
                                icon: 'error',
                                title: errorTitle,
                                text: errorMessage,
                                buttonsStyling: false,
                                confirmButtonText: 'Refresh Page',
                                customClass: {
                                    confirmButton: 'btn btn-warning fw-bold px-4'
                                }
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.reload();
                                }
                            });
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                            Swal.fire({
                                icon: 'error',
                                title: errorTitle,
                                text: errorMessage,
                                buttonsStyling: false,
                                confirmButtonText: 'OK',
                                customClass: {
                                    confirmButton: 'btn btn-danger fw-bold px-4'
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: errorTitle,
                                text: errorMessage,
                                buttonsStyling: false,
                                confirmButtonText: 'OK',
                                customClass: {
                                    confirmButton: 'btn btn-danger fw-bold px-4'
                                }
                            });
                        }
                    }
                });
            });
        });
    </script>
@endpush
