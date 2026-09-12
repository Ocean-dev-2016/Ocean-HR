@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : null;
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $company_id = isset($modules['company_id']) ? $modules['company_id'] : null;
    $colums = 'col-md-3 col-sm-12 mb-2';
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

                    <div class="{{ $colums ?? 'col-12' }} branchDiv" style="display:none;">
                        <div class="form-group">
                            <label class="form-label">Select Branch</label>
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

                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Select Employee <span class="text-danger">*</span></label>
                            <select id="team_person_id"
                                class="form-control select2 search_by_employee @error('team_person_id') is-invalid @enderror"
                                name="team_person_id"
                                data-selectedemployeeid="{{ old('team_person_id') ?? ($edit->team_person_id ?? '') }}">
                                <option value="">Select Employee</option>
                            </select>
                            @error('team_person_id')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="{{ $colums ?? 'col-12' }}">
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

                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Expense SubCategory <span class="text-danger">*</span></label>
                            <select id="expense_subcategory_id"
                                class="form-control select2 @error('expense_subcategory_id') is-invalid @enderror"
                                name="expense_subcategory_id"
                                data-selectedsubcategoryid="{{ old('expense_subcategory_id') ?? ($edit->expense_subcategory_id ?? '') }}">
                                <option value="">Select Expense SubCategory</option>
                            </select>
                            @error('expense_subcategory_id')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="text" id="date" name="date"
                                class="form-control expense_date_picker @error('date') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->date ? $edit->date->format('d/m/Y') : old('date', date('d/m/Y')) }}"
                                placeholder="DD/MM/YYYY">
                            @error('date')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Request Amount <span class="text-danger">*</span></label>
                            <input id="req_amount" type="number" step="0.01"
                                class="form-control @error('req_amount') is-invalid @enderror" name="req_amount"
                                value="{{ isset($edit) && $edit?->req_amount ? $edit?->req_amount : old('req_amount') }}"
                                placeholder="Enter Request Amount">
                            @error('req_amount')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Remark</label>
                            <textarea id="remark" class="form-control @error('remark') is-invalid @enderror" name="remark"
                                rows="3" placeholder="Enter Remark">{{ isset($edit) && $edit?->remark ? $edit?->remark : old('remark') }}</textarea>
                            @error('remark')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Attachment (Image/PDF) <small class="text-muted">(Max: 10MB)</small></label>
                            <input type="file" name="image"
                                class="form-control @error('image') is-invalid @enderror"
                                accept=".jpeg,.jpg,.png,.pdf,.doc,.docx">
                            @if (isset($edit) && !empty($edit->attachment))
                                @php $filePath = public_path($edit->attachment); @endphp
                                @if (file_exists($filePath))
                                    <div class="mt-2">
                                        <a href="{{ asset($edit->attachment) }}" target="_blank" class="btn btn-sm btn-info">
                                            <i class="fa-solid fa-file"></i> View Current Attachment
                                        </a>
                                    </div>
                                @endif
                            @endif
                            @error('image')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    @if (isset($edit) && ($edit->status == 'pass' || $edit->status == 'reject'))
                        <div class="col-md-12 mb-3">
                            <div class="alert alert-info">
                                <strong>Status:</strong> {{ ucfirst($edit->status) }}
                                @if ($edit->status == 'pass' && $edit->pass_amount)
                                    <br><strong>Approved Amount:</strong> {{ number_format($edit->pass_amount, 2) }}
                                @endif
                                @if ($edit->status == 'reject' && $edit->reason)
                                    <br><strong>Reason:</strong> {{ $edit->reason }}
                                @endif
                            </div>
                        </div>
                    @endif

                    <div class="divider">
                        <hr />
                    </div>

                    <div class="col-md-12 text-center">
                        <button type="submit" class="btn btn-success mt-1 mb-1"
                            @if (isset($edit) && ($edit->status == 'pass' || $edit->status == 'reject')) disabled @endif>
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
    @include('utils.getBranch')
    @include('utils.getEmployee')
    @include('utils.getExpenseCategory')
    @include('utils.getExpenseSubCategory')

    <script>
        $(document).ready(function() {
            // Initialize single date picker for expense date
            if ($('.expense_date_picker').length > 0) {
                var defaultDate = $('.expense_date_picker').val();
                var startDate;
                
                // Handle both DD/MM/YYYY and Y-m-d formats
                if (defaultDate) {
                    // Check if it's in Y-m-d format (from validation errors)
                    if (defaultDate.match(/^\d{4}-\d{2}-\d{2}$/)) {
                        startDate = moment(defaultDate, 'YYYY-MM-DD');
                        // Convert to DD/MM/YYYY for display
                        $('.expense_date_picker').val(startDate.format('DD/MM/YYYY'));
                    } else {
                        startDate = moment(defaultDate, 'DD/MM/YYYY');
                    }
                } else {
                    startDate = moment();
                }
                
                // Set min and max dates (past 60 days to today)
                var minDate = moment().subtract(60, 'days');
                var maxDate = moment(); // Today
                
                // Ensure startDate is within valid range
                if (startDate.isBefore(minDate)) {
                    startDate = minDate;
                }
                if (startDate.isAfter(maxDate)) {
                    startDate = maxDate;
                }
                
                $('.expense_date_picker').daterangepicker({
                    singleDatePicker: true,
                    startDate: startDate,
                    minDate: minDate,
                    maxDate: maxDate,
                    autoUpdateInput: true,
                    autoApply: true,
                    locale: {
                        format: 'DD/MM/YYYY',
                        cancelLabel: 'Clear'
                    },
                    opens: 'left'
                }, function(start, end, label) {
                    $('.expense_date_picker').val(start.format('DD/MM/YYYY'));
                });

                // Handle clear button
                $('.expense_date_picker').on('cancel.daterangepicker', function(ev, picker) {
                    $(this).val('');
                });
            }

            // Convert date format from DD/MM/YYYY to Y-m-d before form submission
            $('form').on('submit', function(e) {
                var dateInput = $('#date');
                var dateValue = dateInput.val();
                
                if (dateValue) {
                    // Check if already in Y-m-d format
                    if (!dateValue.match(/^\d{4}-\d{2}-\d{2}$/)) {
                        // Convert DD/MM/YYYY to Y-m-d format
                        var dateParts = dateValue.split('/');
                        if (dateParts.length === 3) {
                            var formattedDate = dateParts[2] + '-' + dateParts[1] + '-' + dateParts[0];
                            dateInput.val(formattedDate);
                        }
                    }
                }
            });
        });
    </script>
@endpush
