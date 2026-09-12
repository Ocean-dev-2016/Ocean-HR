@extends('software.layout.app')

@php
    use App\Models\Company;
    $page_title = isset($modules['title']) ? $modules['title'] : null;
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $i = 0;
    $company_id = isset($modules['company_id']) ? $modules['company_id'] : null;

    // dd($modules);

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
                        <div class="col-md-3 col-sm-12 mb-2">
                            <div class="form-group @error('company_id') is-invalid @enderror">
                                <label class="form-label">Select Company <span class="text-danger">*</span></label>
                                <select id="company_id"
                                    class="form-control select2 search_by_company"
                                    name="company_id"
                                    data-selectedCompanyId="{{ old('company_id') ?? ($edit->company_id ?? ($preselectedCompanyId ?? '')) }}">
                                    <option value="">Select Company</option>
                                </select>
                            </div>
                            @error('company_id')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    @else
                        <input type="hidden" class="form-control search_by_company" name="company_id"
                            value="{{ $company_id }}" />
                    @endif
                    <div class="col-md-3 col-sm-12 mb-2">
                        <div class="form-group @error('employee_id') is-invalid @enderror">
                            <label class="form-label">Select Employee <span class="text-danger">*</span></label>
                            <select id="employee_id" class="form-control select2 search_by_employee " name="employee_id"
                                data-selectedEmployeeId="{{ old('employee_id') ?? ($edit->employee_id ?? ($preselectedEmployeeId ?? '')) }}">
                                <option value="">Select Employee</option>
                            </select>
                        </div>

                        @error('employee_id')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    {{-- Designation Type --}}
                    <div class="col-md-3 col-sm-12">
                        <div class="form-group">
                            <label class="form-label">Designation Type<span class="text-danger">*</span></label>
                            <select class="form-control select2 w-100 @error('designation_type') is-invalid @enderror"
                                name="designation_type" required>
                                <option disabled selected>Select Designation Type</option>
                                @foreach (['worker', 'employee'] as $designation_type)
                                    <option value="{{ $designation_type }}"
                                        @if (isset($edit)) @if ($edit->designation_type == $designation_type) {{ 'selected' }} @endif
                                    @else @if (old('designation_type', 'active') == $designation_type) {{ 'selected' }} @endif @endif> {{ ucfirst($designation_type) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-12 mb-2">
                        <div class="form-group @error('designation_id') is-invalid @enderror">
                            <label class="form-label">Select Designation <span class="text-danger">*</span></label>
                            <select id="designation_id" class="form-control select2 search_by_designation "
                                name="designation_id" data-append="search_by_designation"
                                data-selectedDesignationId="{{ old('designation_id') ?? ($edit->designation_id ?? '') }}">
                                <option value="">Select Designation</option>
                            </select>
                        </div>

                        @error('designation_id')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="col-md-3 col-sm-12 mb-2">
                        <div class="form-group @error('department_id') is-invalid @enderror">
                            <label class="form-label">Select Department <span class="text-danger">*</span></label>
                            <select id="department_id" class="form-control select2 search_by_department "
                                name="department_id" data-append="search_by_department"
                                data-selecteddepartmentid="{{ old('department_id') ?? ($edit->department_id ?? '') }}">
                                <option value="">Select Department</option>
                            </select>

                        </div>
                        @error('department_id')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                  
                    <div class="col-md-3 col-sm-12 mb-2">
                        <div class="form-group">
                            <label class="form-label">Select Sub Department</label>
                            <select id="sub_department_id"
                                class="form-control select2 search_by_subdepartment @error('sub_department_id') is-invalid @enderror"
                                name="sub_department_id"
                                data-selectedsubdepartmentid="{{ old('sub_department_id') ?? ($edit->sub_department_id ?? '') }}">
                                <option value="">Select Sub Department</option>
                            </select>

                            @error('sub_department_id')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                      <div class="col-md-3 col-sm-12 mb-2">
                        <div class="form-group">
                            <label class="form-label">Select Process</label>
                            <select id="process_id"
                                class="form-control select2 search_by_process @error('process_id') is-invalid @enderror"
                                name="process_id"
                                data-selectedprocessid="{{ old('process_id') ?? ($edit->process_id ?? '') }}">
                                <option value="">Select Process</option>
                            </select>

                            @error('process_id')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                    {{-- Date of Joining --}}
                    <div class="col-md-3 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label"> Date of Joining <span class="text-danger">*</span></label>
                            <input type="text" id="date_of_joining" name="date_of_joining"
                                class="form-control plan-form @error('date_of_joining') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->date_of_joining ? \Carbon\Carbon::parse($edit->date_of_joining)->format('Y-m-d') : old('date_of_joining') }}"
                                placeholder="Date of joining" />

                            @error('date_of_joining')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>


                    {{-- Confirmation Date --}}
                    <div class="col-md-3 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label"> Confirmation Date <span class="text-danger">*</span></label>
                            <input type="text" id="employment_confirmation_date" name="employment_confirmation_date"
                                class="form-control plan-form @error('employment_confirmation_date') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->employment_confirmation_date ? \Carbon\Carbon::parse($edit->employment_confirmation_date)->format('Y-m-d') : old('employment_confirmation_date') }}"
                                placeholder="Confirmation Date" />

                            @error('employment_confirmation_date')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Employee PF Number --}}
                    <div class="col-md-3 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label"> Employee PF Number</label>
                            <input id="employee_pf_no" type="text"
                                class="form-control @error('employee_pf_no') is-invalid @enderror" name="employee_pf_no"
                                value="{{ isset($edit) && $edit?->employee_pf_no ? $edit?->employee_pf_no : old('employee_pf_no') }}"
                                placeholder="Enter Employee PF Number">
                            @error('employee_pf_no')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- UAN No --}}
                    <div class="col-md-3 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label"> UAN No </label>
                            <input id="uan_no" type="text"
                                class="form-control @error('uan_no') is-invalid @enderror" name="uan_no"
                                value="{{ isset($edit) && $edit?->uan_no ? $edit?->uan_no : old('uan_no') }}"
                                placeholder="Enter UAN No">
                            @error('uan_no')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-12">
                        <div class="form-group">
                            <label class="form-label">Payment Mode <span class="text-danger">*</span></label>
                            @php
                                $selectedPaymentMode = old('payment_mode', isset($edit) ? ($edit?->payment_mode ?? '') : '');
                            @endphp
                            <select class="form-control select2 w-100 @error('payment_mode') is-invalid @enderror"
                                name="payment_mode" required>
                                <option value="" disabled {{ $selectedPaymentMode == '' ? 'selected' : '' }}>
                                    Select Payment Mode
                                </option>
                                @foreach (['FT', 'NEFT', 'RTGS', 'IMPS'] as $payment_mode)
                                    <option value="{{ $payment_mode }}"
                                        {{ $selectedPaymentMode == $payment_mode ? 'selected' : '' }}>
                                        {{ $payment_mode }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-12 mb-2">
                        <div class="form-group">
                            <label class="form-label">Select Employment Type <span class="text-danger">*</span></label>
                            <select id="employment_type"
                                class="form-control select2 search_by_employee_type @error('employment_type') is-invalid @enderror"
                                name="employment_type" data-append="search_by_employee_type"
                                data-selectedemployeetypeid="{{ old('employment_type') ?? ($edit->employment_type ?? '') }}">
                                <option value="">Select Employment Type</option>
                            </select>

                            @error('employment_type')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-12 mb-2">
                        <div class="form-group">
                            <label class="form-label">Select Shift <span class="text-danger">*</span></label>
                            <select id="shift"
                                class="form-control select2 search_by_shift @error('shift') is-invalid @enderror"
                                name="shift" data-append="search_by_shift"
                                data-selectedshiftid="{{ old('shift') ?? ($edit->shift ?? '') }}">
                                <option value="">Select Shift</option>
                            </select>

                            @error('shift')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>


                    <div class="col-md-3 col-sm-12">
                        <div class="form-group">
                            <label class="form-label">Outdoor Attendance<span class="text-danger">*</span></label>
                            <select class="form-control select2 w-100 @error('outdoor_attendance') is-invalid @enderror"
                                name="outdoor_attendance" required>
                                <option disabled selected>Select Outdoor Attendance</option>
                                @foreach (['permitted', 'not Permitted'] as $outdoor_attendance)
                                    <option value="{{ $outdoor_attendance }}"
                                        @if (isset($edit)) @if ($edit->outdoor_attendance == $outdoor_attendance) {{ 'selected' }} @endif
                                    @else @if (old('outdoor_attendance', 'active') == $outdoor_attendance) {{ 'selected' }} @endif @endif> {{ ucfirst($outdoor_attendance) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- <div class="col-md-3 col-sm-12">
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
                    </div> --}}
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
    <script src="{{ asset('software/vendor/libs/flatpickr/flatpickr.js') }}"></script>
@endsection

@push('page_scripts')
    <script type="text/javascript">
        // initialize date_of_joining
        const date_of_joining = flatpickr("#date_of_joining", {
            defaultDate: document.getElementById("date_of_joining").value || null,
            maxDate: "today",
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d-m-Y",
            onChange: function(selectedDates, dateStr) {
                if (dateStr) {
                    employment_confirmation_date.set('minDate', dateStr); // 👈 update minDate of to_date
                    employment_confirmation_date.setDate(dateStr); // 👈 optional: set same date initially
                }
            }
        });

        // initialize employment_confirmation_date once and keep reference
        const employment_confirmation_date = flatpickr("#employment_confirmation_date", {
            defaultDate: document.getElementById("employment_confirmation_date").value || null,
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d-m-Y",
        });
    </script>
    @if (!$company_id)
        @include('utils.getCompany')
    @endif
    @include('utils.getEmployee')
    @include('utils.getDesignation')
    @include('utils.getDepartment')
    @include('utils.getSubDepartment')
    @include('utils.getProcess')
    @include('utils.getEmployeeType')
    @include('utils.getShiftType')
    @include('utils.getSubDepartment')
    @include('utils.getProcess')
@endpush
