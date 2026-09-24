<style>
    /* ========================================================
       PREMIUM ADMIN DASHBOARD STYLES (HIGH EYE-CATCHING)
       ======================================================== */
    .admin-dashboard-root {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        color: #1e293b;
    }

    /* Stat Cards */
    .adm-stat-card {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03), 0 4px 16px rgba(0, 0, 0, 0.02);
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        cursor: pointer;
        text-decoration: none !important;
        overflow: hidden;
    }

    .adm-stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px -4px rgba(15, 23, 42, 0.08) !important;
        border-color: #cbd5e1;
    }

    .adm-icon-box {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.55rem;
        flex-shrink: 0;
        transition: transform 0.25s ease;
    }

    .adm-stat-card:hover .adm-icon-box {
        transform: scale(1.06);
    }

    /* General Admin Card */
    .adm-card {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03), 0 4px 16px rgba(0, 0, 0, 0.02);
        transition: all 0.25s ease;
        display: flex;
        flex-direction: column;
    }

    .adm-card:hover {
        box-shadow: 0 8px 24px -2px rgba(15, 23, 42, 0.06);
    }

    .adm-card-header {
        padding: 16px 20px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: transparent;
    }

    .adm-card-title {
        font-size: 1.02rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
        letter-spacing: -0.2px;
    }

    .adm-view-all-pill {
        font-size: 0.76rem;
        font-weight: 700;
        color: #2563eb;
        background: #eff6ff;
        padding: 4px 12px;
        border-radius: 8px;
        text-decoration: none !important;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 3px;
    }

    .adm-view-all-pill:hover {
        background: #dbeafe;
        color: #1d4ed8;
        transform: translateX(2px);
    }

    /* Quick Actions */
    .adm-action-item {
        display: flex;
        align-items: center;
        padding: 9px 12px;
        border-radius: 12px;
        text-decoration: none !important;
        color: #1e293b !important;
        font-weight: 600;
        font-size: 0.86rem;
        background: #f8fafc;
        border: 1px solid #f1f5f9;
        transition: all 0.2s ease;
    }

    .adm-action-item:hover {
        background: #ffffff !important;
        border-color: #3b82f6;
        color: #2563eb !important;
        transform: translateX(4px);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.08);
    }

    .adm-action-icon {
        width: 34px;
        height: 34px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        font-size: 1.15rem;
        flex-shrink: 0;
    }

    /* Tables */
    .adm-table {
        margin: 0;
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .adm-table thead th {
        background: #f8fafc;
        color: #64748b;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        padding: 10px 16px;
        border-bottom: 1px solid #edf2f7;
    }

    .adm-table tbody tr {
        transition: background 0.15s ease;
        border-bottom: 1px solid #f8fafc;
    }

    .adm-table tbody tr:hover {
        background: #fbfcfd;
    }

    .adm-table tbody tr:last-child {
        border-bottom: none;
    }

    .adm-table tbody td {
        padding: 11px 16px;
        vertical-align: middle;
        font-size: 0.82rem;
    }

    /* Status Pills */
    .adm-pill-present {
        background: #dcfce7 !important;
        color: #15803d !important;
        font-weight: 700;
        font-size: 0.72rem;
        padding: 4px 11px;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .adm-pill-present::before {
        content: "";
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #16a34a;
    }

    .adm-pill-absent {
        background: #fee2e2 !important;
        color: #b91c1c !important;
        font-weight: 700;
        font-size: 0.72rem;
        padding: 4px 11px;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .adm-pill-absent::before {
        content: "";
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #ef4444;
    }

    .adm-pill-leave {
        background: #fff7ed !important;
        color: #c2410c !important;
        font-weight: 700;
        font-size: 0.72rem;
        padding: 4px 11px;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    /* Avatars */
    .adm-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-weight: 700;
        font-size: 0.76rem;
        flex-shrink: 0;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08);
        border: 2px solid #ffffff;
    }

    /* Circular Rounder Punch Button & 360 Rotating Line (for Employee View) */
    .punch-circle-container {
        position: relative;
        width: 96px;
        height: 96px;
        display: flex;
        align-items: center;
        justify-content: center;
        user-select: none;
        -webkit-user-select: none;
        touch-action: manipulation;
    }

    .punch-rotating-svg {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 10;
        transform: rotate(-90deg);
    }

    .punch-track {
        fill: none;
        stroke: rgba(0, 0, 0, 0.08);
        stroke-width: 4.5;
    }

    .punch-rotating-line {
        fill: none;
        stroke-width: 5;
        stroke-linecap: round;
        stroke-dasharray: 276.46;
        stroke-dashoffset: 276.46;
        transition: stroke-dashoffset 0.25s ease-out, filter 0.25s ease;
        filter: drop-shadow(0 0 5px currentColor);
    }

    .punch-circle-container.is-holding .punch-rotating-line {
        transition: stroke-dashoffset 2000ms linear, filter 0.3s ease !important;
        stroke-dashoffset: 0 !important;
        filter: drop-shadow(0 0 12px currentColor) !important;
    }

    .punch-circle-container.is-holding .punch-circle-btn {
        transform: scale(0.92) !important;
        box-shadow: 0 0 22px rgba(16, 185, 129, 0.45) !important;
    }

    .punch-circle-container.is-animating .punch-rotating-svg {
        animation: punchSvgSuccessSpin 0.6s ease-out forwards !important;
    }

    @keyframes punchSvgSuccessSpin {
        0% { transform: rotate(-90deg) scale(1); }
        50% { transform: rotate(180deg) scale(1.08); }
        100% { transform: rotate(270deg) scale(1); }
    }

    .punch-circle-btn {
        position: relative;
        width: 82px;
        height: 82px;
        border-radius: 50%;
        border: none;
        padding: 0;
        background: transparent;
        cursor: pointer;
        outline: none !important;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s ease;
    }

    .punch-circle-btn:hover { transform: scale(1.05); }
    .punch-circle-btn:active, .punch-circle-btn.is-pressing { transform: scale(0.92); }

    .punch-pulse-ring {
        position: absolute;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        pointer-events: none;
        z-index: 1;
        animation: punchPulse 2.6s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
    }

    @keyframes punchPulse {
        0% { transform: scale(0.85); opacity: 0.8; }
        70% { transform: scale(1.3); opacity: 0; }
        100% { transform: scale(1.3); opacity: 0; }
    }

    .punch-circle-btn.punch-btn-in .punch-pulse-ring { background: rgba(16, 185, 129, 0.35); }
    .punch-circle-btn.punch-btn-out .punch-pulse-ring { background: rgba(239, 68, 68, 0.35); }

    .punch-halo-outer {
        position: absolute;
        width: 82px;
        height: 82px;
        border-radius: 50%;
        z-index: 2;
        transition: all 0.35s ease;
    }

    .punch-circle-btn.punch-btn-in .punch-halo-outer {
        background: radial-gradient(circle, rgba(16, 185, 129, 0.28) 0%, rgba(16, 185, 129, 0.06) 70%, transparent 100%);
        box-shadow: 0 0 18px rgba(16, 185, 129, 0.3);
    }

    .punch-circle-btn.punch-btn-out .punch-halo-outer {
        background: radial-gradient(circle, rgba(239, 68, 68, 0.28) 0%, rgba(239, 68, 68, 0.06) 70%, transparent 100%);
        box-shadow: 0 0 18px rgba(239, 68, 68, 0.3);
    }

    .punch-halo-inner {
        position: absolute;
        width: 70px;
        height: 70px;
        border-radius: 50%;
        z-index: 3;
        transition: all 0.35s ease;
    }

    .punch-circle-btn.punch-btn-in .punch-halo-inner { background: rgba(16, 185, 129, 0.25); }
    .punch-circle-btn.punch-btn-out .punch-halo-inner { background: rgba(239, 68, 68, 0.25); }

    .punch-core {
        position: relative;
        width: 62px;
        height: 62px;
        border-radius: 50%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        z-index: 4;
        transition: all 0.35s ease;
        border: 2px solid rgba(255, 255, 255, 0.5);
    }

    .punch-circle-btn.punch-btn-in .punch-core {
        background: linear-gradient(135deg, #10b981 0%, #059669 60%, #047857 100%);
        box-shadow: 0 5px 16px rgba(16, 185, 129, 0.45), inset 0 2px 4px rgba(255, 255, 255, 0.35);
    }

    .punch-circle-btn.punch-btn-out .punch-core {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 60%, #b91c1c 100%);
        box-shadow: 0 5px 16px rgba(239, 68, 68, 0.45), inset 0 2px 4px rgba(255, 255, 255, 0.35);
    }

    .punch-icon-wrap { display: flex; align-items: center; justify-content: center; line-height: 1; }
    .punch-tap-icon { width: 22px; height: 22px; margin-bottom: 2px; filter: drop-shadow(0 1px 2px rgba(0,0,0,0.25)); }
    .punch-subtext { font-size: 0.62rem; font-weight: 800; letter-spacing: 0.3px; color: rgba(255, 255, 255, 0.98); margin-top: 1px; text-transform: uppercase; }
</style>

{{-- ================================================================= --}}
{{-- 1. COMPANY MAIN ADMIN DASHBOARD (EXACT SAAS STANDARD & EYE-CATCHY) --}}
{{-- ================================================================= --}}
@if (!empty($adminDashboardData))
<div class="admin-dashboard-root">
    {{-- Top Welcome Back Banner --}}
    <div class="card border-0 mb-3 mb-xl-4 position-relative overflow-hidden shadow-sm" 
         style="background: linear-gradient(90deg, #1e40af 0%, #2563eb 50%, #3b82f6 100%); border-radius: 16px; color: #ffffff;">
        {{-- Modern cityscape skyline silhouette --}}
        <div class="position-absolute end-0 bottom-0 d-none d-md-block" style="width: 50%; height: 100%; pointer-events: none; opacity: 0.14;">
            <svg viewBox="0 0 500 130" preserveAspectRatio="none" style="width: 100%; height: 100%; fill: #ffffff;">
                <rect x="20" y="70" width="28" height="60" rx="2" />
                <rect x="55" y="35" width="24" height="95" rx="2" />
                <polygon points="55,35 67,15 79,35" />
                <rect x="88" y="55" width="35" height="75" rx="2" />
                <rect x="130" y="25" width="30" height="105" rx="2" />
                <polygon points="130,25 145,10 160,25" />
                <rect x="170" y="65" width="28" height="65" rx="2" />
                <rect x="205" y="40" width="40" height="90" rx="2" />
                <rect x="255" y="15" width="28" height="115" rx="2" />
                <polygon points="255,15 269,2 283,15" />
                <rect x="292" y="50" width="36" height="80" rx="2" />
                <rect x="338" y="30" width="32" height="100" rx="2" />
                <rect x="380" y="60" width="28" height="70" rx="2" />
                <rect x="418" y="45" width="32" height="85" rx="2" />
                <polygon points="418,45 434,28 450,45" />
                <rect x="460" y="65" width="35" height="65" rx="2" />
            </svg>
        </div>

        <div class="card-body py-3.5 px-4 position-relative">
            <div class="row align-items-center">
                <div class="col-12 col-md-7 mb-2 mb-md-0">
                    <h2 class="text-white fw-bold mb-1" style="font-size: 1.75rem; letter-spacing: -0.3px;">
                        Welcome Back, Admin!
                    </h2>
                    <p class="text-white mb-0" style="opacity: 0.88; font-size: 0.92rem; font-weight: 500;">
                        Here is your complete organization overview.
                    </p>
                </div>

                <div class="col-12 col-md-5 d-flex justify-content-md-end">
                    <div class="d-inline-flex align-items-center gap-3 py-2 px-3 rounded-3" 
                         style="background: rgba(255, 255, 255, 0.16); backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px); border: 1px solid rgba(255, 255, 255, 0.32); border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,0.06);">
                        <div class="d-flex align-items-center justify-content-center text-white rounded-3" 
                             style="width: 38px; height: 38px; background: rgba(255, 255, 255, 0.22); border-radius: 10px;">
                            <i class="ti ti-calendar fs-4"></i>
                        </div>
                        <div>
                            <div class="text-white fw-semibold" style="font-size: 0.80rem; opacity: 0.92; line-height: 1.2;">
                                {{ $adminDashboardData['current_date'] }}
                            </div>
                            <div class="text-white fw-extrabold" id="admin-live-clock" style="font-size: 1.15rem; letter-spacing: 0.4px; line-height: 1.2; margin-top: 2px;">
                                {{ $adminDashboardData['current_time'] }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 4 Key Metrics Stat Cards (Top Row) --}}
    <div class="row g-3 mb-3 mb-xl-4">
        {{-- Card 1: Total Employees --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <a href="{{ route('employees.index') }}" class="adm-stat-card d-block p-3.5 h-100">
                <div class="d-flex align-items-center">
                    <div class="adm-icon-box me-3" style="background: #eff6ff; color: #2563eb;">
                        <i class="ti ti-users"></i>
                    </div>
                    <div class="flex-grow-1">
                        <span class="text-muted fw-semibold d-block mb-1" style="font-size: 0.82rem;">Total Employees</span>
                        <h3 class="mb-0 fw-extrabold text-dark" style="font-size: 1.95rem; line-height: 1;">
                            {{ $adminDashboardData['total_employees'] }}
                        </h3>
                        <div class="mt-1 d-flex align-items-center gap-1" style="font-size: 0.76rem; color: #10b981; font-weight: 700;">
                            <i class="ti ti-arrow-up-right"></i> +{{ $adminDashboardData['new_this_month'] }} this month
                        </div>
                    </div>
                </div>
            </a>
        </div>

        {{-- Card 2: Present Today --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <a href="{{ route('daily-attendance-report.index') }}?status=present" class="adm-stat-card d-block p-3.5 h-100">
                <div class="d-flex align-items-center">
                    <div class="adm-icon-box me-3" style="background: #ecfdf5; color: #10b981;">
                        <i class="ti ti-target"></i>
                    </div>
                    <div class="flex-grow-1">
                        <span class="text-muted fw-semibold d-block mb-1" style="font-size: 0.82rem;">Present Today</span>
                        <h3 class="mb-0 fw-extrabold text-dark" style="font-size: 1.95rem; line-height: 1;">
                            {{ $adminDashboardData['present_today'] }}
                        </h3>
                        <div class="mt-1 d-flex align-items-center gap-1" style="font-size: 0.76rem; color: #10b981; font-weight: 700;">
                            <span>{{ $adminDashboardData['present_pct'] }}% Present</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        {{-- Card 3: Absent Today --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <a href="{{ route('daily-attendance-report.index') }}?status=absent" class="adm-stat-card d-block p-3.5 h-100">
                <div class="d-flex align-items-center">
                    <div class="adm-icon-box me-3" style="background: #fef2f2; color: #ef4444;">
                        <i class="ti ti-clock-pause"></i>
                    </div>
                    <div class="flex-grow-1">
                        <span class="text-muted fw-semibold d-block mb-1" style="font-size: 0.82rem;">Absent Today</span>
                        <h3 class="mb-0 fw-extrabold text-dark" style="font-size: 1.95rem; line-height: 1;">
                            {{ $adminDashboardData['absent_today'] }}
                        </h3>
                        <div class="mt-1 d-flex align-items-center gap-1" style="font-size: 0.76rem; color: #ef4444; font-weight: 700;">
                            <span>{{ $adminDashboardData['absent_pct'] }}% Absent</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        {{-- Card 4: On Leave --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <a href="{{ route('leave-application.index') }}" class="adm-stat-card d-block p-3.5 h-100">
                <div class="d-flex align-items-center">
                    <div class="adm-icon-box me-3" style="background: #fff7ed; color: #f97316;">
                        <i class="ti ti-coffee"></i>
                    </div>
                    <div class="flex-grow-1">
                        <span class="text-muted fw-semibold d-block mb-1" style="font-size: 0.82rem;">On Leave</span>
                        <h3 class="mb-0 fw-extrabold text-dark" style="font-size: 1.95rem; line-height: 1;">
                            {{ $adminDashboardData['on_leave_today'] }}
                        </h3>
                        <div class="mt-1 d-flex align-items-center gap-1" style="font-size: 0.76rem; color: #f97316; font-weight: 700;">
                            <span>{{ $adminDashboardData['on_leave_pct'] }}% On Leave</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- Middle Section: Department Wise Bar Chart | Attendance Donut Chart | Quick Actions --}}
    <div class="row g-3 mb-3 mb-xl-4 align-items-stretch">
        {{-- Department Wise Employee Bar Chart --}}
        <div class="col-12 col-xl-5 col-lg-5 d-flex flex-column">
            <div class="adm-card h-100" style="min-height: 345px;">
                <div class="adm-card-header">
                    <h5 class="adm-card-title">
                        Department Wise Employee
                    </h5>
                    <div class="d-flex align-items-center gap-1.5">
                        <span style="width: 8px; height: 8px; border-radius: 50%; background: #3b82f6;"></span>
                        <small class="text-muted fw-bold" style="font-size: 0.76rem;">Employees</small>
                    </div>
                </div>

                <div class="card-body p-3.5 d-flex flex-column justify-content-end flex-grow-1">
                    {{-- Y-Axis scale 160, 120, 80, 40, 0 matching mockup --}}
                    <div class="d-flex align-items-stretch" style="height: 195px;">
                        <div class="d-flex flex-column justify-content-between text-end pe-2.5 text-muted fw-bold" style="font-size: 0.68rem; width: 28px; user-select: none;">
                            <span>160</span>
                            <span>120</span>
                            <span>80</span>
                            <span>40</span>
                            <span>0</span>
                        </div>

                        <div class="position-relative flex-grow-1 d-flex flex-column justify-content-between" style="border-left: 1px solid #e2e8f0;">
                            <div class="w-100" style="border-top: 1px dashed #f1f5f9; height: 0;"></div>
                            <div class="w-100" style="border-top: 1px dashed #f1f5f9; height: 0;"></div>
                            <div class="w-100" style="border-top: 1px dashed #f1f5f9; height: 0;"></div>
                            <div class="w-100" style="border-top: 1px dashed #f1f5f9; height: 0;"></div>
                            <div class="w-100" style="border-top: 1.5px solid #cbd5e1; height: 0;"></div>

                            {{-- Absolute Bar Columns --}}
                            <div class="position-absolute top-0 start-0 end-0 bottom-0 d-flex align-items-end justify-content-around px-1">
                                @foreach ($adminDashboardData['department_stats'] as $ds)
                                    @php
                                        $barHeight = max(24, round(($ds['count'] / 160) * 160));
                                    @endphp
                                    <div class="d-flex flex-column align-items-center flex-grow-1" style="min-width: 0; max-width: 44px; z-index: 2;">
                                        <span class="fw-extrabold text-dark mb-1" style="font-size: 0.76rem;">{{ $ds['count'] }}</span>
                                        <div style="width: 100%; max-width: 22px; height: {{ $barHeight }}px; background: {{ $ds['color'] }}; border-radius: 5px 5px 0 0; transition: height 0.4s ease; box-shadow: 0 3px 6px {{ $ds['color'] }}35;"></div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- X-Axis Labels --}}
                    <div class="d-flex justify-content-around mt-2 ps-4">
                        @foreach ($adminDashboardData['department_stats'] as $ds)
                            <div class="text-center text-truncate flex-grow-1" style="max-width: 44px;" title="{{ $ds['name'] }}">
                                <span class="text-muted fw-bold" style="font-size: 0.70rem;">{{ $ds['name'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Attendance Overview (This Month) Donut Chart --}}
        <div class="col-12 col-xl-4 col-lg-4 d-flex flex-column">
            <div class="adm-card h-100" style="min-height: 345px;">
                <div class="adm-card-header">
                    <h5 class="adm-card-title">
                        Attendance Overview (This Month)
                    </h5>
                </div>

                <div class="card-body p-3.5 d-flex align-items-center justify-content-between flex-grow-1">
                    {{-- Circular Donut Graphic --}}
                    <div class="position-relative d-flex align-items-center justify-content-center" style="width: 145px; height: 145px; min-width: 145px;">
                        @php
                            $pDeg = ($adminDashboardData['present_pct'] / 100) * 360;
                            $aDeg = ($adminDashboardData['absent_pct'] / 100) * 360;
                            $pEnd = $pDeg;
                            $aEnd = $pEnd + $aDeg;
                        @endphp
                        <div style="width: 140px; height: 140px; border-radius: 50%; background: conic-gradient(#10b981 0deg {{ $pEnd }}deg, #f43f5e {{ $pEnd }}deg {{ $aEnd }}deg, #f59e0b {{ $aEnd }}deg 360deg); display: flex; align-items: center; justify-content: center; box-shadow: 0 6px 18px rgba(0,0,0,0.06);">
                            <div style="width: 96px; height: 96px; border-radius: 50%; background: #ffffff; display: flex; flex-direction: column; align-items: center; justify-content: center; box-shadow: inset 0 2px 6px rgba(0,0,0,0.06);">
                                <span class="fw-extrabold text-dark" style="font-size: 1.45rem; line-height: 1;">{{ $adminDashboardData['present_pct'] }}%</span>
                                <span class="text-muted fw-bold" style="font-size: 0.72rem; margin-top: 3px;">Present</span>
                            </div>
                        </div>
                    </div>

                    {{-- Donut Legend on Right --}}
                    <div class="d-flex flex-column gap-2.5 ps-3 flex-grow-1">
                        <div class="d-flex align-items-center justify-content-between p-2 rounded-3" style="background: #f8fafc; border: 1px solid #f1f5f9;">
                            <div class="d-flex align-items-center gap-2">
                                <span style="width: 10px; height: 10px; border-radius: 50%; background: #10b981; box-shadow: 0 0 6px rgba(16, 185, 129, 0.5);"></span>
                                <span class="fw-bold text-dark" style="font-size: 0.84rem;">Present</span>
                            </div>
                            <span class="text-dark fw-extrabold" style="font-size: 0.82rem;">
                                {{ $adminDashboardData['present_today'] }} <small class="text-muted fw-semibold">({{ $adminDashboardData['present_pct'] }}%)</small>
                            </span>
                        </div>

                        <div class="d-flex align-items-center justify-content-between p-2 rounded-3" style="background: #f8fafc; border: 1px solid #f1f5f9;">
                            <div class="d-flex align-items-center gap-2">
                                <span style="width: 10px; height: 10px; border-radius: 50%; background: #f43f5e; box-shadow: 0 0 6px rgba(244, 63, 94, 0.5);"></span>
                                <span class="fw-bold text-dark" style="font-size: 0.84rem;">Absent</span>
                            </div>
                            <span class="text-dark fw-extrabold" style="font-size: 0.82rem;">
                                {{ $adminDashboardData['absent_today'] }} <small class="text-muted fw-semibold">({{ $adminDashboardData['absent_pct'] }}%)</small>
                            </span>
                        </div>

                        <div class="d-flex align-items-center justify-content-between p-2 rounded-3" style="background: #f8fafc; border: 1px solid #f1f5f9;">
                            <div class="d-flex align-items-center gap-2">
                                <span style="width: 10px; height: 10px; border-radius: 50%; background: #f59e0b; box-shadow: 0 0 6px rgba(245, 158, 11, 0.5);"></span>
                                <span class="fw-bold text-dark" style="font-size: 0.84rem;">On Leave</span>
                            </div>
                            <span class="text-dark fw-extrabold" style="font-size: 0.82rem;">
                                {{ $adminDashboardData['on_leave_today'] }} <small class="text-muted fw-semibold">({{ $adminDashboardData['on_leave_pct'] }}%)</small>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="col-12 col-xl-3 col-lg-3 d-flex flex-column">
            <div class="adm-card h-100" style="min-height: 345px;">
                <div class="adm-card-header">
                    <h5 class="adm-card-title">
                        Quick Actions
                    </h5>
                </div>

                <div class="card-body p-3 d-flex flex-column justify-content-between flex-grow-1">
                    <a href="{{ route('employees.create') }}" class="adm-action-item">
                        <div class="adm-action-icon" style="background: #eff6ff; color: #2563eb;">
                            <i class="ti ti-user-plus"></i>
                        </div>
                        <span>Add Employee</span>
                    </a>

                    <a href="{{ route('attendance.index') }}" class="adm-action-item">
                        <div class="adm-action-icon" style="background: #f5f3ff; color: #8b5cf6;">
                            <i class="ti ti-checkbox"></i>
                        </div>
                        <span>Mark Attendance</span>
                    </a>

                    <a href="{{ route('leave-application.index') }}" class="adm-action-item">
                        <div class="adm-action-icon" style="background: #fff7ed; color: #f97316;">
                            <i class="ti ti-calendar-plus"></i>
                        </div>
                        <span>Apply Leave</span>
                    </a>

                    <a href="{{ route('salary-calculation.index') }}" class="adm-action-item">
                        <div class="adm-action-icon" style="background: #fefce8; color: #ca8a04;">
                            <i class="ti ti-credit-card"></i>
                        </div>
                        <span>Generate Salary</span>
                    </a>

                    <a href="{{ route('daily-attendance-report.index') }}" class="adm-action-item">
                        <div class="adm-action-icon" style="background: #eef2ff; color: #6366f1;">
                            <i class="ti ti-chart-bar"></i>
                        </div>
                        <span>View Reports</span>
                    </a>

                    <a href="{{ route('employees.index') }}" class="adm-action-item">
                        <div class="adm-action-icon" style="background: #faf5ff; color: #a855f7;">
                            <i class="ti ti-address-book"></i>
                        </div>
                        <span>Employee Directory</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom Section: Today's Attendance (Live) | Upcoming Leaves (Table) | Birthdays & Work Anniversaries --}}
    <div class="row g-3 align-items-stretch">
        {{-- Today's Attendance (Live) --}}
        <div class="col-12 col-xl-4 col-lg-4 d-flex flex-column">
            <div class="adm-card h-100" style="min-height: 310px;">
                <div class="adm-card-header">
                    <h5 class="adm-card-title">
                        Today's Attendance (Live)
                    </h5>
                    <a href="{{ route('daily-attendance-report.index') }}" class="adm-view-all-pill">
                        View All
                    </a>
                </div>

                <div class="card-body p-0 d-flex flex-column flex-grow-1">
                    <div class="table-responsive flex-grow-1">
                        <table class="adm-table">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Department</th>
                                    <th>Time</th>
                                    <th class="text-end">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($adminDashboardData['today_live_attendance'] as $la)
                                    @php
                                        $initials = strtoupper(substr($la['name'], 0, 1));
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="position-relative flex-shrink-0" style="width: 32px; height: 32px;">
                                                    @if (!empty($la['avatar']))
                                                        <img src="{{ $la['avatar'] }}" 
                                                             alt="{{ $la['name'] }}" 
                                                             class="rounded-circle" 
                                                             style="width: 32px; height: 32px; object-fit: cover; border: 1.5px solid #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.1);"
                                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                    @endif
                                                    <div class="rounded-circle text-white fw-bold {{ !empty($la['avatar']) ? 'd-none' : 'd-flex' }}" 
                                                         style="width: 32px; height: 32px; background: linear-gradient(135deg, #3b82f6, #1d4ed8); font-size: 0.74rem; align-items: center; justify-content: center; border: 1.5px solid #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                                                        {{ $initials }}
                                                    </div>
                                                </div>
                                                <span class="fw-bold text-dark text-truncate" style="max-width: 105px;" title="{{ $la['name'] }}">
                                                    {{ $la['name'] }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="text-muted fw-semibold text-truncate" style="max-width: 85px;">
                                            {{ $la['department'] }}
                                        </td>
                                        <td class="text-muted fw-semibold">
                                            {{ $la['time'] }}
                                        </td>
                                        <td class="text-end">
                                            @if ($la['badge'] === 'present')
                                                <span class="adm-pill-present">Present</span>
                                            @else
                                                <span class="adm-pill-absent">Absent</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Upcoming Leaves (Table Format Matching Reference) --}}
        <div class="col-12 col-xl-5 col-lg-5 d-flex flex-column">
            <div class="adm-card h-100" style="min-height: 310px;">
                <div class="adm-card-header">
                    <h5 class="adm-card-title">
                        Upcoming Leaves
                    </h5>
                    <a href="{{ route('leave-application.index') }}" class="adm-view-all-pill">
                        View All
                    </a>
                </div>

                <div class="card-body p-0 d-flex flex-column flex-grow-1">
                    <div class="table-responsive flex-grow-1">
                        <table class="adm-table">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Department</th>
                                    <th>Leave Type</th>
                                    <th class="text-end">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($adminDashboardData['upcoming_leaves'] as $ul)
                                    @php
                                        $initials = strtoupper(substr($ul['name'], 0, 1));
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="position-relative flex-shrink-0" style="width: 32px; height: 32px;">
                                                    @if (!empty($ul['avatar']))
                                                        <img src="{{ $ul['avatar'] }}" 
                                                             alt="{{ $ul['name'] }}" 
                                                             class="rounded-circle" 
                                                             style="width: 32px; height: 32px; object-fit: cover; border: 1.5px solid #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.1);"
                                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                    @endif
                                                    <div class="rounded-circle text-white fw-bold {{ !empty($ul['avatar']) ? 'd-none' : 'd-flex' }}" 
                                                         style="width: 32px; height: 32px; background: linear-gradient(135deg, #f97316, #ea580c); font-size: 0.74rem; align-items: center; justify-content: center; border: 1.5px solid #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                                                        {{ $initials }}
                                                    </div>
                                                </div>
                                                <span class="fw-bold text-dark text-truncate" style="max-width: 105px;" title="{{ $ul['name'] }}">
                                                    {{ $ul['name'] }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="text-muted fw-semibold text-truncate" style="max-width: 90px;">
                                            {{ $ul['department'] }}
                                        </td>
                                        <td class="text-dark fw-semibold text-truncate" style="max-width: 100px;">
                                            {{ $ul['leave_type'] }}
                                        </td>
                                        <td class="text-end text-muted fw-semibold">
                                            {{ $ul['date'] }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Birthdays & Work Anniversaries --}}
        <div class="col-12 col-xl-3 col-lg-3 d-flex flex-column">
            <div class="adm-card h-100" style="min-height: 310px;">
                <div class="adm-card-header">
                    <h5 class="adm-card-title" style="font-size: 0.95rem;">
                        Birthdays & Anniversaries
                    </h5>
                    <a href="{{ route('employees.index') }}" class="adm-view-all-pill">
                        View All
                    </a>
                </div>

                <div class="card-body p-3 d-flex flex-column justify-content-between flex-grow-1">
                    @forelse ($adminDashboardData['birthdays_anniversaries'] as $ba)
                        @php
                            $initials = strtoupper(substr($ba['name'], 0, 1));
                        @endphp
                        <div class="d-flex align-items-center justify-content-between p-2 rounded-3" style="background: #f8fafc; border: 1px solid #f1f5f9; transition: all 0.2s ease;">
                            <div class="d-flex align-items-center gap-2">
                                <div class="position-relative flex-shrink-0" style="width: 32px; height: 32px;">
                                    @if (!empty($ba['avatar']))
                                        <img src="{{ $ba['avatar'] }}" 
                                             alt="{{ $ba['name'] }}" 
                                             class="rounded-circle" 
                                             style="width: 32px; height: 32px; object-fit: cover; border: 1.5px solid #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.1);"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    @endif
                                    <div class="rounded-circle text-white fw-bold {{ !empty($ba['avatar']) ? 'd-none' : 'd-flex' }}" 
                                         style="width: 32px; height: 32px; background: linear-gradient(135deg, #8b5cf6, #6d28d9); font-size: 0.74rem; align-items: center; justify-content: center; border: 1.5px solid #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                                        {{ $initials }}
                                    </div>
                                </div>
                                <div style="max-width: 110px;">
                                    <div class="fw-bold text-dark text-truncate" style="font-size: 0.82rem;" title="{{ $ba['name'] }}">
                                        {{ $ba['name'] }}
                                    </div>
                                    <div class="text-muted fw-semibold" style="font-size: 0.70rem;">{{ $ba['subtitle'] }}</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-center rounded-3" 
                                 style="width: 30px; height: 30px; background: {{ $ba['bg'] }}; color: {{ $ba['color'] }}; flex-shrink: 0;" title="{{ $ba['event'] ?? 'Celebration' }}">
                                <i class="ti {{ $ba['icon'] }} fs-5"></i>
                            </div>
                        </div>
                    @empty
                        <div class="p-4 text-center text-muted small my-auto">
                            <i class="ti ti-gift fs-2 mb-1 d-block text-danger"></i> No celebrations this week.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Live ticking clock for admin header
    setInterval(function() {
        var clockEl = document.getElementById('admin-live-clock');
        if (clockEl) {
            var d = new Date();
            var hours = d.getHours();
            var minutes = d.getMinutes();
            var seconds = d.getSeconds();
            var ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12;
            minutes = minutes < 10 ? '0' + minutes : minutes;
            seconds = seconds < 10 ? '0' + seconds : seconds;
            var strTime = (hours < 10 ? '0' + hours : hours) + ':' + minutes + ':' + seconds + ' ' + ampm;
            clockEl.innerText = strTime;
        }
    }, 1000);
</script>
@endif

{{-- ========================================== --}}
{{-- 2. EMPLOYEE PERSONAL / SUPERVISOR DASHBOARD --}}
{{-- ========================================== --}}
@if (!empty($employeeStats) && empty($adminDashboardData))
    @php
        $hour = (int) \Carbon\Carbon::now()->format('H');
        $greeting = 'Good Morning';
        if ($hour >= 12 && $hour < 17) {
            $greeting = 'Good Afternoon';
        } elseif ($hour >= 17) {
            $greeting = 'Good Evening';
        }
        $pState = $employeeStats['punch_state'] ?? 'out';
    @endphp

    {{-- Top Greeting & Interactive Punch Banner --}}
    <div class="card border-0 mb-4 position-relative overflow-hidden" 
         style="background: linear-gradient(135deg, #eef4ff 0%, #e0eaff 50%, #dce9fe 100%); border-radius: 18px; border: 1px solid #d6e4fc; box-shadow: 0 4px 20px rgba(37, 99, 235, 0.05);">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-12 col-lg-8">
                    <div class="mb-3">
                        <span class="text-secondary fw-semibold" style="font-size: 1.1rem;">{{ $greeting }},</span>
                        <h2 class="mb-1 fw-extrabold" style="color: #1e3a8a; font-size: 1.85rem; letter-spacing: -0.3px;">
                            {{ $employeeStats['proper_name'] ?? ($employeeStats['employee']->full_name ?? 'Employee') }}!
                        </h2>
                        <p class="text-muted mb-0 fw-medium" style="font-size: 0.95rem;">Have a productive day at work.</p>
                    </div>

                    {{-- White Interactive Punch Capsule Card --}}
                    <div class="d-inline-flex align-items-center p-3 px-4 bg-white rounded-4 shadow-sm" style="border: 1px solid #edf2f7;">
                        {{-- Circular Punch Button with Glowing Pulse & 360-Degree Rotating Border --}}
                        <div class="punch-circle-container" id="punch-container" style="width: 96px; height: 96px; min-width: 96px; margin: 0; position: relative; display: flex; align-items: center; justify-content: center;">
                            {{-- 360 Degree Rotating Circular SVG Line --}}
                            <svg class="punch-rotating-svg" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="44" class="punch-track"></circle>
                                <circle id="punch-rotating-line" class="punch-rotating-line" cx="50" cy="50" r="44" 
                                    stroke="{{ $pState === 'in' ? '#ef4444' : '#10b981' }}"></circle>
                            </svg>

                            <button type="button" id="dashboard-punch-btn" 
                                class="punch-circle-btn {{ $pState === 'in' ? 'punch-btn-out' : 'punch-btn-in' }}" 
                                data-punch-state="{{ $pState }}"
                                style="width: 82px; height: 82px;">
                                <span class="punch-pulse-ring"></span>
                                <span class="punch-halo-outer" style="width: 82px; height: 82px;"></span>
                                <span class="punch-halo-inner" style="width: 70px; height: 70px;"></span>
                                <span class="punch-core" style="width: 62px; height: 62px;">
                                    <span class="punch-icon-wrap" id="punch-icon-wrap">
                                        @if($pState === 'in')
                                            <svg class="punch-tap-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="width: 22px; height: 22px;">
                                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                                <polyline points="16 17 21 12 16 7"></polyline>
                                                <line x1="21" y1="12" x2="9" y2="12"></line>
                                            </svg>
                                        @else
                                            <svg class="punch-tap-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="width: 22px; height: 22px;">
                                                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                                                <polyline points="10 17 15 12 10 7"></polyline>
                                                <line x1="15" y1="12" x2="3" y2="12"></line>
                                            </svg>
                                        @endif
                                    </span>
                                    <span class="punch-subtext" id="punch-btn-sublabel" style="font-size: 0.62rem; font-weight: 800; margin-top: 1px;">
                                        {{ $pState === 'in' ? 'Punch Out' : 'Punch In' }}
                                    </span>
                                </span>
                            </button>
                        </div>

                        {{-- Vertical Divider --}}
                        <div style="width: 1px; height: 56px; background: #e2e8f0; margin: 0 20px;"></div>

                        {{-- Status Information Text --}}
                        <div class="d-flex flex-column justify-content-center">
                            <div class="d-flex align-items-center mb-1">
                                <span class="text-dark fw-bold me-2" style="font-size: 1.12rem;">You are currently</span>
                                <span id="punch-status-badge" class="badge rounded-pill fw-extrabold px-3 py-1" 
                                    style="font-size: 1.02rem; background: {{ $pState === 'in' ? '#dcfce7' : '#fee2e2' }}; color: {{ $pState === 'in' ? '#15803d' : '#b91c1c' }}; letter-spacing: 0.5px;">
                                    {{ strtoupper($pState) }}
                                </span>
                            </div>
                            <span id="punch-status-desc" class="text-muted fw-medium" style="font-size: 0.92rem;">
                                {{ $pState === 'in' ? 'Hold 2 sec to punch out and end your day' : 'Hold 2 sec to punch in and start your day' }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Right Character Illustration --}}
                <div class="col-12 col-lg-4 d-none d-lg-flex justify-content-end align-items-center">
                    <img src="{{ asset('software/img/illustrations/boy-with-laptop-light.png') }}" 
                         alt="Productive Day" 
                         class="img-fluid" 
                         style="max-height: 180px; object-fit: contain; filter: drop-shadow(0 8px 16px rgba(0,0,0,0.06));">
                </div>
            </div>
        </div>
    </div>

    {{-- 3 Main Stat Cards (Original Rich Card Design) --}}
    <div class="row mb-4">
        {{-- Card 1: Total Attendance --}}
        <div class="col-12 col-md-4 mb-3 mb-md-0">
            <div class="card h-100 border-0 shadow-sm position-relative overflow-hidden stat-card" 
                 style="border-radius: 18px; border-bottom: 5px solid #6366f1 !important; background: #ffffff; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center justify-content-center" 
                             style="width: 44px; height: 44px; border-radius: 12px; background: rgba(99, 102, 241, 0.12); color: #6366f1;">
                            <i class="ti ti-calendar-event fs-3"></i>
                        </div>
                        <span class="badge rounded-pill px-3 py-1 fw-bold" style="background: rgba(99, 102, 241, 0.12); color: #6366f1; font-size: 0.82rem;">
                            Attendance
                        </span>
                    </div>
                    <div class="d-flex align-items-baseline gap-2 my-1">
                        <h1 class="mb-0 fw-extrabold text-primary" style="font-size: 2.35rem; line-height: 1;">{{ $employeeStats['total_present_range'] ?? 0 }}</h1>
                        <span class="fs-5 text-dark fw-bold">Days</span>
                    </div>
                    <h5 class="card-title mb-2 fw-bold text-dark" style="font-size: 1.05rem;">Total Attendance</h5>
                    <div class="d-flex justify-content-between align-items-center pt-2 border-top" style="font-size: 0.86rem;">
                        <span class="text-muted fw-semibold">Month Total:</span>
                        <span class="badge bg-success px-2.5 py-1 fw-bold fs-7 rounded-pill">
                            {{ $employeeStats['total_present_month'] ?? 0 }} Days
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 2: Punch In Count --}}
        <div class="col-12 col-md-4 mb-3 mb-md-0">
            <div class="card h-100 border-0 shadow-sm position-relative overflow-hidden stat-card" 
                 style="border-radius: 18px; border-bottom: 5px solid #10b981 !important; background: #ffffff; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center justify-content-center" 
                             style="width: 44px; height: 44px; border-radius: 12px; background: rgba(16, 185, 129, 0.12); color: #10b981;">
                            <i class="ti ti-login fs-3"></i>
                        </div>
                        <span class="badge rounded-pill px-3 py-1 fw-bold" style="background: rgba(16, 185, 129, 0.12); color: #10b981; font-size: 0.82rem;">
                            Total: {{ $employeeStats['total_punch_in_count'] ?? 0 }}
                        </span>
                    </div>
                    <div class="d-flex align-items-baseline gap-2 my-1">
                        <h1 class="mb-0 fw-extrabold text-success" style="font-size: 2.35rem; line-height: 1;">{{ $employeeStats['total_punch_in_count'] ?? 0 }}</h1>
                        <span class="fs-5 text-dark fw-bold">Punches</span>
                    </div>
                    <h5 class="card-title mb-2 fw-bold text-dark" style="font-size: 1.05rem;">Punch In Count</h5>
                    <div class="d-flex justify-content-between align-items-center pt-2 border-top" style="font-size: 0.86rem;">
                        <span class="text-muted fw-semibold">Today's In Time:</span>
                        <span class="badge bg-{{ $employeeStats['punch_in_time'] ? 'success' : 'label-secondary' }} px-2.5 py-1 fw-bold fs-7 rounded-pill">
                            {{ $employeeStats['punch_in_time'] ?: '--:--' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 3: Punch Out Count --}}
        <div class="col-12 col-md-4">
            <div class="card h-100 border-0 shadow-sm position-relative overflow-hidden stat-card" 
                 style="border-radius: 18px; border-bottom: 5px solid #ef4444 !important; background: #ffffff; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center justify-content-center" 
                             style="width: 44px; height: 44px; border-radius: 12px; background: rgba(239, 68, 68, 0.12); color: #ef4444;">
                            <i class="ti ti-logout fs-3"></i>
                        </div>
                        <span class="badge rounded-pill px-3 py-1 fw-bold" style="background: rgba(239, 68, 68, 0.12); color: #ef4444; font-size: 0.82rem;">
                            Total: {{ $employeeStats['total_punch_out_count'] ?? 0 }}
                        </span>
                    </div>
                    <div class="d-flex align-items-baseline gap-2 my-1">
                        <h1 class="mb-0 fw-extrabold text-danger" style="font-size: 2.35rem; line-height: 1;">{{ $employeeStats['total_punch_out_count'] ?? 0 }}</h1>
                        <span class="fs-5 text-dark fw-bold">Punches</span>
                    </div>
                    <h5 class="card-title mb-2 fw-bold text-dark" style="font-size: 1.05rem;">Punch Out Count</h5>
                    <div class="d-flex justify-content-between align-items-center pt-2 border-top" style="font-size: 0.86rem;">
                        <span class="text-muted fw-semibold">Today's Out Time:</span>
                        <span class="badge bg-{{ $employeeStats['punch_out_time'] ? 'danger' : 'label-secondary' }} px-2.5 py-1 fw-bold fs-7 rounded-pill">
                            {{ $employeeStats['punch_out_time'] ?: '--:--' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Type-wise Leave Balance Section --}}
    <div class="row mb-4">
        <div class="col-12 mb-3">
            <h5 class="mb-0 fw-extrabold text-dark d-flex align-items-center" style="font-size: 1.18rem;">
                <i class="ti ti-calendar-event text-primary me-2 fs-4"></i> Leave Balance (Type-Wise)
            </h5>
        </div>

        @if (!empty($employeeStats['leave_balances']) && count($employeeStats['leave_balances']) > 0)
            @foreach ($employeeStats['leave_balances'] as $leave)
                @php
                    $pal = $leave['palette'] ?? ['bg' => 'rgba(115, 103, 240, 0.1)', 'border' => '#7367f0', 'text' => '#7367f0'];
                @endphp
                <div class="col-12 col-md-6 mb-3">
                    <div class="card h-100 border-0 shadow-sm position-relative overflow-hidden stat-card" 
                         style="border-radius: 16px; border-left: 5px solid {{ $pal['border'] }} !important; background: #ffffff; box-shadow: 0 4px 16px rgba(0,0,0,0.04);">
                        <div class="card-body p-3.5">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="d-flex align-items-center justify-content-center fw-extrabold" 
                                         style="width: 48px; height: 48px; border-radius: 12px; background: {{ $pal['bg'] }}; color: {{ $pal['text'] }}; font-size: 1.15rem; flex-shrink: 0;">
                                        {{ $leave['code'] }}
                                    </div>
                                    <div>
                                        <h5 class="fw-bold text-dark mb-0 text-truncate" style="font-size: 1.05rem;" title="{{ $leave['name'] }}">
                                            {{ $leave['name'] }}
                                        </h5>
                                        <div class="d-flex align-items-baseline gap-1 mt-1">
                                            <h3 class="mb-0 fw-extrabold" style="color: {{ $pal['text'] }}; font-size: 1.55rem; line-height: 1;">
                                                {{ number_format($leave['balance'], 1) }}
                                            </h3>
                                            <span class="text-dark fw-bold ms-1" style="font-size: 0.95rem;">Days Available</span>
                                        </div>
                                    </div>
                                </div>
                                <span class="badge bg-light text-secondary rounded-pill px-3 py-1.5 fw-semibold" style="font-size: 0.76rem; border: 1px solid #e2e8f0;">
                                    {{ $leave['carry_forward'] ? 'Carry Forward' : 'Monthly Accrual' }}
                                </span>
                            </div>

                            <div class="border-top pt-2.5 mt-2.5 d-flex justify-content-between align-items-center text-muted" style="font-size: 0.86rem;">
                                <span>Allocated: <strong class="text-dark fw-bold ms-1">{{ number_format($leave['allocated'], 1) }}</strong></span>
                                <span>Used (FY): <strong class="text-danger fw-bold ms-1">{{ number_format($leave['used_year'], 1) }}</strong></span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="col-12">
                <div class="alert alert-info py-3 px-4 rounded-3 small d-flex align-items-center">
                    <i class="ti ti-info-circle fs-4 me-2"></i> No active leave types configured for your company.
                </div>
            </div>
        @endif
    </div>
@endif