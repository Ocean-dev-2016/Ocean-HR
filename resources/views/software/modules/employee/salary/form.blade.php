@extends('software.layout.app')

@php
    use App\Models\Company;
    $page_title = isset($modules['title']) ? $modules['title'] : null;
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $i = 0;
    $company_id = isset($modules['company_id']) ? $modules['company_id'] : null;

    $colums = 'col-md-3 col-sm-12 mb-2';

@endphp
@section('title', $page_title)
@section('page_leavel_style')
    <link rel="stylesheet" href="{{ asset('software/vendor/libs/flatpickr/flatpickr.css') }}" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
@endsection

@section('content')
    <div class="px-1">
        <div class="d-flex flex-column flex-lg-row justify-content-lg-between align-items-start align-items-lg-center">
            @include('software.inlcudes.breadcrumb', [
                'breadcrumbArray' => [
                    ['title' => $page_title, 'url' => route($route . '.index')],
                    [
                        'title' => isset($edit) && $edit?->id ? 'Edit ' . $page_title : 'Create ' . $page_title,
                        'url' => '',
                    ],
                ],
                'route' => $route,
                'show_back_btn' => true,
            ])
        </div>
    </div>

    <div class="card my-3 mb-4">
        {{-- <h5 class="card-header"></h5> --}}
        <div class="card-body">
            <form
                action="{{ isset($edit) && $edit?->id ? route($route . '.update', [$edit?->id]) : route($route . '.store') }}"
                method="POST" enctype="multipart/form-data">
                @csrf
                @isset($edit)
                    @method('PUT')
                    <input type="hidden" name="edit_id" value="{{ $edit->id }}">
                @endisset
                <div class="row">
                    @if (!$company_id)
                        <div class="{{ $colums ?? 'col-12' }}">
                            <div class="form-group">
                                <label class="form-label">Select Company <span class="text-danger">*</span></label>
                                <select id="company_id"
                                    class="form-control select2 search_by_company @error('company_id') is-invalid @enderror"
                                    name="company_id"
                                    data-selectedCompanyId="{{ old('company_id') ?? ($edit->company_id ?? '') }}"
                                    onchange="getAttendanceReport()">
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

                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Select Branch <span class="text-danger">*</span></label>
                            <select id="branch_id"
                                class="form-control select2 search_by_branch @error('branch_id') is-invalid @enderror"
                                name="branch_id" data-selectedbranchid="{{ old('branch_id') ?? ($edit->branch_id ?? '') }}"
                                onchange="getAttendanceReport()">
                                <option value="">Select Branch</option>
                            </select>

                            @error('branch_id')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Select Department <span class="text-danger">*</span></label>
                            <select id="department_id"
                                class="form-control select2 search_by_department @error('department_id') is-invalid @enderror"
                                name="department_id"
                                data-selecteddepartmentid="{{ old('department_id') ?? ($edit->department_id ?? '') }}"
                                onchange="getAttendanceReport()">
                                <option value="">Select Department</option>
                            </select>

                            @error('department_id')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Select Employee <span class="text-danger">*</span></label>
                            <select id="employee_id"
                                class="form-control select2 search_by_employee @error('employee_id') is-invalid @enderror"
                                name="employee_id"
                                data-selectedEmployeeId="{{ old('employee_id') ?? ($edit->employee_id ?? '') }}"
                                onchange="getAttendanceReport()">
                                <option value="">Select Employee</option>
                            </select>

                            @error('employee_id')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Year --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Year</label>
                            <select name="year" class="form-control select2 @error('year') is-invalid @enderror"
                                onchange="getAttendanceReport()" required>
                                <option value="">Select Year</option>
                                @for ($y = date('Y'); $y >= 2020; $y--)
                                    <option value="{{ $y }}"
                                        {{ old('year', $edit?->year ?? '') == $y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endfor
                            </select>
                            @error('year')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Month --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Month</label>
                            <select name="month" class="form-control select2 @error('month') is-invalid @enderror"
                                onchange="getAttendanceReport()" required>
                                <option value="">Select Month</option>
                                @foreach ([
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
        ] as $num => $name)
                                    <option value="{{ $num }}"
                                        {{ old('month', $edit?->month ?? '') == $num ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('month')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Added Date --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label"> Added Date </label>
                            <input id="added_date" type="text"
                                class="form-control @error('added_date') is-invalid @enderror" name="added_date"
                                value="{{ isset($edit) && $edit?->added_date ? $edit?->added_date : old('added_date') }}"
                                placeholder="Enter Added Date">
                            @error('added_date')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Calculate Days --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Calculate Days</label>
                            <input type="number" step="0.01" name="calculate_days"
                                class="form-control @error('calculate_days') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->calculate_days ? $edit?->calculate_days : old('calculate_days') }}"placeholder="Enter Calculate Days"
                                readonly>
                            @error('calculate_days')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Total Present Days --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Total Present Days</label>
                            <input type="number" step="0.01" name="total_present_days"
                                class="form-control @error('total_present_days') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->total_present_days ? $edit?->total_present_days : old('total_present_days') }}"
                                placeholder="Enter Total Present Days" readonly>
                            @error('total_present_days')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Adjustment Days --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Adjustment Days</label>
                            <input type="number" step="0.01" name="adjustment_days"
                                class="form-control @error('adjustment_days') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->adjustment_days ? $edit?->adjustment_days : old('adjustment_days') }}"
                                placeholder="Enter Adjustment Days">
                            @error('adjustment_days')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Half Day --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Half Day</label>
                            <input type="text" name="half_day"
                                class="form-control @error('half_day') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->half_day ? $edit?->half_day : old('half_day') }}"
                                placeholder="Enter Half Day" readonly>
                            @error('half_day')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Holiday --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Holiday</label>
                            <input type="text" name="holiday"
                                class="form-control @error('holiday') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->holiday ? $edit?->holiday : old('holiday') }}"
                                placeholder="Enter Holiday" readonly>
                            @error('holiday')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Compansion Hour
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Compansion Hour</label>
                            <input type="number" step="0.01" name="compansion_hour"
                                class="form-control @error('compansion_hour') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->compansion_hour ? $edit?->compansion_hour : old('compansion_hour') }}"placeholder="Enter Compansion Hour">
                            @error('compansion_hour')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div> --}}

                    {{-- Compansion Day
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Compansion Day</label>
                            <input type="number" step="0.01" name="compansion_day"
                                class="form-control @error('compansion_day') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->compansion_day ? $edit?->compansion_day : old('compansion_day') }}"placeholder="Enter Compansion Day">
                            @error('compansion_day')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div> --}}

                    {{-- Total Week Off --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Total Week Off</label>
                            <input type="number" step="0.01" name="total_week_off"
                                class="form-control @error('total_week_off') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->total_week_off ? $edit?->total_week_off : old('total_week_off') }}"placeholder="Enter Total Week Off">
                            @error('total_week_off')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Total Sandwich Leave --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Total Sandwich Leave</label>
                            <input type="number" step="0.01" name="total_sandwich_leave"
                                class="form-control @error('total_sandwich_leave') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->total_sandwich_leave ? $edit?->total_sandwich_leave : old('total_sandwich_leave') }}"placeholder="Enter Total Sandwich Leave"
                                readonly>
                            @error('total_sandwich_leave')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Total Leave --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Total Leave</label>
                            <input type="number" step="0.01" name="total_leave"
                                class="form-control @error('total_leave') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->total_leave ? $edit?->total_leave : old('total_leave') }}"placeholder="Enter Total Leave"
                                readonly>
                            @error('total_leave')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Total Company Pay Leave --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Total Company Pay Leave</label>
                            <input type="number" step="0.01" name="total_company_pay_leave"
                                class="form-control @error('total_company_pay_leave') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->total_company_pay_leave ? $edit?->total_company_pay_leave : old('total_company_pay_leave') }}"placeholder="Enter Total Company Pay Leave">
                            @error('total_company_pay_leave')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Total Employee Pay Leave --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Total Employee Pay Leave</label>
                            <input type="number" step="0.01" name="total_employee_pay_leave"
                                class="form-control @error('total_employee_pay_leave') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->total_employee_pay_leave ? $edit?->total_employee_pay_leave : old('total_employee_pay_leave') }}"placeholder="Enter Total Employee Pay Leave">
                            @error('total_employee_pay_leave')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Total Absent --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Total Absent</label>
                            <input type="number" step="0.01" name="total_absent"
                                class="form-control @error('total_absent') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->total_absent ? $edit?->total_absent : old('total_absent') }}"placeholder="Enter Total Absent"
                                readonly>
                            @error('total_absent')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Total Day --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Total Day</label>
                            <input type="number" step="0.01" name="total_day"
                                class="form-control @error('total_day') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->total_day ? $edit?->total_day : old('total_day') }}"placeholder="Enter Total Day"
                                readonly>
                            @error('total_day')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Working hour --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Working hour</label>
                            <input type="number" step="0.01" name="working_hour"
                                class="form-control @error('working_hour') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->working_hour ? $edit?->working_hour : old('working_hour') }}"placeholder="Enter Working hour" readonly>
                            @error('working_hour')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- CTC --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">CTC</label>
                            <input type="number" step="0.01" name="ctc"
                                class="form-control @error('ctc') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->ctc ? $edit?->ctc : old('ctc') }}"placeholder="Enter CTC">
                            @error('ctc')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Gross Salary --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Gross Salary</label>
                            <input type="number" step="0.01" name="gross_salary"
                                class="form-control @error('gross_salary') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->gross_salary ? $edit?->gross_salary : old('gross_salary') }}"placeholder="Enter Gross Salary">
                            @error('gross_salary')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Given Calculate Salary --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Given Calculate Salary</label>
                            <input type="number" step="0.01" name="given_calculate_salary"
                                class="form-control @error('given_calculate_salary') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->given_calculate_salary ? $edit?->given_calculate_salary : old('given_calculate_salary') }}"placeholder="Enter Given Calculate Salary">
                            @error('given_calculate_salary')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Per Day Salary --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Per Day Salary</label>
                            <input type="number" step="0.01" name="per_day_salary"
                                class="form-control @error('per_day_salary') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->per_day_salary ? $edit?->per_day_salary : old('per_day_salary') }}"placeholder="Enter Per Day Salary">
                            @error('per_day_salary')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Conveyance Allowance --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Conveyance Allowance</label>
                            <input type="number" step="0.01" name="conveyance_allowance"
                                class="form-control @error('conveyance_allowance') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->conveyance_allowance ? $edit?->conveyance_allowance : old('conveyance_allowance') }}"placeholder="Enter Conveyance Allowance">
                            @error('conveyance_allowance')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Medical Allowance --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Medical Allowance</label>
                            <input type="number" step="0.01" name="medical_allowance"
                                class="form-control @error('medical_allowance') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->medical_allowance ? $edit?->medical_allowance : old('medical_allowance') }}"placeholder="Enter Medical Allowance">
                            @error('medical_allowance')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Special Allowance --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Special Allowance</label>
                            <input type="number" step="0.01" name="special_allowance"
                                class="form-control @error('special_allowance') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->special_allowance ? $edit?->special_allowance : old('special_allowance') }}"placeholder="Enter Special Allowance">
                            @error('special_allowance')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Present Day Amount --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Present Day Amount</label>
                            <input type="number" step="0.01" name="present_day_amount"
                                class="form-control @error('present_day_amount') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->present_day_amount ? $edit?->present_day_amount : old('present_day_amount') }}"placeholder="Enter Present Day Amount">
                            @error('present_day_amount')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Employee Week-Off Amount --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Employee Week-Off Amount</label>
                            <input type="number" step="0.01" name="employee_week_off_amount"
                                class="form-control @error('employee_week_off_amount') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->employee_week_off_amount ? $edit?->employee_week_off_amount : old('employee_week_off_amount') }}"placeholder="Enter Employee Week-Off Amount">
                            @error('employee_week_off_amount')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Company Pay Leave Amount --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Company Pay Leave Amount</label>
                            <input type="number" step="0.01" name="company_pay_leave_amount"
                                class="form-control @error('company_pay_leave_amount') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->company_pay_leave_amount ? $edit?->company_pay_leave_amount : old('company_pay_leave_amount') }}"placeholder="Enter Company Pay Leave Amount">
                            @error('company_pay_leave_amount')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Employee Pay Leave Amount --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Employee Pay Leave Amount</label>
                            <input type="number" step="0.01" name="employee_pay_leave_amount"
                                class="form-control @error('employee_pay_leave_amount') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->employee_pay_leave_amount ? $edit?->employee_pay_leave_amount : old('employee_pay_leave_amount') }}"placeholder="Enter Employee Pay Leave Amount">
                            @error('employee_pay_leave_amount')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- FXS Basic --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">FXS Basic</label>
                            <input type="number" step="0.01" name="fxs_basic"
                                class="form-control @error('fxs_basic') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->fxs_basic ? $edit?->fxs_basic : old('fxs_basic') }}"placeholder="Enter FXS Basic">
                            @error('fxs_basic')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- FXS Hra --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">FXS Hra</label>
                            <input type="number" step="0.01" name="fxs_hra"
                                class="form-control @error('fxs_hra') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->fxs_hra ? $edit?->fxs_hra : old('fxs_hra') }}"placeholder="Enter FXS Hra">
                            @error('fxs_hra')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- FXS Other --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">FXS Other</label>
                            <input type="number" step="0.01" name="fxs_other"
                                class="form-control @error('fxs_other') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->fxs_other ? $edit?->fxs_other : old('fxs_other') }}"placeholder="Enter FXS Other">
                            @error('fxs_other')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- FXS Total Earning --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">FXS Total Earning</label>
                            <input type="number" step="0.01" name="fxs_total_earning"
                                class="form-control @error('fxs_total_earning') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->fxs_total_earning ? $edit?->fxs_total_earning : old('fxs_total_earning') }}"placeholder="Enter FXS Total Earning">
                            @error('fxs_total_earning')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- DWS Basic --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">DWS Basic</label>
                            <input type="number" step="0.01" name="dws_basic"
                                class="form-control @error('dws_basic') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->dws_basic ? $edit?->dws_basic : old('dws_basic') }}"placeholder="Enter DWS Basic">
                            @error('dws_basic')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- DWS DA --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">DWS DA</label>
                            <input type="number" step="0.01" name="dws_da"
                                class="form-control @error('dws_da') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->dws_da ? $edit?->dws_da : old('dws_da') }}"placeholder="Enter DWS DA">
                            @error('dws_da')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- DWS Other --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">DWS Other</label>
                            <input type="number" step="0.01" name="dws_other"
                                class="form-control @error('dws_other') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->dws_other ? $edit?->dws_other : old('dws_other') }}"placeholder="Enter DWS Other">
                            @error('dws_other')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- DWS Total Earning --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">DWS Total Earning</label>
                            <input type="number" step="0.01" name="dws_total_earning"
                                class="form-control @error('dws_total_earning') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->dws_total_earning ? $edit?->dws_total_earning : old('dws_total_earning') }}"placeholder="Enter DWS Total Earning">
                            @error('dws_total_earning')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Actual Total OT Hours --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Actual Total OT Hours</label>
                            <input type="number" step="0.01" name="actual_total_ot_hours"
                                class="form-control @error('actual_total_ot_hours') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->actual_total_ot_hours ? $edit?->actual_total_ot_hours : old('actual_total_ot_hours') }}"placeholder="Enter Actual Total OT Hours">
                            @error('actual_total_ot_hours')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Earn OT Hours --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Earn OT Hours</label>
                            <input type="number" step="0.01" name="earn_ot_hours"
                                class="form-control @error('earn_ot_hours') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->earn_ot_hours ? $edit?->earn_ot_hours : old('earn_ot_hours') }}"placeholder="Enter Earn OT Hours">
                            @error('earn_ot_hours')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Earn OT Payable Amount --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Earn OT Payable Amount</label>
                            <input type="number" step="0.01" name="earn_ot_payable_amt"
                                class="form-control @error('earn_ot_payable_amt') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->earn_ot_payable_amt ? $edit?->earn_ot_payable_amt : old('earn_ot_payable_amt') }}"placeholder="Enter Earn OT Payable Amount">
                            @error('earn_ot_payable_amt')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Earn OT Days --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Earn OT Days</label>
                            <input type="number" step="0.01" name="earn_ot_days"
                                class="form-control @error('earn_ot_days') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->earn_ot_days ? $edit?->earn_ot_days : old('earn_ot_days') }}"placeholder="Enter Earn OT Days">
                            @error('earn_ot_days')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Earn Performance Incentive --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Earn Performance Incentive</label>
                            <input type="number" step="0.01" name="earn_performation_incentive"
                                class="form-control @error('earn_performation_incentive') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->earn_performation_incentive ? $edit?->earn_performation_incentive : old('earn_performation_incentive') }}"placeholder="Enter Earn Performance Incentive">
                            @error('earn_performation_incentive')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Bonus Master --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Bonus Master</label>
                            <input type="number" step="0.01" name="bonus_amount"
                                class="form-control @error('bonus_amount') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->bonus_amount ? $edit?->bonus_amount : old('bonus_amount') }}"placeholder="Enter Bonus Master">
                            @error('bonus_amount')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Bonus Temp Ammount --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Bonus Temp Ammount</label>
                            <input type="number" step="0.01" name="bonus_temp_amount"
                                class="form-control @error('bonus_temp_amount') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->bonus_temp_amount ? $edit?->bonus_temp_amount : old('bonus_temp_amount') }}"placeholder="Enter Bonus Temp Ammount">
                            @error('bonus_temp_amount')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Bonus Ammount Adjustable --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Bonus Ammount Adjustable</label>
                            <input type="number" step="0.01" name="bonus_amount_adjustment"
                                class="form-control @error('bonus_amount_adjustment') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->bonus_amount_adjustment ? $edit?->bonus_amount_adjustment : old('bonus_amount_adjustment') }}"placeholder="Enter Bonus Ammount Adjustable">
                            @error('bonus_amount_adjustment')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Earn Sub Total --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Earn Sub Total</label>
                            <input type="number" step="0.01" name="earn_sub_total"
                                class="form-control @error('earn_sub_total') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->earn_sub_total ? $edit?->earn_sub_total : old('earn_sub_total') }}"placeholder="Enter Earn Sub Total">
                            @error('earn_sub_total')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Total Earning --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Total Earning</label>
                            <input type="number" step="0.01" name="total_earning"
                                class="form-control @error('total_earning') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->total_earning ? $edit?->total_earning : old('total_earning') }}"placeholder="Enter Total Earning">
                            @error('total_earning')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- DED Employee PF --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">DED Employee PF</label>
                            <input type="number" step="0.01" name="ded_employee_pf"
                                class="form-control @error('ded_employee_pf') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->ded_employee_pf ? $edit?->ded_employee_pf : old('ded_employee_pf') }}"placeholder="Enter DED Employee PF">
                            @error('ded_employee_pf')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- DED Pradhan mantri PF --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">DED Pradhan mantri PF</label>
                            <input type="number" step="0.01" name="ded_pradhan_mantri_pf"
                                class="form-control @error('ded_pradhan_mantri_pf') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->ded_pradhan_mantri_pf ? $edit?->ded_pradhan_mantri_pf : old('ded_pradhan_mantri_pf') }}"placeholder="Enter DED Pradhan mantri PF">
                            @error('ded_pradhan_mantri_pf')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- DED ESI Employee --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">DED ESI Employee</label>
                            <input type="number" step="0.01" name="ded_esi_employee"
                                class="form-control @error('ded_esi_employee') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->ded_esi_employee ? $edit?->ded_esi_employee : old('ded_esi_employee') }}"placeholder="Enter DED ESI Employee">
                            @error('ded_esi_employee')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- DED ESI Company --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">DED ESI Company</label>
                            <input type="number" step="0.01" name="ded_esi_company"
                                class="form-control @error('ded_esi_company') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->ded_esi_company ? $edit?->ded_esi_company : old('ded_esi_company') }}"placeholder="Enter DED ESI Company">
                            @error('ded_esi_company')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- DED PT --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">DED PT</label>
                            <input type="number" step="0.01" name="ded_pt"
                                class="form-control @error('ded_pt') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->ded_pt ? $edit?->ded_pt : old('ded_pt') }}"placeholder="Enter DED PT">
                            @error('ded_pt')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- DED Insurance --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">DED Insurance</label>
                            <input type="number" step="0.01" name="ded_insurance"
                                class="form-control @error('ded_insurance') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->ded_insurance ? $edit?->ded_insurance : old('ded_insurance') }}"placeholder="Enter DED Insurance">
                            @error('ded_insurance')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- DED Advance --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">DED Advance</label>
                            <input type="number" step="0.01" name="ded_advance"
                                class="form-control @error('ded_advance') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->ded_advance ? $edit?->ded_advance : old('ded_advance') }}"placeholder="Enter DED Advance">
                            @error('ded_advance')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Advance Amount Adjustment --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Advance Amount Adjustment</label>
                            <input type="number" step="0.01" name="advance_amount_adjustment"
                                class="form-control @error('advance_amount_adjustment') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->advance_amount_adjustment ? $edit?->advance_amount_adjustment : old('advance_amount_adjustment') }}"placeholder="Enter Advance Amount Adjustment">
                            @error('advance_amount_adjustment')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- DED TDS --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">DED TDS</label>
                            <input type="number" step="0.01" name="ded_tds"
                                class="form-control @error('ded_tds') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->ded_tds ? $edit?->ded_tds : old('ded_tds') }}"placeholder="Enter DED TDS">
                            @error('ded_tds')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- TDS Amount Adjustment --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">TDS Amount Adjustment</label>
                            <input type="number" step="0.01" name="tds_amount_adjustment"
                                class="form-control @error('tds_amount_adjustment') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->tds_amount_adjustment ? $edit?->tds_amount_adjustment : old('tds_amount_adjustment') }}"placeholder="Enter TDS Amount Adjustment">
                            @error('tds_amount_adjustment')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- DED WF --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">DED WF</label>
                            <input type="number" step="0.01" name="ded_wf"
                                class="form-control @error('ded_wf') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->ded_wf ? $edit?->ded_wf : old('ded_wf') }}"placeholder="Enter DED WF">
                            @error('ded_wf')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- DED Loan Amount --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">DED Loan Amount</label>
                            <input type="number" step="0.01" name="ded_loan_amount"
                                class="form-control @error('ded_loan_amount') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->ded_loan_amount ? $edit?->ded_loan_amount : old('ded_loan_amount') }}"placeholder="Enter DED Loan Amount">
                            @error('ded_loan_amount')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Loan Amount Adjustment --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Loan Amount Adjustment</label>
                            <input type="number" step="0.01" name="loan_amount_adjustment"
                                class="form-control @error('loan_amount_adjustment') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->loan_amount_adjustment ? $edit?->loan_amount_adjustment : old('loan_amount_adjustment') }}"placeholder="Enter Loan Amount Adjustment">
                            @error('loan_amount_adjustment')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- DED Other --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">DED Other</label>
                            <input type="number" step="0.01" name="ded_other"
                                class="form-control @error('ded_other') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->ded_other ? $edit?->ded_other : old('ded_other') }}"placeholder="Enter DED Other">
                            @error('ded_other')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Total Deduction --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Total Deduction</label>
                            <input type="number" step="0.01" name="total_deduction"
                                class="form-control @error('total_deduction') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->total_deduction ? $edit?->total_deduction : old('total_deduction') }}"placeholder="Enter Total Deduction">
                            @error('total_deduction')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- DED Other Remark --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">DED Other Remark</label>
                            <input type="number" step="0.01" name="ded_other_remark"
                                class="form-control @error('ded_other_remark') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->ded_other_remark ? $edit?->ded_other_remark : old('ded_other_remark') }}"placeholder="Enter DED Other Remark">
                            @error('ded_other_remark')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Net Bank Pay --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Net Bank Pay</label>
                            <input type="number" step="0.01" name="net_bank_pay"
                                class="form-control @error('net_bank_pay') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->net_bank_pay ? $edit?->net_bank_pay : old('net_bank_pay') }}"placeholder="Enter Net Bank Pay">
                            @error('net_bank_pay')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Additional OT Hour --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Additional OT Hour</label>
                            <input type="number" step="0.01" name="additional_ot_hour"
                                class="form-control @error('additional_ot_hour') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->additional_ot_hour ? $edit?->additional_ot_hour : old('additional_ot_hour') }}"placeholder="Enter Additional OT Hour">
                            @error('additional_ot_hour')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Total Acc OT Plus Add OT --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Total Acc OT Plus Add OT</label>
                            <input type="number" step="0.01" name="total_acc_ot_plus_add_ot"
                                class="form-control @error('total_acc_ot_plus_add_ot') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->total_acc_ot_plus_add_ot ? $edit?->total_acc_ot_plus_add_ot : old('total_acc_ot_plus_add_ot') }}"placeholder="Enter Total Acc OT Plus Add OT">
                            @error('total_acc_ot_plus_add_ot')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select class="form-control select2 w-100 @error('status') is-invalid @enderror"
                                name="status" required>
                                <option disabled selected>Select Status</option>
                                @foreach (['active', 'inactive'] as $status)
                                    <option value="{{ $status }}"
                                        @if (isset($edit)) @if ($edit->status == $status) {{ 'selected' }} @endif
                                    @else @if (old('status', 'active') == $status) {{ 'selected' }} @endif @endif> {{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="divider">
                        <hr />
                    </div>
                    <div class="col-md-12 text-center">
                        <button type="submit" class="btn btn-success mt-1 mb-1">
                            {{ isset($edit) ? 'Update' : 'Submit' }}
                        </button>
                        <a href="{{ route($route . '.index') }}" class="btn btn-danger mt-1 mb-1">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('page_leavel_script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cleave.js/1.6.0/cleave.min.js"></script>
    <script src="{{ asset('software/vendor/libs/flatpickr/flatpickr.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
@endsection

@push('page_scripts')

    @if (!$company_id)
        @include('utils.getCompany')
    @endif
    @include('utils.getEmployee')
    @include('utils.getBranch')
    @include('utils.getDepartment')

    <script type="text/javascript">
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
        });

        flatpickr("#added_date", {
            maxDate: "today",
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d-m-Y"
        });


        $(document).ready(function() {
            $('.time-mask').each(function() {
                const $input = $(this);

                // Get existing value or fallback to default (e.g., current time)
                let initialTime = $input.val().trim();

                if (!initialTime) {
                    // Set fallback default (current time in HH:mm:ss format)
                    const now = new Date();
                    const hours = String(now.getHours()).padStart(2, '0');
                    const minutes = String(now.getMinutes()).padStart(2, '0');
                    const seconds = String(now.getSeconds()).padStart(2, '0');
                    initialTime = `${hours}:${minutes}:${seconds}`;
                    $input.val(initialTime);
                }

                // Initialize Cleave.js mask
                new Cleave(this, {
                    time: true,
                    timePattern: ['h', 'm', 's']
                });
            });

            function calculateCTC() {
                let basic_da = parseFloat($('#basic_da').val()) || 0;
                let hra = parseFloat($('#hra').val()) || 0;
                let conveyance = parseFloat($('#conveyance_allowance').val()) || 0;
                let medical = parseFloat($('#medical_allowance').val()) || 0;
                let special = parseFloat($('#special_allowance').val()) || 0;

                let total = basic_da + hra + conveyance + medical + special;
                console.log(total);
                $('#ctc').val(total.toFixed(2));
            }

            function calculateHRA() {
                const basic = parseFloat($('#basic_da').val()) || 0;
                const hra = (basic * 40) / 100;
                $('#hra').val(hra.toFixed(2));
            }

            $(document).on('input', '.salary-input', function() {
                calculateCTC();
                calculateHRA();
            });


            calculateCTC();
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const employeeSelect = document.querySelector('select[name="employee_id"]');
            const calculateDaysInput = document.querySelector('input[name="calculate_days"]');
            const totalPresentDaysInput = document.querySelector('input[name="total_present_days"]');
            // console.log("hii");

            if (employeeSelect) {
                employeeSelect.addEventListener('change', function() {
                    const employeeId = this.value;

                    if (employeeId) {
                        fetch("{{ route('salaries.getAttendanceDays') }}", {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    employee_id: employeeId
                                })
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.calculate_days !== undefined) {
                                    calculateDaysInput.value = data.calculate_days;
                                    totalPresentDaysInput.value = data.total_present_days;
                                } else {
                                    calculateDaysInput.value = '';
                                    totalPresentDaysInput.value = '';
                                }
                            })
                            .catch(err => console.error(err));
                    } else {
                        calculateDaysInput.value = '';
                        totalPresentDaysInput.value = '';
                    }
                });
            }
        });

        function getAttendanceReport() {
            let formData = {
                company_id: $(".search_by_company").val(),
                branch_id: $(".search_by_branch").val(),
                department_id: $(".search_by_department").val(),
                employee_id: $("#employee_id").val(),
                year: $("select[name='year']").val(),
                month: $("select[name='month']").val(),
                get_calculation_only: true
            };

            if (!formData.company_id || !formData.department_id || !formData.employee_id || !formData.year || !formData
                .month) return;

            $.ajax({
                url: "{{ route('attendance-report.getReport') }}",
                type: "POST",
                data: formData,
                success: function(res) {
                    if (res.employees.length > 0) {
                        let emp = res.employees[0];
                        $("input[name='total_present_days']").val(emp.total_present_days);
                        $("input[name='calculate_days']").val(emp.calculate_days);
                        $("input[name='holiday']").val(emp.total_holidays);
                        $("input[name='half_day']").val(emp.total_half_days);
                        $("input[name='total_week_off']").val(emp.total_week_off);
                        $("input[name='total_leave']").val((emp.total_leave).toFixed(1));
                        $("input[name='total_day']").val(res.daysInMonth);
                        $("input[name='total_absent']").val(emp.total_absent_days);
                        $("input[name='total_sandwich_leave']").val(emp.sandwich_leave);
                        $("input[name='working_hour']").val(emp.total_working_hours);


                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    alert('Something went wrong!');
                }
            });
        }


        // function getAttendanceReport() {

        //     let formData = {
        //         company_id: $(".search_by_company option:selected").val(),
        //         branch_id: $(".search_by_branch option:selected").val(),
        //         department_id: $(".search_by_department option:selected").val(),
        //         employee_id: $(".search_by_employee option:selected").val(),
        //         year: $("select[name='year'] option:selected").val(),
        //         month: $("select[name='month'] option:selected").val(),
        //         added_date: $("input[name='added_date']").val(),
        //         get_calculation_only: true,
        //     };

        //     console.log("L-1173 formData", formData);

        //     if (formData?.company_id && formData?.department_id && formData?.employee_id) {

        //         $.ajax({
        //             url: "{{ route('attendance-report.getReport') }}",
        //             type: "POST",
        //             data: formData,
        //             beforeSend: function() {
        //                 $('#view_report_btn').prop('disabled', true).html(
        //                     '<i class="fa fa-spinner fa-spin"></i> Loading...');
        //             },
        //             success: function(res) {
        //                 $('#view_report_btn').prop('disabled', false).html(
        //                     '<i class="fa fa-eye me-1"></i> Show');

        //                 // ✅ Fill your form fields automatically
        //                 if (res.employees && res.employees.length > 0) {
        //                     let emp = res.employees[0];

        //                     // Update input fields
        //                     $('input[name="calculate_days"]').val(emp.calculate_days ?? 0);
        //                     $('input[name="total_present_days"]').val(emp.total_present_days ?? 0);
        //                 }
        //                 console.log("demoo");
        //                 // ✅ (optional) render attendance report table if needed
        //                 renderAttendanceTable(res);
        //             },
        //             error: function(xhr) {
        //                 console.error(xhr.responseText);
        //                 alert('Something went wrong!');
        //                 $('#view_report_btn').prop('disabled', false).html(
        //                     '<i class="fa fa-eye me-1"></i> Show');
        //             }
        //         });
        //     }
        // } else {
        //     alert("Please select company, department, and employee first!");
        // }
        // }
    </script>

@endpush
