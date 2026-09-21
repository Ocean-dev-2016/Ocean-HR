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
    <link rel="stylesheet" href="{{ asset('software/vendor/libs/flatpickr/flatpickr.css') }}" />
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





                    {{-- Date --}}
                    <div class="{{ $colums ?? 'col-md-4' }}">
                        <div class="form-group">
                            <label class="form-label">Date</label>
                            <input type="text" id="date" name="date" class="form-control"
                                value="{{ old('date', (isset($edit) && $edit?->date) ? \Carbon\Carbon::parse($edit->date)->format('d-m-Y') : date('d-m-Y')) }}" placeholder="DD-MM-YYYY">
                        </div>
                    </div>

                    {{-- Description (Read Only) --}}
                    <div class="{{ $colums ?? 'col-md-8' }}">
                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <input type="text" name="description" class="form-control"
                                value="{{ old('description', $edit->description ?? '') }}" readonly>
                        </div>
                    </div>

                    {{-- Debit Amount (Read Only) --}}
                    <div class="{{ $colums ?? 'col-md-4' }}">
                        <div class="form-group">
                            <label class="form-label">Debit Amount</label>
                            <input type="text" name="debit_amount" class="form-control text-end"
                                value="{{ old('debit_amount', $edit->debit_amount ?? '') }}" readonly>
                        </div>
                    </div>

                    {{-- Credit Amount (Read Only) --}}
                    <div class="{{ $colums ?? 'col-md-4' }}">
                        <div class="form-group">
                            <label class="form-label">Credit Amount</label>
                            <input type="text" name="credit_amount" class="form-control text-end"
                                value="{{ old('credit_amount', $edit->credit_admount ?? '') }}" readonly>
                        </div>
                    </div>

                    {{-- Balance (Read Only) --}}
                    <div class="{{ $colums ?? 'col-md-4' }}">
                        <div class="form-group">
                            <label class="form-label">Balance</label>
                            <input type="text" name="balance" class="form-control text-end"
                                value="{{ old('balance', $edit->balance ?? '') }}" readonly>
                        </div>
                    </div>
                    {{-- From Date --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">From Date <span class="text-danger">*</span> </label>

                            <input type="text" id="from_date" name="from_date"
                                class="form-control plan-form @error('from_date') is-invalid @enderror"
                                value="{{ old('from_date', (isset($edit) && $edit?->from_date) ? \Carbon\Carbon::parse($edit->from_date)->format('d-m-Y') : '') }}"
                                placeholder="DD-MM-YYYY" required />
                            @error('from_date')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                    {{-- To Date --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">To Date <span class="text-danger">*</span> </label>

                            <input type="text" id="to_date" name="to_date"
                                class="form-control plan-form @error('to_date') is-invalid @enderror"
                                value="{{ old('to_date', (isset($edit) && $edit?->to_date) ? \Carbon\Carbon::parse($edit->to_date)->format('d-m-Y') : '') }}"
                                placeholder="DD-MM-YYYY" required />
                            @error('to_date')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                    <div class="{{ $colums ??  'col-12' }}">
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
            dateFormat: "d-m-Y",
            allowInput: true,
            defaultDate: "{{ old('date', (isset($edit) && $edit?->date) ? \Carbon\Carbon::parse($edit->date)->format('d-m-Y') : date('d-m-Y')) }}"
        });

        const toPicker = flatpickr("#to_date", {
            dateFormat: "d-m-Y",
            allowInput: true
        });

        const fromPicker = flatpickr("#from_date", {
            dateFormat: "d-m-Y",
            allowInput: true,
            onChange: function(selectedDates, dateStr) {
                if (dateStr && toPicker) {
                    toPicker.set('minDate', dateStr);
                }
            }
        });
    </script>
    <script>
$(document).ready(function() {
    $('#account_head_id').on('change', function() {
        let accountHeadId = $(this).val();

        if (!accountHeadId) return;

        $.ajax({
            url: "{{ route('account.head.ledger.data') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                account_head_id: accountHeadId,
            },
            success: function(response) {
                if (response.status && response.data) {
                    if (document.querySelector('#date') && document.querySelector('#date')._flatpickr) {
                        document.querySelector('#date')._flatpickr.setDate(response.data.date ?? '', true);
                    } else {
                        $('input[name="date"]').val(response.data.date ?? '');
                    }
                    $('input[name="description"]').val(response.data.description ?? '');
                    $('input[name="debit_amount"]').val(response.data.debit_amount ?? '');
                    $('input[name="credit_amount"]').val(response.data.credit_amount ?? '');
                    $('input[name="balance"]').val(response.data.balance ?? '');
                } else {
                    if (document.querySelector('#date') && document.querySelector('#date')._flatpickr) {
                        document.querySelector('#date')._flatpickr.clear();
                    } else {
                        $('input[name="date"]').val('');
                    }
                    $('input[name="description"], input[name="debit_amount"], input[name="credit_amount"], input[name="balance"]').val('');
                }
            }
        });
    });
});
</script>

    @if (!$company_id)
        @include('utils.getCompany')
    @endif
  
@endpush
