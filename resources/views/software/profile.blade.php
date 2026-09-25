@extends('software.layout.app')

@php
    $page_title = $modules['title'] ?? 'My Profile';
    $route = $modules['route'] ?? 'dashboard';
    $company_id = $modules['company_id'] ?? null;
    $authLoginUserDetail = $modules['authLoginUserDetail'] ?? null;
    $loginUserId = $authLoginUserDetail?->id ?? null;
    $currentGuard = $modules['currentGuard'] ?? null;

    $user = $user ?? $edit ?? $authLoginUserDetail;
    $userFullName = $userName ?? ($user?->proper_name ?? ($user?->full_name ?? ($user?->name ?? 'User')));
    $userRole = $roleName ?? ($user?->role?->name ?? ($user?->type ?? 'Employee'));
    $userEmail = $user?->email ?? '-';
    $userPhone = $user?->contact_number ?? ($user?->phone ?? '-');
    $userCode = $user?->employee_code ?? null;
    $userDept = $user?->employmentDetail?->department?->name ?? null;
    $userDesig = $user?->employmentDetail?->designation?->name ?? null;
    $userDoj = $user?->employmentDetail?->date_of_joining ? \Carbon\Carbon::parse($user->employmentDetail->date_of_joining)->format('d M Y') : null;

    $profileImg = (!empty($authLoginUserDetail?->profile_image) && file_exists(public_path($authLoginUserDetail->profile_image)))
        ? asset($authLoginUserDetail->profile_image)
        : asset('software/img/default/profile.png');
@endphp

@section('title', $userFullName . ' - Profile')

<style>
    /* Full-width OceanHR Profile Theme */
    .emp-profile-shell {
        background: #ffffff;
        border-radius: 14px;
        border: 1px solid #edf2f7;
        box-shadow: 0 4px 18px rgba(75, 70, 92, 0.05);
        margin-bottom: 24px;
        width: 100%;
    }

    /* Top Header Bar */
    .emp-top-header {
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        border-bottom: 1px solid #edf2f7;
        padding: 24px;
        border-top-left-radius: 14px;
        border-top-right-radius: 14px;
    }

    .emp-avatar-wrapper {
        position: relative;
        width: 84px;
        height: 84px;
        flex-shrink: 0;
    }

    .emp-avatar-img {
        width: 84px;
        height: 84px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #2563eb;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);
        background: #ffffff;
    }

    .emp-avatar-upload-badge {
        position: absolute;
        bottom: 0;
        right: 0;
        width: 28px;
        height: 28px;
        background: #2563eb;
        color: #ffffff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        border: 2px solid #ffffff;
        box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        transition: transform 0.2s ease, background 0.2s ease;
    }

    .emp-avatar-upload-badge:hover {
        transform: scale(1.1);
        background: #1d4ed8;
    }

    .emp-title-text {
        font-size: 1.35rem;
        font-weight: 750;
        color: #0f172a;
        letter-spacing: 0.2px;
    }

    .emp-code-badge {
        background-color: #eff6ff;
        color: #1d4ed8;
        font-weight: 700;
        font-size: 0.82rem;
        padding: 4px 10px;
        border-radius: 6px;
        border: 1px solid #bfdbfe;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    /* Split Tabs Layout */
    .emp-profile-body {
        display: flex;
        min-height: 520px;
    }

    .emp-sidebar-wrapper {
        width: 250px;
        min-width: 250px;
        max-width: 250px;
        flex-shrink: 0;
        border-right: 1px solid #edf2f7;
        background: #fafbfc;
        border-bottom-left-radius: 14px;
    }

    .emp-nav-menu {
        padding: 16px 10px;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .emp-nav-item {
        display: flex;
        align-items: center;
        padding: 11px 14px;
        border-radius: 8px;
        color: #475569;
        font-size: 0.88rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s ease;
        cursor: pointer;
        border: none;
        background: transparent;
        width: 100%;
        text-align: left;
    }

    .emp-nav-item i {
        font-size: 1.15rem;
        margin-right: 10px;
        color: #64748b;
        width: 20px;
        text-align: center;
        transition: color 0.2s ease;
    }

    .emp-nav-item:hover {
        background-color: #f1f5f9;
        color: #0f172a;
    }

    .emp-nav-item:hover i {
        color: #2563eb;
    }

    .emp-nav-item.active {
        background-color: #2563eb !important;
        color: #ffffff !important;
        font-weight: 700;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
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
        padding: 24px 30px;
    }

    .emp-section-heading {
        font-size: 1.15rem;
        font-weight: 750;
        color: #0f172a;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 8px;
        border-bottom: 1px solid #edf2f7;
        padding-bottom: 12px;
    }

    /* Field Display Box */
    .info-grid-box {
        background: #f8fafc;
        border: 1px solid #edf2f7;
        border-radius: 12px;
        padding: 14px 16px;
        transition: background 0.15s ease, border-color 0.15s ease;
    }

    .info-grid-box:hover {
        background: #f1f5f9;
        border-color: #e2e8f0;
    }

    .info-grid-label {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
        margin-bottom: 3px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .info-grid-value {
        font-size: 0.95rem;
        font-weight: 700;
        color: #0f172a;
        word-break: break-word;
    }

    /* Photo Upload Box */
    .photo-upload-card {
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
        border: 1px dashed #6ee7b7;
        border-radius: 12px;
        padding: 16px 20px;
        margin-bottom: 24px;
    }

    /* Form Inputs */
    .form-control-ocean {
        border-radius: 8px;
        border: 1.5px solid #e2e8f0;
        padding: 10px 14px;
        font-size: 0.92rem;
        transition: all 0.2s ease;
    }

    .form-control-ocean:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    }

    /* Responsive */
    @media (max-width: 991.98px) {
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
        .emp-content-wrapper {
            padding: 20px 16px;
        }
    }
</style>

@section('content')
<div class="emp-profile-shell">

    {{-- Top Header Bar --}}
    <div class="emp-top-header d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            {{-- Avatar with Upload Badge --}}
            <div class="emp-avatar-wrapper">
                <img src="{{ $profileImg }}" alt="{{ $userFullName }}" class="emp-avatar-img" id="uploadedAvatar">
                <label for="upload" class="emp-avatar-upload-badge" title="Change Profile Picture">
                    <i class="ti ti-camera" style="font-size: 14px;"></i>
                </label>
            </div>

            <div>
                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                    <span class="emp-title-text">{{ strtoupper($userFullName) }}</span>
                    @if ($userCode)
                        <span class="emp-code-badge">
                            <i class="ti ti-id-badge"></i> {{ $userCode }}
                        </span>
                    @endif
                    <span class="badge bg-success ms-1">
                        <i class="ti ti-circle-check me-1"></i> Active
                    </span>
                </div>

                <div class="small text-muted d-flex align-items-center gap-3 flex-wrap">
                    <span><i class="ti ti-briefcase text-primary me-1"></i>{{ $userRole }}</span>
                    @if ($userDept)
                        <span><i class="ti ti-building text-info me-1"></i>{{ $userDept }}</span>
                    @endif
                    @if ($userEmail && $userEmail !== '-')
                        <span><i class="ti ti-mail text-secondary me-1"></i>{{ $userEmail }}</span>
                    @endif
                    @if (!empty($company?->company_name))
                        <span><i class="ti ti-building-skyscraper text-warning me-1"></i>{{ $company->company_name }}</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Hidden Form for Photo Upload --}}
        <div>
            <form action="{{ route($route . '.updateProfileImage', [$edit?->id]) }}" method="POST" enctype="multipart/form-data" id="profileImageForm" class="d-flex align-items-center gap-2 m-0">
                @csrf
                <input type="file" id="upload" name="profile_image" class="d-none" accept="image/png, image/jpeg, image/webp">
                <button type="submit" id="btnSavePhoto" class="btn btn-sm btn-primary px-3 shadow-sm" style="display: none;">
                    <i class="ti ti-device-floppy me-1"></i> Save Photo
                </button>
                <button type="button" id="btnCancelPhoto" class="btn btn-sm btn-outline-secondary" style="display: none;">
                    Cancel
                </button>
            </form>
        </div>
    </div>

    {{-- Photo Selection Alert / Indicator (Visible only when file chosen) --}}
    <div id="photoChosenAlert" class="alert alert-primary d-flex align-items-center justify-content-between m-3 py-2 px-3" style="display: none !important;">
        <div class="d-flex align-items-center gap-2 small">
            <i class="ti ti-photo-check fs-5"></i>
            <span>New photo selected: <strong id="chosenPhotoName">filename.jpg</strong>. Click <strong>"Save Photo"</strong> above to apply changes.</span>
        </div>
        <button type="button" class="btn btn-sm btn-primary py-1 px-3" onclick="$('#profileImageForm').submit();">
            Save Photo
        </button>
    </div>

    {{-- Split Layout Body --}}
    <div class="emp-profile-body">

        {{-- Left Navigation Tabs --}}
        <div class="emp-sidebar-wrapper">
            <div class="emp-nav-menu" role="tablist">
                <button type="button" class="emp-nav-item active" data-bs-toggle="tab" data-bs-target="#tab-profile-info">
                    <i class="ti ti-user-circle"></i> Profile Details
                </button>
                <button type="button" class="emp-nav-item" data-bs-toggle="tab" data-bs-target="#tab-account-settings">
                    <i class="ti ti-shield-lock"></i> Account & Password
                </button>
                <button type="button" class="emp-nav-item" data-bs-toggle="tab" data-bs-target="#tab-company-info">
                    <i class="ti ti-building-skyscraper"></i> Company Workspace
                </button>
            </div>
        </div>

        {{-- Right Tab Content Wrapper --}}
        <div class="emp-content-wrapper">
            <div class="tab-content p-0 m-0">

                {{-- Tab 1: Profile & Employment Information --}}
                <div class="tab-pane fade show active" id="tab-profile-info">
                    <div class="emp-section-heading">
                        <i class="ti ti-user text-primary"></i>
                        <span>Personal & Employment Information</span>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="info-grid-box">
                                <div class="info-grid-label"><i class="ti ti-id"></i> Full Name</div>
                                <div class="info-grid-value">{{ $userFullName }}</div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="info-grid-box">
                                <div class="info-grid-label"><i class="ti ti-at"></i> Username</div>
                                <div class="info-grid-value">{{ $user?->username ?? '-' }}</div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="info-grid-box">
                                <div class="info-grid-label"><i class="ti ti-mail"></i> Official Email</div>
                                <div class="info-grid-value">{{ $userEmail }}</div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="info-grid-box">
                                <div class="info-grid-label"><i class="ti ti-phone"></i> Contact Number</div>
                                <div class="info-grid-value">{{ $userPhone }}</div>
                            </div>
                        </div>

                        @if ($currentGuard == 'employees')
                            <div class="col-12 col-md-6">
                                <div class="info-grid-box">
                                    <div class="info-grid-label"><i class="ti ti-barcode"></i> Employee Code</div>
                                    <div class="info-grid-value">{{ $userCode ?? '-' }}</div>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="info-grid-box">
                                    <div class="info-grid-label"><i class="ti ti-building"></i> Department</div>
                                    <div class="info-grid-value">{{ $userDept ?? '-' }}</div>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="info-grid-box">
                                    <div class="info-grid-label"><i class="ti ti-award"></i> Designation / Role</div>
                                    <div class="info-grid-value">{{ $userDesig ?? $userRole }}</div>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="info-grid-box">
                                    <div class="info-grid-label"><i class="ti ti-calendar"></i> Date of Joining</div>
                                    <div class="info-grid-value">{{ $userDoj ?? '-' }}</div>
                                </div>
                            </div>
                        @else
                            <div class="col-12 col-md-6">
                                <div class="info-grid-box">
                                    <div class="info-grid-label"><i class="ti ti-shield"></i> User Type</div>
                                    <div class="info-grid-value">{{ $userRole }}</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Tab 2: Account Settings & Password Update --}}
                <div class="tab-pane fade" id="tab-account-settings">
                    <div class="emp-section-heading">
                        <i class="ti ti-shield-lock text-primary"></i>
                        <span>Account Security & Password</span>
                    </div>

                    <form id="formAccountSettings" action="{{ route($route . '.updateProfile', [$edit?->id]) }}" method="POST">
                        @csrf

                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold text-dark">Profile Name</label>
                                <input class="form-control form-control-ocean bg-light" type="text" value="{{ $userFullName }}" readonly>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold text-dark">Username / Role</label>
                                <input class="form-control form-control-ocean bg-light" type="text" value="{{ ($user?->username ?? '') . ' (' . $userRole . ')' }}" readonly>
                            </div>
                        </div>

                        <div class="emp-section-heading mt-4" style="font-size: 1rem;">
                            <i class="ti ti-key text-warning"></i>
                            <span>Change Password</span>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold" for="old_password">Current Password <span class="text-danger">*</span></label>
                                <div class="input-group input-group-merge">
                                    <input class="form-control form-control-ocean" type="password" id="old_password" name="old_password" placeholder="Current Password" required>
                                    <span class="input-group-text cursor-pointer" onclick="togglePassword('old_password')">
                                        <i class="ti ti-eye-off" id="togglePasswordIcon_old_password"></i>
                                    </span>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold" for="new_password">New Password <span class="text-danger">*</span></label>
                                <div class="input-group input-group-merge">
                                    <input class="form-control form-control-ocean" type="password" id="new_password" name="new_password" placeholder="New Password" required>
                                    <span class="input-group-text cursor-pointer" onclick="togglePassword('new_password')">
                                        <i class="ti ti-eye-off" id="togglePasswordIcon_new_password"></i>
                                    </span>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold" for="new_password_confirmation">Confirm New Password <span class="text-danger">*</span></label>
                                <div class="input-group input-group-merge">
                                    <input class="form-control form-control-ocean" type="password" id="new_password_confirmation" name="new_password_confirmation" placeholder="Confirm Password" required>
                                    <span class="input-group-text cursor-pointer" onclick="togglePassword('new_password_confirmation')">
                                        <i class="ti ti-eye-off" id="togglePasswordIcon_new_password_confirmation"></i>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-light border d-flex align-items-center gap-2 mt-3 mb-4 py-2 px-3 small text-muted">
                            <i class="ti ti-info-circle text-primary fs-5"></i>
                            <span>Password must be minimum 8 characters with at least one uppercase letter, one lowercase letter, one number, and one special character.</span>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <button type="submit" class="btn btn-primary px-4 py-2 shadow-sm">
                                <i class="ti ti-device-floppy me-1"></i> Save Changes
                            </button>
                            <button type="reset" class="btn btn-outline-secondary px-3 py-2">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Tab 3: Company Workspace Details --}}
                <div class="tab-pane fade" id="tab-company-info">
                    <div class="emp-section-heading">
                        <i class="ti ti-building-skyscraper text-primary"></i>
                        <span>Organization & Workspace Details</span>
                    </div>

                    @if ($company)
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <div class="info-grid-box">
                                    <div class="info-grid-label"><i class="ti ti-building"></i> Company Name</div>
                                    <div class="info-grid-value">{{ $company->company_name }}</div>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="info-grid-box">
                                    <div class="info-grid-label"><i class="ti ti-mail"></i> Support Email</div>
                                    <div class="info-grid-value">{{ $company->email ?? '-' }}</div>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="info-grid-box">
                                    <div class="info-grid-label"><i class="ti ti-brand-whatsapp text-success"></i> WhatsApp Support</div>
                                    <div class="info-grid-value">
                                        @if (!empty($company->whatsapp_number))
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $company->whatsapp_number) }}" target="_blank" class="text-success text-decoration-none">
                                                {{ $company->whatsapp_number }} <i class="ti ti-external-link font-size-xs"></i>
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="info-grid-box">
                                    <div class="info-grid-label"><i class="ti ti-key"></i> Organization App Key</div>
                                    <div class="info-grid-value font-monospace">{{ $company->app_key ?? '-' }}</div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">No company details associated.</div>
                    @endif
                </div>

            </div>
        </div>

    </div>

</div>
@endsection

@push('page_scripts')
    <script>
        function togglePassword(inputId) {
            const passwordInput = document.getElementById(inputId);
            const icon = document.getElementById('togglePasswordIcon_' + inputId);
            if (!passwordInput || !icon) return;
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            icon.classList.toggle('ti-eye-off', !isPassword);
            icon.classList.toggle('ti-eye', isPassword);
        }

        // Live Image Preview & Save Handling
        const originalAvatarSrc = $('#uploadedAvatar').attr('src');

        $(document).on('change', '#upload', function(event) {
            const input = event.target;
            if (input.files && input.files[0]) {
                const file = input.files[0];

                if (file.size > 2 * 1024 * 1024) {
                    if (typeof toastr !== 'undefined') {
                        toastr.error('File size exceeds 2MB limit.', 'Image Too Large');
                    } else {
                        alert('File size exceeds 2MB limit.');
                    }
                    $(this).val('');
                    return;
                }

                $('#chosenPhotoName').text(file.name);
                $('#photoChosenAlert').attr('style', 'display: flex !important;');
                $('#btnSavePhoto, #btnCancelPhoto').fadeIn(200);

                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#uploadedAvatar').attr('src', e.target.result);
                }
                reader.readAsDataURL(file);
            }
        });

        // Cancel Photo Selection
        $(document).on('click', '#btnCancelPhoto', function() {
            $('#upload').val('');
            $('#uploadedAvatar').attr('src', originalAvatarSrc);
            $('#photoChosenAlert').attr('style', 'display: none !important;');
            $('#btnSavePhoto, #btnCancelPhoto').fadeOut(200);
        });
    </script>
@endpush
