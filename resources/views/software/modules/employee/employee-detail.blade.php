@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : 'Employee';
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $company_id = isset($modules['company_id']) ? $modules['company_id'] : null;

    $primaryEmp = $show?->employmentDetail ?? ($show?->employment_details?->first() ?? null);
@endphp
@section('title', ($show?->proper_name ?? $show?->full_name ?? 'Employee') . ' - Detail')

@section('page_leavel_style')
    <link rel="stylesheet" href="{{ asset('software/vendor/libs/flatpickr/flatpickr.css') }}" />
    <style>
        /* Modern HRMS Profile Styling */
        .emp-profile-shell {
            background: #fff;
            border-radius: 14px;
            border: 1px solid rgba(75, 70, 92, 0.08);
            box-shadow: 0 4px 18px rgba(75, 70, 92, 0.04);
        }

        /* Top Header Bar */
        .emp-top-header {
            background: #ffffff;
            border-bottom: 1px solid #edf2f7;
            padding: 16px 24px;
            border-top-left-radius: 14px;
            border-top-right-radius: 14px;
        }
        .emp-avatar-img {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e2e8f0;
            box-shadow: 0 2px 6px rgba(0,0,0,0.06);
        }
        .emp-title-text {
            font-size: 1.15rem;
            font-weight: 700;
            color: #1e293b;
            letter-spacing: 0.3px;
        }
        .emp-code-badge {
            background-color: #f1f5f9;
            color: #475569;
            font-weight: 600;
            font-size: 0.85rem;
            padding: 4px 10px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        /* Split Layout Container */
        .emp-profile-body {
            display: flex;
            min-height: 480px;
        }
        .emp-sidebar-wrapper {
            width: 215px;
            min-width: 215px;
            max-width: 215px;
            flex-shrink: 0;
            border-right: 1px solid #edf2f7;
            background: #fafbfc;
            border-bottom-left-radius: 14px;
        }
        .emp-nav-menu {
            padding: 12px 8px;
            height: 100%;
        }
        .emp-nav-item {
            display: flex;
            align-items: center;
            padding: 9px 12px;
            margin-bottom: 3px;
            border-radius: 7px;
            color: #475569;
            font-size: 0.835rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            border: none;
            background: transparent;
            width: 100%;
            text-align: left;
            white-space: nowrap;
        }
        .emp-nav-item i {
            font-size: 1.05rem;
            margin-right: 8px;
            color: #64748b;
            width: 18px;
            text-align: center;
            transition: color 0.2s ease;
            flex-shrink: 0;
        }
        .emp-nav-item:hover {
            background-color: #f1f5f9;
            color: #1e293b;
        }
        .emp-nav-item:hover i {
            color: #2563eb;
        }
        .emp-nav-item.active {
            background-color: #2563eb !important;
            color: #ffffff !important;
            font-weight: 600;
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.2);
        }
        .emp-nav-item.active i {
            color: #ffffff !important;
        }

        /* Right Content Area */
        .emp-content-wrapper {
            flex: 1 1 auto;
            min-width: 0;
            background: #ffffff;
            border-bottom-right-radius: 14px;
        }
        .emp-content-area {
            /* padding: 24px 30px; */
        }
        .emp-section-heading {
            font-size: 1.25rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0;
        }
        .emp-action-btn {
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            color: #64748b;
            background: #fff;
            transition: all 0.2s ease;
            font-size: 0.95rem;
            text-decoration: none;
        }
        .emp-action-btn:hover {
            color: #2563eb;
            border-color: #2563eb;
            background: #f8fafc;
        }

        /* Responsive */
        @media (max-width: 767.98px) {
            .emp-profile-body {
                flex-direction: column;
            }
            .emp-sidebar-wrapper {
                width: 100%;
                min-width: 100%;
                max-width: 100%;
                border-right: none;
                border-bottom: 1px solid #edf2f7;
            }
            .emp-content-area {
                padding: 20px 16px;
            }
        }

        /* Sub Tabs */
        .emp-sub-tabs {
            border-bottom: 2px solid #e2e8f0;
            display: flex;
            gap: 24px;
            margin-top: 14px;
            margin-bottom: 20px;
        }
        .emp-sub-tab-btn {
            background: none;
            border: none;
            padding: 8px 4px 12px 4px;
            font-size: 0.9rem;
            font-weight: 600;
            color: #64748b;
            position: relative;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .emp-sub-tab-btn:hover {
            color: #2563eb;
        }
        .emp-sub-tab-btn.active {
            color: #2563eb;
        }
        .emp-sub-tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: #2563eb;
            border-radius: 2px;
        }

        /* Field Display Groups */
        .detail-field-group {
            margin-bottom: 18px;
        }
        .field-label {
            font-size: 0.78rem;
            font-weight: 500;
            color: #64748b;
            margin-bottom: 3px;
            display: block;
        }
        .field-value {
            font-size: 0.92rem;
            font-weight: 600;
            color: #1e293b;
            line-height: 1.4;
            word-break: break-word;
        }
        .field-value a {
            color: #2563eb;
            text-decoration: none;
        }
        .field-value a:hover {
            text-decoration: underline;
        }
        .section-subheading {
            font-size: 1.02rem;
            font-weight: 700;
            color: #0f172a;
            margin-top: 18px;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 6px;
        }

        /* Responsive */
        @media (max-width: 991.98px) {
            .emp-nav-menu {
                border-right: none;
                border-bottom: 1px solid #edf2f7;
                min-height: auto;
            }
            .emp-content-area {
                padding: 20px 16px;
            }
        }
    </style>
@endsection

@section('content')
    <div class="px-1 mb-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            @include('software.inlcudes.breadcrumb', [
                'breadcrumbArray' => [
                    ['title' => $page_title, 'url' => route($route . '.index')],
                    [
                        'title' => ($show?->proper_name ?? $show?->full_name ?? 'Employee') . ' Detail',
                        'url' => '',
                    ],
                ],
                'route' => $route,
                'show_back_btn' => true,
            ])
        </div>
    </div>

    <!-- Main HRMS Profile Container -->
    <div class="emp-profile-shell mb-4">
        <!-- Top Header Bar -->
        <div class="emp-top-header d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <img src="{{ $show?->has_profile_image ? asset($show->profile_image) : ($show?->employee_photo_url . '&size=96') }}" alt="{{ $show?->full_name }}" class="emp-avatar-img" />
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="emp-title-text">{{ strtoupper($show?->proper_name ?? $show?->full_name ?? 'EMPLOYEE') }}</span>
                        <span class="emp-code-badge">
                            <i class="ti ti-id-badge"></i> {{ $show?->employee_code ?? 'N/A' }}
                        </span>
                        <span class="badge bg-{{ ($show?->status ?? '') == 'active' ? 'success' : (($show?->status ?? '') == 'resigned' ? 'warning' : 'danger') }} ms-1">
                            {{ ucfirst($show?->status ?? 'Active') }}
                        </span>
                    </div>
                    <div class="small text-muted mt-1 d-flex align-items-center gap-3 flex-wrap">
                        @if($show?->current_role?->name)
                            <span><i class="ti ti-briefcase me-1"></i>{{ $show->current_role->name }}</span>
                        @endif
                        @if($show?->company?->company_name)
                            <span><i class="ti ti-building me-1"></i>{{ $show->company->company_name }}</span>
                        @endif
                        @if($show?->branch?->name)
                            <span><i class="ti ti-git-branch me-1"></i>{{ $show->branch->name }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                @if(isset($modules['update_permission']) && $modules['update_permission'])
                <a href="{{ route($route . '.edit', $show->id) }}" class="btn btn-sm btn-primary">
                    <i class="fa-solid fa-pen-to-square me-1"></i> Edit Profile
                </a>
                @endif
                <a href="{{ route($route . '.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>

        <!-- 2-Column Split Layout -->
        <div class="emp-profile-body">
            <!-- Left Navigation Menu -->
            <div class="emp-sidebar-wrapper">
                <div class="emp-nav-menu" role="tablist">
                    <button type="button" class="emp-nav-item active" data-bs-toggle="tab" data-bs-target="#tab-employee-details">
                        <i class="ti ti-user"></i> Employee Details
                    </button>

                    <button type="button" class="emp-nav-item" data-bs-toggle="tab" data-bs-target="#tab-contact-info">
                        <i class="ti ti-phone-call"></i> Contact Information
                    </button>

                    <button type="button" class="emp-nav-item" data-bs-toggle="tab" data-bs-target="#tab-employment-details">
                        <i class="ti ti-id-badge"></i> Employment Details
                    </button>

                    <button type="button" class="emp-nav-item" data-bs-toggle="tab" data-bs-target="#tab-assign-assets">
                        <i class="ti ti-device-laptop"></i> Assign Assets
                    </button>

                    <button type="button" class="emp-nav-item" data-bs-toggle="tab" data-bs-target="#tab-increment-details">
                        <i class="ti ti-trending-up"></i> Increment Details
                    </button>

                    <button type="button" class="emp-nav-item" data-bs-toggle="tab" data-bs-target="#tab-education-experience">
                        <i class="ti ti-school"></i> Education & Experience
                    </button>

                    <button type="button" class="emp-nav-item" data-bs-toggle="tab" data-bs-target="#tab-salary-details">
                        <i class="ti ti-currency-rupee"></i> Salary Details
                    </button>

                    <button type="button" class="emp-nav-item" data-bs-toggle="tab" data-bs-target="#tab-bank-details">
                        <i class="ti ti-building-bank"></i> Bank Details
                    </button>
                </div>
            </div>

            <!-- Right Content Area -->
            <div class="emp-content-wrapper">
                <div class="emp-content-area tab-content">
                    
                    {{-- 1. EMPLOYEE DETAILS TAB --}}
                    <div class="tab-pane fade show active" id="tab-employee-details" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h4 class="emp-section-heading">Employee Details</h4>
                            <div class="d-flex align-items-center gap-2">
                                <a href="javascript:void(0);" onclick="location.reload();" class="emp-action-btn" title="Refresh"><i class="ti ti-refresh"></i></a>
                                @if(isset($modules['update_permission']) && $modules['update_permission'])
                                <a href="{{ route($route . '.edit', $show->id) }}" class="emp-action-btn" title="Edit Employee"><i class="fa-solid fa-pen-to-square"></i></a>
                                @endif
                            </div>
                        </div>
                        <div class="emp-sub-tabs">
                            <button type="button" class="emp-sub-tab-btn active">Basic Info</button>
                        </div>

                        {{-- Basic Info --}}
                        <div class="section-subheading">Personal & Identification</div>
                        <div class="row">
                            <div class="col-md-4 detail-field-group">
                                <span class="field-label">Employee Code</span>
                                <span class="field-value">{{ $show?->employee_code ?? '--' }}</span>
                            </div>
                            <div class="col-md-4 detail-field-group">
                                <span class="field-label">Biometric User ID</span>
                                <span class="field-value">{{ $show?->biometric_user_id ?? '--' }}</span>
                            </div>
                            <div class="col-md-4 detail-field-group">
                                <span class="field-label">Full Name</span>
                                <span class="field-value">{{ $show?->proper_name ?? $show?->full_name ?? '--' }}</span>
                            </div>
                            <div class="col-md-4 detail-field-group">
                                <span class="field-label">Surname</span>
                                <span class="field-value">{{ $show?->first_name ?? '--' }}</span>
                            </div>
                            <div class="col-md-4 detail-field-group">
                                <span class="field-label">First Name</span>
                                <span class="field-value">{{ $show?->middle_name ?? '--' }}</span>
                            </div>
                            <div class="col-md-4 detail-field-group">
                                <span class="field-label">Father / Husband Name</span>
                                <span class="field-value">{{ $show?->father_name ?? '--' }}</span>
                            </div>
                            <div class="col-md-4 detail-field-group">
                                <span class="field-label">Date of Birth</span>
                                <span class="field-value">{{ $show?->date_of_birth ? \Carbon\Carbon::parse($show->date_of_birth)->format('d-M-Y') : '--' }}</span>
                            </div>
                            <div class="col-md-4 detail-field-group">
                                <span class="field-label">Gender</span>
                                <span class="field-value">{{ ucfirst($show?->gender ?? '--') }}</span>
                            </div>
                            <div class="col-md-4 detail-field-group">
                                <span class="field-label">Blood Group</span>
                                <span class="field-value">{{ $show?->blood_group ?? '--' }}</span>
                            </div>
                            <div class="col-md-4 detail-field-group">
                                <span class="field-label">Marital Status</span>
                                <span class="field-value">{{ ucfirst($show?->marital_status ?? '--') }}</span>
                            </div>
                            <div class="col-md-4 detail-field-group">
                                <span class="field-label">Date of Anniversary</span>
                                <span class="field-value">{{ $show?->date_of_anniversary ? \Carbon\Carbon::parse($show->date_of_anniversary)->format('d-M-Y') : '--' }}</span>
                            </div>
                            <div class="col-md-4 detail-field-group">
                                <span class="field-label">Aadhar Card Number</span>
                                <span class="field-value">{{ $show?->aadhar_card_number ?? '--' }}</span>
                            </div>
                            <div class="col-md-4 detail-field-group">
                                <span class="field-label">PAN Card Number</span>
                                <span class="field-value">{{ $show?->pan_card_number ?? '--' }}</span>
                            </div>
                            @if($show?->resign_date)
                            <div class="col-md-4 detail-field-group">
                                <span class="field-label">Resignation Date</span>
                                <span class="field-value text-danger">{{ \Carbon\Carbon::parse($show->resign_date)->format('d-M-Y') }}</span>
                            </div>
                            @endif
                        </div>

                        {{-- Login Details for Admin --}}
                        @if(isset($modules['currentGuard']) && $modules['currentGuard'] === 'admin_software')
                        <div class="section-subheading">Login Credentials</div>
                        <div class="row">
                            <div class="col-md-3 detail-field-group">
                                <span class="field-label">App Key</span>
                                <span class="field-value">{{ $show?->company?->app_key ?? '--' }}</span>
                            </div>
                            <div class="col-md-3 detail-field-group">
                                <span class="field-label">Username</span>
                                <span class="field-value">{{ $show?->username ?? '--' }}</span>
                            </div>
                            <div class="col-md-3 detail-field-group">
                                <span class="field-label">Password</span>
                                <span class="field-value">{{ isset($show?->sp) ? \App\Helpers\Helper::getSP($show->sp) : '--' }}</span>
                            </div>
                            <div class="col-md-3 detail-field-group d-flex align-items-end">
                                <button type="button" class="btn btn-sm btn-outline-primary copy-login-details-btn mb-2" 
                                    data-app-key="{{ $show?->company?->app_key ?? '' }}"
                                    data-username="{{ $show?->username ?? '' }}"
                                    data-password="{{ isset($show?->sp) ? \App\Helpers\Helper::getSP($show->sp) : '' }}"
                                    title="Copy & Share Login Details">
                                    <i class="ti ti-copy me-1"></i> Copy & Share
                                </button>
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- 2. CONTACT INFORMATION TAB (SEPARATE DEDICATED TAB) --}}
                    <div class="tab-pane fade" id="tab-contact-info" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h4 class="emp-section-heading">Contact Information</h4>
                            <div class="d-flex align-items-center gap-2">
                                <a href="javascript:void(0);" onclick="location.reload();" class="emp-action-btn" title="Refresh"><i class="ti ti-refresh"></i></a>
                                @if(isset($modules['update_permission']) && $modules['update_permission'])
                                <a href="{{ route($route . '.edit', $show->id) }}" class="emp-action-btn" title="Edit Contact Details"><i class="fa-solid fa-pen-to-square"></i></a>
                                @endif
                            </div>
                        </div>
                        <div class="emp-sub-tabs">
                            <button type="button" class="emp-sub-tab-btn active">Contacts & Address</button>
                        </div>

                        {{-- Contacts --}}
                        <div class="section-subheading">Phone & Email</div>
                        <div class="row">
                            <div class="col-md-4 detail-field-group">
                                <span class="field-label">Email Address</span>
                                <span class="field-value"><a href="mailto:{{ $show?->email }}">{{ $show?->email ?? '--' }}</a></span>
                            </div>
                            <div class="col-md-4 detail-field-group">
                                <span class="field-label">Contact Number</span>
                                <span class="field-value"><a href="tel:{{ $show?->contact_number }}">{{ $show?->contact_number ?? '--' }}</a></span>
                            </div>
                            <div class="col-md-4 detail-field-group">
                                <span class="field-label">Other Contact Number</span>
                                <span class="field-value">{{ $show?->other_number ?? '--' }}</span>
                            </div>
                        </div>

                        {{-- Address --}}
                        <div class="section-subheading mt-3">Address Details</div>
                        <div class="row">
                            <div class="col-md-6 detail-field-group">
                                <span class="field-label">Current Address</span>
                                <span class="field-value">{{ $show?->current_address ?? '--' }}</span>
                            </div>
                            <div class="col-md-6 detail-field-group">
                                <span class="field-label">Permanent Address</span>
                                <span class="field-value">{{ $show?->permanent_address ?? '--' }}</span>
                            </div>
                            <div class="col-md-4 detail-field-group">
                                <span class="field-label">City</span>
                                <span class="field-value">{{ $show?->city?->name ?? '--' }}</span>
                            </div>
                            <div class="col-md-4 detail-field-group">
                                <span class="field-label">State</span>
                                <span class="field-value">{{ $show?->state?->name ?? '--' }}</span>
                            </div>
                            <div class="col-md-4 detail-field-group">
                                <span class="field-label">Country</span>
                                <span class="field-value">{{ $show?->country?->name ?? '--' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- 3. EMPLOYMENT DETAILS TAB --}}
                    <div class="tab-pane fade" id="tab-employment-details" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h4 class="emp-section-heading">Employment Details</h4>
                            <div class="d-flex align-items-center gap-2">
                                <a href="javascript:void(0);" onclick="location.reload();" class="emp-action-btn" title="Refresh"><i class="ti ti-refresh"></i></a>
                                @if(isset($modules['employment_details_add_permission']) && $modules['employment_details_add_permission'])
                                    @if($primaryEmp)
                                        <a href="{{ route('employment-details.edit', $primaryEmp->id) }}" class="emp-action-btn" title="Edit Employment">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                    @else
                                        <a href="{{ route('employment-details.create', ['employee_id' => $show->id]) }}" class="btn btn-sm btn-primary">
                                            <i class="fa-solid fa-plus me-1"></i> Add Employment
                                        </a>
                                    @endif
                                @endif
                            </div>
                        </div>
                        <div class="emp-sub-tabs">
                            <button type="button" class="emp-sub-tab-btn active">Work & Role Info</button>
                        </div>

                        @if($primaryEmp)
                            <div class="row">
                                <div class="col-md-4 detail-field-group">
                                    <span class="field-label">Designation Type</span>
                                    <span class="field-value">{{ ucfirst($primaryEmp->designation_type ?? '-') }}</span>
                                </div>
                                <div class="col-md-4 detail-field-group">
                                    <span class="field-label">Designation</span>
                                    <span class="field-value">{{ $primaryEmp->designation?->name ?? '--' }}</span>
                                </div>
                                <div class="col-md-4 detail-field-group">
                                    <span class="field-label">Department</span>
                                    <span class="field-value">{{ $primaryEmp->department?->name ?? '--' }}</span>
                                </div>
                                <div class="col-md-4 detail-field-group">
                                    <span class="field-label">Sub Department</span>
                                    <span class="field-value">{{ $primaryEmp->subdepartment?->sub_department_name ?? '--' }}</span>
                                </div>
                                <div class="col-md-4 detail-field-group">
                                    <span class="field-label">Process</span>
                                    <span class="field-value">{{ $primaryEmp->process?->name ?? '--' }}</span>
                                </div>
                                <div class="col-md-4 detail-field-group">
                                    <span class="field-label">Employment Type</span>
                                    <span class="field-value">{{ $primaryEmp->employee_type?->name ?? strtoupper($primaryEmp->employment_type ?? '--') }}</span>
                                </div>
                                <div class="col-md-4 detail-field-group">
                                    <span class="field-label">Date of Joining</span>
                                    <span class="field-value">{{ $primaryEmp->date_of_joining ? \Carbon\Carbon::parse($primaryEmp->date_of_joining)->format('d-M-Y') : '--' }}</span>
                                </div>
                                <div class="col-md-4 detail-field-group">
                                    <span class="field-label">Confirmation Date</span>
                                    <span class="field-value">{{ $primaryEmp->employment_confirmation_date ? \Carbon\Carbon::parse($primaryEmp->employment_confirmation_date)->format('d-M-Y') : '--' }}</span>
                                </div>
                                <div class="col-md-4 detail-field-group">
                                    <span class="field-label">Shift</span>
                                    <span class="field-value">{{ $primaryEmp->shiftDetail?->name ?? $primaryEmp->shift ?? '--' }}</span>
                                </div>
                                <div class="col-md-4 detail-field-group">
                                    <span class="field-label">Payment Mode</span>
                                    <span class="field-value">{{ strtoupper($primaryEmp->payment_mode ?? '--') }}</span>
                                </div>
                                <div class="col-md-4 detail-field-group">
                                    <span class="field-label">Employee PF No</span>
                                    <span class="field-value">{{ $primaryEmp->employee_pf_no ?? '--' }}</span>
                                </div>
                                <div class="col-md-4 detail-field-group">
                                    <span class="field-label">UAN No</span>
                                    <span class="field-value">{{ $primaryEmp->uan_no ?? '--' }}</span>
                                </div>
                                <div class="col-md-4 detail-field-group">
                                    <span class="field-label">Outdoor Attendance</span>
                                    <span class="field-value">{{ $primaryEmp->outdoor_attendance ?? 'No' }}</span>
                                </div>
                                <div class="col-md-4 detail-field-group">
                                    <span class="field-label">Status</span>
                                    <span class="field-value">
                                        <span class="badge bg-{{ ($primaryEmp->status ?? '') == 'active' ? 'success' : 'danger' }}">
                                            {{ ucfirst($primaryEmp->status ?? 'inactive') }}
                                        </span>
                                    </span>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-light border text-muted py-3">
                                <i class="ti ti-info-circle me-1"></i> No employment details found for this employee.
                            </div>
                        @endif
                    </div>

                    {{-- 4. ASSIGN ASSETS TAB --}}
                    <div class="tab-pane fade" id="tab-assign-assets" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h4 class="emp-section-heading">Assign Assets</h4>
                            <div class="d-flex align-items-center gap-2">
                                @if(isset($modules['assign_assets_add_permission']) && $modules['assign_assets_add_permission'])
                                <a href="{{ route('employee-assign-assets.create', ['employee_id' => $show->id]) }}" class="btn btn-sm btn-primary">
                                    <i class="fa-solid fa-plus me-1"></i> Add Asset
                                </a>
                                @endif
                            </div>
                        </div>
                        <div class="emp-sub-tabs">
                            <button type="button" class="emp-sub-tab-btn active">Allocated Assets</button>
                        </div>

                        @if($show?->employee_asign_assets && $show->employee_asign_assets->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Asset Name</th>
                                            <th>Reference No</th>
                                            <th>Date</th>
                                            <th>Description</th>
                                            <th>Status</th>
                                            @if(isset($modules['assign_assets_update_permission']) && $modules['assign_assets_update_permission'])
                                            <th class="text-center">Action</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($show->employee_asign_assets as $index => $asset)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td><strong>{{ $asset->assets?->name ?? '--' }}</strong></td>
                                            <td>{{ $asset->reference_no ?? '--' }}</td>
                                            <td>{{ $asset->date ? \Carbon\Carbon::parse($asset->date)->format('d-M-Y') : '--' }}</td>
                                            <td>{{ $asset->descrption ?? '--' }}</td>
                                            <td>
                                                <span class="badge bg-{{ $asset->status == 'active' ? 'success' : 'danger' }}">
                                                    {{ ucfirst($asset->status) }}
                                                </span>
                                            </td>
                                            @if(isset($modules['assign_assets_update_permission']) && $modules['assign_assets_update_permission'])
                                            <td class="text-center">
                                                <a href="{{ route('employee-assign-assets.edit', $asset->id) }}" class="emp-action-btn" title="Edit">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </a>
                                            </td>
                                            @endif
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-light border text-muted py-3">
                                <i class="ti ti-info-circle me-1"></i> No assets assigned to this employee.
                            </div>
                        @endif
                    </div>

                    {{-- 5. INCREMENT DETAILS TAB --}}
                    <div class="tab-pane fade" id="tab-increment-details" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h4 class="emp-section-heading">Increment Details</h4>
                            <div class="d-flex align-items-center gap-2">
                                @if(isset($modules['increment_details_add_permission']) && $modules['increment_details_add_permission'])
                                <a href="{{ route('employee-increment-details.create', ['employee_id' => $show->id]) }}" class="btn btn-sm btn-primary">
                                    <i class="fa-solid fa-plus me-1"></i> Add Increment
                                </a>
                                @endif
                            </div>
                        </div>
                        <div class="emp-sub-tabs">
                            <button type="button" class="emp-sub-tab-btn active">Increment History</button>
                        </div>

                        @if($show?->increment_details && $show->increment_details->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Increment Date</th>
                                            <th>Designation</th>
                                            <th>Basic DA</th>
                                            <th>HRA</th>
                                            <th>Conveyance</th>
                                            <th>Medical</th>
                                            <th>Special</th>
                                            <th>PF</th>
                                            <th>Effective Month/Year</th>
                                            <th>Status</th>
                                            @if(isset($modules['increment_details_update_permission']) && $modules['increment_details_update_permission'])
                                            <th class="text-center">Action</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($show->increment_details as $index => $increment)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $increment->icrement_date ?? '--' }}</td>
                                            <td><strong>{{ $increment->designation?->name ?? '--' }}</strong></td>
                                            <td>₹{{ number_format($increment->basic_da ?? 0, 2) }}</td>
                                            <td>₹{{ number_format($increment->hra ?? 0, 2) }}</td>
                                            <td>₹{{ number_format($increment->conveyance_allowance ?? 0, 2) }}</td>
                                            <td>₹{{ number_format($increment->medical_allowance ?? 0, 2) }}</td>
                                            <td>₹{{ number_format($increment->special_allowance ?? 0, 2) }}</td>
                                            <td>₹{{ number_format($increment->pf ?? 0, 2) }}</td>
                                            <td>{{ $increment->effective_month ?? '--' }}/{{ $increment->effective_year ?? '--' }}</td>
                                            <td>
                                                <span class="badge bg-{{ $increment->status == 'active' ? 'success' : 'danger' }}">
                                                    {{ ucfirst($increment->status) }}
                                                </span>
                                            </td>
                                            @if(isset($modules['increment_details_update_permission']) && $modules['increment_details_update_permission'])
                                            <td class="text-center">
                                                <a href="{{ route('employee-increment-details.edit', $increment->id) }}" class="emp-action-btn" title="Edit">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </a>
                                            </td>
                                            @endif
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-light border text-muted py-3">
                                <i class="ti ti-info-circle me-1"></i> No increment details found for this employee.
                            </div>
                        @endif
                    </div>

                    {{-- 6. EDUCATION & EXPERIENCE TAB --}}
                    <div class="tab-pane fade" id="tab-education-experience" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h4 class="emp-section-heading">Education & Experience</h4>
                            <div class="d-flex align-items-center gap-2">
                                @if(isset($modules['education_experience_add_permission']) && $modules['education_experience_add_permission'])
                                <a href="{{ route('employee-education-experience.create', ['employee_id' => $show->id]) }}" class="btn btn-sm btn-primary">
                                    <i class="fa-solid fa-plus me-1"></i> Add Education/Experience
                                </a>
                                @endif
                            </div>
                        </div>
                        <div class="emp-sub-tabs">
                            <button type="button" class="emp-sub-tab-btn active">Qualifications & History</button>
                        </div>

                        @if($show?->education_experience_details && $show->education_experience_details->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Degree</th>
                                            <th>Institution</th>
                                            <th>Passing Year</th>
                                            <th>Marks/Class</th>
                                            <th>Company</th>
                                            <th>Joining Date</th>
                                            <th>Left Date</th>
                                            <th>Designation</th>
                                            <th>CTC Salary</th>
                                            <th>Status</th>
                                            @if(isset($modules['education_experience_update_permission']) && $modules['education_experience_update_permission'])
                                            <th class="text-center">Action</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($show->education_experience_details as $index => $edu)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td><strong>{{ $edu->degree ?? '--' }}</strong></td>
                                            <td>{{ $edu->institution_name ?? '--' }}</td>
                                            <td>{{ $edu->month_of_passing_year ?? '--' }}</td>
                                            <td>{{ $edu->class_or_mark ?? '--' }}</td>
                                            <td>{{ $edu->company_name ?? '--' }}</td>
                                            <td>{{ $edu->joining_date ?? '--' }}</td>
                                            <td>{{ $edu->left_date ?? '--' }}</td>
                                            <td>{{ $edu->designation ?? '--' }}</td>
                                            <td>{{ $edu->ctc_salary ? '₹' . number_format((int)$edu?->ctc_salary, 2) : '--' }}</td>
                                            <td>
                                                <span class="badge bg-{{ $edu->status == 'active' ? 'success' : 'danger' }}">
                                                    {{ ucfirst($edu->status) }}
                                                </span>
                                            </td>
                                            @if(isset($modules['education_experience_update_permission']) && $modules['education_experience_update_permission'])
                                            <td class="text-center">
                                                <a href="{{ route('employee-education-experience.edit', $edu->id) }}" class="emp-action-btn" title="Edit">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </a>
                                            </td>
                                            @endif
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-light border text-muted py-3">
                                <i class="ti ti-info-circle me-1"></i> No education or experience details found for this employee.
                            </div>
                        @endif
                    </div>

                    {{-- 7. SALARY DETAILS TAB --}}
                    <div class="tab-pane fade" id="tab-salary-details" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h4 class="emp-section-heading">Salary Details</h4>
                            <div class="d-flex align-items-center gap-2">
                                @if(isset($modules['salary_details_add_permission']) && $modules['salary_details_add_permission'])
                                <a href="{{ route('employee-wise-salary-details.create', ['employee_id' => $show->id]) }}" class="btn btn-sm btn-primary">
                                    <i class="fa-solid fa-plus me-1"></i> Add Salary Details
                                </a>
                                @endif
                            </div>
                        </div>
                        <div class="emp-sub-tabs">
                            <button type="button" class="emp-sub-tab-btn active">Salary Structure</button>
                        </div>

                        @if($show?->salary_details && $show->salary_details->count() > 0)
                            @foreach($show->salary_details as $index => $salary)
                            <div class="card mb-3 border">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                                    <h6 class="mb-0 fw-bold">Salary Record #{{ $index + 1 }}
                                        <span class="badge bg-{{ ($salary->status ?? '') == 'active' ? 'success' : 'danger' }} ms-2">
                                            {{ ucfirst($salary->status ?? 'inactive') }}
                                        </span>
                                    </h6>
                                    @if(isset($modules['salary_details_update_permission']) && $modules['salary_details_update_permission'])
                                    <a href="{{ route('employee-wise-salary-details.edit', $salary->id) }}" class="emp-action-btn" title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    @endif
                                </div>
                                <div class="card-body py-3">
                                    <div class="row">
                                        <div class="col-md-4 detail-field-group">
                                            <span class="field-label">Salary Classification</span>
                                            <span class="field-value">{{ $salary->salary_classification ?? '--' }}</span>
                                        </div>
                                        <div class="col-md-4 detail-field-group">
                                            <span class="field-label">Week Off</span>
                                            <span class="field-value">{{ $salary->week_off ?? '--' }}</span>
                                        </div>
                                        <div class="col-md-4 detail-field-group">
                                            <span class="field-label">Overtime</span>
                                            <span class="field-value">{{ $salary->overtime ?? '--' }}</span>
                                        </div>
                                        <div class="col-md-4 detail-field-group">
                                            <span class="field-label">Leave Eligibility</span>
                                            <span class="field-value">{{ $salary->leave_elegiblity ?? '--' }}</span>
                                        </div>
                                        <div class="col-md-4 detail-field-group">
                                            <span class="field-label">Basic DA</span>
                                            <span class="field-value">₹{{ number_format($salary->basic_da ?? 0, 2) }}</span>
                                        </div>
                                        <div class="col-md-4 detail-field-group">
                                            <span class="field-label">HRA</span>
                                            <span class="field-value">₹{{ number_format($salary->hra ?? 0, 2) }}</span>
                                        </div>
                                        <div class="col-md-4 detail-field-group">
                                            <span class="field-label">Conveyance Allowance</span>
                                            <span class="field-value">₹{{ number_format($salary->conveyance_allowance ?? 0, 2) }}</span>
                                        </div>
                                        <div class="col-md-4 detail-field-group">
                                            <span class="field-label">Medical Allowance</span>
                                            <span class="field-value">₹{{ number_format($salary->medical_allowance ?? 0, 2) }}</span>
                                        </div>
                                        <div class="col-md-4 detail-field-group">
                                            <span class="field-label">Special Allowance</span>
                                            <span class="field-value">₹{{ number_format($salary->special_allowance ?? 0, 2) }}</span>
                                        </div>
                                        <div class="col-md-4 detail-field-group">
                                            <span class="field-label">Total CTC</span>
                                            <span class="field-value text-success fw-bold">₹{{ number_format((int)$salary?->ctc ?? 0, 2) }}</span>
                                        </div>
                                        <div class="col-md-4 detail-field-group">
                                            <span class="field-label">PF Type & Amount</span>
                                            <span class="field-value">{{ $salary->pf_type ?? '--' }} (₹{{ number_format((int)$salary?->pf ?? 0, 2) }})</span>
                                        </div>
                                        <div class="col-md-4 detail-field-group">
                                            <span class="field-label">TDS</span>
                                            <span class="field-value">₹{{ number_format((int)$salary?->tds ?? 0, 2) }} ({{ $salary->tds_percentage ?? 0 }}%)</span>
                                        </div>
                                        <div class="col-md-4 detail-field-group">
                                            <span class="field-label">PT Amount</span>
                                            <span class="field-value">₹{{ number_format((int)$salary?->pt_amount ?? 0, 2) }}</span>
                                        </div>
                                        <div class="col-md-4 detail-field-group">
                                            <span class="field-label">Insurance Amount</span>
                                            <span class="field-value">₹{{ number_format((int)$salary?->insurance_amount ?? 0, 2) }}</span>
                                        </div>
                                        <div class="col-md-4 detail-field-group">
                                            <span class="field-label">ESI (Employee Side)</span>
                                            <span class="field-value">{{ $salary->esi_employee_side_percentage ?? 0 }}%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        @else
                            <div class="alert alert-light border text-muted py-3">
                                <i class="ti ti-info-circle me-1"></i> No salary details found for this employee.
                            </div>
                        @endif
                    </div>

                    {{-- 8. BANK DETAILS TAB --}}
                    <div class="tab-pane fade" id="tab-bank-details" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h4 class="emp-section-heading">Bank Details</h4>
                            <div class="d-flex align-items-center gap-2">
                                <a href="javascript:void(0);" onclick="location.reload();" class="emp-action-btn" title="Refresh"><i class="ti ti-refresh"></i></a>
                                @if(isset($modules['update_permission']) && $modules['update_permission'])
                                <a href="{{ route($route . '.edit', $show->id) }}" class="emp-action-btn" title="Edit Bank Details"><i class="fa-solid fa-pen-to-square"></i></a>
                                @endif
                            </div>
                        </div>
                        <div class="emp-sub-tabs">
                            <button type="button" class="emp-sub-tab-btn active">Account Information</button>
                        </div>

                        <div class="row">
                            <div class="col-md-4 detail-field-group">
                                <span class="field-label">Bank Name</span>
                                <span class="field-value">{{ $show?->bank_name ?? '--' }}</span>
                            </div>
                            <div class="col-md-4 detail-field-group">
                                <span class="field-label">Account Number</span>
                                <span class="field-value">{{ $show?->bank_account_number ?? '--' }}</span>
                            </div>
                            <div class="col-md-4 detail-field-group">
                                <span class="field-label">IFSC Code</span>
                                <span class="field-value">{{ $show?->ifsc_code ?? '--' }}</span>
                            </div>
                            @if($primaryEmp?->payment_mode)
                            <div class="col-md-4 detail-field-group">
                                <span class="field-label">Payment Mode</span>
                                <span class="field-value">{{ strtoupper($primaryEmp->payment_mode) }}</span>
                            </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@section('page_leavel_script')
@endsection

@push('page_scripts')
@if(isset($modules['currentGuard']) && $modules['currentGuard'] === 'admin_software')
<script>
    $(document).on('click', '.copy-login-details-btn', function() {
        var appKey = $(this).data('app-key');
        var username = $(this).data('username');
        var password = $(this).data('password');
        
        var loginDetails = "Login Details:\n" +
            "App Key: " + appKey + "\n" +
            "Username: " + username + "\n" +
            "Password: " + password;
        
        navigator.clipboard.writeText(loginDetails).then(function() {
            Swal.fire({
                icon: 'success',
                title: 'Copied!',
                text: 'Login details copied to clipboard. You can now share it!',
                timer: 2000,
                showConfirmButton: false
            });
        }).catch(function(err) {
            var textArea = document.createElement("textarea");
            textArea.value = loginDetails;
            textArea.style.position = "fixed";
            textArea.style.left = "-999999px";
            textArea.style.top = "-999999px";
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                document.execCommand('copy');
                Swal.fire({
                    icon: 'success',
                    title: 'Copied!',
                    text: 'Login details copied to clipboard. You can now share it!',
                    timer: 2000,
                    showConfirmButton: false
                });
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to copy. Please try again.',
                });
            }
            document.body.removeChild(textArea);
        });
    });
</script>
@endif
@endpush
