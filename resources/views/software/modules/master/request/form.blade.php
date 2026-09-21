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

                    {{-- Request From Employee Name --}}
                    <div class="col-md-4 col-sm-12 mb-2">
                        <div class="form-group @error('request_from_employee_name') is-invalid @enderror">
                            <label class="form-label">Select From Employee <span class="text-danger">*</span></label>
                            <select id="request_from_employee_name"
                                class="form-control select2 @error('request_from_employee_name') is-invalid @enderror"
                                name="request_from_employee_name"
                                data-selectedemployeeid="{{ old('request_from_employee_name') ?? ($edit->request_from_employee_name ?? '') }}">
                                <option value="">Select Employee</option>
                            </select>

                        </div>
                        @error('request_from_employee_name')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    {{-- Request To Employee Name --}}
                    <div class="col-md-4 col-sm-12 mb-2">
                        <div class="form-group @error('request_to_employee_name') is-invalid @enderror">
                            <label class="form-label">Select To Employee <span class="text-danger">*</span></label>
                            <select id="request_to_employee_name"
                                class="form-control select2 @error('request_to_employee_name') is-invalid @enderror"
                                name="request_to_employee_name"
                                data-selectedemployeeid="{{ old('request_to_employee_name') ?? ($edit->request_to_employee_name ?? '') }}">
                                <option value="">Select Employee</option>
                            </select>

                        </div>
                        @error('request_to_employee_name')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    {{-- attechment --}}
                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">
                                Attechment (PDF, Word, Image) <small class="text-muted">(Max: 10MB)</small>
                            </label>
                            <input type="file" name="attechment"
                                class="form-control @error('attechment') is-invalid @enderror"
                                accept=".jpeg,.jpg,.png,.pdf,.doc,.docx" onchange="previewattechment(this)">

                            {{-- Existing attechment Preview --}}
                            @if (!empty($edit->attechment))
                                @php $filePath = public_path($edit->attechment); @endphp
                                @if (file_exists($filePath))
                                    @php $ext = pathinfo($edit->attechment, PATHINFO_EXTENSION); @endphp
                                    @if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif']))
                                        <div class="mt-2">
                                            <img src="{{ asset($edit->attechment) }}" alt="attechment"
                                                style="max-width:50%; height:50%;" />
                                        </div>
                                    @else
                                        <a href="{{ asset($edit->attechment) }}" download>Download attechment</a>
                                    @endif
                                @else
                                    <p>attechment not found.</p>
                                @endif
                            @endif

                            {{-- Image Preview for new upload --}}
                            <div id="attechmentPreview" class="mt-2"></div>

                            @error('attechment')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                    {{-- Remark --}}
                    <div class="col-md-4 col-sm-12 mb-2">
                        <div class="form-group">
                            <label class="form-label">Request Description</label>
                            <textarea id="request_description" class="form-control autosize @error('request_description') is-invalid @enderror"
                                name="request_description" rows="1" autocomplete="request_description"
                                placeholder="Enter Request Description">{{ isset($edit) ? $edit->request_description : old('request_description') }}</textarea>
                            @error('request_description')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
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
    <script>
        function previewattechment(input) {
            const preview = document.getElementById('attechmentPreview');
            preview.innerHTML = ''; // clear previous preview
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const ext = file.name.split('.').pop().toLowerCase();
                if (['jpg', 'jpeg', 'png', 'gif'].includes(ext)) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.style.maxWidth = '50%';
                        img.style.height = 'auto';
                        preview.appendChild(img);
                    }
                    reader.readAsDataURL(file);
                } else {
                    const link = document.createElement('a');
                    link.href = URL.createObjectURL(file);
                    link.download = file.name;
                    link.textContent = 'Download ' + file.name;
                    preview.appendChild(link);
                }
            }
        }
        function fetchEmployeesForRequestForm() {
            let company_id = $('select[name="company_id"]').val() || $('input[name="company_id"]').val() || $('meta[name="company_id"]').attr('value');
            if (!company_id) return;

            let fromSelected = $('#request_from_employee_name').data('selectedemployeeid') || $('#request_from_employee_name').val() || '';
            let toSelected = $('#request_to_employee_name').data('selectedemployeeid') || $('#request_to_employee_name').val() || '';

            $('#request_from_employee_name, #request_to_employee_name').html('<option value="">Loading...</option>');

            $.ajax({
                type: 'POST',
                url: '{{ url("api/get-employee") }}',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    company_id: company_id
                },
                success: function (response) {
                    if (response.status && Array.isArray(response.data)) {
                        let fromOptions = '<option value="">Select Employee</option>';
                        let toOptions = '<option value="">Select Employee</option>';

                        $.each(response.data, function (i, item) {
                            let isFromSelected = (fromSelected != '' && fromSelected.toString() == item.id.toString());
                            let isToSelected = (toSelected != '' && toSelected.toString() == item.id.toString());

                            let fromSel = isFromSelected ? 'selected' : '';
                            let toSel = isToSelected ? 'selected' : '';
                            let toDisabled = (fromSelected != '' && fromSelected.toString() == item.id.toString()) ? 'disabled' : '';

                            fromOptions += `<option value="${item.id}" ${fromSel}>${item.employee_code} - ${item.full_name}</option>`;
                            toOptions += `<option value="${item.id}" ${toSel} ${toDisabled}>${item.employee_code} - ${item.full_name}</option>`;
                        });

                        $('#request_from_employee_name').html(fromOptions).select2();
                        $('#request_to_employee_name').html(toOptions).select2();
                    } else {
                        $('#request_from_employee_name, #request_to_employee_name').html('<option value="">No Employees Found</option>').select2();
                    }
                },
                error: function (err) {
                    console.error('Employee fetch failed', err);
                    $('#request_from_employee_name, #request_to_employee_name').html('<option value="">Error loading employees</option>').select2();
                }
            });
        }

        function syncEmployeeDropdowns() {
            let fromId = $('#request_from_employee_name').val();
            let toId = $('#request_to_employee_name').val();

            $('#request_to_employee_name option').each(function() {
                let val = $(this).val();
                if (val && fromId && val.toString() === fromId.toString()) {
                    $(this).prop('disabled', true);
                } else {
                    $(this).prop('disabled', false);
                }
            });

            if (toId && fromId && toId.toString() === fromId.toString()) {
                $('#request_to_employee_name').val('').trigger('change');
            } else {
                $('#request_to_employee_name').select2();
            }
        }

        $(document).ready(function() {
            fetchEmployeesForRequestForm();

            $(document).on('change', 'select[name="company_id"], .search_by_company', function() {
                fetchEmployeesForRequestForm();
            });

            $(document).on('change', '#request_from_employee_name', function() {
                syncEmployeeDropdowns();
            });
        });
    </script>
    @if (!$company_id)
        @include('utils.getCompany')
    @endif
@endpush
