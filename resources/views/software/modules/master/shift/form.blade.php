@extends('software.layout.app')

@php
    $i = 0;
    $tabIndex = 0;
    $colums = 'col-md-3 col-sm-12 mb-2';
    $page_title = isset($modules['title']) ? $modules['title'] : null;
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $company_id = isset($modules['company_id']) ? $modules['company_id'] : null;
@endphp
@section('title', $page_title)

@section('page_leavel_style')
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
                    <input type="hidden" name="edit_id" value="{{ $edit?->id ?? '0'}}" >
                @endisset

                <div class="row">

                    @if (!$company_id)
                        <div class="{{ $colums ?? 'col-12' }}">
                            <div class="form-group @error('company_id') is-invalid @enderror">
                                <label class="form-label">Select Company <span class="text-danger">*</span></label>
                                <select id="company_id"
                                    class="form-control select2 search_by_company @error('company_id') is-invalid @enderror"
                                    name="company_id"
                                    data-selectedCompanyId="{{ old('company_id') ?? ($edit->company_id ?? '') }}"
                                    showBranch="branchDiv" tabindex="{{ $tabIndex = $tabIndex + 1 }}">
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

                    <div class="{{ $colums ?? 'col-12' }} branchDiv" style="display:none;">
                        <div class="form-group">
                            <label class="form-label">Select Branch <span class="text-danger">*</span></label>
                            <select id="branch_id"
                                class="form-control select2 search_by_branch @error('branch_id') is-invalid @enderror"
                                name="branch_id" data-selectedbranchid="{{ old('branch_id') ?? ($edit->branch_id ?? '') }}"
                                tabindex="{{ $tabIndex = $tabIndex + 1 }}" data-forceReload=true>
                                <option value="">Select Branch</option>
                            </select>

                            @error('branch_id')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Name --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label"> Name <span class="text-danger">*</span> </label>
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                                name="name" value="{{ isset($edit) && $edit?->name ? $edit?->name : old('name') }}"
                                placeholder="Enter Name" tabindex="{{ $tabIndex = $tabIndex + 1 }}">
                            @error('name')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Punch In Minimum --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Punch In</label>
                            <input id="punch_in_minimum" type="text"
                                class="form-control time-mask @error('punch_in_minimum') is-invalid @enderror"
                                name="punch_in_minimum"
                                value="{{ isset($edit) && $edit?->punch_in_minimum ? $edit?->punch_in_minimum : old('punch_in_minimum') }}"
                                placeholder="hh:mm:ss" min="00:01:01" max="23:59:59"
                                tabindex="{{ $tabIndex = $tabIndex + 1 }}">
                            @error('punch_in_minimum')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Punch Out --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label"> Punch Out </label>
                            <input id="punch_out" type="text"
                                class="form-control time-mask @error('punch_out') is-invalid @enderror" name="punch_out"
                                value="{{ isset($edit) && $edit?->punch_out ? $edit?->punch_out : old('punch_out') }}"
                                placeholder="hh:mm:ss" min="00:01:01" max="23:59:59"
                                tabindex="{{ $tabIndex = $tabIndex + 1 }}">
                            @error('punch_out')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Auto Punch Out --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label"> Auto Punch Out </label>
                            <input id="auto_punch_out" type="text"
                                class="form-control time-mask @error('auto_punch_out') is-invalid @enderror"
                                name="auto_punch_out"
                                value="{{ isset($edit) && $edit?->auto_punch_out ? $edit?->auto_punch_out : old('auto_punch_out') }}"
                                placeholder="hh:mm:ss" min="00:01:01" max="23:59:59"
                                tabindex="{{ $tabIndex = $tabIndex + 1 }}">
                            @error('auto_punch_out')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- In/Out Grace Period --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label"> In/Out Grace Period</label>
                            <div class="input-group @error('in_out_grace_period') is-invalid @enderror">
                                <input id="in_out_grace_period" type="number"
                                    class="form-control @error('in_out_grace_period') is-invalid @enderror number_only"
                                    name="in_out_grace_period"
                                    value="{{ isset($edit) && $edit?->in_out_grace_period ? $edit?->in_out_grace_period : old('in_out_grace_period' ?? 0) }}"
                                    min="0" max="60" placeholder="Enter In/Out Grace Period"
                                    tabindex="{{ $tabIndex = $tabIndex + 1 }}">
                                <span class="input-group-text">minutes</span>
                            </div>
                            @error('in_out_grace_period')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>



                    {{-- Employee Maximum Working Hours --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label"> Employee Maximum Working Hours </label>
                            <div class="input-group @error('grace_period') is-invalid @enderror">
                                <input id="employee_max_working_hours" type="text"
                                    class="form-control @error('employee_max_working_hours') is-invalid @enderror number_only"
                                    name="employee_max_working_hours"
                                    value="{{ isset($edit) && $edit?->employee_max_working_hours ? $edit?->employee_max_working_hours : old('employee_max_working_hours', 0) }}"
                                    min="0" max="24" placeholder="Enter Employee Maximum Working Hours"
                                    tabindex="{{ $tabIndex = $tabIndex + 1 }}">
                                <span class="input-group-text">hours</span>
                            </div>
                            @error('employee_max_working_hours')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label"> Working Hour <span class="text-danger">*</span></label>
                            <input id="working_hour" type="text"
                                class="form-control time-mask @error('working_hour') is-invalid @enderror"
                                name="working_hour"
                                value="{{ isset($edit) && $edit?->working_hour ? $edit?->working_hour : old('working_hour') }}"
                                placeholder="hh:mm:ss" min="00:01:01" max="23:59:59"
                                    tabindex="{{ $tabIndex = $tabIndex + 1 }}">
                            @error('working_hour')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label"> Breaking Hour <span class="text-danger">*</span></label>
                            <input id="breaking_hour" type="text"
                                class="form-control time-mask @error('breaking_hour') is-invalid @enderror"
                                name="breaking_hour"
                                value="{{ isset($edit) && $edit?->breaking_hour ? $edit?->breaking_hour : old('breaking_hour') }}"
                                placeholder="hh:mm:ss" min="00:01:01" max="23:59:59"
                                    tabindex="{{ $tabIndex = $tabIndex + 1 }}">
                            @error('breaking_hour')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label"> Half Day Hour <span class="text-danger">*</span></label>
                            <input id="half_day_hour" type="text"
                                class="form-control time-mask @error('half_day_hour') is-invalid @enderror"
                                name="half_day_hour"
                                value="{{ isset($edit) && $edit?->half_day_hour ? $edit?->half_day_hour : old('half_day_hour') }}"
                                placeholder="hh:mm:ss" min="00:01:01" max="23:59:59"
                                    tabindex="{{ $tabIndex = $tabIndex + 1 }}">
                            @error('half_day_hour')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label"> Present Day Hour <span class="text-danger">*</span></label>
                            <input id="present_day_hour" type="text"
                                class="form-control time-mask @error('present_day_hour') is-invalid @enderror"
                                name="present_day_hour"
                                value="{{ isset($edit) && $edit?->present_day_hour ? $edit?->present_day_hour : old('present_day_hour') }}"
                                placeholder="hh:mm:ss" min="00:01:01" max="23:59:59"
                                    tabindex="{{ $tabIndex = $tabIndex + 1 }}">
                            @error('present_day_hour')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>


                    {{-- Monitor by --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label"> Monitor by </label>
                            <select
                                class="form-control select2 w-100 @error('monitor_by') is-invalid @enderror search_by_employee"
                                name="monitor_by" tabindex="{{ $tabIndex = $tabIndex + 1 }}"
                                data-selectedemployeeid="{{ isset($edit) && $edit?->monitor_by ? $edit?->monitor_by : old('monitor_by', 0) }}">
                                <option disabled selected>Select monitor by</option>
                            </select>
                        </div>
                    </div>


                    {{-- Status --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select class="form-control select2 w-100 @error('status') is-invalid @enderror"
                                name="status" required tabindex="{{ $tabIndex = $tabIndex + 1 }}">
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

@section('page_leavel_script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cleave.js/1.6.0/cleave.min.js"></script>
@endsection

@push('page_scripts')

    <script type="text/javascript">
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
        });
    </script>

    @if (!$company_id)
        @include('utils.getCompany')
    @endif
    @include('utils.getEmployee')


@endpush
