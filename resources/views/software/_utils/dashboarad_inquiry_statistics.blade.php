<style>
    .admin-dashboard-root {
        --navy: #0f1c3f;
        --navy-2: #1a2b56;
        --orange: #f28c28;
        --orange-soft: #fff4e8;
        --green: #0d9f6e;
        --red: #e11d48;
        --amber: #d97706;
        --blue: #2563eb;
        --violet: #7c3aed;
        --border: #e2e8f0;
        --muted: #475569;
        --card-bg: #ffffff;
        --soft-bg: #f8fafc;
        --shadow: 0 2px 4px rgba(15, 28, 63, 0.04), 0 12px 28px rgba(15, 28, 63, 0.07);
        color: #0f172a;
        padding-bottom: 2.75rem;
        margin-bottom: 1.25rem;
    }
    .employee-mini-card {
        transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    .employee-mini-card:hover {
        transform: translateY(-3px) !important;
        box-shadow: 0 12px 28px rgba(99, 102, 241, 0.12) !important;
        border-color: #a5b4fc !important;
    }
    .admin-dashboard-root .od-gap { margin-bottom: 1.15rem; }
    .admin-dashboard-root .od-hero {
        background: linear-gradient(118deg, #0b1530 0%, #1a2b56 45%, #25407a 100%);
        border-radius: 18px; color: #fff; position: relative; overflow: hidden; box-shadow: var(--shadow);
    }
    .admin-dashboard-root .od-hero::before {
        content: ""; position: absolute; right: -40px; top: -60px; width: 240px; height: 240px; border-radius: 50%;
        background: radial-gradient(circle, rgba(242,140,40,0.4) 0%, transparent 70%); pointer-events: none;
    }
    .admin-dashboard-root .od-hero::after {
        content: ""; position: absolute; left: 0; bottom: 0; right: 0; height: 4px;
        background: linear-gradient(90deg, var(--orange), #ffb347, #38bdf8, transparent);
    }
    .admin-dashboard-root .od-pulse { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; }
    @media (max-width: 991.98px) { .admin-dashboard-root .od-pulse { grid-template-columns: repeat(2, 1fr); } }
    .admin-dashboard-root .od-pulse-item {
        background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.2);
        border-radius: 14px; padding: 12px 14px; backdrop-filter: blur(8px);
    }
    .admin-dashboard-root .od-pulse-item .lbl { font-size: 0.8rem; font-weight: 700; opacity: 0.9; margin-bottom: 4px; }
    .admin-dashboard-root .od-pulse-item .val { font-size: 1.45rem; font-weight: 800; line-height: 1.15; letter-spacing: -0.02em; }

    .admin-dashboard-root .od-kpi {
        background: var(--card-bg); border: 1px solid var(--border); border-radius: 16px; box-shadow: var(--shadow);
        padding: 18px 16px; text-decoration: none !important; color: inherit !important; display: block; height: 100%;
        transition: transform .2s ease, box-shadow .2s ease; position: relative; overflow: hidden;
    }
    .admin-dashboard-root .od-kpi:hover { transform: translateY(-3px); box-shadow: 0 14px 32px rgba(15, 28, 63, 0.12); }
    .admin-dashboard-root .od-kpi .accent { position: absolute; left: 0; top: 0; bottom: 0; width: 5px; background: var(--accent, var(--blue)); }
    .admin-dashboard-root .od-kpi .ico {
        width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center;
        font-size: 1.4rem; flex-shrink: 0;
    }
    .admin-dashboard-root .od-kpi .title { font-size: 0.88rem; font-weight: 700; color: var(--muted); margin-bottom: 4px; }
    .admin-dashboard-root .od-kpi .num { font-size: 1.85rem; font-weight: 800; line-height: 1.1; letter-spacing: -0.03em; color: #0f172a; }
    .admin-dashboard-root .od-kpi .sub { font-size: 0.82rem; font-weight: 700; margin-top: 6px; }

    .admin-dashboard-root .od-card {
        background: var(--card-bg); border: 1px solid var(--border); border-radius: 16px; box-shadow: var(--shadow);
        display: flex; flex-direction: column; height: 100%;
    }
    .admin-dashboard-root .od-card-h {
        padding: 14px 16px; border-bottom: 1px solid #eef2f7;
        display: flex; align-items: center; justify-content: space-between; gap: 8px;
        border-radius: 16px 16px 0 0; flex-shrink: 0;
    }
    .admin-dashboard-root .od-card-h h5 { margin: 0; font-size: 1.08rem; font-weight: 800; color: #0f172a; letter-spacing: -0.02em; }
    .admin-dashboard-root .od-card-b { padding: 16px; flex: 1 1 auto; }
    .admin-dashboard-root .od-card-b.od-scroll { max-height: 320px; overflow-y: auto; }
    .admin-dashboard-root .od-absent-grid {
        display: grid; grid-template-columns: repeat(auto-fit, minmax(215px, 1fr)); gap: 12px;
    }
    .admin-dashboard-root .od-absent-item {
        display: flex; align-items: center; justify-content: space-between; gap: 8px;
        padding: 10px 12px; border-radius: 12px; background: #fff; border: 1px solid #fecaca;
        min-height: 60px; box-shadow: 0 1px 3px rgba(0,0,0,.03);
        transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
    }
    .admin-dashboard-root .od-absent-item:hover {
        transform: translateY(-2px);
        border-color: #f87171;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.12);
    }
    .admin-dashboard-root .od-link {
        font-size: 0.8rem; font-weight: 750; color: #fff; background: var(--blue);
        padding: 5px 12px; border-radius: 8px; text-decoration: none !important; white-space: nowrap;
    }
    .admin-dashboard-root .od-link:hover { background: #1d4ed8; color: #fff; }

    .admin-dashboard-root .od-meta { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
    @media (max-width: 767.98px) { .admin-dashboard-root .od-meta { grid-template-columns: repeat(2, 1fr); } }
    .admin-dashboard-root .od-meta-item {
        border-radius: 14px; padding: 14px 12px; text-align: center; border: 1px solid transparent; box-shadow: var(--shadow);
    }
    .admin-dashboard-root .od-meta-item .n { font-size: 1.55rem; font-weight: 800; line-height: 1.1; }
    .admin-dashboard-root .od-meta-item .l { font-size: 0.82rem; font-weight: 750; margin-top: 4px; opacity: .85; }

    .admin-dashboard-root .od-hbar { height: 12px; border-radius: 99px; background: #e2e8f0; overflow: hidden; }
    .admin-dashboard-root .od-hbar > span { display: block; height: 100%; border-radius: 99px; }
    .admin-dashboard-root .od-trend { display: flex; align-items: flex-end; justify-content: space-between; gap: 6px; height: 100px; }
    .admin-dashboard-root .od-trend-col { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: flex-end; min-width: 0; }
    .admin-dashboard-root .od-trend-bar {
        width: 100%; max-width: 32px; border-radius: 8px 8px 3px 3px; min-height: 8px;
        background: linear-gradient(180deg, #60a5fa, #1d4ed8);
    }

    .admin-dashboard-root .od-action {
        display: flex; align-items: center; gap: 10px; padding: 11px 12px; border-radius: 12px;
        background: #fff; border: 1px solid var(--border); text-decoration: none !important; color: #0f172a !important;
        font-weight: 750; font-size: 0.92rem; transition: .18s ease;
    }
    .admin-dashboard-root .od-action:hover {
        border-color: #93c5fd; color: var(--blue) !important; transform: translateX(2px);
        box-shadow: 0 4px 12px rgba(37,99,235,.1);
    }
    .admin-dashboard-root .od-action .ai {
        width: 34px; height: 34px; border-radius: 10px; display: flex; align-items: center; justify-content: center;
        font-size: 1.15rem; flex-shrink: 0;
    }

    .admin-dashboard-root .od-table { width: 100%; margin: 0; border-collapse: separate; border-spacing: 0; }
    .admin-dashboard-root .od-table th {
        background: #f1f5f9; color: #334155; font-size: 0.78rem; font-weight: 800;
        text-transform: uppercase; letter-spacing: .04em; padding: 11px 14px; border-bottom: 1px solid #e2e8f0;
    }
    .admin-dashboard-root .od-table td {
        padding: 11px 14px; font-size: 0.9rem; vertical-align: middle; border-bottom: 1px solid #f1f5f9; color: #0f172a; font-weight: 600;
    }
    .admin-dashboard-root .od-table tr:hover td { background: #f8fafc; }
    .admin-dashboard-root .od-avatar {
        width: 32px; height: 32px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;
        color: #fff; font-weight: 800; font-size: 0.78rem; flex-shrink: 0;
    }
    .admin-dashboard-root .od-pill {
        font-size: 0.75rem; font-weight: 800; padding: 4px 10px; border-radius: 20px;
        display: inline-flex; align-items: center; justify-content: center;
        white-space: nowrap; flex-shrink: 0; line-height: 1.2;
    }
    .admin-dashboard-root .od-pill.ok { background: #dcfce7; color: #15803d; }
    .admin-dashboard-root .od-pill.warn { background: #fef3c7; color: #b45309; }
    .admin-dashboard-root .od-pill.bad { background: #fee2e2; color: #b91c1c; }
    .admin-dashboard-root .od-pill.info { background: #fff7ed; color: #c2410c; }

    .admin-dashboard-root .od-chip-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 14px; }
    .admin-dashboard-root .od-chip {
        text-align: center; padding: 14px 8px; border-radius: 14px; border: 1px solid transparent;
        text-decoration: none !important; color: inherit !important; transition: .18s ease; box-shadow: 0 2px 8px rgba(0,0,0,.04);
    }
    .admin-dashboard-root .od-chip:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,.08); }
    .admin-dashboard-root .od-chip .cn { font-size: 1.55rem; font-weight: 800; line-height: 1; }
    .admin-dashboard-root .od-chip .cl { font-size: 0.8rem; font-weight: 750; margin-top: 6px; }

    .admin-dashboard-root .od-row-item {
        display: flex; align-items: center; justify-content: space-between; gap: 8px;
        padding: 10px 0; border-bottom: 1px solid #eef2f7;
    }
    .admin-dashboard-root .od-row-item:last-child { border-bottom: 0; }
    .admin-dashboard-root .od-finance-hero {
        background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 55%, #fed7aa 100%);
        border: 1px solid #fdba74; border-radius: 14px; padding: 16px;
    }
    .admin-dashboard-root .od-section-label {
        font-size: 0.82rem; font-weight: 800; color: #334155;
        text-transform: uppercase; letter-spacing: .04em; margin-bottom: 10px;
    }
    .admin-dashboard-root .od-stat-grid {
        display: grid; grid-template-columns: 1fr 1fr; gap: 10px;
    }
    .admin-dashboard-root .od-mini {
        border-radius: 12px; padding: 12px; border: 1px solid transparent;
    }
    .admin-dashboard-root .od-mini .mn { font-size: 1.35rem; font-weight: 800; line-height: 1.1; }
    .admin-dashboard-root .od-mini .ml { font-size: 0.8rem; font-weight: 750; margin-top: 4px; opacity: .9; }

    /* Punch (employee view) */
    .punch-circle-container { position: relative; width: 96px; height: 96px; display: flex; align-items: center; justify-content: center; user-select: none; touch-action: manipulation; }
    .punch-rotating-svg { position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 10; transform: rotate(-90deg); }
    .punch-track { fill: none; stroke: rgba(0,0,0,.08); stroke-width: 4.5; }
    .punch-rotating-line { fill: none; stroke-width: 5; stroke-linecap: round; stroke-dasharray: 276.46; stroke-dashoffset: 276.46; transition: stroke-dashoffset .25s ease-out, filter .25s ease; filter: drop-shadow(0 0 5px currentColor); }
    .punch-circle-container.is-holding .punch-rotating-line { transition: stroke-dashoffset 2000ms linear, filter .3s ease !important; stroke-dashoffset: 0 !important; filter: drop-shadow(0 0 12px currentColor) !important; }
    .punch-circle-container.is-holding .punch-circle-btn { transform: scale(.92) !important; box-shadow: 0 0 22px rgba(16,185,129,.45) !important; }
    .punch-circle-container.is-animating .punch-rotating-svg { animation: punchSvgSuccessSpin .6s ease-out forwards !important; }
    @keyframes punchSvgSuccessSpin { 0%{transform:rotate(-90deg) scale(1)} 50%{transform:rotate(180deg) scale(1.08)} 100%{transform:rotate(270deg) scale(1)} }
    .punch-circle-btn { position: relative; width: 82px; height: 82px; border-radius: 50%; border: none; padding: 0; background: transparent; cursor: pointer; outline: none !important; display: flex; align-items: center; justify-content: center; transition: transform .2s cubic-bezier(.34,1.56,.64,1), box-shadow .3s ease; }
    .punch-circle-btn:hover { transform: scale(1.05); }
    .punch-circle-btn:active, .punch-circle-btn.is-pressing { transform: scale(.92); }
    .punch-pulse-ring { position: absolute; width: 100%; height: 100%; border-radius: 50%; pointer-events: none; z-index: 1; animation: punchPulse 2.6s cubic-bezier(.215,.61,.355,1) infinite; }
    @keyframes punchPulse { 0%{transform:scale(.85);opacity:.8} 70%{transform:scale(1.3);opacity:0} 100%{transform:scale(1.3);opacity:0} }
    .punch-circle-btn.punch-btn-in .punch-pulse-ring { background: rgba(16,185,129,.35); }
    .punch-circle-btn.punch-btn-out .punch-pulse-ring { background: rgba(239,68,68,.35); }
    .punch-halo-outer { position: absolute; width: 82px; height: 82px; border-radius: 50%; z-index: 2; }
    .punch-circle-btn.punch-btn-in .punch-halo-outer { background: radial-gradient(circle, rgba(16,185,129,.28) 0%, rgba(16,185,129,.06) 70%, transparent 100%); box-shadow: 0 0 18px rgba(16,185,129,.3); }
    .punch-circle-btn.punch-btn-out .punch-halo-outer { background: radial-gradient(circle, rgba(239,68,68,.28) 0%, rgba(239,68,68,.06) 70%, transparent 100%); box-shadow: 0 0 18px rgba(239,68,68,.3); }
    .punch-halo-inner { position: absolute; width: 70px; height: 70px; border-radius: 50%; z-index: 3; }
    .punch-circle-btn.punch-btn-in .punch-halo-inner { background: rgba(16,185,129,.25); }
    .punch-circle-btn.punch-btn-out .punch-halo-inner { background: rgba(239,68,68,.25); }
    .punch-core { position: relative; width: 62px; height: 62px; border-radius: 50%; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #fff; z-index: 4; border: 2px solid rgba(255,255,255,.5); }
    .punch-circle-btn.punch-btn-in .punch-core { background: linear-gradient(135deg, #10b981 0%, #059669 60%, #047857 100%); box-shadow: 0 5px 16px rgba(16,185,129,.45), inset 0 2px 4px rgba(255,255,255,.35); }
    .punch-circle-btn.punch-btn-out .punch-core { background: linear-gradient(135deg, #ef4444 0%, #dc2626 60%, #b91c1c 100%); box-shadow: 0 5px 16px rgba(239,68,68,.45), inset 0 2px 4px rgba(255,255,255,.35); }
    .punch-icon-wrap { display: flex; align-items: center; justify-content: center; line-height: 1; }
    .punch-tap-icon { width: 22px; height: 22px; margin-bottom: 2px; filter: drop-shadow(0 1px 2px rgba(0,0,0,.25)); }
    .punch-subtext { font-size: .62rem; font-weight: 800; letter-spacing: .3px; color: rgba(255,255,255,.98); margin-top: 1px; text-transform: uppercase; }

    /* Employee dashboard cards */
    .emp-stat-row { width: 100%; }
    .emp-stat-card { min-height: 185px; transition: transform .2s ease, box-shadow .2s ease; }
    .emp-stat-card:hover { transform: translateY(-3px); box-shadow: 0 12px 28px rgba(15,23,42,.12) !important; }
</style>

@if (!empty($adminDashboardData))
@php
    $ad = $adminDashboardData;
    $money = function ($n) {
        $n = (float) $n;
        if (abs($n) >= 10000000) return '₹' . number_format($n / 10000000, 2) . ' Cr';
        if (abs($n) >= 100000) return '₹' . number_format($n / 100000, 2) . ' L';
        return '₹' . number_format($n, 0);
    };
    $pDeg = ($ad['present_pct'] / 100) * 360;
    $aDeg = ($ad['absent_pct'] / 100) * 360;
    $pEnd = $pDeg; $aEnd = $pEnd + $aDeg;
@endphp

<div class="admin-dashboard-root">

    {{-- HERO: company pulse at a glance --}}
    <div class="od-hero od-gap">
        <div class="p-3 p-md-4 position-relative">
            <div class="row g-3 align-items-center">
                <div class="col-12 col-xl-5">
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                        <span class="badge rounded-pill px-2 py-1 fw-bold" style="background:rgba(242,140,40,.95);color:#fff;font-size:.68rem;">Owner Command Center</span>
                        @if (!is_null($ad['plan_days_left']))
                            <span class="badge rounded-pill px-2 py-1 fw-bold" style="background:rgba(255,255,255,.14);font-size:.68rem;">
                                Plan {{ $ad['plan_days_left'] >= 0 ? $ad['plan_days_left'] . ' days left' : 'expired' }}
                            </span>
                        @endif
                    </div>
                    <h2 class="text-white fw-bold mb-1" style="font-size:clamp(1.5rem,2.4vw,1.9rem);letter-spacing:-.03em;">
                        {{ $ad['greeting'] }}, {{ $ad['admin_name'] }}
                    </h2>
                    <p class="mb-0 text-white" style="opacity:.92;font-size:1rem;font-weight:600;">
                        <strong style="color:#ffb347;">{{ $ad['company_name'] }}</strong> — full company overview in one glance.
                    </p>
                    <div class="d-flex align-items-center gap-3 mt-2 text-white" style="opacity:.88;font-size:.88rem;font-weight:650;">
                        <span><i class="ti ti-calendar me-1"></i>{{ $ad['current_date'] }}</span>
                        <span id="admin-live-clock"><i class="ti ti-clock me-1"></i>{{ $ad['current_time'] }}</span>
                    </div>
                </div>
                <div class="col-12 col-xl-7">
                    <div class="od-pulse">
                        <div class="od-pulse-item">
                            <div class="lbl">Workforce</div>
                            <div class="val">{{ $ad['total_employees'] }}</div>
                        </div>
                        <div class="od-pulse-item">
                            <div class="lbl">Present %</div>
                            <div class="val" style="color:#86efac;">{{ $ad['present_pct'] }}%</div>
                        </div>
                        <div class="od-pulse-item">
                            <div class="lbl">Pending Actions</div>
                            <div class="val" style="color:#fcd34d;">{{ $ad['pending_approvals'] }}</div>
                        </div>
                        <div class="od-pulse-item">
                            <div class="lbl">Month Payroll</div>
                            <div class="val" style="font-size:1.05rem;color:#fdba74;">{{ $money($ad['month_total_payroll']) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Company masters strip --}}
    <div class="od-meta od-gap">
        <div class="od-meta-item" style="background:linear-gradient(135deg,#dbeafe,#bfdbfe);color:#1e3a8a;">
            <div class="n">{{ $ad['branches_count'] ?? 0 }}</div>
            <div class="l">Branches</div>
        </div>
        <div class="od-meta-item" style="background:linear-gradient(135deg,#ffedd5,#fdba74);color:#9a3412;">
            <div class="n">{{ $ad['departments_count'] ?? 0 }}</div>
            <div class="l">Departments</div>
        </div>
        <div class="od-meta-item" style="background:linear-gradient(135deg,#d1fae5,#6ee7b7);color:#065f46;">
            <div class="n">{{ $ad['shifts_count'] ?? 0 }}</div>
            <div class="l">Shifts</div>
        </div>
        <div class="od-meta-item" style="background:linear-gradient(135deg,#ede9fe,#c4b5fd);color:#5b21b6;">
            <div class="n">{{ $ad['designations_count'] ?? 0 }}</div>
            <div class="l">Designations</div>
        </div>
    </div>

    {{-- KPI row 1 --}}
    <div class="row g-3 od-gap">
        <div class="col-6 col-lg-3">
            <a href="{{ route('employees.index') }}" class="od-kpi" style="--accent:#2563eb;">
                <span class="accent"></span>
                <div class="d-flex align-items-start gap-2">
                    <div class="ico" style="background:#eff6ff;color:#2563eb;"><i class="ti ti-users"></i></div>
                    <div class="min-w-0">
                        <div class="title">Total Employees</div>
                        <div class="num">{{ $ad['total_employees'] }}</div>
                        <div class="sub" style="color:#0d9f6e;">+{{ $ad['new_this_month'] }} joined · {{ $ad['exits_this_month'] }} exits</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <a href="{{ route('daily-attendance-report.index') }}?status=present" class="od-kpi" style="--accent:#0d9f6e;">
                <span class="accent"></span>
                <div class="d-flex align-items-start gap-2">
                    <div class="ico" style="background:#ecfdf5;color:#0d9f6e;"><i class="ti ti-user-check"></i></div>
                    <div class="min-w-0">
                        <div class="title">Present Today</div>
                        <div class="num">{{ $ad['present_today'] }}</div>
                        <div class="sub" style="color:#0d9f6e;">{{ $ad['present_pct'] }}% of workforce</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <a href="{{ route('daily-attendance-report.index') }}?status=absent" class="od-kpi" style="--accent:#e11d48;">
                <span class="accent"></span>
                <div class="d-flex align-items-start gap-2">
                    <div class="ico" style="background:#fff1f2;color:#e11d48;"><i class="ti ti-user-off"></i></div>
                    <div class="min-w-0">
                        <div class="title">Absent Today</div>
                        <div class="num">{{ $ad['absent_today'] }}</div>
                        <div class="sub" style="color:#e11d48;">{{ $ad['absent_pct'] }}% absent</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <a href="{{ route('leave-application.index') }}" class="od-kpi" style="--accent:#ea580c;">
                <span class="accent"></span>
                <div class="d-flex align-items-start gap-2">
                    <div class="ico" style="background:#fff7ed;color:#ea580c;"><i class="ti ti-beach"></i></div>
                    <div class="min-w-0">
                        <div class="title">On Leave Today</div>
                        <div class="num">{{ $ad['on_leave_today'] }}</div>
                        <div class="sub" style="color:#ea580c;">{{ $ad['on_leave_pct'] }}% · {{ $ad['month_leaves_approved'] ?? 0 }} approved MTD</div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- KPI row 2 --}}
    <div class="row g-3 od-gap">
        <div class="col-6 col-lg-3">
            <a href="{{ route('leave-application.index') }}?status=pending" class="od-kpi" style="--accent:#d97706;">
                <span class="accent"></span>
                <div class="d-flex align-items-start gap-2">
                    <div class="ico" style="background:#fffbeb;color:#d97706;"><i class="ti ti-clipboard-check"></i></div>
                    <div class="min-w-0">
                        <div class="title">Pending Approvals</div>
                        <div class="num">{{ $ad['pending_approvals'] }}</div>
                        <div class="sub text-muted">L {{ $ad['pending_leaves'] }} · E {{ $ad['pending_expenses'] }} · Loan {{ $ad['pending_loans'] }}</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <a href="{{ route('salary-calculation.index') }}" class="od-kpi" style="--accent:#f28c28;">
                <span class="accent"></span>
                <div class="d-flex align-items-start gap-2">
                    <div class="ico" style="background:var(--orange-soft);color:#c2410c;"><i class="ti ti-currency-rupee"></i></div>
                    <div class="min-w-0">
                        <div class="title">Month Payroll</div>
                        <div class="num" style="font-size:1.35rem;">{{ $money($ad['month_total_payroll']) }}</div>
                        <div class="sub text-muted">Staff + Contractor</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <a href="{{ route('late-punch-report.index') }}" class="od-kpi" style="--accent:#0891b2;">
                <span class="accent"></span>
                <div class="d-flex align-items-start gap-2">
                    <div class="ico" style="background:#ecfeff;color:#0891b2;"><i class="ti ti-clock-exclamation"></i></div>
                    <div class="min-w-0">
                        <div class="title">Late / Early</div>
                        <div class="num">{{ $ad['late_today'] }}</div>
                        <div class="sub text-muted">Late today · Early go {{ $ad['early_go'] }}</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <a href="{{ route('employees.index') }}" class="od-kpi" style="--accent:#0f766e;">
                <span class="accent"></span>
                <div class="d-flex align-items-start gap-2">
                    <div class="ico" style="background:#f0fdfa;color:#0f766e;"><i class="ti ti-building-factory-2"></i></div>
                    <div class="min-w-0">
                        <div class="title">Staff / Contractors</div>
                        <div class="num" style="font-size:1.35rem;">{{ $ad['regular_employees'] }} / {{ $ad['contractors'] }}</div>
                        <div class="sub text-muted">
                            @if (($ad['max_employees'] ?? 0) > 0)
                                Seats {{ $ad['seat_used_pct'] }}% of {{ $ad['max_employees'] }}
                            @else
                                Active workforce mix
                            @endif
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- Middle: Attendance + Dept + Actions (filled, no blank stretch) --}}
    <div class="row g-3 od-gap align-items-stretch">
        <div class="col-12 col-xl-4 d-flex flex-column">
            <div class="od-card w-100 flex-grow-1" style="background:linear-gradient(180deg,#ffffff 0%,#f0f9ff 100%);">
                <div class="od-card-h" style="background:linear-gradient(90deg,#eff6ff,#dbeafe);">
                    <h5><i class="ti ti-chart-donut text-primary me-1"></i> Today's Attendance</h5>
                    <a href="{{ route('daily-attendance-report.index') }}" class="od-link">Report</a>
                </div>
                <div class="od-card-b d-flex flex-column">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div style="width:130px;height:130px;min-width:130px;border-radius:50%;background:conic-gradient(#0d9f6e 0deg {{ $pEnd }}deg,#e11d48 {{ $pEnd }}deg {{ $aEnd }}deg,#f59e0b {{ $aEnd }}deg 360deg);display:flex;align-items:center;justify-content:center;box-shadow:0 10px 24px rgba(15,28,63,.12);">
                            <div style="width:90px;height:90px;border-radius:50%;background:#fff;display:flex;flex-direction:column;align-items:center;justify-content:center;">
                                <span style="font-size:1.45rem;font-weight:800;line-height:1;color:#0f172a;">{{ $ad['present_pct'] }}%</span>
                                <span style="font-size:.78rem;font-weight:750;color:#475569;">Present</span>
                            </div>
                        </div>
                        <div class="flex-grow-1 d-flex flex-column gap-2">
                            <div class="d-flex justify-content-between px-2 py-2 rounded-3" style="background:#dcfce7;">
                                <span style="font-size:.9rem;font-weight:750;color:#166534;">Present</span>
                                <span style="font-size:1rem;font-weight:800;color:#166534;">{{ $ad['present_today'] }}</span>
                            </div>
                            <div class="d-flex justify-content-between px-2 py-2 rounded-3" style="background:#fecaca;">
                                <span style="font-size:.9rem;font-weight:750;color:#9f1239;">Absent</span>
                                <span style="font-size:1rem;font-weight:800;color:#9f1239;">{{ $ad['absent_today'] }}</span>
                            </div>
                            <div class="d-flex justify-content-between px-2 py-2 rounded-3" style="background:#fed7aa;">
                                <span style="font-size:.9rem;font-weight:750;color:#9a3412;">Leave</span>
                                <span style="font-size:1rem;font-weight:800;color:#9a3412;">{{ $ad['on_leave_today'] }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="od-stat-grid mb-3">
                        <div class="od-mini" style="background:#ecfeff;border-color:#a5f3fc;">
                            <div class="mn" style="color:#0e7490;">{{ $ad['late_today'] }}</div>
                            <div class="ml" style="color:#155e75;">Late Punch</div>
                        </div>
                        <div class="od-mini" style="background:#fef3c7;border-color:#fde68a;">
                            <div class="mn" style="color:#b45309;">{{ $ad['early_go'] }}</div>
                            <div class="ml" style="color:#92400e;">Early Going</div>
                        </div>
                        <div class="od-mini" style="background:#fce7f3;border-color:#fbcfe8;">
                            <div class="mn" style="color:#be185d;">{{ $ad['not_punched'] ?? $ad['absent_today'] }}</div>
                            <div class="ml" style="color:#9d174d;">Not Punched</div>
                        </div>
                        <div class="od-mini" style="background:#ede9fe;border-color:#ddd6fe;">
                            <div class="mn" style="color:#6d28d9;">{{ $ad['month_leaves_approved'] ?? 0 }}</div>
                            <div class="ml" style="color:#5b21b6;">Leaves MTD</div>
                        </div>
                    </div>

                    <div class="od-section-label">Last 7 Days Present</div>
                    <div class="od-trend mb-3">
                        @foreach ($ad['attendance_trend'] as $ti => $t)
                            @php
                                $h = $ad['trend_max'] > 0 ? max(10, round(($t['present'] / $ad['trend_max']) * 80)) : 10;
                                $barColors = ['#2563eb','#0d9f6e','#f28c28','#e11d48','#7c3aed','#0891b2','#ca8a04'];
                            @endphp
                            <div class="od-trend-col" title="{{ $t['date'] }}: {{ $t['present'] }}">
                                <span style="font-size:.75rem;font-weight:800;color:#334155;margin-bottom:4px;">{{ $t['present'] }}</span>
                                <div class="od-trend-bar" style="height:{{ $h }}px;background:linear-gradient(180deg,{{ $barColors[$ti % 7] }},#1e3a8a);"></div>
                                <span style="font-size:.75rem;font-weight:750;color:#475569;margin-top:5px;">{{ $t['label'] }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-auto pt-2">
                        <div class="od-section-label">Late Today</div>
                        @forelse ($ad['late_list'] ?? [] as $ll)
                            <div class="od-row-item">
                                <div class="min-w-0">
                                    <div class="text-truncate" style="font-size:.92rem;font-weight:750;">{{ $ll['name'] }}</div>
                                    <div style="font-size:.78rem;color:#64748b;font-weight:600;">{{ $ll['code'] }}</div>
                                </div>
                                <span class="od-pill warn">{{ $ll['time'] }}</span>
                            </div>
                        @empty
                            <div class="text-center py-2 rounded-3" style="background:#ecfdf5;color:#065f46;font-weight:700;font-size:.9rem;">
                                <i class="ti ti-checks me-1"></i> No late punches today
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4 d-flex flex-column">
            <div class="od-card w-100 flex-grow-1" style="background:linear-gradient(180deg,#ffffff 0%,#fffbeb 100%);">
                <div class="od-card-h" style="background:linear-gradient(90deg,#fff7ed,#ffedd5);">
                    <h5><i class="ti ti-building text-warning me-1"></i> Workforce Mix</h5>
                    <span class="od-pill info">{{ count($ad['department_stats']) }} Depts</span>
                </div>
                <div class="od-card-b d-flex flex-column">
                    <div class="od-section-label">Department Headcount</div>
                    @forelse ($ad['department_stats'] as $ds)
                        @php $pct = $ad['dept_max'] > 0 ? round(($ds['count'] / $ad['dept_max']) * 100) : 0; @endphp
                        <div class="mb-2">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-truncate pe-2" style="font-size:.92rem;font-weight:750;" title="{{ $ds['name'] }}">{{ $ds['name'] }}</span>
                                <span style="font-size:.95rem;font-weight:800;color:{{ $ds['color'] }};">{{ $ds['count'] }}</span>
                            </div>
                            <div class="od-hbar"><span style="width:{{ max(8,$pct) }}%;background:{{ $ds['color'] }};"></span></div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-3" style="font-size:.9rem;font-weight:650;">No department data yet.</div>
                    @endforelse

                    <div class="od-section-label mt-3">Gender Mix</div>
                    <div class="d-flex gap-2 mb-3">
                        <div class="flex-fill text-center p-2 rounded-3" style="background:linear-gradient(135deg,#dbeafe,#93c5fd);">
                            <div style="font-weight:800;font-size:1.35rem;color:#1e3a8a;">{{ $ad['male_count'] ?? 0 }}</div>
                            <div style="font-size:.8rem;font-weight:750;color:#1e40af;">Male</div>
                        </div>
                        <div class="flex-fill text-center p-2 rounded-3" style="background:linear-gradient(135deg,#fce7f3,#f9a8d4);">
                            <div style="font-weight:800;font-size:1.35rem;color:#9d174d;">{{ $ad['female_count'] ?? 0 }}</div>
                            <div style="font-size:.8rem;font-weight:750;color:#be185d;">Female</div>
                        </div>
                        <div class="flex-fill text-center p-2 rounded-3" style="background:linear-gradient(135deg,#e2e8f0,#cbd5e1);">
                            <div style="font-weight:800;font-size:1.35rem;color:#334155;">{{ $ad['other_gender_count'] ?? 0 }}</div>
                            <div style="font-size:.8rem;font-weight:750;color:#475569;">Other</div>
                        </div>
                    </div>

                    <div class="od-section-label">Top Designations</div>
                    @forelse ($ad['designation_stats'] ?? [] as $dg)
                        @php $dp = ($ad['desig_max'] ?? 1) > 0 ? round(($dg['count'] / $ad['desig_max']) * 100) : 0; @endphp
                        <div class="mb-2">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-truncate pe-2" style="font-size:.88rem;font-weight:700;">{{ $dg['name'] }}</span>
                                <span style="font-size:.9rem;font-weight:800;">{{ $dg['count'] }}</span>
                            </div>
                            <div class="od-hbar"><span style="width:{{ max(8,$dp) }}%;background:{{ $dg['color'] }};"></span></div>
                        </div>
                    @empty
                        <div class="od-stat-grid mb-2">
                            <div class="od-mini" style="background:#eff6ff;">
                                <div class="mn" style="color:#1d4ed8;">{{ $ad['regular_employees'] }}</div>
                                <div class="ml">Staff</div>
                            </div>
                            <div class="od-mini" style="background:#ecfdf5;">
                                <div class="mn" style="color:#047857;">{{ $ad['contractors'] }}</div>
                                <div class="ml">Contractors</div>
                            </div>
                        </div>
                    @endforelse

                    <div class="od-stat-grid mt-auto pt-3">
                        <div class="od-mini" style="background:#dcfce7;">
                            <div class="mn" style="color:#166534;">{{ $ad['regular_employees'] }}</div>
                            <div class="ml" style="color:#166534;">Active Staff</div>
                        </div>
                        <div class="od-mini" style="background:#fee2e2;">
                            <div class="mn" style="color:#b91c1c;">{{ $ad['inactive_employees'] ?? 0 }}</div>
                            <div class="ml" style="color:#b91c1c;">Inactive</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4 d-flex flex-column">
            <div class="od-card w-100 flex-grow-1" style="background:linear-gradient(180deg,#ffffff 0%,#faf5ff 100%);">
                <div class="od-card-h" style="background:linear-gradient(90deg,#f5f3ff,#ede9fe);">
                    <h5><i class="ti ti-bell-ringing text-danger me-1"></i> Needs Your Action</h5>
                    @if ($ad['pending_approvals'] > 0)
                        <span class="od-pill warn">{{ $ad['pending_approvals'] }} Pending</span>
                    @endif
                </div>
                <div class="od-card-b d-flex flex-column">
                    <div class="od-chip-row">
                        <a href="{{ route('leave-application.index') }}?status=pending" class="od-chip" style="background:linear-gradient(135deg,#ffedd5,#fdba74);">
                            <div class="cn" style="color:#9a3412;">{{ $ad['pending_leaves'] }}</div>
                            <div class="cl" style="color:#9a3412;">Leaves</div>
                        </a>
                        <a href="{{ route('expense.index') }}" class="od-chip" style="background:linear-gradient(135deg,#d1fae5,#6ee7b7);">
                            <div class="cn" style="color:#065f46;">{{ $ad['pending_expenses'] }}</div>
                            <div class="cl" style="color:#065f46;">Expenses</div>
                        </a>
                        <a href="{{ route('loan.index') }}" class="od-chip" style="background:linear-gradient(135deg,#ede9fe,#c4b5fd);">
                            <div class="cn" style="color:#5b21b6;">{{ $ad['pending_loans'] }}</div>
                            <div class="cl" style="color:#5b21b6;">Loans</div>
                        </a>
                    </div>

                    <div class="od-section-label">Pending Leave Requests</div>
                    @forelse ($ad['pending_leave_items'] as $pli)
                        <div class="od-row-item">
                            <div class="d-flex align-items-center gap-2 min-w-0">
                                @if(!empty($pli['has_avatar']) && !empty($pli['avatar']))
                                    <img src="{{ $pli['avatar'] }}" alt="{{ $pli['name'] }}" class="od-avatar" style="width:32px;height:32px;border-radius:50%;object-fit:cover;border:1.5px solid #fde047;" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($pli['name']) }}';">
                                @else
                                    <span class="od-avatar" style="background:linear-gradient(135deg,#eab308,#ca8a04);">{{ strtoupper(substr($pli['name'],0,1)) }}</span>
                                @endif
                                <div class="min-w-0">
                                    <div class="text-truncate" style="font-size:.92rem;font-weight:750;">{{ $pli['name'] }}</div>
                                    <div style="font-size:.78rem;color:#64748b;font-weight:600;">{{ $pli['type'] }} · {{ $pli['date'] }}</div>
                                </div>
                            </div>
                            <span class="od-pill warn">Pending</span>
                        </div>
                    @empty
                        <div class="text-center py-2 mb-2 rounded-3" style="background:#ecfdf5;color:#065f46;font-weight:700;font-size:.9rem;">
                            <i class="ti ti-checks me-1"></i> No pending leaves
                        </div>
                    @endforelse

                    <div class="od-section-label mt-2">Pending Expenses</div>
                    @forelse ($ad['pending_expense_items'] ?? [] as $pei)
                        <div class="od-row-item">
                            <div class="min-w-0">
                                <div class="text-truncate" style="font-size:.9rem;font-weight:750;">{{ $pei['name'] }}</div>
                                <div style="font-size:.76rem;color:#64748b;font-weight:600;">{{ $pei['date'] }}</div>
                            </div>
                            <span style="font-size:.95rem;font-weight:800;color:#0d9f6e;">{{ $money($pei['amount']) }}</span>
                        </div>
                    @empty
                        <div class="text-center py-2 mb-2 rounded-3" style="background:#f0fdf4;color:#166534;font-weight:700;font-size:.9rem;">
                            No pending expenses
                        </div>
                    @endforelse

                    <div class="mt-auto pt-2">
                        <div class="od-section-label mt-2">Quick Actions</div>
                        <div class="d-flex flex-column gap-2">
                            <a href="{{ route('employees.create') }}" class="od-action"><span class="ai" style="background:#dbeafe;color:#1d4ed8;"><i class="ti ti-user-plus"></i></span>Add Employee</a>
                            <a href="{{ route('attendance.index') }}" class="od-action"><span class="ai" style="background:#ede9fe;color:#6d28d9;"><i class="ti ti-checkbox"></i></span>Mark Attendance</a>
                            <a href="{{ route('salary-calculation.index') }}" class="od-action"><span class="ai" style="background:#ffedd5;color:#c2410c;"><i class="ti ti-credit-card"></i></span>Generate Salary</a>
                            <a href="{{ route('expense.index') }}" class="od-action"><span class="ai" style="background:#d1fae5;color:#047857;"><i class="ti ti-receipt"></i></span>Review Expenses</a>
                            <a href="{{ route('daily-attendance-report.index') }}" class="od-action"><span class="ai" style="background:#e0e7ff;color:#4338ca;"><i class="ti ti-chart-bar"></i></span>View Reports</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Finance + Joiners + Celebrations --}}
    <div class="row g-3 od-gap align-items-stretch">
        <div class="col-12 col-xl-4">
            <div class="od-card" style="background:linear-gradient(180deg,#ffffff 0%,#fff7ed 100%);">
                <div class="od-card-h" style="background:linear-gradient(90deg,#ffedd5,#fed7aa);">
                    <h5><i class="ti ti-currency-rupee me-1" style="color:#c2410c;"></i> Finance Snapshot</h5>
                    <a href="{{ route('salary-calculation.index') }}" class="od-link" style="background:#c2410c;">Payroll</a>
                </div>
                <div class="od-card-b od-scroll">
                    <div class="od-finance-hero mb-3">
                        <div style="font-size:.85rem;font-weight:750;color:#9a3412;">This Month Total Pay</div>
                        <div style="font-size:1.85rem;font-weight:800;color:#0f172a;letter-spacing:-.02em;">{{ $money($ad['month_total_payroll']) }}</div>
                        <div class="d-flex gap-3 mt-1" style="font-size:.82rem;font-weight:700;color:#78716c;">
                            <span>Staff {{ $money($ad['month_payroll']) }}</span>
                            <span>Contractor {{ $money($ad['month_contractor_pay']) }}</span>
                        </div>
                    </div>
                    <div class="od-row-item">
                        <div>
                            <div style="font-size:.92rem;font-weight:750;">Pending Expenses</div>
                            <div style="font-size:.8rem;color:#64748b;font-weight:600;">{{ $ad['pending_expenses'] }} requests</div>
                        </div>
                        <div style="font-weight:800;color:#0d9f6e;font-size:1.05rem;">{{ $money($ad['pending_expense_amount']) }}</div>
                    </div>
                    <div class="od-row-item">
                        <div style="font-size:.92rem;font-weight:750;">Passed Expenses (MTD)</div>
                        <div style="font-weight:800;font-size:1.05rem;">{{ $money($ad['month_expense_passed']) }}</div>
                    </div>
                    <div class="od-row-item">
                        <div>
                            <div style="font-size:.92rem;font-weight:750;">Active Loan Balance</div>
                            <div style="font-size:.8rem;color:#64748b;font-weight:600;">{{ $ad['active_loans'] }} loans</div>
                        </div>
                        <div style="font-weight:800;color:#7c3aed;font-size:1.05rem;">{{ $money($ad['active_loan_balance']) }}</div>
                    </div>
                    @forelse (($ad['pending_expense_items'] ?? []) as $pei)
                        <div class="od-row-item">
                            <div class="min-w-0">
                                <div class="text-truncate" style="font-size:.78rem;font-weight:700;">{{ $pei['name'] }}</div>
                                <div style="font-size:.65rem;color:#64748b;">{{ $pei['date'] }}</div>
                            </div>
                            <span style="font-size:.78rem;font-weight:800;">{{ $money($pei['amount']) }}</span>
                        </div>
                    @empty
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="od-card" style="background:linear-gradient(180deg,#ffffff 0%,#ecfdf5 100%);">
                <div class="od-card-h" style="background:linear-gradient(90deg,#d1fae5,#a7f3d0);">
                    <h5><i class="ti ti-user-plus me-1" style="color:#047857;"></i> New Joiners</h5>
                    <span class="od-pill ok">+{{ $ad['new_this_month'] }}</span>
                </div>
                <div class="od-card-b od-scroll">
                    @forelse ($ad['new_joiners'] ?? [] as $nj)
                        <div class="od-row-item">
                            <div class="d-flex align-items-center gap-2 min-w-0">
                                @if(!empty($nj['has_avatar']) && !empty($nj['avatar']))
                                    <img src="{{ $nj['avatar'] }}" alt="{{ $nj['name'] }}" class="od-avatar" style="width:32px;height:32px;border-radius:50%;object-fit:cover;border:1.5px solid #e2e8f0;" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($nj['name']) }}';">
                                @else
                                    <span class="od-avatar" style="background:linear-gradient(135deg,#2563eb,#1e3a8a);">{{ strtoupper(substr($nj['name'],0,1)) }}</span>
                                @endif
                                <div class="min-w-0">
                                    <div class="text-truncate" style="font-size:.88rem;font-weight:750;">{{ $nj['name'] }}</div>
                                    <div style="font-size:.76rem;color:#64748b;font-weight:600;">{{ $nj['code'] }} · {{ $nj['department'] }}</div>
                                </div>
                            </div>
                            <span style="font-size:.76rem;font-weight:650;color:#64748b;white-space:nowrap;">{{ $nj['date'] }}</span>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4" style="font-size:.9rem;font-weight:650;">No new joiners this month.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="od-card" style="background:linear-gradient(180deg,#ffffff 0%,#fdf2f8 100%);">
                <div class="od-card-h" style="background:linear-gradient(90deg,#fce7f3,#fbcfe8);">
                    <h5><i class="ti ti-cake me-1" style="color:#be185d;"></i> Birthdays &amp; Anniversaries</h5>
                    <a href="{{ route('employees.index') }}" class="od-link" style="background:#be185d;">Directory</a>
                </div>
                <div class="od-card-b od-scroll">
                    @forelse ($ad['birthdays_anniversaries'] as $ba)
                        <div class="od-row-item">
                            <div class="d-flex align-items-center gap-2 min-w-0">
                                @if(!empty($ba['has_avatar']) && !empty($ba['avatar']))
                                    <img src="{{ $ba['avatar'] }}" alt="{{ $ba['name'] }}" class="od-avatar" style="width:32px;height:32px;border-radius:50%;object-fit:cover;border:1.5px solid #e2e8f0;" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($ba['name']) }}';">
                                @else
                                    <span class="od-avatar" style="background:linear-gradient(135deg,#8b5cf6,#6d28d9);">{{ strtoupper(substr($ba['name'],0,1)) }}</span>
                                @endif
                                <div class="min-w-0">
                                    <div class="text-truncate" style="font-size:.88rem;font-weight:750;">{{ $ba['name'] }}</div>
                                    <div style="font-size:.76rem;color:#64748b;font-weight:600;">{{ $ba['subtitle'] }}</div>
                                </div>
                            </div>
                            <span class="d-inline-flex align-items-center justify-content-center rounded-3" style="width:28px;height:28px;background:{{ $ba['bg'] }};color:{{ $ba['color'] }};">
                                <i class="ti {{ $ba['icon'] }}"></i>
                            </span>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4" style="font-size:.9rem;font-weight:650;">
                            <i class="ti ti-gift fs-2 d-block mb-1 text-danger"></i>No celebrations this week.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Absent Today: full width --}}
    <div class="row g-3 od-gap">
        <div class="col-12">
            <div class="od-card" style="background:linear-gradient(180deg,#ffffff 0%,#fff1f2 100%);">
                <div class="od-card-h" style="background:linear-gradient(90deg,#fee2e2,#fecaca);">
                    <h5><i class="ti ti-user-off me-1" style="color:#b91c1c;"></i> Absent Today</h5>
                    <div class="d-flex align-items-center gap-2">
                        <span class="od-pill bad">{{ $ad['absent_today'] }} Absent</span>
                        <a href="{{ route('daily-attendance-report.index') }}?status=absent" class="od-link" style="background:#b91c1c;">View All</a>
                    </div>
                </div>
                <div class="od-card-b">
                    @if (!empty($ad['absent_list']))
                        <div class="od-absent-grid">
                            @foreach ($ad['absent_list'] as $ab)
                                <div class="od-absent-item" title="{{ $ab['name'] }} ({{ $ab['code'] ?? '' }} · {{ $ab['department'] }})">
                                    <div class="d-flex align-items-center gap-2 min-w-0 flex-grow-1" style="overflow:hidden;">
                                        @if(!empty($ab['has_avatar']) && !empty($ab['avatar']))
                                            <img src="{{ $ab['avatar'] }}" alt="{{ $ab['name'] }}" class="od-avatar" style="width:32px;height:32px;border-radius:50%;object-fit:cover;border:1.5px solid #fca5a5;" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($ab['name']) }}';">
                                        @else
                                            <span class="od-avatar" style="background:linear-gradient(135deg,#ef4444,#b91c1c);">{{ strtoupper(substr($ab['name'],0,1)) }}</span>
                                        @endif
                                        <div class="min-w-0 flex-grow-1" style="overflow:hidden;">
                                            <div class="text-truncate" style="font-size:.88rem;font-weight:750;color:#0f172a;line-height:1.25;" title="{{ $ab['name'] }}">{{ $ab['name'] }}</div>
                                            <div class="text-truncate mt-1" style="font-size:.75rem;color:#64748b;font-weight:600;line-height:1.2;" title="{{ $ab['code'] ?? '' }} · {{ $ab['department'] }}">{{ $ab['code'] ?? '' }} · {{ $ab['department'] }}</div>
                                        </div>
                                    </div>
                                    <span class="od-pill bad ms-1" style="font-size:.72rem;padding:3px 8px;">Absent</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-3 rounded-3" style="background:#ecfdf5;color:#065f46;font-weight:700;font-size:.95rem;">
                            <i class="ti ti-checks me-1"></i> No absentees listed for today.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Live lists --}}
    <div class="row g-3 od-gap align-items-stretch">
        <div class="col-12 col-xl-6">
            <div class="od-card" style="background:linear-gradient(180deg,#ffffff 0%,#eff6ff 100%);">
                <div class="od-card-h" style="background:linear-gradient(90deg,#dbeafe,#bfdbfe);">
                    <h5><i class="ti ti-clock me-1 text-primary"></i> Today's Live Attendance</h5>
                    <a href="{{ route('daily-attendance-report.index') }}" class="od-link">View All</a>
                </div>
                <div class="od-card-b p-0">
                    <div class="table-responsive">
                        <table class="od-table">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Department</th>
                                    <th>In Time</th>
                                    <th class="text-end">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($ad['today_live_attendance'] as $la)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                @if(!empty($la['has_avatar']) && !empty($la['avatar']))
                                                    <img src="{{ $la['avatar'] }}" alt="{{ $la['name'] }}" class="od-avatar" style="width:32px;height:32px;border-radius:50%;object-fit:cover;border:1.5px solid #bfdbfe;" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($la['name']) }}';">
                                                @else
                                                    <span class="od-avatar" style="background:linear-gradient(135deg,#3b82f6,#1d4ed8);">{{ strtoupper(substr($la['name'],0,1)) }}</span>
                                                @endif
                                                <span class="fw-bold text-nowrap" title="{{ $la['name'] }}">{{ $la['name'] }}</span>
                                            </div>
                                        </td>
                                        <td class="text-muted fw-semibold">{{ $la['department'] }}</td>
                                        <td class="fw-semibold">{{ $la['time'] }}</td>
                                        <td class="text-end"><span class="od-pill ok">Present</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-4" style="font-size:.9rem;font-weight:650;">No punches recorded yet today.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-6">
            <div class="od-card" style="background:linear-gradient(180deg,#ffffff 0%,#fff7ed 100%);">
                <div class="od-card-h" style="background:linear-gradient(90deg,#ffedd5,#fed7aa);">
                    <h5><i class="ti ti-calendar-event me-1" style="color:#c2410c;"></i> Upcoming Leaves</h5>
                    <a href="{{ route('leave-application.index') }}" class="od-link" style="background:#ea580c;">View All</a>
                </div>
                <div class="od-card-b p-0">
                    <div class="table-responsive">
                        <table class="od-table">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Dept</th>
                                    <th>Type</th>
                                    <th class="text-end">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($ad['upcoming_leaves'] as $ul)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                @if(!empty($ul['has_avatar']) && !empty($ul['avatar']))
                                                    <img src="{{ $ul['avatar'] }}" alt="{{ $ul['name'] }}" class="od-avatar" style="width:32px;height:32px;border-radius:50%;object-fit:cover;border:1.5px solid #fed7aa;" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($ul['name']) }}';">
                                                @else
                                                    <span class="od-avatar" style="background:linear-gradient(135deg,#f97316,#ea580c);">{{ strtoupper(substr($ul['name'],0,1)) }}</span>
                                                @endif
                                                <span class="fw-bold text-nowrap" title="{{ $ul['name'] }}">{{ $ul['name'] }}</span>
                                            </div>
                                        </td>
                                        <td class="text-muted fw-semibold">{{ $ul['department'] }}</td>
                                        <td class="fw-semibold">{{ $ul['leave_type'] }}</td>
                                        <td class="text-end text-muted fw-semibold">{{ $ul['date'] }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-4" style="font-size:.9rem;font-weight:650;">No upcoming leaves.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
setInterval(function () {
    var el = document.getElementById('admin-live-clock');
    if (!el) return;
    var d = new Date(), h = d.getHours(), m = d.getMinutes(), s = d.getSeconds();
    var ap = h >= 12 ? 'PM' : 'AM';
    h = h % 12; h = h ? h : 12;
    m = m < 10 ? '0' + m : m; s = s < 10 ? '0' + s : s;
    el.innerHTML = '<i class="ti ti-clock me-1"></i>' + (h < 10 ? '0' + h : h) + ':' + m + ':' + s + ' ' + ap;
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
         style="background-color: #e8f1ff; background-image: url('{{ asset('software/img/set-img.png') }}'); background-repeat: no-repeat; background-position: right center; background-size: cover; border-radius: 18px; border: 1px solid #d6e4fc; box-shadow: 0 4px 20px rgba(37, 99, 235, 0.08); min-height: 220px;">
        <div class="card-body p-4 position-relative" style="z-index: 1;">
            <div class="row align-items-center">
                <div class="col-12 col-lg-8">
                    <div class="mb-3">
                        <span style="font-size: 1.2rem; font-weight: 750; color: #334155;">{{ $greeting }},</span>
                        <h2 class="mb-1" style="color: #1e3a8a; font-size: 2rem; letter-spacing: -0.03em; font-weight: 900;">
                            {{ $employeeStats['proper_name'] ?? ($employeeStats['employee']->full_name ?? 'Employee') }}!
                        </h2>
                        <p class="mb-0" style="font-size: 1.05rem; color: #475569; font-weight: 700;">Have a productive day at work.</p>
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

                {{-- Right side kept empty so banner character from background shows --}}
                <div class="col-12 col-lg-4 d-none d-lg-block" style="min-height: 160px;"></div>
            </div>
        </div>
    </div>

    {{-- 3 Main Stat Cards --}}
    <div class="row g-3 g-xl-4 mb-4 emp-stat-row">
        {{-- Card 1: Total Attendance --}}
        <div class="col-12 col-md-4">
            <div class="card h-100 border-0 position-relative overflow-hidden emp-stat-card"
                 style="border-radius: 18px; border-bottom: 5px solid #6366f1 !important; background: linear-gradient(180deg,#ffffff 0%,#eef2ff 100%); box-shadow: 0 6px 22px rgba(99,102,241,0.12);">
                <div class="card-body p-4 p-xl-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center justify-content-center"
                             style="width: 52px; height: 52px; border-radius: 14px; background: rgba(99, 102, 241, 0.15); color: #4f46e5;">
                            <i class="ti ti-calendar-event" style="font-size:1.6rem;"></i>
                        </div>
                        <span class="badge rounded-pill px-3 py-2" style="background: rgba(99, 102, 241, 0.14); color: #4338ca; font-size: 0.88rem; font-weight: 800;">
                            Attendance
                        </span>
                    </div>
                    <div class="d-flex align-items-baseline gap-2 my-2">
                        <h1 class="mb-0" style="font-size: 2.75rem; line-height: 1; font-weight: 900; color: #4f46e5; letter-spacing: -0.04em;">{{ $employeeStats['total_present_range'] ?? 0 }}</h1>
                        <span style="font-size: 1.15rem; font-weight: 800; color: #0f172a;">Days</span>
                    </div>
                    <h5 class="card-title mb-3" style="font-size: 1.2rem; font-weight: 800; color: #0f172a; letter-spacing: -0.02em;">Total Attendance</h5>
                    <div class="d-flex justify-content-between align-items-center pt-3 border-top" style="font-size: 0.95rem; font-weight: 700;">
                        <span style="color:#475569;">Month Total</span>
                        <span class="badge bg-success px-3 py-2 rounded-pill" style="font-size: 0.9rem; font-weight: 800;">
                            {{ $employeeStats['total_present_month'] ?? 0 }} Days
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 2: Punch In Count --}}
        <div class="col-12 col-md-4">
            <div class="card h-100 border-0 position-relative overflow-hidden emp-stat-card"
                 style="border-radius: 18px; border-bottom: 5px solid #10b981 !important; background: linear-gradient(180deg,#ffffff 0%,#ecfdf5 100%); box-shadow: 0 6px 22px rgba(16,185,129,0.12);">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center justify-content-center"
                             style="width: 52px; height: 52px; border-radius: 14px; background: rgba(16, 185, 129, 0.15); color: #059669;">
                            <i class="ti ti-login" style="font-size:1.6rem;"></i>
                        </div>
                        <span class="badge rounded-pill px-3 py-2" style="background: rgba(16, 185, 129, 0.14); color: #047857; font-size: 0.88rem; font-weight: 800;">
                            Total: {{ $employeeStats['total_punch_in_count'] ?? 0 }}
                        </span>
                    </div>
                    <div class="d-flex align-items-baseline gap-2 my-2">
                        <h1 class="mb-0" style="font-size: 2.75rem; line-height: 1; font-weight: 900; color: #059669; letter-spacing: -0.04em;">{{ $employeeStats['total_punch_in_count'] ?? 0 }}</h1>
                        <span style="font-size: 1.15rem; font-weight: 800; color: #0f172a;">Punches</span>
                    </div>
                    <h5 class="card-title mb-3" style="font-size: 1.2rem; font-weight: 800; color: #0f172a; letter-spacing: -0.02em;">Punch In Count</h5>
                    <div class="d-flex justify-content-between align-items-center pt-3 border-top" style="font-size: 0.95rem; font-weight: 700;">
                        <span style="color:#475569;">Today's In Time</span>
                        <span class="badge bg-{{ $employeeStats['punch_in_time'] ? 'success' : 'secondary' }} px-3 py-2 rounded-pill" style="font-size: 0.9rem; font-weight: 800;">
                            {{ $employeeStats['punch_in_time'] ?: '--:--' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 3: Punch Out Count --}}
        <div class="col-12 col-md-4">
            <div class="card h-100 border-0 position-relative overflow-hidden emp-stat-card"
                 style="border-radius: 18px; border-bottom: 5px solid #ef4444 !important; background: linear-gradient(180deg,#ffffff 0%,#fef2f2 100%); box-shadow: 0 6px 22px rgba(239,68,68,0.12);">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center justify-content-center"
                             style="width: 52px; height: 52px; border-radius: 14px; background: rgba(239, 68, 68, 0.15); color: #dc2626;">
                            <i class="ti ti-logout" style="font-size:1.6rem;"></i>
                        </div>
                        <span class="badge rounded-pill px-3 py-2" style="background: rgba(239, 68, 68, 0.14); color: #b91c1c; font-size: 0.88rem; font-weight: 800;">
                            Total: {{ $employeeStats['total_punch_out_count'] ?? 0 }}
                        </span>
                    </div>
                    <div class="d-flex align-items-baseline gap-2 my-2">
                        <h1 class="mb-0" style="font-size: 2.75rem; line-height: 1; font-weight: 900; color: #dc2626; letter-spacing: -0.04em;">{{ $employeeStats['total_punch_out_count'] ?? 0 }}</h1>
                        <span style="font-size: 1.15rem; font-weight: 800; color: #0f172a;">Punches</span>
                    </div>
                    <h5 class="card-title mb-3" style="font-size: 1.2rem; font-weight: 800; color: #0f172a; letter-spacing: -0.02em;">Punch Out Count</h5>
                    <div class="d-flex justify-content-between align-items-center pt-3 border-top" style="font-size: 0.95rem; font-weight: 700;">
                        <span style="color:#475569;">Today's Out Time</span>
                        <span class="badge bg-{{ $employeeStats['punch_out_time'] ? 'danger' : 'secondary' }} px-3 py-2 rounded-pill" style="font-size: 0.9rem; font-weight: 800;">
                            {{ $employeeStats['punch_out_time'] ?: '--:--' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Type-wise Leave Balance Section --}}
    <div class="row g-3 g-xl-4 mb-4">
        <div class="col-12 mb-1">
            <h5 class="mb-0 d-flex align-items-center" style="font-size: 1.35rem; font-weight: 900; color: #0f172a; letter-spacing: -0.02em;">
                <i class="ti ti-calendar-event text-primary me-2" style="font-size:1.5rem;"></i> Leave Balance (Type-Wise)
            </h5>
        </div>

        @if (!empty($employeeStats['leave_balances']) && count($employeeStats['leave_balances']) > 0)
            @foreach ($employeeStats['leave_balances'] as $leave)
                @php
                    $pal = $leave['palette'] ?? ['bg' => 'rgba(115, 103, 240, 0.1)', 'border' => '#7367f0', 'text' => '#7367f0'];
                    $leaveCount = count($employeeStats['leave_balances']);
                    $leaveCol = $leaveCount <= 2 ? 'col-12 col-lg-6' : ($leaveCount <= 3 ? 'col-12 col-md-6 col-xl-4' : 'col-12 col-md-6 col-xl-4');
                @endphp
                <div class="{{ $leaveCol }}">
                    <div class="card h-100 border-0 position-relative overflow-hidden emp-stat-card"
                         style="border-radius: 18px; border-left: 6px solid {{ $pal['border'] }} !important; background: #ffffff; box-shadow: 0 6px 20px rgba(15,23,42,0.06); min-height: 150px;">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="d-flex align-items-center justify-content-center"
                                         style="width: 56px; height: 56px; border-radius: 14px; background: {{ $pal['bg'] }}; color: {{ $pal['text'] }}; font-size: 1.2rem; font-weight: 900; flex-shrink: 0;">
                                        {{ $leave['code'] }}
                                    </div>
                                    <div>
                                        <h5 class="mb-1 text-truncate" style="font-size: 1.2rem; font-weight: 800; color: #0f172a; letter-spacing: -0.02em; max-width: 220px;" title="{{ $leave['name'] }}">
                                            {{ $leave['name'] }}
                                        </h5>
                                        <div class="d-flex align-items-baseline gap-2 mt-1">
                                            <span style="color: {{ $pal['text'] }}; font-size: 2rem; font-weight: 900; line-height: 1; letter-spacing: -0.03em;">
                                                {{ number_format($leave['balance'], 1) }}
                                            </span>
                                            <span style="color: #0f172a; font-size: 1.05rem; font-weight: 800;">Days Available</span>
                                        </div>
                                    </div>
                                </div>
                                <span class="badge rounded-pill px-3 py-2" style="font-size: 0.82rem; font-weight: 750; background: #f8fafc; color: #334155; border: 1px solid #e2e8f0;">
                                    {{ $leave['carry_forward'] ? 'Carry Forward' : 'Monthly Accrual' }}
                                </span>
                            </div>

                            <div class="border-top pt-3 d-flex justify-content-between align-items-center" style="font-size: 0.98rem; font-weight: 700; color: #475569;">
                                <span>Allocated: <strong style="color:#0f172a; font-weight: 900;">{{ number_format($leave['allocated'], 1) }}</strong></span>
                                <span>Used (FY): <strong style="color:#dc2626; font-weight: 900;">{{ number_format($leave['used_year'], 1) }}</strong></span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
        @endif
    </div>

    {{-- 3. MULTI-TIER HIERARCHY: DEPARTMENT HEADS -> SUPERVISORS -> EMPLOYEES (MAIN HR / TOP LEVEL VIEW) --}}
    @if (!empty($departmentHeadsHierarchy) && count($departmentHeadsHierarchy) > 0)
        @php
            $totalDeptHeads = count($departmentHeadsHierarchy);
            $allSupervisorsInDept = collect($departmentHeadsHierarchy)->pluck('supervisors')->flatten(1);
            $allEmployeesInDept = $allSupervisorsInDept->pluck('employees')->flatten(1);
            
            $dhIn = collect($departmentHeadsHierarchy)->filter(fn($d) => ($d['dept_head']['status_type'] ?? '') === 'present_in')->count();
            $dhOut = collect($departmentHeadsHierarchy)->filter(fn($d) => ($d['dept_head']['status_type'] ?? '') === 'present_out')->count();
            $dhLeave = collect($departmentHeadsHierarchy)->filter(fn($d) => ($d['dept_head']['status_type'] ?? '') === 'leave')->count();
            $dhNotPunched = collect($departmentHeadsHierarchy)->filter(fn($d) => ($d['dept_head']['status_type'] ?? '') === 'not_punched')->count();
        @endphp

        {{-- Department Heads Overview Header --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mt-5 mb-3">
            <div>
                <h4 class="mb-1" style="color: #0f172a; font-weight: 850; letter-spacing: -0.02em;">
                    <i class="ti ti-building-community text-primary me-2"></i> Department Heads ({{ $totalDeptHeads }})
                </h4>
                <p class="text-muted mb-0" style="font-size: 0.95rem; font-weight: 650;">
                    Live hierarchy of Department Heads, Supervisors & their Employees
                </p>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="badge px-3 py-2 rounded-pill" style="background:#dcfce7; color:#166534; font-weight:800; font-size:.84rem;">
                    <i class="ti ti-check me-1"></i> {{ $dhIn }} In
                </span>
                @if ($dhOut > 0)
                    <span class="badge px-3 py-2 rounded-pill" style="background:#f1f5f9; color:#475569; font-weight:800; font-size:.84rem;">
                        <i class="ti ti-logout me-1"></i> {{ $dhOut }} Out
                    </span>
                @endif
                <span class="badge px-3 py-2 rounded-pill" style="background:#fee2e2; color:#991b1b; font-weight:800; font-size:.84rem;">
                    <i class="ti ti-alert-circle me-1"></i> {{ $dhNotPunched }} Not Punched
                </span>
                @if ($dhLeave > 0)
                    <span class="badge px-3 py-2 rounded-pill" style="background:#ffedd5; color:#9a3412; font-weight:800; font-size:.84rem;">
                        <i class="ti ti-calendar me-1"></i> {{ $dhLeave }} Leave
                    </span>
                @endif
            </div>
        </div>

        {{-- Single Department Head Layout --}}
        @if ($totalDeptHeads == 1)
            @php 
                $dh = $departmentHeadsHierarchy[0];
                $deptHead = $dh['dept_head'];
                $deptSups = $dh['supervisors'];
                $deptDirectEmps = $dh['direct_employees'];
            @endphp

            {{-- 1. Department Head Profile Banner --}}
            <div class="card border-0 mb-4 shadow-sm" style="border-radius: 18px; border: 2px solid #6366f1 !important; background: #ffffff;">
                <div class="p-3.5 p-md-4" style="background: linear-gradient(135deg, #eef2ff 0%, #ffffff 100%);">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-3 min-w-0">
                            <div class="position-relative">
                                <img src="{{ $deptHead['avatar'] }}" alt="{{ $deptHead['name'] }}"
                                     class="rounded-circle border border-2 border-white shadow-xs"
                                     style="width: 58px; height: 58px; object-fit: cover; background: #fff;"
                                     onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($deptHead['name']) }}';">
                                <span class="position-absolute bottom-0 end-0 badge p-1 rounded-circle bg-warning text-dark border border-white" title="Department Head">
                                    <i class="ti ti-shield-check" style="font-size: 0.75rem;"></i>
                                </span>
                            </div>
                            <div class="min-w-0">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <h4 class="mb-0 fw-bold text-dark text-truncate" style="letter-spacing: -0.02em;">
                                        {{ $deptHead['name'] }}
                                    </h4>
                                    <span class="badge rounded-pill px-2.5 py-1"
                                          style="font-size: 0.75rem; font-weight: 800;
                                          @if ($deptHead['status_type'] === 'present_in') background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0;
                                          @elseif ($deptHead['status_type'] === 'present_out') background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;
                                          @elseif ($deptHead['status_type'] === 'leave') background: #ffedd5; color: #c2410c; border: 1px solid #fed7aa;
                                          @else background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca;
                                          @endif">
                                        <i class="ti {{ $deptHead['status_icon'] }} me-1"></i>{{ $deptHead['status_label'] }}
                                    </span>
                                </div>
                                <div class="text-primary fw-bold" style="font-size: 0.92rem; margin-top: 2px;">
                                    {{ $deptHead['code'] }} · <span class="text-dark">{{ $deptHead['designation'] ?: 'Department Head' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-label-primary px-3 py-2 rounded-pill" style="font-size: 0.85rem; font-weight: 800;">
                                <i class="ti ti-crown me-1"></i> {{ count($deptSups) }} Supervisors
                            </span>
                            <a href="{{ route('employees.show', $deptHead['id']) }}" class="btn btn-outline-primary rounded-pill px-3 py-1.5" style="font-weight: 750; font-size: 0.82rem;">
                                View Profile <i class="ti ti-chevron-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. Department Head Full Attendance Stat Cards --}}
            <div class="row g-3 g-xl-4 mb-4 emp-stat-row">
                {{-- Card 1: Total Attendance --}}
                <div class="col-12 col-md-4">
                    <div class="card h-100 border-0 position-relative overflow-hidden emp-stat-card"
                         style="border-radius: 18px; border-bottom: 5px solid #6366f1 !important; background: linear-gradient(180deg,#ffffff 0%,#eef2ff 100%); box-shadow: 0 6px 22px rgba(99,102,241,0.12);">
                        <div class="card-body p-4 p-xl-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center justify-content-center"
                                     style="width: 52px; height: 52px; border-radius: 14px; background: rgba(99, 102, 241, 0.15); color: #4f46e5;">
                                    <i class="ti ti-calendar-event" style="font-size:1.6rem;"></i>
                                </div>
                                <span class="badge rounded-pill px-3 py-2" style="background: rgba(99, 102, 241, 0.14); color: #4338ca; font-size: 0.88rem; font-weight: 800;">
                                    Attendance
                                </span>
                            </div>
                            <div class="d-flex align-items-baseline gap-2 my-2">
                                <h1 class="mb-0" style="font-size: 2.75rem; line-height: 1; font-weight: 900; color: #4f46e5; letter-spacing: -0.04em;">{{ $deptHead['month_present'] ?? 0 }}</h1>
                                <span style="font-size: 1.15rem; font-weight: 800; color: #0f172a;">Days</span>
                            </div>
                            <h5 class="card-title mb-3" style="font-size: 1.2rem; font-weight: 800; color: #0f172a; letter-spacing: -0.02em;">Total Attendance</h5>
                            <div class="d-flex justify-content-between align-items-center pt-3 border-top" style="font-size: 0.95rem; font-weight: 700;">
                                <span style="color:#475569;">Month Total</span>
                                <span class="badge bg-success px-3 py-2 rounded-pill" style="font-size: 0.9rem; font-weight: 800;">
                                    {{ $deptHead['month_present'] ?? 0 }} Days
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card 2: Punch In Count --}}
                <div class="col-12 col-md-4">
                    <div class="card h-100 border-0 position-relative overflow-hidden emp-stat-card"
                         style="border-radius: 18px; border-bottom: 5px solid #10b981 !important; background: linear-gradient(180deg,#ffffff 0%,#ecfdf5 100%); box-shadow: 0 6px 22px rgba(16,185,129,0.12);">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center justify-content-center"
                                     style="width: 52px; height: 52px; border-radius: 14px; background: rgba(16, 185, 129, 0.15); color: #059669;">
                                    <i class="ti ti-login" style="font-size:1.6rem;"></i>
                                </div>
                                <span class="badge rounded-pill px-3 py-2" style="background: rgba(16, 185, 129, 0.14); color: #047857; font-size: 0.88rem; font-weight: 800;">
                                    Total: {{ $deptHead['total_punch_in_count'] ?? 0 }}
                                </span>
                            </div>
                            <div class="d-flex align-items-baseline gap-2 my-2">
                                <h1 class="mb-0" style="font-size: 2.75rem; line-height: 1; font-weight: 900; color: #059669; letter-spacing: -0.04em;">{{ $deptHead['total_punch_in_count'] ?? 0 }}</h1>
                                <span style="font-size: 1.15rem; font-weight: 800; color: #0f172a;">Punches</span>
                            </div>
                            <h5 class="card-title mb-3" style="font-size: 1.2rem; font-weight: 800; color: #0f172a; letter-spacing: -0.02em;">Punch In Count</h5>
                            <div class="d-flex justify-content-between align-items-center pt-3 border-top" style="font-size: 0.95rem; font-weight: 700;">
                                <span style="color:#475569;">Today's In Time</span>
                                <span class="badge bg-{{ $deptHead['punch_in_time'] ? 'success' : 'secondary' }} px-3 py-2 rounded-pill" style="font-size: 0.9rem; font-weight: 800;">
                                    {{ $deptHead['punch_in_time'] ?: '--:--' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card 3: Punch Out Count --}}
                <div class="col-12 col-md-4">
                    <div class="card h-100 border-0 position-relative overflow-hidden emp-stat-card"
                         style="border-radius: 18px; border-bottom: 5px solid #ef4444 !important; background: linear-gradient(180deg,#ffffff 0%,#fef2f2 100%); box-shadow: 0 6px 22px rgba(239,68,68,0.12);">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center justify-content-center"
                                     style="width: 52px; height: 52px; border-radius: 14px; background: rgba(239, 68, 68, 0.15); color: #dc2626;">
                                    <i class="ti ti-logout" style="font-size:1.6rem;"></i>
                                </div>
                                <span class="badge rounded-pill px-3 py-2" style="background: rgba(239, 68, 68, 0.14); color: #b91c1c; font-size: 0.88rem; font-weight: 800;">
                                    Total: {{ $deptHead['total_punch_out_count'] ?? 0 }}
                                </span>
                            </div>
                            <div class="d-flex align-items-baseline gap-2 my-2">
                                <h1 class="mb-0" style="font-size: 2.75rem; line-height: 1; font-weight: 900; color: #dc2626; letter-spacing: -0.04em;">{{ $deptHead['total_punch_out_count'] ?? 0 }}</h1>
                                <span style="font-size: 1.15rem; font-weight: 800; color: #0f172a;">Punches</span>
                            </div>
                            <h5 class="card-title mb-3" style="font-size: 1.2rem; font-weight: 800; color: #0f172a; letter-spacing: -0.02em;">Punch Out Count</h5>
                            <div class="d-flex justify-content-between align-items-center pt-3 border-top" style="font-size: 0.95rem; font-weight: 700;">
                                <span style="color:#475569;">Today's Out Time</span>
                                <span class="badge bg-{{ $deptHead['punch_out_time'] ? 'danger' : 'secondary' }} px-3 py-2 rounded-pill" style="font-size: 0.9rem; font-weight: 800;">
                                    {{ $deptHead['punch_out_time'] ?: '--:--' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. Department Head Leave Balance (Type-Wise) --}}
            <div class="row g-3 g-xl-4 mb-4">
                <div class="col-12 mb-1">
                    <h5 class="mb-0 d-flex align-items-center" style="font-size: 1.35rem; font-weight: 900; color: #0f172a; letter-spacing: -0.02em;">
                        <i class="ti ti-calendar-event text-primary me-2" style="font-size:1.5rem;"></i> Leave Balance (Type-Wise) - {{ $deptHead['name'] }}
                    </h5>
                </div>

                @if (!empty($deptHead['leave_balances']) && count($deptHead['leave_balances']) > 0)
                    @foreach ($deptHead['leave_balances'] as $leave)
                        @php
                            $pal = $leave['palette'] ?? ['bg' => 'rgba(115, 103, 240, 0.1)', 'border' => '#7367f0', 'text' => '#7367f0'];
                            $leaveCount = count($deptHead['leave_balances']);
                            $leaveCol = $leaveCount <= 2 ? 'col-12 col-lg-6' : 'col-12 col-md-4';
                        @endphp
                        <div class="{{ $leaveCol }}">
                            <div class="card h-100 border-0 position-relative overflow-hidden emp-stat-card"
                                 style="border-radius: 18px; border-left: 6px solid {{ $pal['border'] }} !important; background: #ffffff; box-shadow: 0 6px 20px rgba(15,23,42,0.06); min-height: 150px;">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="d-flex align-items-center justify-content-center"
                                                 style="width: 56px; height: 56px; border-radius: 14px; background: {{ $pal['bg'] }}; color: {{ $pal['text'] }}; font-size: 1.2rem; font-weight: 900; flex-shrink: 0;">
                                                {{ $leave['code'] }}
                                            </div>
                                            <div>
                                                <h5 class="mb-1 text-truncate" style="font-size: 1.2rem; font-weight: 800; color: #0f172a; letter-spacing: -0.02em; max-width: 220px;" title="{{ $leave['name'] }}">
                                                   {{ $leave['name'] }}
                                                </h5>
                                                <div class="d-flex align-items-baseline gap-2 mt-1">
                                                    <span style="color: {{ $pal['text'] }}; font-size: 2rem; font-weight: 900; line-height: 1; letter-spacing: -0.03em;">
                                                        {{ number_format($leave['balance'], 1) }}
                                                    </span>
                                                    <span style="color: #0f172a; font-size: 1.05rem; font-weight: 800;">Days Available</span>
                                                </div>
                                            </div>
                                        </div>
                                        <span class="badge rounded-pill px-3 py-2" style="font-size: 0.82rem; font-weight: 750; background: #f8fafc; color: #334155; border: 1px solid #e2e8f0;">
                                            {{ !empty($leave['carry_forward']) ? 'Carry Forward' : 'Monthly Accrual' }}
                                        </span>
                                    </div>

                                    <div class="border-top pt-3 d-flex justify-content-between align-items-center" style="font-size: 0.98rem; font-weight: 700; color: #475569;">
                                        <span>Allocated: <strong style="color:#0f172a; font-weight: 900;">{{ number_format($leave['allocated'] ?? 0, 1) }}</strong></span>
                                        <span>Used (FY): <strong style="color:#dc2626; font-weight: 900;">{{ number_format($leave['used_year'] ?? 0, 1) }}</strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            {{-- 4. Supervisors under this Single Department Head --}}
            @if (!empty($deptSups) && count($deptSups) > 0)
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mt-4 mb-3">
                    <div>
                        <h4 class="mb-1" style="color: #0f172a; font-weight: 850; letter-spacing: -0.02em;">
                            <i class="ti ti-user-star text-primary me-2"></i> Supervisors under {{ $deptHead['name'] }} ({{ count($deptSups) }})
                        </h4>
                        <p class="text-muted mb-0" style="font-size: 0.92rem; font-weight: 650;">
                            Supervisors reporting to {{ $deptHead['name'] }} and their direct team members
                        </p>
                    </div>
                </div>

                @if (count($deptSups) == 1)
                    @php 
                        $sItem = $deptSups[0];
                        $sSup = $sItem['supervisor'];
                    @endphp
                    {{-- 4.1 Single Supervisor Profile Banner --}}
                    <div class="card border-0 mb-4 shadow-sm" style="border-radius: 18px; border: 2px solid #818cf8 !important; background: #ffffff;">
                        <div class="p-3.5 p-md-4" style="background: linear-gradient(135deg, #eef2ff 0%, #ffffff 100%);">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                                <div class="d-flex align-items-center gap-3 min-w-0">
                                    <div class="position-relative">
                                        <img src="{{ $sSup['avatar'] }}" alt="{{ $sSup['name'] }}"
                                             class="rounded-circle border border-2 border-white shadow-xs"
                                             style="width: 58px; height: 58px; object-fit: cover; background: #fff;"
                                             onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($sSup['name']) }}';">
                                        <span class="position-absolute bottom-0 end-0 badge p-1 rounded-circle bg-primary text-white border border-white" title="Supervisor">
                                            <i class="ti ti-crown" style="font-size: 0.75rem;"></i>
                                        </span>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <h4 class="mb-0 fw-bold text-dark text-truncate" style="letter-spacing: -0.02em;">
                                                {{ $sSup['name'] }}
                                            </h4>
                                            <span class="badge rounded-pill px-2.5 py-1"
                                                  style="font-size: 0.75rem; font-weight: 800;
                                                  @if ($sSup['status_type'] === 'present_in') background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0;
                                                  @elseif ($sSup['status_type'] === 'present_out') background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;
                                                  @elseif ($sSup['status_type'] === 'leave') background: #ffedd5; color: #c2410c; border: 1px solid #fed7aa;
                                                  @else background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca;
                                                  @endif">
                                                <i class="ti {{ $sSup['status_icon'] }} me-1"></i>{{ $sSup['status_label'] }}
                                            </span>
                                        </div>
                                        <div class="text-primary fw-bold" style="font-size: 0.92rem; margin-top: 2px;">
                                            {{ $sSup['code'] }} · <span class="text-dark">{{ $sSup['designation'] ?: 'Supervisor' }}</span> ({{ $sSup['department'] }})
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-label-primary px-3 py-2 rounded-pill" style="font-size: 0.85rem; font-weight: 800;">
                                        <i class="ti ti-users me-1"></i> {{ $sItem['employee_count'] }} Team Members
                                    </span>
                                    <a href="{{ route('employees.show', $sSup['id']) }}" class="btn btn-outline-primary rounded-pill px-3 py-1.5" style="font-weight: 750; font-size: 0.82rem;">
                                        View Profile <i class="ti ti-chevron-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 4.2 Single Supervisor 3 Stat Cards --}}
                    <div class="row g-3 g-xl-4 mb-4 emp-stat-row">
                        <div class="col-12 col-md-4">
                            <div class="card h-100 border-0 position-relative overflow-hidden emp-stat-card"
                                 style="border-radius: 18px; border-bottom: 5px solid #6366f1 !important; background: linear-gradient(180deg,#ffffff 0%,#eef2ff 100%); box-shadow: 0 6px 22px rgba(99,102,241,0.12);">
                                <div class="card-body p-4 p-xl-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div class="d-flex align-items-center justify-content-center"
                                             style="width: 52px; height: 52px; border-radius: 14px; background: rgba(99, 102, 241, 0.15); color: #4f46e5;">
                                            <i class="ti ti-calendar-event" style="font-size:1.6rem;"></i>
                                        </div>
                                        <span class="badge rounded-pill px-3 py-2" style="background: rgba(99, 102, 241, 0.14); color: #4338ca; font-size: 0.88rem; font-weight: 800;">
                                            Attendance
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-baseline gap-2 my-2">
                                        <h1 class="mb-0" style="font-size: 2.75rem; line-height: 1; font-weight: 900; color: #4f46e5; letter-spacing: -0.04em;">{{ $sSup['month_present'] ?? 0 }}</h1>
                                        <span style="font-size: 1.15rem; font-weight: 800; color: #0f172a;">Days</span>
                                    </div>
                                    <h5 class="card-title mb-3" style="font-size: 1.2rem; font-weight: 800; color: #0f172a; letter-spacing: -0.02em;">Total Attendance</h5>
                                    <div class="d-flex justify-content-between align-items-center pt-3 border-top" style="font-size: 0.95rem; font-weight: 700;">
                                        <span style="color:#475569;">Month Total</span>
                                        <span class="badge bg-success px-3 py-2 rounded-pill" style="font-size: 0.9rem; font-weight: 800;">
                                            {{ $sSup['month_present'] ?? 0 }} Days
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="card h-100 border-0 position-relative overflow-hidden emp-stat-card"
                                 style="border-radius: 18px; border-bottom: 5px solid #10b981 !important; background: linear-gradient(180deg,#ffffff 0%,#ecfdf5 100%); box-shadow: 0 6px 22px rgba(16,185,129,0.12);">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div class="d-flex align-items-center justify-content-center"
                                             style="width: 52px; height: 52px; border-radius: 14px; background: rgba(16, 185, 129, 0.15); color: #059669;">
                                            <i class="ti ti-login" style="font-size:1.6rem;"></i>
                                        </div>
                                        <span class="badge rounded-pill px-3 py-2" style="background: rgba(16, 185, 129, 0.14); color: #047857; font-size: 0.88rem; font-weight: 800;">
                                            Total: {{ $sSup['total_punch_in_count'] ?? 0 }}
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-baseline gap-2 my-2">
                                        <h1 class="mb-0" style="font-size: 2.75rem; line-height: 1; font-weight: 900; color: #059669; letter-spacing: -0.04em;">{{ $sSup['total_punch_in_count'] ?? 0 }}</h1>
                                        <span style="font-size: 1.15rem; font-weight: 800; color: #0f172a;">Punches</span>
                                    </div>
                                    <h5 class="card-title mb-3" style="font-size: 1.2rem; font-weight: 800; color: #0f172a; letter-spacing: -0.02em;">Punch In Count</h5>
                                    <div class="d-flex justify-content-between align-items-center pt-3 border-top" style="font-size: 0.95rem; font-weight: 700;">
                                        <span style="color:#475569;">Today's In Time</span>
                                        <span class="badge bg-{{ $sSup['punch_in_time'] ? 'success' : 'secondary' }} px-3 py-2 rounded-pill" style="font-size: 0.9rem; font-weight: 800;">
                                            {{ $sSup['punch_in_time'] ?: '--:--' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="card h-100 border-0 position-relative overflow-hidden emp-stat-card"
                                 style="border-radius: 18px; border-bottom: 5px solid #ef4444 !important; background: linear-gradient(180deg,#ffffff 0%,#fef2f2 100%); box-shadow: 0 6px 22px rgba(239,68,68,0.12);">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div class="d-flex align-items-center justify-content-center"
                                             style="width: 52px; height: 52px; border-radius: 14px; background: rgba(239, 68, 68, 0.15); color: #dc2626;">
                                            <i class="ti ti-logout" style="font-size:1.6rem;"></i>
                                        </div>
                                        <span class="badge rounded-pill px-3 py-2" style="background: rgba(239, 68, 68, 0.14); color: #b91c1c; font-size: 0.88rem; font-weight: 800;">
                                            Total: {{ $sSup['total_punch_out_count'] ?? 0 }}
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-baseline gap-2 my-2">
                                        <h1 class="mb-0" style="font-size: 2.75rem; line-height: 1; font-weight: 900; color: #dc2626; letter-spacing: -0.04em;">{{ $sSup['total_punch_out_count'] ?? 0 }}</h1>
                                        <span style="font-size: 1.15rem; font-weight: 800; color: #0f172a;">Punches</span>
                                    </div>
                                    <h5 class="card-title mb-3" style="font-size: 1.2rem; font-weight: 800; color: #0f172a; letter-spacing: -0.02em;">Punch Out Count</h5>
                                    <div class="d-flex justify-content-between align-items-center pt-3 border-top" style="font-size: 0.95rem; font-weight: 700;">
                                        <span style="color:#475569;">Today's Out Time</span>
                                        <span class="badge bg-{{ $sSup['punch_out_time'] ? 'danger' : 'secondary' }} px-3 py-2 rounded-pill" style="font-size: 0.9rem; font-weight: 800;">
                                            {{ $sSup['punch_out_time'] ?: '--:--' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 4.3 Single Supervisor Leave Balance --}}
                    <div class="row g-3 g-xl-4 mb-4">
                        <div class="col-12 mb-1">
                            <h5 class="mb-0 d-flex align-items-center" style="font-size: 1.35rem; font-weight: 900; color: #0f172a; letter-spacing: -0.02em;">
                                <i class="ti ti-calendar-event text-primary me-2" style="font-size:1.5rem;"></i> Leave Balance (Type-Wise) - {{ $sSup['name'] }}
                            </h5>
                        </div>

                        @if (!empty($sSup['leave_balances']) && count($sSup['leave_balances']) > 0)
                            @foreach ($sSup['leave_balances'] as $leave)
                                @php
                                    $pal = $leave['palette'] ?? ['bg' => 'rgba(115, 103, 240, 0.1)', 'border' => '#7367f0', 'text' => '#7367f0'];
                                    $leaveCount = count($sSup['leave_balances']);
                                    $leaveCol = $leaveCount <= 2 ? 'col-12 col-lg-6' : 'col-12 col-md-4';
                                @endphp
                                <div class="{{ $leaveCol }}">
                                    <div class="card h-100 border-0 position-relative overflow-hidden emp-stat-card"
                                         style="border-radius: 18px; border-left: 6px solid {{ $pal['border'] }} !important; background: #ffffff; box-shadow: 0 6px 20px rgba(15,23,42,0.06); min-height: 150px;">
                                        <div class="card-body p-4">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="d-flex align-items-center justify-content-center"
                                                         style="width: 56px; height: 56px; border-radius: 14px; background: {{ $pal['bg'] }}; color: {{ $pal['text'] }}; font-size: 1.2rem; font-weight: 900; flex-shrink: 0;">
                                                        {{ $leave['code'] }}
                                                    </div>
                                                    <div>
                                                        <h5 class="mb-1 text-truncate" style="font-size: 1.2rem; font-weight: 800; color: #0f172a; letter-spacing: -0.02em; max-width: 220px;" title="{{ $leave['name'] }}">
                                                            {{ $leave['name'] }}
                                                        </h5>
                                                        <div class="d-flex align-items-baseline gap-2 mt-1">
                                                            <span style="color: {{ $pal['text'] }}; font-size: 2rem; font-weight: 900; line-height: 1; letter-spacing: -0.03em;">
                                                                {{ number_format($leave['balance'], 1) }}
                                                            </span>
                                                            <span style="color: #0f172a; font-size: 1.05rem; font-weight: 800;">Days Available</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <span class="badge rounded-pill px-3 py-2" style="font-size: 0.82rem; font-weight: 750; background: #f8fafc; color: #334155; border: 1px solid #e2e8f0;">
                                                    {{ !empty($leave['carry_forward']) ? 'Carry Forward' : 'Monthly Accrual' }}
                                                </span>
                                            </div>

                                            <div class="border-top pt-3 d-flex justify-content-between align-items-center" style="font-size: 0.98rem; font-weight: 700; color: #475569;">
                                                <span>Allocated: <strong style="color:#0f172a; font-weight: 900;">{{ number_format($leave['allocated'] ?? 0, 1) }}</strong></span>
                                                <span>Used (FY): <strong style="color:#dc2626; font-weight: 900;">{{ number_format($leave['used_year'] ?? 0, 1) }}</strong></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                @else
                    {{-- 4.4 Multi Supervisors Grid (2+) --}}
                    <div class="row g-3 mb-4">
                        @php
                            $supGridCol = count($deptSups) == 2 ? 'col-12 col-md-6' : 'col-12 col-md-4';
                        @endphp
                        @foreach ($deptSups as $sItem)
                            @php $sSup = $sItem['supervisor']; @endphp
                            <div class="{{ $supGridCol }}">
                                <div class="card h-100 border-0 overflow-hidden shadow-xs"
                                     style="border-radius: 16px; border: 1.5px solid #a5b4fc !important; background: #ffffff;">
                                    <div class="p-3" style="background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%); border-bottom: 1px solid #ddd6fe;">
                                        <div class="d-flex align-items-center justify-content-between gap-2">
                                            <div class="d-flex align-items-center gap-2 min-w-0 flex-grow-1">
                                                <img src="{{ $sSup['avatar'] }}" alt="{{ $sSup['name'] }}"
                                                     class="rounded-circle border"
                                                     style="width: 44px; height: 44px; object-fit: cover; flex-shrink: 0; background: #fff;"
                                                     onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($sSup['name']) }}';">
                                                <div class="min-w-0 flex-grow-1">
                                                    <div class="text-truncate fw-bold text-dark" style="font-size: 1rem;" title="{{ $sSup['name'] }}">
                                                        {{ $sSup['name'] }}
                                                    </div>
                                                    <div class="text-primary text-truncate" style="font-size: 0.78rem; font-weight: 750;">
                                                        {{ $sSup['code'] }} · {{ $sSup['designation'] ?: 'Supervisor' }}
                                                    </div>
                                                </div>
                                            </div>
                                            <span class="badge rounded-pill px-2 py-1 flex-shrink-0"
                                                  style="font-size: 0.72rem; font-weight: 800;
                                                  @if ($sSup['status_type'] === 'present_in') background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0;
                                                  @elseif ($sSup['status_type'] === 'present_out') background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;
                                                  @elseif ($sSup['status_type'] === 'leave') background: #ffedd5; color: #c2410c; border: 1px solid #fed7aa;
                                                  @else background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca;
                                                  @endif">
                                                <i class="ti {{ $sSup['status_icon'] }} me-1"></i>{{ $sSup['status_label'] }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="card-body p-3 d-flex flex-column justify-content-between">
                                        <div class="row g-2 text-center mb-2">
                                            <div class="col-4">
                                                <div class="p-1.5 rounded-2" style="background: #eff6ff; border: 1px solid #dbeafe;">
                                                    <div class="fw-bolder text-primary" style="font-size: 1.15rem; line-height: 1.1;">{{ $sSup['month_present'] }}</div>
                                                    <div class="text-muted" style="font-size: 0.7rem; font-weight: 700; margin-top: 2px;">Days MTD</div>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="p-1.5 rounded-2" style="background: #ecfdf5; border: 1px solid #a7f3d0;">
                                                    <div class="fw-bolder text-success text-truncate" style="font-size: 0.85rem; line-height: 1.4;">{{ $sSup['punch_in_time'] ?: '—' }}</div>
                                                    <div class="text-muted" style="font-size: 0.7rem; font-weight: 700; margin-top: 2px;">In Time</div>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="p-1.5 rounded-2" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                                                    <div class="fw-bolder text-secondary text-truncate" style="font-size: 0.85rem; line-height: 1.4;">{{ $sSup['punch_out_time'] ?: '—' }}</div>
                                                    <div class="text-muted" style="font-size: 0.7rem; font-weight: 700; margin-top: 2px;">Out Time</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-center justify-content-between py-1.5 border-top border-bottom my-1.5">
                                            <span style="font-size: 0.8rem; font-weight: 750; color: #475569;">
                                                <i class="ti ti-users me-1 text-primary"></i> Team Size:
                                            </span>
                                            <span class="badge bg-label-primary rounded-pill px-2.5 py-0.5" style="font-weight: 800; font-size: 0.78rem;">
                                                {{ $sItem['employee_count'] }} Employees
                                            </span>
                                        </div>

                                        @if (!empty($sSup['leave_balances']) && count($sSup['leave_balances']) > 0)
                                            <div class="d-flex flex-wrap gap-1 mt-1">
                                                @foreach ($sSup['leave_balances'] as $lb)
                                                    <span class="badge px-2 py-0.5 rounded-1"
                                                          style="background: #f8fafc; color: #334155; border: 1px solid #e2e8f0; font-size: 0.72rem; font-weight: 700;">
                                                        {{ $lb['code'] }}: <strong style="color: #0f172a; font-weight: 850;">{{ number_format($lb['balance'], 1) }}</strong>
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- 4.5 Employees under each Supervisor --}}
                @foreach ($deptSups as $sItem)
                    @php 
                        $sSup = $sItem['supervisor'];
                        $sEmpList = $sItem['employees'];
                    @endphp

                    <div class="card border-0 mb-4 shadow-sm" style="border-radius: 16px; border: 1.5px solid #e2e8f0 !important; background: #ffffff;">
                        <div class="card-body p-3.5 p-md-4">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 pb-2.5 border-bottom">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-label-primary p-2 rounded-2">
                                        <i class="ti ti-users fs-4"></i>
                                    </span>
                                    <div>
                                        <h5 class="mb-0 fw-bolder text-dark">
                                            Team Members under <span class="text-primary">{{ $sSup['name'] }}</span> (Supervisor)
                                        </h5>
                                        <span class="text-muted" style="font-size: 0.82rem; font-weight: 650;">Total {{ count($sEmpList) }} Team Members</span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                    <span class="badge px-2.5 py-1.5 rounded-pill" style="background:#dcfce7; color:#166534; font-weight:800; font-size:.8rem;">
                                        {{ $sItem['in_count'] }} In
                                    </span>
                                    @if ($sItem['out_count'] > 0)
                                        <span class="badge px-2.5 py-1.5 rounded-pill" style="background:#f1f5f9; color:#475569; font-weight:800; font-size:.8rem;">
                                            {{ $sItem['out_count'] }} Out
                                        </span>
                                    @endif
                                    <span class="badge px-2.5 py-1.5 rounded-pill" style="background:#fee2e2; color:#991b1b; font-weight:800; font-size:.8rem;">
                                        {{ $sItem['not_punched_count'] }} Not Punched
                                    </span>
                                    @if ($sItem['leave_count'] > 0)
                                        <span class="badge px-2.5 py-1.5 rounded-pill" style="background:#ffedd5; color:#9a3412; font-weight:800; font-size:.8rem;">
                                            {{ $sItem['leave_count'] }} Leave
                                        </span>
                                    @endif
                                </div>
                            </div>

                            @if (!empty($sEmpList) && count($sEmpList) > 0)
                                <div class="row g-3">
                                    @foreach ($sEmpList as $cEmp)
                                        <div class="col-12 col-md-4">
                                            <div class="card h-100 border-0 shadow-sm employee-mini-card"
                                                 style="border-radius: 16px; border: 1.5px solid #e2e8f0 !important; background: #ffffff; overflow: hidden;">
                                                
                                                {{-- Employee Header --}}
                                                <div class="p-3" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-bottom: 1px solid #eef2f7;">
                                                    <div class="d-flex align-items-center justify-content-between gap-2">
                                                        <div class="d-flex align-items-center gap-2.5 min-w-0 flex-grow-1">
                                                            <div class="position-relative flex-shrink-0">
                                                                <img src="{{ $cEmp['avatar'] }}" alt="{{ $cEmp['name'] }}"
                                                                     class="rounded-circle border border-2 border-white shadow-xs"
                                                                     style="width: 44px; height: 44px; object-fit: cover; background: #fff;"
                                                                     onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($cEmp['name']) }}';">
                                                            </div>
                                                            <div class="min-w-0 flex-grow-1">
                                                                <div class="text-truncate fw-bold text-dark" style="font-size: 0.96rem; letter-spacing: -0.01em;" title="{{ $cEmp['name'] }}">
                                                                    {{ $cEmp['name'] }}
                                                                </div>
                                                                <div class="text-muted text-truncate" style="font-size: 0.76rem; font-weight: 600; margin-top: 1px;">
                                                                    <span class="text-primary fw-bold">{{ $cEmp['code'] }}</span> · {{ $cEmp['designation'] ?: 'Employee' }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <span class="badge rounded-pill px-2.5 py-1.5 flex-shrink-0"
                                                              style="font-size: 0.72rem; font-weight: 800; letter-spacing: 0.02em;
                                                              @if ($cEmp['status_type'] === 'present_in') background: #dcfce7; color: #15803d; border: 1px solid #86efac;
                                                              @elseif ($cEmp['status_type'] === 'present_out') background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;
                                                              @elseif ($cEmp['status_type'] === 'leave') background: #ffedd5; color: #c2410c; border: 1px solid #fdba74;
                                                              @else background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5;
                                                              @endif">
                                                            <i class="ti {{ $cEmp['status_icon'] }} me-1" style="font-size: 0.75rem;"></i>{{ $cEmp['status_label'] }}
                                                        </span>
                                                    </div>
                                                </div>

                                                {{-- Employee Body --}}
                                                <div class="card-body p-3 d-flex flex-column justify-content-between">
                                                    {{-- 3 Attendance Stats Row --}}
                                                    <div class="row g-2 text-center mb-2.5">
                                                        <div class="col-4">
                                                            <div class="p-2 rounded-3 text-center" style="background: linear-gradient(180deg, #ffffff 0%, #eef2ff 100%); border: 1.5px solid #e0e7ff; box-shadow: 0 1px 3px rgba(99,102,241,0.04);">
                                                                <div class="fw-bolder" style="color: #4338ca; font-size: 1.25rem; line-height: 1.1; letter-spacing: -0.03em;">{{ $cEmp['month_present'] }}</div>
                                                                <div style="font-size: 0.68rem; font-weight: 800; color: #6366f1; text-transform: uppercase; margin-top: 3px; letter-spacing: 0.03em;">MTD</div>
                                                            </div>
                                                        </div>
                                                        <div class="col-4">
                                                            <div class="p-2 rounded-3 text-center" style="background: linear-gradient(180deg, #ffffff 0%, #ecfdf5 100%); border: 1.5px solid #d1fae5; box-shadow: 0 1px 3px rgba(16,185,129,0.04);">
                                                                <div class="fw-bolder text-truncate" style="color: {{ $cEmp['punch_in_time'] ? '#047857' : '#94a3b8' }}; font-size: 0.85rem; line-height: 1.45; font-weight: 850;">
                                                                    {{ $cEmp['punch_in_time'] ?: '—' }}
                                                                </div>
                                                                <div style="font-size: 0.68rem; font-weight: 800; color: #059669; text-transform: uppercase; margin-top: 3px; letter-spacing: 0.03em;">In Time</div>
                                                            </div>
                                                        </div>
                                                        <div class="col-4">
                                                            <div class="p-2 rounded-3 text-center" style="background: linear-gradient(180deg, #ffffff 0%, #fef2f2 100%); border: 1.5px solid #fee2e2; box-shadow: 0 1px 3px rgba(239,68,68,0.04);">
                                                                <div class="fw-bolder text-truncate" style="color: {{ $cEmp['punch_out_time'] ? '#b91c1c' : '#94a3b8' }}; font-size: 0.85rem; line-height: 1.45; font-weight: 850;">
                                                                    {{ $cEmp['punch_out_time'] ?: '—' }}
                                                                </div>
                                                                <div style="font-size: 0.68rem; font-weight: 800; color: #dc2626; text-transform: uppercase; margin-top: 3px; letter-spacing: 0.03em;">Out Time</div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- Leave Balances & View Profile Footer --}}
                                                    <div class="d-flex align-items-center justify-content-between pt-2 border-top gap-1.5" style="border-color: #f1f5f9 !important;">
                                                        @if (!empty($cEmp['leave_balances']) && count($cEmp['leave_balances']) > 0)
                                                            <div class="d-flex flex-wrap align-items-center gap-1.5 flex-grow-1 min-w-0">
                                                                @foreach ($cEmp['leave_balances'] as $lb)
                                                                    @php $pal = $lb['palette'] ?? ['bg' => 'rgba(115, 103, 240, 0.1)', 'border' => '#7367f0', 'text' => '#7367f0']; @endphp
                                                                    <span class="badge px-2.5 py-1 rounded-pill"
                                                                          style="background: {{ $pal['bg'] }}; color: {{ $pal['text'] }}; border: 1px solid {{ $pal['border'] }}40; font-size: 0.72rem; font-weight: 750;"
                                                                          title="{{ $lb['name'] }}: {{ $lb['balance'] }} available">
                                                                        {{ $lb['code'] }}: <strong style="font-weight: 900;">{{ number_format($lb['balance'], 1) }}</strong>
                                                                    </span>
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            <span class="text-muted" style="font-size: 0.72rem;">No leave data</span>
                                                        @endif
                                                        <a href="{{ route('employees.show', $cEmp['id']) }}" class="btn btn-sm btn-icon btn-light rounded-circle shadow-none flex-shrink-0"
                                                           title="View Profile" style="width: 30px; height: 30px; color: #6366f1; border: 1px solid #e2e8f0; display: inline-flex; align-items: center; justify-content: center;">
                                                            <i class="ti ti-chevron-right" style="font-size: 0.85rem;"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-muted py-2" style="font-size: 0.85rem;">No team members assigned under this supervisor.</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            @endif

            {{-- 5. Direct Employees under Single Department Head (if any) --}}
            @if (!empty($deptDirectEmps) && count($deptDirectEmps) > 0)
                <div class="card border-0 mb-4 shadow-sm" style="border-radius: 16px; border: 1.5px solid #e2e8f0 !important; background: #ffffff;">
                    <div class="card-body p-3.5 p-md-4">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 pb-2.5 border-bottom">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-label-info p-2 rounded-2">
                                    <i class="ti ti-users fs-4"></i>
                                </span>
                                <div>
                                    <h5 class="mb-0 fw-bolder text-dark">
                                        Direct Team Members under <span class="text-primary">{{ $deptHead['name'] }}</span>
                                    </h5>
                                    <span class="text-muted" style="font-size: 0.82rem; font-weight: 650;">Total {{ count($deptDirectEmps) }} Team Members</span>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            @foreach ($deptDirectEmps as $cEmp)
                                <div class="col-12 col-md-4">
                                    <div class="card h-100 border-0 shadow-sm employee-mini-card"
                                         style="border-radius: 16px; border: 1.5px solid #e2e8f0 !important; background: #ffffff; overflow: hidden;">
                                        
                                        {{-- Employee Header --}}
                                        <div class="p-3" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-bottom: 1px solid #eef2f7;">
                                            <div class="d-flex align-items-center justify-content-between gap-2">
                                                <div class="d-flex align-items-center gap-2.5 min-w-0 flex-grow-1">
                                                    <div class="position-relative flex-shrink-0">
                                                        <img src="{{ $cEmp['avatar'] }}" alt="{{ $cEmp['name'] }}"
                                                             class="rounded-circle border border-2 border-white shadow-xs"
                                                             style="width: 44px; height: 44px; object-fit: cover; background: #fff;"
                                                             onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($cEmp['name']) }}';">
                                                    </div>
                                                    <div class="min-w-0 flex-grow-1">
                                                        <div class="text-truncate fw-bold text-dark" style="font-size: 0.96rem; letter-spacing: -0.01em;" title="{{ $cEmp['name'] }}">
                                                            {{ $cEmp['name'] }}
                                                        </div>
                                                        <div class="text-muted text-truncate" style="font-size: 0.76rem; font-weight: 600; margin-top: 1px;">
                                                            <span class="text-primary fw-bold">{{ $cEmp['code'] }}</span> · {{ $cEmp['designation'] ?: 'Employee' }}
                                                        </div>
                                                    </div>
                                                </div>
                                                <span class="badge rounded-pill px-2.5 py-1.5 flex-shrink-0"
                                                      style="font-size: 0.72rem; font-weight: 800; letter-spacing: 0.02em;
                                                      @if ($cEmp['status_type'] === 'present_in') background: #dcfce7; color: #15803d; border: 1px solid #86efac;
                                                      @elseif ($cEmp['status_type'] === 'present_out') background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;
                                                      @elseif ($cEmp['status_type'] === 'leave') background: #ffedd5; color: #c2410c; border: 1px solid #fdba74;
                                                      @else background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5;
                                                      @endif">
                                                    <i class="ti {{ $cEmp['status_icon'] }} me-1" style="font-size: 0.75rem;"></i>{{ $cEmp['status_label'] }}
                                                </span>
                                            </div>
                                        </div>

                                        {{-- Employee Body --}}
                                        <div class="card-body p-3 d-flex flex-column justify-content-between">
                                            {{-- 3 Attendance Stats Row --}}
                                            <div class="row g-2 text-center mb-2.5">
                                                <div class="col-4">
                                                    <div class="p-2 rounded-3 text-center" style="background: linear-gradient(180deg, #ffffff 0%, #eef2ff 100%); border: 1.5px solid #e0e7ff; box-shadow: 0 1px 3px rgba(99,102,241,0.04);">
                                                        <div class="fw-bolder" style="color: #4338ca; font-size: 1.25rem; line-height: 1.1; letter-spacing: -0.03em;">{{ $cEmp['month_present'] }}</div>
                                                        <div style="font-size: 0.68rem; font-weight: 800; color: #6366f1; text-transform: uppercase; margin-top: 3px; letter-spacing: 0.03em;">MTD</div>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="p-2 rounded-3 text-center" style="background: linear-gradient(180deg, #ffffff 0%, #ecfdf5 100%); border: 1.5px solid #d1fae5; box-shadow: 0 1px 3px rgba(16,185,129,0.04);">
                                                        <div class="fw-bolder text-truncate" style="color: {{ $cEmp['punch_in_time'] ? '#047857' : '#94a3b8' }}; font-size: 0.85rem; line-height: 1.45; font-weight: 850;">
                                                            {{ $cEmp['punch_in_time'] ?: '—' }}
                                                        </div>
                                                        <div style="font-size: 0.68rem; font-weight: 800; color: #059669; text-transform: uppercase; margin-top: 3px; letter-spacing: 0.03em;">In Time</div>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="p-2 rounded-3 text-center" style="background: linear-gradient(180deg, #ffffff 0%, #fef2f2 100%); border: 1.5px solid #fee2e2; box-shadow: 0 1px 3px rgba(239,68,68,0.04);">
                                                        <div class="fw-bolder text-truncate" style="color: {{ $cEmp['punch_out_time'] ? '#b91c1c' : '#94a3b8' }}; font-size: 0.85rem; line-height: 1.45; font-weight: 850;">
                                                            {{ $cEmp['punch_out_time'] ?: '—' }}
                                                        </div>
                                                        <div style="font-size: 0.68rem; font-weight: 800; color: #dc2626; text-transform: uppercase; margin-top: 3px; letter-spacing: 0.03em;">Out Time</div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Leave Balances & View Profile Footer --}}
                                            <div class="d-flex align-items-center justify-content-between pt-2 border-top gap-1.5" style="border-color: #f1f5f9 !important;">
                                                @if (!empty($cEmp['leave_balances']) && count($cEmp['leave_balances']) > 0)
                                                    <div class="d-flex flex-wrap align-items-center gap-1.5 flex-grow-1 min-w-0">
                                                        @foreach ($cEmp['leave_balances'] as $lb)
                                                            @php $pal = $lb['palette'] ?? ['bg' => 'rgba(115, 103, 240, 0.1)', 'border' => '#7367f0', 'text' => '#7367f0']; @endphp
                                                            <span class="badge px-2.5 py-1 rounded-pill"
                                                                  style="background: {{ $pal['bg'] }}; color: {{ $pal['text'] }}; border: 1px solid {{ $pal['border'] }}40; font-size: 0.72rem; font-weight: 750;"
                                                                  title="{{ $lb['name'] }}: {{ $lb['balance'] }} available">
                                                                {{ $lb['code'] }}: <strong style="font-weight: 900;">{{ number_format($lb['balance'], 1) }}</strong>
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <span class="text-muted" style="font-size: 0.72rem;">No leave data</span>
                                                @endif
                                                <a href="{{ route('employees.show', $cEmp['id']) }}" class="btn btn-sm btn-icon btn-light rounded-circle shadow-none flex-shrink-0"
                                                   title="View Profile" style="width: 30px; height: 30px; color: #6366f1; border: 1px solid #e2e8f0; display: inline-flex; align-items: center; justify-content: center;">
                                                    <i class="ti ti-chevron-right" style="font-size: 0.85rem;"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

        {{-- Multiple Department Heads Layout (2+) --}}
        @else
            {{-- Iterate Each Department Head Container --}}
            @foreach ($departmentHeadsHierarchy as $dh)
                @php 
                    $deptHead = $dh['dept_head'];
                    $deptSups = $dh['supervisors'];
                    $deptDirectEmps = $dh['direct_employees'];
                @endphp

                <div class="card border-0 mb-4 shadow-sm" style="border-radius: 20px; border: 2px solid #6366f1 !important; background: #ffffff;">
                    {{-- Department Head Card Header Banner --}}
                    <div class="p-3.5 p-md-4" style="background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%); border-bottom: 2px solid #c7d2fe; border-radius: 18px 18px 0 0;">
                        <div class="row align-items-center g-3">
                            <div class="col-12 col-lg-7">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="position-relative">
                                        <img src="{{ $deptHead['avatar'] }}" alt="{{ $deptHead['name'] }}"
                                             class="rounded-circle border border-2 border-white shadow-sm"
                                             style="width: 58px; height: 58px; object-fit: cover; background: #fff;"
                                             onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($deptHead['name']) }}';">
                                        <span class="position-absolute bottom-0 end-0 badge p-1 rounded-circle bg-warning text-dark border border-white" title="Department Head">
                                            <i class="ti ti-shield-check" style="font-size: 0.75rem;"></i>
                                        </span>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <h4 class="mb-0 fw-bolder text-dark" style="letter-spacing: -0.02em;">{{ $deptHead['name'] }}</h4>
                                            <span class="badge rounded-pill px-2.5 py-1"
                                                  style="font-size: 0.75rem; font-weight: 800;
                                                  @if ($deptHead['status_type'] === 'present_in') background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0;
                                                  @elseif ($deptHead['status_type'] === 'present_out') background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;
                                                  @elseif ($deptHead['status_type'] === 'leave') background: #ffedd5; color: #c2410c; border: 1px solid #fed7aa;
                                                  @else background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca;
                                                  @endif">
                                                <i class="ti {{ $deptHead['status_icon'] }} me-1"></i>{{ $deptHead['status_label'] }}
                                            </span>
                                        </div>
                                        <div class="text-primary fw-bold" style="font-size: 0.92rem; margin-top: 2px;">
                                            {{ $deptHead['code'] }} · <span class="text-dark">{{ $deptHead['designation'] ?: 'Department Head' }}</span> ({{ $deptHead['department'] }})
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Department Head Live Punch & Stats Capsule --}}
                            <div class="col-12 col-lg-5">
                                <div class="d-flex align-items-center justify-content-lg-end gap-2 flex-wrap">
                                    <div class="p-2 px-3 rounded-3 bg-white border text-center shadow-xs">
                                        <div class="fw-bolder text-primary" style="font-size: 1.1rem; line-height: 1;">{{ $deptHead['month_present'] }}</div>
                                        <div class="text-muted" style="font-size: 0.68rem; font-weight: 800; margin-top: 2px;">Days MTD</div>
                                    </div>
                                    <div class="p-2 px-3 rounded-3 bg-white border text-center shadow-xs">
                                        <div class="fw-bolder text-success" style="font-size: 0.88rem; line-height: 1.2;">{{ $deptHead['punch_in_time'] ?: '—' }}</div>
                                        <div class="text-muted" style="font-size: 0.68rem; font-weight: 800; margin-top: 2px;">In Time</div>
                                    </div>
                                    <div class="p-2 px-3 rounded-3 bg-white border text-center shadow-xs">
                                        <div class="fw-bolder text-secondary" style="font-size: 0.88rem; line-height: 1.2;">{{ $deptHead['punch_out_time'] ?: '—' }}</div>
                                        <div class="text-muted" style="font-size: 0.68rem; font-weight: 800; margin-top: 2px;">Out Time</div>
                                    </div>
                                    <div class="p-2 px-3 rounded-3 bg-primary text-white text-center shadow-xs">
                                        <div class="fw-bolder" style="font-size: 1.1rem; line-height: 1;">{{ $dh['supervisor_count'] }}</div>
                                        <div class="text-white-50" style="font-size: 0.68rem; font-weight: 800; margin-top: 2px;">Supervisors</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Department Head Body: Supervisors and Employees --}}
                    <div class="card-body p-3.5 p-md-4" style="background: #f8fafc;">
                        {{-- 1. Supervisors under this Department Head --}}
                        @if (!empty($deptSups) && count($deptSups) > 0)
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h5 class="mb-0 fw-bold text-dark">
                                    <i class="ti ti-user-star text-primary me-2"></i> Supervisors under {{ $deptHead['name'] }} ({{ count($deptSups) }})
                                </h5>
                            </div>

                            {{-- Supervisors Cards Grid --}}
                            <div class="row g-3 mb-4">
                                @php
                                    $supGridCol = count($deptSups) == 2 ? 'col-12 col-md-6' : 'col-12 col-md-4';
                                @endphp
                                @foreach ($deptSups as $sItem)
                                    @php $sSup = $sItem['supervisor']; @endphp
                                    <div class="{{ $supGridCol }}">
                                        <div class="card h-100 border-0 overflow-hidden shadow-xs"
                                             style="border-radius: 16px; border: 1.5px solid #a5b4fc !important; background: #ffffff;">
                                            <div class="p-3" style="background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%); border-bottom: 1px solid #ddd6fe;">
                                                <div class="d-flex align-items-center justify-content-between gap-2">
                                                    <div class="d-flex align-items-center gap-2 min-w-0 flex-grow-1">
                                                        <img src="{{ $sSup['avatar'] }}" alt="{{ $sSup['name'] }}"
                                                             class="rounded-circle border"
                                                             style="width: 44px; height: 44px; object-fit: cover; flex-shrink: 0; background: #fff;"
                                                             onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($sSup['name']) }}';">
                                                        <div class="min-w-0 flex-grow-1">
                                                            <div class="text-truncate fw-bold text-dark" style="font-size: 1rem;" title="{{ $sSup['name'] }}">
                                                                {{ $sSup['name'] }}
                                                            </div>
                                                            <div class="text-primary text-truncate" style="font-size: 0.78rem; font-weight: 750;">
                                                                {{ $sSup['code'] }} · {{ $sSup['designation'] ?: 'Supervisor' }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <span class="badge rounded-pill px-2 py-1 flex-shrink-0"
                                                          style="font-size: 0.72rem; font-weight: 800;
                                                          @if ($sSup['status_type'] === 'present_in') background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0;
                                                          @elseif ($sSup['status_type'] === 'present_out') background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;
                                                          @elseif ($sSup['status_type'] === 'leave') background: #ffedd5; color: #c2410c; border: 1px solid #fed7aa;
                                                          @else background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca;
                                                          @endif">
                                                        <i class="ti {{ $sSup['status_icon'] }} me-1"></i>{{ $sSup['status_label'] }}
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="card-body p-3 d-flex flex-column justify-content-between">
                                                <div class="row g-2 text-center mb-2">
                                                    <div class="col-4">
                                                        <div class="p-1.5 rounded-2" style="background: #eff6ff; border: 1px solid #dbeafe;">
                                                            <div class="fw-bolder text-primary" style="font-size: 1.15rem; line-height: 1.1;">{{ $sSup['month_present'] }}</div>
                                                            <div class="text-muted" style="font-size: 0.7rem; font-weight: 700; margin-top: 2px;">Days MTD</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-4">
                                                        <div class="p-1.5 rounded-2" style="background: #ecfdf5; border: 1px solid #a7f3d0;">
                                                            <div class="fw-bolder text-success text-truncate" style="font-size: 0.85rem; line-height: 1.4;">{{ $sSup['punch_in_time'] ?: '—' }}</div>
                                                            <div class="text-muted" style="font-size: 0.7rem; font-weight: 700; margin-top: 2px;">In Time</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-4">
                                                        <div class="p-1.5 rounded-2" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                                                            <div class="fw-bolder text-secondary text-truncate" style="font-size: 0.85rem; line-height: 1.4;">{{ $sSup['punch_out_time'] ?: '—' }}</div>
                                                            <div class="text-muted" style="font-size: 0.7rem; font-weight: 700; margin-top: 2px;">Out Time</div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="d-flex align-items-center justify-content-between py-1.5 border-top border-bottom my-1.5">
                                                    <span style="font-size: 0.8rem; font-weight: 750; color: #475569;">
                                                        <i class="ti ti-users me-1 text-primary"></i> Team Size:
                                                    </span>
                                                    <span class="badge bg-label-primary rounded-pill px-2.5 py-0.5" style="font-weight: 800; font-size: 0.78rem;">
                                                        {{ $sItem['employee_count'] }} Employees
                                                    </span>
                                                </div>

                                                @if (!empty($sSup['leave_balances']) && count($sSup['leave_balances']) > 0)
                                                    <div class="d-flex flex-wrap gap-1 mt-1">
                                                        @foreach ($sSup['leave_balances'] as $lb)
                                                            <span class="badge px-2 py-0.5 rounded-1"
                                                                  style="background: #f8fafc; color: #334155; border: 1px solid #e2e8f0; font-size: 0.72rem; font-weight: 700;">
                                                                {{ $lb['code'] }}: <strong style="color: #0f172a; font-weight: 850;">{{ number_format($lb['balance'], 1) }}</strong>
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            {{-- 2. Employees under each Supervisor --}}
                            @foreach ($deptSups as $sItem)
                                @php 
                                    $sSup = $sItem['supervisor'];
                                    $sEmpList = $sItem['employees'];
                                @endphp

                                <div class="card border-0 mb-3 shadow-xs" style="border-radius: 14px; border: 1px solid #e2e8f0 !important; background: #ffffff;">
                                    <div class="card-body p-3">
                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2.5 pb-2 border-bottom">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge bg-label-primary p-1.5 rounded-2">
                                                    <i class="ti ti-users fs-5"></i>
                                                </span>
                                                <div>
                                                    <h6 class="mb-0 fw-bolder text-dark">
                                                        Employees under <span class="text-primary">{{ $sSup['name'] }}</span> (Supervisor)
                                                    </h6>
                                                    <span class="text-muted" style="font-size: 0.78rem; font-weight: 650;">Total {{ count($sEmpList) }} Employees</span>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                                <span class="badge px-2.5 py-1 rounded-pill" style="background:#dcfce7; color:#166534; font-weight:800; font-size:.76rem;">
                                                    {{ $sItem['in_count'] }} In
                                                </span>
                                                @if ($sItem['out_count'] > 0)
                                                    <span class="badge px-2.5 py-1 rounded-pill" style="background:#f1f5f9; color:#475569; font-weight:800; font-size:.76rem;">
                                                        {{ $sItem['out_count'] }} Out
                                                    </span>
                                                @endif
                                                <span class="badge px-2.5 py-1 rounded-pill" style="background:#fee2e2; color:#991b1b; font-weight:800; font-size:.76rem;">
                                                    {{ $sItem['not_punched_count'] }} Not Punched
                                                </span>
                                                @if ($sItem['leave_count'] > 0)
                                                    <span class="badge px-2.5 py-1 rounded-pill" style="background:#ffedd5; color:#9a3412; font-weight:800; font-size:.76rem;">
                                                        {{ $sItem['leave_count'] }} Leave
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        @if (!empty($sEmpList) && count($sEmpList) > 0)
                                            <div class="row g-3">
                                                @foreach ($sEmpList as $cEmp)
                                                    <div class="col-12 col-md-4">
                                                        <div class="card h-100 border-0 shadow-sm employee-mini-card"
                                                             style="border-radius: 16px; border: 1.5px solid #e2e8f0 !important; background: #ffffff; overflow: hidden;">
                                                            
                                                            {{-- Employee Header --}}
                                                            <div class="p-3" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-bottom: 1px solid #eef2f7;">
                                                                <div class="d-flex align-items-center justify-content-between gap-2">
                                                                    <div class="d-flex align-items-center gap-2.5 min-w-0 flex-grow-1">
                                                                        <div class="position-relative flex-shrink-0">
                                                                            <img src="{{ $cEmp['avatar'] }}" alt="{{ $cEmp['name'] }}"
                                                                                 class="rounded-circle border border-2 border-white shadow-xs"
                                                                                 style="width: 44px; height: 44px; object-fit: cover; background: #fff;"
                                                                                 onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($cEmp['name']) }}';">
                                                                        </div>
                                                                        <div class="min-w-0 flex-grow-1">
                                                                            <div class="text-truncate fw-bold text-dark" style="font-size: 0.96rem; letter-spacing: -0.01em;" title="{{ $cEmp['name'] }}">
                                                                                {{ $cEmp['name'] }}
                                                                            </div>
                                                                            <div class="text-muted text-truncate" style="font-size: 0.76rem; font-weight: 600; margin-top: 1px;">
                                                                                <span class="text-primary fw-bold">{{ $cEmp['code'] }}</span> · {{ $cEmp['designation'] ?: 'Employee' }}
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <span class="badge rounded-pill px-2.5 py-1.5 flex-shrink-0"
                                                                          style="font-size: 0.72rem; font-weight: 800; letter-spacing: 0.02em;
                                                                          @if ($cEmp['status_type'] === 'present_in') background: #dcfce7; color: #15803d; border: 1px solid #86efac;
                                                                          @elseif ($cEmp['status_type'] === 'present_out') background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;
                                                                          @elseif ($cEmp['status_type'] === 'leave') background: #ffedd5; color: #c2410c; border: 1px solid #fdba74;
                                                                          @else background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5;
                                                                          @endif">
                                                                        <i class="ti {{ $cEmp['status_icon'] }} me-1" style="font-size: 0.75rem;"></i>{{ $cEmp['status_label'] }}
                                                                    </span>
                                                                </div>
                                                            </div>

                                                            {{-- Employee Body --}}
                                                            <div class="card-body p-3 d-flex flex-column justify-content-between">
                                                                {{-- 3 Attendance Stats Row --}}
                                                                <div class="row g-2 text-center mb-2.5">
                                                                    <div class="col-4">
                                                                        <div class="p-2 rounded-3 text-center" style="background: linear-gradient(180deg, #ffffff 0%, #eef2ff 100%); border: 1.5px solid #e0e7ff; box-shadow: 0 1px 3px rgba(99,102,241,0.04);">
                                                                            <div class="fw-bolder" style="color: #4338ca; font-size: 1.25rem; line-height: 1.1; letter-spacing: -0.03em;">{{ $cEmp['month_present'] }}</div>
                                                                            <div style="font-size: 0.68rem; font-weight: 800; color: #6366f1; text-transform: uppercase; margin-top: 3px; letter-spacing: 0.03em;">MTD</div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-4">
                                                                        <div class="p-2 rounded-3 text-center" style="background: linear-gradient(180deg, #ffffff 0%, #ecfdf5 100%); border: 1.5px solid #d1fae5; box-shadow: 0 1px 3px rgba(16,185,129,0.04);">
                                                                            <div class="fw-bolder text-truncate" style="color: {{ $cEmp['punch_in_time'] ? '#047857' : '#94a3b8' }}; font-size: 0.85rem; line-height: 1.45; font-weight: 850;">
                                                                                {{ $cEmp['punch_in_time'] ?: '—' }}
                                                                            </div>
                                                                            <div style="font-size: 0.68rem; font-weight: 800; color: #059669; text-transform: uppercase; margin-top: 3px; letter-spacing: 0.03em;">In Time</div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-4">
                                                                        <div class="p-2 rounded-3 text-center" style="background: linear-gradient(180deg, #ffffff 0%, #fef2f2 100%); border: 1.5px solid #fee2e2; box-shadow: 0 1px 3px rgba(239,68,68,0.04);">
                                                                            <div class="fw-bolder text-truncate" style="color: {{ $cEmp['punch_out_time'] ? '#b91c1c' : '#94a3b8' }}; font-size: 0.85rem; line-height: 1.45; font-weight: 850;">
                                                                                {{ $cEmp['punch_out_time'] ?: '—' }}
                                                                            </div>
                                                                            <div style="font-size: 0.68rem; font-weight: 800; color: #dc2626; text-transform: uppercase; margin-top: 3px; letter-spacing: 0.03em;">Out Time</div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                {{-- Leave Balances Footer --}}
                                                                <div class="d-flex align-items-center justify-content-between pt-2 border-top gap-1.5" style="border-color: #f1f5f9 !important;">
                                                                    @if (!empty($cEmp['leave_balances']) && count($cEmp['leave_balances']) > 0)
                                                                        <div class="d-flex flex-wrap align-items-center gap-1.5 flex-grow-1 min-w-0">
                                                                            @foreach ($cEmp['leave_balances'] as $lb)
                                                                                @php $pal = $lb['palette'] ?? ['bg' => 'rgba(115, 103, 240, 0.1)', 'border' => '#7367f0', 'text' => '#7367f0']; @endphp
                                                                                <span class="badge px-2.5 py-1 rounded-pill"
                                                                                      style="background: {{ $pal['bg'] }}; color: {{ $pal['text'] }}; border: 1px solid {{ $pal['border'] }}40; font-size: 0.72rem; font-weight: 750;"
                                                                                      title="{{ $lb['name'] }}: {{ $lb['balance'] }} available">
                                                                                    {{ $lb['code'] }}: <strong style="font-weight: 900;">{{ number_format($lb['balance'], 1) }}</strong>
                                                                                </span>
                                                                            @endforeach
                                                                        </div>
                                                                    @else
                                                                        <span class="text-muted" style="font-size: 0.72rem;">No leave data</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="text-muted py-2" style="font-size: 0.82rem;">No employees assigned under this supervisor.</div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
            @endforeach
        @endif

    {{-- 4. DEPARTMENT HEAD VIEW (SUPERVISORS -> EMPLOYEES) --}}
    @elseif (!empty($hierarchySupervisors) && count($hierarchySupervisors) > 0)
        @php
            $totalSupervisors = count($hierarchySupervisors);
            $allChildEmployees = collect($hierarchySupervisors)->pluck('employees')->flatten(1);
            $totalEmployeesUnderSup = $allChildEmployees->count();
            
            $supIn = collect($hierarchySupervisors)->filter(fn($s) => ($s['supervisor']['status_type'] ?? '') === 'present_in')->count();
            $supOut = collect($hierarchySupervisors)->filter(fn($s) => ($s['supervisor']['status_type'] ?? '') === 'present_out')->count();
            $supLeave = collect($hierarchySupervisors)->filter(fn($s) => ($s['supervisor']['status_type'] ?? '') === 'leave')->count();
            $supNotPunched = collect($hierarchySupervisors)->filter(fn($s) => ($s['supervisor']['status_type'] ?? '') === 'not_punched')->count();
        @endphp

        {{-- Supervisors Header --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mt-5 mb-3">
            <div>
                <h4 class="mb-1" style="color: #0f172a; font-weight: 850; letter-spacing: -0.02em;">
                    <i class="ti ti-user-star text-primary me-2"></i> Supervisors ({{ $totalSupervisors }})
                </h4>
                <p class="text-muted mb-0" style="font-size: 0.95rem; font-weight: 650;">
                    Live attendance & status of Supervisors reporting to you
                </p>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="badge px-3 py-2 rounded-pill" style="background:#dcfce7; color:#166534; font-weight:800; font-size:.84rem;">
                    <i class="ti ti-check me-1"></i> {{ $supIn }} In
                </span>
                @if ($supOut > 0)
                    <span class="badge px-3 py-2 rounded-pill" style="background:#f1f5f9; color:#475569; font-weight:800; font-size:.84rem;">
                        <i class="ti ti-logout me-1"></i> {{ $supOut }} Out
                    </span>
                @endif
                <span class="badge px-3 py-2 rounded-pill" style="background:#fee2e2; color:#991b1b; font-weight:800; font-size:.84rem;">
                    <i class="ti ti-alert-circle me-1"></i> {{ $supNotPunched }} Not Punched
                </span>
                @if ($supLeave > 0)
                    <span class="badge px-3 py-2 rounded-pill" style="background:#ffedd5; color:#9a3412; font-weight:800; font-size:.84rem;">
                        <i class="ti ti-calendar me-1"></i> {{ $supLeave }} Leave
                    </span>
                @endif
            </div>
        </div>

        {{-- Supervisors Cards Grid (Full-width for 1 supervisor, multi-column grid for 2+) --}}
        @if ($totalSupervisors == 1)
            @php 
                $item = $hierarchySupervisors[0];
                $sup = $item['supervisor'];
            @endphp
            {{-- 1. Supervisor Profile Banner --}}
            <div class="card border-0 mb-4 shadow-sm" style="border-radius: 18px; border: 2px solid #818cf8 !important; background: #ffffff;">
                <div class="p-3.5 p-md-4" style="background: linear-gradient(135deg, #eef2ff 0%, #ffffff 100%);">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-3 min-w-0">
                            <div class="position-relative">
                                <img src="{{ $sup['avatar'] }}" alt="{{ $sup['name'] }}"
                                     class="rounded-circle border border-2 border-white shadow-xs"
                                     style="width: 58px; height: 58px; object-fit: cover; background: #fff;"
                                     onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($sup['name']) }}';">
                                <span class="position-absolute bottom-0 end-0 badge p-1 rounded-circle bg-primary text-white border border-white" title="Supervisor">
                                    <i class="ti ti-crown" style="font-size: 0.75rem;"></i>
                                </span>
                            </div>
                            <div class="min-w-0">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <h4 class="mb-0 fw-bold text-dark text-truncate" style="letter-spacing: -0.02em;">
                                        {{ $sup['name'] }}
                                    </h4>
                                    <span class="badge rounded-pill px-2.5 py-1"
                                          style="font-size: 0.75rem; font-weight: 800;
                                          @if ($sup['status_type'] === 'present_in') background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0;
                                          @elseif ($sup['status_type'] === 'present_out') background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;
                                          @elseif ($sup['status_type'] === 'leave') background: #ffedd5; color: #c2410c; border: 1px solid #fed7aa;
                                          @else background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca;
                                          @endif">
                                        <i class="ti {{ $sup['status_icon'] }} me-1"></i>{{ $sup['status_label'] }}
                                    </span>
                                </div>
                                <div class="text-primary fw-bold" style="font-size: 0.92rem; margin-top: 2px;">
                                    {{ $sup['code'] }} · <span class="text-dark">{{ $sup['designation'] ?: 'Supervisor' }}</span> ({{ $sup['department'] }})
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-label-primary px-3 py-2 rounded-pill" style="font-size: 0.85rem; font-weight: 800;">
                                <i class="ti ti-users me-1"></i> {{ $item['employee_count'] }} Team Members
                            </span>
                            <a href="{{ route('employees.show', $sup['id']) }}" class="btn btn-outline-primary rounded-pill px-3 py-1.5" style="font-weight: 750; font-size: 0.82rem;">
                                View Profile <i class="ti ti-chevron-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. Supervisor Full Attendance Stat Cards --}}
            <div class="row g-3 g-xl-4 mb-4 emp-stat-row">
                {{-- Card 1: Total Attendance --}}
                <div class="col-12 col-md-4">
                    <div class="card h-100 border-0 position-relative overflow-hidden emp-stat-card"
                         style="border-radius: 18px; border-bottom: 5px solid #6366f1 !important; background: linear-gradient(180deg,#ffffff 0%,#eef2ff 100%); box-shadow: 0 6px 22px rgba(99,102,241,0.12);">
                        <div class="card-body p-4 p-xl-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center justify-content-center"
                                     style="width: 52px; height: 52px; border-radius: 14px; background: rgba(99, 102, 241, 0.15); color: #4f46e5;">
                                    <i class="ti ti-calendar-event" style="font-size:1.6rem;"></i>
                                </div>
                                <span class="badge rounded-pill px-3 py-2" style="background: rgba(99, 102, 241, 0.14); color: #4338ca; font-size: 0.88rem; font-weight: 800;">
                                    Attendance
                                </span>
                            </div>
                            <div class="d-flex align-items-baseline gap-2 my-2">
                                <h1 class="mb-0" style="font-size: 2.75rem; line-height: 1; font-weight: 900; color: #4f46e5; letter-spacing: -0.04em;">{{ $sup['month_present'] ?? 0 }}</h1>
                                <span style="font-size: 1.15rem; font-weight: 800; color: #0f172a;">Days</span>
                            </div>
                            <h5 class="card-title mb-3" style="font-size: 1.2rem; font-weight: 800; color: #0f172a; letter-spacing: -0.02em;">Total Attendance</h5>
                            <div class="d-flex justify-content-between align-items-center pt-3 border-top" style="font-size: 0.95rem; font-weight: 700;">
                                <span style="color:#475569;">Month Total</span>
                                <span class="badge bg-success px-3 py-2 rounded-pill" style="font-size: 0.9rem; font-weight: 800;">
                                    {{ $sup['month_present'] ?? 0 }} Days
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card 2: Punch In Count --}}
                <div class="col-12 col-md-4">
                    <div class="card h-100 border-0 position-relative overflow-hidden emp-stat-card"
                         style="border-radius: 18px; border-bottom: 5px solid #10b981 !important; background: linear-gradient(180deg,#ffffff 0%,#ecfdf5 100%); box-shadow: 0 6px 22px rgba(16,185,129,0.12);">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center justify-content-center"
                                     style="width: 52px; height: 52px; border-radius: 14px; background: rgba(16, 185, 129, 0.15); color: #059669;">
                                    <i class="ti ti-login" style="font-size:1.6rem;"></i>
                                </div>
                                <span class="badge rounded-pill px-3 py-2" style="background: rgba(16, 185, 129, 0.14); color: #047857; font-size: 0.88rem; font-weight: 800;">
                                    Total: {{ $sup['total_punch_in_count'] ?? 0 }}
                                </span>
                            </div>
                            <div class="d-flex align-items-baseline gap-2 my-2">
                                <h1 class="mb-0" style="font-size: 2.75rem; line-height: 1; font-weight: 900; color: #059669; letter-spacing: -0.04em;">{{ $sup['total_punch_in_count'] ?? 0 }}</h1>
                                <span style="font-size: 1.15rem; font-weight: 800; color: #0f172a;">Punches</span>
                            </div>
                            <h5 class="card-title mb-3" style="font-size: 1.2rem; font-weight: 800; color: #0f172a; letter-spacing: -0.02em;">Punch In Count</h5>
                            <div class="d-flex justify-content-between align-items-center pt-3 border-top" style="font-size: 0.95rem; font-weight: 700;">
                                <span style="color:#475569;">Today's In Time</span>
                                <span class="badge bg-{{ $sup['punch_in_time'] ? 'success' : 'secondary' }} px-3 py-2 rounded-pill" style="font-size: 0.9rem; font-weight: 800;">
                                    {{ $sup['punch_in_time'] ?: '--:--' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card 3: Punch Out Count --}}
                <div class="col-12 col-md-4">
                    <div class="card h-100 border-0 position-relative overflow-hidden emp-stat-card"
                         style="border-radius: 18px; border-bottom: 5px solid #ef4444 !important; background: linear-gradient(180deg,#ffffff 0%,#fef2f2 100%); box-shadow: 0 6px 22px rgba(239,68,68,0.12);">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center justify-content-center"
                                     style="width: 52px; height: 52px; border-radius: 14px; background: rgba(239, 68, 68, 0.15); color: #dc2626;">
                                    <i class="ti ti-logout" style="font-size:1.6rem;"></i>
                                </div>
                                <span class="badge rounded-pill px-3 py-2" style="background: rgba(239, 68, 68, 0.14); color: #b91c1c; font-size: 0.88rem; font-weight: 800;">
                                    Total: {{ $sup['total_punch_out_count'] ?? 0 }}
                                </span>
                            </div>
                            <div class="d-flex align-items-baseline gap-2 my-2">
                                <h1 class="mb-0" style="font-size: 2.75rem; line-height: 1; font-weight: 900; color: #dc2626; letter-spacing: -0.04em;">{{ $sup['total_punch_out_count'] ?? 0 }}</h1>
                                <span style="font-size: 1.15rem; font-weight: 800; color: #0f172a;">Punches</span>
                            </div>
                            <h5 class="card-title mb-3" style="font-size: 1.2rem; font-weight: 800; color: #0f172a; letter-spacing: -0.02em;">Punch Out Count</h5>
                            <div class="d-flex justify-content-between align-items-center pt-3 border-top" style="font-size: 0.95rem; font-weight: 700;">
                                <span style="color:#475569;">Today's Out Time</span>
                                <span class="badge bg-{{ $sup['punch_out_time'] ? 'danger' : 'secondary' }} px-3 py-2 rounded-pill" style="font-size: 0.9rem; font-weight: 800;">
                                    {{ $sup['punch_out_time'] ?: '--:--' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. Supervisor Leave Balance (Type-Wise) --}}
            <div class="row g-3 g-xl-4 mb-4">
                <div class="col-12 mb-1">
                    <h5 class="mb-0 d-flex align-items-center" style="font-size: 1.35rem; font-weight: 900; color: #0f172a; letter-spacing: -0.02em;">
                        <i class="ti ti-calendar-event text-primary me-2" style="font-size:1.5rem;"></i> Leave Balance (Type-Wise) - {{ $sup['name'] }}
                    </h5>
                </div>

                @if (!empty($sup['leave_balances']) && count($sup['leave_balances']) > 0)
                    @foreach ($sup['leave_balances'] as $leave)
                        @php
                            $pal = $leave['palette'] ?? ['bg' => 'rgba(115, 103, 240, 0.1)', 'border' => '#7367f0', 'text' => '#7367f0'];
                            $leaveCount = count($sup['leave_balances']);
                            $leaveCol = $leaveCount <= 2 ? 'col-12 col-lg-6' : 'col-12 col-md-4';
                        @endphp
                        <div class="{{ $leaveCol }}">
                            <div class="card h-100 border-0 position-relative overflow-hidden emp-stat-card"
                                 style="border-radius: 18px; border-left: 6px solid {{ $pal['border'] }} !important; background: #ffffff; box-shadow: 0 6px 20px rgba(15,23,42,0.06); min-height: 150px;">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="d-flex align-items-center justify-content-center"
                                                 style="width: 56px; height: 56px; border-radius: 14px; background: {{ $pal['bg'] }}; color: {{ $pal['text'] }}; font-size: 1.2rem; font-weight: 900; flex-shrink: 0;">
                                                {{ $leave['code'] }}
                                            </div>
                                            <div>
                                                <h5 class="mb-1 text-truncate" style="font-size: 1.2rem; font-weight: 800; color: #0f172a; letter-spacing: -0.02em; max-width: 220px;" title="{{ $leave['name'] }}">
                                                    {{ $leave['name'] }}
                                                </h5>
                                                <div class="d-flex align-items-baseline gap-2 mt-1">
                                                    <span style="color: {{ $pal['text'] }}; font-size: 2rem; font-weight: 900; line-height: 1; letter-spacing: -0.03em;">
                                                        {{ number_format($leave['balance'], 1) }}
                                                    </span>
                                                    <span style="color: #0f172a; font-size: 1.05rem; font-weight: 800;">Days Available</span>
                                                </div>
                                            </div>
                                        </div>
                                        <span class="badge rounded-pill px-3 py-2" style="font-size: 0.82rem; font-weight: 750; background: #f8fafc; color: #334155; border: 1px solid #e2e8f0;">
                                            {{ !empty($leave['carry_forward']) ? 'Carry Forward' : 'Monthly Accrual' }}
                                        </span>
                                    </div>

                                    <div class="border-top pt-3 d-flex justify-content-between align-items-center" style="font-size: 0.98rem; font-weight: 700; color: #475569;">
                                        <span>Allocated: <strong style="color:#0f172a; font-weight: 900;">{{ number_format($leave['allocated'] ?? 0, 1) }}</strong></span>
                                        <span>Used (FY): <strong style="color:#dc2626; font-weight: 900;">{{ number_format($leave['used_year'] ?? 0, 1) }}</strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        @else
            <div class="row g-3 mb-4">
                @php
                    $supCol = $totalSupervisors == 2 ? 'col-12 col-md-6' : 'col-12 col-md-4';
                @endphp
                @foreach ($hierarchySupervisors as $item)
                    @php $sup = $item['supervisor']; @endphp
                    <div class="{{ $supCol }}">
                        <div class="card h-100 border-0 overflow-hidden shadow-sm"
                             style="border-radius: 18px; border: 2px solid #818cf8 !important; background: #ffffff; transition: transform .2s ease, box-shadow .2s ease;">
                            {{-- Top Header of Supervisor Card --}}
                            <div class="p-3" style="background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%); border-bottom: 1px solid #c7d2fe;">
                                <div class="d-flex align-items-center justify-content-between gap-2">
                                    <div class="d-flex align-items-center gap-2 min-w-0 flex-grow-1">
                                        <div class="position-relative">
                                            <img src="{{ $sup['avatar'] }}" alt="{{ $sup['name'] }}"
                                                 class="rounded-circle border"
                                                 style="width: 46px; height: 46px; object-fit: cover; flex-shrink: 0; background: #fff;"
                                                 onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($sup['name']) }}';">
                                            <span class="position-absolute bottom-0 end-0 badge p-1 rounded-circle bg-primary" title="Supervisor">
                                                <i class="ti ti-crown" style="font-size: 0.65rem;"></i>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-grow-1">
                                            <div class="text-truncate fw-bold text-dark" style="font-size: 1.05rem;" title="{{ $sup['name'] }}">
                                                {{ $sup['name'] }}
                                            </div>
                                            <div class="text-primary text-truncate" style="font-size: 0.8rem; font-weight: 750;">
                                                {{ $sup['code'] }} · {{ $sup['designation'] ?: 'Supervisor' }}
                                            </div>
                                        </div>
                                    </div>
                                    <span class="badge rounded-pill px-2.5 py-1.5 flex-shrink-0"
                                          style="font-size: 0.75rem; font-weight: 800;
                                          @if ($sup['status_type'] === 'present_in') background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0;
                                          @elseif ($sup['status_type'] === 'present_out') background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;
                                          @elseif ($sup['status_type'] === 'leave') background: #ffedd5; color: #c2410c; border: 1px solid #fed7aa;
                                          @else background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca;
                                          @endif">
                                        <i class="ti {{ $sup['status_icon'] }} me-1"></i>{{ $sup['status_label'] }}
                                    </span>
                                </div>
                            </div>

                            {{-- Supervisor Card Body --}}
                            <div class="card-body p-3 d-flex flex-column justify-content-between">
                                {{-- Attendance Mini Stats Row --}}
                                <div class="row g-2 text-center mb-2">
                                    <div class="col-4">
                                        <div class="p-2 rounded-3" style="background: #eff6ff; border: 1px solid #dbeafe;">
                                            <div class="fw-bolder text-primary" style="font-size: 1.2rem; line-height: 1.1;">{{ $sup['month_present'] }}</div>
                                            <div class="text-muted" style="font-size: 0.72rem; font-weight: 700; margin-top: 3px;">Days MTD</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="p-2 rounded-3" style="background: #ecfdf5; border: 1px solid #a7f3d0;">
                                            <div class="fw-bolder text-success text-truncate" style="font-size: 0.88rem; line-height: 1.4;">{{ $sup['punch_in_time'] ?: '—' }}</div>
                                            <div class="text-muted" style="font-size: 0.72rem; font-weight: 700; margin-top: 3px;">In Time</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="p-2 rounded-3" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                                            <div class="fw-bolder text-secondary text-truncate" style="font-size: 0.88rem; line-height: 1.4;">{{ $sup['punch_out_time'] ?: '—' }}</div>
                                            <div class="text-muted" style="font-size: 0.72rem; font-weight: 700; margin-top: 3px;">Out Time</div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Team Count Tag --}}
                                <div class="d-flex align-items-center justify-content-between py-2 border-top border-bottom my-2">
                                    <span style="font-size: 0.82rem; font-weight: 750; color: #475569;">
                                        <i class="ti ti-users me-1 text-primary"></i> Team Size:
                                    </span>
                                    <span class="badge bg-label-primary rounded-pill px-2.5 py-1" style="font-weight: 800; font-size: 0.8rem;">
                                        {{ $item['employee_count'] }} Employees
                                    </span>
                                </div>

                                {{-- Leave Balances --}}
                                @if (!empty($sup['leave_balances']) && count($sup['leave_balances']) > 0)
                                    <div>
                                        <div class="d-flex justify-content-between align-items-center mb-1.5">
                                            <span class="text-muted text-uppercase" style="font-size: 0.7rem; font-weight: 800; letter-spacing: .03em;">Leave Balance</span>
                                            <a href="{{ route('employees.show', $sup['id']) }}" class="text-primary text-decoration-none" style="font-size: 0.75rem; font-weight: 750;">
                                                Profile <i class="ti ti-chevron-right"></i>
                                            </a>
                                        </div>
                                        <div class="d-flex flex-wrap gap-1.5">
                                            @foreach ($sup['leave_balances'] as $lb)
                                                <span class="badge px-2 py-1 rounded-2"
                                                      style="background: #f8fafc; color: #334155; border: 1px solid #e2e8f0; font-size: 0.74rem; font-weight: 700;"
                                                      title="{{ $lb['name'] }}: {{ $lb['balance'] }} days available">
                                                    {{ $lb['code'] }}: <strong style="color: #0f172a; font-weight: 850;">{{ number_format($lb['balance'], 1) }}</strong>
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Employees Reporting Under Each Supervisor --}}
        @foreach ($hierarchySupervisors as $item)
            @php 
                $sup = $item['supervisor'];
                $empList = $item['employees'];
            @endphp

            <div class="card border-0 mb-4 shadow-sm" style="border-radius: 18px; border: 1px solid #e2e8f0 !important; background: #fafafa;">
                <div class="card-body p-4">
                    {{-- Section Header for this Supervisor's team --}}
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3 pb-3 border-bottom">
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center justify-content-center"
                                 style="width: 44px; height: 44px; border-radius: 12px; background: rgba(99, 102, 241, 0.15); color: #4f46e5;">
                                <i class="ti ti-users-group fs-4"></i>
                            </div>
                            <div>
                                <h5 class="mb-0" style="color: #0f172a; font-weight: 850;">
                                    Team Members under <span class="text-primary">{{ $sup['name'] }}</span> (Supervisor)
                                </h5>
                                <div class="text-muted" style="font-size: 0.85rem; font-weight: 650;">
                                    Total {{ count($empList) }} Employees reporting directly to {{ $sup['name'] }}
                                </div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge px-3 py-1.5 rounded-pill" style="background:#dcfce7; color:#166534; font-weight:800; font-size:.8rem;">
                                <i class="ti ti-check me-1"></i> {{ $item['in_count'] }} In
                            </span>
                            @if ($item['out_count'] > 0)
                                <span class="badge px-3 py-1.5 rounded-pill" style="background:#f1f5f9; color:#475569; font-weight:800; font-size:.8rem;">
                                    <i class="ti ti-logout me-1"></i> {{ $item['out_count'] }} Out
                                </span>
                            @endif
                            <span class="badge px-3 py-1.5 rounded-pill" style="background:#fee2e2; color:#991b1b; font-weight:800; font-size:.8rem;">
                                <i class="ti ti-alert-circle me-1"></i> {{ $item['not_punched_count'] }} Not Punched
                            </span>
                            @if ($item['leave_count'] > 0)
                                <span class="badge px-3 py-1.5 rounded-pill" style="background:#ffedd5; color:#9a3412; font-weight:800; font-size:.8rem;">
                                    <i class="ti ti-calendar me-1"></i> {{ $item['leave_count'] }} Leave
                                </span>
                            @endif
                        </div>
                    </div>

                    @if (!empty($empList) && count($empList) > 0)
                        <div class="row g-3">
                            @foreach ($empList as $child)
                                <div class="col-12 col-md-4">
                                    <div class="card h-100 border-0 shadow-sm employee-mini-card"
                                         style="border-radius: 16px; border: 1.5px solid #e2e8f0 !important; background: #ffffff; overflow: hidden;">
                                        
                                        {{-- Employee Header --}}
                                        <div class="p-3" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-bottom: 1px solid #eef2f7;">
                                            <div class="d-flex align-items-center justify-content-between gap-2">
                                                <div class="d-flex align-items-center gap-2.5 min-w-0 flex-grow-1">
                                                    <div class="position-relative flex-shrink-0">
                                                        <img src="{{ $child['avatar'] }}" alt="{{ $child['name'] }}"
                                                             class="rounded-circle border border-2 border-white shadow-xs"
                                                             style="width: 44px; height: 44px; object-fit: cover; background: #fff;"
                                                             onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($child['name']) }}';">
                                                    </div>
                                                    <div class="min-w-0 flex-grow-1">
                                                        <div class="text-truncate fw-bold text-dark" style="font-size: 0.96rem; letter-spacing: -0.01em;" title="{{ $child['name'] }}">
                                                            {{ $child['name'] }}
                                                        </div>
                                                        <div class="text-muted text-truncate" style="font-size: 0.76rem; font-weight: 600; margin-top: 1px;">
                                                            <span class="text-primary fw-bold">{{ $child['code'] }}</span> · {{ $child['designation'] ?: 'Employee' }}
                                                        </div>
                                                    </div>
                                                </div>
                                                <span class="badge rounded-pill px-2.5 py-1.5 flex-shrink-0"
                                                      style="font-size: 0.72rem; font-weight: 800; letter-spacing: 0.02em;
                                                      @if ($child['status_type'] === 'present_in') background: #dcfce7; color: #15803d; border: 1px solid #86efac;
                                                      @elseif ($child['status_type'] === 'present_out') background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;
                                                      @elseif ($child['status_type'] === 'leave') background: #ffedd5; color: #c2410c; border: 1px solid #fdba74;
                                                      @else background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5;
                                                      @endif">
                                                    <i class="ti {{ $child['status_icon'] }} me-1" style="font-size: 0.75rem;"></i>{{ $child['status_label'] }}
                                                </span>
                                            </div>
                                        </div>

                                        {{-- Employee Body --}}
                                        <div class="card-body p-3 d-flex flex-column justify-content-between">
                                            {{-- 3 Attendance Stats Row --}}
                                            <div class="row g-2 text-center mb-2.5">
                                                <div class="col-4">
                                                    <div class="p-2 rounded-3 text-center" style="background: linear-gradient(180deg, #ffffff 0%, #eef2ff 100%); border: 1.5px solid #e0e7ff; box-shadow: 0 1px 3px rgba(99,102,241,0.04);">
                                                        <div class="fw-bolder" style="color: #4338ca; font-size: 1.25rem; line-height: 1.1; letter-spacing: -0.03em;">{{ $child['month_present'] }}</div>
                                                        <div style="font-size: 0.68rem; font-weight: 800; color: #6366f1; text-transform: uppercase; margin-top: 3px; letter-spacing: 0.03em;">MTD</div>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="p-2 rounded-3 text-center" style="background: linear-gradient(180deg, #ffffff 0%, #ecfdf5 100%); border: 1.5px solid #d1fae5; box-shadow: 0 1px 3px rgba(16,185,129,0.04);">
                                                        <div class="fw-bolder text-truncate" style="color: {{ $child['punch_in_time'] ? '#047857' : '#94a3b8' }}; font-size: 0.85rem; line-height: 1.45; font-weight: 850;">
                                                            {{ $child['punch_in_time'] ?: '—' }}
                                                        </div>
                                                        <div style="font-size: 0.68rem; font-weight: 800; color: #059669; text-transform: uppercase; margin-top: 3px; letter-spacing: 0.03em;">In Time</div>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="p-2 rounded-3 text-center" style="background: linear-gradient(180deg, #ffffff 0%, #fef2f2 100%); border: 1.5px solid #fee2e2; box-shadow: 0 1px 3px rgba(239,68,68,0.04);">
                                                        <div class="fw-bolder text-truncate" style="color: {{ $child['punch_out_time'] ? '#b91c1c' : '#94a3b8' }}; font-size: 0.85rem; line-height: 1.45; font-weight: 850;">
                                                            {{ $child['punch_out_time'] ?: '—' }}
                                                        </div>
                                                        <div style="font-size: 0.68rem; font-weight: 800; color: #dc2626; text-transform: uppercase; margin-top: 3px; letter-spacing: 0.03em;">Out Time</div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Leave Balances Footer --}}
                                            <div class="d-flex align-items-center justify-content-between pt-2 border-top gap-1.5" style="border-color: #f1f5f9 !important;">
                                                @if (!empty($child['leave_balances']) && count($child['leave_balances']) > 0)
                                                    <div class="d-flex flex-wrap align-items-center gap-1.5 flex-grow-1 min-w-0">
                                                        @foreach ($child['leave_balances'] as $lb)
                                                            @php $pal = $lb['palette'] ?? ['bg' => 'rgba(115, 103, 240, 0.1)', 'border' => '#7367f0', 'text' => '#7367f0']; @endphp
                                                            <span class="badge px-2.5 py-1 rounded-pill"
                                                                  style="background: {{ $pal['bg'] }}; color: {{ $pal['text'] }}; border: 1px solid {{ $pal['border'] }}40; font-size: 0.72rem; font-weight: 750;"
                                                                  title="{{ $lb['name'] }}: {{ $lb['balance'] }} available">
                                                                {{ $lb['code'] }}: <strong style="font-weight: 900;">{{ number_format($lb['balance'], 1) }}</strong>
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <span class="text-muted" style="font-size: 0.72rem;">No leave data</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-light border text-muted py-2 px-3 mb-0" style="font-size: 0.88rem;">
                            <i class="ti ti-info-circle me-1"></i> No employees currently assigned under {{ $sup['name'] }}.
                        </div>
                    @endif
                </div>
            </div>
        @endforeach

        {{-- Direct Employees under Dept Head (if any without supervisor) --}}
        @if (!empty($directEmployees) && count($directEmployees) > 0)
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mt-4 mb-3">
                <div>
                    <h5 class="mb-1" style="color: #0f172a; font-weight: 850;">
                        <i class="ti ti-users text-primary me-2"></i> Direct Team Members ({{ count($directEmployees) }})
                    </h5>
                    <p class="text-muted mb-0" style="font-size: 0.88rem; font-weight: 650;">
                        Employees reporting directly to you
                    </p>
                </div>
            </div>
            <div class="row g-3">
                @foreach ($directEmployees as $sub)
                    <div class="col-12 col-md-4">
                        <div class="card h-100 border-0 shadow-sm employee-mini-card"
                             style="border-radius: 16px; border: 1.5px solid #e2e8f0 !important; background: #ffffff; overflow: hidden;">
                            
                            {{-- Employee Header --}}
                            <div class="p-3" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-bottom: 1px solid #eef2f7;">
                                <div class="d-flex align-items-center justify-content-between gap-2">
                                    <div class="d-flex align-items-center gap-2.5 min-w-0 flex-grow-1">
                                        <div class="position-relative flex-shrink-0">
                                            <img src="{{ $sub['avatar'] }}" alt="{{ $sub['name'] }}"
                                                 class="rounded-circle border border-2 border-white shadow-xs"
                                                 style="width: 44px; height: 44px; object-fit: cover; background: #fff;"
                                                 onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($sub['name']) }}';">
                                        </div>
                                        <div class="min-w-0 flex-grow-1">
                                            <div class="text-truncate fw-bold text-dark" style="font-size: 0.96rem; letter-spacing: -0.01em;" title="{{ $sub['name'] }}">
                                                {{ $sub['name'] }}
                                            </div>
                                            <div class="text-muted text-truncate" style="font-size: 0.76rem; font-weight: 600; margin-top: 1px;">
                                                <span class="text-primary fw-bold">{{ $sub['code'] }}</span> · {{ $sub['designation'] ?: 'Employee' }}
                                            </div>
                                        </div>
                                    </div>
                                    <span class="badge rounded-pill px-2.5 py-1.5 flex-shrink-0"
                                          style="font-size: 0.72rem; font-weight: 800; letter-spacing: 0.02em;
                                          @if ($sub['status_type'] === 'present_in') background: #dcfce7; color: #15803d; border: 1px solid #86efac;
                                          @elseif ($sub['status_type'] === 'present_out') background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;
                                          @elseif ($sub['status_type'] === 'leave') background: #ffedd5; color: #c2410c; border: 1px solid #fdba74;
                                          @else background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5;
                                          @endif">
                                        <i class="ti {{ $sub['status_icon'] }} me-1" style="font-size: 0.75rem;"></i>{{ $sub['status_label'] }}
                                    </span>
                                </div>
                            </div>

                            {{-- Employee Body --}}
                            <div class="card-body p-3 d-flex flex-column justify-content-between">
                                {{-- 3 Attendance Stats Row --}}
                                <div class="row g-2 text-center mb-2.5">
                                    <div class="col-4">
                                        <div class="p-2 rounded-3 text-center" style="background: linear-gradient(180deg, #ffffff 0%, #eef2ff 100%); border: 1.5px solid #e0e7ff; box-shadow: 0 1px 3px rgba(99,102,241,0.04);">
                                            <div class="fw-bolder" style="color: #4338ca; font-size: 1.25rem; line-height: 1.1; letter-spacing: -0.03em;">{{ $sub['month_present'] }}</div>
                                            <div style="font-size: 0.68rem; font-weight: 800; color: #6366f1; text-transform: uppercase; margin-top: 3px; letter-spacing: 0.03em;">MTD</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="p-2 rounded-3 text-center" style="background: linear-gradient(180deg, #ffffff 0%, #ecfdf5 100%); border: 1.5px solid #d1fae5; box-shadow: 0 1px 3px rgba(16,185,129,0.04);">
                                            <div class="fw-bolder text-truncate" style="color: {{ $sub['punch_in_time'] ? '#047857' : '#94a3b8' }}; font-size: 0.85rem; line-height: 1.45; font-weight: 850;">
                                                {{ $sub['punch_in_time'] ?: '—' }}
                                            </div>
                                            <div style="font-size: 0.68rem; font-weight: 800; color: #059669; text-transform: uppercase; margin-top: 3px; letter-spacing: 0.03em;">In Time</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="p-2 rounded-3 text-center" style="background: linear-gradient(180deg, #ffffff 0%, #fef2f2 100%); border: 1.5px solid #fee2e2; box-shadow: 0 1px 3px rgba(239,68,68,0.04);">
                                            <div class="fw-bolder text-truncate" style="color: {{ $sub['punch_out_time'] ? '#b91c1c' : '#94a3b8' }}; font-size: 0.85rem; line-height: 1.45; font-weight: 850;">
                                                {{ $sub['punch_out_time'] ?: '—' }}
                                            </div>
                                            <div style="font-size: 0.68rem; font-weight: 800; color: #dc2626; text-transform: uppercase; margin-top: 3px; letter-spacing: 0.03em;">Out Time</div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Leave Balances Footer --}}
                                <div class="d-flex align-items-center justify-content-between pt-2 border-top gap-1.5" style="border-color: #f1f5f9 !important;">
                                    @if (!empty($sub['leave_balances']) && count($sub['leave_balances']) > 0)
                                        <div class="d-flex flex-wrap align-items-center gap-1.5 flex-grow-1 min-w-0">
                                            @foreach ($sub['leave_balances'] as $lb)
                                                @php $pal = $lb['palette'] ?? ['bg' => 'rgba(115, 103, 240, 0.1)', 'border' => '#7367f0', 'text' => '#7367f0']; @endphp
                                                <span class="badge px-2.5 py-1 rounded-pill"
                                                      style="background: {{ $pal['bg'] }}; color: {{ $pal['text'] }}; border: 1px solid {{ $pal['border'] }}40; font-size: 0.72rem; font-weight: 750;"
                                                      title="{{ $lb['name'] }}: {{ $lb['balance'] }} available">
                                                    {{ $lb['code'] }}: <strong style="font-weight: 900;">{{ number_format($lb['balance'], 1) }}</strong>
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted" style="font-size: 0.72rem;">No leave data</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    {{-- 5. REGULAR SUPERVISOR / MANAGER TEAM VIEW (FLAT DIRECT SUBORDINATES) --}}
    @elseif (!empty($subordinateEmployees) && count($subordinateEmployees) > 0)
        @php
            $teamTotal = count($subordinateEmployees);
            $teamIn = collect($subordinateEmployees)->where('status_type', 'present_in')->count();
            $teamOut = collect($subordinateEmployees)->where('status_type', 'present_out')->count();
            $teamLeave = collect($subordinateEmployees)->where('status_type', 'leave')->count();
            $teamNotPunched = collect($subordinateEmployees)->where('status_type', 'not_punched')->count();
        @endphp

        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mt-5 mb-3">
            <div>
                <h4 class="mb-1" style="color: #0f172a; font-weight: 850; letter-spacing: -0.02em;">
                    <i class="ti ti-users-group text-primary me-2"></i> Team Members ({{ $teamTotal }})
                </h4>
                <p class="text-muted mb-0" style="font-size: 0.95rem; font-weight: 650;">
                    Live attendance & leave summary of employees reporting to you
                </p>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="badge px-3 py-2 rounded-pill" style="background:#dcfce7; color:#166534; font-weight:800; font-size:.84rem;">
                    <i class="ti ti-check me-1"></i> {{ $teamIn }} In
                </span>
                @if ($teamOut > 0)
                    <span class="badge px-3 py-2 rounded-pill" style="background:#f1f5f9; color:#475569; font-weight:800; font-size:.84rem;">
                        <i class="ti ti-logout me-1"></i> {{ $teamOut }} Out
                    </span>
                @endif
                <span class="badge px-3 py-2 rounded-pill" style="background:#fee2e2; color:#991b1b; font-weight:800; font-size:.84rem;">
                    <i class="ti ti-alert-circle me-1"></i> {{ $teamNotPunched }} Not Punched
                </span>
                @if ($teamLeave > 0)
                    <span class="badge px-3 py-2 rounded-pill" style="background:#ffedd5; color:#9a3412; font-weight:800; font-size:.84rem;">
                        <i class="ti ti-calendar me-1"></i> {{ $teamLeave }} Leave
                    </span>
                @endif
            </div>
        </div>

        <div class="row g-3">
            @foreach ($subordinateEmployees as $sub)
                <div class="col-12 col-md-4">
                    <div class="card h-100 border-0 shadow-sm employee-mini-card"
                         style="border-radius: 16px; border: 1.5px solid #e2e8f0 !important; background: #ffffff; overflow: hidden;">
                        
                        {{-- Top Header of Mini Dashboard Card --}}
                        <div class="p-3" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-bottom: 1px solid #eef2f7;">
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <div class="d-flex align-items-center gap-2.5 min-w-0 flex-grow-1">
                                    <div class="position-relative flex-shrink-0">
                                        <img src="{{ $sub['avatar'] }}" alt="{{ $sub['name'] }}"
                                             class="rounded-circle border border-2 border-white shadow-xs"
                                             style="width: 44px; height: 44px; object-fit: cover; background: #fff;"
                                             onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($sub['name']) }}';">
                                    </div>
                                    <div class="min-w-0 flex-grow-1">
                                        <div class="text-truncate fw-bold text-dark" style="font-size: 0.96rem; letter-spacing: -0.01em;" title="{{ $sub['name'] }}">
                                            {{ $sub['name'] }}
                                        </div>
                                        <div class="text-muted text-truncate" style="font-size: 0.76rem; font-weight: 600; margin-top: 1px;">
                                            <span class="text-primary fw-bold">{{ $sub['code'] }}</span> · {{ $sub['designation'] ?: 'Employee' }}
                                        </div>
                                    </div>
                                </div>
                                <span class="badge rounded-pill px-2.5 py-1.5 flex-shrink-0"
                                      style="font-size: 0.72rem; font-weight: 800; letter-spacing: 0.02em;
                                      @if ($sub['status_type'] === 'present_in') background: #dcfce7; color: #15803d; border: 1px solid #86efac;
                                      @elseif ($sub['status_type'] === 'present_out') background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;
                                      @elseif ($sub['status_type'] === 'leave') background: #ffedd5; color: #c2410c; border: 1px solid #fdba74;
                                      @else background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5;
                                      @endif">
                                    <i class="ti {{ $sub['status_icon'] }} me-1" style="font-size: 0.75rem;"></i>{{ $sub['status_label'] }}
                                </span>
                            </div>
                        </div>

                        {{-- Card Body --}}
                        <div class="card-body p-3 d-flex flex-column justify-content-between">
                            {{-- Mini Attendance Stats Row --}}
                            <div class="row g-2 text-center mb-2.5">
                                <div class="col-4">
                                    <div class="p-2 rounded-3 text-center" style="background: linear-gradient(180deg, #ffffff 0%, #eef2ff 100%); border: 1.5px solid #e0e7ff; box-shadow: 0 1px 3px rgba(99,102,241,0.04);">
                                        <div class="fw-bolder" style="color: #4338ca; font-size: 1.25rem; line-height: 1.1; letter-spacing: -0.03em;">{{ $sub['month_present'] }}</div>
                                        <div style="font-size: 0.68rem; font-weight: 800; color: #6366f1; text-transform: uppercase; margin-top: 3px; letter-spacing: 0.03em;">MTD</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-2 rounded-3 text-center" style="background: linear-gradient(180deg, #ffffff 0%, #ecfdf5 100%); border: 1.5px solid #d1fae5; box-shadow: 0 1px 3px rgba(16,185,129,0.04);">
                                        <div class="fw-bolder text-truncate" style="color: {{ $sub['punch_in_time'] ? '#047857' : '#94a3b8' }}; font-size: 0.85rem; line-height: 1.45; font-weight: 850;">
                                            {{ $sub['punch_in_time'] ?: '—' }}
                                        </div>
                                        <div style="font-size: 0.68rem; font-weight: 800; color: #059669; text-transform: uppercase; margin-top: 3px; letter-spacing: 0.03em;">In Time</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-2 rounded-3 text-center" style="background: linear-gradient(180deg, #ffffff 0%, #fef2f2 100%); border: 1.5px solid #fee2e2; box-shadow: 0 1px 3px rgba(239,68,68,0.04);">
                                        <div class="fw-bolder text-truncate" style="color: {{ $sub['punch_out_time'] ? '#b91c1c' : '#94a3b8' }}; font-size: 0.85rem; line-height: 1.45; font-weight: 850;">
                                            {{ $sub['punch_out_time'] ?: '—' }}
                                        </div>
                                        <div style="font-size: 0.68rem; font-weight: 800; color: #dc2626; text-transform: uppercase; margin-top: 3px; letter-spacing: 0.03em;">Out Time</div>
                                    </div>
                                </div>
                            </div>

                            {{-- Leave Balance Section --}}
                            <div class="d-flex align-items-center justify-content-between pt-2 border-top gap-1.5" style="border-color: #f1f5f9 !important;">
                                @if (!empty($sub['leave_balances']) && count($sub['leave_balances']) > 0)
                                    <div class="d-flex flex-wrap align-items-center gap-1.5 flex-grow-1 min-w-0">
                                        @foreach ($sub['leave_balances'] as $lb)
                                            @php $pal = $lb['palette'] ?? ['bg' => 'rgba(115, 103, 240, 0.1)', 'border' => '#7367f0', 'text' => '#7367f0']; @endphp
                                            <span class="badge px-2.5 py-1 rounded-pill"
                                                  style="background: {{ $pal['bg'] }}; color: {{ $pal['text'] }}; border: 1px solid {{ $pal['border'] }}40; font-size: 0.72rem; font-weight: 750;"
                                                  title="{{ $lb['name'] }}: {{ $lb['balance'] }} available">
                                                {{ $lb['code'] }}: <strong style="font-weight: 900;">{{ number_format($lb['balance'], 1) }}</strong>
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted" style="font-size: 0.72rem;">No leave data</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endif
