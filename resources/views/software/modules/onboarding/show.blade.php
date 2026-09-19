@extends('software.layout.app')

@php
    $page_title = 'Onboarding: ' . ($onboarding->full_name ?: ($onboarding->first_name . ' ' . $onboarding->last_name));
    $folder_path = $modules['folder_path'] ?? 'software.modules.onboarding';
    $route = $modules['route'] ?? 'onboarding';
    $progress = $onboarding->progress_percentage;
    $progressColor = $progress >= 100 ? 'bg-success' : ($progress >= 50 ? 'bg-primary' : 'bg-warning');
@endphp

@section('title', $page_title)

@section('page_leavel_style')
    <link rel="stylesheet" href="{{ asset('software/vendor/libs/select2/select2.css') }}" />
    <link rel="stylesheet" href="{{ asset('software/vendor/libs/flatpickr/flatpickr.css') }}" />
    <style>
        .onboarding-header-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #eef2f7 100%);
            border-radius: 12px;
            border: 1px solid rgba(75, 70, 92, 0.1);
        }
        .step-nav .nav-link {
            border-radius: 8px;
            padding: 11px 16px;
            font-weight: 600;
            color: #4b5563;
            background: #ffffff;
            border: 1px solid #edf2f7;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.2s ease-in-out;
            cursor: pointer;
        }
        .step-nav .nav-link:hover {
            background-color: #f0f5ff !important;
            color: #266BEE !important;
            border-color: #d0e1fd !important;
            transform: translateX(3px);
        }
        .step-nav .nav-link:hover .step-num-badge {
            background: rgba(38, 107, 238, 0.12);
            color: #266BEE;
        }
        .step-nav .nav-link.active {
            background: linear-gradient(92deg, #266BEE 22.16%, rgba(103, 150, 226, 0.88) 76.47%) !important;
            color: #ffffff !important;
            border-color: transparent !important;
            box-shadow: 0 4px 12px rgba(38, 107, 238, 0.3) !important;
            transform: none !important;
        }
        .step-nav .nav-link.active span,
        .step-nav .nav-link.active i {
            color: #ffffff !important;
        }
        .step-nav .nav-link.active .badge {
            background: rgba(255, 255, 255, 0.25) !important;
            color: #ffffff !important;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .doc-card, .training-card, .asset-card {
            border-radius: 10px;
            border: 1px solid rgba(75, 70, 92, 0.12);
            background: #ffffff;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .doc-card:hover, .training-card:hover, .asset-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 4px 14px rgba(75, 70, 92, 0.06);
        }
        .doc-details-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 14px;
        }
        .doc-info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 4px 0;
        }
        .doc-info-row:not(:last-child) {
            border-bottom: 1px dashed #e2e8f0;
            margin-bottom: 6px;
            padding-bottom: 6px;
        }
        .doc-id-pill {
            font-family: monospace;
            font-size: 0.85rem;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            padding: 2px 8px;
            border-radius: 6px;
            font-weight: 600;
            color: #1e293b;
        }
        .btn-view-doc {
            display: inline-flex;
            align-items: center;
            padding: 3px 10px;
            font-size: 0.78rem;
            font-weight: 600;
            border-radius: 6px;
            background-color: rgba(38, 107, 238, 0.1) !important;
            color: #266BEE !important;
            border: 1px solid rgba(38, 107, 238, 0.25) !important;
            text-decoration: none !important;
            transition: all 0.2s ease;
        }
        .btn-view-doc:hover {
            background-color: #266BEE !important;
            color: #ffffff !important;
            border-color: #266BEE !important;
            box-shadow: 0 2px 6px rgba(38, 107, 238, 0.25);
        }
        .status-pill {
            font-size: 0.75rem;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 600;
        }
        .step-num-badge {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            margin-right: 8px;
            background: #f1f5f9;
            color: #64748b;
            font-weight: 700;
            transition: all 0.2s ease;
        }
        .step-nav .nav-link.active .step-num-badge {
            background: rgba(255, 255, 255, 0.25) !important;
            color: #ffffff !important;
        }

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
        .industry-white_collar .role-icon-box { background: rgba(99, 102, 241, 0.12); color: #4f46e5; }
        .industry-grey_collar .role-icon-box { background: rgba(100, 116, 139, 0.15); color: #334155; }
        .industry-blue_collar .role-icon-box { background: rgba(2, 132, 199, 0.12); color: #0284c7; }
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
    <!-- Top Breadcrumb & Action Bar -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('software.dashboard') }}">Home</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('onboarding.index') }}">Employee Onboarding</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    {{ $onboarding->full_name ?: ($onboarding->first_name . ' ' . $onboarding->last_name) }}
                </li>
            </ol>
        </nav>
        <div class="d-flex align-items-center gap-2">
            @if ($onboarding->status != 'completed')
                @if(empty($is_view_only))
                    <button type="button" class="btn btn-primary btn-sm waves-effect waves-light d-flex align-items-center" id="btn-quick-complete">
                        <i class="ti ti-circle-check me-1"></i>
                        <span>Complete Onboarding</span>
                    </button>
                @endif
            @else
                <span class="badge bg-label-success px-3 py-2"><i class="ti ti-check me-1"></i> Onboarding Completed</span>
            @endif

            <a class="btn btn-outline-secondary btn-sm waves-effect d-flex align-items-center"
                href="{{ route('onboarding.index') }}">
                <i class="ti ti-arrow-left me-1"></i>
                <span>Back</span>
            </a>
        </div>
    </div>

    <!-- Candidate Profile & Progress Banner -->
    <div class="card onboarding-header-card shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center g-3">
                <div class="col-md-7 d-flex align-items-center">
                    <div class="avatar avatar-xl me-3">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($onboarding->full_name) }}&background=266BEE&color=fff&size=128" alt="Avatar" class="rounded-circle shadow-sm">
                    </div>
                    <div>
                        <h4 class="mb-1 text-heading fw-bold">{{ $onboarding->full_name }}</h4>
                        <div class="d-flex flex-wrap gap-2 align-items-center text-muted small">
                            <span><i class="ti ti-mail me-1"></i>{{ $onboarding->email }}</span>
                            <span>•</span>
                            <span><i class="ti ti-phone me-1"></i>{{ $onboarding->contact_number }}</span>
                            <span>•</span>
                            <span><i class="ti ti-briefcase me-1"></i>{{ $onboarding->designation?->name ?? 'Designation Pending' }}</span>
                            <span>•</span>
                            <span><i class="ti ti-building me-1"></i>{{ $onboarding->department?->name ?? 'Dept Pending' }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="bg-white p-3 rounded shadow-sm border">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-semibold text-heading small">Onboarding Completion Progress</span>
                            <span class="fw-bold text-primary fs-6" id="progress-text">{{ $progress }}%</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar {{ $progressColor }}" id="progress-bar-fill" role="progressbar" style="width: {{ $progress }}%;" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2 small text-muted">
                            <span>Status: <strong class="text-capitalize text-heading" id="status-text">{{ str_replace('_', ' ', $onboarding->status) }}</strong></span>
                            <span>Joining: <strong>{{ $onboarding->joining_date ? $onboarding->joining_date->format('d M, Y') : 'Not Set' }}</strong></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-1 small text-muted border-top pt-1">
                            <span>Probation: <strong class="text-capitalize text-primary">{{ str_replace('_', ' ', $onboarding->probation_status ?? 'on_probation') }} ({{ $onboarding->probation_period_months ?? 3 }}M)</strong></span>
                            <span>Confirmation: <strong>{{ $onboarding->probation_end_date ? $onboarding->probation_end_date->format('d M, Y') : 'Pending' }}</strong></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Multi-Step Stepper & Content Unified Container -->
    <div class="card shadow-sm border-0 mb-5 overflow-hidden">
        <div class="row g-0">
            <!-- Sidebar Navigation (Steps 1 to 6) -->
            <div class="col-xl-3 col-lg-4 border-end bg-light-subtle p-3 p-xl-4">
                <h6 class="text-muted text-uppercase small fw-bold px-2 mb-3">Onboarding Steps</h6>
                <div class="nav flex-column nav-pills step-nav" id="onboarding-tabs" role="tablist">
                    <a class="nav-link {{ ($active_tab ?? 'basic-details') == 'basic-details' ? 'active' : '' }}" id="tab-basic-details-btn" data-slug="basic-details" data-bs-toggle="pill" href="#tab-basic-details" role="tab">
                        <span><span class="step-num-badge">1</span> Basic Profile</span>
                        <i class="ti ti-check text-success step-icon-check"></i>
                    </a>
                    <a class="nav-link {{ ($active_tab ?? '') == 'documents' ? 'active' : '' }}" id="tab-documents-btn" data-slug="documents" data-bs-toggle="pill" href="#tab-documents" role="tab">
                        <span><span class="step-num-badge">2</span> Documents & KYC</span>
                        <span class="badge bg-label-warning rounded-pill" id="docs-count-badge">{{ $onboarding->documents()->where('status', 'verified')->count() }}/{{ $onboarding->documents()->count() }}</span>
                    </a>
                    <a class="nav-link {{ ($active_tab ?? '') == 'jd-kra' ? 'active' : '' }}" id="tab-jd-kra-btn" data-slug="jd-kra" data-bs-toggle="pill" href="#tab-jd-kra" role="tab">
                        <span><span class="step-num-badge">3</span> JD, KRA/KPI & Orientation</span>
                        @if($onboarding->company_overview_acknowledged)
                            <i class="ti ti-check text-success"></i>
                        @else
                            <i class="ti ti-clock text-warning"></i>
                        @endif
                    </a>
                    <a class="nav-link {{ ($active_tab ?? '') == 'trainings' ? 'active' : '' }}" id="tab-trainings-btn" data-slug="trainings" data-bs-toggle="pill" href="#tab-trainings" role="tab">
                        <span><span class="step-num-badge">4</span> Induction & Training</span>
                        <span class="badge bg-label-info rounded-pill">{{ $onboarding->trainings()->where('status', 'completed')->count() }}/{{ $onboarding->trainings()->count() }}</span>
                    </a>
                    <a class="nav-link {{ ($active_tab ?? '') == 'assets' ? 'active' : '' }}" id="tab-assets-btn" data-slug="assets" data-bs-toggle="pill" href="#tab-assets" role="tab">
                        <span><span class="step-num-badge">5</span> Asset Allocation</span>
                        <span class="badge bg-label-primary rounded-pill">{{ $onboarding->assets()->whereIn('status', ['assigned', 'handed_over'])->count() }}/{{ $onboarding->assets()->count() }}</span>
                    </a>
                    <a class="nav-link {{ ($active_tab ?? '') == 'reporting' ? 'active' : '' }}" id="tab-reporting-btn" data-slug="reporting" data-bs-toggle="pill" href="#tab-reporting" role="tab">
                        <span><span class="step-num-badge">6</span> Reporting & Finalize</span>
                        @if($onboarding->status == 'completed')
                            <i class="ti ti-check text-success"></i>
                        @endif
                    </a>
                </div>
            </div>

            <!-- Content Area -->
            <div class="col-xl-9 col-lg-8">
                <div class="tab-content p-0 h-100">

                    <!-- STEP 1: Basic Profile Details -->
                    <div class="tab-pane fade {{ ($active_tab ?? 'basic-details') == 'basic-details' ? 'show active' : '' }}" id="tab-basic-details" role="tabpanel">
                        <div class="card border-0 shadow-none rounded-0 h-100">
                            <div class="card-header border-bottom py-3">
                                <h5 class="mb-0 fw-bold"><i class="ti ti-user text-primary me-2"></i>Step 1: Personal, Contact & Job Details</h5>
                                <small class="text-muted">Review candidate identity, contact details, branch and employment setup.</small>
                            </div>
                            <div class="card-body pt-4">
                                <form id="form-step1">
                                    @csrf
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-4">
                                            <label class="form-label" for="s1_first_name">First Name (Surname) <span class="text-danger">*</span></label>
                                            <input type="text" id="s1_first_name" name="first_name" class="form-control" value="{{ $onboarding->first_name }}" @if(!empty($is_view_only)) disabled @endif required />
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="s1_middle_name">Middle Name (Candidate Name)</label>
                                            <input type="text" id="s1_middle_name" name="middle_name" class="form-control" value="{{ $onboarding->middle_name }}" @if(!empty($is_view_only)) disabled @endif />
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="s1_father_name">Father / Husband Name</label>
                                            <input type="text" id="s1_father_name" name="father_name" class="form-control" value="{{ $onboarding->father_name }}" placeholder="Enter Father / Husband Name" @if(!empty($is_view_only)) disabled @endif />
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label" for="s1_email">Email Address <span class="text-danger">*</span></label>
                                            <input type="email" id="s1_email" name="email" class="form-control" value="{{ $onboarding->email }}" @if(!empty($is_view_only)) disabled @endif required />
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="s1_contact_number">Mobile Number <span class="text-danger">*</span></label>
                                            <input type="text" id="s1_contact_number" name="contact_number" class="form-control" value="{{ $onboarding->contact_number }}" @if(!empty($is_view_only)) disabled @endif required maxlength="10" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);" />
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="s1_other_number">Emergency / Alternate Contact</label>
                                            <input type="text" id="s1_other_number" name="other_number" class="form-control" value="{{ $onboarding->other_number }}" @if(!empty($is_view_only)) disabled @endif maxlength="10" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);" />
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label" for="s1_date_of_birth">Date of Birth</label>
                                            <input type="text" id="s1_date_of_birth" name="date_of_birth" class="form-control @if(empty($is_view_only)) flatpickr-date @endif" value="{{ $onboarding->date_of_birth ? $onboarding->date_of_birth->format('d-m-Y') : '' }}" placeholder="DD-MM-YYYY" @if(!empty($is_view_only)) disabled @endif />
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label" for="s1_gender">Gender</label>
                                            <select id="s1_gender" name="gender" class="form-select select2" @if(!empty($is_view_only)) disabled @endif>
                                                <option value="Male" {{ $onboarding->gender == 'Male' ? 'selected' : '' }}>Male</option>
                                                <option value="Female" {{ $onboarding->gender == 'Female' ? 'selected' : '' }}>Female</option>
                                                <option value="Other" {{ $onboarding->gender == 'Other' ? 'selected' : '' }}>Other</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label" for="s1_blood_group">Blood Group</label>
                                            <select id="s1_blood_group" name="blood_group" class="form-select select2" @if(!empty($is_view_only)) disabled @endif>
                                                <option value="">Select Blood Group</option>
                                                @foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bg)
                                                    <option value="{{ $bg }}" {{ $onboarding->blood_group == $bg ? 'selected' : '' }}>{{ $bg }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label" for="s1_marital_status">Marital Status</label>
                                            <select id="s1_marital_status" name="marital_status" class="form-select select2" @if(!empty($is_view_only)) disabled @endif>
                                                <option value="Single" {{ $onboarding->marital_status == 'Single' ? 'selected' : '' }}>Single</option>
                                                <option value="Married" {{ $onboarding->marital_status == 'Married' ? 'selected' : '' }}>Married</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label" for="s1_current_address">Current Address</label>
                                            <textarea id="s1_current_address" name="current_address" class="form-control" rows="2" @if(!empty($is_view_only)) readonly @endif>{{ $onboarding->current_address }}</textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label" for="s1_permanent_address">Permanent Address</label>
                                            <textarea id="s1_permanent_address" name="permanent_address" class="form-control" rows="2" @if(!empty($is_view_only)) readonly @endif>{{ $onboarding->permanent_address }}</textarea>
                                        </div>
                                    </div>

                                    <div class="border-top pt-3 mb-3">
                                        <h6 class="fw-bold mb-3 text-primary"><i class="ti ti-briefcase me-1"></i>Employment & Probation Configuration</h6>
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <label class="form-label" for="s1_joining_date">Date of Joining</label>
                                                <input type="text" id="s1_joining_date" name="joining_date" class="form-control @if(empty($is_view_only)) flatpickr-date @endif" value="{{ $onboarding->joining_date ? $onboarding->joining_date->format('d-m-Y') : '' }}" placeholder="DD-MM-YYYY" @if(!empty($is_view_only)) disabled @endif />
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label" for="s1_employment_type">Employment Type</label>
                                                <select id="s1_employment_type" name="employment_type" class="form-select select2" @if(!empty($is_view_only)) disabled @endif>
                                                    <option value="">Select Employment Type</option>
                                                    @foreach ($employeeTypes as $et)
                                                        <option value="{{ $et->id }}" {{ ($onboarding->employment_type == $et->id || $onboarding->employment_type == $et->name) ? 'selected' : '' }}>{{ $et->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label" for="s1_probation_period_months">Probation Duration</label>
                                                <select id="s1_probation_period_months" name="probation_period_months" class="form-select select2" @if(!empty($is_view_only)) disabled @endif>
                                                    <option value="0" {{ $onboarding->probation_period_months === 0 ? 'selected' : '' }}>No Probation (Direct Confirmed)</option>
                                                    <option value="1" {{ $onboarding->probation_period_months == 1 ? 'selected' : '' }}>1 Month</option>
                                                    <option value="2" {{ $onboarding->probation_period_months == 2 ? 'selected' : '' }}>2 Months</option>
                                                    <option value="3" {{ ($onboarding->probation_period_months ?? 3) == 3 ? 'selected' : '' }}>3 Months (Standard)</option>
                                                    <option value="6" {{ $onboarding->probation_period_months == 6 ? 'selected' : '' }}>6 Months</option>
                                                    <option value="12" {{ $onboarding->probation_period_months == 12 ? 'selected' : '' }}>12 Months (1 Year)</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label" for="s1_probation_end_date">Confirmation Date</label>
                                                <input type="text" id="s1_probation_end_date" name="probation_end_date" class="form-control @if(empty($is_view_only)) flatpickr-date @endif" value="{{ $onboarding->probation_end_date ? $onboarding->probation_end_date->format('d-m-Y') : '' }}" placeholder="DD-MM-YYYY" @if(!empty($is_view_only)) disabled @endif />
                                            </div>

                                            <div class="col-md-3">
                                                <label class="form-label" for="s1_probation_status">Probation Status</label>
                                                <select id="s1_probation_status" name="probation_status" class="form-select select2" @if(!empty($is_view_only)) disabled @endif>
                                                    <option value="on_probation" {{ $onboarding->probation_status == 'on_probation' ? 'selected' : '' }}>On Probation</option>
                                                    <option value="confirmed" {{ $onboarding->probation_status == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                                    <option value="extended" {{ $onboarding->probation_status == 'extended' ? 'selected' : '' }}>Extended</option>
                                                    <option value="waived" {{ $onboarding->probation_status == 'waived' ? 'selected' : '' }}>Waived / Direct</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label" for="s1_branch_id">Branch</label>
                                                <select id="s1_branch_id" name="branch_id" class="form-select select2" @if(!empty($is_view_only)) disabled @endif>
                                                    <option value="">Select Branch</option>
                                                    @foreach ($branches as $b)
                                                        <option value="{{ $b->id }}" {{ $onboarding->branch_id == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label" for="s1_department_id">Department</label>
                                                <select id="s1_department_id" name="department_id" class="form-select select2" @if(!empty($is_view_only)) disabled @endif>
                                                    <option value="">Select Department</option>
                                                    @foreach ($departments as $d)
                                                        <option value="{{ $d->id }}" {{ $onboarding->department_id == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label" for="s1_designation_id">Designation</label>
                                                <select id="s1_designation_id" name="designation_id" class="form-select select2" @if(!empty($is_view_only)) disabled @endif>
                                                    <option value="">Select Designation</option>
                                                    @foreach ($designations as $des)
                                                        <option value="{{ $des->id }}" {{ $onboarding->designation_id == $des->id ? 'selected' : '' }}>{{ $des->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                                        @if(empty($is_view_only))
                                            <button type="submit" class="btn btn-primary waves-effect waves-light">
                                                <i class="ti ti-device-floppy me-1"></i> Save Step 1
                                            </button>
                                        @endif
                                        <button type="button" class="btn btn-outline-primary btn-next-tab" data-next="#tab-documents">
                                            Next: Documents & KYC <i class="ti ti-arrow-right ms-1"></i>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 2: Document Collection & KYC Verification -->
                    <div class="tab-pane fade {{ ($active_tab ?? '') == 'documents' ? 'show active' : '' }}" id="tab-documents" role="tabpanel">
                        <div class="card border-0 shadow-none rounded-0 h-100">
                            <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0 fw-bold"><i class="ti ti-files text-primary me-2"></i>Step 2: Document Collection & KYC Verification</h5>
                                    <small class="text-muted">Review, collect and verify Aadhaar, PAN Card, Photograph, Bank Details, and Education Records.</small>
                                </div>
                                @if(empty($is_view_only))
                                    <button type="button" class="btn btn-sm btn-primary waves-effect" data-bs-toggle="modal" data-bs-target="#modal-add-document">
                                        <i class="ti ti-plus me-1"></i> Add Custom Document
                                    </button>
                                @endif
                            </div>
                            <div class="card-body pt-4">
                                <div class="row g-3" id="documents-container">
                                    @forelse ($onboarding->documents as $doc)
                                        @php
                                            $statusClass = $doc->status == 'verified' ? 'bg-label-success' : ($doc->status == 'uploaded' ? 'bg-label-primary' : ($doc->status == 'rejected' ? 'bg-label-danger' : 'bg-label-warning'));
                                        @endphp
                                        <div class="col-md-6" id="doc-card-{{ $doc->id }}">
                                            <div class="doc-card p-3 h-100 position-relative d-flex flex-column justify-content-between shadow-xs">
                                                <div>
                                                    <!-- Header: Title & Status -->
                                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                                        <div class="d-flex align-items-center me-2">
                                                            <div class="avatar avatar-sm {{ $doc->file_path ? 'bg-label-primary' : 'bg-label-secondary' }} me-2 rounded-circle d-flex align-items-center justify-content-center">
                                                                <i class="ti ti-file-text fs-5"></i>
                                                            </div>
                                                            <div>
                                                                <h6 class="mb-0 fw-bold text-heading fs-6">{{ $doc->document_title ?: ucfirst(str_replace('_', ' ', $doc->document_type)) }}</h6>
                                                                <small class="text-muted text-capitalize">{{ str_replace('_', ' ', $doc->document_type) }}</small>
                                                            </div>
                                                        </div>
                                                        <span class="badge {{ $statusClass }} px-3 py-2 rounded-pill shadow-xs" id="badge-doc-{{ $doc->id }}">{{ ucfirst($doc->status) }}</span>
                                                    </div>

                                                    <!-- Premium Document Info Box -->
                                                    <div class="doc-details-box mb-3">
                                                        <div class="doc-info-row">
                                                            <span class="text-muted small d-flex align-items-center"><i class="ti ti-id me-1 text-primary"></i> Document / ID No:</span>
                                                            @if ($doc->document_number)
                                                                <span class="doc-id-pill">{{ $doc->document_number }}</span>
                                                            @else
                                                                <span class="text-muted fst-italic small">Not entered</span>
                                                            @endif
                                                        </div>

                                                        <div class="doc-info-row">
                                                            <span class="text-muted small d-flex align-items-center"><i class="ti ti-paperclip me-1 text-primary"></i> Attached File:</span>
                                                            @if ($doc->file_path)
                                                                <a href="{{ asset($doc->file_path) }}" target="_blank" class="btn-view-doc">
                                                                    <i class="ti ti-eye me-1"></i> View Document
                                                                </a>
                                                            @else
                                                                <span class="badge bg-label-danger px-2 py-1 small"><i class="ti ti-x me-1"></i>No file uploaded</span>
                                                            @endif
                                                        </div>

                                                        @if ($doc->rejection_reason)
                                                            <div class="mt-2 p-2 bg-danger-subtle border border-danger-subtle text-danger rounded small d-flex align-items-center">
                                                                <i class="ti ti-alert-circle me-1 fs-5"></i>
                                                                <span><strong>Rejection Reason:</strong> {{ $doc->rejection_reason }}</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>

                                                <!-- Action Buttons (Only in Edit Mode) -->
                                                @if(empty($is_view_only))
                                                    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                                        <button type="button" class="btn btn-sm btn-outline-primary waves-effect btn-upload-doc" data-id="{{ $doc->id }}" data-type="{{ $doc->document_type }}" data-title="{{ $doc->document_title }}" data-num="{{ $doc->document_number }}">
                                                            <i class="ti ti-upload me-1"></i> Upload / Edit
                                                        </button>
                                                        <div class="d-flex gap-1">
                                                            <button type="button" class="btn btn-sm btn-success waves-effect waves-light btn-verify-doc" data-id="{{ $doc->id }}" data-action="verified" title="Approve Document">
                                                                <i class="ti ti-check me-1"></i> Verify
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-label-danger waves-effect btn-reject-doc" data-id="{{ $doc->id }}" data-action="rejected" title="Reject Document">
                                                                <i class="ti ti-x me-1"></i> Reject
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-12 text-center py-4 text-muted">
                                            <i class="ti ti-file-off fs-1 d-block mb-2"></i> No documents configured yet.
                                        </div>
                                    @endforelse
                                </div>

                                <div class="d-flex justify-content-between pt-4 border-top mt-4">
                                    <button type="button" class="btn btn-label-secondary btn-prev-tab" data-prev="#tab-basic-details"><i class="ti ti-arrow-left me-1"></i> Previous</button>
                                    <button type="button" class="btn btn-primary btn-next-tab" data-next="#tab-jd-kra">Next: JD & KRA/KPI <i class="ti ti-arrow-right ms-1"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 3: Company Overview, Job Description & Performance KRA/KPI -->
                    <div class="tab-pane fade {{ ($active_tab ?? '') == 'jd-kra' ? 'show active' : '' }}" id="tab-jd-kra" role="tabpanel">
                        <div class="card border-0 shadow-none rounded-0 h-100">
                            <div class="card-header border-bottom py-3">
                                <h5 class="mb-0 fw-bold"><i class="ti ti-target-arrow text-primary me-2"></i>Step 3: Job Description (JD), Attendance & Performance KRA / KPI</h5>
                                <small class="text-muted">Review job deliverables, core duties, monthly attendance & punctuality expectations, and role-specific performance metrics.</small>
                            </div>
                            <div class="card-body pt-4">
                                <form id="form-step3">
                                    @csrf
                                    <input type="hidden" name="kra_kpi_json" id="s3_kra_kpi_json" value="{{ json_encode($onboarding->parsed_kra_kpi) }}">

                                    <!-- One-Stop Industry & Role Template Switcher (Only in Edit Mode) -->
                                    @if(empty($is_view_only))
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
                                                        <small class="text-muted">Click any role below to automatically fill standard JD, Attendance & KRA/KPI metrics</small>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="input-group input-group-sm" style="width: 200px;">
                                                        <span class="input-group-text bg-white border-end-0 py-1"><i class="ti ti-search text-muted small"></i></span>
                                                        <input type="text" id="role-search-input" class="form-control bg-white border-start-0 ps-0 py-1" placeholder="Search role (e.g. CNC, Dev)...">
                                                    </div>
                                                    <span class="badge bg-label-primary px-3 py-2 rounded-pill" id="active-template-badge">
                                                        <i class="ti ti-check me-1"></i> <span id="active-template-title">{{ $onboarding->designation?->name ?? 'Default Role' }}</span>
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- Industry Filter Pills -->
                                            <div class="p-2 px-3 border-bottom bg-white">
                                                <div class="d-flex flex-wrap gap-1 industry-filter-pills">
                                                    @foreach($industryCategories as $indKey => $ind)
                                                        <button type="button" class="industry-tab-pill {{ $indKey === 'all' ? 'active' : '' }} btn-filter-industry" data-filter="{{ $indKey }}">
                                                            <i class="ti {{ $ind['icon'] }} me-1"></i> {{ $ind['label'] }}
                                                        </button>
                                                    @endforeach
                                                </div>
                                            </div>

                                            <!-- Role Cards Grid -->
                                            <div class="role-grid-container bg-light bg-opacity-25">
                                                <div class="row g-2" id="role-cards-grid">
                                                    @foreach($allRoleTemplates as $tKey => $template)
                                                        @php
                                                            $indClass = 'industry-' . ($template['industry'] ?? 'general');
                                                        @endphp
                                                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 role-card-col" data-role="{{ $tKey }}" data-industry="{{ $template['industry'] ?? 'general' }}" data-title="{{ strtolower($template['title'] . ' ' . ($template['badge'] ?? '')) }}">
                                                            <div class="role-chip-card {{ $indClass }} btn-load-role-template" data-role="{{ $tKey }}">
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
                                                <div id="no-role-found-msg" class="text-center py-4 text-muted d-none">
                                                    <i class="ti ti-search-off fs-2 mb-1 d-block opacity-50"></i>
                                                    <span>No matching roles found. Try a different keyword or select "All Industries".</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- 1. Job Description (JD) Overview -->
                                    <div class="card border mb-4 shadow-none">
                                        <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                                            <span class="fw-bold text-heading"><i class="ti ti-notes text-primary me-1"></i> 1. Role Job Description (JD) & Key Responsibilities</span>
                                            <span class="badge bg-label-primary" id="badge-jd-designation">{{ $onboarding->designation?->name ?? 'Designation Pending' }}</span>
                                        </div>
                                        <div class="card-body pt-3">
                                            <div class="mb-1">
                                                <label class="form-label fw-semibold" for="s3_job_description">Job Summary & Core Deliverables</label>
                                                <textarea id="s3_job_description" name="job_description" class="form-control" style="field-sizing: content; min-height: 140px; line-height: 1.6; overflow-y: hidden; resize: none;" oninput="this.style.height = ''; this.style.height = (this.scrollHeight + 10) + 'px';" placeholder="Enter detailed responsibilities, key functions, software/tools required, and day-to-day duties for this role..." @if(!empty($is_view_only)) readonly @endif>{{ $onboarding->job_description ?: $onboarding->getDefaultJobDescription() }}</textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 2. Attendance & Punctuality Expectations (KPI) -->
                                    <div class="card border mb-4 shadow-none">
                                        <div class="card-header bg-light py-2 px-3">
                                            <span class="fw-bold text-heading"><i class="ti ti-clock-check text-success me-1"></i> 2. Attendance & Punctuality Performance Expectations</span>
                                        </div>
                                        <div class="card-body pt-3">
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <label class="form-label" for="s3_attendance_target_percentage">Monthly Attendance Target (%)</label>
                                                    <div class="input-group">
                                                        <input type="number" id="s3_attendance_target_percentage" name="attendance_target_percentage" class="form-control" value="{{ $onboarding->attendance_target_percentage ?? 95 }}" min="50" max="100" @if(!empty($is_view_only)) readonly @endif />
                                                        <span class="input-group-text">%</span>
                                                    </div>
                                                    <small class="text-muted">Target monthly attendance percentage required</small>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label" for="s3_late_mark_tolerance">Punctuality / Grace Late Marks Tolerance</label>
                                                    <div class="input-group">
                                                        <input type="number" id="s3_late_mark_tolerance" name="late_mark_tolerance" class="form-control" value="{{ $onboarding->late_mark_tolerance ?? 2 }}" min="0" max="10" @if(!empty($is_view_only)) readonly @endif />
                                                        <span class="input-group-text">times / month</span>
                                                    </div>
                                                    <small class="text-muted">Max acceptable grace late arrivals per month</small>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label" for="s3_daily_working_hours_target">Daily Work Hours Target</label>
                                                    <div class="input-group">
                                                        <input type="number" step="0.25" id="s3_daily_working_hours_target" name="daily_working_hours_target" class="form-control" value="{{ $onboarding->daily_working_hours_target ?? 8.50 }}" min="4" max="14" @if(!empty($is_view_only)) readonly @endif />
                                                        <span class="input-group-text">Hrs / Day</span>
                                                    </div>
                                                    <small class="text-muted">Assigned Shift: <strong>{{ $onboarding->shift?->name ?? 'Standard General Shift' }}</strong></small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 3. Structured Key Result Areas (KRAs) & Measurable Goals -->
                                    <div class="card border mb-4 shadow-none">
                                        <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                                            <span class="fw-bold text-heading"><i class="ti ti-chart-arrows text-info me-1"></i> 3. Key Result Areas (KRAs) & Evaluation Metrics</span>
                                            <span class="badge bg-label-info small" id="kra-count-badge">{{ count($onboarding->parsed_kra_kpi) }} KRA Modules</span>
                                        </div>
                                        <div class="card-body pt-3 p-0">
                                            <div class="table-responsive">
                                                <table class="table table-hover mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th width="30%">Key Result Area (KRA)</th>
                                                            <th width="15%">Weightage</th>
                                                            <th width="40%">Measurable Performance Indicators (KPIs)</th>
                                                            <th width="15%">Review Cycle</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="s3_kra_tbody">
                                                        @foreach ($onboarding->parsed_kra_kpi as $kra)
                                                            <tr>
                                                                <td class="align-middle fw-semibold text-heading">
                                                                    <i class="ti ti-chevron-right text-primary me-1"></i>{{ $kra['kra_title'] }}
                                                                </td>
                                                                <td class="align-middle">
                                                                    <span class="badge bg-label-primary fs-7">{{ $kra['weightage'] }}%</span>
                                                                </td>
                                                                <td class="align-middle">
                                                                    <ul class="mb-0 ps-3 small text-muted">
                                                                        @foreach ($kra['kpis'] as $kpi)
                                                                            <li><strong>{{ $kpi['metric'] }}</strong>: <span class="badge bg-label-success">{{ $kpi['target'] }}</span></li>
                                                                        @endforeach
                                                                    </ul>
                                                                </td>
                                                                <td class="align-middle">
                                                                    <span class="badge bg-label-secondary">{{ $kra['kpis'][0]['frequency'] ?? 'Monthly' }}</span>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 4. Company Orientation & Employee Handbook -->
                                    @php
                                        $companyObj = $company ?: ($onboarding->company ?? \App\Models\Company::find($onboarding->company_id));
                                        $compSlug = \Illuminate\Support\Str::slug(($companyObj?->id ?? $onboarding->company_id ?? '1') . ' ' . ($companyObj?->company_name ?? 'company'));
                                        $handbookDir = public_path('uploads/' . $compSlug . '/handbook/');
                                        $handbookRelPath = null;
                                        $handbookType = 'Document';
                                        if (is_dir($handbookDir)) {
                                            foreach (['handbook.pdf', 'handbook.webp', 'handbook.png', 'handbook.jpg', 'handbook.jpeg'] as $f) {
                                                if (file_exists($handbookDir . $f)) {
                                                    $handbookRelPath = 'uploads/' . $compSlug . '/handbook/' . $f;
                                                    $handbookType = strtoupper(pathinfo($f, PATHINFO_EXTENSION));
                                                    break;
                                                }
                                            }
                                        }
                                        $handbookExists = !empty($handbookRelPath) && file_exists(public_path($handbookRelPath));
                                        $handbookUrl = $handbookExists ? asset($handbookRelPath) . '?v=' . filemtime(public_path($handbookRelPath)) : '#';
                                    @endphp
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-7">
                                            <div class="p-3 bg-light rounded-3 border h-100">
                                                <h6 class="fw-bold text-primary mb-2"><i class="ti ti-building me-1"></i>Welcome to {{ $companyObj->company_name ?? 'OceanHR' }}</h6>
                                                <p class="text-muted small mb-2">
                                                    We are thrilled to welcome you. Our mission is to build innovative HR solutions while upholding excellence, transparency, and teamwork.
                                                </p>
                                                <div class="d-flex align-items-center p-2 bg-white rounded border mt-2">
                                                    @if($handbookType === 'PDF')
                                                        <i class="ti ti-file-type-pdf text-danger fs-3 me-2"></i>
                                                    @else
                                                        <i class="ti ti-photo text-primary fs-3 me-2"></i>
                                                    @endif
                                                    <div>
                                                        <div class="fw-bold small">Employee Handbook & Policies</div>
                                                        <small class="text-muted">{{ $companyObj->company_name ?? 'Company' }} attendance, leave policy & code of conduct</small>
                                                    </div>
                                                    @if($handbookExists)
                                                        <a href="{{ $handbookUrl }}" target="_blank" class="btn btn-xs btn-outline-primary ms-auto">
                                                            <i class="ti ti-eye me-1"></i> View Handbook ({{ $handbookType }})
                                                        </a>
                                                    @else
                                                        <a href="javascript:void(0);" onclick="Swal.fire('Handbook Not Uploaded', 'Please upload the Employee Handbook (PDF or Image) from Company Details -> Profile Tab.', 'info')" class="btn btn-xs btn-label-secondary ms-auto">
                                                            <i class="ti ti-file-off me-1"></i> View Handbook
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-5">
                                            <div class="card border p-3 h-100 bg-white shadow-none">
                                                <h6 class="fw-bold mb-2"><i class="ti ti-checklist text-success me-1"></i>Sign-off & Acknowledgment</h6>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" id="company_overview_acknowledged" name="company_overview_acknowledged" value="1" {{ $onboarding->company_overview_acknowledged ? 'checked' : '' }} @if(!empty($is_view_only)) disabled @endif>
                                                    <label class="form-check-label small fw-semibold" for="company_overview_acknowledged">
                                                        Completed Company Orientation & Received Handbook
                                                    </label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" id="jd_acknowledged" name="jd_acknowledged" value="1" {{ $onboarding->jd_acknowledged ? 'checked' : '' }} @if(!empty($is_view_only)) disabled @endif>
                                                    <label class="form-check-label small fw-semibold" for="jd_acknowledged">
                                                        Accepted Job Description (JD) & Attendance / KRA Goals
                                                    </label>
                                                </div>
                                                <div class="mt-2">
                                                    <label class="form-label small mb-1" for="company_overview_notes">Facilitator / HR Remarks</label>
                                                    <textarea id="company_overview_notes" name="company_overview_notes" class="form-control form-control-sm" rows="2" placeholder="e.g. Explained JD, KPIs & shift timings on joining day." @if(!empty($is_view_only)) readonly @endif>{{ $onboarding->company_overview_notes }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between pt-3 border-top">
                                        <button type="button" class="btn btn-label-secondary btn-prev-tab" data-prev="#tab-documents"><i class="ti ti-arrow-left me-1"></i> Previous</button>
                                        <div class="d-flex gap-2">
                                            @if(empty($is_view_only))
                                                <button type="submit" class="btn btn-primary waves-effect waves-light">
                                                    <i class="ti ti-device-floppy me-1"></i> Save Step 3 (JD & KRA)
                                                </button>
                                            @endif
                                            <button type="button" class="btn btn-outline-primary btn-next-tab" data-next="#tab-trainings">
                                                Next: Induction & Training <i class="ti ti-arrow-right ms-1"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 4: Induction & Training (Modules 1, 2, 3 + PDF Attachments) -->
                    <div class="tab-pane fade {{ ($active_tab ?? '') == 'trainings' ? 'show active' : '' }}" id="tab-trainings" role="tabpanel">
                        <div class="card border-0 shadow-none rounded-0 h-100">
                            <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0 fw-bold"><i class="ti ti-school text-primary me-2"></i>Step 4: Induction & Training Modules</h5>
                                    <small class="text-muted">Track training modules (Module 1, 2, 3), view PDF materials, and monitor completion status.</small>
                                </div>
                                @if(empty($is_view_only))
                                    <button type="button" class="btn btn-sm btn-primary waves-effect" data-bs-toggle="modal" data-bs-target="#modal-add-training">
                                        <i class="ti ti-plus me-1"></i> Add Custom Training Track
                                    </button>
                                @endif
                            </div>
                            <div class="card-body pt-4">
                                <div class="row g-3">
                                    @forelse ($onboarding->trainings as $training)
                                        @php
                                            $tStatusClass = match($training->status) {
                                                'completed' => 'bg-label-success',
                                                'in_progress' => 'bg-label-primary',
                                                default => 'bg-label-warning'
                                            };
                                        @endphp
                                        <div class="col-12">
                                            <div class="card training-card p-3">
                                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                                    <div class="d-flex align-items-center">
                                                        <span class="badge bg-primary rounded-circle p-2 me-3 fs-6">#{{ $training->module_number }}</span>
                                                        <div>
                                                            <h6 class="mb-0 fw-bold text-heading">{{ $training->title }}</h6>
                                                            <p class="text-muted small mb-0">{{ $training->description }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="badge {{ $tStatusClass }}" id="badge-training-{{ $training->id }}">{{ ucfirst(str_replace('_', ' ', $training->status)) }}</span>
                                                        @if(empty($is_view_only))
                                                            <select class="form-select form-select-sm select-training-status" data-id="{{ $training->id }}" style="width: 140px;">
                                                                <option value="pending" {{ $training->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                                                <option value="in_progress" {{ $training->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                                                <option value="completed" {{ $training->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                                            </select>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="d-flex flex-wrap justify-content-between align-items-center bg-light p-2 rounded mt-2 small">
                                                    <div>
                                                        <span class="text-muted me-2"><i class="ti ti-file-text me-1"></i>Material:</span>
                                                        @if ($training->pdf_file_path)
                                                            <a href="{{ asset($training->pdf_file_path) }}" target="_blank" class="badge bg-label-info text-decoration-none">
                                                                <i class="ti ti-file-type-pdf me-1"></i> {{ $training->pdf_file_name ?: 'Download Training PDF' }}
                                                            </a>
                                                        @else
                                                            <span class="text-muted">No PDF attached yet</span>
                                                        @endif
                                                    </div>
                                                    @if(empty($is_view_only))
                                                        <div>
                                                            <button type="button" class="btn btn-xs btn-outline-primary btn-upload-training-pdf" data-id="{{ $training->id }}">
                                                                <i class="ti ti-upload me-1"></i> Attach Training PDF
                                                            </button>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-12 text-center py-4 text-muted">
                                            <i class="ti ti-books-off fs-1 d-block mb-2"></i> No training tracks configured yet.
                                        </div>
                                    @endforelse
                                </div>

                                <div class="d-flex justify-content-between pt-4 border-top mt-4">
                                    <button type="button" class="btn btn-label-secondary btn-prev-tab" data-prev="#tab-jd-kra"><i class="ti ti-arrow-left me-1"></i> Previous</button>
                                    <button type="button" class="btn btn-primary btn-next-tab" data-next="#tab-assets">Next: Asset Allocation <i class="ti ti-arrow-right ms-1"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 5: Asset Allocation (Mobile, Laptop, T-Shirt, Shoes, ID Card) -->
                    <div class="tab-pane fade {{ ($active_tab ?? '') == 'assets' ? 'show active' : '' }}" id="tab-assets" role="tabpanel">
                        <div class="card border-0 shadow-none rounded-0 h-100">
                            <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0 fw-bold"><i class="ti ti-devices text-primary me-2"></i>Step 5: Asset Allocation & Uniform Issuance</h5>
                                    <small class="text-muted">Review Mobile Phone, Laptop/PC, Company T-Shirt, Shoes, and ID Access Card assignments.</small>
                                </div>
                                @if(empty($is_view_only))
                                    <button type="button" class="btn btn-sm btn-primary waves-effect" data-bs-toggle="modal" data-bs-target="#modal-add-asset">
                                        <i class="ti ti-plus me-1"></i> Add Custom Asset
                                    </button>
                                @endif
                            </div>
                            <div class="card-body pt-4">
                                <div class="row g-3">
                                    @forelse ($onboarding->assets as $asset)
                                        @php
                                            $aStatusClass = match($asset->status) {
                                                'handed_over' => 'bg-label-success',
                                                'assigned' => 'bg-label-primary',
                                                default => 'bg-label-warning'
                                            };
                                            $aIcon = match($asset->asset_type) {
                                                'mobile' => 'ti-device-mobile',
                                                'sim' => 'ti-sim-card',
                                                'laptop' => 'ti-device-laptop',
                                                'tshirt' => 'ti-shirt',
                                                'shoes' => 'ti-shoe',
                                                'id_card' => 'ti-id-badge',
                                                default => 'ti-package'
                                            };
                                        @endphp
                                        <div class="col-md-6">
                                            <div class="card asset-card h-100 p-3">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar avatar-md me-2 bg-label-info rounded p-2">
                                                            <i class="ti {{ $aIcon }} fs-4"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0 fw-bold">{{ $asset->asset_name }}</h6>
                                                            <small class="text-muted">Type: <code>{{ $asset->asset_type }}</code></small>
                                                        </div>
                                                    </div>
                                                    <span class="badge {{ $aStatusClass }}" id="badge-asset-{{ $asset->id }}">{{ ucfirst(str_replace('_', ' ', $asset->status)) }}</span>
                                                </div>

                                                <div class="bg-light p-2 rounded small my-2">
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span class="text-muted">Spec / Size:</span>
                                                        <strong class="text-heading">{{ $asset->specification_or_size ?: 'N/A' }}</strong>
                                                    </div>
                                                    <div class="d-flex justify-content-between">
                                                        <span class="text-muted">Serial / Code:</span>
                                                        <strong class="text-heading">{{ $asset->asset_code_or_serial ?: '-' }}</strong>
                                                    </div>
                                                    <div class="d-flex justify-content-between mt-1">
                                                        <span class="text-muted">Issued Date:</span>
                                                        <span>{{ $asset->issued_date ? $asset->issued_date->format('d M, Y') : 'Not Issued' }}</span>
                                                    </div>
                                                </div>

                                                @if(empty($is_view_only))
                                                    <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                                                        <button type="button" class="btn btn-xs btn-outline-secondary btn-edit-asset" data-id="{{ $asset->id }}" data-type="{{ $asset->asset_type }}" data-name="{{ $asset->asset_name }}" data-spec="{{ $asset->specification_or_size }}" data-code="{{ $asset->asset_code_or_serial }}" data-status="{{ $asset->status }}">
                                                            <i class="ti ti-pencil me-1"></i> Edit / Assign
                                                        </button>
                                                        <div class="btn-group btn-group-xs">
                                                            <button type="button" class="btn btn-xs btn-outline-success btn-asset-quick-status" data-id="{{ $asset->id }}" data-status="assigned">
                                                                <i class="ti ti-check"></i> Assign
                                                            </button>
                                                            <button type="button" class="btn btn-xs btn-success btn-asset-quick-status" data-id="{{ $asset->id }}" data-status="handed_over">
                                                                <i class="ti ti-truck-delivery"></i> Handed Over
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-12 text-center py-4 text-muted">
                                            <i class="ti ti-package-off fs-1 d-block mb-2"></i> No assets configured yet.
                                        </div>
                                    @endforelse
                                </div>

                                <div class="d-flex justify-content-between pt-4 border-top mt-4">
                                    <button type="button" class="btn btn-label-secondary btn-prev-tab" data-prev="#tab-trainings"><i class="ti ti-arrow-left me-1"></i> Previous</button>
                                    <button type="button" class="btn btn-primary btn-next-tab" data-next="#tab-reporting">Next: Reporting Manager & Finalize <i class="ti ti-arrow-right ms-1"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 6: Reporting Manager Assignment & Finalization -->
                    <div class="tab-pane fade {{ ($active_tab ?? '') == 'reporting' ? 'show active' : '' }}" id="tab-reporting" role="tabpanel">
                        <div class="card border-0 shadow-none rounded-0 h-100">
                            <div class="card-header border-bottom py-3">
                                <h5 class="mb-0 fw-bold"><i class="ti ti-user-check text-primary me-2"></i>Step 6: Assign Reporting Person & Finalize Onboarding</h5>
                                <small class="text-muted">Review supervisor, mentor, work shift, and official employee status.</small>
                            </div>
                            <div class="card-body pt-4">
                                <form id="form-step6">
                                    @csrf
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-6">
                                            <label class="form-label" for="s6_reporting_manager_id">Reporting Manager / Supervisor <span class="text-danger">*</span></label>
                                            <select id="s6_reporting_manager_id" name="reporting_manager_id" class="form-select select2" @if(!empty($is_view_only)) disabled @endif required>
                                                <option value="">Select Reporting Manager</option>
                                                @foreach ($employees as $emp)
                                                    <option value="{{ $emp->id }}" {{ $onboarding->reporting_manager_id == $emp->id ? 'selected' : '' }}>
                                                        {{ $emp->full_name ?: ($emp->first_name . ' ' . $emp->last_name) }} ({{ $emp->employee_code ?? 'EMP#' . $emp->id }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label" for="s6_buddy_id">Onboarding Buddy / Mentor</label>
                                            <select id="s6_buddy_id" name="buddy_id" class="form-select select2" @if(!empty($is_view_only)) disabled @endif>
                                                <option value="">Select Mentor (Optional)</option>
                                                @foreach ($employees as $emp)
                                                    <option value="{{ $emp->id }}" {{ $onboarding->buddy_id == $emp->id ? 'selected' : '' }}>
                                                        {{ $emp->full_name ?: ($emp->first_name . ' ' . $emp->last_name) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label" for="s6_department_id">Department</label>
                                            <select id="s6_department_id" name="department_id" class="form-select select2" @if(!empty($is_view_only)) disabled @endif>
                                                <option value="">Select Department</option>
                                                @foreach ($departments as $d)
                                                    <option value="{{ $d->id }}" {{ $onboarding->department_id == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="s6_designation_id">Designation</label>
                                            <select id="s6_designation_id" name="designation_id" class="form-select select2" @if(!empty($is_view_only)) disabled @endif>
                                                <option value="">Select Designation</option>
                                                @foreach ($designations as $des)
                                                    <option value="{{ $des->id }}" {{ $onboarding->designation_id == $des->id ? 'selected' : '' }}>{{ $des->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="s6_shift_id">Assigned Shift</label>
                                            <select id="s6_shift_id" name="shift_id" class="form-select select2" @if(!empty($is_view_only)) disabled @endif>
                                                <option value="">Select Shift</option>
                                                @foreach ($shifts as $s)
                                                    <option value="{{ $s->id }}" {{ $onboarding->shift_id == $s->id ? 'selected' : '' }}>
                                                        {{ $s->name }}@if(!empty($s->punch_in_minimum) && !empty($s->punch_out)) ({{ \Carbon\Carbon::parse($s->punch_in_minimum)->format('h:i A') }} - {{ \Carbon\Carbon::parse($s->punch_out)->format('h:i A') }})@endif
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label" for="s6_remarks">HR Closing Remarks / Welcome Notes</label>
                                            <textarea id="s6_remarks" name="remarks" class="form-control" rows="2" placeholder="e.g. All documents verified, system setup complete." @if(!empty($is_view_only)) readonly @endif>{{ $onboarding->remarks }}</textarea>
                                        </div>
                                    </div>

                                    <!-- Activation & Completion CTA Card -->
                                    <div class="bg-label-primary p-4 rounded-3 border mb-4">
                                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                                            <div>
                                                <h5 class="fw-bold mb-1 text-primary"><i class="ti ti-sparkles me-2"></i>OceanHR Employee Profile Sync</h5>
                                                <p class="mb-0 text-muted small">
                                                    Generate official Employee Code and sync documents, asset records, and employment profile directly into the core OceanHR system.
                                                </p>
                                            </div>
                                            <div>
                                                @if (!$onboarding->employee_id)
                                                    @if(empty($is_view_only))
                                                        <button type="button" class="btn btn-primary waves-effect waves-light" id="btn-convert-employee">
                                                            <i class="ti ti-user-plus me-1"></i> Activate & Create Full Employee
                                                        </button>
                                                    @else
                                                        <span class="badge bg-label-warning px-3 py-2"><i class="ti ti-clock me-1"></i> Employee Record Pending Activation</span>
                                                    @endif
                                                @else
                                                    <a href="{{ route('employees.show', $onboarding->employee_id) }}" target="_blank" class="btn btn-outline-primary waves-effect">
                                                        <i class="ti ti-external-link me-1"></i> View Employee Profile ({{ $onboarding->employee?->employee_code }})
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between pt-3 border-top">
                                        <button type="button" class="btn btn-label-secondary btn-prev-tab" data-prev="#tab-assets"><i class="ti ti-arrow-left me-1"></i> Previous</button>
                                        @if(empty($is_view_only))
                                            <button type="submit" class="btn btn-success waves-effect waves-light">
                                                <i class="ti ti-circle-check me-1"></i> Save & Complete Onboarding
                                            </button>
                                        @endif
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@if(empty($is_view_only))
<!-- MODAL: Upload / Edit Document -->
<div class="modal fade" id="modal-upload-document" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-upload-doc" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="document_id" id="modal_doc_id" />
                <input type="hidden" name="document_type" id="modal_doc_type" />
                <div class="modal-header">
                    <h5 class="modal-title" id="modal_doc_title">Upload Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="modal_doc_number">Document / ID Number (e.g. Aadhaar / PAN No.)</label>
                        <input type="text" id="modal_doc_number" name="document_number" class="form-control" placeholder="Enter Document / ID Number" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="modal_doc_file">Select File (JPG, PNG, PDF max 10MB)</label>
                        <input type="file" id="modal_doc_file" name="file" class="form-control" accept=".jpg,.jpeg,.png,.pdf,.webp" />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="ti ti-upload me-1"></i> Upload File</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: Add Custom Document -->
<div class="modal fade" id="modal-add-document" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-add-custom-doc" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Custom Onboarding Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="custom_doc_title">Document Title <span class="text-danger">*</span></label>
                        <input type="text" id="custom_doc_title" name="document_title" class="form-control" placeholder="Enter Document Title" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="custom_doc_type">Document Type Identifier <span class="text-danger">*</span></label>
                        <select id="custom_doc_type" name="document_type" class="form-select select2" data-dropdown-parent="#modal-add-document">
                            <option value="education_certificate">Education Certificate</option>
                            <option value="experience_letter">Experience Letter</option>
                            <option value="medical_fitness">Medical Fitness Report</option>
                            <option value="other">Other Document</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="custom_doc_number">Document / Serial Number</label>
                        <input type="text" id="custom_doc_number" name="document_number" class="form-control" placeholder="Enter Document Number (Optional)" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="custom_doc_file">Select File</label>
                        <input type="file" id="custom_doc_file" name="file" class="form-control" accept=".jpg,.jpeg,.png,.pdf,.webp" />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="ti ti-plus me-1"></i> Add Document</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: Add Custom Training Module -->
<div class="modal fade" id="modal-add-training" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-add-training" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Custom Induction Training Track</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="new_training_title">Training Title <span class="text-danger">*</span></label>
                        <input type="text" id="new_training_title" name="new_title" class="form-control" placeholder="Enter Training Title" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="new_training_desc">Description / Objectives</label>
                        <textarea id="new_training_desc" name="new_description" class="form-control" rows="3" placeholder="Enter Description / Training Objectives"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="new_training_pdf">Attach Training Material / PDF</label>
                        <input type="file" id="new_training_pdf" name="pdf_file" class="form-control" accept=".pdf" />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="ti ti-plus me-1"></i> Create Training Track</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: Upload Training PDF -->
<div class="modal fade" id="modal-upload-training-pdf" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-upload-training-pdf-direct" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="training_id" id="upload_training_id" />
                <div class="modal-header">
                    <h5 class="modal-title">Attach Training PDF</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="upload_training_pdf_file">Select PDF File</label>
                        <input type="file" id="upload_training_pdf_file" name="pdf_file" class="form-control" accept=".pdf" required />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="ti ti-upload me-1"></i> Upload PDF</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: Add / Edit Asset Allocation -->
<div class="modal fade" id="modal-add-asset" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-asset-save">
                @csrf
                <input type="hidden" name="asset_id" id="asset_modal_id" />
                <div class="modal-header">
                    <h5 class="modal-title" id="asset_modal_title">Allocate Asset</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="asset_modal_type">Asset Type <span class="text-danger">*</span></label>
                        <select id="asset_modal_type" name="asset_type" class="form-select select2" data-dropdown-parent="#modal-add-asset" required>
                            <option value="mobile">Company Mobile / SIM</option>
                            <option value="laptop">Laptop / PC</option>
                            <option value="tshirt">Uniform / T-Shirt</option>
                            <option value="shoes">Safety / Work Shoes</option>
                            <option value="id_card">ID Card & Badge</option>
                            <option value="other">Other Asset</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="asset_modal_name">Asset Name <span class="text-danger">*</span></label>
                        <input type="text" id="asset_modal_name" name="asset_name" class="form-control" placeholder="Enter Asset Name" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="asset_modal_spec">Specification / Size</label>
                        <input type="text" id="asset_modal_spec" name="specification_or_size" class="form-control" placeholder="Enter Size / Specification (e.g. Size L, 16GB RAM)" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="asset_modal_code">Serial Number / Asset Tag</label>
                        <input type="text" id="asset_modal_code" name="asset_code_or_serial" class="form-control" placeholder="Enter Serial Number / Asset Tag" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="asset_modal_status">Allocation Status</label>
                        <select id="asset_modal_status" name="status" class="form-select select2" data-dropdown-parent="#modal-add-asset">
                            <option value="pending">Pending</option>
                            <option value="assigned">Assigned / In Process</option>
                            <option value="handed_over">Handed Over / Delivered</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i> Save Asset</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@section('page_leavel_script')
    <script src="{{ asset('software/vendor/libs/select2/select2.js') }}"></script>
    <script src="{{ asset('software/vendor/libs/flatpickr/flatpickr.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const onboardingId = "{{ $onboarding->id }}";
        const csrfToken = "{{ csrf_token() }}";

        function updateProgressUI(progress, status) {
            $('#progress-text').text(progress + '%');
            $('#progress-bar-fill').css('width', progress + '%').attr('aria-valuenow', progress);
            if (progress >= 100) {
                $('#progress-bar-fill').removeClass('bg-warning bg-primary').addClass('bg-success');
            } else if (progress >= 50) {
                $('#progress-bar-fill').removeClass('bg-warning bg-success').addClass('bg-primary');
            }
            if (status) {
                $('#status-text').text(status.replace('_', ' '));
            }
        }

        $(document).ready(function() {
            $('.select2').each(function() {
                let $this = $(this);
                let parentModal = $this.closest('.modal');
                $this.select2({
                    dropdownParent: parentModal.length ? parentModal : $(document.body),
                    width: '100%'
                });
            });

            @if(empty($is_view_only))
            // Date of Birth (dd-mm-yyyy)
            flatpickr("#s1_date_of_birth", {
                dateFormat: "d-m-Y",
                maxDate: "today",
                allowInput: true
            });

            // Joining Date (dd-mm-yyyy)
            let fpJoining = flatpickr("#s1_joining_date", {
                dateFormat: "d-m-Y",
                allowInput: true
            });

            // Probation End Date (dd-mm-yyyy)
            let fpProbation = flatpickr("#s1_probation_end_date", {
                dateFormat: "d-m-Y",
                allowInput: true
            });

            function calculateProbationEndShow() {
                let joinVal = $('#s1_joining_date').val();
                let months = parseInt($('#s1_probation_period_months').val());
                if (joinVal && !isNaN(months)) {
                    if (months === 0) {
                        $('#s1_probation_status').val('confirmed').trigger('change');
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

            $('#s1_joining_date, #s1_probation_period_months').on('change', calculateProbationEndShow);
            @endif

            // Tab URL Routing & Persistence
            const isViewOnly = {{ !empty($is_view_only) ? 'true' : 'false' }};
            const currentTabFromServer = "{{ $active_tab ?? 'basic-details' }}";
            const onboardingBaseUrl = "{{ url('software/onboarding/' . $onboarding->id) }}" + (isViewOnly ? '' : '/edit');

            $('a[data-bs-toggle="pill"]').on('shown.bs.tab', function(e) {
                let target = $(e.target).attr('href');
                let slug = $(e.target).data('slug');
                if (slug) {
                    sessionStorage.setItem('onboarding_active_tab_' + onboardingId, slug);
                    if (history.replaceState) {
                        history.replaceState(null, null, onboardingBaseUrl + '/' + slug);
                    }
                    if ($('#btn-mode-toggle').length) {
                        let modeBase = isViewOnly ? "{{ url('software/onboarding/' . $onboarding->id . '/edit') }}" : "{{ url('software/onboarding/' . $onboarding->id) }}";
                        $('#btn-mode-toggle').attr('href', modeBase + '/' + slug);
                    }
                }
            });

            // Initial tab selection
            let initialTabSlug = currentTabFromServer || sessionStorage.getItem('onboarding_active_tab_' + onboardingId);
            if (initialTabSlug) {
                let targetBtn = $(`a[data-slug="${initialTabSlug}"]`);
                if (targetBtn.length && !targetBtn.hasClass('active')) {
                    targetBtn.tab('show');
                }
            }

            // Next / Prev step navigation buttons
            $('.btn-next-tab').on('click', function() {
                let nextTarget = $(this).data('next');
                $(`a[href="${nextTarget}"]`).tab('show');
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
            $('.btn-prev-tab').on('click', function() {
                let prevTarget = $(this).data('prev');
                $(`a[href="${prevTarget}"]`).tab('show');
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });

            // STEP 1: Form Submit
            $('#form-step1').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('onboarding.step1.update', $onboarding->id) }}",
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        if (res.status) {
                            Swal.fire({ icon: 'success', title: 'Saved!', text: res.message, timer: 1500, showConfirmButton: false });
                            updateProgressUI(res.progress);
                            $('a[href="#tab-documents"]').tab('show');
                        }
                    },
                    error: function(err) {
                        Swal.fire({ icon: 'error', title: 'Error', text: err.responseJSON?.message || 'Error updating details' });
                    }
                });
            });

            // STEP 2: Document Upload Modal Trigger
            $(document).on('click', '.btn-upload-doc', function() {
                let id = $(this).data('id');
                let type = $(this).data('type');
                let title = $(this).data('title');
                let num = $(this).data('num');

                $('#modal_doc_id').val(id);
                $('#modal_doc_type').val(type);
                $('#modal_doc_title').text('Upload ' + title);
                $('#modal_doc_number').val(num);
                $('#modal_doc_file').val('');
                $('#modal-upload-document').modal('show');
            });

            $('#form-upload-doc, #form-add-custom-doc').on('submit', function(e) {
                e.preventDefault();
                let formData = new FormData(this);
                $.ajax({
                    url: "{{ route('onboarding.document.upload', $onboarding->id) }}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        if (res.status) {
                            $('#modal-upload-document, #modal-add-document').modal('hide');
                            sessionStorage.setItem('onboarding_active_tab_' + onboardingId, 'documents');
                            Swal.fire({ icon: 'success', title: 'Uploaded!', text: res.message, timer: 1500, showConfirmButton: false });
                            setTimeout(() => location.reload(), 1200);
                        }
                    },
                    error: function(err) {
                        Swal.fire({ icon: 'error', title: 'Error', text: err.responseJSON?.message || 'Upload failed' });
                    }
                });
            });

            // STEP 2: Document Verify / Reject
            $(document).on('click', '.btn-verify-doc', function() {
                let docId = $(this).data('id');
                let btn = $(this);
                btn.prop('disabled', true);

                $.ajax({
                    url: "{{ route('onboarding.document.verify', $onboarding->id) }}",
                    type: 'POST',
                    data: { _token: csrfToken, document_id: docId, action: 'verified' },
                    success: function(res) {
                        btn.prop('disabled', false);
                        if (res.status) {
                            $('#badge-doc-' + docId)
                                .removeClass('bg-label-secondary bg-label-info bg-label-danger bg-label-primary bg-label-warning')
                                .addClass('bg-label-success')
                                .text('Verified');
                            $('#doc-card-' + docId).find('.bg-danger-subtle').remove();
                            updateProgressUI(res.progress);
                            Swal.fire({ icon: 'success', title: 'Verified!', text: res.message, timer: 1200, showConfirmButton: false });
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false);
                        Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Verification failed. Please try again.' });
                    }
                });
            });

            $(document).on('click', '.btn-reject-doc', function() {
                let docId = $(this).data('id');
                let btn = $(this);
                Swal.fire({
                    title: 'Reject Document',
                    input: 'text',
                    inputLabel: 'Reason for Rejection',
                    inputPlaceholder: 'e.g. Document image is blurry or expired',
                    showCancelButton: true,
                    confirmButtonText: 'Reject',
                    confirmButtonColor: '#ea5455'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        btn.prop('disabled', true);
                        $.ajax({
                            url: "{{ route('onboarding.document.verify', $onboarding->id) }}",
                            type: 'POST',
                            data: { _token: csrfToken, document_id: docId, action: 'rejected', rejection_reason: result.value },
                            success: function(res) {
                                btn.prop('disabled', false);
                                if (res.status) {
                                    $('#badge-doc-' + docId)
                                        .removeClass('bg-label-secondary bg-label-info bg-label-success bg-label-primary bg-label-warning')
                                        .addClass('bg-label-danger')
                                        .text('Rejected');
                                    updateProgressUI(res.progress);
                                    Swal.fire({ icon: 'info', title: 'Rejected', text: res.message, timer: 1200, showConfirmButton: false });
                                }
                            },
                            error: function(xhr) {
                                btn.prop('disabled', false);
                                Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Rejection failed. Please try again.' });
                            }
                        });
                    }
                });
            });

            // Dynamic Role Template Loader (Developer, UI/UX, QA, HR, Sales, Marketing, Operations)
            function renderKraTable(kraList) {
                let html = '';
                if (Array.isArray(kraList)) {
                    kraList.forEach(function(kra) {
                        let kpisHtml = '<ul class="mb-0 ps-3 small text-muted">';
                        if (Array.isArray(kra.kpis)) {
                            kra.kpis.forEach(function(kpi) {
                                kpisHtml += `<li><strong>${kpi.metric}</strong>: <span class="badge bg-label-success">${kpi.target}</span></li>`;
                            });
                        }
                        kpisHtml += '</ul>';

                        let freq = (kra.kpis && kra.kpis[0] && kra.kpis[0].frequency) ? kra.kpis[0].frequency : 'Monthly';

                        html += `<tr>
                            <td class="align-middle fw-semibold text-heading">
                                <i class="ti ti-chevron-right text-primary me-1"></i>${kra.kra_title}
                            </td>
                            <td class="align-middle">
                                <span class="badge bg-label-primary fs-7">${kra.weightage}%</span>
                            </td>
                            <td class="align-middle">${kpisHtml}</td>
                            <td class="align-middle">
                                <span class="badge bg-label-secondary">${freq}</span>
                            </td>
                        </tr>`;
                    });
                }
                $('#s3_kra_tbody').html(html);
                $('#kra-count-badge').text((kraList ? kraList.length : 0) + ' KRA Modules');
            }

            // Auto-resize Job Summary Textarea so height automatically fits the content with zero scrollbars
            function autoResizeJdTextarea() {
                let el = document.getElementById('s3_job_description');
                if (el) {
                    el.style.height = 'auto';
                    el.style.height = Math.max(140, el.scrollHeight + 10) + 'px';
                    el.style.overflowY = 'hidden';
                }
            }

            $('#s3_job_description').on('input keyup change focus', autoResizeJdTextarea);
            $('a[href="#tab-jd-kra"]').on('shown.bs.tab', function() {
                setTimeout(autoResizeJdTextarea, 50);
            });
            $(window).on('load resize', autoResizeJdTextarea);
            setTimeout(autoResizeJdTextarea, 100);

            // Industry Filter
            $('.btn-filter-industry').on('click', function() {
                let filter = $(this).data('filter');
                $('.btn-filter-industry').removeClass('active');
                $(this).addClass('active');

                $('#role-search-input').val('');
                let visibleCount = 0;

                if (filter === 'all') {
                    $('.role-card-col').show();
                    visibleCount = $('.role-card-col').length;
                } else {
                    $('.role-card-col').each(function() {
                        if ($(this).data('industry') === filter) {
                            $(this).show();
                            visibleCount++;
                        } else {
                            $(this).hide();
                        }
                    });
                }

                if (visibleCount === 0) {
                    $('#no-role-found-msg').removeClass('d-none');
                } else {
                    $('#no-role-found-msg').addClass('d-none');
                }
            });

            // Live Role Search
            $('#role-search-input').on('input keyup', function() {
                let term = $(this).val().toLowerCase().trim();
                let activeFilter = $('.btn-filter-industry.active').data('filter') || 'all';
                let visibleCount = 0;

                $('.role-card-col').each(function() {
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
                    $('#no-role-found-msg').removeClass('d-none');
                } else {
                    $('#no-role-found-msg').addClass('d-none');
                }
            });

            $('.btn-load-role-template').on('click', function() {
                let roleKey = $(this).data('role');
                let card = $(this);
                
                $('.role-chip-card').removeClass('active-selected');
                $('.role-check-indicator').addClass('d-none');
                card.addClass('active-selected');
                card.find('.role-check-indicator').removeClass('d-none');

                $.ajax({
                    url: "{{ url('software/onboarding/role-template') }}/" + roleKey,
                    type: 'GET',
                    success: function(res) {
                        if (res.status && res.template) {
                            let t = res.template;
                            $('#s3_job_description').val(t.job_description);
                            $('#s3_attendance_target_percentage').val(t.attendance_target_percentage);
                            $('#s3_late_mark_tolerance').val(t.late_mark_tolerance);
                            $('#s3_daily_working_hours_target').val(t.daily_working_hours_target);
                            $('#s3_kra_kpi_json').val(JSON.stringify(t.kra_kpis));
                            $('#active-template-badge').html('<i class="ti ti-check me-1"></i>Applied: ' + t.title);
                            $('#badge-jd-designation').text(t.badge || t.title);

                            renderKraTable(t.kra_kpis);
                            setTimeout(autoResizeJdTextarea, 50);

                            Swal.fire({
                                icon: 'success',
                                title: t.title + ' Template Loaded!',
                                text: 'Job Description, attendance KPIs, and KRA evaluation metrics have been updated.',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        }
                    }
                });
            });

            // STEP 3: Company Overview Submit
            $('#form-step3').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('onboarding.step3.update', $onboarding->id) }}",
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        if (res.status) {
                            Swal.fire({ icon: 'success', title: 'Confirmed!', text: res.message, timer: 1500, showConfirmButton: false });
                            updateProgressUI(res.progress);
                            $('a[href="#tab-trainings"]').tab('show');
                        }
                    }
                });
            });

            // STEP 4: Training Status Change
            $(document).on('change', '.select-training-status', function() {
                let trainingId = $(this).data('id');
                let status = $(this).val();
                $.ajax({
                    url: "{{ route('onboarding.training.status', $onboarding->id) }}",
                    type: 'POST',
                    data: { _token: csrfToken, training_id: trainingId, status: status },
                    success: function(res) {
                        if (res.status) {
                            let badge = $('#badge-training-' + trainingId);
                            badge.removeClass('bg-label-warning bg-label-primary bg-label-success');
                            if (status == 'completed') badge.addClass('bg-label-success').text('Completed');
                            else if (status == 'in_progress') badge.addClass('bg-label-primary').text('In Progress');
                            else badge.addClass('bg-label-warning').text('Pending');

                            updateProgressUI(res.progress);
                        }
                    }
                });
            });

            // STEP 4: Attach Training PDF
            $(document).on('click', '.btn-upload-training-pdf', function() {
                let id = $(this).data('id');
                $('#upload_training_id').val(id);
                $('#upload_training_pdf_file').val('');
                $('#modal-upload-training-pdf').modal('show');
            });

            $('#form-upload-training-pdf-direct, #form-add-training').on('submit', function(e) {
                e.preventDefault();
                let formData = new FormData(this);
                $.ajax({
                    url: "{{ route('onboarding.training.status', $onboarding->id) }}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        if (res.status) {
                            $('#modal-upload-training-pdf, #modal-add-training').modal('hide');
                            sessionStorage.setItem('onboarding_active_tab_' + onboardingId, 'trainings');
                            Swal.fire({ icon: 'success', title: 'Saved!', text: res.message, timer: 1500, showConfirmButton: false });
                            setTimeout(() => location.reload(), 1200);
                        }
                    }
                });
            });

            // STEP 5: Asset Allocation Modal & Status
            $(document).on('click', '.btn-edit-asset', function() {
                $('#asset_modal_id').val($(this).data('id'));
                $('#asset_modal_type').val($(this).data('type'));
                $('#asset_modal_name').val($(this).data('name'));
                $('#asset_modal_spec').val($(this).data('spec'));
                $('#asset_modal_code').val($(this).data('code'));
                $('#asset_modal_status').val($(this).data('status'));
                $('#asset_modal_title').text('Edit Asset Allocation');
                $('#modal-add-asset').modal('show');
            });

            $('#form-asset-save').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('onboarding.asset.assign', $onboarding->id) }}",
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        if (res.status) {
                            $('#modal-add-asset').modal('hide');
                            sessionStorage.setItem('onboarding_active_tab_' + onboardingId, 'assets');
                            Swal.fire({ icon: 'success', title: 'Asset Saved!', text: res.message, timer: 1500, showConfirmButton: false });
                            setTimeout(() => location.reload(), 1200);
                        }
                    }
                });
            });

            $(document).on('click', '.btn-asset-quick-status', function() {
                let assetId = $(this).data('id');
                let status = $(this).data('status');
                let card = $(this).closest('.asset-card');
                let name = card.find('h6').text();
                let spec = card.find('.bg-light strong').first().text();

                $.ajax({
                    url: "{{ route('onboarding.asset.assign', $onboarding->id) }}",
                    type: 'POST',
                    data: {
                        _token: csrfToken,
                        asset_id: assetId,
                        asset_type: 'other',
                        asset_name: name,
                        specification_or_size: spec,
                        status: status
                    },
                    success: function(res) {
                        if (res.status) {
                            let badge = $('#badge-asset-' + assetId);
                            badge.removeClass('bg-label-warning bg-label-primary bg-label-success');
                            if (status == 'handed_over') badge.addClass('bg-label-success').text('Handed Over');
                            else if (status == 'assigned') badge.addClass('bg-label-primary').text('Assigned');
                            updateProgressUI(res.progress);
                            Swal.fire({ icon: 'success', title: 'Status Updated', timer: 1000, showConfirmButton: false });
                        }
                    }
                });
            });

            // STEP 6: Finalize Onboarding & Assign Reporting Manager
            $('#form-step6').on('submit', function(e) {
                e.preventDefault();
                let formData = $(this).serialize() + '&mark_complete=1';
                $.ajax({
                    url: "{{ route('onboarding.step6.finalize', $onboarding->id) }}",
                    type: 'POST',
                    data: formData,
                    success: function(res) {
                        if (res.status) {
                            updateProgressUI(100, 'completed');
                            Swal.fire({
                                icon: 'success',
                                title: 'Onboarding Completed!',
                                text: res.message,
                                confirmButtonText: 'Great!'
                            }).then(() => location.reload());
                        }
                    },
                    error: function(err) {
                        Swal.fire({ icon: 'error', title: 'Error', text: err.responseJSON?.message || 'Finalization failed' });
                    }
                });
            });

            // Convert to full OceanHR employee
            $('#btn-convert-employee').on('click', function() {
                Swal.fire({
                    title: 'Activate Employee?',
                    text: "This will create a live employee code and sync profile, assets, and documents into OceanHR.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Activate!',
                    customClass: { confirmButton: 'btn btn-primary me-3', cancelButton: 'btn btn-label-secondary' },
                    buttonsStyling: false
                }).then(function(result) {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('onboarding.employee.convert', $onboarding->id) }}",
                            type: 'POST',
                            data: { _token: csrfToken },
                            success: function(res) {
                                if (res.status) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Activated!',
                                        text: res.message,
                                        confirmButtonText: 'OK'
                                    }).then(() => location.reload());
                                }
                            },
                            error: function(err) {
                                Swal.fire({ icon: 'error', title: 'Error', text: err.responseJSON?.message || 'Activation failed' });
                            }
                        });
                    }
                });
            });

            // Quick Complete button in top header
            $('#btn-quick-complete').on('click', function() {
                $('a[href="#tab-reporting"]').tab('show');
                window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
            });
        });
    </script>
@endsection
