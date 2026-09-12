@extends('software.layout.app')

@php
    use App\Models\Company;
    $page_title = isset($modules['title']) ? $modules['title'] : null;
    if (request('view_mode') == 1) {
        $page_title = "View " . $page_title;
    }
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $i = 0;
    $company_id = isset($modules['company_id']) ? $modules['company_id'] : null;

    $colums = 'col-md-3 col-sm-12 mb-2';
    $colums_half = 'col-md-6 col-sm-12 mb-2';

    $months = config('constants.months', [
        1 => 'January',
        2 => 'February',
        3 => 'March',
        4 => 'April',
        5 => 'May',
        6 => 'June',
        7 => 'July',
        8 => 'August',
        9 => 'September',
        10 => 'October',
        11 => 'November',
        12 => 'December',
    ]);

    // Auto-select current year and month
    $currentYear = date('Y');
    $currentMonth = date('n');
@endphp

@section('title', $page_title)

@section('page_leavel_style')
    <link rel="stylesheet" href="{{ asset('software/vendor/libs/flatpickr/flatpickr.css') }}" />
    <style>
        /* Main Container Spacing */
        .salary-form-container {
            /* padding: 20px 15px;
                                                                max-width: 1400px;
                                                                margin: 0 auto; */
        }

        /* Section Cards - Enhanced Styling */
        .section-card {
            border: 1px solid #e3e6f0;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            background: #ffffff;
            transition: box-shadow 0.3s ease;
        }

        .section-card:hover {
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
        }

        .section-card .card-header {
            background: linear-gradient(135deg, #f8f9fc 0%, #e9ecef 100%);
            border-bottom: 2px solid #e3e6f0;
            font-weight: 600;
            color: #5a5c69;
            padding: 18px 24px;
            font-size: 1.05rem;
            border-radius: 12px 12px 0 0;
        }

        .section-card .card-header i {
            font-size: 1.1rem;
            margin-right: 8px;
            color: #667eea;
        }

        .section-card .card-header.bg-danger {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
        }

        .section-card .card-header.bg-danger i {
            color: white;
        }

        .section-card .card-header.bg-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }

        .section-card .card-header.bg-success i {
            color: white;
        }

        .section-card .card-body {
            padding: 28px 24px;
        }

        /* Form Group Spacing */
        .form-group {
            margin-bottom: 22px;
        }

        .form-label-sm {
            font-size: 0.875rem;
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
            display: block;
        }

        .form-label-sm .badge {
            margin-left: 8px;
            font-size: 0.7rem;
            padding: 4px 8px;
            vertical-align: middle;
        }

        /* Input Field Styling */
        .form-control {
            padding: 10px 14px;
            border-radius: 8px;
            border: 1px solid #ced4da;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
        }

        .readonly-field {
            background-color: #f8f9fc !important;
            border-color: #e3e6f0 !important;
            cursor: not-allowed;
        }

        .readonly-field:focus {
            border-color: #e3e6f0 !important;
            box-shadow: none;
        }

        .manual-field {
            background-color: #fff9e6 !important;
            border: 2px solid #ffc107 !important;
            font-weight: 500;
        }

        .manual-field:focus {
            border-color: #ffc107 !important;
            box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
        }

        /* Value Styling */
        .calculated-value {
            font-weight: 700;
            color: #28a745;
            font-size: 1.05rem;
        }

        .deduction-value {
            font-weight: 700;
            color: #dc3545;
            font-size: 1.05rem;
        }

        /* Net Pay Box - Enhanced */
        .net-pay-box {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 32px 24px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }

        .net-pay-box label {
            font-size: 0.9rem;
            opacity: 0.95;
            margin-bottom: 12px;
            font-weight: 500;
            letter-spacing: 0.5px;
        }

        .net-pay-box h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Calculate Button */
        .btn-calculate {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 14px 32px;
            font-weight: 600;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .btn-calculate:hover {
            background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
        }

        .btn-calculate:active {
            transform: translateY(0);
        }

        /* Row Spacing */
        .row {
            margin-left: -10px;
            margin-right: -10px;
        }

        .row>[class*='col-'] {
            padding-left: 10px;
            padding-right: 10px;
        }

        /* Loading Overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            backdrop-filter: blur(4px);
        }

        .loading-spinner {
            background: white;
            padding: 40px 60px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        }

        .loading-spinner p {
            margin-top: 16px;
            font-weight: 500;
            color: #495057;
        }

        /* Summary Section Styling */
        .summary-display {
            padding: 20px;
            background: #f8f9fc;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .summary-display h4 {
            font-size: 1.5rem;
            margin-bottom: 8px;
        }

        /* Divider */
        .divider {
            margin: 28px 0;
        }

        .divider hr {
            border-top: 2px solid #e3e6f0;
            margin: 0;
        }

        /* Submit Buttons Section */
        .submit-section {
            padding: 24px;
            background: #f8f9fc;
            border-radius: 10px;
            margin-top: 20px;
        }

        /* Status Badge in Labels */
        .form-label-sm .badge {
            font-weight: 500;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .salary-form-container {
                padding: 15px 10px;
            }

            .section-card .card-body {
                padding: 20px 16px;
            }

            .section-card .card-header {
                padding: 14px 18px;
                font-size: 0.95rem;
            }

            .net-pay-box {
                padding: 24px 18px;
            }

            .net-pay-box h2 {
                font-size: 2rem;
            }
        }

        /* Field Group Spacing */
        .field-group {
            margin-bottom: 20px;
        }

        /* Auto-spacing for fields in section cards */
        .section-card .card-body>.row>[class*='col-'] {
            margin-bottom: 20px;
        }

        .section-card .card-body>.row>[class*='col-']>label {
            margin-bottom: 8px;
        }

        .section-card .card-body>.row>[class*='col-']>input,
        .section-card .card-body>.row>[class*='col-']>select {
            margin-bottom: 0;
        }

        /* Section Separator */
        .section-separator {
            height: 2px;
            background: linear-gradient(90deg, transparent, #e3e6f0, transparent);
            margin: 30px 0;
        }

        /* Enhanced Input Focus States */
        input[type="number"]:focus,
        input[type="text"]:focus,
        select:focus {
            outline: none;
        }

        /* Select2 Styling */
        .select2-container--default .select2-selection--single {
            height: 42px;
            border-radius: 8px;
            border: 1px solid #ced4da;
            padding: 4px 12px;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 34px;
            padding-left: 0;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px;
        }
    </style>
@endsection

@section('content')
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner">
            <div class="spinner-border text-primary mb-3" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mb-0">Calculating Salary...</p>
        </div>
    </div>

    <div class="salary-form-container">
        <div class="px-1 mb-4">
            <div class="d-flex flex-column flex-lg-row justify-content-lg-between align-items-start align-items-lg-center">
                @include('software.inlcudes.breadcrumb', [
                    'breadcrumbArray' => [
                        ['title' => $page_title, 'url' => route($route . '.index')],
                        [
                            'title' =>
                                isset($edit) && $edit?->id ? 'Edit ' . $page_title : 'Create ' . $page_title,
                            'url' => '',
                        ],
                    ],
                    'route' => $route,
                    'show_back_btn' => true,
                ])
            </div>
        </div>

        <form action="{{ isset($edit) && $edit?->id ? route($route . '.update', [$edit?->id]) : route($route . '.store') }}"
            method="POST" enctype="multipart/form-data" id="salaryCalculationForm">
            @csrf
            @isset($edit)
                @method('PUT')
                <input type="hidden" name="edit_id" value="{{ $edit->id }}">
            @endisset

            {{-- SECTION 1: Employee Selection --}}
            <div class="section-card">
                <div class="card-header">
                    <i class="fa fa-user me-2"></i> Employee & Period Selection
                </div>
                <div class="card-body">
                    <div class="row">
                        @if (!$company_id)
                            <div class="{{ $colums ?? 'col-12' }}">
                                <div class="form-group field-group">
                                    <label class="form-label form-label-sm">Company <span
                                            class="text-danger">*</span></label>
                                    <select id="company_id"
                                        class="form-control select2 search_by_company @error('company_id') is-invalid @enderror"
                                        name="company_id"
                                        data-selectedCompanyId="{{ old('company_id') ?? ($edit->company_id ?? '') }}">
                                        <option value="">Select Company</option>
                                    </select>
                                    @error('company_id')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                        @else
                            <input type="hidden" class="form-control search_by_company" name="company_id"
                                value="{{ $company_id }}" />
                        @endif

                        @if (isset($edit) && $edit?->id)

                            @if ($edit?->branch_id)
                                <div class="col-md-4">
                                    <b>Branch : </b>
                                    {{ $edit->branch_id }}
                                </div>
                            @endif

                            @if ($edit?->department_id)
                                <div class="col-md-4">
                                    <input type="hidden" name="department_id" class="search_by_department"
                                        value="{{ $edit->department_id }}">
                                    <b>Department : </b>
                                    {!! $edit->department?->name ?? 'N/A' !!}
                                </div>
                            @endif

                            @if ($edit?->designation_id)
                                <div class="col-md-4">
                                    <input type="hidden" name="designation_id" value="{{ $edit->designation_id }}">
                                    <b>Designation : </b>
                                    {!! $edit->designation !!}
                                </div>
                            @endif

                            @if ($edit?->employee_id)
                                <div class="col-md-4">
                                    <input type="hidden" name="employee_id" id="employee_id"
                                        value="{{ $edit->employee_id }}">
                                    <b>Employee : </b>
                                    {{ $edit->employee?->employee_code ?? '' }}
                                    {{ $edit->employee?->full_name ?? '' }}
                                </div>
                            @endif
                            <div class="col-md-4">
                                @php
                                    $period = '';
                                    isset($months[$edit?->month]) ? ($period = $months[$edit?->month]) : ($period = '');
                                    isset($edit?->year) ? ($period .= ' ' . $edit?->year) : ($period .= '');
                                @endphp
                                <input type="hidden" name="year" id="year" value="{{ $edit?->year ?? '' }}">
                                <input type="hidden" name="month" id="month" value="{{ $edit?->month ?? '' }}">
                                <b>Period : </b>
                                {{ $period }}
                            </div>
                        @else
                            <div class="{{ $colums ?? 'col-12' }}" id="branch_div">
                                <div class="form-group field-group">
                                    <label class="form-label form-label-sm">Branch <span
                                            class="text-danger">*</span></label>
                                    <select id="branch_id"
                                        class="form-control select2 search_by_branch @error('branch_id') is-invalid @enderror"
                                        name="branch_id"
                                        data-selectedbranchid="{{ old('branch_id') ?? ($edit->branch_id ?? '') }}">
                                        <option value="">Select Branch</option>
                                    </select>
                                    @error('branch_id')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            <div class="{{ $colums ?? 'col-12' }}">
                                <div class="form-group field-group">
                                    <label class="form-label form-label-sm">Department <span
                                            class="text-danger">*</span></label>
                                    <select id="department_id"
                                        class="form-control select2 search_by_department @error('department_id') is-invalid @enderror"
                                        name="department_id"
                                        data-selecteddepartmentid="{{ old('department_id') ?? ($edit->department_id ?? '') }}"
                                        @if (isset($edit) && $edit?->id) disabled @endif>
                                        <option value="">Select Department</option>
                                    </select>
                                    @if (isset($edit) && $edit?->id)
                                        <input type="hidden" name="department_id" value="{{ $edit->department_id }}">
                                    @endif
                                    @error('department_id')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            <div class="{{ $colums ?? 'col-12' }}">
                                <div class="form-group field-group">
                                    <label class="form-label form-label-sm">Employee <span
                                            class="text-danger">*</span></label>
                                    <select id="employee_id"
                                        class="form-control select2 search_by_employee @error('employee_id') is-invalid @enderror"
                                        name="employee_id"
                                        data-selectedEmployeeId="{{ old('employee_id') ?? ($edit->employee_id ?? '') }}"
                                        @if (isset($edit) && $edit?->id) disabled @endif>
                                        <option value="">Select Employee</option>
                                    </select>
                                    @if (isset($edit) && $edit?->id)
                                        <input type="hidden" name="employee_id" value="{{ $edit->employee_id }}">
                                    @endif
                                    @error('employee_id')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Year and Month Filter - Common Component --}}
                            @include('utils.yearMonthFilter', [
                                'currentYear' => $currentYear,
                                'currentMonth' => $currentMonth,
                                'months' => $months,
                                'yearId' => 'year',
                                'monthId' => 'month',
                                'yearName' => 'year',
                                'monthName' => 'month',
                                'yearLabel' => 'Year',
                                'monthLabel' => 'Month',
                                'yearRequired' => true,
                                'monthRequired' => true,
                                'yearSelected' => old('year', $edit?->year ?? $currentYear),
                                'monthSelected' => old('month', $edit?->month ?? $currentMonth),
                                'yearClass' =>
                                    'form-control select2' . ($errors->has('year') ? ' is-invalid' : ''),
                                'monthClass' =>
                                    'form-control select2' . ($errors->has('month') ? ' is-invalid' : ''),
                                'yearColClass' => $colums ?? 'col-12',
                                'monthColClass' => $colums ?? 'col-12',
                                'minYear' => 2020,
                                'maxYear' => date('Y'),
                                'useCustomMonthPicker' => false,
                                'formGroupClass' => 'field-group',
                                'labelClass' => 'form-label-sm',
                            ])

                            {{-- Make Year and Month readonly in edit mode --}}
                            @if (isset($edit) && $edit?->id)
                                <script>
                                    $(document).ready(function() {
                                        // Disable Year and Month selects in edit mode
                                        $('#year, #month').prop('disabled', true).addClass('readonly-field');

                                        // Create hidden inputs to preserve values during form submission
                                        if ($('input[name="year"][type="hidden"]').length === 0) {
                                            $('<input>').attr({
                                                type: 'hidden',
                                                name: 'year',
                                                value: '{{ $edit->year }}'
                                            }).appendTo('#salaryCalculationForm');
                                        }

                                        if ($('input[name="month"][type="hidden"]').length === 0) {
                                            $('<input>').attr({
                                                type: 'hidden',
                                                name: 'month',
                                                value: '{{ $edit->month }}'
                                            }).appendTo('#salaryCalculationForm');
                                        }
                                    });
                                </script>
                            @endif


                            {{-- Error Messages for Year --}}
                            @error('year')
                                <div class="col-12">
                                    <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                                </div>
                            @enderror

                            {{-- Error Messages for Month --}}
                            @error('month')
                                <div class="col-12">
                                    <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                                </div>
                            @enderror
                        @endif
                        @if (request('view_mode') != 1)
                        <div class="col-12 mb-0">
                            <div class="d-flex justify-content-center">
                                <button type="button" class="btn btn-calculate" id="calculateSalaryBtn">
                                    <i class="fa fa-calculator me-2"></i> Calculate Salary
                                </button>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="section-separator"></div>

            {{-- SECTION 2: Attendance & Days --}}
            <div class="section-card">
                <div class="card-header">
                    <i class="fa fa-calendar-check me-2"></i> Attendance & Days Calculation
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="{{ $colums ?? 'col-12' }}">
                            <div class="form-group field-group">
                                <label class="form-label form-label-sm">Total Days in Month</label>
                                <input type="number" step="0.01" name="total_day" id="total_day"
                                    class="form-control readonly-field"
                                    value="{{ old('total_day', $edit?->total_day ?? '') }}" readonly>
                            </div>
                        </div>

                        <div class="{{ $colums ?? 'col-12' }}">
                            <div class="form-group field-group">
                                <label class="form-label form-label-sm">Calculate Days (Payable)</label>
                                <input type="number" step="0.01" name="calculate_days" id="calculate_days"
                                    class="form-control readonly-field"
                                    value="{{ old('calculate_days', $edit?->calculate_days ?? '') }}" readonly>
                            </div>
                        </div>

                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">Total Present Days</label>
                            <input type="number" step="0.01" name="total_present_day" id="total_present_day"
                                class="form-control readonly-field"
                                value="{{ old('total_present_day', $edit?->total_present_day ?? '') }}" readonly>
                        </div>

                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">Half Days</label>
                            <input type="number" step="0.01" name="half_day" id="half_day"
                                class="form-control readonly-field" value="{{ old('half_day', $edit?->half_day ?? '') }}"
                                readonly>
                        </div>

                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">Holidays</label>
                            <input type="number" step="0.01" name="holiday" id="holiday"
                                class="form-control readonly-field" value="{{ old('holiday', $edit?->holiday ?? '') }}"
                                readonly>
                        </div>

                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">Week Off</label>
                            <input type="number" step="0.01" name="total_week_off" id="total_week_off"
                                class="form-control readonly-field"
                                value="{{ old('total_week_off', $edit?->total_week_off ?? '') }}" readonly>
                        </div>

                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">Working Week Off</label>
                            <input type="number" step="0.01" name="working_week_off" id="working_week_off"
                                class="form-control readonly-field"
                                value="{{ old('working_week_off', $edit?->working_week_off ?? '') }}" readonly>
                        </div>


                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">Total Sandwich Leave</label>
                            <input type="number" step="0.01" name="total_sandwich_leave" id="total_sandwich_leave"
                                class="form-control readonly-field"
                                value="{{ old('total_sandwich_leave', $edit?->total_sandwich_leave ?? '') }}" readonly>
                        </div>

                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">Total Leave</label>
                            <input type="number" step="0.01" name="total_leave" id="total_leave"
                                class="form-control readonly-field"
                                value="{{ old('total_leave', $edit?->total_leave ?? '') }}" readonly>
                        </div>

                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">Company Pay Leave</label>
                            <input type="number" step="0.01" name="total_company_pay_leave"
                                id="total_company_pay_leave" class="form-control readonly-field"
                                value="{{ old('total_company_pay_leave', $edit?->total_company_pay_leave ?? '') }}"
                                readonly>
                        </div>

                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">Employee Pay Leave</label>
                            <input type="number" step="0.01" name="total_employee_pay_leave"
                                id="total_employee_pay_leave" class="form-control readonly-field"
                                value="{{ old('total_employee_pay_leave', $edit?->total_employee_pay_leave ?? '') }}"
                                readonly>
                        </div>

                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">Total Absent</label>
                            <input type="number" step="0.01" name="total_absent" id="total_absent"
                                class="form-control readonly-field"
                                value="{{ old('total_absent', $edit?->total_absent ?? '') }}" readonly>
                        </div>

                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">Working Hours</label>
                            <input type="text" name="working_hour" id="working_hour"
                                class="form-control readonly-field"
                                value="{{ old('working_hour', $edit?->working_hour ?? '') }}" readonly>
                        </div>

                        {{-- Manual Adjustment Fields --}}
                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">Adjustment Days <span
                                    class="badge bg-warning text-dark">Manual</span></label>
                            <input type="number" step="0.01" name="adjustment_days" id="adjustment_days"
                                class="form-control manual-field"
                                value="{{ old('adjustment_days', $edit?->adjustment_days ?? 0) }}"
                                placeholder="Enter adjustment days">
                        </div>

                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">Adjustment Remark <span
                                    class="badge bg-warning text-dark">Manual</span></label>
                            <input type="text" name="adjustment_remark" id="adjustment_remark"
                                class="form-control manual-field"
                                value="{{ old('adjustment_remark', $edit?->adjustment_remark ?? '') }}"
                                placeholder="Enter remark">
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-separator"></div>

            {{-- SECTION 3: Salary Details & Allowances --}}
            <div class="section-card">
                <div class="card-header">
                    <i class="fa fa-money-bill me-2"></i> Salary Details & Allowances
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">CTC (Monthly)</label>
                            <input type="number" step="0.01" name="ctc" id="ctc"
                                class="form-control readonly-field" value="{{ old('ctc', $edit?->ctc ?? '') }}" readonly>
                        </div>

                        <div class="{{ $colums ?? 'col-12' }} d-none">
                            <label class="form-label form-label-sm">Given Calculate Salary</label>
                            <input type="number" step="0.01" name="given_calculate_salary"
                                id="given_calculate_salary" class="form-control"
                                value="{{ old('given_calculate_salary', $edit?->given_calculate_salary ?? '') }}">
                        </div>

                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">Salary Calculation Month Count</label>
                            <input type="text" name="salary_calculation_month_count"
                                id="salary_calculation_month_count" class="form-control readonly-field"
                                value="{{ old('salary_calculation_month_count', $edit?->salary_calculation_month_count ?? '') }}"
                                readonly>
                        </div>

                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">Per Day Salary</label>
                            <input type="number" step="0.01" name="per_day_salary" id="per_day_salary"
                                class="form-control readonly-field"
                                value="{{ old('per_day_salary', $edit?->per_day_salary ?? '') }}" readonly>
                        </div>

                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">Conveyance Allowance</label>
                            <input type="number" step="0.01" name="conveyance_allowance" id="conveyance_allowance"
                                class="form-control readonly-field"
                                value="{{ old('conveyance_allowance', $edit?->conveyance_allowance ?? '') }}" readonly>
                        </div>

                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">Medical Allowance</label>
                            <input type="number" step="0.01" name="medical_allowance" id="medical_allowance"
                                class="form-control readonly-field"
                                value="{{ old('medical_allowance', $edit?->medical_allowance ?? '') }}" readonly>
                        </div>

                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">Special Allowance</label>
                            <input type="number" step="0.01" name="special_allowance" id="special_allowance"
                                class="form-control readonly-field"
                                value="{{ old('special_allowance', $edit?->special_allowance ?? '') }}" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-separator"></div>

            {{-- SECTION 4: Day-wise Amounts --}}
            <div class="section-card">
                <div class="card-header">
                    <i class="fa fa-calculator me-2"></i> Day-wise Amount Calculation
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">Present Day Amount</label>
                            <input type="number" step="0.01" name="present_day_amount" id="present_day_amount"
                                class="form-control readonly-field calculated-value"
                                value="{{ old('present_day_amount', $edit?->present_day_amount ?? '') }}" readonly>
                        </div>

                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">Employee Week-off Amount</label>
                            <input type="number" step="0.01" name="employee_weekoff_amount"
                                id="employee_weekoff_amount" class="form-control readonly-field"
                                value="{{ old('employee_weekoff_amount', $edit?->employee_weekoff_amount ?? '') }}"
                                readonly>
                        </div>

                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">Working Week-off Amount</label>
                            <input type="number" step="0.01" name="working_weekoff_amount"
                                id="working_weekoff_amount" class="form-control readonly-field"
                                value="{{ old('working_weekoff_amount', $edit?->working_weekoff_amount ?? '') }}"
                                readonly>
                        </div>

                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">Company Pay Leave Amount</label>
                            <input type="number" step="0.01" name="company_pay_leave_amount"
                                id="company_pay_leave_amount" class="form-control readonly-field"
                                value="{{ old('company_pay_leave_amount', $edit?->company_pay_leave_amount ?? '') }}"
                                readonly>
                        </div>

                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">Employee Pay Leave Amount</label>
                            <input type="number" step="0.01" name="employee_pay_leave_amount"
                                id="employee_pay_leave_amount" class="form-control readonly-field"
                                value="{{ old('employee_pay_leave_amount', $edit?->employee_pay_leave_amount ?? '') }}"
                                readonly>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-separator"></div>

            {{-- SECTION 5: Fixed Salary Structure (FXS) --}}
            <div class="section-card">
                <div class="card-header">
                    <i class="fa fa-building me-2"></i> Fixed Salary Structure (FXS)
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">FXS Basic</label>
                            <input type="number" step="0.01" name="fxs_basic" id="fxs_basic"
                                class="form-control readonly-field"
                                value="{{ old('fxs_basic', $edit?->fxs_basic ?? '') }}" readonly>
                        </div>

                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">FXS HRA</label>
                            <input type="number" step="0.01" name="fxs_hra" id="fxs_hra"
                                class="form-control readonly-field" value="{{ old('fxs_hra', $edit?->fxs_hra ?? '') }}"
                                readonly>
                        </div>

                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">FXS Other (Allowances Sum)</label>
                            <input type="number" step="0.01" name="fxs_other" id="fxs_other"
                                class="form-control readonly-field"
                                value="{{ old('fxs_other', $edit?->fxs_other ?? '') }}" readonly>
                        </div>

                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">FXS Total Earning</label>
                            <input type="number" step="0.01" name="fxs_total_earning" id="fxs_total_earning"
                                class="form-control readonly-field calculated-value"
                                value="{{ old('fxs_total_earning', $edit?->fxs_total_earning ?? '') }}" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-separator"></div>

            {{-- SECTION 6: Daily Wage Salary (DWS) --}}
            <div class="section-card">
                <div class="card-header">
                    <i class="fa fa-clock me-2"></i> Daily Wage Salary (DWS)
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">DWS Basic</label>
                            <input type="number" step="0.01" name="dws_basic" id="dws_basic"
                                class="form-control readonly-field"
                                value="{{ old('dws_basic', $edit?->dws_basic ?? '') }}" readonly>
                        </div>

                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">DWS DA</label>
                            <input type="number" step="0.01" name="dws_da" id="dws_da"
                                class="form-control readonly-field" value="{{ old('dws_da', $edit?->dws_da ?? '') }}"
                                readonly>
                        </div>

                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">DWS Other (Allowances Sum)</label>
                            <input type="number" step="0.01" name="dws_other" id="dws_other"
                                class="form-control readonly-field"
                                value="{{ old('dws_other', $edit?->dws_other ?? '') }}" readonly>
                        </div>

                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">DWS Total Earning</label>
                            <input type="number" step="0.01" name="dws_total_earning" id="dws_total_earning"
                                class="form-control readonly-field calculated-value"
                                value="{{ old('dws_total_earning', $edit?->dws_total_earning ?? '') }}" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-separator"></div>

            {{-- SECTION 7: Overtime & Earnings --}}
            <div class="section-card">
                <div class="card-header">
                    <i class="fa fa-chart-line me-2"></i> Overtime & Additional Earnings
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">Actual Total OT Hours</label>
                            <input type="text" name="actual_total_ot_hours" id="actual_total_ot_hours"
                                class="form-control readonly-field"
                                value="{{ old('actual_total_ot_hours', $edit?->actual_total_ot_hours ?? '') }}" readonly>
                        </div>

                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">Earn OT Hours</label>
                            <input type="text" name="earn_ot_hours" id="earn_ot_hours" class="form-control"
                                value="{{ old('earn_ot_hours', $edit?->earn_ot_hours ?? '') }}">
                        </div>

                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">Earn OT Payable Amount</label>
                            <input type="number" step="0.01" name="earn_ot_payable_amt" id="earn_ot_payable_amt"
                                class="form-control readonly-field"
                                value="{{ old('earn_ot_payable_amt', $edit?->earn_ot_payable_amt ?? '') }}" readonly>
                        </div>

                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">Earn OT Days</label>
                            <input type="number" step="0.01" name="earn_ot_days" id="earn_ot_days"
                                class="form-control readonly-field"
                                value="{{ old('earn_ot_days', $edit?->earn_ot_days ?? '') }}" readonly>
                        </div>

                        {{-- Hidden Shortfall Amount --}}
                        <input type="hidden" id="shortfall_amount" value="0">

                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">Performance Incentive <span
                                    class="badge bg-warning text-dark">Manual</span></label>
                            <input type="number" step="0.01" name="earn_performation_incentive"
                                id="earn_performation_incentive" class="form-control manual-field"
                                value="{{ old('earn_performation_incentive', $edit?->earn_performation_incentive ?? 0) }}"
                                placeholder="Enter performance incentive">
                        </div>

                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">Bonus Amount</label>
                            <input type="number" step="0.01" name="bonus_amount" id="bonus_amount"
                                class="form-control readonly-field"
                                value="{{ old('bonus_amount', $edit?->bonus_amount ?? '') }}" readonly>
                        </div>

                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">Bonus Adjustment <span
                                    class="badge bg-warning text-dark">Manual</span></label>
                            <input type="number" step="0.01" name="bonus_amount_adjustment"
                                id="bonus_amount_adjustment" class="form-control manual-field"
                                value="{{ old('bonus_amount_adjustment', $edit?->bonus_amount_adjustment ?? 0) }}"
                                placeholder="Enter bonus adjustment">
                        </div>

                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">Earn Sub Total</label>
                            <input type="number" step="0.01" name="earn_sub_total" id="earn_sub_total"
                                class="form-control readonly-field calculated-value"
                                value="{{ old('earn_sub_total', $edit?->earn_sub_total ?? '') }}" readonly>
                        </div>

                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">Total Earning</label>
                            <input type="number" step="0.01" name="total_earning" id="total_earning"
                                class="form-control readonly-field calculated-value"
                                value="{{ old('total_earning', $edit?->total_earning ?? '') }}" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-separator"></div>

            {{-- SECTION 8: Deductions --}}
            <div class="section-card">
                <div class="card-header bg-danger text-white">
                    <i class="fa fa-minus-circle me-2"></i> Deductions
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">Employee PF</label>
                            <input type="number" step="0.01" name="ded_employee_pf" id="ded_employee_pf"
                                class="form-control readonly-field deduction-value"
                                value="{{ old('ded_employee_pf', $edit?->ded_employee_pf ?? '') }}" readonly>
                        </div>

                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">Pradhan Mantri PF</label>
                            <input type="number" step="0.01" name="ded_pradhan_mantri_pf" id="ded_pradhan_mantri_pf"
                                class="form-control readonly-field"
                                value="{{ old('ded_pradhan_mantri_pf', $edit?->ded_pradhan_mantri_pf ?? '') }}" readonly>
                        </div>

                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">ESI Employee</label>
                            <input type="number" step="0.01" name="ded_esi_employee" id="ded_esi_employee"
                                class="form-control readonly-field"
                                value="{{ old('ded_esi_employee', $edit?->ded_esi_employee ?? '') }}" readonly>
                        </div>

                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">ESI Company</label>
                            <input type="number" step="0.01" name="ded_esi_company" id="ded_esi_company"
                                class="form-control readonly-field"
                                value="{{ old('ded_esi_company', $edit?->ded_esi_company ?? '') }}" readonly>
                        </div>

                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">PT (Professional Tax)</label>
                            <input type="number" step="0.01" name="ded_pt" id="ded_pt"
                                class="form-control readonly-field" value="{{ old('ded_pt', $edit?->ded_pt ?? '') }}"
                                readonly>
                        </div>

                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">Insurance</label>
                            <input type="number" step="0.01" name="ded_insurance" id="ded_insurance"
                                class="form-control readonly-field"
                                value="{{ old('ded_insurance', $edit?->ded_insurance ?? '') }}" readonly>
                        </div>

                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">TDS</label>
                            <input type="number" step="0.01" name="ded_tds" id="ded_tds"
                                class="form-control readonly-field" value="{{ old('ded_tds', $edit?->ded_tds ?? '') }}"
                                readonly>
                        </div>

                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">TDS Adjustment <span
                                    class="badge bg-warning text-dark">Manual</span></label>
                            <input type="number" step="0.01" name="tds_amount_adjustment" id="tds_amount_adjustment"
                                class="form-control manual-field"
                                value="{{ old('tds_amount_adjustment', $edit?->tds_amount_adjustment ?? 0) }}"
                                placeholder="Enter TDS adjustment">
                        </div>

                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">Welfare Fund</label>
                            <input type="number" step="0.01" name="ded_wf" id="ded_wf"
                                class="form-control readonly-field" value="{{ old('ded_wf', $edit?->ded_wf ?? '') }}"
                                readonly>
                        </div>

                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">Loan Amount</label>
                            <input type="number" step="0.01" name="ded_loan_amount" id="ded_loan_amount"
                                class="form-control readonly-field"
                                value="{{ old('ded_loan_amount', $edit?->ded_loan_amount ?? '') }}" readonly>
                            <span id="loan_info_text" class="badge bg-info mt-1 d-block text-start" style="white-space: normal; {{ isset($edit) && !empty($edit->loan_info) ? '' : 'display: none;' }}">
                                {{ $edit->loan_info ?? '' }}
                            </span>
                        </div>

                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">Loan Adjustment <span
                                    class="badge bg-warning text-dark">Manual</span></label>
                            <input type="number" step="0.01" name="loan_amount_adjustment"
                                id="loan_amount_adjustment" class="form-control manual-field"
                                value="{{ old('loan_amount_adjustment', $edit?->loan_amount_adjustment ?? 0) }}"
                                placeholder="Enter loan adjustment">
                        </div>

                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">Other Deduction <span
                                    class="badge bg-warning text-dark">Manual</span></label>
                            <input type="number" step="0.01" name="ded_other" id="ded_other"
                                class="form-control manual-field" value="{{ old('ded_other', $edit?->ded_other ?? 0) }}"
                                placeholder="Enter other deduction">
                        </div>

                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">Other Deduction Remark <span
                                    class="badge bg-warning text-dark">Manual</span></label>
                            <input type="text" name="ded_other_remark" id="ded_other_remark"
                                class="form-control manual-field"
                                value="{{ old('ded_other_remark', $edit?->ded_other_remark ?? '') }}"
                                placeholder="Enter remark for other deduction">
                        </div>

                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">Advance Amount <span
                                    class="badge bg-warning text-dark">Manual</span></label>
                            <input type="number" step="0.01" name="ded_advance" id="ded_advance"
                                class="form-control manual-field"
                                value="{{ old('ded_advance', $edit?->ded_advance ?? 0) }}"
                                placeholder="Enter advance amount">
                        </div>

                        <div class="{{ $colums ?? 'col-12' }}">
                            <label class="form-label form-label-sm">Total Deduction</label>
                            <input type="number" step="0.01" name="total_deduction" id="total_deduction"
                                class="form-control readonly-field deduction-value"
                                value="{{ old('total_deduction', $edit?->total_deduction ?? '') }}" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-separator"></div>

            {{-- SECTION 9: Net Pay Summary --}}
            <div class="section-card">
                <div class="card-header bg-success text-white">
                    <i class="fa fa-wallet me-2"></i> Net Pay Summary
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6 mb-4 mb-md-0">
                            <div class="row">
                                <div class="col-6 mb-4">
                                    <label class="form-label form-label-sm d-block mb-2">Total Earning</label>
                                    <div class="summary-display">
                                        <h4 class="calculated-value mb-0">₹ <span id="display_total_earning">0.00</span>
                                        </h4>
                                    </div>
                                </div>
                                <div class="col-6 mb-4">
                                    <label class="form-label form-label-sm d-block mb-2">Total Deduction</label>
                                    <div class="summary-display">
                                        <h4 class="deduction-value mb-0">₹ <span id="display_total_deduction">0.00</span>
                                        </h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="net-pay-box">
                                <label class="form-label mb-2">NET BANK PAY</label>
                                <h2>₹ <span id="display_net_bank_pay">0.00</span></h2>
                                <input type="hidden" name="net_bank_pay" id="net_bank_pay"
                                    value="{{ old('net_bank_pay', $edit?->net_bank_pay ?? 0) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-separator"></div>

            {{-- Status and Submit --}}
            <div class="section-card">
                <div class="card-body submit-section">
                    <div class="row mb-4">
                        <div class="divider">
                            <hr />
                        </div>
                        <div class="col-12 text-center pt-3">
                            @if (request('view_mode') == 1)
                                <a href="{{ route($route . '.index') }}" class="btn btn-secondary btn-lg px-5">
                                    <i class="fa fa-arrow-left me-2"></i>Back
                                </a>
                            @else
                                <button type="submit" class="btn btn-success btn-lg px-5 me-3">
                                    <i class="fa fa-save me-2"></i>{{ isset($edit) ? 'Update Salary' : 'Save Salary' }}
                                </button>
                                <a href="{{ route($route . '.index') }}" class="btn btn-danger btn-lg px-5">
                                    <i class="fa fa-times me-2"></i>Cancel
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
        </form>
    </div>
@endsection

@section('page_leavel_script')
    <script src="{{ asset('software/vendor/libs/flatpickr/flatpickr.js') }}"></script>
@endsection

@push('page_scripts')
    @if (!$company_id)
        @include('utils.getCompany')
    @endif
    @include('utils.getDepartment')

    {{-- Custom Branch fetch with single branch auto-select --}}
    <script>
        // Helper: Convert Decimal Hours to HH:MM (e.g., 3.63 -> 03:38)
        function decimalToTime(decimal) {
            if (!decimal && decimal !== 0) return '00:00';
            let hours = Math.floor(decimal);
            let minutes = Math.round((decimal - hours) * 60);

            // Handle edge case where rounding pushes minutes to 60
            if (minutes === 60) {
                hours++;
                minutes = 0;
            }

            return String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0');
        }

        // Helper: Convert HH:MM to Decimal Hours (e.g., 03:38 -> 3.63)
        function timeToDecimal(timeStr) {
            if (!timeStr) return 0;
            if (typeof timeStr !== 'string') return parseFloat(timeStr) || 0; // Fallback if already decimal
            if (!timeStr.includes(':')) return parseFloat(timeStr) || 0;

            const [hours, minutes] = timeStr.split(':').map(Number);
            return (hours || 0) + ((minutes || 0) / 60);
        }

        function fetch_branch_with_auto_select() {
            let company_id = $(".search_by_company").val();
            const instance = $('.search_by_branch');
            const selected_id = instance.data("selectedbranchid") || '';

            if (!company_id && $('meta[name="company_id"]').attr('value')) {
                company_id = $('meta[name="company_id"]').attr('value');
            }

            if (company_id) {
                $.ajax({
                    type: 'POST',
                    url: '{{ env('API_URL') }}get-branch',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        company_id
                    },
                    success: function(response) {
                        if (response.status && Array.isArray(response.data)) {
                            let branches = response.data;

                            // Check if single branch
                            if (branches.length === 1) {
                                // Hide branch dropdown and auto-select the single branch
                                $('#branch_div').hide();
                                let singleBranch = branches[0];
                                let options =
                                    `<option value="${singleBranch.id}" selected>${singleBranch.name}</option>`;
                                instance.html(options);

                                // Trigger change to load departments and employees
                                if (instance.hasClass('select2')) {
                                    instance.select2();
                                }
                                instance.trigger('change');
                            } else if (branches.length > 1) {
                                // Show branch dropdown with all branches
                                $('#branch_div').show();
                                let options = "<option value=''>Select Branch</option>";
                                $.each(branches, function(i, item) {
                                    const selected = selected_id == item.id ? "selected" : "";
                                    options +=
                                        `<option value="${item.id}" ${selected}>${item.name}</option>`;
                                });

                                instance.html(options);
                                if (instance.hasClass('select2')) {
                                    instance.select2();
                                }
                            } else {
                                // No branches - hide the dropdown
                                $('#branch_div').hide();
                                instance.html("<option value=''>No Branch Available</option>");
                            }
                        }
                    },
                    error: function(err) {
                        console.error("Branch fetch failed", err);
                    }
                });
            }
        }

        // Initialize on page load if company_id exists
        $(document).ready(function() {
            @if ($company_id)
                fetch_branch_with_auto_select();
            @endif
        });

        // Trigger on company change
        $(document).on('change', '.search_by_company', function() {
            if ($(".search_by_company").val()) {
                fetch_branch_with_auto_select();
            } else {
                // Reset and show branch div when company is cleared
                $('#branch_div').show();
                $('.search_by_branch').html("<option value=''>Select Branch</option>");
            }
        });
    </script>

    {{-- Custom Employee fetch with department grouping --}}
    <script>
        let employeeFetchXhr = null;

        // Fetch employees grouped by department when company/branch/department changes
        function fetchEmployeesByDepartment() {
            let company_id = $(".search_by_company").val();
            let branch_id = $(".search_by_branch").val();
            let department_id = $(".search_by_department").val();

            const instance = $('.search_by_employee');
            const selected_id = instance.data("selectedemployeeid") || '';

            if (!company_id && $('meta[name="company_id"]').attr('value')) {
                company_id = $('meta[name="company_id"]').attr('value');
            }

            // Abort previous request if it exists
            if (employeeFetchXhr && employeeFetchXhr.readyState !== 4) {
                employeeFetchXhr.abort();
            }

            if (company_id) {
                employeeFetchXhr = $.ajax({
                    type: 'POST',
                    url: '{{ route('salary-calculation.get-employees') }}',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        company_id: company_id,
                        branch_id: branch_id,
                        department_id: department_id
                    },
                    beforeSend: function() {
                        // Optional: Show loading state in dropdown?
                        instance.prop("disabled", true);
                    },
                    complete: function() {
                        instance.prop("disabled", false);
                    },
                    success: function(response) {
                        if (response.status && response.data) {
                            let options = "<option value=''>Select Employee</option>";

                            // Group employees by department
                            $.each(response.data, function(deptName, employees) {
                                if (employees.length > 0) {
                                    options += `<optgroup label="${deptName}">`;
                                    $.each(employees, function(i, emp) {
                                        const selected = selected_id == emp.id ? "selected" :
                                            "";
                                        options +=
                                            `<option value="${emp.id}" ${selected}>${emp.employee_code} - ${emp.full_name}</option>`;
                                    });
                                    options += `</optgroup>`;
                                }
                            });

                            // Destroy Select2 before updating options to ensure clean state
                            if (instance.hasClass('select2-hidden-accessible')) {
                                instance.select2('destroy');
                            }

                            instance.html(options);

                            // Re-initialize Select2
                            instance.select2();
                        }
                    },
                    error: function(err) {
                        if (err.statusText !== 'abort') {
                            console.error("Employee fetch failed", err);
                            // Fallback to default employee fetch
                            fetchEmployeesDefault();
                        }
                    }
                });
            }
        }

        // Fallback default employee fetch
        function fetchEmployeesDefault() {
            let company_id = $(".search_by_company").val();
            let branch_id = $(".search_by_branch").val();

            const instance = $('.search_by_employee');
            const selected_id = instance.data("selectedemployeeid") || '';

            if (!company_id && $('meta[name="company_id"]').attr('value')) {
                company_id = $('meta[name="company_id"]').attr('value');
            }

            if (company_id) {
                $.ajax({
                    type: 'POST',
                    url: '{{ env('API_URL') }}get-employee',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        company_id: company_id,
                        branch_id: branch_id
                    },
                    success: function(response) {
                        if (response.status && response.data) {
                            let options = "<option value=''>Select Employee</option>";
                            $.each(response.data, function(deptName, employees) {
                                // Add optgroup for departments
                                options += '<optgroup label="' + deptName + '">';
                                $.each(employees, function(index, emp) {
                                    const selected = selected_id == emp.id ? "selected" : "";
                                    // Store salary_calculation_month_count in data attribute (handle nulls)
                                    const monthCount = emp.salary_calculation_month_count || '';
                                    options +=
                                        `<option value="${emp.id}" ${selected} data-month-count="${monthCount}">${emp.employee_code} - ${emp.full_name}</option>`;
                                });
                                options += '</optgroup>';
                            });

                            instance.html(options);
                            if (instance.hasClass('select2')) {
                                instance.select2();
                            }

                            // Trigger change to populate if default selected
                            if (selected_id) {
                                $('#employee_id').trigger('change');
                            }
                        }
                    },
                    error: function(err) {
                        console.error("Employee fetch failed", err);
                    }
                });
            }
        }

        // Initialize on page load
        $(document).ready(function() {
            @if ($company_id)
                // Wait for branch to be loaded/auto-selected first
                setTimeout(function() {
                    fetchEmployeesByDepartment();
                }, 800);
            @endif

            // Bind change event to populate month count immediately
            $('#employee_id').on('change', function() {
                var selectedOption = $(this).find('option:selected');
                var monthCount = selectedOption.attr('data-month-count');
                $('#salary_calculation_month_count').val(monthCount || '');
            });
        });

        // Trigger on company change - employees will be fetched after branch is loaded
        $(document).on('change', '.search_by_company', function() {
            // Branch fetch will trigger employee fetch via branch change event
        });

        // Trigger on branch change
        $(document).on('change', '.search_by_branch', function() {
            if ($(".search_by_branch").val()) {
                fetchEmployeesByDepartment();
            }
        });

        // Trigger on department change
        $(document).on('change', '.search_by_department', function() {
            fetchEmployeesByDepartment();
        });
    </script>

    <script type="text/javascript">
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
        });

        // Calculate Salary Button Click
        $('#calculateSalaryBtn').on('click', function() {
            calculateSalary();
        });

        function calculateSalary() {
            let companyId = $(".search_by_company").val();
            let branchId = $(".search_by_branch").val();
            let departmentId = $(".search_by_department").val();
            let employeeId = $("#employee_id").val();
            let year = $("#year").val();
            let month = $("#month").val();

            // Validation
            if (!companyId) {
                toastr.error('Please select a Company.');
                return;
            }
            // Require branchId if the company supports multiple branches
            @if (count(Company::find($company_id)?->branches ?? []) > 1)
                if (!branchId) {
                    toastr.error('Please select a Branch.');
                    return;
                }
            @endif
            // departmentId is optional - no validation needed here
            if (!employeeId) {
                toastr.error('Please select an Employee.');
                return;
            }
            if (!year) {
                toastr.error('Please select a Year.');
                return;
            }
            if (!month) {
                toastr.error('Please select a Month.');
                return;
            }

            // Show loading
            $('#loadingOverlay').css('display', 'flex');

            $.ajax({
                url: '{{ route('salary-calculation.calculate') }}',
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                data: {
                    _token: getCsrfToken(),
                    company_id: companyId,
                    branch_id: branchId,
                    department_id: departmentId,
                    employee_id: employeeId,
                    year: year,
                    month: month
                },
                success: function(response) {
                    $('#loadingOverlay').hide();

                    // Handle API response format (status/data) or legacy format (success/data)
                    if ((response.status && response.data) || (response.success && response.data)) {
                        const salaryData = response.data;
                        populateForm(salaryData);
                        toastr.success(response.message || 'Salary calculated successfully!');
                    } else {
                        toastr.error(response.message || response.error || 'Failed to calculate salary');
                    }
                },
                error: function(xhr) {
                    $('#loadingOverlay').hide();
                    let message = 'Error calculating salary';
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        } else if (xhr.responseJSON.error) {
                            message = xhr.responseJSON.error;
                        }
                    }
                    toastr.error(message);
                }
            });
        }

        function populateForm(data) {
            // Store base values for recalculation (before adjustment_days are applied)
            $('#calculate_days').data('base-val', data.calculate_days || 0);
            $('#dws_total_earning').data('base-val', data.dws_total_earning || 0);

            // Days calculation
            $('#total_day').val(data.total_day || 0);
            $('#calculate_days').val(data.calculate_days || 0);
            $('#total_present_day').val(data.total_present_day || 0);
            $('#half_day').val(data.half_day || 0);
            $('#holiday').val(data.holiday || 0);
            $('#total_week_off').val(data.total_week_off || 0);
            $('#working_week_off').val(data.working_week_off || 0);
            $('#total_sandwich_leave').val(data.total_sandwich_leave || 0);
            $('#total_leave').val(data.total_leave || 0);
            $('#total_company_pay_leave').val(data.total_company_pay_leave || 0);
            $('#total_employee_pay_leave').val(data.total_employee_pay_leave || 0);
            $('#total_absent').val(data.total_absent || 0);
            $('#working_hour').val(data.working_hour || '00:00:00');

            // Salary details
            $('#ctc').val(data.ctc || 0);
            $('#salary_calculation_month_count').val(data.salary_calculation_month_count || '');
            $('#per_day_salary').val(data.per_day_salary || 0);
            $('#conveyance_allowance').val(data.conveyance_allowance || 0);
            $('#medical_allowance').val(data.medical_allowance || 0);
            $('#special_allowance').val(data.special_allowance || 0);

            // Amount calculations
            $('#present_day_amount').val(data.present_day_amount || 0);
            $('#employee_weekoff_amount').val(data.employee_weekoff_amount || 0);
            $('#working_weekoff_amount').val(data.working_weekoff_amount || 0);
            $('#company_pay_leave_amount').val(data.company_pay_leave_amount || 0);
            $('#employee_pay_leave_amount').val(data.employee_pay_leave_amount || 0);

            // FXS
            $('#fxs_basic').val(data.fxs_basic || 0);
            $('#fxs_hra').val(data.fxs_hra || 0);
            $('#fxs_other').val(data.fxs_other || 0);
            $('#fxs_total_earning').val(data.fxs_total_earning || 0);

            // DWS
            $('#dws_basic').val(data.dws_basic || 0);
            $('#dws_da').val(data.dws_da || 0);
            $('#dws_other').val(data.dws_other || 0);
            $('#dws_total_earning').val(data.dws_total_earning || 0);

            // OT
            $('#actual_total_ot_hours').val(decimalToTime(data.actual_total_ot_hours || 0));
            $('#earn_ot_hours').val(decimalToTime(data.earn_ot_hours || 0));
            $('#earn_ot_payable_amt').val(data.earn_ot_payable_amt || 0);
            $('#earn_ot_days').val(data.earn_ot_days || 0);
            $('#shortfall_amount').val(data.shortfall_amount || 0);

            // Bonus
            $('#bonus_amount').val(data.bonus_amount || 0);

            // Earnings
            $('#earn_sub_total').val(data.earn_sub_total || 0);
            $('#total_earning').val(data.total_earning || 0);

            // Deductions
            // Store PF/ESI configuration as data attributes
            $('#ded_employee_pf').data('pf-enabled', data.pf_enabled);
            $('#ded_employee_pf').data('pf-percentage', data.pf_percentage);
            $('#ded_pradhan_mantri_pf').data('pm-pf-enabled', data.pradhanmantri_pf_enabled);
            $('#ded_pradhan_mantri_pf').data('pm-pf-percentage', data.pradhanmantri_pf_percentage);
            $('#ded_esi_employee').data('esi-enabled', data.esi_employee_enabled);
            $('#ded_esi_employee').data('esi-percentage', data.esi_employee_percentage);
            $('#ded_esi_employee').data('esi-company-enabled', data.esi_company_enabled);
            $('#ded_esi_employee').data('esi-company-percentage', data.esi_company_percentage);

            $('#ded_employee_pf').val(data.ded_employee_pf || 0);
            $('#ded_pradhan_mantri_pf').val(data.ded_pradhan_mantri_pf || 0);
            $('#ded_esi_employee').val(data.ded_esi_employee || 0);
            $('#ded_esi_company').val(data.ded_esi_company || 0);
            $('#ded_pt').val(data.ded_pt || 0);
            $('#ded_insurance').val(data.ded_insurance || 0);
            $('#ded_tds').val(data.ded_tds || 0);
            $('#ded_wf').val(data.ded_wf || 0);
            $('#ded_loan_amount').val(data.ded_loan_amount || 0);
            $('#loan_amount_adjustment').val(data.loan_amount_adjustment || 0);
            if (data.loan_info) {
                $('#loan_info_text').text(data.loan_info).show();
            } else {
                $('#loan_info_text').hide();
            }
            $('#ded_other').val(data.ded_other || 0);
            $('#ded_other_remark').val(data.ded_other_remark || '');

            // Advance Amount
            $('#ded_advance').val(data.ded_advance || 0);

            $('#total_deduction').val(data.total_deduction || 0);

            // Net Pay
            $('#net_bank_pay').val(data.net_bank_pay || 0);

            // Apply recalculation based on manual inputs immediately
            recalculateTotals();
        }

        function updateDisplayValues(data) {
            $('#display_total_earning').text(formatCurrency(data.total_earning || 0));
            $('#display_total_deduction').text(formatCurrency(data.total_deduction || 0));
            $('#display_net_bank_pay').text(formatCurrency(data.net_bank_pay || 0));
        }

        function formatCurrency(value) {
            return parseFloat(value || 0).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        }

        // Recalculate totals when manual fields change
        $(document).on('input', '.manual-field', function() {
            recalculateTotals();
        });

        // Recalculate OT Amount when Hours change (HH:MM input)
        $(document).on('change', '#earn_ot_hours', function() {
            let timeStr = $(this).val();
            let hours = timeToDecimal(timeStr);
            let perDaySalary = parseFloat($('#per_day_salary').val() || 0);

            // Determine working hours (default 8 if invalid or missing)
            let workingHourStr = $('#working_hour').val();
            let dailyHours = timeToDecimal(workingHourStr);
            if (dailyHours === 0) dailyHours = 8;

            let hourlyRate = perDaySalary / dailyHours;
            let otAmount = hours * hourlyRate;

            $('#earn_ot_payable_amt').val(otAmount.toFixed(2));
            recalculateTotals();
        });

        function recalculateTotals() {
            let adjDays = parseFloat($('#adjustment_days').val() || 0);
            let perDay = parseFloat($('#per_day_salary').val() || 0);
            let adjAmount = adjDays * perDay;

            // Get or initialize base values
            let baseCalculateDays = parseFloat($('#calculate_days').data('base-val'));
            if (isNaN(baseCalculateDays)) {
                let currentCalcDays = parseFloat($('#calculate_days').val() || 0);
                baseCalculateDays = currentCalcDays - adjDays;
                $('#calculate_days').data('base-val', baseCalculateDays);
            }

            let baseDwsTotalEarning = parseFloat($('#dws_total_earning').data('base-val'));
            if (isNaN(baseDwsTotalEarning)) {
                let currentDws = parseFloat($('#dws_total_earning').val() || 0);
                baseDwsTotalEarning = currentDws - adjAmount;
                $('#dws_total_earning').data('base-val', baseDwsTotalEarning);
            }

            // Update calculate_days input dynamically
            let newCalculateDays = baseCalculateDays + adjDays;
            $('#calculate_days').val(newCalculateDays.toFixed(2));

            // Update DWS total earning dynamically
            let newDwsTotalEarning = baseDwsTotalEarning + adjAmount;
            $('#dws_total_earning').val(newDwsTotalEarning.toFixed(2));

            // Get current values
            let earnSubTotal = parseFloat($('#earn_ot_payable_amt').val() || 0) +
                parseFloat($('#earn_performation_incentive').val() || 0) +
                parseFloat($('#bonus_amount').val() || 0) +
                parseFloat($('#bonus_amount_adjustment').val() || 0);

            let shortfallAmount = parseFloat($('#shortfall_amount').val() || 0);
            let totalEarning = newDwsTotalEarning + earnSubTotal;

            // Recalculate PF dynamically based on new DWS total earning
            let pfEnabled = $('#ded_employee_pf').data('pf-enabled');
            let pfPercentage = parseFloat($('#ded_employee_pf').data('pf-percentage') || 12);
            let pmPfEnabled = $('#ded_pradhan_mantri_pf').data('pm-pf-enabled');
            let pmPfPercentage = parseFloat($('#ded_pradhan_mantri_pf').data('pm-pf-percentage') || 0);

            let dedEmployeePf = 0;
            let dedPradhanMantriPf = 0;

            if (pfEnabled === true || pfEnabled === 'true' || pfEnabled === 1 || pfEnabled === '1') {
                let calculatedPf = (newDwsTotalEarning * pfPercentage) / 100;
                dedEmployeePf = calculatedPf > 1800 ? 1800 : calculatedPf;
            }
            $('#ded_employee_pf').val(dedEmployeePf.toFixed(2));

            if (pmPfEnabled === true || pmPfEnabled === 'true' || pmPfEnabled === 1 || pmPfEnabled === '1') {
                dedPradhanMantriPf = (newDwsTotalEarning * pmPfPercentage) / 100;
            }
            $('#ded_pradhan_mantri_pf').val(dedPradhanMantriPf.toFixed(2));

            // Recalculate ESI dynamically based on new total earning
            let esiEnabled = $('#ded_esi_employee').data('esi-enabled');
            let esiPercentage = parseFloat($('#ded_esi_employee').data('esi-percentage') || 0.75);
            let esiCompanyEnabled = $('#ded_esi_employee').data('esi-company-enabled');
            let esiCompanyPercentage = parseFloat($('#ded_esi_employee').data('esi-company-percentage') || 3.25);

            let dedEsiEmployee = 0;
            let dedEsiCompany = 0;

            if (esiEnabled === true || esiEnabled === 'true' || esiEnabled === 1 || esiEnabled === '1') {
                dedEsiEmployee = (totalEarning * esiPercentage) / 100;
            }
            $('#ded_esi_employee').val(dedEsiEmployee.toFixed(2));

            if (esiCompanyEnabled === true || esiCompanyEnabled === 'true' || esiCompanyEnabled === 1 || esiCompanyEnabled === '1') {
                dedEsiCompany = (totalEarning * esiCompanyPercentage) / 100;
            }
            $('#ded_esi_company').val(dedEsiCompany.toFixed(2));

            // Calculate total deductions including updated PF and ESI
            let totalDeduction = dedEmployeePf +
                dedPradhanMantriPf +
                dedEsiEmployee +
                parseFloat($('#ded_pt').val() || 0) +
                parseFloat($('#ded_insurance').val() || 0) +
                parseFloat($('#ded_tds').val() || 0) +
                parseFloat($('#tds_amount_adjustment').val() || 0) +
                parseFloat($('#ded_wf').val() || 0) +
                parseFloat($('#ded_loan_amount').val() || 0) +
                parseFloat($('#loan_amount_adjustment').val() || 0) +
                parseFloat($('#ded_advance').val() || 0) +
                parseFloat($('#ded_other').val() || 0);

            // Rounding (Nearest Rupee) for summary values
            let totalEarningRounded = Math.round(totalEarning);
            let totalDeductionRounded = Math.round(totalDeduction);
            let netBankPayRounded = totalEarningRounded - totalDeductionRounded;

            // Update fields
            $('#earn_sub_total').val(earnSubTotal.toFixed(2));
            $('#total_earning').val(totalEarningRounded.toFixed(2));
            $('#total_deduction').val(totalDeductionRounded.toFixed(2));
            $('#net_bank_pay').val(netBankPayRounded.toFixed(2));

            // Update display
            $('#display_total_earning').text(formatCurrency(totalEarningRounded));
            $('#display_total_deduction').text(formatCurrency(totalDeductionRounded));
            $('#display_net_bank_pay').text(formatCurrency(netBankPayRounded));
        }

        // Load data if editing
        @if (isset($edit) && $edit?->id)
            $(document).ready(function() {
                let data = {
                    total_day: {{ $edit->total_day ?? 0 }},
                    calculate_days: {{ $edit->calculate_days ?? 0 }},
                    total_present_day: {{ $edit->total_present_day ?? 0 }},
                    half_day: {{ $edit->half_day ?? 0 }},
                    holiday: {{ $edit->holiday ?? 0 }},
                    total_week_off: {{ $edit->total_week_off ?? 0 }},
                    total_sandwich_leave: {{ $edit->total_sandwich_leave ?? 0 }},
                    total_leave: {{ $edit->total_leave ?? 0 }},
                    total_company_pay_leave: {{ $edit->total_company_pay_leave ?? 0 }},
                    total_employee_pay_leave: {{ $edit->total_employee_pay_leave ?? 0 }},
                    total_absent: {{ $edit->total_absent ?? 0 }},
                    ctc: {{ $edit->ctc ?? 0 }},
                    per_day_salary: {{ $edit->per_day_salary ?? 0 }},
                    total_earning: {{ $edit->total_earning ?? 0 }},
                    total_deduction: {{ $edit->total_deduction ?? 0 }},
                    net_bank_pay: {{ $edit->net_bank_pay ?? 0 }},

                    pf_enabled: {{ $edit->pf_enabled ? 'true' : 'false' }},
                    pf_percentage: {{ $edit->pf_percentage ?? 12 }},
                    pradhanmantri_pf_enabled: {{ $edit->pradhanmantri_pf_enabled ? 'true' : 'false' }},
                    pradhanmantri_pf_percentage: {{ $edit->pradhanmantri_pf_percentage ?? 0 }},
                    esi_employee_enabled: {{ $edit->esi_employee_enabled ? 'true' : 'false' }},
                    esi_employee_percentage: {{ $edit->esi_employee_percentage ?? 0.75 }},
                    esi_company_enabled: {{ $edit->esi_company_enabled ? 'true' : 'false' }},
                    esi_company_percentage: {{ $edit->esi_company_percentage ?? 3.25 }}
                };

                // Store base values by subtracting saved adjustment_days
                let adjDays = parseFloat($('#adjustment_days').val() || 0);
                let perDay = parseFloat($('#per_day_salary').val() || 0);

                $('#calculate_days').data('base-val', (data.calculate_days || 0) - adjDays);
                $('#dws_total_earning').data('base-val', (parseFloat($('#dws_total_earning').val() || 0) - (adjDays * perDay)));

                // Store PF/ESI configuration as data attributes
                $('#ded_employee_pf').data('pf-enabled', data.pf_enabled);
                $('#ded_employee_pf').data('pf-percentage', data.pf_percentage);
                $('#ded_pradhan_mantri_pf').data('pm-pf-enabled', data.pradhanmantri_pf_enabled);
                $('#ded_pradhan_mantri_pf').data('pm-pf-percentage', data.pradhanmantri_pf_percentage);
                $('#ded_esi_employee').data('esi-enabled', data.esi_employee_enabled);
                $('#ded_esi_employee').data('esi-percentage', data.esi_employee_percentage);
                $('#ded_esi_employee').data('esi-company-enabled', data.esi_company_enabled);
                $('#ded_esi_employee').data('esi-company-percentage', data.esi_company_percentage);

                updateDisplayValues(data);
            });
        @endif

        @if (request('view_mode') == 1)
            $(document).ready(function() {
                // Disable all inputs, selects, and textareas
                $('input, select, textarea').prop('disabled', true);
                
                // Keep the back/cancel button clickable
                $('.submit-section a').prop('disabled', false);

                // Hide the Calculate Salary button
                $('#calculateSalaryBtn').hide();

                // Hide the save/update button
                $('.submit-section button[type="submit"]').hide();

                // Change cancel button to back
                let cancelBtn = $('.submit-section a.btn-danger');
                if (cancelBtn.length) {
                    cancelBtn.html('<i class="fa fa-arrow-left me-2"></i>Back')
                        .removeClass('btn-danger')
                        .addClass('btn-secondary');
                }

                @if (isset($edit) && $edit?->id)
                // Auto-fetch fresh attendance & salary data for view mode
                // First try full calculate, if that fails (no salary detail), use attendance-summary
                var viewModeData = {
                    _token: '{{ csrf_token() }}',
                    company_id: '{{ $edit->company_id }}',
                    branch_id: '{{ $edit->branch_id ?? "" }}',
                    department_id: '{{ $edit->department_id ?? "" }}',
                    employee_id: '{{ $edit->employee_id }}',
                    year: '{{ $edit->year }}',
                    month: '{{ $edit->month }}'
                };

                $.ajax({
                    url: '{{ route("salary-calculation.calculate") }}',
                    type: "POST",
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: viewModeData,
                    success: function(response) {
                        if ((response.status && response.data) || (response.success && response.data)) {
                            populateForm(response.data);
                            updateDisplayValues(response.data);
                        }
                        // Re-disable all inputs after populating
                        $('input, select, textarea').prop('disabled', true);
                        $('.submit-section a').prop('disabled', false);
                    },
                    error: function(xhr) {
                        // Fallback: Use attendance-summary endpoint (works without salary detail)
                        $.ajax({
                            url: '{{ route("salary-calculation.attendance-summary") }}',
                            type: "POST",
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            data: viewModeData,
                            success: function(res) {
                                if (res.success && res.data) {
                                    var d = res.data;
                                    $('#total_day').val(d.total_day || 0);
                                    $('#calculate_days').val(d.calculate_days || 0);
                                    $('#total_present_day').val(d.total_present_day || 0);
                                    $('#half_day').val(d.half_day || 0);
                                    $('#holiday').val(d.holiday || 0);
                                    $('#total_week_off').val(d.total_week_off || 0);
                                    $('#working_week_off').val(d.working_week_off || 0);
                                    $('#total_sandwich_leave').val(d.total_sandwich_leave || 0);
                                    $('#total_leave').val(d.total_leave || 0);
                                    $('#total_company_pay_leave').val(d.total_company_pay_leave || 0);
                                    $('#total_employee_pay_leave').val(d.total_employee_pay_leave || 0);
                                    $('#total_absent').val(d.total_absent || 0);
                                    $('#working_hour').val(d.working_hour || '00:00:00');
                                }
                                // Re-disable all inputs after populating
                                $('input, select, textarea').prop('disabled', true);
                                $('.submit-section a').prop('disabled', false);
                            },
                            error: function(xhr2) {
                                console.log('Attendance summary also failed', xhr2);
                            }
                        });
                    }
                });
                @endif
            });
        @endif
    </script>
@endpush
