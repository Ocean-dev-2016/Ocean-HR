@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : null;
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $company_id = isset($modules['company_id']) ? $modules['company_id'] : null;
    $selectedTeamPersonIds = isset($edit) && $edit->team_person_ids ? explode(',', $edit->team_person_ids) : (old('team_person_ids') ?? []);
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
                    <input type="hidden" name="edit_id" value="{{ $edit?->id }}" />
                @endisset

                <div class="row">
                    @if (!$company_id)
                        <div class="col-md-3 col-sm-12 mb-2">
                            <div class="form-group">
                                <label class="form-label">Select Company <span class="text-danger">*</span></label>
                                <select id="company_id"
                                    class="form-control select2 search_by_company @error('company_id') is-invalid @enderror"
                                    name="company_id"
                                    showBranch="branchDiv"
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

                    <div class="col-md-3 col-sm-12 mb-2 branchDiv" style="display:none;">
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

                    <div class="col-md-3 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Expense Category <span class="text-danger">*</span></label>
                            <select id="expense_category_id"
                                class="form-control select2 @error('expense_category_id') is-invalid @enderror"
                                name="expense_category_id"
                                data-selectedcategoryid="{{ old('expense_category_id') ?? ($edit->expense_category_id ?? '') }}">
                                <option value="">Select Expense Category</option>
                            </select>
                            @error('expense_category_id')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Expense SubCategory Name <span class="text-danger">*</span></label>
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                                name="name" value="{{ isset($edit) && $edit?->name ? $edit?->name : old('name') }}"
                                placeholder="Enter Expense SubCategory Name">
                            @error('name')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Expense Type <span class="text-danger">*</span></label>
                            <select id="expense_type" class="form-control select2 @error('expense_type') is-invalid @enderror"
                                name="expense_type">
                                <option value="">Select Expense Type</option>
                                @foreach ($expense_type ?? [] as $key => $value)
                                    <option value="{{ $key }}"
                                        @if (isset($edit)) @if ($edit->expense_type == $key) {{ 'selected' }} @endif
                                    @else @if (old('expense_type', 'General') == $key) {{ 'selected' }} @endif @endif>
                                        {{ $value }}</option>
                                @endforeach
                            </select>
                            @error('expense_type')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Select employee(s) <span class="text-danger">*</span></label>
                            <select id="team_person_ids" class="form-control select2 search_by_employee @error('team_person_ids') is-invalid @enderror"
                                name="team_person_ids[]" multiple
                                data-selectedemployeeid="{{ implode(',', $selectedTeamPersonIds) }}">
                                <option value="">Select employee(s)</option>
                            </select>
                            @error('team_person_ids')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-12 mb-3">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" id="is_image_required" name="is_image_required"
                                value="1" {{ (isset($edit) && $edit->is_image_required == 1) || old('is_image_required') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_image_required">
                                Image Required
                            </label>
                        </div>
                    </div>

                    {{-- Conditional Fields for General Type --}}
                    <div id="general_fields" class="expense-type-fields" style="display: none;">
                        <div class="col-md-6 col-sm-12 mb-3">
                            <div class="form-group">
                                <label class="form-label">Minimum Amount <span class="text-danger">*</span></label>
                                <input id="min_amount" type="number" step="0.01"
                                    class="form-control @error('min_amount') is-invalid @enderror" name="min_amount"
                                    value="{{ isset($edit) && $edit?->min_amount ? $edit?->min_amount : old('min_amount') }}"
                                    placeholder="Enter Minimum Amount">
                                @error('min_amount')
                                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12 mb-3">
                            <div class="form-group">
                                <label class="form-label">Maximum Amount <span class="text-danger">*</span></label>
                                <input id="max_amount" type="number" step="0.01"
                                    class="form-control @error('max_amount') is-invalid @enderror" name="max_amount"
                                    value="{{ isset($edit) && $edit?->max_amount ? $edit?->max_amount : old('max_amount') }}"
                                    placeholder="Enter Maximum Amount">
                                @error('max_amount')
                                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Conditional Fields for KM Type --}}
                    <div id="km_fields" class="expense-type-fields" style="display: none;">
                        <div class="col-md-6 col-sm-12 mb-3">
                            <div class="form-group">
                                <label class="form-label">Per KM Rate <span class="text-danger">*</span></label>
                                <input id="per_km_rate" type="number" step="0.01"
                                    class="form-control @error('per_km_rate') is-invalid @enderror" name="per_km_rate"
                                    value="{{ isset($edit) && $edit?->per_km_rate ? $edit?->per_km_rate : old('per_km_rate') }}"
                                    placeholder="Enter Per KM Rate">
                                @error('per_km_rate')
                                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Conditional Fields for Food Type --}}
                    <div id="food_fields" class="expense-type-fields" style="display: none;">
                        <div class="col-md-3 col-sm-12 mb-3">
                            <div class="form-group">
                                <label class="form-label">Fix Amount <span class="text-danger">*</span></label>
                                <input id="fix_amount" type="number" step="0.01"
                                    class="form-control @error('fix_amount') is-invalid @enderror" name="fix_amount"
                                    value="{{ isset($edit) && $edit?->fix_amount ? $edit?->fix_amount : old('fix_amount') }}"
                                    placeholder="Enter Fix Amount">
                                @error('fix_amount')
                                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-12 mb-3">
                            <div class="form-group">
                                <label class="form-label">From Time <span class="text-danger">*</span></label>
                                <input id="from_time" type="time"
                                    class="form-control @error('from_time') is-invalid @enderror" name="from_time"
                                    value="{{ isset($edit) && $edit?->from_time ? $edit?->from_time : old('from_time') }}">
                                @error('from_time')
                                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-12 mb-3">
                            <div class="form-group">
                                <label class="form-label">To Time <span class="text-danger">*</span></label>
                                <input id="to_time" type="time"
                                    class="form-control @error('to_time') is-invalid @enderror" name="to_time"
                                    value="{{ isset($edit) && $edit?->to_time ? $edit?->to_time : old('to_time') }}">
                                @error('to_time')
                                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select class="form-control select2 w-100 @error('status') is-invalid @enderror" name="status"
                                required>
                                <option disabled selected>Select Status</option>
                                @foreach (['active', 'inactive'] as $status)
                                    <option value="{{ $status }}"
                                        @if (isset($edit)) @if ($edit->status == $status) {{ 'selected' }} @endif
                                    @else @if (old('status', 'active') == $status) {{ 'selected' }} @endif @endif>
                                        {{ ucfirst($status) }}</option>
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

@push('page_scripts')
    <script>
        $(document).ready(function() {
            // Show/hide fields based on expense type
            function toggleExpenseTypeFields() {
                $('.expense-type-fields').hide();
                var expenseType = $('#expense_type').val();
                if (expenseType === 'General') {
                    $('#general_fields').show();
                } else if (expenseType === 'KM') {
                    $('#km_fields').show();
                } else if (expenseType === 'Food') {
                    $('#food_fields').show();
                }
            }

            // Initial toggle on page load
            toggleExpenseTypeFields();

            // Toggle on change
            $('#expense_type').on('change', function() {
                toggleExpenseTypeFields();
            });
        });
    </script>

    @if (!$company_id)
        @include('utils.getCompany')
    @endif
    @include('utils.getBranch')
    @include('utils.getExpenseCategory')
    @include('utils.getEmployee')
@endpush
