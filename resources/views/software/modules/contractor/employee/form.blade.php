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
                <input type="hidden" id="edit_id" name="edit_id"
                    value="{{ isset($edit) && $edit?->id ? $edit?->id : '' }}" />
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

                    @php
                        $isManual = false;
                        if (isset($edit) && isset($edit->company_id)) {
                            $company = \App\Models\Company::find($edit->company_id);
                            $isManual = $company && $company->employee_code_auto_generation === 'manual';
                        } elseif (isset($company_id)) {
                            $company = \App\Models\Company::find($company_id);
                            $isManual = $company && $company->employee_code_auto_generation === 'manual';
                        }
                    @endphp

                    {{-- Employee Code --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Employee Code <span class="text-danger">*</span></label>
                            <input id="employee_code" type="text"
                                class="form-control employee-code-input @error('employee_code') is-invalid @enderror @if (!$isManual) readonly-look @endif"
                                name="employee_code"
                                value="{{ old('employee_code', $edit->employee_code ?? ($nextEmployeeCode ?? '')) }}"
                                placeholder="Employee Code" @if (!$isManual) readonly @endif>
                            @error('employee_code')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Biometric User ID --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Biometric User ID</label>
                            <input id="biometric_user_id" type="text"
                                class="form-control @error('biometric_user_id') is-invalid @enderror"
                                name="biometric_user_id"
                                value="{{ old('biometric_user_id', $edit->biometric_user_id ?? '') }}"
                                placeholder="Biometric User ID">
                            @error('biometric_user_id')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Parent Id --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label"> Select Parent </label>
                            <select id="parent_id"
                                class="form-control select2 search_by_employee @error('parent_id') is-invalid @enderror"
                                name="parent_id"
                                data-selectedEmployeeId="{{ old('parent_id') ?? ($edit->parent_id ?? '') }}">
                                <option value="">Select Parent Employee</option>
                            </select>

                            @error('parent_id')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Surname Name --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label"> Surname <span class="text-danger">*</span> </label>
                            <input id="first_name" type="text"
                                class="form-control @error('first_name') is-invalid @enderror" name="first_name"
                                value="{{ isset($edit) && $edit?->first_name ? $edit?->first_name : old('first_name') }}"
                                placeholder="Enter Surname">
                            @error('first_name')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- First Name --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label"> First Name <span class="text-danger">*</span> </label>
                            <input id="middle_name" type="text"
                                class="form-control @error('middle_name') is-invalid @enderror" name="middle_name"
                                value="{{ isset($edit) && $edit?->middle_name ? $edit?->middle_name : old('middle_name') }}"
                                placeholder="Enter first name">
                            @error('middle_name')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Father Name --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label"> Father Name <span class="text-danger">*</span> </label>
                            <input id="father_name" type="text"
                                class="form-control @error('father_name') is-invalid @enderror" name="father_name"
                                value="{{ isset($edit) && $edit?->father_name ? $edit?->father_name : old('father_name') }}"
                                placeholder="Enter Father Name">
                            @error('father_name')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- As Per Aadhar --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label"> As Per Aadhar <span class="text-danger">*</span> </label>
                            <input id="full_name" type="text"
                                class="form-control @error('full_name') is-invalid @enderror" name="full_name"
                                value="{{ isset($edit) && $edit?->full_name ? $edit?->full_name : old('full_name') }}"
                                placeholder="Enter As Per Aadhar">
                            @error('full_name')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Date of Birth --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label"> Date of Birth </label>

                            <input type="text" id="date_of_birth" name="date_of_birth"
                                class="form-control @error('date_of_birth') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->date_of_birth ? $edit->date_of_birth : old('date_of_birth') }}"
                                placeholder="Date of Birth" />
                            @error('date_of_birth')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Gender --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Gender <span class="text-danger">*</span></label>
                            <select class="form-control select2 w-100 @error('gender') is-invalid @enderror"
                                name="gender" required>
                                <option disabled selected>Select Gender</option>
                                @foreach (config('constants.genders') as $key => $gender)
                                    <option value="{{ $key }}"
                                        @if (isset($edit) && $edit->gender == $key) selected
                                        @elseif(old('gender') == $key) selected @endif>
                                        {{ $gender }}
                                    </option>
                                @endforeach
                            </select>
                            @error('gender')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>


                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label"> Blood Group  </label>
                            <input id="blood_group" type="text"
                                class="form-control @error('blood_group') is-invalid @enderror" name="blood_group"
                                value="{{ isset($edit) && $edit?->blood_group ? $edit?->blood_group : old('blood_group') }}"
                                placeholder="Enter blood group">
                            @error('blood_group')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Email Id --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label"> Email Id </label>
                            <input id="email" type="text"
                                class="form-control @error('email') is-invalid @enderror" name="email"
                                value="{{ isset($edit) && $edit?->email ? $edit?->email : old('email') }}"
                                placeholder="Enter Email Id">
                            @error('email')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- User Name --}}
                    <div class="col-md-3 col-sm-12 mb-2">
                        <div class="form-group">
                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <input id="username" type="text"
                                class="form-control @error('username') is-invalid @enderror" name="username"
                                value="{{ old('username', $edit->username ?? '') }}" autocomplete="name"
                                placeholder="Username">
                            @error('username')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Password --}}
                    <div class="col-md-3 col-sm-12 mb-2">
                        <div class="form-group">
                            <label class="form-label">Password @if (!isset($edit)) <span class="text-danger">*</span>
                                @endif </label>
                            <div class="input-group input-group-merge @error('password') is-invalid @enderror">
                                <input type="password" id="password"
                                    class="form-control @error('password') is-invalid @enderror " name="password"
                                    placeholder="Password" aria-describedby="password" value="{{ old('password') }}" />
                                <span class="input-group-text cursor-pointer toggle-password" onclick="togglePassword()">
                                    <i class="ti ti-eye-off" id="togglePasswordIcon"></i>
                                </span>
                            </div>
                            @error('password')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Role Id --}}
                    <div class="col-md-3 col-sm-12 mb-2">
                        <div class="form-group">
                            <label class="form-label">Team Role </label>
                            <select name="role_id" id="role_id" placeholder="Team Role"
                                class="form-control @error('role_id') is-invalid @enderror search_by_team_role select2"
                                data-append="search_by_team_role"
                                data-selectedRoleId="{{ isset($edit) && $edit?->role_id ? $edit?->role_id : old('role_id') }}"
                                autofocus>
                                <option value="" disabled
                                    {{ old('role_id', $edit->role_id ?? '') ? '' : 'selected' }}>
                                    Select Team Role
                                </option>
                            </select>
                            @error('role_id')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Contact Number --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Contact Number</label>
                            <input id="contact_number" type="text"
                                class="form-control @error('contact_number') is-invalid @enderror" name="contact_number"
                                value="{{ old('contact_number', $edit->contact_number ?? '') }}"
                                placeholder="Enter Contact Number" maxlength="10" pattern="\d{10}"
                                title="Please enter exactly 10 digits"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);">
                            @error('contact_number')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Other Number --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group"> <label class="form-label"> Other Number </label> <input
                                class="form-check-input sameAsParent" data-parent-id="contact_number"
                                data-child-id="other_number" type="checkbox" value="" id="sameAsParent">
                            <label class="form-check-label sameAsParent" data-parent-id="contact_number"
                                data-child-id="other_number" for="sameAsParent">
                                Same as Contact Number </label>

                            <input id="other_number" type="text"
                                class="form-control @error('other_number') is-invalid @enderror" name="other_number"
                                value="{{ old('other_number', $edit->other_number ?? '') }}"
                                placeholder="Enter Other Number" maxlength="10" pattern="\d{10}"
                                title="Please enter exactly 10 digits"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);">
                            @error('other_number')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Country --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Country <span class="text-danger">*</span></label>
                            <select name="country_id" id="country_id"
                                class="form-control @error('country_id') is-invalid @enderror search_by_country select2"
                                data-append="search_by_country" data-filterByStatus="active"
                                data-selectedCountryId="{{ isset($edit) && $edit?->country_id ? $edit?->country_id : old('country_id') }}"
                                data-selectedStateId="{{ isset($edit) && $edit?->state_id ? $edit?->state_id : old('state_id') }}"
                                autofocus>
                                <option value="" disabled
                                    {{ old('country_id', $edit->country_id ?? '') ? '' : 'selected' }}>
                                    Select Country
                                </option>
                            </select>
                            @error('country_id')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- State --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">State <span class="text-danger">*</span></label>
                            <select name="state_id" id="state"
                                class="form-control @error('state_id') is-invalid @enderror search_by_state select2"
                                data-append="search_by_state"
                                data-selectedCountryId="{{ isset($edit) && $edit?->country_id ? $edit?->country_id : old('country_id') }}"
                                data-selectedStateId="{{ isset($edit) && $edit?->state_id ? $edit?->state_id : old('state_id') }}">

                                <option value="" disabled
                                    {{ old('state_id', $edit->state_id ?? '') ? '' : 'selected' }}>
                                    Select State
                                </option>
                            </select>
                            @error('state_id')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- City --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">City <span class="text-danger">*</span></label>
                            <select id="city_id" name="city_id"
                                class="form-control @error('city_id') is-invalid @enderror search_by_city select2"
                                data-append="search_by_city"
                                data-selectedCityId="{{ isset($edit) && $edit?->city_id ? $edit?->city_id : old('city_id') }}">
                                <option value="" disabled
                                    {{ old('city_id', $edit->city_id ?? '') ? '' : 'selected' }}>
                                    Select City
                                </option>
                            </select>
                            @error('city_id')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Current Address --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Current Address <span class="text-danger">*</span></label>
                            <textarea id="current_address" class="form-control @error('current_address') is-invalid @enderror"
                                name="current_address" placeholder="Enter Current Address" rows="3">{{ isset($edit) && $edit?->current_address ? $edit?->current_address : old('current_address') }}</textarea>
                            @error('current_address')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Permanent Address --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Permanent Address <span class="text-danger">*</span></label>
                            <input class="form-check-input sameAsParent" data-parent-id="current_address"
                                data-child-id="permanent_address" type="checkbox" value=""
                                id="sameAsCurrentAddress">
                            <label class="form-check-label sameAsParent" data-parent-id="current_address"
                                data-child-id="permanent_address" for="sameAsCurrentAddress"> Same as Current Address
                            </label>
                            <textarea id="permanent_address" class="form-control @error('permanent_address') is-invalid @enderror"
                                name="permanent_address" placeholder="Enter Permanent Address" rows="3">{{ isset($edit) && $edit?->permanent_address ? $edit?->permanent_address : old('permanent_address') }}</textarea>
                            @error('permanent_address')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Aadhar Card Number --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label"> Aadhar Card Number <span class="text-danger">*</span> </label>
                            <input id="aadhar_card_number" type="text"
                                class="form-control @error('aadhar_card_number') is-invalid @enderror"
                                name="aadhar_card_number"
                                value="{{ isset($edit) && $edit?->aadhar_card_number ? $edit?->aadhar_card_number : old('aadhar_card_number') }}"
                                placeholder="Enter Aadhar Card Number">
                            @error('aadhar_card_number')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Pan Card Number --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label"> Pan Card Number </label>
                            <br>
                            <input id="pan_card_number" type="text"
                                class="form-control @error('pan_card_number') is-invalid @enderror"
                                name="pan_card_number"
                                value="{{ isset($edit) && $edit?->pan_card_number ? $edit?->pan_card_number : old('pan_card_number') }}"
                                placeholder="eg: ABCDE 1234 F">
                            @error('pan_card_number')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Marital Status --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Marital Status</label>
                            <select id="marital_status"
                                class="form-control select2 w-100 @error('marital_status') is-invalid @enderror"
                                name="marital_status" required>
                                <option disabled selected>Select marital status</option>
                                @foreach (config('constants.marital_status') as $key => $marital_status)
                                    <option value="{{ $key }}"
                                        @if (isset($edit) && $edit->marital_status == $key) selected
                                        @elseif(old('marital_status') == $key) selected @endif>
                                        {{ $marital_status }}
                                    </option>
                                @endforeach
                            </select>
                            @error('marital_status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Date of Anniversary --}}
                    <div id="anniversary_div" class="{{ $colums ?? 'col-12' }}" style="display: none;">
                        <div class="form-group">
                            <label class="form-label"> Date of Anniversary </label>

                            <input type="text" id="date_of_anniversary" name="date_of_anniversary"
                                class="form-control plan-form @error('date_of_anniversary') is-invalid @enderror"
                                value="{{ isset($edit) && $edit?->date_of_anniversary ? $edit->date_of_anniversary : (request()->isMethod('post') ? old('date_of_anniversary') : '') }}"
                                placeholder="Date of Anniversary" />
                            @error('date_of_anniversary')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Grade --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label"> Grade </label>
                            <input id="grade" type="text"
                                class="form-control @error('grade') is-invalid @enderror" name="grade"
                                value="{{ isset($edit) && $edit?->grade ? $edit?->grade : old('grade') }}"
                                placeholder="Enter Grade">
                            @error('grade')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Bank Name --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label"> Bank Name </label>
                            <input id="bank_name" type="text"
                                class="form-control @error('bank_name') is-invalid @enderror" name="bank_name"
                                value="{{ isset($edit) && $edit?->bank_name ? $edit?->bank_name : old('bank_name') }}"
                                placeholder="Enter Bank Name">
                            @error('bank_name')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Bank Account Number --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Bank Account Number</label>
                            <input id="bank_account_number" type="text"
                                class="form-control @error('bank_account_number') is-invalid @enderror"
                                name="bank_account_number"
                                value="{{ old('bank_account_number', $edit->bank_account_number ?? '') }}"
                                placeholder="Enter Bank Account Number" maxlength="18" pattern="\d{9,18}"
                                title="Please enter 9 to 18 digits"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 18);">
                            @error('bank_account_number')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- IFSC Code --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label"> IFSC Code </label>
                            <input id="ifsc_code" type="text"
                                class="form-control @error('ifsc_code') is-invalid @enderror" name="ifsc_code"
                                value="{{ isset($edit) && $edit?->ifsc_code ? $edit?->ifsc_code : old('ifsc_code') }}"
                                placeholder="Enter IFSC Code">
                            @error('ifsc_code')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Status --}}
                    <div class="{{ $colums ?? 'col-12' }}">
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

@section('page_leavel_script')
    <script src="{{ asset('software/vendor/libs/flatpickr/flatpickr.js') }}"></script>
@endsection

@push('page_scripts')
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const icon = document.getElementById('togglePasswordIcon');
            const isPassword = passwordInput.type === 'password';

            passwordInput.type = isPassword ? 'text' : 'password';
            icon.classList.toggle('ti-eye-off', !isPassword);
            icon.classList.toggle('ti-eye', isPassword);
        }
    </script>
    <script>
        let isManual = "{{ $isManual ?? '' }}";

        $(document).on('change', '.search_by_company, .search_by_branch', function() {
            checkCompanyEmployeeCodeSetting();
        });

        function checkCompanyEmployeeCodeSetting() {
            let company_id = $(".search_by_company").val();

            if (!company_id && $('meta[name="company_id"]').attr('value')) {
                company_id = $('meta[name="company_id"]').attr('value');
            }

            if (!company_id) {
                $('#employee_code').val('');
                $('#employee_code').removeAttr('readonly').removeClass('readonly-look');
                return;
            }

            // Get company's employee_code_auto_generation setting
            $.ajax({
                type: 'GET',
                url: '{{ env('API_URL') }}get-company-setting',
                data: {
                    company_id: company_id
                },
                beforeSend: function(request) {
                    request.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
                },
                success: function(response) {
                    if (response.status && response.data) {
                        isManual = response?.data?.employee_code_auto_generation === 'manual';
                        let employeeCodeInput = $('#employee_code');

                        if (isManual) {
                            // Manual mode - make field editable
                            employeeCodeInput.removeAttr('readonly').removeClass('readonly-look');
                        } else {
                            // Auto mode - make field readonly and generate code
                            employeeCodeInput.attr('readonly', 'readonly').addClass('readonly-look');
                            getEmployeeCode();
                        }
                    }
                },
                error: function() {
                    // Default to auto mode if API fails
                    $('#employee_code').attr('readonly', 'readonly').addClass('readonly-look');
                    getEmployeeCode();
                }
            });
        }

        function getEmployeeCode() {
            let company_id = $(".search_by_company").val();
            let branch_id = $(".search_by_branch").val();
            let formJson = {};

            if (!company_id && $('meta[name="company_id"]').attr('value')) {
                company_id = $('meta[name="company_id"]').attr('value');
            }

            if (!company_id) {
                $('#employee_code').val('');
                return;
            }

            $.ajax({
                type: 'GET',
                url: '{{ env('API_URL') }}get-company-setting',
                data: {
                    company_id: company_id
                },
                beforeSend: function(request) {
                    request.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
                },
                success: function(settingResponse) {
                    formJson = {
                        ...formJson,
                        company_id: company_id
                    };

                    if (branch_id) {
                        formJson = {
                            ...formJson,
                            branch_id: branch_id
                        };
                    }

                    $.ajax({
                        type: 'POST',
                        url: '{{ env('API_URL') }}generate-employee-code',
                        beforeSend: function(request) {
                            request.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]')
                                .attr(
                                    'content'));
                        },
                        data: formJson,
                        success: function(response) {
                            if (response.status && response.data) {
                                setTimeout(function() {
                                    $('#employee_code').val(response.data);
                                }, 100);
                            } else {
                                $('#employee_code').val('');
                            }
                        }
                    });
                }
            });
        }
    </script>
    @if (!$company_id)
        @include('utils.getCompany')
    @endif
    @include('utils.getBranch')
    @include('utils.getEmployee')
    @include('utils.getTeamRole')
    @include('utils.getCountry')
    @include('utils.getStateByCountry')
    @include('utils.getCityByState')

    <script type="text/javascript">
        $(document).ready(function() {
            $('.sameAsParent').on('change', function() {
                let parentId = $(this).data('parent-id');
                let childId = $(this).data('child-id');
                if ($(this).is(':checked')) {
                    $('#' + childId).val($('#' + parentId).val());
                } else {
                    $('#' + childId).val('');
                }
            });
            
            function updateFullName() {
                var first = $("#first_name").val().trim();
                var middle = $("#middle_name").val().trim();
                var father = $("#father_name").val().trim();
                var full = [first, middle, father].filter(Boolean).join(" ");
                $("#full_name").val(full);
            }
            $("#first_name, #middle_name, #father_name").on("keyup change", updateFullName);

            flatpickr("#date_of_birth", {
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d-m-Y",
                maxDate: "today",
            });

            flatpickr("#date_of_anniversary", {
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d-m-Y",
                maxDate: "today",
            });

            $('#marital_status').change(function() {
                if ($(this).val() == 'married') {
                    $('#anniversary_div').show();
                } else {
                    $('#anniversary_div').hide();
                }
            });

            if ($('#marital_status').val() == 'married') {
                $('#anniversary_div').show();
            }
        });
    </script>
@endpush
