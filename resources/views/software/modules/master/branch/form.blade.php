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
@endsection

@section('content')
    <div class="px-1">
        <div class="d-lg-flex justify-content-lg-between flex-column flex-lg-row">
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

                    @if (!$company_id)
                        <div class="col-md-4 col-sm-12 mb-2">
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

                    {{-- Name --}}
                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label"> Name <span class="text-danger">*</span> </label>
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                                name="name" value="{{ isset($edit) && $edit?->name ? $edit?->name : old('name') }}"
                                placeholder="Enter Name">
                            @error('name')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label"> Prefix </label>
                            <input id="prefix" type="text" class="form-control @error('prefix') is-invalid @enderror"
                                name="prefix" value="{{ isset($edit) && $edit?->prefix ? $edit?->prefix : old('prefix') }}"
                                placeholder="Enter Prefix">
                            @error('prefix')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label"> Employee Code Start</label>
                            <input id="employee_code_start" type="text" class="form-control @error('employee_code_start') is-invalid @enderror"
                                name="employee_code_start" value="{{ old('employee_code_start', isset($edit) && $edit?->employee_code_start ? $edit?->employee_code_start : '') }}"
                                placeholder="Enter employee code start">
                            @error('employee_code_start')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label"> Canteen max hours </label>
                            <input id="canteen_max_time" type="text"
                                class="form-control time-mask @error('canteen_max_time') is-invalid @enderror"
                                name="canteen_max_time"
                                value="{{ isset($edit) && $edit?->canteen_max_time ? $edit?->canteen_max_time : old('canteen_max_time') }}"
                                placeholder="hh:mm:ss" min="00:01:01" max="23:59:59">
                            @error('canteen_max_time')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label"> Canteen min hours</label>
                            <input id="canteen_min_time" type="text"
                                class="form-control time-mask @error('canteen_min_time') is-invalid @enderror"
                                name="canteen_min_time"
                                value="{{ isset($edit) && $edit?->canteen_min_time ? $edit?->canteen_min_time : old('canteen_min_time') }}"
                                placeholder="hh:mm:ss" min="00:01:01" max="23:59:59">
                            @error('canteen_min_time')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Status --}}
                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select class="form-control select2 w-100 @error('status') is-invalid @enderror" name="status"
                                required>
                                <option disabled selected>Select Status</option>
                                @foreach (['active', 'inactive'] as $status)
                                    <option value="{{ $status }}"
                                        @if (isset($edit)) @if ($edit->status == $status) {{ 'selected' }} @endif
                                    @else @if (old('status', 'active') == $status) {{ 'selected' }} @endif @endif> {{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label"> Address </label>
                            <textarea id="branch_address" class="form-control @error('branch_address') is-invalid @enderror" name="branch_address"
                                placeholder="Enter branch address" rows="3">{{ isset($edit) && $edit?->branch_address ? $edit?->branch_address : old('branch_address') }}</textarea>
                            @error('canteen_min_time')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
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
@endsection

@push('page_scripts')
    @if (!$company_id)
        @include('utils.getCompany')
    @endif

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
@endpush
