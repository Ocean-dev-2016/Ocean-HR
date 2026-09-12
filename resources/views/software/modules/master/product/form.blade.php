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

                    {{-- Operation --}}
                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label"> Operation <span class="text-danger">*</span> </label>
                            <select name="operation" id="operation" class="form-control select2 @error('operation') is-invalid @enderror" required>
                                <option value="">Select Operation</option>
                                @foreach($operations as $op)
                                    <option value="{{ $op }}" {{ (isset($edit) && $edit->operation == $op) || old('operation') == $op ? 'selected' : '' }}>{{ $op }}</option>
                                @endforeach
                            </select>
                            @error('operation')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    

                    {{-- Product Name --}}
                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label"> Product Name <span class="text-danger">*</span> </label>
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                                name="name" value="{{ isset($edit) && $edit?->name ? $edit?->name : old('name') }}"
                                placeholder="Enter Product Name">
                            @error('name')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Process --}}
                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label"> Process </label>
                            <input id="process" type="text" class="form-control @error('process') is-invalid @enderror"
                                name="process" value="{{ isset($edit) && $edit?->process ? $edit?->process : old('process') }}"
                                placeholder="Enter Process">
                            @error('process')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                    

                    
                    

                    {{-- Unit --}}
                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label"> Unit </label>
                            <select name="unit" id="unit" class="form-control select2 @error('unit') is-invalid @enderror">
                                <option value="">Select Unit</option>
                                <option value="KG" {{ (isset($edit) && $edit->unit == 'KG') || old('unit') == 'KG' ? 'selected' : '' }}>KG</option>
                                <option value="PCS" {{ (isset($edit) && $edit->unit == 'PCS') || old('unit') == 'PCS' ? 'selected' : '' }}>PCS</option>
                            </select>
                            @error('unit')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                    {{-- Rate --}}
                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label"> Rate </label>
                            <input id="rate" type="number" step="0.01" class="form-control @error('rate') is-invalid @enderror"
                                name="rate" value="{{ isset($edit) && $edit?->rate !== null ? $edit?->rate : old('rate', '0.00') }}"
                                placeholder="Enter Rate">
                            @error('rate')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Ot text --}}
                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label"> OT Rate </label>
                            <input id="ot_text" type="text" class="form-control @error('ot_text') is-invalid @enderror"
                                name="ot_text" value="{{ isset($edit) && $edit?->ot_text ? $edit?->ot_text : old('ot_text') }}"
                                placeholder="Enter OT Rate">
                            @error('ot_text')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Rejection Rate --}}
                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label"> Rejection Rate </label>
                            <input id="rejection_rate" type="number" step="0.01" class="form-control @error('rejection_rate') is-invalid @enderror"
                                name="rejection_rate" value="{{ isset($edit) && $edit?->rejection_rate !== null ? $edit?->rejection_rate : old('rejection_rate', '0.00') }}"
                                placeholder="Enter Rejection Rate">
                            @error('rejection_rate')
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
    @if (!$company_id)
        @include('utils.getCompany')
    @endif

    <script>
        $(document).on('change', '.search_by_company', function() {
            let company_id = $(this).val();
            if (company_id) {
                $.ajax({
                    url: "{{ route('products.get-extra-data') }}",
                    type: "GET",
                    data: { company_id: company_id },
                    success: function(response) {
                        // Update Operation dropdown
                        let operationSelect = $('#operation');
                        operationSelect.empty().append('<option value="">Select Operation</option>');
                        $.each(response.operations, function(key, value) {
                            operationSelect.append('<option value="' + value + '">' + value + '</option>');
                        });

                        // Re-initialize select2
                        $('.select2').select2();
                    }
                });
            }
        });
    </script>
@endpush
