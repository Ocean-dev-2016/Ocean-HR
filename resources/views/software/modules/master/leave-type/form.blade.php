@extends('software.layout.app')

@php
    $i = 0;

    $page_title = isset($modules['title']) ? $modules['title'] : null;
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $company_id = isset($modules['company_id']) ? $modules['company_id'] : null;

@endphp
@section('title', $page_title)

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
                    {{-- Sort Name --}}
                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label"> Sort Name <span class="text-danger">*</span> </label>
                            <input id="sort_name" type="text" class="form-control @error('sort_name') is-invalid @enderror"
                                name="sort_name" value="{{ isset($edit) && $edit?->sort_name ? $edit?->sort_name : old('sort_name') }}"
                                placeholder="Enter Sort Name">
                            @error('sort_name')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Full Name --}}
                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label"> Full Name <span class="text-danger">*</span> </label>
                            <input id="full_name" type="text" class="form-control @error('full_name') is-invalid @enderror"
                                name="full_name" value="{{ isset($edit) && $edit?->full_name ? $edit?->full_name : old('full_name') }}"
                                placeholder="Enter Full Name">
                            @error('full_name')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Count --}}
                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label"> Count </label>
                            <input id="count" type="text" class="form-control @error('count') is-invalid @enderror"
                                name="count" value="{{ isset($edit) && $edit?->count ? $edit?->count : old('count') }}"
                                placeholder="Enter Count">
                            @error('count')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-12 mb-3">
                        <label><strong>Mode:</strong></label><br>
                        <div class="form-check form-check-inline">
                            <input type="radio" class="form-check-input" name="mode" value="1" id="company-pay"
                                {{ old('mode', $edit?->mode ?? '1') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="company-pay">Company Pay</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" class="form-check-input" name="mode" value="0" id="employee-pay"
                                {{ old('mode', $edit?->mode ?? '0') == '0' ? 'checked' : '' }}>
                            <label class="form-check-label" for="employee-pay">Employee Pay</label>
                        </div>
                    </div>

                    {{-- Carry Forward --}}
                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label d-block"><strong>Carry Forward:</strong></label>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="carry_forward" value="1" id="carry_forward"
                                    {{ old('carry_forward', $edit?->carry_forward ?? 0) == 1 ? 'checked' : '' }}>
                                <label class="form-check-label" for="carry_forward">Carry Forward</label>
                            </div>
                        </div>
                    </div>

                    {{-- Attachment Required Checkbox --}}
                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label d-block"><strong>Attachment Required:</strong></label>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="attachment_required" value="1" id="attachment_required"
                                    {{ old('attachment_required', $edit?->attachment_required ?? 0) == 1 ? 'checked' : '' }}>
                                <label class="form-check-label" for="attachment_required">Compulsory for this leave type</label>
                            </div>
                        </div>
                    </div>

                    {{-- Attachment Required Days --}}
                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label"><strong>Days:</strong></label>
                            <input id="attachment_required_days" type="number" class="form-control @error('attachment_required_days') is-invalid @enderror"
                                name="attachment_required_days" value="{{ isset($edit) ? $edit?->attachment_required_days : old('attachment_required_days', 0) }}"
                                placeholder="Enter min days for attachment" min="0">
                            @error('attachment_required_days')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                            <small class="text-muted"><i class="ti ti-info-circle"></i> Compulsory if leave duration exceeds these days. Use 0 to disable.</small>
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
    @if (!$company_id)
        @include('utils.getCompany')
    @endif

@endpush
