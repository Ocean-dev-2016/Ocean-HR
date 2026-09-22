@extends('software.layout.app')

@php
    $i = 0;
    $page_title = $modules['title'] ?? null;
    $folder_path = $modules['folder_path'] ?? null;
    $route = $modules['route'] ?? null;
    $company_id = $modules['company_id'] ?? null;
    $authLoginUserDetail = $modules['authLoginUserDetail'] ?? null;
    $loginUserId = $authLoginUserDetail?->id ?? null;
    $parent_type_id = $modules['parent_type_id'] ?? null;
@endphp

@section('title', $page_title)

@section('page_leavel_style')
    <link rel="stylesheet" href="{{ asset('software/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('software/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('content')
    <style>
        .salary-slip {
            border: 1px solid #000000;
            background-color: #f8f9f9;
        }

        .salary-header {
            display: flex;
            width: 100%;
            border-bottom: 1px solid #4a5b41;
        }

        .salary-header .left-block,
        .salary-header .center-block,
        .salary-header .right-block {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 80px;
            padding: 5px;
            background-color: #fff;
        }

        .salary-header .left-block img {
            max-width: 100%;
            height: auto;
        }

        .salary-header .center-block {
            flex: 1;
            flex-direction: column;
            border-left: 1px solid #4a5b41;
            border-right: 1px solid #4a5b41;
        }

        .salary-header .right-block {
            /* background-color: #dbe5d1; */
            font-weight: bold;
            letter-spacing: 1px;
        }

        .salary-header h4 {
            font-weight: 700;
            margin-bottom: 4px;
        }

        .salary-header .divider {
            width: 80px;
            border-bottom: 1px solid #000;
            margin: 4px auto;
        }

        /* Employee Info */
        .employee-info {
            display: flex;
            border-bottom: 1px solid #4a5b41;
        }

        .employee-info .col {
            flex: 1;
            /* text-align: center; */
            padding: 10px;
        }

        .employee-info .col:first-child {
            border-right: 1px solid #4a5b41;
        }

        /* Table */
        .salary-slip table {
            width: 100%;
            border-collapse: collapse;
        }

        .salary-slip table th,
        .salary-slip table td {
            border: 1px solid #000;
            padding: 6px 8px;
            vertical-align: middle;
        }

        .salary-slip table thead th {
            background-color: #dbe5d1;
            color: white;
            font-weight: bold;
        }

        .table-info {
            background-color: #dbe5d1 !important;
        }

        .table-warning {
            background-color: #fdf3d1 !important;
            font-size: 15px;
        }

        .fs-5 {
            font-size: 1.25rem !important;
        }

        /* Slip content (from partial) */
        .salary-slip-single {
            border: 1px solid #333;
            background: #fafafa;
            margin-bottom: 20px;
        }

        .salary-slip-single .salary-header {
            display: flex;
            width: 100%;
            border-bottom: 1px solid #333;
        }

        .salary-slip-single .left-block {
            width: 28%;
            padding: 10px;
            text-align: center;
            border-right: 1px solid #333;
        }

        .salary-slip-single .left-block img {
            max-height: 70px;
        }

        .salary-slip-single .center-block {
            flex: 1;
            text-align: center;
            padding: 10px;
            border-right: 1px solid #333;
        }

        .salary-slip-single .right-block.confidential {
            width: 22%;
            padding: 10px;
            text-align: center;
            font-weight: bold;
            /* background: #f5f0e6; */
        }

        .salary-slip-single .employee-info .col {
            padding: 10px;
        }

        .salary-slip-single .attendance-summary {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }

        .salary-slip-single .slip-table {
            width: 100%;
            border-collapse: collapse;
        }

        .salary-slip-single .slip-table th,
        .salary-slip-single .slip-table td {
            border: 1px solid #333;
            padding: 6px 8px;
        }

        .salary-slip-single .slip-table th {
            background: #e8e4dc;
        }

        .salary-slip-single .slip-table tfoot .row-total th {
            background: #e8e4dc;
        }

        .salary-slip-single .slip-table tfoot .row-net {
            background: #f5f0e6;
        }

        .salary-slip-single .slip-footer {
            padding: 6px 10px;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
        }

        /* jQuery UI month/year picker - hide calendar (jsfiddle style) */
        .salary-slip-month-year-picker+.ui-datepicker-calendar,
        #ui-datepicker-div.ui-datepicker .ui-datepicker-calendar {
            display: none !important;
        }

        /* Report-specific Scroll Layout */
        #salary_slip_content {
            max-height: 70vh;
            overflow-y: auto;
            overflow-x: auto;
            padding: 15px;
        }
        
        .salary-slip-single {
            min-width: 800px;
        }
    </style>
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.inlcudes.breadcrumb', [
            'breadcrumbArray' => [['title' => $page_title, 'url' => '']],
        ])
    </div>

    <div class="row my-3">
        <div class="col-md-12 mb-5">
            {{-- Filters --}}
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        @if (!$company_id)
                            <div class="col-md-3 col-sm-12 mb-2">
                                <div class="form-group">
                                    <label class="form-label">Filter by Company</label>
                                    <select id="company_id" name="company_id"
                                        class="form-control search_by_company select2 select_filter"
                                        style="width: 100%;"
                                        data-append="search_by_company" showBranch="branchDiv">
                                        <option value="">Filter by Company</option>
                                    </select>
                                </div>
                            </div>
                        @else
                            <input type="hidden" id="company_id" value="{{ $company_id }}">
                        @endif

                        <div class="{{ !$company_id ? 'col-md-3' : 'col-md-4' }} col-sm-12 mb-2">
                            <div class="form-group">
                                <label class="form-label">Filter by Employee</label>
                                <select id="employee_id" name="employee_id"
                                    class="form-control search_by_employee select2 select_filter"
                                    style="width: 100%;"
                                    data-append="search_by_employee">
                                    <option value="">Filter by Employee</option>
                                </select>
                            </div>
                        </div>

                        <div class="{{ !$company_id ? 'col-md-2' : 'col-md-3' }} col-sm-12 mb-2">
                            <div class="form-group">
                                <label class="form-label">Filter by Month & Year</label>
                                <input type="text" id="salary_slip_month_year"
                                    class="form-control salary-slip-month-year-picker" readonly
                                    placeholder="Select Month & Year">

                                <input type="hidden" name="followup_date" id="salary_slip_followup_date">
                            </div>
                        </div>

                        <input type="hidden" id="customer_id_hidden" value="">

                        <div class="{{ !$company_id ? 'col-md-4' : 'col-md-5' }} col-sm-12 mb-2 d-flex align-items-end gap-2 flex-wrap">
                            <button type="button" class="btn btn-primary" id="view_slip_btn">
                                <i class="fa fa-eye me-1"></i> Show Slip
                            </button>
                            <button type="button" class="btn btn-warning" id="print_slip_btn">
                                <i class="fa fa-print me-1"></i> Print
                            </button>
                            <button type="button" class="btn btn-success" id="pdf_slip_btn">
                                <i class="fa fa-file-pdf me-1"></i> Download PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Salary Slip container (filled by Show Slip / AJAX) --}}
            <div id="salary_slip_container" class="card p-0 mt-4" style="display: none;">
                <div class="card-body p-0">
                    <div id="salary_slip_content"></div>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('page_leavel_script')
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="{{ asset('software/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endsection

@push('page_scripts')
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(function() {

            let $monthYear = $("#salary_slip_month_year");
            let $hiddenDate = $("#salary_slip_followup_date");

            let today = new Date();
            let currentMonth = today.getMonth();
            let currentYear = today.getFullYear();
            let minYear = 2025;

            $monthYear.datepicker({
                changeMonth: true,
                changeYear: true,
                showButtonPanel: true,
                dateFormat: "MM yy",

                minDate: new Date(minYear, 0, 1), // ✅ Jan 2020
                maxDate: new Date(currentYear, currentMonth, 1),

                beforeShow: function() {
                    $(".ui-datepicker-calendar").hide();
                },

                onClose: function(dateText, inst) {
                    let month = inst.selectedMonth;
                    let year = inst.selectedYear;

                    if (year === undefined) return;

                    $(this).datepicker("setDate", new Date(year, month, 1));

                    let lastDay = new Date(year, month + 1, 0).getDate();
                    let pad = n => n < 10 ? '0' + n : n;

                    $hiddenDate.val(
                        `01/${pad(month + 1)}/${year} - ${pad(lastDay)}/${pad(month + 1)}/${year}`
                    );
                }
            });

            // ✅ Default current month
            $monthYear.datepicker("setDate", new Date(currentYear, currentMonth, 1));
            let lastDay = new Date(currentYear, currentMonth + 1, 0).getDate();
            $hiddenDate.val(
                `01/${String(currentMonth + 1).padStart(2,'0')}/${currentYear} - ` +
                `${lastDay}/${String(currentMonth + 1).padStart(2,'0')}/${currentYear}`
            );

            // Ensure datepicker opens on click (fix for readonly input not opening)
            $monthYear.on("click", function() {
                $(this).datepicker("show");
            });
        });

        function getSlipParams() {
            let company_id = $('#company_id').val() || '{{ $company_id ?? '' }}';
            let employee_id = $('#employee_id').val();
            let followup_date = ($('input[name="followup_date"]').val() || '').replace(/ to /g, ' - ');
            return {
                company_id,
                employee_id,
                followup_date
            };
        }

        function validateSlipFilters() {
            let {
                company_id,
                followup_date
            } = getSlipParams();
            if (!company_id) {
                toastr.error("Please select Company.");
                return false;
            }
            if (!followup_date) {
                toastr.error("Please select Month & Year.");
                return false;
            }
            return true;
        }

        $('#view_slip_btn').click(function() {
            if (!validateSlipFilters()) return;
            let data = getSlipParams();
            data._token = '{{ csrf_token() }}';

            $('#salary_slip_content').html('<div class="text-center p-4">Loading...</div>');
            $('#salary_slip_container').show();

            $.ajax({
                url: "{{ route('salary-slip.getReport') }}",
                type: "POST",
                dataType: 'json',
                data: data,
                success: function(response) {
                    if (response.status && response.data?.html) {
                        $('#salary_slip_content').html(response.data.html);
                        if (response.data.count > 0) {
                            toastr.success('Slip(s) loaded.');
                        }
                    } else {
                        $('#salary_slip_content').html(response.data?.html ||
                            '<div class="alert alert-warning">No salary record found.</div>');
                    }
                },
                error: function(xhr) {
                    let msg = xhr.responseJSON?.message || 'Failed to load salary slip.';
                    $('#salary_slip_content').html('<div class="alert alert-danger">' + msg + '</div>');
                    toastr.error(msg);
                }
            });
        });

        $('#print_slip_btn').click(function() {
            if (!validateSlipFilters()) return;
            let {
                company_id,
                employee_id,
                followup_date
            } = getSlipParams();
            const params = new URLSearchParams();
            params.append("company_id", company_id);
            if (employee_id) params.append("employee_id", employee_id);
            if (followup_date) params.append("followup_date", followup_date);
            window.open("{{ route('salary-slip.print') }}?" + params.toString(), "_blank");
        });

        $('#pdf_slip_btn').click(function() {
            if (!validateSlipFilters()) return;
            let {
                company_id,
                employee_id,
                followup_date
            } = getSlipParams();
            const params = new URLSearchParams();
            params.append("company_id", company_id);
            if (employee_id) params.append("employee_id", employee_id);
            if (followup_date) params.append("followup_date", followup_date);
            window.location.href = "{{ route('salary-slip.pdf') }}?" + params.toString();
        });
    </script>

    @if (!$company_id)
        @include('utils.getCompany')
    @endif
    @include('utils.getEmployee')

    @include('software.inlcudes.script-delete-record')
    @include('software.inlcudes.script-update-status')
    @include('software.inlcudes.script-restore-record')
@endpush
