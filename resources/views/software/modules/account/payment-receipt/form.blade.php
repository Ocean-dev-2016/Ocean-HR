@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : null;
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $i = 0;
    $company_id = isset($modules['company_id']) ? $modules['company_id'] : null;
    $colums = 'col-md-3 col-sm-12 mb-2';

@endphp
@section('page_leavel_style')
    {{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css"> --}}
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.min.css" />


@endsection
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
                    {{-- Employee name --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Select Employee <span class="text-danger">*</span></label>
                            <select id="employee_id"
                                class="form-control select2 search_by_employee @error('employee_id') is-invalid @enderror"
                                name="employee_id"
                                data-selectedemployeeid="{{ old('employee_id') ?? ($edit->employee_id ?? '') }}">
                                <option value="">Select Employee</option>
                            </select>

                            @error('employee_id')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Auto Generate Receipt No --}}

                    <div class="{{ $colums ?? 'col-12' }}">
                        <label class="form-label">Receipt No</label>
                        <input type="text" class="form-control readonly-look" name="receipt_no" id="receipt_no"
                            placeholder="Enter Receipt No" readonly value="{{ $edit?->receipt_no ?? '' }}">
                    </div>
                    {{-- department --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Select Department <span class="text-danger">*</span></label>
                            <select id="department"
                                class="form-control select2 search_by_department @error('department') is-invalid @enderror"
                                name="department"
                                data-selecteddepartmentid="{{ old('department') ?? ($edit->department ?? '') }}">
                                <option value="">Select Department</option>
                            </select>

                            @error('department')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{--  Effective Month --}}
                    <div class="col-md-3 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Month <span class="text-danger">*</span> </label>
                            <select id="effect_on_month"
                                class="form-control select2 @error('effect_on_month') is-invalid @enderror"
                                name="effect_on_month">
                                <option value="">Select Effect On Month</option>
                            </select>
                            @error('effect_on_month')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                    {{-- Effective Year --}}
                    <div class="col-md-3 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Year <span class="text-danger">*</span></label>
                            <select id="effect_of_year"
                                class="form-control select2 @error('effect_of_year') is-invalid @enderror"
                                name="effect_of_year">
                                <option value="">Select Effect Of Year</option>
                            </select>
                            @error('effect_of_year')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>


                    {{-- To Date --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label"> Date <span class="text-danger">*</span> </label>

                            <input type="text" id="date" name="date"
                                class="form-control plan-form @error('date') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->date ? $edit->date : (request()->isMethod('post') ? old('date') : '') }}"
                                placeholder=" Date" />
                            @error('date')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                    {{-- Payment By --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <label class="form-label" for="plan">Payment Type <span class="text-danger">*</span></label>
                        <select id="payment_type" class="form-select select2 @error('payment_type') is-invalid @enderror"
                            name="payment_type">
                            <option value=""> Select Payment Type</option>
                            @foreach ($payment_type as $key => $value)
                                <option value="{{ $key }}"
                                    {{ old('payment_type', $edit->payment_type ?? '') == $key ? 'selected' : '' }}>
                                    {{ $value }}
                                </option>
                            @endforeach

                        </select>
                        @error('payment_type')
                            <span class="invalid-feedback">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    {{-- Cheque No --}}
                    <div class="{{ $colums ?? 'col-12' }} payment-field payment-cheque d-none">
                        <label class="form-label">Cheque No <span class="text-danger">*</span></label>
                        <input type="number" name="cheque_no" value="{{ old('cheque_no', $edit->cheque_no ?? '') }}"
                            class="form-control">
                    </div>

                    {{-- UPI No --}}
                    <div class="{{ $colums ?? 'col-12' }} payment-field payment-upi d-none">
                        <label class="form-label">UPI No <span class="text-danger">*</span></label>
                        <input type="text" name="upi_no" value="{{ old('upi_no', $edit->upi_no ?? '') }}"
                            class="form-control">
                    </div>
                    {{-- Payment Mode --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <label class="form-label" for="plan">Payment Mode <span class="text-danger">*</span></label>
                        <select id="payment_mode" class="form-select select2 @error('payment_mode') is-invalid @enderror"
                            name="payment_mode">
                            <option value=""> Select Payment By</option>
                            @foreach ($payment_mode as $key => $value)
                                <option value="{{ $key }}"
                                    {{ old('payment_mode', $edit->payment_mode ?? '') == $key ? 'selected' : '' }}>
                                    {{ $value }}
                                </option>
                            @endforeach

                        </select>
                        @error('payment_mode')
                            <span class="invalid-feedback">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    {{-- Amount --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label"> Amount <span class="text-danger">*</span> </label>
                            <input id="amount" type="number"
                                class="form-control @error('amount') is-invalid @enderror" name="amount"
                                value="{{ isset($edit) && $edit?->amount ? $edit?->amount : old('amount') }}"
                                placeholder="Enter amount">
                            @error('amount')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Account Head Id --}}
                    {{-- <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Select Account Head <span class="text-danger">*</span></label>
                            <select id="account_head_id"
                                class="form-control select2 search_by_employee @error('account_head_id') is-invalid @enderror"
                                name="account_head_id"
                                data-selectedemployeeid="{{ old('account_head_id') ?? ($edit->account_head_id ?? '') }}">
                                <option value="">Select Employee</option>
                            </select>

                            @error('account_head_id')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div> --}}
                    {{-- Account Head Id --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Select Account Head <span class="text-danger">*</span></label>
                            <select id="account_head_id"
                                class="form-control select2 @error('account_head_id') is-invalid @enderror"
                                name="account_head_id"
                                data-selectedemployeeid="{{ old('account_head_id') ?? ($edit->account_head_id ?? '') }}">

                                <option value="">Select Account Head</option>
                                <option value="101"
                                    {{ (old('account_head_id') ?? ($edit->account_head_id ?? '')) == 101 ? 'selected' : '' }}>
                                    Cash</option>
                                <option value="102"
                                    {{ (old('account_head_id') ?? ($edit->account_head_id ?? '')) == 102 ? 'selected' : '' }}>
                                    Bank</option>
                                <option value="103"
                                    {{ (old('account_head_id') ?? ($edit->account_head_id ?? '')) == 103 ? 'selected' : '' }}>
                                    Expenses</option>
                                <option value="104"
                                    {{ (old('account_head_id') ?? ($edit->account_head_id ?? '')) == 104 ? 'selected' : '' }}>
                                    Revenue</option>

                            </select>

                            @error('account_head_id')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Remark --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Remark</label>
                            <textarea rows="3" cols="3" class="form-control @error('remark') is-invalid @enderror" name="remark"
                                placeholder="Enter Remark" id="remark">{{ old('remark', $edit->remark ?? '') }}</textarea>
                            @error('remark')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script type="text/javascript">
        flatpickr("#date", {
            defaultDate: null,
            dateFormat: "Y-m-d",
            maxDate: "today", // ✅ Prevents selecting future dates
        });
    </script>

    <script>
        $(document).ready(function() {
            let currentYear = new Date().getFullYear();
            let startYear = currentYear - 5;
            let endYear = currentYear;


            let selectedYear = "{{ old('effect_of_year', $edit->effect_of_year ?? '') }}";

            for (let y = startYear; y <= endYear; y++) {
                let isSelected = (selectedYear == y) ? 'selected' : '';
                $('#effect_of_year').append(
                    `<option value="${y}" ${isSelected}>${y}</option>`
                );
            }
        });
    </script>
    <script>
        $(document).ready(function() {
            let months = [
                'January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'
            ];

            let selectedMonth = "{{ isset($edit) ? $edit?->effect_on_month : old('effect_on_month') }}";

            months.forEach(function(m) {
                let selected = (selectedMonth === m) ? 'selected' : '';
                $('#effect_on_month').append(`<option value="${m}" ${selected}>${m}</option>`);
            });
        });
    </script>

    {{-- <script>
        $(document).on("change", ".search_by_company", function() {
            // Only run for CREATE mode, not EDIT
            @if (!isset($edit))
                let company_id = $(this).val();

                if (!company_id) {
                    console.log("No company selected, skipping receipt no fetch");
                    return;
                }

                console.log('Fetching receipt number for company:', company_id);

                $.ajax({
                    url: "{{ route('payment-receipt.generate-receipt-no') }}",
                    type: "POST",
                    data: {
                        company_id: company_id
                    },
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    dataType: "json",
                    success: function(data) {
                        console.log('Data returned from server:', data);
                        if (data.receipt_no) {
                            $("#receipt_no").val(data.receipt_no);
                            console.log('Receipt number set in input:', data.receipt_no);
                        } else {
                            console.error('No receipt number returned');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching receipt no:', error);
                    }
                });
            @else
                console.log("Edit mode → receipt number not regenerated");
            @endif
        });
    </script> --}}
    <script>
        function generateReceiptNo(company_id) {
            if (!company_id) return;

            $.ajax({
                url: "{{ route('payment-receipt.generate-receipt-no') }}",
                type: "POST",
                data: {
                    company_id: company_id
                },
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                dataType: "json",
                success: function(data) {
                    if (data.receipt_no) {
                        $("#receipt_no").val(data.receipt_no);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching receipt no:', error);
                }
            });
        }

        $(document).ready(function() {
            // Trigger on change
            $(document).on("change", ".search_by_company", function() {
                generateReceiptNo($(this).val());
            });

            // Trigger automatically if company is already selected (edit mode or logged-in)
            let selectedCompany = $(".search_by_company").val();
            if (selectedCompany) {
                generateReceiptNo(selectedCompany);
            }
        });
    </script>





    <script>
        function togglePaymentFields(paymentBy) {
            $('.payment-field').addClass('d-none');
            if (paymentBy === 'cheque') {
                $('.payment-cheque').removeClass('d-none');
            } else if (paymentBy === 'upi') {
                $('.payment-upi').removeClass('d-none');
            } else if (paymentBy === 'other') {
                $('.payment-other').removeClass('d-none');
            }
        }

        $(document).ready(function() {
            togglePaymentFields($('#payment_type').val());
            $('#payment_type').on('change', function() {
                togglePaymentFields($(this).val());
            });
        });
    </script>



    @if (!$company_id)
        @include('utils.getCompany')
    @endif
    @include('utils.getEmployee')
    @include('utils.getBranch')

    @include('utils.getDepartment')

@endpush
