@extends('software.layout.app')

@php
    $i = 0;
    $page_title = isset($modules['title']) ? $modules['title'] : null;
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $form_route = isset($modules['form_route']) ? $modules['form_route'] : null;
    $company_id = isset($modules['company_id']) ? $modules['company_id'] : null;
    $authLoginUserDetail = isset($modules['authLoginUserDetail']) ? $modules['authLoginUserDetail'] : null;
    $loginUserId = isset($authLoginUserDetail?->id) ? $authLoginUserDetail?->id : null;
    $parent_type_id = isset($modules['parent_type_id']) ? $modules['parent_type_id'] : null;

    $maring_bottom = 'mb-3';
@endphp

@section('title', $page_title)


@section('page_leavel_style')
    <link rel="stylesheet" href="{{ asset('software/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('software/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.css" />

    <style>
        .select2-container {
            display: block !important;
        }

        .bootstrap-tagsinput {
            width: 100%;
            min-height: 40px;
            padding: 6px 10px;
            line-height: 22px;
            border: 1px solid #ccc;
            border-radius: 0.25rem;
            background: #fff;
        }

        .bootstrap-tagsinput .tag {
            margin-right: 2px;
            color: white;
            background-color: #29c3c0;
            padding: 4px 8px;
            border-radius: 4px;
            display: inline-block;
        }

        /* --- Modern Company Detail Redesign --- */
        .company-pill-nav {
            background: #ffffff;
            padding: 6px;
            border-radius: 14px;
            display: inline-flex;
            gap: 6px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
            margin-bottom: 22px;
        }

        .company-pill-nav .nav-link {
            border-radius: 10px;
            padding: 9px 20px;
            font-size: 0.88rem;
            font-weight: 600;
            color: #64748b;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .company-pill-nav .nav-link:hover {
            color: #1877f2;
            background: rgba(24, 119, 242, 0.08);
        }

        .company-pill-nav .nav-link.active {
            background: linear-gradient(135deg, #1565d8 0%, #1e70eb 45%, #2979ff 100%) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px rgba(24, 119, 242, 0.4) !important;
        }

        .comp-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
            margin-bottom: 24px;
            overflow: hidden;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .comp-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.04);
        }

        .comp-card-header {
            padding: 16px 22px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            border-bottom: 1px solid #edf2f7;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .comp-card-title {
            margin: 0;
            font-size: 0.98rem;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .comp-card-title i {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 9px;
            background: rgba(24, 119, 242, 0.1);
            color: #1877f2;
            font-size: 1.15rem;
        }

        .comp-card-subtitle {
            font-size: 0.78rem;
            color: #94a3b8;
            margin: 2px 0 0 44px;
        }

        .comp-card-body {
            padding: 22px;
        }

        .comp-label {
            font-size: 0.78rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #475569;
            margin-bottom: 7px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .comp-input {
            border-radius: 10px;
            border: 1.5px solid #e2e8f0;
            padding: 9px 14px;
            font-size: 0.92rem;
            color: #1e293b;
            background-color: #ffffff;
            transition: all 0.2s ease;
        }

        .comp-input:focus {
            border-color: #1877f2;
            box-shadow: 0 0 0 3px rgba(24, 119, 242, 0.15);
        }

        /* Dropzone Upload Styling */
        .comp-upload-card {
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            padding: 16px;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: all 0.2s ease;
        }

        .comp-upload-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 4px 14px rgba(0,0,0,0.03);
        }

        .comp-upload-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .comp-upload-title {
            font-size: 0.84rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }

        .comp-upload-dropzone {
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            background: #f8fafc;
            padding: 22px 14px;
            text-align: center;
            position: relative;
            cursor: pointer;
            transition: all 0.25s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 130px;
        }

        .comp-upload-dropzone:hover {
            border-color: #1877f2;
            background: #f4f8ff;
        }

        .comp-upload-dropzone input[type="file"] {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
            z-index: 5;
        }

        .comp-upload-icon {
            font-size: 1.8rem;
            color: #1877f2;
            margin-bottom: 6px;
            transition: transform 0.2s ease;
        }

        .comp-upload-dropzone:hover .comp-upload-icon {
            transform: translateY(-2px);
        }

        .comp-upload-text {
            font-size: 0.82rem;
            color: #64748b;
            margin: 0;
            font-weight: 500;
        }

        .comp-upload-hint {
            font-size: 0.72rem;
            color: #94a3b8;
            margin-top: 2px;
        }

        .comp-preview-box {
            margin-top: 12px;
            padding: 10px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            text-align: center;
            min-height: 75px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .comp-preview-img {
            max-height: 70px;
            max-width: 100%;
            object-fit: contain;
            border-radius: 6px;
        }

        /* Sticky bottom save bar */
        .comp-save-bar {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 14px 20px;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04);
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 10px;
            margin-bottom: 24px;
        }

        .btn-submit, #submit {
            background: linear-gradient(135deg, #1565d8 0%, #1e70eb 45%, #2979ff 100%) !important;
            border: none !important;
            box-shadow: 0 4px 14px rgba(24, 119, 242, 0.35) !important;
        }

        .btn-submit:hover, #submit:hover {
            background: linear-gradient(135deg, #1055bc 0%, #175fd0 45%, #1c68e8 100%) !important;
            box-shadow: 0 6px 18px rgba(24, 119, 242, 0.45) !important;
        }

        .btn-outline-primary {
            border-color: #1877f2 !important;
            color: #1877f2 !important;
        }

        .btn-outline-primary:hover {
            background: #1877f2 !important;
            color: #ffffff !important;
        }
    </style>

@endsection

@section('content')
    <div class="d-flex justify-content-lg-between px-1">
        @php
            $tab = request('tab');
            $tabTitles = [
                'profile-tab' => 'Company',
                'integration-tab' => 'Third Party Integration',
                'mail-tab' => 'Mail Configuration',
                'subscription-tab' => 'Subscription History',
            ];

            $authUser = Auth::guard('employees')->user();
            $authRole = strtolower(trim($authUser?->teamRole?->name ?? ''));
            $authDesig = strtolower(trim($authUser?->designation?->name ?? ''));

            $isPrivilegedUser = Auth::guard('admin_software')->check() || (
                $authUser && (
                    $authUser->company_id == 1 ||
                    (int)$authUser->parent_id === 0 ||
                    str_contains($authRole, 'hr') ||
                    str_contains($authRole, 'department head') ||
                    str_contains($authRole, 'dept head') ||
                    str_contains($authRole, 'hod') ||
                    str_contains($authRole, 'supervisor') ||
                    str_contains($authDesig, 'hr') ||
                    str_contains($authDesig, 'department head') ||
                    str_contains($authDesig, 'hod') ||
                    str_contains($authDesig, 'supervisor')
                )
            );

            $isAdminUser = isset($isCompanyAdmin) ? $isCompanyAdmin : $isPrivilegedUser;
        @endphp
    </div>

    <!-- Navigation Tabs -->
    <div class="row my-2">
        <div class="col-md-12">
            <div class="company-pill-nav">
                <a class="nav-link {{ request('tab') === 'profile-tab' || !request('tab') ? 'active' : '' }}" href="{{ route($route . '.detail', [$edit?->id]) . '?tab=profile-tab' }}">
                    <i class="ti ti-building"></i> Company Profile
                </a>
                @if($isAdminUser)
                    <a class="nav-link {{ request('tab') === 'subscription-tab' ? 'active' : '' }}" href="{{ route($route . '.detail', [$edit?->id]) . '?tab=subscription-tab' }}">
                        <i class="ti ti-history"></i> Subscription History
                    </a>
                @endif
            </div>
        </div>
    </div>

    @if(request('tab') != 'subscription-tab')
        <div class="col-12">
            <div class="tab-content p-0">
                @if(request('tab') === 'profile-tab' || !request('tab'))
                    <div class="tab-pane fade show active" id="profile-tab">
                        <form id="CompanyDetailsForm" action="{{ route($route . '.update', [$edit?->id]) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @isset($edit)
                                @method('PUT')
                            @endisset

                            <input type="hidden" id="email" name="email" value="{{ $edit->email }}" />
                            <input type="hidden" id="country_id" name="country_id" value="{{ $edit->country_id }}" />
                            <input type="hidden" name="team_set" value="team_update" />
                            <input type="hidden" name="tab" value="profile-tab" />

                            <!-- 1. Organization & Contact Details -->
                            <div class="comp-card">
                                <div class="comp-card-header d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="comp-card-title"><i class="ti ti-building-skyscraper"></i> Organization & Contact Details</h5>
                                        <p class="comp-card-subtitle">Primary company details, contact person, and identification numbers</p>
                                    </div>
                                    @if(!$isAdminUser)
                                        <span class="badge bg-label-info px-3 py-2 rounded-pill">
                                            <i class="ti ti-eye me-1"></i> Read-Only View
                                        </span>
                                    @endif
                                </div>
                                <div class="comp-card-body">
                                    <div class="row g-3">
                                        <div class="col-md-4 col-sm-12">
                                            <label class="comp-label" for="company_name"><i class="ti ti-building text-primary"></i> Company Name</label>
                                            <input type="text" id="company_name" name="company_name" class="form-control comp-input @error('company_name') is-invalid @enderror" value="{{ old('company_name', $edit->company_name) }}" placeholder="Company Name" {{ !$isAdminUser ? 'readonly' : '' }} />
                                            @error('company_name')
                                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                            @enderror
                                        </div>

                                        <div class="col-md-4 col-sm-12">
                                            <label class="comp-label" for="person_name"><i class="ti ti-user text-primary"></i> Person Name @if($isAdminUser)<span class="text-danger">*</span>@endif</label>
                                            <input type="text" id="person_name" name="person_name" class="form-control comp-input @error('person_name') is-invalid @enderror" value="{{ old('person_name', $edit->person_name) }}" placeholder="Person Name" {{ !$isAdminUser ? 'readonly' : '' }} />
                                            @error('person_name')
                                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                            @enderror
                                        </div>

                                        <div class="col-md-4 col-sm-12">
                                            <label class="comp-label" for="whatsapp_number"><i class="ti ti-phone text-primary"></i> Mobile Number @if($isAdminUser)<span class="text-danger">*</span>@endif</label>
                                            <input type="text" id="whatsapp_number" name="whatsapp_number" class="form-control comp-input @error('whatsapp_number') is-invalid @enderror" value="{{ old('whatsapp_number', $edit->whatsapp_number) }}" maxlength="10" placeholder="Mobile Number" {{ !$isAdminUser ? 'readonly' : '' }} />
                                            @error('whatsapp_number')
                                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                            @enderror
                                        </div>

                                        <div class="col-md-4 col-sm-12">
                                            <label class="comp-label" for="gst_no"><i class="ti ti-receipt-tax text-primary"></i> GST No</label>
                                            <input type="text" id="gst_no" name="gst_no" class="form-control comp-input @error('gst_no') is-invalid @enderror" value="{{ old('gst_no', $edit->gst_no) }}" placeholder="GST No" style="text-transform: uppercase;" {{ !$isAdminUser ? 'readonly' : '' }} />
                                            @error('gst_no')
                                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                            @enderror
                                        </div>

                                        <div class="col-md-4 col-sm-12">
                                            <label class="comp-label" for="pan_card"><i class="ti ti-credit-card text-primary"></i> PAN Card Number</label>
                                            <input type="text" id="pan_card" name="pan_card" class="form-control comp-input @error('pan_card') is-invalid @enderror" value="{{ old('pan_card', $edit->pan_card) }}" maxlength="10" placeholder="ABCDE1234F" style="text-transform: uppercase;" pattern="[A-Z]{5}[0-9]{4}[A-Z]{1}" title="Please enter exactly 10 characters PAN card (e.g. ABCDE1234F)" oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 10);" {{ !$isAdminUser ? 'readonly' : '' }} />
                                            @error('pan_card')
                                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                            @enderror
                                        </div>

                                        <div class="col-md-4 col-sm-12">
                                            <label class="comp-label" for="app_key"><i class="ti ti-key text-primary"></i> Organization App Key</label>
                                            <div class="input-group">
                                                <input type="text" id="app_key" name="app_key" class="form-control comp-input font-monospace @error('app_key') is-invalid @enderror" value="{{ old('app_key', $edit->app_key) }}" placeholder="App Key" {{ !$isAdminUser ? 'readonly' : '' }} />
                                                <button class="btn btn-outline-primary d-flex align-items-center justify-content-center px-3" type="button" onclick="const k=document.getElementById('app_key').value; if(navigator.clipboard){navigator.clipboard.writeText(k);}else{const t=document.createElement('textarea');t.value=k;document.body.appendChild(t);t.select();document.execCommand('copy');document.body.removeChild(t);} if(typeof toastr !== 'undefined'){ toastr.success('App Key copied!'); } else { alert('App Key copied!'); }" title="Copy App Key">
                                                    <i class="ti ti-copy"></i>
                                                </button>
                                            </div>
                                            @error('app_key')
                                                <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if($isAdminUser)
                            <!-- 2. Address & Financial Details -->
                            <div class="comp-card">
                                <div class="comp-card-header">
                                    <div>
                                        <h5 class="comp-card-title"><i class="ti ti-building-bank"></i> Address & Financial Details</h5>
                                        <p class="comp-card-subtitle">Official banking information and registered company address</p>
                                    </div>
                                </div>
                                <div class="comp-card-body">
                                    <div class="row g-4">
                                        <div class="col-md-6 col-sm-12">
                                            <label class="comp-label mb-2" for="bank_details"><i class="ti ti-building-bank text-primary"></i> Bank Details</label>
                                            <textarea name="bank_details" id="bank_details" class="ckeditor_common_cls form-control @error('bank_details') is-invalid @enderror" placeholder="Bank Details">{{ $edit->bank_details }}</textarea>
                                            @error('bank_details')
                                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                            @enderror
                                        </div>
                                        <div class="col-md-6 col-sm-12">
                                            <label class="comp-label mb-2" for="address"><i class="ti ti-map-pin text-primary"></i> Registered Address</label>
                                            <textarea name="address" id="address" class="ckeditor_common_cls form-control @error('address') is-invalid @enderror" placeholder="Address">{{ $edit->address }}</textarea>
                                            @error('address')
                                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 3. Branding & Media Assets -->
                            <div class="comp-card">
                                <div class="comp-card-header">
                                    <div>
                                        <h5 class="comp-card-title"><i class="ti ti-photo"></i> Branding & Media Assets</h5>
                                        <p class="comp-card-subtitle">Upload company logo, white-labeling assets, app logo, and favicon</p>
                                    </div>
                                </div>
                                <div class="comp-card-body">
                                    <div class="row g-4">
                                        <!-- Company Logo -->
                                        <div class="col-lg-4 col-md-6 col-sm-12">
                                            <div class="comp-upload-card">
                                                <div class="comp-upload-header">
                                                    <h6 class="comp-upload-title">Company Logo</h6>
                                                    <span class="badge bg-label-info">Min 400 × 300</span>
                                                </div>
                                                <div class="comp-upload-dropzone">
                                                    <input type="file" id="company_logo" name="company_logo" onchange="previewImage(event, 'companyLogoPreview')" accept="image/*">
                                                    <i class="ti ti-photo-up comp-upload-icon"></i>
                                                    <p class="comp-upload-text">Click or Drop Image</p>
                                                    <span class="comp-upload-hint">PNG, JPG, WebP supported</span>
                                                </div>
                                                <div class="comp-preview-box">
                                                    <img id="companyLogoPreview" src="{{ $edit->company_logo && $edit->company_logo_url ? $edit->company_logo_url : '#' }}" alt="Company Logo" class="comp-preview-img" style="display: {{ $edit->company_logo ? 'inline-block' : 'none' }};" onerror="this.onerror=null; this.src=''; this.style.display='none';">
                                                    <span class="text-muted small" style="display: {{ $edit->company_logo ? 'none' : 'inline' }};">No logo uploaded yet</span>
                                                    <input type="hidden" name="company_logo_old" value="{{ $edit->company_logo }}">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- White Labeling Logo -->
                                        <div class="col-lg-4 col-md-6 col-sm-12">
                                            <div class="comp-upload-card">
                                                <div class="comp-upload-header">
                                                    <h6 class="comp-upload-title">White Labeling Logo</h6>
                                                    <span class="badge bg-label-primary">White Label</span>
                                                </div>
                                                <div class="comp-upload-dropzone">
                                                    <input type="file" id="white_labeling_logo" name="white_labeling_logo" onchange="previewImage(event, 'whiteLabelingLogoPreview')" accept="image/*">
                                                    <i class="ti ti-palette comp-upload-icon"></i>
                                                    <p class="comp-upload-text">Click or Drop Image</p>
                                                    <span class="comp-upload-hint">Brand white-label logo/banner</span>
                                                </div>
                                                <div class="comp-preview-box">
                                                    <img id="whiteLabelingLogoPreview" src="{{ $edit->white_labeling_logo && $edit->white_labeling_logo_url ? $edit->white_labeling_logo_url : '#' }}" alt="White Label Logo" class="comp-preview-img" style="display: {{ $edit->white_labeling_logo ? 'inline-block' : 'none' }};" onerror="this.onerror=null; this.src=''; this.style.display='none';">
                                                    <span class="text-muted small" style="display: {{ $edit->white_labeling_logo ? 'none' : 'inline' }};">No logo uploaded yet</span>
                                                    <input type="hidden" name="white_labeling_logo_old" value="{{ $edit->white_labeling_logo }}">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Favicon Icon -->
                                        <div class="col-lg-4 col-md-6 col-sm-12">
                                            <div class="comp-upload-card">
                                                <div class="comp-upload-header">
                                                    <h6 class="comp-upload-title">Favicon Icon</h6>
                                                    <span class="badge bg-label-info">Min 32 × 32</span>
                                                </div>
                                                <div class="comp-upload-dropzone">
                                                    <input type="file" id="company_favicon" name="company_favicon" onchange="previewImage(event, 'CompanyFaviconPreview')" accept="image/*">
                                                    <i class="ti ti-world comp-upload-icon"></i>
                                                    <p class="comp-upload-text">Click or Drop Favicon</p>
                                                    <span class="comp-upload-hint">Square icon for browser tab</span>
                                                </div>
                                                <div class="comp-preview-box">
                                                    <img id="CompanyFaviconPreview" src="{{ $edit->company_favicon && $edit->company_favicon_url ? $edit->company_favicon_url : '#' }}" alt="Favicon" class="comp-preview-img" style="max-height: 36px; display: {{ $edit->company_favicon ? 'inline-block' : 'none' }};" onerror="this.onerror=null; this.src=''; this.style.display='none';">
                                                    <span class="text-muted small" style="display: {{ $edit->company_favicon ? 'none' : 'inline' }};">No favicon uploaded yet</span>
                                                    <input type="hidden" name="company_favicon_old" value="{{ $edit->company_favicon }}">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Watermark Logo -->
                                        <div class="col-lg-6 col-md-6 col-sm-12">
                                            <div class="comp-upload-card">
                                                <div class="comp-upload-header">
                                                    <h6 class="comp-upload-title">Watermark Logo</h6>
                                                    <span class="badge bg-label-secondary">Doc Watermark</span>
                                                </div>
                                                <div class="comp-upload-dropzone">
                                                    <input type="file" id="watermark_logo" name="watermark_logo" onchange="previewImage(event, 'WatermarkLogoPreview')" accept="image/*">
                                                    <i class="ti ti-stamp comp-upload-icon"></i>
                                                    <p class="comp-upload-text">Click or Drop Watermark</p>
                                                    <span class="comp-upload-hint">Used as background watermark on employee documents</span>
                                                </div>
                                                <div class="comp-preview-box">
                                                    <img id="WatermarkLogoPreview" src="{{ $edit->watermark_logo && $edit->watermark_logo_url ? $edit->watermark_logo_url : '#' }}" alt="Watermark Logo" class="comp-preview-img" style="display: {{ $edit->watermark_logo ? 'inline-block' : 'none' }};" onerror="this.onerror=null; this.src=''; this.style.display='none';">
                                                    <span class="text-muted small" style="display: {{ $edit->watermark_logo ? 'none' : 'inline' }};">No watermark uploaded yet</span>
                                                    <input type="hidden" name="watermark_logo_old" value="{{ $edit->watermark_logo }}">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- App Logo -->
                                        <div class="col-lg-6 col-md-6 col-sm-12">
                                            <div class="comp-upload-card">
                                                <div class="comp-upload-header">
                                                    <h6 class="comp-upload-title">Mobile App Logo</h6>
                                                    <span class="badge bg-label-info">Min 512 × 512</span>
                                                </div>
                                                <div class="comp-upload-dropzone">
                                                    <input type="file" id="app_logo" name="app_logo" onchange="previewImage(event, 'appLogoPreview')" accept="image/*">
                                                    <i class="ti ti-device-mobile comp-upload-icon"></i>
                                                    <p class="comp-upload-text">Click or Drop App Logo</p>
                                                    <span class="comp-upload-hint">Square HD logo for mobile applications</span>
                                                </div>
                                                <div class="comp-preview-box">
                                                    <img id="appLogoPreview" src="{{ $edit->app_logo && $edit->app_logo_url ? $edit->app_logo : '#' }}" alt="App Logo" class="comp-preview-img" style="display: {{ $edit->app_logo ? 'inline-block' : 'none' }};" onerror="this.onerror=null; this.src=''; this.style.display='none';">
                                                    <span class="text-muted small" style="display: {{ $edit->app_logo ? 'none' : 'inline' }};">No app logo uploaded yet</span>
                                                    <input type="hidden" name="app_logo_old" value="{{ $edit->app_logo }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 4. Print Headers, Footers & Policies -->
                            <div class="comp-card">
                                <div class="comp-card-header">
                                    <div>
                                        <h5 class="comp-card-title"><i class="ti ti-file-certificate"></i> Print Headers, Footers & Policies</h5>
                                        <p class="comp-card-subtitle">Order, dispatch & quotation print headers and employee policy handbook</p>
                                    </div>
                                </div>
                                <div class="comp-card-body">
                                    <div class="row g-4">
                                        <!-- Header Image -->
                                        <div class="col-lg-4 col-md-6 col-sm-12">
                                            <div class="comp-upload-card">
                                                <div class="comp-upload-header">
                                                    <h6 class="comp-upload-title">Order / Quotation Header</h6>
                                                    <span class="badge bg-label-info">800 × 120</span>
                                                </div>
                                                <div class="comp-upload-dropzone">
                                                    <input type="file" id="header_image" name="header_image" onchange="previewImage(event, 'headerimagePreview')" accept="image/*">
                                                    <i class="ti ti-layout-navbar comp-upload-icon"></i>
                                                    <p class="comp-upload-text">Click or Drop Header</p>
                                                    <span class="comp-upload-hint">Order / Quotation / Dispatch Header</span>
                                                </div>
                                                <div class="comp-preview-box">
                                                    <img id="headerimagePreview" src="{{ $edit->order_header_logo && $edit->order_header_logo_url ? $edit->order_header_logo_url : '#' }}" alt="Header Preview" class="comp-preview-img" style="display: {{ $edit->order_header_logo ? 'inline-block' : 'none' }};" onerror="this.onerror=null; this.src=''; this.style.display='none';">
                                                    <span class="text-muted small" style="display: {{ $edit->order_header_logo ? 'none' : 'inline' }};">No header uploaded yet</span>
                                                    <input type="hidden" name="header_image_old" value="{{ $edit->order_header_logo }}">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Footer Image -->
                                        <div class="col-lg-4 col-md-6 col-sm-12">
                                            <div class="comp-upload-card">
                                                <div class="comp-upload-header">
                                                    <h6 class="comp-upload-title">Order / Quotation Footer</h6>
                                                    <span class="badge bg-label-info">800 × 100</span>
                                                </div>
                                                <div class="comp-upload-dropzone">
                                                    <input type="file" id="footer_image" name="footer_image" onchange="previewImage(event, 'footerimagePreview')" accept="image/*">
                                                    <i class="ti ti-layout-bottombar comp-upload-icon"></i>
                                                    <p class="comp-upload-text">Click or Drop Footer</p>
                                                    <span class="comp-upload-hint">Order / Quotation / Dispatch Footer</span>
                                                </div>
                                                <div class="comp-preview-box">
                                                    <img id="footerimagePreview" src="{{ $edit->order_footer_logo && $edit->order_footer_logo_url ? $edit->order_footer_logo_url : '#' }}" alt="Footer Preview" class="comp-preview-img" style="display: {{ $edit->order_footer_logo ? 'inline-block' : 'none' }};" onerror="this.onerror=null; this.src=''; this.style.display='none';">
                                                    <span class="text-muted small" style="display: {{ $edit->order_footer_logo ? 'none' : 'inline' }};">No footer uploaded yet</span>
                                                    <input type="hidden" name="footer_image_old" value="{{ $edit->order_footer_logo }}">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Employee Handbook & Policies -->
                                        <div class="col-lg-4 col-md-12 col-sm-12">
                                            @php
                                                $compSlug = \Illuminate\Support\Str::slug($edit->id . ' ' . $edit->company_name);
                                                $handbookDir = public_path('uploads/' . $compSlug . '/handbook/');
                                                $handbookRelPath = null;
                                                $handbookType = 'PDF / Image';
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
                                            <div class="comp-upload-card">
                                                <div class="comp-upload-header">
                                                    <h6 class="comp-upload-title">Employee Handbook</h6>
                                                    @if($handbookExists)
                                                        <span class="badge bg-label-success"><i class="ti ti-check me-1"></i>{{ $handbookType }}</span>
                                                    @else
                                                        <span class="badge bg-label-warning">Not Uploaded</span>
                                                    @endif
                                                </div>
                                                <div class="comp-upload-dropzone">
                                                    <input type="file" id="handbook_file" name="handbook_file" accept=".pdf,application/pdf,image/png,image/jpeg,image/jpg,image/webp" onchange="if(this.files[0]){ document.getElementById('handbookSelectedName').innerText = this.files[0].name; document.getElementById('handbookSelectedBox').style.display='block'; }">
                                                    <i class="ti ti-file-text comp-upload-icon"></i>
                                                    <p class="comp-upload-text">Click or Drop PDF / Image</p>
                                                    <span class="comp-upload-hint">PDF, PNG, JPG, WebP supported</span>
                                                </div>
                                                <div class="comp-preview-box d-flex flex-column align-items-center justify-content-center">
                                                    <div id="handbookSelectedBox" style="display: none; width: 100%; margin-bottom: 6px;" class="p-2 bg-label-primary rounded small">
                                                        <i class="ti ti-paperclip me-1"></i> Selected: <span id="handbookSelectedName" class="fw-semibold"></span>
                                                    </div>
                                                    @if($handbookExists)
                                                        <a href="{{ $handbookUrl }}" target="_blank" class="btn btn-xs btn-outline-primary w-100">
                                                            <i class="ti ti-eye me-1"></i> View Handbook ({{ $handbookType }})
                                                        </a>
                                                    @else
                                                        <span class="text-muted small">No handbook uploaded</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 5. Save Action Bar -->
                            <div class="comp-save-bar">
                                <div class="d-none d-md-flex align-items-center gap-2 text-muted small">
                                    <i class="ti ti-info-circle text-primary font-size-base"></i> All changes will take effect across the company workspace immediately.
                                </div>
                                <div class="d-flex align-items-center gap-2 w-100 w-md-auto justify-content-end">
                                    <a href="{{ route($route . '.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                                    <button type="submit" id="submit" class="btn btn-primary btn-submit waves-effect waves-light px-4">
                                        <i class="ti ti-device-floppy me-1"></i> Save Changes
                                    </button>
                                </div>
                            </div>
                            @endif
                        </form>
                    </div>
                @endif

                        @if(request('tab') === 'mail-tab')
                        <div class="tab-pane fade {{ request('tab') === 'mail-tab' ? 'show active' : '' }}" id="mail-tab">
                            <!-- Mail Config Content -->
                            <form id="myEmailSettingForm" action="{{ route($modules['route'] . '.mail_setting', [$edit?->id]) }}" method="POST"
                                    enctype="multipart/form-data">
                                    <input type="hidden" name="team_set" value="team_update" />
                                    <input type="hidden" name="tab" value="mail-tab" />
                                    <input type="hidden" name="company_id" value="{{ $edit?->id ?? '' }}" />
                                    @csrf
                            <div class="row g-7">
                                <div class="col-md-4 col-sm-12 mb-3">
                                    <label class="form-label" for="mailer">Mailer</label>
                                    <input type="text" id="mailer" name="mailer"
                                        class="form-control @error('mailer') is-invalid @enderror"
                                        value="{{ old('mailer', $mail_setting->mailer ?? '') }}" placeholder="Mailer" />
                                    @error('mailer')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="col-md-4 col-sm-12 mb-3">
                                    <label class="form-label" for="host">Host</label>
                                    <input type="text" id="host" name="host"
                                        class="form-control @error('host') is-invalid @enderror"
                                        value="{{ old('host', $mail_setting?->host ?? '') }}" placeholder="Host" />
                                    @error('host')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="col-md-4 col-sm-12 mb-3">
                                    <label class="form-label" for="app_key">Port</label>
                                    <input type="text" id="port" name="port"
                                        class="form-control @error('port') is-invalid @enderror"
                                        value="{{ old('port', $mail_setting?->port ?? '') }}" placeholder="Port" />
                                    @error('port')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="col-md-4 col-sm-12 mb-3">
                                    <label class="form-label" for="username">Username</label>
                                    <input type="text" id="username" name="username"
                                        class="form-control @error('username') is-invalid @enderror"
                                        value="{{ old('host', $mail_setting?->username ?? '') }}" placeholder="Username" />
                                    @error('username')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>


                                <div class="col-md-4 col-sm-12 mb-3">
                                    <label class="form-label" for="mail_password">Password</label>
                                    <input type="text" id="mail_password" name="password"
                                        class="form-control @error('password') is-invalid @enderror"
                                        value="{{ old('host', $mail_setting?->password ?? '') }}" placeholder="Password" />
                                    @error('password')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="col-md-4 col-sm-12 mb-3">
                                    <label class="form-label" for="encryption">Encryption</label>
                                    <select id="encryption" name="encryption"
                                        class="form-select @error('encryption') is-invalid @enderror">
                                        <option value="tls"
                                            {{ old('encryption', $mail_setting?->encryption) == 'tls' ? 'selected' : '' }}>TLS
                                        </option>
                                        <option value="ssl"
                                            {{ old('encryption', $mail_setting?->encryption) == 'ssl' ? 'selected' : '' }}>SSL
                                        </option>
                                    </select>
                                    @error('encryption')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="col-md-4 col-sm-12 mb-3">
                                    <label class="form-label" for="from_address">From Address</label>
                                    <input type="text" id="from_address" name="from_address"
                                        class="form-control @error('from_address') is-invalid @enderror"
                                        value="{{ old('from_address', $mail_setting->from_address ?? '') }}"
                                        placeholder="From Address" />
                                    @error('from_address')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="col-md-4 col-sm-12 mb-3">
                                    <label class="form-label" for="from_name">From Name</label>
                                    <input type="text" id="from_name" name="from_name"
                                        class="form-control @error('from_name') is-invalid @enderror"
                                        value="{{ old('from_name', $mail_setting->from_name ?? '') }}"
                                        placeholder="From Name" />
                                    @error('from_name')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="col-md-4 col-sm-12 mb-3">
                                    <label class="form-label" for="bcc">BCC <small class="muted">Press Enter add More
                                            than One BCC
                                            Mail</small></label>
                                    <input type="text" id="bcc" name="bcc" data-role="tagsinput"
                                        class="input-tags form-control  @error('bcc') is-invalid @enderror"
                                        value="{{ old('bcc', $mail_setting->bcc ?? '') }}" />
                                    @error('bcc')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <!-- Submit and Cancel Buttons -->
                                <div class="col-12 d-flex justify-content-end">
                                    <button type="submit" name="btn_submit" value="submit_and_exit"
                                        class="btn btn-primary btn-submit waves-effect waves-light mt-3">
                                        Submit
                                    </button>
                                </div>
                                </div>
                            </form>
                        </div>
                        @endif

                    </div>
                </div>
            @endif

        @if(request('tab') === 'subscription-tab')
            <div class="tab-pane fade {{ request('tab') === 'subscription-tab' ? 'show active' : '' }}" id="subscription-tab">
                <!-- Subscription History -->
                <div class="row my-3">
                    <div class="col-md-12 mb-5" id="filter_section">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-2">
                                        <label class="form-label">Filter by Name</label>
                                        <input type="search" class="form-control search" name="search" placeholder="search..." autofocus>
                                    </div>
                                    <div class="col-md-3 col-sm-12">
                                        <div class="form-group">
                                            <label class="form-label">Filter by Plan</label>
                                            <select id="plan_id" name="plan_id"
                                                class="form-control select2 select_filter">
                                                <option value="">Filter by Plan</option>
                                                @if(isset($subscription_plan_list) && count($subscription_plan_list) > 0)
                                                    @foreach($subscription_plan_list as $planItem)
                                                        <option value="{{ $planItem->id }}">{{ $planItem->name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-3 mb-2">
                                        <label class="form-label">Filter by Plan Expire Date</label>
                                        <input type="text" name="plan_date" class="form-control my_daterangepicker table_filter"
                                            value="" placeholder="Filter by date range">
                                    </div>
                                    <div class="col-md-3 col-sm-12">
                                        <label for="subscription_status_id" class="form-label">Filter by Status</label>
                                        <select id="subscription_status_id" name="subscription_status_id" class="form-select select2 select_filter">
                                            <option value="all">Select Subscription Status</option>
                                            <option value="active">Active</option>
                                            <option value="expire">Expire</option>
                                        </select>
                                    </div>
                                    <div class="col-md-1 text-center">
                                        <button type="button" title="Cilory Filter" id="cilory_filter"
                                            class="btn btn-outline-danger btn-icon ms-75 me-75 mt-4"><i class="ti ti-x"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-datatable text-nowrap mt-3">
                                <div class="card-datatable table-responsive" style="overflow-x: unset;">
                                    <table id="yajra-datatables" class="dt-responsive table table-hover">
                                            <thead>
                                            <tr>
                                                <th>Plan Name</th>
                                                <th>Plan From</th>
                                                <th>Plan To</th>
                                                <th>Plan Expiry Date</th>
                                                <th>Subscription Status</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>


@endsection

@section('page_leavel_script')
<!-- Data tables -->
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="{{ asset('software/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
@endsection

@push('page_scripts')

@include('utils.getPlans')
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        initializeCKEditor(".ckeditor_common_cls");

        function previewImage(event, previewId) {
            const input = event.target;
            const preview = document.getElementById(previewId);
            const file = input.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'inline-block';
                    if (preview.nextElementSibling && preview.nextElementSibling.tagName === 'SPAN') {
                        preview.nextElementSibling.style.display = 'none';
                    }
                }
                reader.readAsDataURL(file);
            } else {
                preview.src = '#';
                preview.style.display = 'none';
                if (preview.nextElementSibling && preview.nextElementSibling.tagName === 'SPAN') {
                    preview.nextElementSibling.style.display = 'inline';
                }
            }
        }

        $(function() {
            var picker = $('.my_daterangepicker').data('daterangepicker');
            if (picker) {
                picker.maxDate = false;

                var startDate = moment().startOf('month');
                var endDate = moment().add(2, 'year').endOf('year');

                picker.setStartDate(startDate);
                picker.setEndDate(endDate);
                picker.callback(startDate, endDate, 'Initial range');
            }
        });

    var dtable = null;

    $(document).ready(function() {

        dtable = $('#yajra-datatables').DataTable({
            processing: true,
            serverSide: true,
            dom: '<"table-responsive"t><"d-flex justify-content-between align-items-center"<"ps-3"l>i<"pe-4"p>>',
            order: [[0, 'DESC']],
            ajax: {
                url: "{{ route('company.detail', $edit?->id) }}",
                type: "GET",
                beforeSend: function(request) {
                    request.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
                },
                data: function(data) {
                    data.search = $('input[name="search"]').val();
                    data.filter_plan = $('select[name="plan_id"] option:selected').val();
                    data.filter_plan_date = $('input[name="plan_date"]').val().replace(' - ', ' to ');
                    data.filter_subscription_status_id = $('select[name="subscription_status_id"] option:selected').val();
                    data.tab = 'subscription-tab';
                },
                dataSrc: function(json) {
                    if (json.meta) {
                        $('#company-info-name').text(json.meta.company_name);
                        $('#company-info-plan').text(json.meta.plan_name);
                        $('#company-info-email').text(json.meta.company_email);
                    }
                    return json.data;
                }
            },
            columns: [
                // { data: 'company_arrow', name: 'company_id' },
                { data: 'company_arrow', name: 'plan_id' },
                { data: 'plan_from', name: 'plan_from' },
                { data: 'plan_to', name: 'plan_to' },
                { data: 'plan_expiry_date', name: 'plan_expiry_date' },
                { data: 'subscription_status', name: 'subscription_status' },
            ],
            createdRow: function (row, data) {
                $(row).attr('data-company_id', data.company_id);
                $(row).attr('data-plan_id', data.plan?.id);
                $(row).attr('data-subscription_id', data.id);
            },
            language: {
                searchPlaceholder: 'Search...',
            }
        });
    });

    $(document).on('change', '.select_filter, .table_filter', function(event) {
        event.preventDefault();
        if (dtable) {
            dtable.draw();
        }
    });

    $('input[name="search"]').keyup(function() {
        dtable.draw();
    });

    setTimeout(function() {
        $("#filter_section").show();
    }, 1000);

    $("#cilory_filter").click(function() {
        $('.select_filter').val(null).trigger('change');
        $('#status_filter').val('all').trigger('change');
        $('.search').val('');
        $('#filter_by_date').val('');
        $("#subscription_status_id").html("<option value=''>Select Sales Status</option>");

        var picker = $('.my_daterangepicker').data('daterangepicker');
        if(picker){
            picker.maxDate = false;
            let startOfMonth = moment().startOf('month');
            let today =  moment().add(2, 'year').endOf('year');

            picker.setStartDate(startOfMonth);
            picker.setEndDate(today);
            picker.callback(startOfMonth, today, 'This Month');
        }
        dtable.draw();
    });

    $(document).on('click', '.company_sub_addon_expand', function () {
        const $icon = $(this);
        const companyId = $icon.data('company_id');
        const planId = $icon.data('plan_id');
        const subscriptionId = $icon.data('subscription_id');
        const $currentRow = $icon.closest('tr');

        const $nextRow = $currentRow.next();

        // If the next row is the expanded one, toggle (close it)
        if ($nextRow.hasClass('inner-table-row')) {
            $nextRow.remove();
            return;
        }

        // Remove any other open inner rows before appending new one
        $('.inner-table-row').remove();

        $.ajax({
            url: "{{ route('company-subscription-plan.get_subscription_addons') }}",
            method: "POST",
            data: {
                company_id: companyId,
                plan_id: planId,
                subscription_id: subscriptionId,
            },
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            datatype: 'json',
            success: function (response) {
                if(response.status){
                    var resData = response.data;
                    var innerTableHtml = '';
                    if(resData.length > 0){
                        innerTableHtml += '<tr class="inner-table-row" style="background-color:#e2e6e8;"><td colspan="6"><table class="table table-bordered mb-0"><thead><tr><th colspan="6" style="text-align: center;"><h5 style="font-weight: bold;">Subscription Plan Addon Days</h5></th></tr><tr><th>Plan Name</th><th>Plan From</th><th>Plan Expire</th><th>Add Days</th><th>Created Date</th></tr></thead><tbody>';

                        $.each(response.data, function(index, item) {
                            innerTableHtml += '<tr>';
                                    // innerTableHtml += '<td>'+ item.company?.company_name ?? +'</td>';
                                    innerTableHtml += '<td>'+ item.plan?.name ?? +'</td>';
                                    innerTableHtml += '<td>'+ item.plan_from ?? +'</td>';
                                    innerTableHtml += '<td>'+ item.plan_to ?? +'</td>';
                                    innerTableHtml += '<td>'+ item.add_days ?? +'</td>';
                                    innerTableHtml += '<td>'+ item.created_at_org ?? +'</td>';
                            innerTableHtml += '</tr>';
                        });
                        innerTableHtml += '</tbody></table></td></tr>';
                        $(innerTableHtml).insertAfter($currentRow);
                    }
                }
            }
        });
    });
    </script>
@endpush
