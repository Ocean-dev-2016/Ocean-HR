@extends('software.layout.app')

@section('title', 'Admin Login')

@section('page_leavel_style')
    <link rel="stylesheet" href="{{ asset('software/vendor/libs/@form-validation/form-validation.css') }}" />
    <link rel="stylesheet" href="{{ asset('software/vendor/css/pages/page-auth.css') }}" />
    <style>
        .btn-dull-orange {
            background: linear-gradient(135deg, #ea580c 0%, #d97706 100%) !important;
            border: none !important;
            color: #ffffff !important;
            border-radius: 10px !important;
            box-shadow: 0 4px 14px rgba(234, 88, 12, 0.3) !important;
            transition: all 0.25s ease-in-out !important;
        }

        .btn-dull-orange:hover, .btn-dull-orange:focus {
            background: linear-gradient(135deg, #c2410c 0%, #b45309 100%) !important;
            color: #ffffff !important;
            box-shadow: 0 6px 18px rgba(234, 88, 12, 0.4) !important;
            transform: translateY(-1px);
        }

        .btn-outline-dull-orange {
            border: 2px solid #ea580c !important;
            color: #ea580c !important;
            background: #ffffff !important;
            border-radius: 10px !important;
            transition: all 0.25s ease-in-out !important;
        }

        .btn-outline-dull-orange:hover, .btn-outline-dull-orange:focus {
            background: #fff7ed !important;
            border-color: #c2410c !important;
            color: #c2410c !important;
            transform: translateY(-1px);
        }
        .auth-cover-bg-color {
            background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%) !important;
        }
        .auth-illustration {
            max-height: 75% !important;
            max-width: 75% !important;
            height: auto !important;
            transform: scale(1.05) !important;
            transition: transform 0.3s ease-in-out !important;
        }
    </style>
@endsection

@section('content')
    <!-- Content -->

    <div class="authentication-wrapper authentication-cover authentication-bg">
        <div class="authentication-inner row">
            <!-- /Left Text -->
            <div class="d-none d-lg-flex col-lg-6 p-0">
                <div class="auth-cover-bg auth-cover-bg-color d-flex justify-content-center align-items-center">
                    <img src="{{ asset('software/img/illustrations/auth-login-illustration-light.png') }}"
                        alt="auth-login-cover" class="img-fluid my-5 auth-illustration" />

                    <img src="{{ asset('software/img/illustrations/bg-shape-image-light.png') }}" alt="auth-login-cover"
                        class="platform-bg" />
                </div>
            </div>
            <!-- /Left Text -->

            <!-- Login -->
            <div class="d-flex col-12 col-lg-6 align-items-center p-sm-5 p-4">
                <div class="w-100 mx-auto" style="max-width: 580px;">
                    <!-- Logo -->
                    <div class="app-brand mb-3 text-center d-flex justify-content-center">
                        <a href="{{ route('software.login') }}" class="app-brand-link gap-2 w-100 justify-content-center">
                            <img src="{{ asset('software/img/logo.png') }}" style="max-width: 440px; width: 100%; height: auto; object-fit: contain;" />
                        </a>
                    </div>
                    <!-- /Logo -->
                    <h3 class="mb-2 fw-bold">Welcome to {{ env('APP_NAME') }} 👋</h3>

                    <form id="formAuthentication" class="mb-3" action="{{ route('software.submit.login') }}"
                        method="post">
                        @csrf
                        <div class="mb-3">
                            <label for="app_key" class="form-label">App Key</label>
                            <input type="text" class="form-control" id="app_key" name="app_key"
                                placeholder="Enter your app Key" autofocus value="{{ old('app_key') }}" />
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Username or Email or Phone</label>
                            <input type="text" class="form-control" id="email" name="username"
                                placeholder="Enter your email or username" value="{{ old('username') }}" />
                        </div>
                        <div class="mb-3 form-password-toggle">
                            <div class="d-flex justify-content-between">
                                <label class="form-label" for="password">Password</label>
                            </div>
                            <div class="input-group input-group-merge">
                                <input type="password" id="password" class="form-control" name="password"
                                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                    aria-describedby="password" />
                                <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember-me" />
                                <label class="form-check-label" for="remember-me"> Remember Me </label>
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <button type="submit" class="btn btn-dull-orange w-100 py-2.5 fw-bold">Sign in</button>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('software.register.company') }}" class="btn btn-outline-dull-orange w-100 py-2.5 fw-bold text-nowrap d-flex align-items-center justify-content-center">
                                    <i class="ti ti-building-plus me-1"></i> Register with Demo
                                </a>
                            </div>
                        </div>
                        <div class="divider my-3">
                            <div class="divider-text text-muted small fw-semibold">OR CONTINUE WITH GOOGLE</div>
                        </div>

                        <div class="mb-3">
                            <a href="{{ route('software.auth.google') }}" class="btn btn-outline-secondary w-100 py-2.5 fw-bold d-flex align-items-center justify-content-center border shadow-sm rounded-3 text-nowrap" style="background: #ffffff; color: #1f2937; transition: all 0.2s; font-size: 14px;">
                                <svg class="me-2" width="19" height="19" viewBox="0 0 24 24">
                                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                                </svg>
                                Sign in with Google
                            </a>
                        </div>
                    </form>

                    <footer class="footer text-center py-3">
                        <p class="mb-0 text-muted">
                            Powered By
                            <a href="https://oceaninfotech.co.in/" target="_blank" class="fw-bold" style="color: #ea580c !important; text-decoration: none;">
                                Ocean Infotech
                            </a>
                        </p>
                    </footer>
                    {{-- <p class="text-center">
                    <span>New on our platform?</span>
                    <a href="auth-register-cover.html">
                        <span>Create an account</span>
                    </a>
                </p>

                <div class="divider my-4">
                    <div class="divider-text">or</div>
                </div>

                <div class="d-flex justify-content-center">
                    <a href="javascript:;" class="btn btn-icon btn-label-facebook me-3">
                        <i class="tf-icons fa-brands fa-facebook-f fs-5"></i>
                    </a>

                    <a href="javascript:;" class="btn btn-icon btn-label-google-plus me-3">
                        <i class="tf-icons fa-brands fa-google fs-5"></i>
                    </a>

                    <a href="javascript:;" class="btn btn-icon btn-label-twitter">
                        <i class="tf-icons fa-brands fa-twitter fs-5"></i>
                    </a>
                </div> --}}
                </div>
            </div>
            <!-- /Login -->
        </div>
    </div>

    <!-- / Content -->
@endsection

@section('page_leavel_script')
    <script src="{{ asset('software/js/pages-auth.js') }}"></script>
    <script>
        $(document).ready(function() {
            localStorage.removeItem('fcm_token');
        });
    </script>
@endsection
