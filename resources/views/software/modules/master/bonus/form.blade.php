@extends('software.layout.app')

@php
    $i = 0;

    $page_title = isset($modules['title']) ? $modules['title'] : null;
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $company_id = isset($modules['company_id']) ? $modules['company_id'] : null;

@endphp
@section('page_leavel_style')
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

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
                            value="{{ $company_id }}" />
                    @endif



                    {{--  Effective Month --}}
                    <div class="col-md-3 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Month <span class="text-danger">*</span> </label>
                            <select id="month"
                                class="form-control select2 @error('month') is-invalid @enderror"
                                name="month">
                                <option value="">Select Month</option>
                            </select>
                            @error('month')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                    {{-- Effective Year --}}
                    <div class="col-md-3 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Year <span class="text-danger">*</span></label>
                            <select id="year"
                                class="form-control select2 @error('year') is-invalid @enderror"
                                name="year">
                                <option value="">Select Year</option>
                            </select>
                            @error('year')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                    {{-- Amount --}}
                    <div class="col-md-3 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label"> Amount <span class="text-danger">*</span> </label>
                            <input id="amount" type="number" class="form-control @error('amount') is-invalid @enderror"
                                name="amount" value="{{ isset($edit) && $edit?->amount ? $edit?->amount : old('amount') }}"
                                placeholder="Enter amount">
                            @error('amount')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                    {{-- Branch --}}
                    <div class="col-md-3 col-sm-12 mb-2 branchDiv" style="display:none;">
                        <div class="form-group">
                            <label class="form-label">Select Branch <span class="text-danger">*</span></label>
                            <select id="branch"
                                class="form-control select2 search_by_branch @error('branch') is-invalid @enderror"
                                name="branch" data-forceReload="true"
                                data-selectedbranchid="{{ old('branch') ?? ($edit->branch ?? '') }}">
                                <option value="">Select Branch</option>
                            </select>

                            @error('branch')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Employee --}}
                    {{-- <div class="col-md-3 col-sm-12 mb-2">
                        <div class="form-group">
                            <label class="form-label">Select Employee <span class="text-danger">*</span></label>
                            <select id="employee"
                                class="form-control select2 search_by_employee @error('employee') is-invalid @enderror"
                                name="employee" data-selectedbranchid="{{ old('employee') ?? ($edit->employee ?? '') }}">
                                <option value="">Select employee</option>
                            </select>

                            @error('employee')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div> --}}

                    <div class="col-md-3 col-sm-12 mb-2">
                        <div class="form-group">
                            <label class="form-label">Select Employee <span class="text-danger">*</span></label>
                            <select id="employee"
                                class="form-control select2 search_by_employee @error('employee') is-invalid @enderror"
                                name="employee" data-selectedEmployeeId="{{ old('employee') ?? ($edit->employee ?? '') }}">
                                <option value="">Select Employee</option>
                            </select>

                            @error('employee')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                    {{-- Status --}}
                    <div class="col-md-3 col-sm-12 mb-3">
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
        $(document).ready(function() {
            let currentYear = new Date().getFullYear();
            let startYear = currentYear - 5;
            let endYear = currentYear;


            let selectedYear = "{{ old('year', $edit->year ?? '') }}";

            for (let y = startYear; y <= endYear; y++) {
                let isSelected = (selectedYear == y) ? 'selected' : '';
                $('#year').append(
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

            let selectedMonth = "{{ isset($edit) ? $edit?->month : old('month') }}";

            months.forEach(function(m) {
                let selected = (selectedMonth === m) ? 'selected' : '';
                $('#month').append(`<option value="${m}" ${selected}>${m}</option>`);
            });
        });
    </script>
    {{-- <script>
        $(function() {
            $('#month').datepicker({
                changeMonth: true,
                changeYear: false,
                showButtonPanel: false,
                dateFormat: 'MM',
                onClose: function(dateText, inst) {

                },
                beforeShow: function(input, inst) {
                    $(inst.dpDiv).addClass('month_only_datepicker');
                    setTimeout(function() {

                        inst.dpDiv.find('.ui-datepicker-calendar').hide();
                        inst.dpDiv.find('.ui-datepicker-year').hide();


                        inst.dpDiv.find('.ui-datepicker-month').off('change').on('change',
                            function() {
                                var month = $(this).val();
                                var year = new Date().getFullYear(); // current year
                                $('#month').datepicker('setDate', new Date(year, month, 1));
                                $('#month').datepicker('hide'); // Close the picker
                            });
                    }, 0);
                }
            });
        });

        $(document).ready(function() {
            $("#year").datepicker({
                changeYear: true,
                dateFormat: 'yy',
                yearRange: "2000:" + new Date().getFullYear(),
                showButtonPanel: false,


                onChangeMonthYear: function(year, month, inst) {
                    // set value
                    $("#year").val(year);

                    // close datepicker immediately
                    $("#year").datepicker("hide");
                },

                onClose: function(dateText, inst) {
                    // ensure final value is year only
                    var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
                    if (year) {
                        $(this).val(year);
                    }
                }
            });

            // hide month & calendar
            $("#year").focus(function() {
                $(".ui-datepicker-month").hide();
                $(".ui-datepicker-calendar").hide();
            });
        });
    </script>
 --}}


    @if (!$company_id)
        @include('utils.getCompany')
    @endif
    @include('utils.getEmployee')

@endpush
