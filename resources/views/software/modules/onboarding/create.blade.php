@extends('software.layout.app')

@php
    $page_title = 'Initiate New Employee Onboarding';
    $folder_path = $modules['folder_path'] ?? 'software.modules.onboarding';
    $route = $modules['route'] ?? 'onboarding';
@endphp

@section('title', $page_title)

@section('page_leavel_style')
    <link rel="stylesheet" href="{{ asset('software/vendor/libs/select2/select2.css') }}" />
    <link rel="stylesheet" href="{{ asset('software/vendor/libs/flatpickr/flatpickr.css') }}" />
    <style>
        /* One-Stop Industry & Role Selector Premium Styling */
        .template-picker-container {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(75, 70, 92, 0.05);
            overflow: hidden;
        }
        .template-picker-header {
            background: linear-gradient(135deg, #f8faff 0%, #edf3fc 100%);
            border-bottom: 1px solid #e2e8f0;
            padding: 12px 18px;
        }
        .industry-tab-pill {
            font-size: 0.78rem;
            font-weight: 600;
            padding: 5px 13px;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            color: #475569;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
        }
        .industry-tab-pill:hover {
            background: #f1f5f9;
            color: #266BEE;
            border-color: #cbd5e1;
            transform: translateY(-1px);
        }
        .industry-tab-pill.active {
            background: #266BEE !important;
            color: #ffffff !important;
            border-color: #266BEE !important;
            box-shadow: 0 2px 8px rgba(38, 107, 238, 0.3);
        }
        .role-grid-container {
            max-height: 240px;
            overflow-y: auto;
            scrollbar-width: thin;
            padding: 14px 18px;
        }
        .role-chip-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 9px;
            padding: 9px 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            height: 100%;
            user-select: none;
        }
        .role-chip-card:hover {
            border-color: #266BEE;
            background: #f8faff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(38, 107, 238, 0.1);
        }
        .role-chip-card.active-selected {
            border-color: #266BEE !important;
            background: #f0f5ff !important;
            box-shadow: 0 0 0 1.5px #266BEE, 0 4px 12px rgba(38, 107, 238, 0.15) !important;
        }
        .role-chip-card.active-selected .role-chip-title {
            color: #266BEE !important;
            font-weight: 700 !important;
        }
        .role-icon-box {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.05rem;
            flex-shrink: 0;
        }
        .role-chip-title {
            font-size: 0.82rem;
            font-weight: 600;
            color: #1e293b;
            line-height: 1.25;
            margin-bottom: 2px;
        }
        .role-chip-subtitle {
            font-size: 0.72rem;
            color: #64748b;
            line-height: 1.2;
        }
        .industry-it_tech .role-icon-box { background: rgba(38, 107, 238, 0.12); color: #266bee; }
        .industry-industrial .role-icon-box { background: rgba(245, 158, 11, 0.12); color: #d97706; }
        .industry-accounts .role-icon-box { background: rgba(16, 185, 129, 0.12); color: #059669; }
        .industry-hr_admin .role-icon-box { background: rgba(139, 92, 246, 0.12); color: #7c3aed; }
        .industry-sales_marketing .role-icon-box { background: rgba(6, 182, 212, 0.12); color: #0891b2; }
        .industry-logistics .role-icon-box { background: rgba(236, 72, 153, 0.12); color: #db2777; }
        .industry-general .role-icon-box { background: rgba(100, 116, 139, 0.12); color: #475569; }
    </style>
@endsection

@section('content')
<div class="px-1 pb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        @include('software.inlcudes.breadcrumb', [
            'breadcrumbArray' => [
                ['title' => 'Employee Onboarding', 'url' => route('onboarding.index')],
                ['title' => 'Initiate Onboarding', 'url' => ''],
            ],
            'route' => $route,
            'show_back_btn' => true,
        ])
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0 mb-5">
        <div class="card-body">
            <form action="{{ route('onboarding.store') }}" method="POST" id="onboarding-create-form">
                @csrf

                <!-- Section: Personal Information -->
                <div class="d-flex align-items-center mb-3 pb-2 border-bottom">
                    <span class="badge bg-label-primary rounded-circle p-2 me-2"><i class="ti ti-user"></i></span>
                    <h5 class="mb-0">1. Personal & Contact Details</h5>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label" for="first_name">First Name (Surname) <span class="text-danger">*</span></label>
                        <input type="text" id="first_name" name="first_name" class="form-control" value="{{ old('first_name') }}" required placeholder="Enter Surname" />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="middle_name">Middle Name (Candidate Name)</label>
                        <input type="text" id="middle_name" name="middle_name" class="form-control" value="{{ old('middle_name') }}" placeholder="Enter First Name" />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="father_name">Father / Husband Name</label>
                        <input type="text" id="father_name" name="father_name" class="form-control" value="{{ old('father_name') }}" placeholder="Enter Father / Husband Name" />
                    </div>

                    <div class="col-md-4">
                        <label class="form-label" for="email">Email Address <span class="text-danger">*</span></label>
                        <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required placeholder="Enter Email Address" />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="contact_number">Mobile Number <span class="text-danger">*</span></label>
                        <input type="text" id="contact_number" name="contact_number" class="form-control" value="{{ old('contact_number') }}" required placeholder="Enter Mobile Number" maxlength="10" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);" />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="other_number">Emergency / Alternate Contact</label>
                        <input type="text" id="other_number" name="other_number" class="form-control" value="{{ old('other_number') }}" placeholder="Enter Alternate Contact Number" maxlength="10" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);" />
                    </div>

                    <div class="col-md-3">
                        <label class="form-label" for="date_of_birth">Date of Birth</label>
                        <input type="text" id="date_of_birth" name="date_of_birth" class="form-control auto-date-mask" value="{{ old('date_of_birth') }}" placeholder="DD-MM-YYYY" maxlength="10" inputmode="numeric" />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="gender">Gender</label>
                        <select id="gender" name="gender" class="form-select select2">
                            <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                            <option value="Other" {{ old('gender') == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="blood_group">Blood Group</label>
                        <select id="blood_group" name="blood_group" class="form-select select2">
                            <option value="">Select Blood Group</option>
                            @foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bg)
                                <option value="{{ $bg }}" {{ old('blood_group') == $bg ? 'selected' : '' }}>{{ $bg }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="marital_status">Marital Status</label>
                        <select id="marital_status" name="marital_status" class="form-select select2">
                            <option value="Single" {{ old('marital_status') == 'Single' ? 'selected' : '' }}>Single</option>
                            <option value="Married" {{ old('marital_status') == 'Married' ? 'selected' : '' }}>Married</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="current_address">Current Address</label>
                        <textarea id="current_address" name="current_address" class="form-control" rows="2" placeholder="Enter Current Address">{{ old('current_address') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="permanent_address">Permanent Address</label>
                        <textarea id="permanent_address" name="permanent_address" class="form-control" rows="2" placeholder="Enter Permanent Address">{{ old('permanent_address') }}</textarea>
                    </div>
                </div>

                <!-- Section: Job & Department Allocation -->
                <div class="d-flex align-items-center mb-3 pb-2 border-bottom">
                    <span class="badge bg-label-success rounded-circle p-2 me-2"><i class="ti ti-briefcase"></i></span>
                    <h5 class="mb-0">2. Job Role & Reporting Allocation</h5>
                </div>

                <div class="row g-3 mb-4">
                    <!-- Row 1: Core Allocation -->
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label" for="joining_date">Date of Joining</label>
                        <input type="text" id="joining_date" name="joining_date" class="form-control auto-date-mask" value="{{ old('joining_date', date('d-m-Y')) }}" placeholder="DD-MM-YYYY" maxlength="10" inputmode="numeric" />
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label" for="branch_id">Branch</label>
                        <select id="branch_id" name="branch_id" class="form-select select2">
                            <option value="">Select Branch</option>
                            @foreach ($branches as $b)
                                <option value="{{ $b->id }}" {{ old('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label" for="department_id">Department</label>
                        <select id="department_id" name="department_id" class="form-select select2">
                            <option value="">Select Department</option>
                            @foreach ($departments as $d)
                                <option value="{{ $d->id }}" {{ old('department_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label" for="designation_id">Designation</label>
                        <select id="designation_id" name="designation_id" class="form-select select2">
                            <option value="">Select Designation</option>
                            @foreach ($designations as $des)
                                <option value="{{ $des->id }}" {{ old('designation_id') == $des->id ? 'selected' : '' }}>{{ $des->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Row 2: Management & Role Allocation -->
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label" for="reporting_manager_id">Reporting Person / Manager</label>
                        <select id="reporting_manager_id" name="reporting_manager_id" class="form-select select2">
                            <option value="">Select Reporting Manager</option>
                            @foreach ($employees as $emp)
                                <option value="{{ $emp->id }}" {{ old('reporting_manager_id') == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->full_name ?: ($emp->first_name . ' ' . $emp->last_name) }} ({{ $emp->employee_code ?? 'EMP#' . $emp->id }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label" for="buddy_id">Onboarding Buddy / Mentor</label>
                        <select id="buddy_id" name="buddy_id" class="form-select select2">
                            <option value="">Select Mentor (Optional)</option>
                            @foreach ($employees as $emp)
                                <option value="{{ $emp->id }}" {{ old('buddy_id') == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->full_name ?: ($emp->first_name . ' ' . $emp->last_name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label" for="role_id">System Role</label>
                        <select id="role_id" name="role_id" class="form-select select2">
                            <option value="">Select Role</option>
                            @foreach ($roles as $r)
                                <option value="{{ $r->id }}" {{ old('role_id') == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label" for="shift_id">Work Shift</label>
                        <select id="shift_id" name="shift_id" class="form-select select2">
                            <option value="">Select Shift</option>
                            @foreach ($shifts as $s)
                                <option value="{{ $s->id }}" {{ old('shift_id') == $s->id ? 'selected' : '' }}>
                                    {{ $s->name }}@if(!empty($s->punch_in_minimum) && !empty($s->punch_out)) ({{ \Carbon\Carbon::parse($s->punch_in_minimum)->format('h:i A') }} - {{ \Carbon\Carbon::parse($s->punch_out)->format('h:i A') }})@endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Row 3: Employment Type & Probation Policy -->
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label" for="employment_type">Employment Type</label>
                        <select id="employment_type" name="employment_type" class="form-select select2">
                            <option value="">Select Employment Type</option>
                            @foreach ($employeeTypes as $et)
                                <option value="{{ $et->id }}" {{ old('employment_type') == $et->id ? 'selected' : '' }}>{{ $et->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label" for="probation_period_months">Probation Period</label>
                        <select id="probation_period_months" name="probation_period_months" class="form-select select2">
                            <option value="0" {{ old('probation_period_months') === '0' ? 'selected' : '' }}>No Probation (Direct Confirmed)</option>
                            <option value="1" {{ old('probation_period_months') == 1 ? 'selected' : '' }}>1 Month</option>
                            <option value="2" {{ old('probation_period_months') == 2 ? 'selected' : '' }}>2 Months</option>
                            <option value="3" {{ old('probation_period_months', 3) == 3 ? 'selected' : '' }}>3 Months (Standard)</option>
                            <option value="6" {{ old('probation_period_months') == 6 ? 'selected' : '' }}>6 Months</option>
                            <option value="12" {{ old('probation_period_months') == 12 ? 'selected' : '' }}>12 Months (1 Year)</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label" for="probation_end_date">Probation End / Confirmation Date</label>
                        <input type="text" id="probation_end_date" name="probation_end_date" class="form-control auto-date-mask" value="{{ old('probation_end_date') }}" placeholder="DD-MM-YYYY" maxlength="10" inputmode="numeric" />
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label" for="probation_status">Initial Probation Status</label>
                        <select id="probation_status" name="probation_status" class="form-select select2">
                            <option value="on_probation" {{ old('probation_status') == 'on_probation' ? 'selected' : '' }}>On Probation</option>
                            <option value="confirmed" {{ old('probation_status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="extended" {{ old('probation_status') == 'extended' ? 'selected' : '' }}>Extended</option>
                            <option value="waived" {{ old('probation_status') == 'waived' ? 'selected' : '' }}>Waived / Direct</option>
                        </select>
                    </div>
                </div>

                <!-- Section: Job Description & KRA/KPI Expectations -->
                <div class="d-flex align-items-center mb-3 pb-2 border-bottom">
                    <span class="badge bg-label-warning rounded-circle p-2 me-2"><i class="ti ti-target-arrow"></i></span>
                    <h5 class="mb-0">3. Job Description (JD) & Attendance / Performance KPIs</h5>
                </div>

                <!-- One-Stop Industry & Role Template Switcher -->
                @php
                    $allRoleTemplates = \App\Models\Onboarding::getRoleTemplates();
                    $industryCategories = \App\Models\Onboarding::getIndustryCategories();
                @endphp
                <div class="template-picker-container mb-4">
                    <!-- Header -->
                    <div class="template-picker-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-2 text-primary d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="ti ti-sparkles fs-4 text-primary"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold text-heading">One-Stop Role & Benchmark Auto-Filler</h6>
                                <small class="text-muted">Click any role below to automatically fill standard JD, Attendance & Targets</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="input-group input-group-sm" style="width: 200px;">
                                <span class="input-group-text bg-white border-end-0 py-1"><i class="ti ti-search text-muted small"></i></span>
                                <input type="text" id="role-create-search-input" class="form-control bg-white border-start-0 ps-0 py-1" placeholder="Search role (e.g. CNC, Dev)...">
                            </div>
                            <span class="badge bg-label-primary px-3 py-2 rounded-pill" id="active-create-template-badge">
                                <i class="ti ti-sparkles me-1"></i> <span id="active-create-template-title">Auto-matched from Designation</span>
                            </span>
                        </div>
                    </div>

                    <!-- Industry Filter Pills -->
                    <div class="p-2 px-3 border-bottom bg-white">
                        <div class="d-flex flex-wrap gap-1 industry-filter-pills">
                            @foreach($industryCategories as $indKey => $ind)
                                <button type="button" class="industry-tab-pill {{ $indKey === 'all' ? 'active' : '' }} btn-filter-create-industry" data-filter="{{ $indKey }}">
                                    <i class="ti {{ $ind['icon'] }} me-1"></i> {{ $ind['label'] }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Role Cards Grid -->
                    <div class="role-grid-container bg-light bg-opacity-25">
                        <div class="row g-2" id="create-role-cards-grid">
                            @foreach($allRoleTemplates as $tKey => $template)
                                @php
                                    $indClass = 'industry-' . ($template['industry'] ?? 'general');
                                @endphp
                                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 role-create-card-col" data-role="{{ $tKey }}" data-industry="{{ $template['industry'] ?? 'general' }}" data-title="{{ strtolower($template['title'] . ' ' . ($template['badge'] ?? '')) }}">
                                    <div class="role-chip-card {{ $indClass }} btn-create-role-template" data-role="{{ $tKey }}">
                                        <div class="role-icon-box">
                                            <i class="ti {{ $template['icon'] ?? 'ti-user' }}"></i>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden">
                                            <div class="role-chip-title text-truncate" title="{{ $template['title'] }}">{{ $template['title'] }}</div>
                                            <div class="role-chip-subtitle d-flex align-items-center gap-1">
                                                <span class="badge bg-label-secondary p-0 px-1 font-size-10">{{ $template['industry_label'] ?? 'General' }}</span>
                                                <span>•</span>
                                                <span>{{ count($template['kra_kpis'] ?? []) }} KRAs</span>
                                            </div>
                                        </div>
                                        <div class="role-check-indicator text-primary d-none">
                                            <i class="ti ti-circle-check-filled fs-5"></i>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div id="no-create-role-found-msg" class="text-center py-4 text-muted d-none">
                            <i class="ti ti-search-off fs-2 mb-1 d-block opacity-50"></i>
                            <span>No matching roles found. Try a different keyword or select "All Industries".</span>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-12">
                        <label class="form-label fw-semibold" for="job_description">Role Job Description (JD) / Key Deliverables</label>
                        <textarea id="job_description" name="job_description" class="form-control" style="field-sizing: content; min-height: 140px; line-height: 1.6; overflow-y: hidden; resize: none;" oninput="this.style.height = ''; this.style.height = (this.scrollHeight + 10) + 'px';" placeholder="Enter Core Responsibilities, Job Overview, Tasks, and Deliverables">{{ old('job_description') }}</textarea>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label" for="attendance_target_percentage">Monthly Attendance Target (%)</label>
                        <div class="input-group">
                            <input type="number" id="attendance_target_percentage" name="attendance_target_percentage" class="form-control" value="{{ old('attendance_target_percentage', 95) }}" min="50" max="100" placeholder="e.g. 95" />
                            <span class="input-group-text">%</span>
                        </div>
                        <small class="text-muted">Target attendance rate per monthly payroll cycle</small>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label" for="late_mark_tolerance">Punctuality / Grace Late Marks Tolerance</label>
                        <div class="input-group">
                            <input type="number" id="late_mark_tolerance" name="late_mark_tolerance" class="form-control" value="{{ old('late_mark_tolerance', 2) }}" min="0" max="10" placeholder="e.g. 2" />
                            <span class="input-group-text">times / month</span>
                        </div>
                        <small class="text-muted">Max acceptable grace late arrivals per month</small>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label" for="daily_working_hours_target">Daily Shift Working Hours Target</label>
                        <div class="input-group">
                            <input type="number" step="0.25" id="daily_working_hours_target" name="daily_working_hours_target" class="form-control" value="{{ old('daily_working_hours_target', 8.50) }}" min="4" max="14" placeholder="e.g. 8.5" />
                            <span class="input-group-text">Hrs / Day</span>
                        </div>
                        <small class="text-muted">Standard daily working hours requirement</small>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 pt-4 pb-2 mt-4 border-top">
                    <a href="{{ route('onboarding.index') }}" class="btn btn-label-secondary waves-effect px-4">
                        <i class="ti ti-x me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary waves-effect waves-light px-4">
                        Save & Continue to Document Collection <i class="ti ti-arrow-right ms-1"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('page_leavel_script')
    <script src="{{ asset('software/vendor/libs/select2/select2.js') }}"></script>
    <script src="{{ asset('software/vendor/libs/flatpickr/flatpickr.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({ width: '100%' });

            // Auto-resize Job Summary Textarea
            function autoResizeCreateJd() {
                let el = document.getElementById('job_description');
                if (el) {
                    el.style.height = 'auto';
                    el.style.height = (el.scrollHeight + 15) + 'px';
                }
            }
            $('#job_description').on('input keyup change', autoResizeCreateJd);
            autoResizeCreateJd();

            // Auto Date Formatter: typing '10' -> '10-', typing '11' -> '10-11-'
            function autoMaskDateInput(e) {
                let input = e.target;
                if (e.inputType === 'deleteContentBackward' || e.inputType === 'deleteContentForward') {
                    return;
                }
                let val = input.value.replace(/[^0-9]/g, '');
                if (val.length > 8) val = val.substring(0, 8);

                let formatted = '';
                if (val.length >= 5) {
                    formatted = val.substring(0, 2) + '-' + val.substring(2, 4) + '-' + val.substring(4);
                } else if (val.length === 4) {
                    formatted = val.substring(0, 2) + '-' + val.substring(2, 4) + '-';
                } else if (val.length === 3) {
                    formatted = val.substring(0, 2) + '-' + val.substring(2);
                } else if (val.length === 2) {
                    formatted = val.substring(0, 2) + '-';
                } else {
                    formatted = val;
                }
                input.value = formatted;
            }

            $(document).on('input', '.auto-date-mask', autoMaskDateInput);

            // Date of Birth (dd-mm-yyyy)
            flatpickr("#date_of_birth", {
                dateFormat: "d-m-Y",
                maxDate: "today",
                allowInput: true
            });

            // Joining Date (dd-mm-yyyy)
            let fpJoining = flatpickr("#joining_date", {
                dateFormat: "d-m-Y",
                defaultDate: "{{ old('joining_date', date('d-m-Y')) }}",
                allowInput: true
            });

            // Probation End Date (dd-mm-yyyy)
            let fpProbation = flatpickr("#probation_end_date", {
                dateFormat: "d-m-Y",
                allowInput: true
            });

            function calculateProbationEnd() {
                let joinVal = $('#joining_date').val();
                let months = parseInt($('#probation_period_months').val());
                if (joinVal && !isNaN(months)) {
                    if (months === 0) {
                        $('#probation_status').val('confirmed').trigger('change');
                        fpProbation.setDate(joinVal);
                    } else {
                        let parts = joinVal.split('-');
                        let d;
                        if (parts.length === 3 && parts[0].length === 2) {
                            d = new Date(parts[2], parseInt(parts[1]) - 1, parts[0]);
                        } else {
                            d = new Date(joinVal);
                        }
                        d.setMonth(d.getMonth() + months);
                        let yyyy = d.getFullYear();
                        let mm = String(d.getMonth() + 1).padStart(2, '0');
                        let dd = String(d.getDate()).padStart(2, '0');
                        let formatted = `${dd}-${mm}-${yyyy}`;
                        fpProbation.setDate(formatted);
                    }
                }
            }

            $('#joining_date, #probation_period_months').on('change input', calculateProbationEnd);
            if (!$('#probation_end_date').val()) {
                calculateProbationEnd();
            }

            // Role Template Auto-Filler
            function loadRoleTemplate(roleKey) {
                $.ajax({
                    url: "{{ url('software/onboarding/role-template') }}/" + roleKey,
                    type: 'GET',
                    success: function(res) {
                        if (res.status && res.template) {
                            let t = res.template;
                            $('#job_description').val(t.job_description);
                            $('#attendance_target_percentage').val(t.attendance_target_percentage);
                            $('#late_mark_tolerance').val(t.late_mark_tolerance);
                            $('#daily_working_hours_target').val(t.daily_working_hours_target);
                            $('#active-create-template-badge').html('<i class="ti ti-check me-1"></i>Loaded: ' + t.title);
                            setTimeout(autoResizeCreateJd, 50);
                        }
                    }
                });
            }

            // Industry Filter in Create
            $('.btn-filter-create-industry').on('click', function() {
                let filter = $(this).data('filter');
                $('.btn-filter-create-industry').removeClass('active');
                $(this).addClass('active');

                $('#role-create-search-input').val('');
                let visibleCount = 0;

                if (filter === 'all') {
                    $('.role-create-card-col').show();
                    visibleCount = $('.role-create-card-col').length;
                } else {
                    $('.role-create-card-col').each(function() {
                        if ($(this).data('industry') === filter) {
                            $(this).show();
                            visibleCount++;
                        } else {
                            $(this).hide();
                        }
                    });
                }

                if (visibleCount === 0) {
                    $('#no-create-role-found-msg').removeClass('d-none');
                } else {
                    $('#no-create-role-found-msg').addClass('d-none');
                }
            });

            // Live Search in Create
            $('#role-create-search-input').on('input keyup', function() {
                let term = $(this).val().toLowerCase().trim();
                let activeFilter = $('.btn-filter-create-industry.active').data('filter') || 'all';
                let visibleCount = 0;

                $('.role-create-card-col').each(function() {
                    let cardIndustry = $(this).data('industry');
                    let cardTitle = $(this).data('title') || '';
                    let matchesIndustry = (activeFilter === 'all' || cardIndustry === activeFilter);
                    let matchesSearch = (term === '' || cardTitle.indexOf(term) !== -1);

                    if (matchesIndustry && matchesSearch) {
                        $(this).show();
                        visibleCount++;
                    } else {
                        $(this).hide();
                    }
                });

                if (visibleCount === 0) {
                    $('#no-create-role-found-msg').removeClass('d-none');
                } else {
                    $('#no-create-role-found-msg').addClass('d-none');
                }
            });

            $('.btn-create-role-template').on('click', function() {
                let roleKey = $(this).data('role');
                let card = $(this);
                $('.role-chip-card').removeClass('active-selected');
                $('.role-check-indicator').addClass('d-none');
                card.addClass('active-selected');
                card.find('.role-check-indicator').removeClass('d-none');
                loadRoleTemplate(roleKey);
            });

            // When designation changes, auto-pick matching template if JD is empty or user is selecting
            $('#designation_id').on('change', function() {
                let text = $(this).find('option:selected').text();
                if (text && text !== 'Select Designation') {
                    loadRoleTemplate(encodeURIComponent(text));
                }
            });
        });
    </script>
@endsection
