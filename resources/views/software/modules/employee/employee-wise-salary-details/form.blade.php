@extends('software.layout.app')

@php
    use App\Models\Company;
    $page_title = isset($modules['title']) ? $modules['title'] : null;
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $i = 0;
    $company_id = isset($modules['company_id']) ? $modules['company_id'] : null;
    if (is_object($company_id)) {
        $company_id = $company_id->company_id ?? null;
    }

    $colums = 'col-md-3 col-sm-12 mb-2';

    $companyHraPercentage = 40;
    if (isset($edit) && $edit?->company?->hra_percentage !== null) {
        $companyHraPercentage = (float) $edit->company->hra_percentage;
    } elseif ($company_id) {
        $companyHraPercentage = (float) (Company::find($company_id)?->hra_percentage ?? 40);
    }

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
                'route' => $route,
                'show_back_btn' => true,
            ])
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
                <input type="hidden" id="company_hra_percentage" value="{{ $companyHraPercentage }}">
                <div class="row">
                    @if (!$company_id)
                        <div class="{{ $colums ?? 'col-12' }}">
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
                    <div class="{{ $colums ?? 'col-12' }}">
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

                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Salary Classification <span class="text-danger">*</span></label>
                            <select class="form-control select2 w-100 @error('salary_classification') is-invalid @enderror"
                                name="salary_classification" required>
                                <option disabled selected>Select Salary Classification</option>
                                @foreach (config('constants.salary_classification') as $key => $value)
                                    <option value="{{ $key }}"
                                        @if (isset($edit))
                                            {{ $edit->salary_classification == $key ? 'selected' : '' }}
                                        @else
                                            {{ old('salary_classification', config('constants.salary_classification.PMS')) == $key ? 'selected' : '' }}
                                        @endif>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Week Off</label>
                            @php
                                $selectedWeekOff = old(
                                    'week_off',
                                    isset($edit) && !empty($edit->week_off)
                                        ? json_decode($edit->week_off, true) ?? []
                                        : []
                                );
                            @endphp
                            <select class="form-control select2 @error('week_off') is-invalid @enderror" name="week_off[]" multiple required>
                                @foreach (['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'] as $day)
                                    <option value="{{ $day }}" {{ in_array($day, $selectedWeekOff) ? 'selected' : '' }}>
                                        {{ $day }}
                                    </option>
                                @endforeach
                            </select>

                            @error('week_off')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror

                        </div>
                    </div>

                    {{-- <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label"> Working Hour <span class="text-danger">*</span></label>
                            <input id="working_hour" type="text"
                                class="form-control time-mask @error('working_hour') is-invalid @enderror"
                                name="working_hour"
                                value="{{ isset($edit) && $edit?->working_hour ? $edit?->working_hour : old('working_hour') }}"
                                placeholder="hh:mm:ss" min="00:01:01" max="23:59:59">
                            @error('working_hour')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label"> Breaking Hour <span class="text-danger">*</span></label>
                            <input id="breaking_hour" type="text"
                                class="form-control time-mask @error('breaking_hour') is-invalid @enderror"
                                name="breaking_hour"
                                value="{{ isset($edit) && $edit?->breaking_hour ? $edit?->breaking_hour : old('breaking_hour') }}"
                                placeholder="hh:mm:ss" min="00:01:01" max="23:59:59">
                            @error('breaking_hour')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label"> Half Day Hour <span class="text-danger">*</span></label>
                            <input id="half_day_hour" type="text"
                                class="form-control time-mask @error('half_day_hour') is-invalid @enderror"
                                name="half_day_hour"
                                value="{{ isset($edit) && $edit?->half_day_hour ? $edit?->half_day_hour : old('half_day_hour') }}"
                                placeholder="hh:mm:ss" min="00:01:01" max="23:59:59">
                            @error('half_day_hour')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label"> Present Day Hour <span class="text-danger">*</span></label>
                            <input id="present_day_hour" type="text"
                                class="form-control time-mask @error('present_day_hour') is-invalid @enderror"
                                name="present_day_hour"
                                value="{{ isset($edit) && $edit?->present_day_hour ? $edit?->present_day_hour : old('present_day_hour') }}"
                                placeholder="hh:mm:ss" min="00:01:01" max="23:59:59">
                            @error('present_day_hour')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div> --}}
                </div>
                <div class="row">
                    <div class='col-3'>
                        <label><strong>Overtime:</strong></label><br>
                        <div class="form-check form-check-inline">
                            <input type="radio" class="form-check-input" name="overtime" id="overtime_yes" value="1" id="yes"
                                {{ old('overtime', $edit?->overtime ?? '0') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="overtime_yes">Yes</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" class="form-check-input" name="overtime" id="overtime_no" value="0" id="no"
                                {{ old('overtime', $edit?->overtime ?? '0') == '0' ? 'checked' : '' }}>
                            <label class="form-check-label" for="overtime_no">No</label>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-12  ">
                        <label><strong>Is Welfare Fund Applied:</strong></label><br>
                        <div class="form-check form-check-inline">
                            <input type="radio" class="form-check-input" name="is_welfare_fund_applied" id="welfare_fund_applied_yes" value="1" id="yes"
                                {{ old('is_welfare_fund_applied', $edit?->is_welfare_fund_applied ?? '1') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="welfare_fund_applied_yes">Yes</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" class="form-check-input" name="is_welfare_fund_applied" id="welfare_fund_applied_no" value="0" id="no"
                                {{ old('is_welfare_fund_applied', $edit?->is_welfare_fund_applied ?? '0') == '0' ? 'checked' : '' }}>
                            <label class="form-check-label" for="welfare_fund_applied_no">No</label>
                        </div>
                    </div>

                    {{-- Welfare Fund Amount --}}
                    <div class="col-md-3 welfare-fund-related" style="display:none;">
                        <div class="form-group">
                            <label class="form-label">Welfare Fund Amount</label>
                            <input type="number" step="0.01" class="form-control @error('welfare_fund_amount') is-invalid @enderror" 
                                name="welfare_fund_amount"
                                value="{{ old('welfare_fund_amount', $edit?->welfare_fund_amount ?? 10) }}"
                                placeholder="Enter Welfare Fund Amount">
                             @error('welfare_fund_amount')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-12  ">
                        <label><strong>Leave Elegiblity:</strong></label><br>
                        <div class="form-check form-check-inline">
                            <input type="radio" class="form-check-input" name="leave_elegiblity" id="leave_elegiblity_yes" value="1" id="yes"
                                {{ old('leave_elegiblity', $edit?->leave_elegiblity ?? '1') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="leave_elegiblity_yes">Yes</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" class="form-check-input" name="leave_elegiblity" id="leave_elegiblity_no" value="0" id="no"
                                {{ old('leave_elegiblity', $edit?->leave_elegiblity ?? '0') == '0' ? 'checked' : '' }}>
                            <label class="form-check-label" for="leave_elegiblity_no">No</label>
                        </div>
                    </div>
                        {{-- Bonus --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <label><strong>Bonus Applied:</strong></label><br>
                        <div class="form-check form-check-inline">
                            <input type="radio" class="form-check-input" name="is_bonus_applied" id="bonus_applied_yes" value="1"
                                {{ old('is_bonus_applied', $edit?->is_bonus_applied ?? '1') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="bonus_applied_yes">Yes</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" class="form-check-input" name="is_bonus_applied" id="bonus_applied_no" value="0"
                                {{ old('is_bonus_applied', $edit?->is_bonus_applied ?? '0') == '0' ? 'checked' : '' }}>
                            <label class="form-check-label" for="bonus_applied_no">No</label>
                        </div>
                    </div>
                       {{-- Salary Calculation Month Count --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Salary Calculation Month Count</label>
                            <select class="form-control select2 @error('salary_calculation_month_count') is-invalid @enderror"
                                name="salary_calculation_month_count">
                                <option value="">Select</option>
                                @foreach (['Fix 30 Days', 'Per Month Total Days', 'Per Month Total Days - Week Off'] as $calculation_month)
                                    <option value="{{ $calculation_month }}"
                                        {{ old('salary_calculation_month_count', ($edit ?? null)?->salary_calculation_month_count ?? '') == $calculation_month ? 'selected' : '' }}>
                                        {{ $calculation_month }}
                                    </option>
                                @endforeach
                            </select>

                        </div>
                    </div>

                    {{-- PF Type --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">PF Type</label>
                            <select id="pf_type_select" class="form-control select2 w-100 @error('pf_type') is-invalid @enderror" name="pf_type">
                                <option disabled selected>Select PF Type</option>
                                @foreach (['NO-PF', 'PF-ABRY', 'COMPANY GIVE BOTH SIDE PF', 'EMPLOYEE'] as $pf_type)
                                    <option value="{{ $pf_type }}"
                                        {{ old('pf_type', ($edit ?? null)?->pf_type ?? 'NO-PF') == $pf_type ? 'selected' : '' }}>
                                        {{ ucfirst($pf_type) }}
                                    </option>
                                @endforeach
                            </select>

                        </div>
                    </div>

                </div>
               <div class="row mt-3 ">
                {{-- Sandwich Rule Flag --}}
                <div class="col-md-3">
                    <label><strong>Sandwich Rule Flag:</strong></label><br>
                    <div class="form-check form-check-inline">
                        <input type="radio" class="form-check-input sandwich-flag" id="sandwich_rule_flag_yes" name="sandwich_rule_flag" value="1"
                            {{ old('sandwich_rule_flag', $edit?->sandwich_rule_flag ?? '1') == '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="sandwich_rule_flag_yes">Yes</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input type="radio" class="form-check-input sandwich-flag" name="sandwich_rule_flag" id="sandwich_rule_flag_no" value="0"
                            {{ old('sandwich_rule_flag', $edit?->sandwich_rule_flag ?? '0') == '0' ? 'checked' : '' }}>
                        <label class="form-check-label" for="sandwich_rule_flag_no">No</label>
                    </div>
                </div>

                {{-- Sandwich Rule Applied On --}}
                <div class="col-md-3 sandwich-related">
                    <div class="form-group">
                        <label class="form-label">Sandwich Rule Applied On</label>
                        <select class="form-control select2 @error('sandwich_rule_applied_on') is-invalid @enderror"
                            name="sandwich_rule_applied_on">
                            <option value="">Select</option>
                            @foreach (['Both', 'Week Off', 'Holiday'] as $option)
                                <option value="{{ $option }}"
                                    {{ old('sandwich_rule_applied_on', ($edit ?? null)?->sandwich_rule_applied_on ?? '') == $option ? 'selected' : '' }}>
                                    {{ ucfirst($option) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Sandwich Rule Type --}}
                <div class="col-md-3 sandwich-related">
                    <div class="form-group">
                        <label class="form-label">Sandwich Rule Type</label>
                        <select class="form-control select2 @error('sandwich_rule_type') is-invalid @enderror"
                            name="sandwich_rule_type">
                            <option value="">Select</option>
                            @foreach (['Full Sandwich','Half Sandwich With Pay','Half Sandwich With Deduct'] as $rule_type)
                                <option value="{{ $rule_type }}"
                                    {{ old('sandwich_rule_type', ($edit ?? null)?->sandwich_rule_type ?? '') == $rule_type ? 'selected' : '' }}>
                                    {{ $rule_type }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="row mt-3">



                </div>
                  <div class="row mt-2">
                    {{-- PF --}}
                    <div class="col-md-3 pf-radio-container">
                        <label><strong>PF:</strong></label><br>
                        <div class="form-check form-check-inline">
                            <input type="radio" class="form-check-input pf-flag" id="pf_yes" name="pf" value="1"
                                {{ old('pf', $edit?->pf ?? '1') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="pf_yes">Yes</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" class="form-check-input pf-flag" id="pf_no" name="pf" value="0"
                                {{ old('pf', $edit?->pf ?? '0') == '0' ? 'checked' : '' }}>
                            <label class="form-check-label" for="pf_no">No</label>
                        </div>
                    </div>

                    {{-- PF Percentage --}}
                    <div class="col-md-3 pf-related">
                        <div class="form-group">
                            <label class="form-label">PF Percentage</label>
                            <input type="number" step="0.01" min="0" max="100"
                                class="form-control @error('pf_percentage') is-invalid @enderror"
                                name="pf_percentage"
                                value="{{ old('pf_percentage', ($edit->pf_percentage ?? null)) }}"
                                placeholder="Enter PF %">
                        </div>
                    </div>
                       {{-- PRADHANMANTRI PF --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <label><strong>PRADHANMANTRI PF:</strong></label><br>
                        <div class="form-check form-check-inline">
                            <input type="radio" class="form-check-input" name="pradhanmantri_pf" id="pradhanmantri_pf_yes" value="1"
                                {{ old('pradhanmantri_pf', $edit?->pradhanmantri_pf ?? '1') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="pradhanmantri_pf_yes">Yes</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" class="form-check-input" name="pradhanmantri_pf" id="pradhanmantri_pf_no" value="0"
                                {{ old('pradhanmantri_pf', $edit?->pradhanmantri_pf ?? '0') == '0' ? 'checked' : '' }}>
                            <label class="form-check-label" for="pradhanmantri_pf_no">No</label>
                        </div>
                    </div>

                    {{-- PRADHANMANTRI PF Percentage --}}
                    <div class="{{ $colums ?? 'col-12' }} pradhanmantri-pf-related">
                        <div class="form-group">
                            <label class="form-label">PRADHANMANTRI PF Percentage</label>
                            <input type="number" step="0.01" min="0" max="100"
                                class="form-control @error('pradhanmantri_pf_percentage') is-invalid @enderror"
                                name="pradhanmantri_pf_percentage"
                                value="{{ old('pradhanmantri_pf_percentage', ($edit->pradhanmantri_pf_percentage ?? null)) }}"
                                placeholder="Enter PRADHANMANTRI PF %">
                        </div>
                    </div>
                </div>

                <div class="row mt-3">

                </div>
                 <div class="row mt-3">
                    {{-- TDS --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <label><strong>TDS:</strong></label><br>
                        <div class="form-check form-check-inline">
                            <input type="radio" class="form-check-input" name="tds" id="tds_yes" value="1"
                                {{ old('tds', $edit?->tds ?? '1') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="tds_yes">Yes</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" class="form-check-input" name="tds" id="tds_no" value="0"
                                {{ old('tds', $edit?->tds ?? '0') == '0' ? 'checked' : '' }}>
                            <label class="form-check-label" for="tds_no">No</label>
                        </div>
                    </div>

                    {{-- TDS Percentage --}}
                    <div class="{{ $colums ?? 'col-12' }} tds-related">
                        <div class="form-group">
                            <label class="form-label">TDS Percentage</label>
                            <input type="number" step="0.01" min="0" max="100"
                                class="form-control @error('tds_percentage') is-invalid @enderror"
                                name="tds_percentage"
                                value="{{ old('tds_percentage', ($edit->tds_percentage ?? null)) }}"
                                placeholder="Enter TDS %">
                        </div>
                    </div>
                     {{-- Insurance --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <label><strong>Insurance:</strong></label><br>
                        <div class="form-check form-check-inline">
                            <input type="radio" class="form-check-input" name="insurance" id="insurance_yes" value="1"
                                {{ old('insurance', $edit?->insurance ?? '1') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="insurance_yes">Yes</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" class="form-check-input" name="insurance" id="insurance_no" value="0"
                                {{ old('insurance', $edit?->insurance ?? '0') == '0' ? 'checked' : '' }}>
                            <label class="form-check-label" for="insurance_no">No</label>
                        </div>
                    </div>

                    {{-- Insurance Amount --}}
                    <div class="{{ $colums ?? 'col-12' }} insurance-related">
                        <div class="form-group">
                            <label class="form-label">Insurance Amount</label>
                            <input type="number" step="0.01"
                                class="form-control @error('insurance_amount') is-invalid @enderror"
                                name="insurance_amount"
                                value="{{ old('insurance_amount', ($edit->insurance_amount ?? null)) }}"
                                placeholder="Enter Insurance Amount">
                        </div>
                    </div>
                 </div>
                  <div class="row mt-3">

                  </div>
                   <div class="row mt-3">
                    {{-- PT --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <label><strong>PT:</strong></label><br>
                        <div class="form-check form-check-inline">
                            <input type="radio" class="form-check-input" name="pt" id="pt_yes" value="1"
                                {{ old('pt', $edit?->pt ?? '1') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="pt_yes">Yes</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" class="form-check-input" name="pt" id="pt_no" value="0"
                                {{ old('pt', $edit?->pt ?? '0') == '0' ? 'checked' : '' }}>
                            <label class="form-check-label" for="pt_no">No</label>
                        </div>
                    </div>

                    {{-- PT Amount --}}
                    <div class="{{ $colums ?? 'col-12' }} pt-related">
                        <div class="form-group">
                            <label class="form-label">PT Amount</label>
                            <input type="number" step="0.01"
                                class="form-control @error('pt_amount') is-invalid @enderror"
                                name="pt_amount"
                                value="{{ old('pt_amount', ($edit->pt_amount ?? null)) }}"
                                placeholder="PT Amount">
                        </div>
                    </div>
                      {{-- Is ESI Company Side --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <label><strong>Is ESI Company Side:</strong></label><br>
                        <div class="form-check form-check-inline">
                            <input type="radio" class="form-check-input" name="is_esi_company_side" id="esi_company_side_yes" value="1"
                                {{ old('is_esi_company_side', $edit?->is_esi_company_side ?? '1') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="esi_company_side_yes">Yes</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" class="form-check-input" name="is_esi_company_side" id="esi_company_side_no" value="0"
                                {{ old('is_esi_company_side', $edit?->is_esi_company_side ?? '0') == '0' ? 'checked' : '' }}>
                            <label class="form-check-label" for="esi_company_side_no">No</label>
                        </div>
                    </div>

                    {{-- ESI Company Side (%) --}}
                    <div class="{{ $colums ?? 'col-12' }} esi-company-side-related">
                        <div class="form-group">
                            <label class="form-label">ESI Company Side (%)</label>
                            <input type="number" step="0.01" min="0" max="100"
                                class="form-control @error('esi_company_side_percentage') is-invalid @enderror"
                                name="esi_company_side_percentage"
                                value="{{ old('esi_company_side_percentage', ($edit->esi_company_side_percentage ?? null)) }}"
                                placeholder="Enter ESI Company Side (%)">
                        </div>
                    </div>
                   </div>
                    <div class="row mt-3">

                    </div>
                     <div class="row mt-3">
                    {{-- ESI Emloyee Side --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <label><strong>ESI Emloyee Side:</strong></label><br>
                        <div class="form-check form-check-inline">
                            <input type="radio" class="form-check-input" name="esi_employee_side" id="esi_employee_side_yes" value="1"
                                {{ old('esi_employee_side', $edit?->esi_employee_side ?? '1') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="esi_employee_side_yes">Yes</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" class="form-check-input" name="esi_employee_side" id="esi_employee_side_no" value="0"
                                {{ old('esi_employee_side', $edit?->esi_employee_side ?? '0') == '0' ? 'checked' : '' }}>
                            <label class="form-check-label" for="esi_employee_side_no">No</label>
                        </div>
                    </div>

                    {{-- Esi Employee Side Percentage(%) --}}
                    <div class="{{ $colums ?? 'col-12' }} esi-employee-side-related">
                        <div class="form-group">
                            <label class="form-label">Esi Employee Side Percentage(%)</label>
                            <input type="number" step="0.01" min="0" max="100" id="esi_employee_side_percentage"
                                class="form-control @error('esi_employee_side_percentage') is-invalid @enderror"
                                name="esi_employee_side_percentage"
                                value="{{ old('esi_employee_side_percentage', ($edit->esi_employee_side_percentage ?? null)) }}"
                                placeholder="Enter Esi Employee Side Percentage(%)">
                        </div>
                    </div>
                       {{-- Gratuity Calculation --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <label><strong>Gratuity Calculation:</strong></label><br>
                        <div class="form-check form-check-inline">
                            <input type="radio" class="form-check-input" name="gratuity_calculation" id="gratuity_calculation_yes" value="1"
                                {{ old('gratuity_calculation', $edit?->gratuity_calculation ?? '1') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="gratuity_calculation_yes">Yes</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" class="form-check-input" name="gratuity_calculation" id="gratuity_calculation_no" value="0"
                                {{ old('gratuity_calculation', $edit?->gratuity_calculation ?? '0') == '0' ? 'checked' : '' }}>
                            <label class="form-check-label" for="gratuity_calculation_no">No</label>
                        </div>
                    </div>
                     </div>
                      <div class="row mt-3">

                    </div>
                    <div class="row">

                    {{-- Basic + D.A. --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Basic + D.A.</label>
                            <input type="number"
                                class="form-control @error('basic_da') is-invalid @enderror salary-input"
                                name="basic_da" id="basic_da"
                                value="{{ old('basic_da', ($edit->basic_da	 ?? null)) }}"
                                placeholder="Enter Basic + D.A.">
                        </div>
                    </div>

                    {{-- HRA
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">HRA</label>
                            <input type="number"
                                class="form-control @error('hra') is-invalid @enderror salary-input"
                                name="hra" id="hra"
                                value="{{ old('hra', ($edit->hra ?? null)) }}"
                                placeholder="Enter HRA" readonly>
                        </div>
                    </div> --}}

                    {{-- HRA --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">HRA</label>
                            <input type="number"
                                class="form-control @error('hra') is-invalid @enderror salary-input"
                                name="hra" id="hra"
                                value="{{ old('hra', ($edit->hra ?? null)) }}"
                                placeholder="Enter HRA">
                        </div>
                    </div>



                    {{-- Conveyance Allowance --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Conveyance Allowance</label>
                            <input type="number"
                                class="form-control @error('conveyance_allowance') is-invalid @enderror salary-input" id="conveyance_allowance"
                                name="conveyance_allowance"
                                value="{{ old('conveyance_allowance', ($edit->conveyance_allowance	 ?? null)) }}"
                                placeholder="Enter Conveyance Allowance">
                        </div>
                    </div>

                    {{-- Medical Allowance --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Medical Allowance</label>
                            <input type="number"
                                class="form-control @error('medical_allowance') is-invalid @enderror salary-input" id="medical_allowance"
                                name="medical_allowance"
                                value="{{ old('medical_allowance', ($edit->medical_allowance	 ?? null)) }}"
                                placeholder="Enter Medical Allowance">
                        </div>
                    </div>

                    {{-- Special Allowance --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">Special Allowance</label>
                            <input type="number"
                                class="form-control @error('special_allowance') is-invalid @enderror salary-input" id="special_allowance"
                                name="special_allowance"
                                value="{{ old('special_allowance', ($edit->special_allowance	 ?? null)) }}"
                                placeholder="Enter Special Allowance">
                        </div>
                    </div>

                    {{-- CTC --}}
                    <div class="{{ $colums ?? 'col-12' }}">
                        <div class="form-group">
                            <label class="form-label">CTC</label>
                            <div class="input-group">
                                <input type="number"
                                    class="form-control @error('ctc') is-invalid @enderror"
                                    name="ctc" id="ctc"
                                    value="{{ old('ctc', ($edit->ctc ?? null)) }}"
                                    placeholder="Enter CTC" readonly>

                                {{-- Tooltip Icon / Label --}}
                                <span class="input-group-text"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    title="CTC = Basic D.A + HRA + Conveyance + Medical  + Special ">
                                    ₹
                                </span>
                            </div>
                        </div>
                    </div>



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

@section('page_leavel_script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cleave.js/1.6.0/cleave.min.js"></script>
@endsection

@push('page_scripts')

    @if (!$company_id)
        @include('utils.getCompany')
    @endif
    @include('utils.getEmployee')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    });
</script>

     <script type="text/javascript">
        $(document).ready(function() {
            $('.time-mask').each(function() {
                const $input = $(this);

                // Get existing value or fallback to default (e.g., current time)
                let initialTime = $input.val().trim();

                if (!initialTime) {
                    // Set fallback default (current time in HH:mm:ss format)
                    const now = new Date();
                    const hours = String(now.getHours()).padStart(2, '0');
                    const minutes = String(now.getMinutes()).padStart(2, '0');
                    const seconds = String(now.getSeconds()).padStart(2, '0');
                    initialTime = `${hours}:${minutes}:${seconds}`;
                    $input.val(initialTime);
                }

                // Initialize Cleave.js mask
                new Cleave(this, {
                    time: true,
                    timePattern: ['h', 'm', 's']
                });
            });

            function togglePFFields() {
                let pfValue = $('input[name="pf"]:checked').val();
                if (pfValue === '1') {
                    $('.pf-related').show();
                } else {
                    $('.pf-related').hide();
                    $('.pf-related input').val('');
                }

                let pradhanmantripfValue = $('input[name="pradhanmantri_pf"]:checked').val();
                if (pradhanmantripfValue === '1') {
                    $('.pradhanmantri-pf-related').show();
                } else {
                    $('.pradhanmantri-pf-related').hide();
                    $('.pradhanmantri-pf-related input').val('');
                }

                let tdsValue = $('input[name="tds"]:checked').val();
                if (tdsValue === '1') {
                    $('.tds-related').show();
                } else {
                    $('.tds-related').hide();
                    $('.tds-related input').val('');
                }

                let insuranceValue = $('input[name="insurance"]:checked').val();
                if (insuranceValue === '1') {
                    $('.insurance-related').show();
                } else {
                    $('.insurance-related').hide();
                    $('.insurance-related input').val('');
                }

                let ptValue = $('input[name="pt"]:checked').val();
                if (ptValue === '1') {
                    $('.pt-related').show();
                } else {
                    $('.pt-related').hide();
                    $('.pt-related input').val('');
                }

                let esiCompanySideValue = $('input[name="is_esi_company_side"]:checked').val();
                if (esiCompanySideValue === '1') {
                    $('.esi-company-side-related').show();
                } else {
                    $('.esi-company-side-related').hide();
                    $('.esi-company-side-related input').val('');
                }

                let esiEmployeeSideValue = $('input[name="esi_employee_side"]:checked').val();
                if (esiEmployeeSideValue === '1') {
                    $('.esi-employee-side-related').show();
                } else {
                    $('.esi-employee-side-related').hide();
                    $('.esi-employee-side-related input').val('');
                }

                let welfareFundValue = $('input[name="is_welfare_fund_applied"]:checked').val();
                if (welfareFundValue === '1') {
                    $('.welfare-fund-related').show();
                } else {
                    $('.welfare-fund-related').hide();
                }

                let sandwichruleValue = $('input[name="sandwich_rule_flag"]:checked').val();
                if (sandwichruleValue === '1') {
                    $('.sandwich-related').show();
                } else {
                    $('.sandwich-related').hide();
                    $('.sandwich-related input').val('');
                }
            }

            togglePFFields();

            // Store the NO-PF option for restoration
            let noPfOptionElement = null;
            
            // Handle PF radio button change - control NO-PF option availability
            function handlePFRadioChange() {
                const pfValue = $('input[name="pf"]:checked').val();
                const currentPFType = $('#pf_type_select').val();
                const $pfTypeSelect = $('#pf_type_select');
                const $noPfOption = $pfTypeSelect.find('option[value="NO-PF"]');
                const isSelect2 = $pfTypeSelect.hasClass('select2-hidden-accessible');
                
                if (pfValue === '1') {
                    // When PF is "Yes" - Remove "NO-PF" option completely
                    // Store the option element if it exists and not already stored
                    if ($noPfOption.length && !noPfOptionElement) {
                        noPfOptionElement = $noPfOption.detach();
                    } else if ($noPfOption.length) {
                        // If already stored, just remove the current one
                        $noPfOption.remove();
                    }
                    
                    // If current PF Type is "NO-PF", change it to first available option
                    if (currentPFType === 'NO-PF' || !currentPFType) {
                        // Find first enabled option that's not "NO-PF" and not the disabled placeholder
                        const firstEnabledOption = $pfTypeSelect.find('option:not([value="NO-PF"]):not([disabled]):not([value=""]):first');
                        if (firstEnabledOption.length) {
                            $pfTypeSelect.val(firstEnabledOption.val());
                        } else {
                            // Fallback: select any non-disabled option
                            const fallbackOption = $pfTypeSelect.find('option:not([disabled]):not([value=""]):first');
                            if (fallbackOption.length) {
                                $pfTypeSelect.val(fallbackOption.val());
                            }
                        }
                    }
                    
                    // Update select2 if initialized
                    if (isSelect2) {
                        $pfTypeSelect.trigger('change.select2');
                    }
                    
                    // Show PF Percentage field
                    $('.pf-related').show();
                } else if (pfValue === '0') {
                    // When PF is "No"
                    // Store and detach all other PF Type options (PF-ABRY, COMPANY GIVE BOTH SIDE PF, EMPLOYEE)
                    const otherOptions = pfTypeSelect.find('option[value="PF-ABRY"], option[value="COMPANY GIVE BOTH SIDE PF"], option[value="EMPLOYEE"]');
                    if (otherOptions.length > 0) {
                        // If options are already in DOM, detach and store them
                        if (!storedOtherPFOptions || storedOtherPFOptions.length === 0) {
                            storedOtherPFOptions = otherOptions.detach();
                        }
                    }
                    
                    // Enable "NO-PF" option in PF Type dropdown
                    pfTypeSelect.find('option[value="NO-PF"]').prop('disabled', false);
                    
                    // Automatically select "NO-PF" in PF Type
                    pfTypeSelect.val('NO-PF');
                    
                    // Trigger select2 update if select2 is initialized
                    if (pfTypeSelect.hasClass('select2-hidden-accessible')) {
                        pfTypeSelect.trigger('change.select2');
                    }
                    
                    // Clear PF Percentage value
                    $('.pf-related input[name="pf_percentage"]').val('');
                    // Hide PF Percentage field
                    $('.pf-related').hide();
                }
            }

            // Prevent selecting "NO-PF" when PF is "Yes"
            function preventNoPFSelection() {
                const pfValue = $('input[name="pf"]:checked').val();
                const selectedPFType = $('#pf_type_select').val();
                
                if (pfValue === '1' && selectedPFType === 'NO-PF') {
                    // If PF is "Yes" and "NO-PF" is selected, prevent it
                    const $pfTypeSelect = $('#pf_type_select');
                    // Find first available option that's not "NO-PF"
                    const firstEnabledOption = $pfTypeSelect.find('option:not([value="NO-PF"]):not([disabled]):first');
                    if (firstEnabledOption.length) {
                        $pfTypeSelect.val(firstEnabledOption.val());
                        // Trigger select2 update if select2 is initialized
                        if ($pfTypeSelect.hasClass('select2-hidden-accessible')) {
                            $pfTypeSelect.trigger('change.select2');
                        }
                        // Show warning message
                        alert('PF Type "NO-PF" cannot be selected when PF is "Yes". Please select PF as "No" first.');
                    }
                }
            }

            // Initialize on page load (after a small delay to ensure select2 is initialized)
            setTimeout(function() {
                handlePFRadioChange();
                togglePFFields();
            }, 100);

            // Handle PF radio button change
            $(document).on('change', 'input[name="pf"]', function() {
                handlePFRadioChange();
                togglePFFields();
            });

            // Prevent selecting "NO-PF" when PF is "Yes" - handle both regular change and select2 events
            $(document).on('change', '#pf_type_select', function(e) {
                preventNoPFSelection();
            });
            
            // Also handle select2:select event for better compatibility with select2
            $(document).on('select2:select', '#pf_type_select', function(e) {
                preventNoPFSelection();
            });
            $(document).on('change', 'input[name="pradhanmantri_pf"]', togglePFFields);
            $(document).on('change', 'input[name="tds"]', togglePFFields);
            $(document).on('change', 'input[name="insurance"]', togglePFFields);
            $(document).on('change', 'input[name="pt"]', togglePFFields);
            $(document).on('change', 'input[name="is_esi_company_side"]', togglePFFields);
            $(document).on('change', 'input[name="esi_employee_side"]', togglePFFields);
            $(document).on('change', 'input[name="sandwich_rule_flag"]', togglePFFields);
            $(document).on('change', 'input[name="is_welfare_fund_applied"]', togglePFFields);

            let companyHraPercentage = parseFloat($('#company_hra_percentage').val()) || 40;

            function calculateHRA() {
                 // HRA is now manual, just return the current value or 0
                return parseFloat($('#hra').val()) || 0;
            }

            function calculateCTC() {
                const basic_da = parseFloat($('#basic_da').val()) || 0;
                // Use the manual HRA value
                const hra = parseFloat($('#hra').val()) || 0; 
                const conveyance = parseFloat($('#conveyance_allowance').val()) || 0;
                const medical = parseFloat($('#medical_allowance').val()) || 0;
                const special = parseFloat($('#special_allowance').val()) || 0;

                const total = basic_da + hra + conveyance + medical + special;
                $('#ctc').val(total.toFixed(2));
            }

            function setCompanyHraPercentage(value) {
                const numericValue = parseFloat(value);
                companyHraPercentage = Number.isFinite(numericValue) ? numericValue : 40;
                $('#company_hra_percentage').val(companyHraPercentage);
                calculateCTC();
            }

            function syncCompanyHraFromSelection() {
                const selected = $('.search_by_company option:selected');
                if (!selected.length) {
                    return;
                }

                const rawData = selected.attr('data-companydata');
                if (!rawData) {
                    setCompanyHraPercentage(companyHraPercentage);
                    return;
                }

                try {
                    const parsed = JSON.parse(rawData);
                    if (Object.prototype.hasOwnProperty.call(parsed, 'hra_percentage') && parsed.hra_percentage !== null) {
                        setCompanyHraPercentage(parsed.hra_percentage);
                    } else {
                        setCompanyHraPercentage(40);
                    }
                } catch (error) {
                    setCompanyHraPercentage(40);
                }
            }

            $(document).on('input', '.salary-input', function(){
                calculateCTC();
            });

            $(document).on('change', '.search_by_company', function(){
                syncCompanyHraFromSelection();
            });

            if ($('.search_by_company').length) {
                syncCompanyHraFromSelection();
            }

            calculateCTC();
        });
    </script>
@endpush
