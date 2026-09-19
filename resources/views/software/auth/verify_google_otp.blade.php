@extends('software.layout.app')

@section('title', 'Verify Email OTP - OceanHR')

@section('page_leavel_style')
    <link rel="stylesheet" href="{{ asset('software/vendor/css/pages/page-auth.css') }}" />
    <style>
        .auth-card {
            max-width: 460px;
            margin: 60px auto;
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
        .google-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #ffffff;
            color: #9a3412;
            font-size: 12px;
            font-weight: 700;
            padding: 5px 14px;
            border-radius: 30px;
            border: 1px solid #fdba74;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 6px rgba(234, 88, 12, 0.1);
        }
        .email-pill {
            display: inline-block;
            background: #fff7ed;
            color: #ea580c;
            border: 1px solid #fdba74;
            border-radius: 30px;
            padding: 6px 18px;
            font-size: 14px;
            font-weight: 700;
        }
        .otp-input {
            letter-spacing: 14px;
            font-size: 26px;
            font-weight: 700;
            text-align: center;
            height: 58px;
            border-radius: 12px;
            border: 2px solid #cbd5e1;
            color: #1e293b;
            background: #f8fafc;
            transition: all 0.2s ease;
        }
        .otp-input:focus {
            background: #ffffff;
            border-color: #ea580c;
            box-shadow: 0 0 0 0.25rem rgba(234, 88, 12, 0.25);
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
        .countdown-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #fff7ed;
            color: #c2410c;
            font-size: 13px;
            font-weight: 600;
            padding: 6px 16px;
            border-radius: 20px;
            border: 1px solid #fed7aa;
            transition: all 0.3s ease;
        }
        .countdown-badge.expired {
            background: #fef2f2;
            color: #dc2626;
            border-color: #fecaca;
        }
        .resend-link {
            color: #ea580c !important;
            font-weight: 600;
            text-decoration: none !important;
            transition: all 0.2s ease;
        }
        .resend-link.disabled, .resend-link:disabled, .resend-link[disabled] {
            color: #94a3b8 !important;
            cursor: not-allowed !important;
            opacity: 0.6;
            text-decoration: none !important;
            pointer-events: none;
        }
        .resend-link:hover:not(.disabled):not([disabled]) {
            color: #c2410c !important;
            text-decoration: none !important;
            opacity: 0.9;
        }
    </style>
@endsection

@section('content')
    <div class="container">
        <div class="auth-card">
            <!-- Brand Logo Header with Soft Dull Orange -->
            <div class="auth-logo-wrapper">
                <a href="{{ route('software.login') }}">
                    <img src="{{ asset('software/img/logo.png') }}" alt="OceanHR Logo" />
                </a>
                <div class="google-badge">
                    <svg width="14" height="14" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                    </svg>
                    Google Account Verification
                </div>
            </div>
            
            <div class="p-4 p-md-4">
                <div class="text-center mb-4">
                    <h5 class="fw-bold text-dark mb-1">Verify Your Email</h5>
                    <p class="text-muted small mb-2">We have sent a 6-digit OTP code to:</p>
                    <span class="email-pill">{{ $pendingEmail ?? '' }}</span>
                </div>

                <form action="{{ route('software.auth.google.verify-otp-submit') }}" method="POST" id="verifyOtpForm">
                    @csrf

                    <div class="mb-4">
                        <label for="otp" class="form-label text-muted small fw-semibold text-uppercase text-center w-100">Enter 6-Digit Code</label>
                        <input type="text" name="otp" id="otp" maxlength="6" inputmode="numeric" pattern="[0-9]*" class="form-control otp-input required" placeholder="------" autofocus required autocomplete="off" />
                    </div>

                    <button type="submit" id="btnSubmitOtp" class="btn btn-dull-orange mb-3">
                        <i class="ti ti-shield-check me-1"></i> Verify OTP & Continue
                    </button>
                </form>

                <!-- 2-Minute Countdown Timer & Resend / Cancel Section -->
                <div class="text-center pt-2">
                    <div class="mb-3">
                        <div id="countdownBox" class="countdown-badge">
                            <i class="ti ti-clock-hour-4"></i>
                            <span id="timerTextLabel">Code expires in: <strong id="timerDisplay" class="font-monospace fw-bold">02:00</strong></span>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-center gap-2">
                        <form action="{{ route('software.auth.google.resend-otp') }}" method="POST" id="resendOtpForm" class="d-inline">
                            @csrf
                            <button type="submit" id="btnResendOtp" class="btn btn-link text-decoration-none small resend-link disabled" disabled>
                                <i class="ti ti-refresh me-1"></i> Resend OTP
                            </button>
                        </form>
                        <span class="text-muted small">•</span>
                        <a href="{{ route('software.login') }}" class="btn btn-link text-decoration-none small text-danger">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page_leavel_script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let remainingSeconds = parseInt("{{ $remainingSeconds ?? 120 }}", 10);
            if (isNaN(remainingSeconds) || remainingSeconds < 0) {
                remainingSeconds = 0;
            }

            const timerDisplay = document.getElementById('timerDisplay');
            const countdownBox = document.getElementById('countdownBox');
            const timerTextLabel = document.getElementById('timerTextLabel');
            const btnResendOtp = document.getElementById('btnResendOtp');
            const resendOtpForm = document.getElementById('resendOtpForm');
            const otpInput = document.getElementById('otp');

            // Only allow numbers in OTP input
            if (otpInput) {
                otpInput.addEventListener('input', function () {
                    this.value = this.value.replace(/[^0-9]/g, '');
                });
            }

            function formatTime(seconds) {
                const mins = Math.floor(seconds / 60);
                const secs = seconds % 60;
                return (mins < 10 ? '0' : '') + mins + ':' + (secs < 10 ? '0' : '') + secs;
            }

            function updateTimerUI() {
                if (remainingSeconds > 0) {
                    timerDisplay.textContent = formatTime(remainingSeconds);
                    countdownBox.classList.remove('expired');
                    btnResendOtp.setAttribute('disabled', 'disabled');
                    btnResendOtp.classList.add('disabled');
                    timerTextLabel.innerHTML = 'Code expires in: <strong id="timerDisplay" class="font-monospace fw-bold">' + formatTime(remainingSeconds) + '</strong>';
                } else {
                    countdownBox.classList.add('expired');
                    countdownBox.innerHTML = '<i class="ti ti-alert-triangle text-danger"></i> <span>OTP has expired. Click Resend below.</span>';
                    btnResendOtp.removeAttribute('disabled');
                    btnResendOtp.classList.remove('disabled');
                    btnResendOtp.classList.add('fw-bold');
                }
            }

            updateTimerUI();

            if (remainingSeconds > 0) {
                const timerInterval = setInterval(function () {
                    remainingSeconds--;
                    updateTimerUI();

                    if (remainingSeconds <= 0) {
                        clearInterval(timerInterval);
                    }
                }, 1000);
            }

            if (resendOtpForm) {
                resendOtpForm.addEventListener('submit', function (e) {
                    if (btnResendOtp.hasAttribute('disabled') || btnResendOtp.classList.contains('disabled')) {
                        e.preventDefault();
                        return false;
                    }
                    btnResendOtp.setAttribute('disabled', 'disabled');
                    btnResendOtp.classList.add('disabled');
                    btnResendOtp.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Sending OTP...';
                });
            }
        });
    </script>
@endsection

