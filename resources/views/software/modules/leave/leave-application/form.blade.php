@extends('software.layout.app')

@php
    $i = 0;
    $page_title = isset($modules['title']) ? $modules['title'] : null;
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $company_id = isset($modules['company_id']) ? $modules['company_id'] : null;
    $authLoginUserDetail = isset($modules['authLoginUserDetail']) ? $modules['authLoginUserDetail'] : null;
    $loginUserId = isset($authLoginUserDetail?->id) ? $authLoginUserDetail?->id : null;
    $parent_type_id = isset($modules['parent_type_id']) ? $modules['parent_type_id'] : null;
    $colums = 'col-md-3 col-sm-12 mb-2';

@endphp
@section('title', $page_title)

@section('page_leavel_style')
    {{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css"> --}}
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.min.css" />
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
                        <div class="{{ $colums ?? 'col-12' }}">
                            <div class="form-group">
                                <label class="form-label">Select Company <span class="text-danger">*</span></label>
                                <select id="company_id"
                                    class="form-control select2 search_by_company @error('company_id') is-invalid @enderror"
                                    name="company_id"
                                    data-selectedCompanyId="{{ old('company_id') ?? ($edit->company_id ?? '') }}"
                                    showBranch="branchDiv">
                                    <option value="">Select Company</option>
                                </select>

                                @error('company_id')
                                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>
                    @else
                        <input type="hidden" class="form-control search_by_company" name="company_id"
                            value="{{ $company_id }}" showBranch="branchDiv" />
                    @endif

                    {{-- Branch --}}
                    <div class="{{ $colums ?? 'col-12' }} branchDiv" style="display:none;">
                        <div class="form-group">
                            <label class="form-label">Select Branch <span class="text-danger">*</span></label>
                            <select id="branch_id"
                                class="form-control select2 search_by_branch @error('branch_id') is-invalid @enderror"
                                name="branch_id" data-selectedbranchid="{{ old('branch_id') ?? ($edit->branch_id ?? '') }}">
                                <option value="">Select Branch</option>
                            </select>

                            @error('branch_id')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                    @php
                        $authEmp = Auth::guard('employees')->user();
                        $isTeamMember = $authEmp && (
                            (!empty($authEmp->created_by) && (string)$authEmp->created_by !== '0') ||
                            (!empty($authEmp->parent_id) && (string)$authEmp->parent_id !== '0') ||
                            ($authEmp->employee_code !== 'EMP-001')
                        );
                        $defaultEmpId = $isTeamMember ? $authEmp->id : '';
                    @endphp
                    {{-- Employee name --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Select Employee <span class="text-danger">*</span></label>
                            <select id="employee_id"
                                class="form-control select2 search_by_employee @error('employee_id') is-invalid @enderror"
                                @if (!$isTeamMember) name="employee_id" @endif
                                data-selectedemployeeid="{{ old('employee_id', $edit->employee_id ?? $defaultEmpId) }}"
                                data-exclude-contractor="1"
                                @if ($isTeamMember) disabled @endif>
                                <option value="">Select Employee</option>
                            </select>
                            @if ($isTeamMember)
                                <input type="hidden" name="employee_id" value="{{ old('employee_id', $edit->employee_id ?? $defaultEmpId) }}">
                            @endif

                            @error('employee_id')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                    {{-- leave Name --}}
                    {{-- <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label"> Select Leave Type <span class="text-danger">*</span> </label>
                            <select id="leave_type_id"
                                class="form-control select2 @error('leave_type_id') is-invalid @enderror"
                                name="leave_type_id">
                                <option value="">Select Leave Type</option>
                                @foreach ($leaveTypes as $leaveType)
                                    <option value="{{ $leaveType->id }}"
                                        {{ old('leave_type_id', $edit->leave_type_id ?? '') == $leaveType->id ? 'selected' : '' }}>
                                        {{ $leaveType->full_name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('leave_type_id')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div> --}}
                    {{-- Leave Type --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label mb-0"> Select Leave Type <span class="text-danger">*</span> </label>
                                <!-- Dynamic Available Leave Balance Display -->
                                <span id="leave_balance_container" style="display: none; font-weight: 600; color: #007bff; font-size: 0.8rem; background-color: rgba(0, 123, 255, 0.08); padding: 2px 8px; border-radius: 4px;">
                                    <i class="menu-icon ti ti-info-circle me-1" style="font-size: 0.9rem; vertical-align: middle;"></i> Available: <span id="leave_balance_value">0.0</span> Days
                                </span>
                            </div>
                            <select id="filter_leave_type"
                                class="form-control select2 @error('leave_type_id') is-invalid @enderror"
                                name="leave_type_id">
                                <option value="">Select Leave Type</option>
                                {{-- @foreach ($leaveTypes as $leaveType)
                                    <option value="{{ $leaveType->id }}"
                                        {{ old('leave_type_id', $edit->leave_type_id ?? '') == $leaveType->id ? 'selected' : '' }}>
                                        {{ $leaveType->full_name }}
                                    </option>
                                @endforeach --}}
                            </select>

                            @error('leave_type_id')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- halfday / fullday --}}

                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label"> Select Day <span class="text-danger">*</span> </label>
                            <select id="halfday_fullday"
                                class="form-control select2 @error('halfday_fullday') is-invalid @enderror"
                                name="halfday_fullday">
                                <option value="">Select Day</option>
                                @foreach ($leaveForDay as $key => $value)
                                    <option value="{{ $key }}"
                                        {{ old('halfday_fullday', $edit->halfday_fullday ?? '') == $key ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                            @error('halfday_fullday')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- single day /multiple day --}}

                    {{-- <div id="single_multiple_day" class="{{ $colums ?? 'col-12' }}col-sm-12 mb-2" style="display:none;">
                        <div class="form-group">
                            <label class="form-label">Single Day / Multiple Day <span class="text-danger">*</span></label>
                            <select id="singleday_multipleday"
                                class="form-control @error('singleday_multipleday') is-invalid @enderror"
                                name="singleday_multipleday"
                                data-selectedValue="{{ old('singleday_multipleday') ?? ($edit->singleday_multipleday ?? '') }}">
                                <option value="">Select Day</option>
                                @foreach ($leaveByDays as $key => $value)
                                    <option value="{{ $key }}"
                                        {{ old('singleday_multipleday', $edit->singleday_multipleday ?? '') == $key ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                            @error('singleday_multipleday')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div> --}}

                    {{-- first half / second half --}}
                    <div id="first_second_half" class="{{ $colums ?? 'col-12' }}" style="display:none;">
                        <div class="form-group">
                            <label class="form-label"> First Half / Second Half <span class="text-danger">*</span>
                            </label>
                            <select id="firsthalf_secondhalf"
                                class="form-control select2  @error('firsthalf_secondhalf') is-invalid @enderror"
                                name="firsthalf_secondhalf">
                                <option value="">Select Day</option>
                                @foreach ($leaveForHalfdays as $key => $value)
                                    <option value="{{ $key }}"
                                        {{ old('firsthalf_secondhalf', $edit->firsthalf_secondhalf ?? '') == $key ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                            @error('firsthalf_secondhalf')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>


                    {{-- From Date --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label" for="fromdate_time">From Date <span
                                    class="text-danger">*</span></label>
                            <input type="text"
                                class="form-control datetimepicker @error('fromdate_time') is-invalid @enderror"
                                name="fromdate_time"
                                value="{{ old('fromdate_time', isset($edit) && !empty($edit->fromdate_time) ? (old('halfday_fullday', $edit->halfday_fullday ?? '') == 'fullday' ? date('d-m-Y', strtotime($edit->fromdate_time)) : date('d-m-Y H:i', strtotime($edit->fromdate_time))) : '') }}"
                                placeholder="DD-MM-YYYY" id="fromdate_time" autocomplete="off" />

                            @error('fromdate_time')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                    {{-- To days  --}}
                    <div class="{{ $colums ?? 'col-12' }}" id="toDateContainer"
                        style="display: {{ old('singleday_multipleday', $edit->singleday_multipleday ?? '') == 'multiple' ? 'block' : 'none' }}">
                        <div class="form-group">
                            <label class="form-label" for="todate_time">To Date <span class="text-danger">*</span></label>
                            <input type="text"
                                class="form-control datetimepicker @error('todate_time') is-invalid @enderror"
                                name="todate_time"
                                value="{{ old('todate_time', isset($edit) && !empty($edit->todate_time) ? (old('halfday_fullday', $edit->halfday_fullday ?? '') == 'fullday' ? date('d-m-Y', strtotime($edit->todate_time)) : date('d-m-Y H:i', strtotime($edit->todate_time))) : '') }}"
                                placeholder="DD-MM-YYYY" id="todate_time" autocomplete="off" />

                            @error('todate_time')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    @if (!$company_id)
                        {{-- status --}}
                        <div class="{{ $colums ?? 'col-12' }}">
                            <div class="form-group">
                                <label class="form-label">Status</label>
                                <select class="form-control select2 w-100 @error('status') is-invalid @enderror"
                                    name="status" required id="statusSelect">
                                    <option disabled>Select Status</option>
                                    <option value="pending" style="color: orange;" selected
                                        @if ((isset($edit) && $edit->status == 'pending') || old('status') == 'pending') selected @endif>
                                        Pending
                                    </option>
                                </select>
                            </div>
                        </div>
                    @else
                        <input type="hidden" class="form-control" name="status"
                            value="{{ old('status', 'pending') }}" />
                    @endif



                    {{-- Leave Reason --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label" for="leave_reason">Leave Reason <span
                                    class="text-danger">*</span></label>

                            <textarea rows="3" cols="3" class="form-control @error('leave_reason') is-invalid @enderror"
                                name="leave_reason" placeholder="Enter Leave Reason" id="leave_reason">{{ old('leave_reason', $edit->leave_reason ?? '') }}</textarea>

                            @error('leave_reason')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Attachment</label>
                            <div class="input-group">
                                <input type="file" name="attachment" id="attachment"
                                    class="form-control @error('attachment') is-invalid @enderror">
                                @if (isset($edit) && $edit?->attachment && $edit?->attachment_url)
                                    <a href="{{ $edit?->attachment_url ?? '#' }}" target="_blank"
                                        class="input-group-text" for="inputGroupFile02">View File</a>
                                @endif
                            </div>
                            @error('attachment')
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


@push('page_scripts')
    {{-- <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script> --}}
    <!-- datetimepicker jQuery CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.full.min.js">
    </script>

    <script type="text/javascript">
        $(document).ready(function() {
            // Function to query and show dynamic available leave balance for selected Employee & Leave Type
            function updatePendingLeaveBalance() {
                var employeeId = $('#employee_id').val();
                var leaveTypeId = $('#filter_leave_type').val();
                
                if (!employeeId || !leaveTypeId) {
                    $('#leave_balance_container').fadeOut();
                    return;
                }
                
                $.ajax({
                    url: "{{ route('leave-application.index') }}",
                    type: "GET",
                    data: {
                        action: 'get_balance',
                        employee_id: employeeId,
                        leave_type_id: leaveTypeId
                    },
                    success: function(response) {
                        if (response && response.balance !== undefined) {
                            $('#leave_balance_value').text(response.balance);
                            $('#leave_balance_container').fadeIn();
                        } else {
                            $('#leave_balance_container').fadeOut();
                        }
                    },
                    error: function() {
                        $('#leave_balance_container').fadeOut();
                    }
                });
            }

            // Trigger when employee or leave type changes
            $(document).on('change', '#employee_id, #filter_leave_type', function() {
                updatePendingLeaveBalance();
            });

            // Initial trigger (wait slightly for select2 options to initialize/load)
            setTimeout(function() {
                updatePendingLeaveBalance();
            }, 1200);

            // Function to initialize date-only picker (for fullday)
            function initializeDatePicker(selector, minDate) {
                $(selector).datetimepicker('destroy');
                $(selector).datetimepicker({
                    format: 'd-m-Y',
                    timepicker: false,
                    closeOnDateSelect: true,
                });
            }

            // Function to initialize datetime picker (for halfday)
            function initializeDateTimePicker(selector, minDate) {
                $(selector).datetimepicker('destroy');
                $(selector).datetimepicker({
                    format: 'd-m-Y H:i',
                    timepicker: true,
                    closeOnDateSelect: true,
                });
            }

            // Function to update picker based on day selection
            function updateDatePickers() {
                var selectedDay = $('#halfday_fullday').val();
                
                if (selectedDay === 'fullday') {
                    // Use date-only picker for fullday
                    initializeDatePicker('#fromdate_time');
                    initializeDatePicker('#todate_time');
                } else if (selectedDay === 'halfday') {
                    // Use datetime picker for halfday
                    initializeDateTimePicker('#fromdate_time');
                    initializeDateTimePicker('#todate_time');
                } else {
                    // Default to datetime picker
            initializeDateTimePicker('#fromdate_time');
            initializeDateTimePicker('#todate_time');
                }
            }

            // Initialize on page load
            updateDatePickers();

            // Update when day selection changes
            $('#halfday_fullday').on('change', function() {
                var selectedDay = $(this).val();
                updateDatePickers();
                
                // Update placeholder text
                if (selectedDay === 'fullday') {
                    $('#fromdate_time').attr('placeholder', 'DD-MM-YYYY');
                    $('#todate_time').attr('placeholder', 'DD-MM-YYYY');
                    
                    // Clear time portion if switching to fullday
                    var fromVal = $('#fromdate_time').val();
                    var toVal = $('#todate_time').val();
                    
                    if (fromVal && fromVal.includes(' ')) {
                        $('#fromdate_time').val(fromVal.split(' ')[0]);
                    }
                    if (toVal && toVal.includes(' ')) {
                        $('#todate_time').val(toVal.split(' ')[0]);
                    }
                } else if (selectedDay === 'halfday') {
                    $('#fromdate_time').attr('placeholder', 'DD-MM-YYYY HH:MM');
                    $('#todate_time').attr('placeholder', 'DD-MM-YYYY HH:MM');
                }
            });
            
            // Set initial placeholder based on current selection
            var initialDay = $('#halfday_fullday').val();
            if (initialDay === 'fullday') {
                $('#fromdate_time').attr('placeholder', 'DD-MM-YYYY');
                $('#todate_time').attr('placeholder', 'DD-MM-YYYY');
            } else {
                $('#fromdate_time').attr('placeholder', 'DD-MM-YYYY HH:MM');
                $('#todate_time').attr('placeholder', 'DD-MM-YYYY HH:MM');
            }

            // Helper function to parse date (handles both date-only and datetime formats)
            function parseDate(dateStr) {
                var parts = dateStr.split(/[- :]/);
                if (parts.length >= 5) {
                    // Has time component
                    return new Date(parts[2], parts[1] - 1, parts[0], parts[3] || 0, parts[4] || 0);
                } else {
                    // Date only
                    return new Date(parts[2], parts[1] - 1, parts[0], 0, 0);
                }
            }

            // Set initial min date for to date based on from date
            (function setInitialMinDateForEdit() {
                var fromDateVal = $('#fromdate_time').val();
                if (fromDateVal) {
                    var fromDate = parseDate(fromDateVal);
                    var selectedDay = $('#halfday_fullday').val();
                    
                    if (selectedDay === 'fullday') {
                        initializeDatePicker('#todate_time', fromDate);
                    } else {
                    initializeDateTimePicker('#todate_time', fromDate);
                    }
                }
            })();

            $('#fromdate_time').on('change', function() {
                var fromDate = $(this).val();
                var selectedDay = $('#halfday_fullday').val();
                
                if (fromDate) {
                    var dateObj = parseDate(fromDate);
                    
                    if (selectedDay === 'fullday') {
                        initializeDatePicker('#todate_time', dateObj);
                    } else {
                    initializeDateTimePicker('#todate_time', dateObj);
                    }
                }
            });

            $('#todate_time').on('blur', function() {
                var fromVal = $('#fromdate_time').val();
                var toVal = $('#todate_time').val();
                var selectedDay = $('#halfday_fullday').val();

                if (fromVal && toVal) {
                    var fromDate = parseDate(fromVal);
                    var toDate = parseDate(toVal);

                    if (toDate < fromDate) {
                        showToastError('To Date cannot be earlier than From Date.');

                        // Format based on day type
                        if (selectedDay === 'fullday') {
                            var f = fromVal.split(/[- :]/);
                            $('#todate_time').val(('0' + f[0]).slice(-2) + '-' + ('0' + f[1]).slice(-2) + '-' + f[2]);
                        } else {
                            var f = fromVal.split(/[- :]/);
                        var formatted = ('0' + f[0]).slice(-2) + '-' +
                            ('0' + f[1]).slice(-2) + '-' +
                            f[2] + ' ' +
                                ('0' + (f[3] || 0)).slice(-2) + ':' +
                                ('0' + (f[4] || 0)).slice(-2);
                        $('#todate_time').val(formatted);
                        }
                    }
                }
            });

            function calculateSelectedLeaveDays() {
                var selectedDay = $('#halfday_fullday').val();
                if (selectedDay === 'halfday') {
                    return 0.5;
                }

                var fromVal = $('#fromdate_time').val();
                var toVal = $('#todate_time').val();
                if (!fromVal) {
                    return 0;
                }
                if (!toVal) {
                    toVal = fromVal;
                }

                var fromDate = parseDate(fromVal);
                var toDate = parseDate(toVal);
                return Math.floor((toDate - fromDate) / (1000 * 60 * 60 * 24)) + 1;
            }

            $('form').on('submit', function(e) {
                var fromVal = $('#fromdate_time').val();
                var toVal = $('#todate_time').val();
                var selectedDay = $('#halfday_fullday').val();

                if (fromVal && toVal && selectedDay !== 'halfday') {
                    var fromDate = parseDate(fromVal);
                    var toDate = parseDate(toVal);

                    if (toDate < fromDate) {
                        e.preventDefault();
                        showToastError('To Date must be greater than or equal to From Date.');
                        return false;
                    }
                }

                if ($('#leave_balance_container').is(':visible')) {
                    var availableBalance = parseFloat($('#leave_balance_value').text());
                    var selectedLeaveDays = calculateSelectedLeaveDays();

                    if (!isNaN(availableBalance) && selectedLeaveDays > availableBalance) {
                        e.preventDefault();
                        showToastError('You only have ' + availableBalance + ' days available. You are trying to apply for ' + selectedLeaveDays + ' days.');
                        return false;
                    }
                }
            });


            function showToastError(message) {
                toastr.error(message);
            }

        });
    </script>



    <script>
        function previewImage(event, previewId) {
            const reader = new FileReader();
            const imageField = document.getElementById(previewId);

            reader.onload = function() {
                imageField.src = reader.result;
                imageField.style.display = 'block';
            }

            if (event.target.files[0]) {
                reader.readAsDataURL(event.target.files[0]);
            }
        }
    </script>


    @if (!$company_id)
        @include('utils.getCompany')
    @endif


    @include('utils.getEmployee')
    @include('utils.getBranch')
    @include('utils.getLeaveType')



    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const fromDateInput = document.getElementById('fromdate_time');
            const toDateInput = document.getElementById('todate_time');
            const daySelect = document.getElementById('halfday_fullday');
            const toDateField = document.getElementById('toDateField');

            /*
            const fromDateTimePicker = flatpickr(fromDateInput, {
                enableTime: true,
                dateFormat: "d-m-Y H:i",
                minDate: "today",
                time_24hr: true,
                onChange: function(selectedDates) {
                    if (toDateField.style.display !== 'none') {
                        toDateTimePicker.set('minDate', selectedDates[0]);
                    }
                }
            });

            const toDateTimePicker = flatpickr(toDateInput, {
                enableTime: true,
                dateFormat: "d-m-Y H:i",
                minDate: "today",
                time_24hr: true,
                onChange: function(selectedDates, dateStr, instance) {
                    const fromDate = flatpickr.parseDate(fromDateInput.value, "d-m-Y H:i");
                    const toDate = flatpickr.parseDate(dateStr, "d-m-Y H:i");

                    if (fromDate && toDate && toDate < fromDate) {
                        alert("To Date cannot be earlier than From Date.");
                        instance.clear();
                    }
                }
            });
            */

            // function  () {
            //     const selectedValue = daySelect.value;
            //     if (selectedValue === 'halfday') {
            //         toDateField.style.display = 'none';
            //     } else {
            //         toDateField.style.display = 'block';
            //         toDateTimePicker.open();
            //     }
            // }




        });
    </script>

    <!-- Optional jQuery version (only if you want both) -->
    <script>
        $(document).ready(function() {
            function toggleToDateField() {
                var selectedValue = $('#halfday_fullday').val();
                if (selectedValue.toLowerCase() === 'halfday') {
                    $('#toDateContainer').hide();
                } else {
                    $('#toDateContainer').show();
                }
            }

            // Run on page load
            toggleToDateField();

            // Run on change
            $('#halfday_fullday').on('change', function() {
                toggleToDateField();
            });
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const halfDaySelect = document.getElementById('halfday_fullday');
            const singleMultipleDay = document.getElementById('single_multiple_day');
            const firstSecondHalf = document.getElementById('first_second_half');

            function toggleLeaveInputs() {
                const selectedValue = halfDaySelect.value;

                if (selectedValue === 'half') {

                    singleMultipleDay.style.display = 'none';
                    firstSecondHalf.style.display = 'block';
                } else if (selectedValue === 'full') {

                    firstSecondHalf.style.display = 'none';
                    singleMultipleDay.style.display = 'block';
                } else {

                    singleMultipleDay.style.display = 'block';
                    firstSecondHalf.style.display = 'block';
                }
            }

            toggleLeaveInputs();

            halfDaySelect.addEventListener('change', toggleLeaveInputs);
        });

        $(document).ready(function() {
            function toggleLeaveFields() {
                var selectedValue = $('#halfday_fullday').val();

                if (selectedValue === 'halfday') {
                    $('#first_second_half').show();
                    $('#single_multiple_day').hide();
                    $('#todate_time').hide();
                } else if (selectedValue === 'fulalday') {
                    $('#first_second_half').hide();
                    $('#single_multiple_day').show();
                    $('#fromdate_time').show();
                } else {

                    $('#first_second_half').hide();
                    $('#single_multiple_day').hide();
                }
            }

            toggleLeaveFields();

            $('#halfday_fullday').on('change', function() {
                toggleLeaveFields();
            });
        });

        $(document).ready(function() {
            function toggleToDateField() {
                var selectedValue = $('#halfday_fullday').val();
                if (selectedValue === 'halfday') {
                    $('#todate_time').hide().val('');
                } else {
                    $('#todate_time').show();
                }
            }

            function toggleLeaveFields() {
                var selectedValue = $('#halfday_fullday').val();

                // Show/hide relevant fields
                if (selectedValue === 'halfday') {
                    $('#first_second_half').show();
                    $('#single_multiple_day').hide();
                } else if (selectedValue === 'fullday') {
                    $('#first_second_half').hide();
                    $('#single_multiple_day').show();
                } else {
                    $('#first_second_half').hide();
                    $('#single_multiple_day').hide();
                }

                // Check single/multiple day selection
                var dayType = $('#singleday_multipleday').val();
                if (dayType === 'singleday') {
                    $('#toDateContainer').hide();
                    $('#todate_time').val('');
                } else if (dayType === 'multiple') {
                    $('#toDateContainer').show();
                }
            }

            // Bind change events
            $('#halfday_fullday, #singleday_multipleday').change(function() {
                toggleLeaveFields();
                toggleToDateField();
            });

            // Call on page load
            toggleLeaveFields();
            toggleToDateField();


        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {

            function updateToDateVisibility() {
                const halfdayOrFullday = halfdayFullDaySelect.value;
                const singleOrMultiple = singleMultipleDaySelect.value;

                if (halfdayOrFullday === 'halfday') {
                    toDateContainer.style.display = 'none';
                    singleMultipleDayContainer.style.display = 'none';
                } else if (halfdayOrFullday === 'fullday') {
                    singleMultipleDayContainer.style.display = 'block';

                    // if (singleOrMultiple === 'singleday') {
                    //     toDateContainer.style.display = 'none';
                    // } else if (singleOrMultiple === 'multipleday') {
                    //     toDateContainer.style.display = 'block';
                    // } else {
                    //     toDateContainer.style.display = 'none';
                    // }
                } else {

                    toDateContainer.style.display = 'none';
                    singleMultipleDayContainer.style.display = 'none';
                }
            }

            updateToDateVisibility();


            halfdayFullDaySelect.addEventListener('change', updateToDateVisibility);
            // singleMultipleDaySelect.addEventListener('change', updateToDateVisibility);


            // const selectedValue = singleMultipleDaySelect.dataset.selectedvalue;
            // if (selectedValue) {
            //     singleMultipleDaySelect.value = selectedValue;
            // }
        });
    </script>

@endpush
