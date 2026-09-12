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
    <link rel="stylesheet" href="{{ asset('software/vendor/libs/flatpickr/flatpickr.css') }}" />
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
            ])
            <a class="btn btn-primary waves-effect waves-light text-white" href="{{ url()->previous() }}">
                <i class="menu-icon ti ti-chevrons-left"></i> Back
            </a>
        </div>
    </div>


    <div class="card my-3 mb-4">
        <div class="card-body">
            <form
                action="{{ isset($edit) && $edit?->id ? route($route . '.update', [$edit?->id]) : route($route . '.store') }}"
                method="POST" enctype="multipart/form-data">
                @csrf
                @isset($edit)
                    @method('PUT')
                @endisset

                <div class="row">

                    @if (!$company_id)
                        <div class="col-md-3 col-sm-12 mb-2">
                            <div class="form-group">
                                <label class="form-label">Select Company <span class="text-danger">*</span></label>
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
                    {{-- Employee name --}}
                    <div class="col-md-3 col-sm-12 mb-2">
                        <div class="form-group">
                            <label class="form-label">Select Employee <span class="text-danger">*</span></label>
                            <select id="employee_id"
                                class="form-control select2 search_by_employee @error('employee_id') is-invalid @enderror"
                                name="employee_id"
                                data-selectedemployeeid="{{ old('employee_id') ?? ($edit->employee_id ?? ($preselectedEmployeeId ?? '')) }}">
                                <option value="">Select Employee</option>
                            </select>

                            @error('employee_id')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                    {{-- Date --}}
                    <div class="col-md-3 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label"> Increment Date <span class="text-danger">*</span> </label>

                            <input type="text" id="icrement_date" name="icrement_date"
                                class="form-control plan-form @error('icrement_date') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->icrement_date ? $edit->icrement_date : (request()->isMethod('post') ? old('icrement_date') : '') }}"
                                placeholder="Increment Date" />
                            @error('icrement_date')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                    {{-- basic + da --}}
                    <div class="col-md-3 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label"> Basic + D.A <span class="text-danger">*</span> </label>
                            <input id="basic_da" type="number"  step="0.01"
                                class="form-control @error('basic_da') is-invalid @enderror" name="basic_da"
                                value="{{ isset($edit) && $edit?->basic_da ? $edit?->basic_da : old('basic_da') }}"
                                placeholder="Enter Basic + D.A">
                            @error('basic_da')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                    {{-- HRA --}}
                    <div class="col-md-3 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label"> HRA</label>
                            <input id="hra" type="number"  step="0.01" class="form-control @error('hra') is-invalid @enderror"
                                name="hra" value="{{ isset($edit) && $edit?->hra ? $edit?->hra : old('hra') }}"
                                placeholder="Enter HRA" readonly>
                            @error('hra')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                    {{-- Conveyance Allowance --}}
                    <div class="col-md-3 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label"> Conveyance Allowance  </label>
                            <input id="conveyance_allowance" type="number"  step="0.01"
                                class="form-control @error('conveyance_allowance') is-invalid @enderror"
                                name="conveyance_allowance"
                                value="{{ isset($edit) && $edit?->conveyance_allowance ? $edit?->conveyance_allowance : old('conveyance_allowance') }}"
                                placeholder="Enter Conveyance Allowance">
                            @error('conveyance_allowance')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                    {{-- Medical Allowance --}}
                    <div class="col-md-3 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Medical Allowance</label>
                            <input id="medical_allowance" type="number"  step="0.01"
                                class="form-control @error('medical_allowance') is-invalid @enderror"
                                name="medical_allowance"
                                value="{{ isset($edit) && $edit?->medical_allowance ? $edit?->medical_allowance : old('medical_allowance') }}"
                                placeholder="Enter Medical Allowance">
                            @error('medical_allowance')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                    {{-- Special Allowance --}}
                    <div class="col-md-3 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Special Allowance </label>
                            <input id="special_allowance" type="number"  step="0.01"
                                class="form-control @error('special_allowance') is-invalid @enderror"
                                name="special_allowance"
                                value="{{ isset($edit) && $edit?->special_allowance ? $edit?->special_allowance : old('special_allowance') }}"
                                placeholder="Enter Special Allowance">
                            @error('special_allowance')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                    {{-- PF --}}
                    <div class="col-md-3 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">PF </label>
                            <input id="pf" type="number"  step="0.01" class="form-control @error('pf') is-invalid @enderror"
                                name="pf" value="{{ isset($edit) && $edit?->pf ? $edit?->pf : old('pf') }}"
                                placeholder="Enter PF">
                            @error('pf')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{--  Effective Month --}}
                    <div class="col-md-3 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label"> Effective Month <span class="text-danger">*</span> </label>
                            <select id="effective_month"
                                class="form-control select2 @error('effective_month') is-invalid @enderror"
                                name="effective_month">
                                <option value="">Select Month</option>
                            </select>
                            @error('effective_month')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                    {{-- Effective Year --}}
                    <div class="col-md-3 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Effective Year <span class="text-danger">*</span></label>
                            <select id="effective_year"
                                class="form-control select2 @error('effective_year') is-invalid @enderror"
                                name="effective_year">
                                <option value="">Select Year</option>
                            </select>
                            @error('effective_year')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                    {{-- Designation  --}}
                    <div class="col-md-3 col-sm-12 mb-2">
                        <div class="form-group">
                            <label class="form-label">Select Designation <span class="text-danger">*</span></label>
                            <select id="designation_id"
                                class="form-control select2 search_by_designation  @error('designation_id') is-invalid @enderror"
                                name="designation_id"
                                data-selecteddesignationid="{{ old('designation_id') ?? ($edit->designation_id ?? '') }}"
                                data-append="search_by_designation">
                                <option value="">Select Designation</option>
                            </select>

                            @error('designation_id')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                    {{-- Per Day Salary  --}}
                    <div class="col-md-3 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Per Day Salary</label>
                            <input id="per_day_salary" type="number"  step="0.01"
                                class="form-control @error('per_day_salary') is-invalid @enderror" name="per_day_salary"
                                value="{{ isset($edit) && $edit?->per_day_salary ? $edit?->per_day_salary : old('per_day_salary') }}"
                                placeholder="Enter Per Day Salary ">
                            @error('per_day_salary')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                    {{-- Per Hour Salary  --}}
                    <div class="col-md-3 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Per Hour Salary </label>
                            <input id="per_hour_salary" type="number"  step="0.01"
                                class="form-control @error('per_hour_salary') is-invalid @enderror"
                                name="per_hour_salary"
                                value="{{ isset($edit) && $edit?->per_hour_salary ? $edit?->per_hour_salary : old('per_hour_salary') }}"
                                placeholder="Enter Per Hour Salary ">
                            @error('per_hour_salary')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                    {{-- Remark --}}
                    <div class="col-md-3 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Remark</label>
                            <textarea id="remark" class="form-control @error('remark') is-invalid @enderror autosize" name="remark"
                                placeholder="Enter Remark">{{ isset($edit) && $edit?->remark ? $edit?->remark : old('remark') }}</textarea>
                            @error('remark')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Status --}}
                    <div class="col-md-3 col-sm-12 mb-3">
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
                    {{-- Divider --}}
                    <div class="divider">
                        <hr />
                    </div>

                    {{-- Submit Buttons --}}
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
@push('page_scripts')
    <script src="{{ asset('software/vendor/libs/flatpickr/flatpickr.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/autosize.js/4.0.2/autosize.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            autosize(document.querySelectorAll('.autosize'));
        });
    </script>
    <script>
        $(document).ready(function() {
            const currentYear = new Date().getFullYear();
            const startYear = currentYear - 5;
            const endYear = currentYear + 5;
            const selectedYear = "{{ old('effective_year', $edit->effective_year ?? '') }}";

            const yearOptions = new Set();
            for (let y = startYear; y <= endYear; y++) {
                yearOptions.add(y);
            }

            if (selectedYear) {
                const parsedSelectedYear = parseInt(selectedYear, 10);
                if (!Number.isNaN(parsedSelectedYear)) {
                    yearOptions.add(parsedSelectedYear);
                }
            }

            Array.from(yearOptions)
                .sort((a, b) => a - b)
                .forEach((year) => {
                    const isSelected = String(year) === selectedYear ? 'selected' : '';
                    $('#effective_year').append(`<option value="${year}" ${isSelected}>${year}</option>`);
                });
        });
    </script>
    <script>
        $(document).ready(function() {
            let months = [
                'January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'
            ];

            let selectedMonth = "{{ isset($edit) ? $edit?->effective_month : old('effective_month') }}";

            months.forEach(function(m) {
                let selected = (selectedMonth === m) ? 'selected' : '';
                $('#effective_month').append(`<option value="${m}" ${selected}>${m}</option>`);
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            const earningsSelectors = '#hra, #conveyance_allowance, #medical_allowance, #special_allowance';
            const workingDays = 26;
            const hoursPerDay = 8;
            const defaultCompanyHra = @json($companyHraPercentage ?? null);
            let companyHraPercentage = parseFloat(defaultCompanyHra);

            if (Number.isNaN(companyHraPercentage)) {
                companyHraPercentage = null;
            }

            function parseAmount(selector) {
                return parseFloat($(selector).val()) || 0;
            }

            function recalculateIncrementBreakdown() {
                const monthlyTotal =
                    parseAmount('#basic_da') +
                    parseAmount('#hra') +
                    parseAmount('#conveyance_allowance') +
                    parseAmount('#medical_allowance') +
                    parseAmount('#special_allowance');

                const perDay = monthlyTotal / (workingDays || 1);
                const perHour = perDay / (hoursPerDay || 1);

                $('#per_day_salary').val(perDay.toFixed(2));
                $('#per_hour_salary').val(perHour.toFixed(2));
            }

            function toggleHraAccessibility() {
                if (companyHraPercentage === null) {
                    $('#hra').prop('readonly', false);
                } else {
                    $('#hra').prop('readonly', true);
                }
            }

            function applyCompanyHra(shouldRecalculate = true) {
                toggleHraAccessibility();

                if (companyHraPercentage === null) {
                    if (shouldRecalculate) {
                        recalculateIncrementBreakdown();
                    }
                    return;
                }

                const basicValue = parseFloat($('#basic_da').val()) || 0;
                const computedHra = basicValue * (companyHraPercentage / 100);
                $('#hra').val(computedHra.toFixed(2));

                if (shouldRecalculate) {
                    recalculateIncrementBreakdown();
                }
            }

            $(document).on('change', '#employee_id', function() {
                let employeeId = $(this).val();
                if (employeeId) {
                    $.ajax({
                        url: '{{ route("employee-increment-details.get-salary-details") }}',
                        type: 'GET',
                        data: { employee_id: employeeId },
                        success: function(response) {
                            if (response.success && response.data) {
                                let data = response.data;
                                $('#basic_da').val(data.basic_da);
                                $('#hra').val(data.hra);
                                $('#conveyance_allowance').val(data.conveyance_allowance);
                                $('#medical_allowance').val(data.medical_allowance);
                                $('#special_allowance').val(data.special_allowance);
                                $('#pf').val(data.pf);
                                $('#per_day_salary').val(data.per_day_salary);
                                $('#per_hour_salary').val(data.per_hour_salary);
                                applyCompanyHra(true);
                            }
                        }
                    });
                }
            });

            $(document).on('input change', earningsSelectors, recalculateIncrementBreakdown);

            $(document).on('input', '#basic_da', function() {
                applyCompanyHra();
            });

            $(document).on('change', '.search_by_company', function() {
                const companyDataAttr = $(this).find('option:selected').attr('data-companydata');
                let parsedCompany = null;

                if (companyDataAttr) {
                    try {
                        parsedCompany = JSON.parse(companyDataAttr);
                    } catch (error) {
                        parsedCompany = null;
                    }
                }

                if (parsedCompany && parsedCompany.hra_percentage !== undefined && parsedCompany.hra_percentage !== null && parsedCompany.hra_percentage !== '') {
                    const parsedValue = parseFloat(parsedCompany.hra_percentage);
                    companyHraPercentage = Number.isNaN(parsedValue) ? null : parsedValue;
                } else {
                    companyHraPercentage = null;
                }

                applyCompanyHra();
            });

            applyCompanyHra();
        });
    </script>
    <script type="text/javascript">
        flatpickr("#icrement_date", {
            defaultDate: null,
            dateFormat: "Y-m-d",
            allowInput: true,
            minDate: null,
            maxDate: null
        });
    </script>
    @if (!$company_id)
        @include('utils.getCompany')
    @endif
    @include('utils.getEmployee');
    @include('utils.getDesignation');
@endpush
