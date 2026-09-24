@extends('software.layout.app')

@php
    $i = 0;

    $page_title = isset($modules['title']) ? $modules['title'] : null;
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $company_id = isset($modules['company_id']) ? $modules['company_id'] : null;
    $authLoginUserDetail = isset($modules['authLoginUserDetail']) ? $modules['authLoginUserDetail'] : null;
    $loginUserId = isset($authLoginUserDetail?->id) ? $authLoginUserDetail?->id : null;
    $hasPersonalOnly = !empty($modules['personal_data_permission']) && empty($modules['all_data_permission']);
    $selectedEmployeeId = old('employee_id', isset($edit) ? $edit->employee_id : ($hasPersonalOnly ? $loginUserId : ''));
    $defaultShiftId = isset($edit) ? $edit->shift_id : (old('shift_id') ?? ($hasPersonalOnly && $authLoginUserDetail ? ($authLoginUserDetail->employmentDetail?->shift ?? '') : ''));
@endphp
@section('title', $page_title)
@section('page_leavel_style')
    <link rel="stylesheet" href="{{ asset('software/vendor/libs/flatpickr/flatpickr.css') }}" />
    <style>
        .invalid-feedback {
            display: block;
        }
    </style>
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

                    {{-- Attendance --}}
                    <div class="col-md-3 col-sm-12 mb-2">
                        <div class="form-group">
                            <label class="form-label">Select Employee <span class="text-danger">*</span></label>
                            <select id="employee_id"
                                class="form-control select2 search_by_employee @error('employee_id') is-invalid @enderror"
                                {{ $hasPersonalOnly ? 'disabled' : 'name=employee_id' }}
                                data-selectedEmployeeId="{{ $selectedEmployeeId }}">
                                <option value="">Select Employee</option>
                            </select>
                            @if ($hasPersonalOnly)
                                <input type="hidden" name="employee_id" value="{{ $selectedEmployeeId }}" />
                            @endif

                            @error('employee_id')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                    {{-- Shift --}}
                    <div class="col-md-3 col-sm-12 mb-2">
                        <div class="form-group">
                            <label class="form-label">Select Shift <span class="text-danger">*</span></label>
                            <select id="shift_id"
                                class="form-control select2 search_by_shift @error('shift_id') is-invalid @enderror"
                                name="shift_id" data-selectedShiftId="{{ $defaultShiftId }}">
                                <option value="">Select Shift</option>
                            </select>


                            @error('shift_id')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                    {{-- Attendance Date --}}
                    <div class="col-md-3 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Attendance Date <span class="text-danger">*</span> </label>

                            <input type="date" id="attendance_date" name="attendance_date"
                                class="form-control plan-form @error('attendance_date') is-invalid @enderror"
                                value="{{ old('attendance_date', isset($edit) && $edit?->attendance_date ? $edit->attendance_date : date('Y-m-d')) }}"
                                max="{{ date('Y-m-d') }}" placeholder="Attendance Date" />

                            @error('attendance_date')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Punch In / Out  Minimum --}}
                    <div class="col-md-3 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Punch In / Out Time <span class="text-danger">*</span></label>
                            <input id="punch_in_time" type="time" step="1"
                                class="form-control @error('punch_in_time') is-invalid @enderror" name="punch_in_time"
                                value="{{ old('punch_in_time', isset($edit) && $edit?->punch_in_time ? $edit->punch_in_time : date('H:i:s')) }}">

                            @error('punch_in_time')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Attendance Type --}}
                    <div class="col-md-3 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label"> Attendance Type <span class="text-danger">*</span></label>
                            <select id="attendace_type" name="attendace_type"
                                class="form-control select2 @error('attendace_type') is-invalid @enderror">
                                <option value="">Select Attendance Type</option>
                                @if ($AttendaceType && isset($AttendaceType))
                                @foreach ($AttendaceType ?? [] as $key => $value)
                                    <option value="{{ $key }}"
                                        {{ (isset($edit) && $edit?->attendace_type == $key) || old('attendace_type') == $key ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                                @endif
                            </select>
                            @error('attendace_type')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <!-- Remark -->
                    <div class="col-md-3 col-sm-12 mb-2">
                        <div class="form-group">
                            <label class="form-label">Remark</label>
                            <textarea id="remark" class="form-control autosize @error('remark') is-invalid @enderror" name="remark"
                                rows="1" autocomplete="remark" placeholder="Enter remark">{{ isset($edit) ? $edit->remark : old('remark') }}</textarea>
                            @error('remark')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
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
@section('page_leavel_script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cleave.js/1.6.0/cleave.min.js"></script>
    <script src="{{ asset('software/vendor/libs/flatpickr/flatpickr.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/autosize@4.0.2/dist/autosize.min.js">
        $(document).ready(function() {
            autosize($('.autosize'));
        });
    </script>
@endsection

@push('page_scripts')
    <script type="text/javascript">
        flatpickr("#attendance_date", {
            dateFormat: "Y-m-d", // DB format
            altInput: true,
            altFormat: "d-m-Y",
            maxDate: "today",
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const timeInput = document.getElementById('punch_in_time');

            // If input is empty, set current time (even on validation errors)
            if (!timeInput.value.trim()) {
                const now = new Date();
                const hh = String(now.getHours()).padStart(2, '0');
                const mm = String(now.getMinutes()).padStart(2, '0');
                const ss = String(now.getSeconds()).padStart(2, '0');
                timeInput.value = `${hh}:${mm}:${ss}`;
            }

            // Live validation: mark invalid if not HH:MM:SS
            timeInput.addEventListener('input', function() {
                const regex = /^([01]\d|2[0-3]):([0-5]\d):([0-5]\d)$/;
                if (timeInput.value && !regex.test(timeInput.value)) {
                    timeInput.classList.add('is-invalid');
                } else {
                    timeInput.classList.remove('is-invalid');
                }
            });
        });
    </script>

    <script>
        // When employee is changed, auto-select default shift using data from get-employee API
        $(document).on('change', '#employee_id', function() {
            const $selected = $(this).find('option:selected');
            const shiftId = $selected.data('shift-id');

            if (!shiftId) {
                $('#shift_id').val('').trigger('change');
                return;
            }
            
            // Prefer letting getShift utility handle selection via data-selectedShiftId
            $('#shift_id').attr('data-selectedshiftid', shiftId);
            $('#shift_id').val(shiftId).trigger('change');
            /*
            if (typeof fetch_shift === 'function') {
                console.log("LN-240 fetch_shift", shiftId);
                fetch_shift();
            } else {
                console.log("LN-248", shiftId);
                $('#shift_id').val(shiftId).trigger('change');
            }
                */
        });
    </script>

    @if (!$company_id)
        @include('utils.getCompany')
    @endif
    @include('utils.getShift')
    @include('utils.getEmployee')
@endpush
