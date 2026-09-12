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
                    <div class="col-md-3 col-sm-12 mb-2">
                        <div class="form-group">
                            <label class="form-label">Select Employee <span class="text-danger">*</span></label>
                            <select id="employee_id"
                                class="form-control select2 search_by_employee @error('employee_id') is-invalid @enderror"
                                name="employee_id"
                                data-selectedEmployeeId="{{ old('employee_id') ?? ($edit->employee_id ?? ($preselectedEmployeeId ?? '')) }}">
                                <option value="">Select Employee</option>
                            </select>

                            @error('employee_id')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-12 mb-2">
                        <div class="form-group">
                            <label class="form-label">Select Assets Name <span class="text-danger">*</span></label>
                            <select id="assets_id"
                                class="form-control select2 search_by_assets @error('assets_id') is-invalid @enderror"
                                name="assets_id" data-selectedassetsid="{{ old('assets_id') ?? ($edit->assets_id ?? '') }}">
                                <option value="">Select Assets</option>
                            </select>



                            @error('assets_id')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                    {{-- Date --}}
                    <div class="col-md-3 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label"> Date <span class="text-danger">*</span> </label>

                            <input type="text" id="date" name="date"
                                class="form-control plan-form @error('date') is-invalid @enderror"
                                  value="{{ isset($edit) && $edit?->date ? $edit->date : (request()->isMethod('post') ? old('date') : '') }}"
                                placeholder="Date" />
                            @error('date')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                    {{-- Reference No --}}
                    <div class="col-md-3 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label"> Reference No <span class="text-danger">*</span> </label>
                            <input id="reference_no" type="text"
                                class="form-control @error('reference_no') is-invalid @enderror" name="reference_no"
                                value="{{ isset($edit) && $edit?->reference_no ? $edit?->reference_no : old('reference_no') }}"
                                placeholder="Enter Reference No">
                            @error('reference_no')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                    {{-- Attechment
                    <div class="col-md-3 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Attachment (PDF, Word, Image) <small class="text-muted">(Max:
                                    10MB)</small></label>
                            <input type="file" name="attachment"
                                class="form-control @error('attachment') is-invalid @enderror"
                                accept=".jpeg,.jpg,.png,.pdf,.doc,.docx">
                            @if (!empty($edit->attachment))
                                @php $filePath = public_path($edit->attachment); @endphp
                                @if (file_exists($filePath))
                                    <a href="{{ asset($edit->attachment) }}" download>Download Attachment</a>
                                @else
                                    <p>Attachment not found.</p>
                                @endif
                            @endif
                            @error('attachment')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div> --}}
                    {{-- Attachment --}}
                    <div class="col-md-3 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">
                                Attachment (PDF, Word, Image) <small class="text-muted">(Max: 10MB)</small>
                            </label>
                            <input type="file" name="attachment"
                                class="form-control @error('attachment') is-invalid @enderror"
                                accept=".jpeg,.jpg,.png,.pdf,.doc,.docx" onchange="previewAttachment(this)">

                            {{-- Existing Attachment Preview --}}
                            @if (!empty($edit->attachment))
                                @php $filePath = public_path($edit->attachment); @endphp
                                @if (file_exists($filePath))
                                    @php $ext = pathinfo($edit->attachment, PATHINFO_EXTENSION); @endphp
                                    @if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif']))
                                        <div class="mt-2">
                                            <img src="{{ asset($edit->attachment) }}" alt="Attachment"
                                                style="max-width:100%; height:auto;" />
                                        </div>
                                    @else
                                        <a href="{{ asset($edit->attachment) }}" download>Download Attachment</a>
                                    @endif
                                @else
                                    <p>Attachment not found.</p>
                                @endif
                            @endif

                            {{-- Image Preview for new upload --}}
                            <div id="attachmentPreview" class="mt-2"></div>

                            @error('attachment')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Description --}}
                    <div class="col-md-3 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea id="descrption" class="form-control @error('descrption') is-invalid @enderror" name="descrption"
                                placeholder="Enter descrption">{{ isset($edit) && $edit?->descrption ? $edit?->descrption : old('descrption') }}</textarea>
                            @error('descrption')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>


                    <div class="col-md-3 col-sm-12">
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
    <script src="{{ asset('software/vendor/libs/flatpickr/flatpickr.js') }}"></script>
    <script type="text/javascript">
        flatpickr("#date", {
            defaultDate: null,
            maxDate: "today",
            dateFormat: "Y-m-d"
        });
    </script>
    <script>
        function previewAttachment(input) {
            const preview = document.getElementById('attachmentPreview');
            preview.innerHTML = ''; // clear previous preview
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const ext = file.name.split('.').pop().toLowerCase();
                if (['jpg', 'jpeg', 'png', 'gif'].includes(ext)) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.style.maxWidth = '100%';
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
    </script>
    @if (!$company_id)
        @include('utils.getCompany')
    @endif
    @include('utils.getEmployee')

    @include('utils.getAssetsAllocation')
@endpush
