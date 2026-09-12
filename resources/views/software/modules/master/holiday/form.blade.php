@extends('software.layout.app')

@php
    $i = 0;

    $page_title = isset($modules['title']) ? $modules['title'] : null;
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $company_id = isset($modules['company_id']) ? $modules['company_id'] : null;
    $defualtCountryId = 101;
    $colums = 'col-md-3 col-sm-12 mb-2';

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
                'route' => $route,
                'show_back_btn' => true,
            ])
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


                    {{-- Holiday Label --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label"> Holiday Label <span class="text-danger">*</span> </label>
                            <input id="holiday_label" type="text"
                                class="form-control @error('holiday_label') is-invalid @enderror" name="holiday_label"
                                value="{{ isset($edit) && $edit?->holiday_label ? $edit?->holiday_label : old('holiday_label') }}"
                                placeholder="Enter Holiday Label">
                            @error('holiday_label')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    @if (!$company_id)
                        <div class="{{ $colums ?? 'col-12' }}">
                            <div class="form-group">
                                <label class="form-label">Select Company <span class="text-danger">*</span></label>
                                <select id="company_id"
                                    class="form-control select2 search_by_company @error('company_id') is-invalid @enderror"
                                    name="company_id" data-auto_select_option="{{ isset($edit) ? 'false' : 'true' }}"
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

                    {{-- Country --}}
                    <div class="{{ $colums ?? 'col-12' }} d-none">
                        <div class="form-group">
                            <label class="form-label">Country <span class="text-danger">*</span></label>
                            <select name="country_id" id="country_id"
                                class="form-control @error('country_id') is-invalid @enderror search_by_country select2"
                                data-append="search_by_country" data-filterByStatus="active"
                                data-selectedCountryId="{{ isset($edit) && $edit?->country_id ? $edit?->country_id : old('country_id', $defualtCountryId) }}"
                                data-selectedStateId="{{ isset($edit) && $edit?->state_ids ? implode(',', $edit->state_ids) : '' }}"
                                autofocus>
                                <option value="" disabled
                                    {{ old('country_id', $edit->country_id ?? '') ? '' : 'selected' }}>
                                    Select Country
                                </option>
                            </select>
                            @error('country_id')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- State --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">State <span class="text-danger">*</span></label>
                            <select name="state_ids[]" id="state"
                                class="form-control @error('state_ids') is-invalid @enderror search_by_state select2"
                                multiple data-append="search_by_state" data-show_select_all="true"
                                data-selectedCountryId="{{ isset($edit) && $edit?->country_id ? $edit?->country_id : old('country_id') }}"
                                data-selectedStateId="{{ isset($edit) && $edit?->state_ids ? implode(',', $edit->state_ids) : '' }}">
                                {{-- old('state_ids') --}}

                                <option value="" disabled>
                                    Select State
                                </option>
                            </select>
                            @error('state_id')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Employee Designation Type --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <label class="form-label" for="employee_designation_type">Employee Designation Type <span
                                class="text-danger">*</span></label>
                        <select id="employee_designation_type" name="employee_designation_type"
                            class="form-select select2 @error('employee_designation_type') is-invalid @enderror">
                            @foreach ($employee_designation_type_arr as $key => $value)
                                <option value="{{ $key }}"
                                    {{ old('employee_designation_type', $edit->employee_designation_type ?? '') == $key ? 'selected' : '' }}>
                                    {{ $value }}
                                </option>
                            @endforeach
                        </select>
                        @error('employee_designation_type')
                            <span class="invalid-feedback">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    {{-- From Date --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label"> From Date <span class="text-danger">*</span> </label>

                            <input type="text" id="from_date" name="from_date"
                                class="form-control @error('from_date') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->from_date ? $edit->from_date : old('from_date') }}"
                                placeholder="Select from date" />
                            @error('from_date')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- To Date --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label"> To Date </label>

                            <input type="text" id="to_date" name="to_date"
                                class="form-control @error('to_date') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->to_date ? $edit->to_date : old('to_date') }}"
                                placeholder="Select to date" />
                            @error('to_date')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Remark --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label"> Remark </label>
                            <input id="remark" type="text" class="form-control @error('remark') is-invalid @enderror"
                                name="remark"
                                value="{{ isset($edit) && $edit?->remark ? $edit?->remark : old('remark') }}"
                                placeholder="Enter Remark">
                            @error('remark')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Status --}}
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

    <script type="text/javascript">
        // initialize from_date
        const fromPicker = flatpickr("#from_date", {
            minDate: "today",
            dateFormat: "d-m-Y",
            defaultDate: null,
            onChange: function(selectedDates, dateStr) {
                if (dateStr) {
                    toPicker.set('minDate', dateStr); // 👈 update minDate of to_date
                    toPicker.setDate(dateStr); // 👈 optional: set same date initially
                }
            }
        });

        // initialize to_date once and keep reference
        const toPicker = flatpickr("#to_date", {
            minDate: "today",
            dateFormat: "d-m-Y",
            defaultDate: null
        });
    </script>

    @if (!$company_id)
        @include('utils.getCompany')
    @endif
    @include('utils.getCountry')
    @include('utils.getStateByCountry')

@endpush
