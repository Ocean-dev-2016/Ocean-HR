@extends('software.layout.app')

@php
    $i = 0;

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
                        <div class="{{ $colums ?? 'col-12' }}">
                            <div class="form-group">
                                <label class="form-label">Select Company <span class="text-danger">*</span></label>
                                <select id="company_id"
                                    class="form-control select2 search_by_company @error('company_id') is-invalid @enderror"
                                    name="company_id"
                                    data-selectedCompanyId="{{ old('company_id') ?? ($edit->company_id ?? '') }}"
                                    tabindex="{{ $i = $i + 1 }}">
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

                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group @error('employee_id') is-invalid @enderror">
                            <label class="form-label">Select Employee <span class="text-danger">*</span></label>
                            <select id="employee_id"
                                class="form-control select2 search_by_employee @error('employee_id') is-invalid @enderror"
                                name="employee_id"
                                data-selectedemployeeid="{{ old('employee_id') ?? ($edit->employee_id ?? '') }}"
                                tabindex="{{ $i = $i + 1 }}">
                                <option value="">Select Employee</option>
                            </select>

                        </div>
                        @error('employee_id')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    {{-- Loan Date --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label"> Loan Date </label>

                            <input type="text" id="loan_date" name="loan_date"
                                class="form-control @error('loan_date') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->loan_date ? $edit->loan_date : old('loan_date') }}"
                                placeholder="Loan Date" />
                            @error('loan_date')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group @error('loan_type_id') is-invalid @enderror">
                            <label class="form-label">Select loan type <span class="text-danger">*</span></label>
                            <select id="loan_type_id"
                                class="form-control select2 search_by_loan @error('loan_type_id') is-invalid @enderror"
                                name="loan_type_id"
                                data-selectedloantypeid="{{ old('loan_type_id') ?? ($edit->loan_type_id ?? '') }}"
                                tabindex="{{ $i = $i + 1 }}">
                                <option value="">Select Loan Type</option>
                            </select>

                        </div>
                        @error('loan_type_id')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>


                    {{-- loan_amount --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Loan Amount <span class="text-danger">*</span></label>
                            <input id="loan_amount" type="number"
                                class="form-control autosize @error('loan_amount') is-invalid @enderror number_only"
                                name="loan_amount" rows="1" autocomplete="loan_amount"
                                value="{{ isset($edit) ? $edit->loan_amount : old('loan_amount') }}"
                                placeholder="Enter loan amount" tabindex="{{ $i = $i + 1 }}" />
                            @error('loan_amount')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    {{-- interest_rate --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Interest Rate (%) <span class="text-danger">*</span></label>
                            <input id="interest_rate" type="number" step="0.01"
                                class="form-control autosize @error('interest_rate') is-invalid @enderror number_only"
                                name="interest_rate" autocomplete="interest_rate"
                                value="{{ isset($edit) ? $edit->interest_rate : old('interest_rate') }}"
                                placeholder="Enter interest rate (%)" tabindex="{{ $i = $i + 1 }}" />
                            @error('interest_rate')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    {{-- total_installments --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Total Installments <span class="text-danger">*</span></label>
                            <input id="total_installments" type="number" step="0.01"
                                class="form-control autosize @error('total_installments') is-invalid @enderror number_only"
                                name="total_installments" rows="1" autocomplete="total_installments"
                                value="{{ isset($edit) ? $edit->total_installments : old('total_installments') }}"
                                placeholder="Enter total installments" tabindex="{{ $i = $i + 1 }}" />
                            @error('total_installments')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    {{-- Interest Type --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group mb-3">
                            <label for="interest_type">Interest Type</label>
                            <select name="interest_type" id="interest_type"
                                class="form-control @error('interest_type') is-invalid @enderror"
                                tabindex="{{ $i = $i + 1 }}">
                                @foreach (['flat' => 'Flat', 'reducing' => 'Reducing Balance (EMI)'] as $key => $value)
                                    <option value="{{ $key }}"
                                        @if (isset($edit)) @if ($edit->interest_type == $key) {{ 'selected' }} @endif
                                    @else @if (old('status', 'flat') == $key) {{ 'selected' }} @endif @endif> {{ ucfirst($value) }}</option>
                                @endforeach
                            </select>
                            @error('interest_type')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    {{-- Remark --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Remark</label>
                            <textarea id="remark" class="form-control autosize @error('remark') is-invalid @enderror" name="remark"
                                rows="1" autocomplete="remark" placeholder="Enter Request Description" tabindex="{{ $i = $i + 1 }}">{{ isset($edit) ? $edit->remark : old('remark') }}</textarea>
                            @error('remark')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    {{-- Status --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select class="form-control select2 w-100 @error('status') is-invalid @enderror" name="status"
                                required tabindex="{{ $i = $i + 1 }}">
                                <option disabled selected>Select Status</option>
                                @foreach ($loanStatus as $key => $value)
                                    <option value="{{ $key }}"
                                        @if (isset($edit)) @if ($edit->status == $key) {{ 'selected' }} @endif
                                    @else @if (old('status', 'pending') == $key) {{ 'selected' }} @endif @endif> {{ ucfirst($value) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="{{ 'col-12' }}">

                        {{-- Installment Table --}}
                        <div id="installmentSchedule" style="display:none;">
                            <hr>
                            <h4>Installment Schedule</h4>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Installment No</th>
                                        <th>Due Date</th>
                                        <th>Principal</th>
                                        <th>Interest</th>
                                        <th>Installment Amount</th>
                                    </tr>
                                </thead>
                                <tbody id="scheduleBody"></tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Divider --}}
                    <div class="divider">
                        <hr />
                    </div>

                    {{-- Submit Buttons --}}
                    <div class="col-md-12 text-center">
                        <button type="button" class="btn btn-primary" onclick="calculateLoan()">Calculate</button>
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
    <script src="{{ asset('software/vendor/libs/flatpickr/flatpickr.js') }}"></script>
@endsection

@push('page_scripts')
    @if (!$company_id)
        @include('utils.getCompany')
    @endif
    @include('utils.getEmployee')
    @include('utils.getLoanTypes')
    <script>
        flatpickr("#loan_date", {
            defaultDate: null,
            maxDate: "today",
            dateFormat: "d-m-Y"
        });

        function calculateLoan() {
            let data = {
                _token: "{{ csrf_token() }}",
                company_id: $(".search_by_company option:selected").val(),
                loan_amount: document.getElementById('loan_amount').value,
                total_installments: document.getElementById('total_installments').value,
                interest_rate: 0,
                interest_type: 'flat',
            };
            if ($('meta[name="company_id"]').attr('value')) {
                data = {
                    ...data,
                    company_id: $('meta[name="company_id"]').attr('value')
                }
            }

            if (document.getElementById('interest_rate')) {
                data = {
                    ...data,
                    interest_rate: document.getElementById('interest_rate').value
                }
            }
            if (document.getElementById('interest_type')) {
                data = {
                    ...data,
                    interest_type: document.getElementById('interest_type').value
                }
            }

            fetch('{{ env('API_URL') }}loan/calculation', {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": data._token
                    },

                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(schedule => {
                    console.log("schedule 293", schedule);
                    if (schedule?.status && schedule?.data?.length) {
                        let tbody = document.getElementById('scheduleBody');
                        tbody.innerHTML = "";
                        schedule?.data.forEach(row => {
                            tbody.innerHTML += `
                            <tr>
                                <td>${row.installment_no}</td>
                                <td>${row.due_date}</td>
                                <td>${row.principal}</td>
                                <td>${row.interest}</td>
                                <td>${row.installment_amount}</td>
                            </tr>`;
                        });
                        document.getElementById('installmentSchedule').style.display = "block";
                    }
                });
        }
    </script>
@endpush
