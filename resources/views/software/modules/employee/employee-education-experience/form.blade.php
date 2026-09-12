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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
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

                    {{-- Degree --}}
                    <div class="col-md-3 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label"> Degree <span class="text-danger">*</span> </label>
                            <input id="degree" type="text"
                                class="form-control @error('degree') is-invalid @enderror" name="degree"
                                value="{{ isset($edit) && $edit?->degree ? $edit?->degree : old('degree') }}"
                                placeholder="Enter Degree">
                            @error('degree')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Institute Name --}}
                    <div class="col-md-3 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label"> Institute Name <span class="text-danger">*</span> </label>
                            <input id="institution_name" type="text"
                                class="form-control @error('institution_name') is-invalid @enderror" name="institution_name"
                                value="{{ isset($edit) && $edit?->institution_name ? $edit?->institution_name : old('institution_name') }}"
                                placeholder="Enter Institute Name">
                            @error('institution_name')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Month Of Passing Year --}}
                    <div class="col-md-3 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label"> Month Of Passing Year <span class="text-danger">*</span> </label>
                            <input id="month_of_passing_year" type="text"
                                class="form-control @error('month_of_passing_year') is-invalid @enderror" name="month_of_passing_year"
                                value="{{ isset($edit) && $edit?->month_of_passing_year ? $edit?->month_of_passing_year : old('month_of_passing_year') }}"
                                placeholder="Enter Month Of Passing Year">
                            @error('month_of_passing_year')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Class Or % Marks --}}
                    <div class="col-md-3 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label"> Class Or % Marks <span class="text-danger">*</span> </label>
                            <input id="class_or_mark" type="text"
                                class="form-control @error('class_or_mark') is-invalid @enderror" name="class_or_mark"
                                value="{{ isset($edit) && $edit?->class_or_mark ? $edit?->class_or_mark : old('class_or_mark') }}"
                                placeholder="Enter Class Or % Marks">
                            @error('class_or_mark')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="divider">
                        <hr />
                    </div>
                    <h5>Previous Job Details Details</h5>
                    
                    {{-- Company Name --}}
                    <div class="col-md-3 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label"> Company Name <span class="text-danger">*</span> </label>
                            <input id="company_name" type="text"
                                class="form-control @error('company_name') is-invalid @enderror" name="company_name"
                                value="{{ isset($edit) && $edit?->company_name ? $edit?->company_name : old('company_name') }}"
                                placeholder="Enter Company Name">
                            @error('company_name')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Joining Date --}}
                    <div class="col-md-3 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label"> Joining Date <span class="text-danger">*</span> </label>
                            <input id="joining_date" type="text"
                                class="form-control @error('joining_date') is-invalid @enderror" name="joining_date"
                                value="{{ isset($edit) && $edit?->joining_date ? $edit?->joining_date : old('joining_date') }}"
                                placeholder="Enter Joining Date">
                            @error('joining_date')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Left Date --}}
                    <div class="col-md-3 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label"> Left Date <span class="text-danger">*</span> </label>
                            <input id="left_date" type="text"
                                class="form-control @error('left_date') is-invalid @enderror" name="left_date"
                                value="{{ isset($edit) && $edit?->left_date ? $edit?->left_date : old('left_date') }}"
                                placeholder="Enter Left Date">
                            @error('left_date')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Desiganation --}}
                    <div class="col-md-3 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label"> Desiganation <span class="text-danger">*</span> </label>
                            <input id="designation" type="text"
                                class="form-control @error('designation') is-invalid @enderror" name="designation"
                                value="{{ isset($edit) && $edit?->designation ? $edit?->designation : old('designation') }}"
                                placeholder="Enter Desiganation">
                            @error('designation')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- CTC Salary Details --}}
                    <div class="col-md-3 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label"> CTC Salary Details <span class="text-danger">*</span> </label>
                            <input id="ctc_salary" type="text"
                                class="form-control @error('ctc_salary') is-invalid @enderror" name="ctc_salary"
                                value="{{ isset($edit) && $edit?->ctc_salary ? $edit?->ctc_salary : old('ctc_salary') }}"
                                placeholder="Enter CTC Salary Details">
                            @error('ctc_salary')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="divider">
                        <hr />
                    </div>
                    <h5>Promotion And Transfer Details</h5>

                    <div class="col-md-3 col-sm-12 mb-2">
                        <div class="form-group">
                            <label class="form-label">Select Department <span class="text-danger">*</span></label>
                            <select id="department_name"
                                class="form-control select2 search_by_department @error('department_name') is-invalid @enderror"
                                name="department_name"
                                data-append="search_by_department"
                                data-selecteddepartmentid="{{ old('department_name') ?? ($edit->department_name ?? '') }}">
                                <option value="">Select Department</option>
                            </select>

                            @error('department_name')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-12 mb-2">
                        <div class="form-group">
                            <label class="form-label">Select Designation <span class="text-danger">*</span></label>
                            <select id="designation_name"
                                class="form-control select2 search_by_designation @error('designation_name') is-invalid @enderror"
                                name="designation_name"
                                data-append="search_by_designation"
                                data-selectedDesignationId="{{ old('designation_name') ?? ($edit->designation_name ?? '') }}">
                                <option value="">Select Designation</option>
                            </select>

                            @error('designation_name')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-12">
                        <div class="form-group">
                            <label class="form-label">Unit Change</label>
                            <select class="form-control select2 w-100 @error('unit_change') is-invalid @enderror"
                                name="unit_change" required>
                                <option disabled selected>Select Unit Change</option>
                                @foreach (['IT','HR','Finance','Sales','R&D','Support'] as $unit_change)
                                    <option value="{{ $unit_change }}"
                                        @if (isset($edit)) @if ($edit->unit_change == $unit_change) {{ 'selected' }} @endif
                                    @else @if (old('unit_change', 'active') == $unit_change) {{ 'selected' }} @endif @endif> {{ ucfirst($unit_change) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Effective Date --}}
                    <div class="col-md-3 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label"> Effective Date <span class="text-danger">*</span> </label>

                            <input type="text" id="effective_date" name="effective_date"
                                class="form-control plan-form @error('effective_date') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->effective_date ? $edit->effective_date : old('effective_date') }}"
                                placeholder="Date" />
                            @error('effective_date')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>


                    <div class="divider">
                        <hr />
                    </div>
                    <h5>Document Type</h5>
                    <div class="col-md-3 col-sm-12 mb-2">
                        <div class="form-group">
                            <label class="form-label">Select Document Type <span class="text-danger">*</span></label>
                            <select id="document_type"
                                class="form-control select2 search_by_document_type_id @error('document_type') is-invalid @enderror"
                                name="document_type"
                                data-append="search_by_document_type_id"
                                data-selectedDocumentTypeId="{{ old('document_type') ?? ($edit->document_type ?? '') }}">
                                <option value="">Select Document Type</option>
                            </select>

                            @error('document_type')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Document Name --}}
                    <div class="col-md-3 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label"> Document Name <span class="text-danger">*</span> </label>
                            <input id="document_name" type="text"
                                class="form-control @error('document_name') is-invalid @enderror" name="document_name"
                                value="{{ isset($edit) && $edit?->document_name ? $edit?->document_name : old('document_name') }}"
                                placeholder="Enter Document Name">
                            @error('document_name')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Attachment
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

                    <div class="divider">
                        <hr />
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
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>

    <script type="text/javascript">
        flatpickr("#joining_date", {
            defaultDate: null,
            maxDate: "today",
            dateFormat: "d-m-Y"
        });
        flatpickr("#left_date", {
            defaultDate: null,
            maxDate: "today",
            dateFormat: "d-m-Y"
        });
        flatpickr("#effective_date", {
            defaultDate: null,
            maxDate: "today",
            dateFormat: "d-m-Y"
        });
        flatpickr("#month_of_passing_year", {
            plugins: [
                new monthSelectPlugin({
                    shorthand: true,   // display short month names
                    dateFormat: "m-Y", // e.g. 09-2025
                    altFormat: "F Y"   // e.g. September 2025
                })
            ],
            maxDate: "today"
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
    @include('utils.getDepartment')
    @include('utils.getDesignation')
    @include('utils.getDocumentType')
@endpush
