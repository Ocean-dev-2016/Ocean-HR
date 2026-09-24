<?php
$path = 'resources/views/software/_utils/dashboarad_inquiry_statistics.blade.php';
$t = file_get_contents($path);
$start = strpos($t, '    {{-- Finance + Joiners + Celebrations --}}');
$end = strpos($t, "</div>\n\n<script>\nsetInterval(function () {\n    var el = document.getElementById('admin-live-clock');");
if ($start === false || $end === false) {
    // try alternate end
    $end = strpos($t, "setInterval(function () {\n    var el = document.getElementById('admin-live-clock');");
    if ($end !== false) {
        // move back to closing div before script
        $end2 = strrpos(substr($t, 0, $end), "</div>");
        // find the admin root closing - look for pattern before script
        preg_match('/\n<\/div>\n\n<script>\nsetInterval\(function \(\) \{\n    var el = document\.getElementById\(\'admin-live-clock\'\)/', $t, $m, PREG_OFFSET_CAPTURE);
        if (!empty($m)) {
            $end = $m[0][1] + 1; // position of </div>
            // actually we want to replace INCLUDING through the live lists row, keeping script
            $end = $m[0][1]; // start of "\n</div>\n\n<script>..."
        } else {
            echo "end not found\n";
            exit(1);
        }
    } else {
        echo "markers fail start=$start\n";
        exit(1);
    }
}

$new = <<<'BLADE'
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
                                <span class="od-avatar" style="background:linear-gradient(135deg,#2563eb,#1e3a8a);">{{ strtoupper(substr($nj['name'],0,1)) }}</span>
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
                                <span class="od-avatar" style="background:linear-gradient(135deg,#8b5cf6,#6d28d9);">{{ strtoupper(substr($ba['name'],0,1)) }}</span>
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
                                <div class="od-absent-item">
                                    <div class="d-flex align-items-center gap-2 min-w-0">
                                        <span class="od-avatar" style="background:linear-gradient(135deg,#ef4444,#b91c1c);">{{ strtoupper(substr($ab['name'],0,1)) }}</span>
                                        <div class="min-w-0">
                                            <div class="text-truncate" style="font-size:.9rem;font-weight:750;">{{ $ab['name'] }}</div>
                                            <div style="font-size:.76rem;color:#64748b;font-weight:600;">{{ $ab['code'] ?? '' }} · {{ $ab['department'] }}</div>
                                        </div>
                                    </div>
                                    <span class="od-pill bad">Absent</span>
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
                                                <span class="od-avatar" style="background:linear-gradient(135deg,#3b82f6,#1d4ed8);">{{ strtoupper(substr($la['name'],0,1)) }}</span>
                                                <span class="text-truncate fw-bold" style="max-width:130px;" title="{{ $la['name'] }}">{{ $la['name'] }}</span>
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
                                                <span class="od-avatar" style="background:linear-gradient(135deg,#f97316,#ea580c);">{{ strtoupper(substr($ul['name'],0,1)) }}</span>
                                                <span class="text-truncate fw-bold" style="max-width:120px;" title="{{ $ul['name'] }}">{{ $ul['name'] }}</span>
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

BLADE;

// Find precise end: from Finance comment to the closing </div> of admin-dashboard-root (before script)
$scriptPos = strpos($t, "<script>\nsetInterval(function () {\n    var el = document.getElementById('admin-live-clock');");
if ($scriptPos === false) {
    $scriptPos = strpos($t, "setInterval(function () {");
}
if ($start === false || $scriptPos === false) {
    echo "fail start=$start script=$scriptPos\n";
    exit(1);
}
// walk back from script to include the closing </div> of root in replacement (new already has it)
$before = substr($t, 0, $start);
$after = substr($t, $scriptPos);
file_put_contents($path, $before . $new . $after);
echo "ok\n";
