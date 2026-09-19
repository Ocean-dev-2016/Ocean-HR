@extends('software.layout.app')

@section('title', 'Register New Company')

@section('page_leavel_style')
    <link rel="stylesheet" href="{{ asset('software/vendor/libs/@form-validation/form-validation.css') }}" />
    <link rel="stylesheet" href="{{ asset('software/vendor/css/pages/page-auth.css') }}" />
    <style>
        .reg-hero-card {
            border: 1px solid #fed7aa !important;
            border-radius: 18px !important;
            overflow: hidden;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.08), 0 6px 18px rgba(234, 88, 12, 0.05) !important;
            background: #ffffff !important;
        }

        .reg-hero-card .card-body {
            background: linear-gradient(180deg, #ffffff 0%, #fff7ed 100%) !important;
            padding: 1.5rem 2rem !important;
        }

        .reg-hero-header {
            background: linear-gradient(135deg, #ffedd5 0%, #fff7ed 100%);
            padding: 1.25rem 2rem;
            position: relative;
            overflow: hidden;
            border-radius: 18px 18px 0 0 !important;
            border-bottom: 1px solid #fed7aa;
        }

        .reg-hero-header h3 {
            font-size: 1.35rem !important;
        }

        .reg-hero-header::before {
            content: '';
            position: absolute;
            top: -40px;
            right: -40px;
            width: 160px;
            height: 160px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.12);
            pointer-events: none;
        }

        .reg-hero-header::after {
            content: '';
            position: absolute;
            bottom: -30px;
            left: -30px;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            pointer-events: none;
        }

        .reg-section-box {
            background: #fffbf7;
            border: 1px solid #fed7aa;
            border-radius: 14px;
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.25rem;
            transition: all 0.25s ease-in-out;
        }

        .reg-section-box:hover {
            border-color: #fdba74;
            box-shadow: 0 4px 12px rgba(234, 88, 12, 0.05);
        }

        .reg-section-title {
            font-size: 0.925rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #9a3412;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }

        .reg-section-title i {
            font-size: 1.15rem;
            color: #f97316;
            margin-right: 0.5rem;
            background: #ffedd5;
            padding: 6px;
            border-radius: 8px;
        }

        .form-label {
            font-weight: 600;
            color: #334155;
            font-size: 0.85rem;
            margin-bottom: 0.35rem;
        }

        .form-control, .form-select {
            border-radius: 10px;
            border: 1px solid #cbd5e1;
            padding: 0.55rem 0.85rem;
            font-size: 0.9rem;
            transition: all 0.2s ease-in-out;
        }

        .form-control:focus, .form-select:focus {
            border-color: #f97316;
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.12);
        }

        .input-group-text {
            border-radius: 0 10px 10px 0;
            border: 1px solid #cbd5e1;
            border-left: none;
            background: #ffffff;
            padding: 0.55rem 0.85rem;
        }

        .input-group .form-control {
            border-radius: 10px 0 0 10px !important;
        }

        .pass-requirement-card {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            border: 1px dashed #cbd5e1;
            margin-bottom: 1rem;
        }

        .pass-requirement-card ul {
            list-style: none;
            padding-left: 0;
            margin-bottom: 0;
        }

        .pass-requirement-card li {
            font-size: 0.85rem;
            color: #64748b;
            margin-bottom: 0.35rem;
            display: flex;
            align-items: center;
            line-height: 1.4;
        }

        .pass-requirement-card li i {
            font-size: 1.15rem;
            margin-right: 0.5rem;
            flex-shrink: 0;
        }

        .btn-submit-reg {
            background: linear-gradient(135deg, #ea580c 0%, #d97706 100%);
            border: none;
            border-radius: 12px;
            padding: 0.65rem 2.25rem;
            font-weight: 700;
            font-size: 0.95rem;
            color: #ffffff;
            box-shadow: 0 8px 18px rgba(234, 88, 12, 0.22);
            transition: all 0.25s ease;
        }

        .btn-submit-reg:hover {
            background: linear-gradient(135deg, #c2410c 0%, #b45309 100%);
            transform: translateY(-1px);
            box-shadow: 0 10px 22px rgba(234, 88, 12, 0.32);
            color: #ffffff;
        }

        .btn-cancel-reg {
            border-radius: 12px;
            padding: 0.65rem 2rem;
            font-weight: 700;
            font-size: 0.9rem;
            background: #ffffff !important;
            color: #334155 !important;
            border: 2px solid #cbd5e1 !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.03);
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-cancel-reg:hover {
            background: #f1f5f9 !important;
            border-color: #94a3b8 !important;
            color: #0f172a !important;
            transform: translateY(-1px);
        }

        .otp-input-field {
            width: 55px;
            height: 55px;
            font-size: 1.6rem;
            font-weight: 700;
            border-radius: 12px;
            border: 2px solid #e2e8f0;
            text-align: center;
            transition: all 0.2s ease;
        }

        .otp-input-field:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
        }
    </style>
@endsection

@section('content')
    <div class="min-vh-100 d-flex flex-column justify-content-center align-items-center py-3 py-md-4 px-2 px-md-4" style="background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%); width: 100%;">
        <div class="container-xxl">
            <div class="row justify-content-center">
                <div class="col-12">
                    <div class="card reg-hero-card">
                        <!-- Header -->
                        <div class="reg-hero-header text-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <h3 class="fw-bold mb-1 d-flex align-items-center" style="color: #7c2d12 !important;">
                                    <i class="ti ti-building-store me-2" style="color: #ea580c !important;"></i> Register New Company
                                </h3>
                                <p class="mb-0" style="color: #9a3412 !important; font-size: 0.875rem; font-weight: 600;">Fill out the form below to initiate your company account registration.</p>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-white text-dark fw-bold rounded-pill px-3 py-2 shadow-sm d-inline-flex align-items-center" style="color: #f97316 !important;">
                                    <i class="ti ti-gift me-1 text-danger"></i> 7-Day Free Trial Included
                                </span>
                                <a href="{{ route('software.login') }}" class="btn btn-sm bg-white text-dark fw-bold rounded-pill px-3 py-2 shadow-sm border-0">
                                    <i class="ti ti-arrow-left me-1 text-danger fw-bold"></i> <span class="text-dark fw-bold">Back to Login</span>
                                </a>
                            </div>
                        </div>

                        <!-- Form Body -->
                        <div class="card-body px-3 px-md-4 py-3 pt-3">
                            <!-- 7 Days Trial Plan Alert Banner -->
                            <div class="alert border-0 rounded-4 mb-3 p-2.5 px-3 d-flex align-items-center shadow-sm" style="background: #fff7ed; border-left: 5px solid #f97316 !important;">
                                <div class="me-3 p-2 rounded-circle text-white d-flex align-items-center justify-content-center" style="background: #f97316; width: 42px; height: 42px; min-width: 42px;">
                                    <i class="ti ti-gift fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold" style="color: #9a3412;">Special 7-Day Free Trial Plan Included</h6>
                                    <p class="mb-0 small text-muted">Register today and enjoy full platform access with our complimentary 7-Day Free Trial Plan!</p>
                                </div>
                            </div>


                            <form action="{{ route('software.register.company.submit') }}" method="POST" enctype="multipart/form-data" id="companyAddForm">
                                @csrf
                                <input type="hidden" name="otp" value="" id="otp">

                                <!-- Section 1: Company Profile -->
                                <div class="reg-section-box">
                                    <div class="reg-section-title">
                                        <i class="ti ti-building"></i> Company Information
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-4 col-sm-12">
                                            <label class="form-label">GST No</label>
                                            <input id="gst_no" type="text"
                                                class="form-control @error('gst_no') is-invalid @enderror" name="gst_no"
                                                value="{{ old('gst_no') }}" autocomplete="gst_no" placeholder="Enter GST No (Optional)"
                                                data-name="gst_no">
                                            @error('gst_no')
                                                <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                                            @enderror
                                        </div>
                                        <div class="col-md-4 col-sm-12">
                                            <label class="form-label">Company Name <span class="text-danger">*</span></label>
                                            <input id="company_name" type="text"
                                                class="form-control @error('company_name') is-invalid @enderror required"
                                                name="company_name" value="{{ old('company_name') }}" autocomplete="company_name"
                                                placeholder="Enter company name" data-name="company_name">
                                            @error('company_name')
                                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                            @enderror
                                        </div>
                                        <div class="col-md-4 col-sm-12">
                                            <label class="form-label">Person Name <span class="text-danger">*</span></label>
                                            <input id="person_name" type="text"
                                                class="form-control @error('person_name') is-invalid @enderror required" name="person_name"
                                                value="{{ old('person_name') }}" autocomplete="person_name" placeholder="Enter contact person name"
                                                data-name="person_name">
                                            @error('person_name')
                                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Section 2: Contact & Security -->
                                <div class="reg-section-box">
                                    <div class="reg-section-title">
                                        <i class="ti ti-lock"></i> Account & Contact Details
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-lg-3 col-md-6 col-sm-12">
                                            <label class="form-label">WhatsApp Number (Used at Login) <span class="text-danger">*</span></label>
                                            <input id="whatsapp_number" type="text"
                                                class="length10 form-control @error('whatsapp_number') is-invalid @enderror required"
                                                name="whatsapp_number" value="{{ old('whatsapp_number') }}" autocomplete="whatsapp_number"
                                                placeholder="Enter 10-digit mobile number" data-name="whatsapp_number"
                                                onkeypress="return isNumber(event)">
                                            @error('whatsapp_number')
                                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                            @enderror
                                        </div>
                                        <div class="col-lg-3 col-md-6 col-sm-12">
                                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                            <input id="email" type="text"
                                                class="form-control @error('email') is-invalid @enderror required" name="email"
                                                value="{{ old('email') }}" autocomplete="email" placeholder="Enter valid email"
                                                data-name="email">
                                            @error('email')
                                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                            @enderror
                                        </div>
                                        <div class="col-lg-3 col-md-6 col-sm-12">
                                            <label class="form-label">Password <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input id="password" type="password"
                                                    class="form-control @error('password') is-invalid @enderror required" name="password"
                                                    value="{{ old('password') }}" placeholder="Create password" aria-describedby="password"
                                                    data-name="password">
                                                <span class="input-group-text cursor-pointer toggle-password" onclick="togglePassword('password', 'togglePasswordIcon')">
                                                    <i class="ti ti-eye-off" id="togglePasswordIcon"></i>
                                                </span>
                                            </div>
                                            @error('password')
                                                <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                                            @enderror
                                        </div>
                                        <div class="col-lg-3 col-md-6 col-sm-12">
                                            <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input id="password_confirmation" type="password"
                                                    class="form-control @error('password_confirmation') is-invalid @enderror required" name="password_confirmation"
                                                    value="{{ old('password_confirmation') }}" placeholder="Re-enter password" aria-describedby="password_confirmation"
                                                    data-name="confirm_password">
                                                <span class="input-group-text cursor-pointer toggle-password" onclick="togglePassword('password_confirmation', 'toggleConfirmPasswordIcon')">
                                                    <i class="ti ti-eye-off" id="toggleConfirmPasswordIcon"></i>
                                                </span>
                                            </div>
                                            @error('password_confirmation')
                                                <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Hidden default values for Country, State, City, Plan, Date Format, Time Format, and HRA Percentage -->
                                <input type="hidden" name="country_id" id="country_id" value="{{ old('country_id', 1) }}">
                                <input type="hidden" name="state_id" id="state_id" value="{{ old('state_id', 1) }}">
                                <input type="hidden" name="city_id" id="city_id" value="{{ old('city_id', 1) }}">
                                <input type="hidden" name="plan_id" id="plan_id" value="{{ old('plan_id', $plans->first()?->id) }}">
                                <input type="hidden" name="date_format" id="date_format" value="{{ old('date_format', \App\Helpers\Helper::getDefaultDateFormat()) }}">
                                <input type="hidden" name="time_format" id="time_format" value="{{ old('time_format', \App\Helpers\Helper::getDefaultTimeFormat()) }}">
                                <input type="hidden" name="hra_percentage" id="hra_percentage" value="{{ old('hra_percentage', 40) }}">

                                <!-- Password Requirement Checklist -->
                                <div class="pass-requirement-card mb-4">
                                    <div class="fw-bold text-dark mb-3 small"><i class="ti ti-shield-check me-1" style="color: #ea580c;"></i> Password Security Requirements:</div>
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <ul class="mb-0 ps-0">
                                                <li id="minimumlength" class="mb-2"><i class="ti ti-circle-dot me-2"></i><span class="me-1">Minimum</span><strong>8 characters</strong></li>
                                                <li id="leastlowercase" class="mb-2"><i class="ti ti-circle-dot me-2"></i><span class="me-1">At least</span><strong class="me-1">one lowercase</strong><span>letter (a-z)</span></li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <ul class="mb-0 ps-0">
                                                <li id="leastuppercase" class="mb-2"><i class="ti ti-circle-dot me-2"></i><span class="me-1">At least</span><strong class="me-1">one uppercase</strong><span>letter (A-Z)</span></li>
                                                <li id="onenumber" class="mb-2"><i class="ti ti-circle-dot me-2"></i><span class="me-1">At least</span><strong class="me-1">one number</strong><span>(0-9)</span></li>
                                                <li id="specialcharacter" class="mb-2"><i class="ti ti-circle-dot me-2"></i><span class="me-1">At least</span><strong class="me-1">one special character</strong><span>(!@#$%^&*)</span></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <!-- Form Actions -->
                                <div class="text-center pt-2">
                                    <button type="button" class="btn btn-submit-reg me-3" id="submit_btn">
                                        <i class="ti ti-check me-1"></i> Submit Registration
                                    </button>
                                    <a href="{{ route('software.login') }}" class="btn btn-cancel-reg">
                                        <i class="ti ti-x me-1"></i> Cancel
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="text-center mt-3 mb-1">
                        <p class="mb-0 text-muted small">
                            Powered By <a href="https://oceaninfotech.co.in/" target="_blank" class="fw-bold" style="color: #ea580c !important; text-decoration: none;">Ocean Infotech</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modern OTP Modal -->
    <div class="modal fade" id="otpModal" tabindex="-1" aria-labelledby="otpModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header text-white border-0 py-3" style="background: linear-gradient(135deg, #d97706 0%, #ea580c 100%);">
                    <h5 class="modal-title text-white fw-bold fs-6" id="otpModalLabel">
                        <i class="ti ti-shield-lock me-1"></i> Verify WhatsApp OTP
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <p class="text-muted small mb-3">Your verification OTP code is <strong id="otpNumberDisplay" class="fs-5" style="color: #ea580c;"></strong></p>
                    <div class="d-flex justify-content-center gap-2 mb-4">
                        <input type="text" class="form-control otp-input-field modal_opt_numbers" maxlength="1"
                            oninput="moveToNext(this, 'otp2')" id="otp1" autofocus>
                        <input type="text" class="form-control otp-input-field modal_opt_numbers" maxlength="1"
                            oninput="moveToNext(this, 'otp3')" id="otp2">
                        <input type="text" class="form-control otp-input-field modal_opt_numbers" maxlength="1"
                            oninput="moveToNext(this, 'otp4')" id="otp3">
                        <input type="text" class="form-control otp-input-field modal_opt_numbers" maxlength="1" id="otp4">
                    </div>
                    <button class="btn text-white w-100 rounded-pill py-2 fw-bold shadow-sm" style="background: linear-gradient(135deg, #ea580c 0%, #d97706 100%); border: none;" onclick="submitOTP()">
                        <i class="ti ti-shield-check me-1"></i> Verify & Submit
                    </button>
                    <div class="mt-3">
                        <p class="small text-muted mb-0">Didn't get code? <a href="javascript:void(0)" class="fw-bold ms-1" style="color: #ea580c;" onclick="resendOTP()">Resend OTP</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('page_scripts')
    @include('utils.getCountry')
    @include('utils.getStateByCountry')
    @include('utils.getCityByState')
@endpush

@push('page_scripts')
    <script type="text/javascript">
        function togglePassword(inputId = 'password', iconId = 'togglePasswordIcon') {
            const passwordInput = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (!passwordInput || !icon) return;
            const isPassword = passwordInput.type === 'password';

            passwordInput.type = isPassword ? 'text' : 'password';
            icon.classList.toggle('ti-eye-off', !isPassword);
            icon.classList.toggle('ti-eye', isPassword);
        }

        function isNumber(evt) {
            evt = (evt) ? evt : window.event;
            var charCode = (evt.which) ? evt.which : evt.keyCode;
            if (charCode > 31 && (charCode < 48 || charCode > 57)) {
                return false;
            }
            return true;
        }

        function isValidGSTIN(gstin) {
            if (!gstin) return false;
            const regex = /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/;
            return regex.test(gstin.toUpperCase());
        }

        function validatePassword() {
            const password = document.getElementById("password").value;
            let isValid = true;

            if (password.length < 8) {
                $("#minimumlength").css("color", "#ef4444").find("i").attr("class", "ti ti-circle-x me-1");
                isValid = false;
            } else {
                $("#minimumlength").css("color", "#10b981").find("i").attr("class", "ti ti-circle-check me-1");
            }

            if (!/[a-z]/.test(password)) {
                $("#leastlowercase").css("color", "#ef4444").find("i").attr("class", "ti ti-circle-x me-1");
                isValid = false;
            } else {
                $("#leastlowercase").css("color", "#10b981").find("i").attr("class", "ti ti-circle-check me-1");
            }

            if (!/[A-Z]/.test(password)) {
                $("#leastuppercase").css("color", "#ef4444").find("i").attr("class", "ti ti-circle-x me-1");
                isValid = false;
            } else {
                $("#leastuppercase").css("color", "#10b981").find("i").attr("class", "ti ti-circle-check me-1");
            }

            if (!/[0-9]/.test(password)) {
                $("#onenumber").css("color", "#ef4444").find("i").attr("class", "ti ti-circle-x me-1");
                isValid = false;
            } else {
                $("#onenumber").css("color", "#10b981").find("i").attr("class", "ti ti-circle-check me-1");
            }

            if (!/[^A-Za-z0-9]/.test(password)) {
                $("#specialcharacter").css("color", "#ef4444").find("i").attr("class", "ti ti-circle-x me-1");
                isValid = false;
            } else {
                $("#specialcharacter").css("color", "#10b981").find("i").attr("class", "ti ti-circle-check me-1");
            }

            return isValid;
        }

        function generateOTP() {
            return Math.floor(1000 + Math.random() * 9000);
        }

        function openOTPModal() {
            const otpModal = new bootstrap.Modal(document.getElementById('otpModal'));
            otpModal.show();
        }

        function moveToNext(current, nextFieldId) {
            if (current.value.length === 1) {
                document.getElementById(nextFieldId).focus();
            }
        }

        function resendOTP() {
            var otp = generateOTP();
            $("#otp").val(otp);
            $("#otpNumberDisplay").text(otp);
            toastr.success('OTP resent successfully', 'Success');
        }

        function submitCompanyRegistrationDirect() {
            var $btn = $('#submit_btn');
            var originalHtml = $btn.data('original-html') || '<i class="ti ti-check me-1"></i> Submit Registration';

            if (!$("#otp").val()) {
                $("#otp").val(generateOTP());
            }
            var formData = new FormData($('#companyAddForm')[0]);

            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Submitting...');

            $.ajax({
                url: "{{ route('software.register.company.submit') }}",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    toastr.options = {
                        "closeButton": true,
                        "progressBar": true,
                        "positionClass": "toast-top-right",
                        "timeOut": "4000",
                        "extendedTimeOut": "1000"
                    };
                    var successMsg = (response && response.message) ? response.message : 'Company created successfully! Login credentials sent to your email.';
                    toastr.success(successMsg, 'Success');

                    setTimeout(function() {
                        window.location.href = response.redirect_url || "{{ route('software.login') }}";
                    }, 3000);
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).html(originalHtml);
                    var errMsg = "Registration failed. Please try again.";
                    if (xhr.status === 422) {
                        let res = xhr.responseJSON;
                        if (res.errors && Object.keys(res.errors).length > 0) {
                            var firstKey = Object.keys(res.errors)[0];
                            errMsg = res.errors[firstKey][0];
                        } else if (res.message) {
                            errMsg = res.message;
                        }
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errMsg = xhr.responseJSON.message;
                    }
                    toastr.error(errMsg, 'Error');
                }
            });
        }

        function submitOTP() {
            submitCompanyRegistrationDirect();
        }

        $(document).ready(function() {
            $(".length10").attr("maxlength", "10").attr("minlength", "10");

            $(document).on('keyup', '#password', function(e) {
                validatePassword();
            });

            $(document).on('blur', '#password_confirmation', function(e) {
                var password = $("#password").val();
                var confirmPassword = $(this).val();
                if (confirmPassword !== '' && confirmPassword !== null) {
                    if (password !== confirmPassword) {
                        $(this).addClass('is-invalid');
                        toastr.error('Password and Confirm Password do not match.');
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                }
            });

            $(document).on('click', '#submit_btn', function(e) {
                let isValid = true;
                e.preventDefault();
                var $btn = $(this);
                if (!$btn.data('original-html')) {
                    $btn.data('original-html', $btn.html());
                }
                var originalHtml = $btn.data('original-html');

                $("#companyAddForm").find(".required").each(function() {
                    if (!$(this).val()) {
                        $(this).addClass('is-invalid');
                        isValid = false;
                        var error_message = $(this).attr("data-name").replace(/_/g, " ");
                        toastr.error(error_message + " is required");
                    } else {
                        $(this).removeClass("is-invalid");
                    }
                });

                var gst_no = $('#gst_no').val();
                if (gst_no != '' && gst_no != null) {
                    if (!isValidGSTIN(gst_no)) {
                        $('#gst_no').addClass('is-invalid');
                        toastr.error('Please enter valid GST No.');
                        isValid = false;
                    } else {
                        $("#gst_no").removeClass("is-invalid");
                    }
                }

                var whatsapp_number = $('#whatsapp_number').val();
                if (whatsapp_number.length != 10) {
                    $('#whatsapp_number').addClass('is-invalid');
                    toastr.error('Please enter a valid 10-digit WhatsApp number.');
                    isValid = false;
                } else {
                    $("#whatsapp_number").removeClass("is-invalid");
                }

                var password = $("#password").val();
                var confirmPassword = $("#password_confirmation").val();

                if (validatePassword(password) === false) {
                    $('#password').addClass('is-invalid');
                    toastr.error('Please fulfill password security requirements.');
                    isValid = false;
                }

                if (password !== confirmPassword) {
                    $('#password_confirmation').addClass('is-invalid');
                    toastr.error('Password and Confirm Password do not match.');
                    isValid = false;
                } else {
                    $("#password_confirmation").removeClass("is-invalid");
                }

                
                if (isValid) {
                    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Validating...');

                    var company_name = $("#company_name").val();
                    var gst_no = $("#gst_no").val();
                    var whatsapp_number = $("#whatsapp_number").val();
                    var email = $("#email").val();

                    $.ajax({
                        url: "{{ route('company.check-company-exists') }}",
                        type: "POST",
                        data: {
                            company_name: company_name,
                            gst_no: gst_no,
                            whatsapp_number: whatsapp_number,
                            email: email,
                            _token: '{{ csrf_token() }}'
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (typeof response === 'string') {
                                try { response = JSON.parse(response); } catch(e) {}
                            }
                            if (response && response.status === true) {
                                var otp = generateOTP();
                                $("#otp").val(otp);
                                submitCompanyRegistrationDirect();
                            } else {
                                $btn.prop('disabled', false).html(originalHtml);
                                toastr.error((response && response.message) ? response.message : "Validation failed.", 'Error');
                            }
                        },
                        error: function(xhr, status, error) {
                            submitCompanyRegistrationDirect();
                        }
                    });
                }
            });
        });
    </script>
@endpush
