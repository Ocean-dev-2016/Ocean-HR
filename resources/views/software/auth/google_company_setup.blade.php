@extends('software.layout.app')

@section('title', 'Set Up Company Name - OceanHR')

@section('page_leavel_style')
    <link rel="stylesheet" href="{{ asset('software/vendor/css/pages/page-auth.css') }}" />
    <style>
        .auth-card {
            max-width: 480px;
            margin: 50px auto;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(234, 88, 12, 0.08), 0 5px 15px rgba(0,0,0,0.04);
            border: 1px solid #fed7aa;
            background: #ffffff;
            overflow: hidden;
        }
        .auth-logo-wrapper {
            background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
            padding: 30px 20px 22px;
            text-align: center;
            border-bottom: 1px solid #fed7aa;
        }
        .auth-logo-wrapper img {
            max-height: 52px;
            max-width: 230px;
            width: auto;
            height: auto;
            object-fit: contain;
            display: block;
            margin: 0 auto 12px;
        }
        .verified-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #ecfdf5;
            color: #065f46;
            font-size: 12px;
            font-weight: 700;
            padding: 5px 14px;
            border-radius: 30px;
            border: 1px solid #a7f3d0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .user-info-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 24px;
        }
        .custom-input-group {
            border-radius: 12px;
            overflow: hidden;
            border: 2px solid #cbd5e1;
            transition: all 0.2s ease;
        }
        .custom-input-group:focus-within {
            border-color: #ea580c;
            box-shadow: 0 0 0 0.25rem rgba(234, 88, 12, 0.2);
        }
        .custom-input-group .input-group-text {
            background: #ffffff;
            border: none;
            color: #ea580c;
            padding-left: 16px;
            padding-right: 12px;
        }
        .custom-input-group .form-control {
            border: none;
            height: 52px;
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
            padding-left: 0;
        }
        .custom-input-group .form-control:focus {
            box-shadow: none;
            outline: none;
        }
        .btn-dull-orange {
            background: linear-gradient(135deg, #ea580c 0%, #d97706 100%) !important;
            border: none !important;
            color: #ffffff !important;
            border-radius: 12px !important;
            padding: 14px !important;
            font-weight: 700 !important;
            font-size: 15px !important;
            width: 100% !important;
            box-shadow: 0 4px 14px rgba(234, 88, 12, 0.3) !important;
            transition: all 0.25s ease-in-out !important;
        }
        .btn-dull-orange:hover, .btn-dull-orange:focus {
            background: linear-gradient(135deg, #c2410c 0%, #b45309 100%) !important;
            color: #ffffff !important;
            box-shadow: 0 6px 18px rgba(234, 88, 12, 0.4) !important;
            transform: translateY(-1px);
        }
    </style>
@endsection

@section('content')
    <div class="container">
        <div class="auth-card">
            <!-- Brand Logo Header -->
            <div class="auth-logo-wrapper">
                <a href="{{ route('software.login') }}">
                    <img src="{{ asset('software/img/logo.png') }}" alt="OceanHR Logo" />
                </a>
                <div class="verified-badge">
                    <i class="ti ti-check text-success fs-6"></i>
                    Email Verified
                </div>
            </div>
            
            <div class="p-4 p-md-4">
                <div class="text-center mb-3">
                    <h5 class="fw-bold text-dark mb-1">Set Up Your Company</h5>
                    <p class="text-muted small">Please enter your company name to complete registration</p>
                </div>

                <!-- Verified Account Details -->
                <div class="user-info-card d-flex align-items-center gap-3">
                    <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; font-weight: 700; font-size: 18px; flex-shrink: 0;">
                        {{ strtoupper(substr($pendingName ?? 'U', 0, 1)) }}
                    </div>
                    <div class="overflow-hidden">
                        <div class="fw-bold text-dark text-truncate">{{ $pendingName ?? 'User' }}</div>
                        <div class="text-muted small text-truncate">{{ $pendingEmail ?? '' }}</div>
                    </div>
                </div>

                <form action="{{ route('software.auth.google.company-setup-submit') }}" method="POST" id="companySetupForm">
                    @csrf
                    
                    <div class="mb-3 text-start">
                        <label for="company_name" class="form-label text-muted small fw-semibold text-uppercase">
                            Company Name <span class="text-danger">*</span>
                        </label>
                        <div class="input-group custom-input-group">
                            <span class="input-group-text">
                                <i class="ti ti-building fs-4"></i>
                            </span>
                            <input type="text" 
                                   name="company_name" 
                                   id="company_name" 
                                   class="form-control" 
                                   placeholder="Enter Company Name" 
                                   value="{{ old('company_name') }}" 
                                   required 
                                   autofocus
                                   autocomplete="organization" />
                        </div>
                    </div>

                    <div class="mb-4 text-start">
                        <label for="whatsapp_number" class="form-label text-muted small fw-semibold text-uppercase">
                            WhatsApp / Mobile Number <span class="text-danger">*</span>
                        </label>
                        <div class="input-group custom-input-group">
                            <span class="input-group-text">
                                <i class="ti ti-brand-whatsapp fs-4 text-success"></i>
                            </span>
                            <span class="input-group-text text-muted fw-bold ps-0 pe-2 bg-white" style="font-size: 15px;">
                                +91
                            </span>
                            <input type="tel" 
                                   name="whatsapp_number" 
                                   id="whatsapp_number" 
                                   maxlength="10"
                                   inputmode="numeric"
                                   pattern="[0-9]{10}"
                                   class="form-control" 
                                   placeholder="Enter 10-digit number" 
                                   value="{{ old('whatsapp_number') }}" 
                                   required 
                                   autocomplete="tel" />
                        </div>
                    </div>

                    <button type="submit" id="btnSubmitCompany" class="btn btn-dull-orange mb-3">
                        <i class="ti ti-rocket me-1"></i> Complete Registration & Login
                    </button>
                </form>

                <div class="text-center pt-1">
                    <a href="{{ route('software.login') }}" class="btn btn-link text-decoration-none small text-secondary">
                        Cancel & Return to Login
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page_leavel_script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('companySetupForm');
            const submitBtn = document.getElementById('btnSubmitCompany');
            const phoneInput = document.getElementById('whatsapp_number');

            // Allow only digits in whatsapp number
            if (phoneInput) {
                phoneInput.addEventListener('input', function () {
                    this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
                });
            }

            if (form && submitBtn) {
                form.addEventListener('submit', function () {
                    submitBtn.setAttribute('disabled', 'disabled');
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Creating Your Workspace...';
                });
            }
        });
    </script>
@endsection
