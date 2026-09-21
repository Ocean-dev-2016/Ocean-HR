@push('push_style')
    <style>
        .stat-card {
            transition: all 0.3s ease-in-out;
            cursor: pointer;
            text-decoration: none !important;
        }

        .stat-card:hover {
            background-color: #cff4fc !important;
            /* Bootstrap 5 `bg-info-subtle` */
            transform: translateY(-4px) scale(1.02);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }

        .stat-card:active {
            transform: translateY(-2px) scale(0.98);
            box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1) !important;
        }

        .operation-tile-link {
            text-decoration: none !important;
            display: block;
            color: inherit !important;
        }
    </style>
@endpush

@if ($modules['currentGuard'] === 'admin_software')

    <div class="row mb-1 mt-4">
        @if (isset($showNumericModule['totalCompanies']))
            <div class="col-12 col-sm-6 col-md-4 col-xl-2 mb-4" id="totalCompanyCard">
                <a href="{{ route('company.index') }}" target="_blank">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div class="badge bg-label-warning rounded">
                                    <i class="ti ti-building ti-md"></i>
                                </div>
                                <h1 class="mb-0 fw-bold text-primary">{{ $showNumericModule['totalCompanies'] ?? '00' }}
                                </h1>
                            </div>
                            <h5 class="card-title mb-1 pt-2">Total Company</h5>

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Expired Company</span>
                                <span
                                    class="badge bg-danger px-3 py-1">{{ isset($showNumericModule['expiredCompanies']) ? $showNumericModule['expiredCompanies'] : '0' }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Inactive Company</span>
                                <span
                                    class="badge bg-warning px-3 py-1">{{ isset($showNumericModule['inactiveCompanies']) ? $showNumericModule['inactiveCompanies'] : '0' }}</span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endif

        @if (isset($showNumericModule['totalPlans']))
            <div class="col-12 col-sm-6 col-md-4 col-xl-2 mb-4" id="totalPlanMasterCard">
                <a href="{{ route('plan-master.index') }}" target="_blank">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div class="badge bg-label-warning rounded">
                                    <i class="ti ti-list-details ti-md"></i>
                                </div>
                                <h1 class="mb-0 fw-bold text-primary">{{ $showNumericModule['totalPlans'] ?? '00' }}
                                </h1>
                            </div>
                            <h5 class="card-title mb-1 pt-2">Total Plans</h5>
                            {{-- <small class="text-muted">Last week</small> --}}
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Inactive Plans</span>
                                <span
                                    class="badge bg-danger px-3 py-1">{{ isset($showNumericModule['inactivePlans']) ? $showNumericModule['inactivePlans'] : '0' }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>General Plans</span>
                                <span
                                    class="badge bg-success px-3 py-1">{{ isset($showNumericModule['generalPlans']) ? $showNumericModule['generalPlans'] : '0' }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Private Plans</span>
                                <span
                                    class="badge bg-warning px-3 py-1">{{ isset($showNumericModule['privatePlans']) ? $showNumericModule['privatePlans'] : '0' }}</span>
                            </div>

                        </div>
                    </div>
                </a>
            </div>
        @endif
    </div>




@endif

{{-- Summary Statistics --}}
<div class="row mb-1 mt-4">
    @if (isset($modules['salary_calculation_view']) && $modules['salary_calculation_view'] && isset($showNumericModule['totalCompanyPayroll']))
        <div class="col-12 col-sm-6 col-md-4 col-xl-3 mb-4">
            <a href="{{ route('salary-calculation.index') }}" class="operation-tile-link">
                <div class="card h-100 shadow-sm border-0 stat-card" style="border-bottom: 4px solid #7367f0 !important; background: linear-gradient(135deg, #fff 0%, #f8f7ff 100%);">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="badge bg-label-primary rounded p-3">
                                <i class="ti ti-wallet ti-lg"></i>
                            </div>
                            <div class="text-end">
                                <h3 class="mb-0 fw-bold text-primary">₹{{ number_format($showNumericModule['totalCompanyPayroll'], 2) }}</h3>
                                <!-- <small class="text-muted">Filtered Amount</small> -->
                            </div>
                        </div>
                        <h5 class="card-title mb-1 fw-bold">Company Payroll</h5>
                        <p class="card-text text-muted mb-0 small">Total Company Payroll Employee Salary Calculation</p>
                    </div>
                </div>
            </a>
        </div>
    @endif

    @if (isset($modules['operations_rate_list_view']) && $modules['operations_rate_list_view'] && isset($showNumericModule['totalContractorSalary']))
        <div class="col-12 col-sm-6 col-md-4 col-xl-3 mb-4">
            <a href="{{ route('operations-rate-list.index') }}" class="operation-tile-link">
                <div class="card h-100 shadow-sm border-0 stat-card" style="border-bottom: 4px solid #28c76f !important; background: linear-gradient(135deg, #fff 0%, #f0fff4 100%);">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="badge bg-label-success rounded p-3">
                                <i class="ti ti-users-group ti-lg"></i>
                            </div>
                            <div class="text-end">
                                <h3 class="mb-0 fw-bold text-success">₹{{ number_format($showNumericModule['totalContractorSalary'], 2) }}</h3>
                                <!-- <small class="text-muted">Filtered Amount</small> -->
                            </div>
                        </div>
                        <h5 class="card-title mb-1 fw-bold">Contractor Salary</h5>
                        <p class="card-text text-muted mb-0 small">Total Contractor Salary Amount Sum</p>
                    </div>
                </div>
            </a>
        </div>
    @endif

    @if (isset($showNumericModule['totalSalary']))
        <div class="col-12 col-sm-6 col-md-4 col-xl-3 mb-4">
            <div class="card h-100 shadow-sm border-0 stat-card" style="border-bottom: 4px solid #ff9f43 !important; background: linear-gradient(135deg, #fff 0%, #fff7f0 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="badge bg-label-warning rounded p-3">
                            <i class="ti ti-businessplan ti-lg"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="mb-0 fw-bold text-warning">₹{{ number_format($showNumericModule['totalSalary'], 2) }}</h3>
                        </div>
                    </div>
                    <h5 class="card-title mb-1 fw-bold">Total Salary</h5>
                    <p class="card-text text-muted mb-0 small">Total Company Salary</p>
                </div>
            </div>
        </div>
    @endif
</div>


{{-- Operation Statistics (Hidden) --}}
{{-- @if (isset($modules['operations_rate_list_view']) && $modules['operations_rate_list_view'] && isset($operationStats) && count($operationStats) > 0)
    <div class="row mb-1 mt-4">
        <div class="col-12 mb-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="ti ti-chart-pie-2 me-2"></i>Operation Wise Total Amount</h5>
            <small class="text-muted">Filtered by selected date range</small>
        </div>
        @foreach ($operationStats as $stat)
            @php
                $icon = 'ti-settings';
                $color = '#5c6bc0'; // Default indigo
                $bg_color = 'rgba(92, 107, 192, 0.1)';
                
                $opName = strtoupper($stat->operation);
                if (str_contains($opName, 'CLEANING')) { $icon = 'ti-vacuum-cleaner'; $color = '#26a69a'; $bg_color = 'rgba(38, 166, 154, 0.1)'; }
                elseif (str_contains($opName, 'LATHE')) { $icon = 'ti-engine'; $color = '#ffa726'; $bg_color = 'rgba(255, 167, 38, 0.1)'; }
                elseif (str_contains($opName, 'GRINDING')) { $icon = 'ti-hammer'; $color = '#78909c'; $bg_color = 'rgba(120, 144, 156, 0.1)'; }
                elseif (str_contains($opName, 'CNC')) { $icon = 'ti-cpu'; $color = '#424242'; $bg_color = 'rgba(66, 66, 66, 0.1)'; }
                elseif (str_contains($opName, 'WELDING')) { $icon = 'ti-flame'; $color = '#ef5350'; $bg_color = 'rgba(239, 83, 80, 0.1)'; }
                elseif (str_contains($opName, 'BUFF')) { $icon = 'ti-sparkles'; $color = '#66bb6a'; $bg_color = 'rgba(102, 187, 106, 0.1)'; }
                elseif (str_contains($opName, 'COATING')) { $icon = 'ti-paint'; $color = '#29b6f6'; $bg_color = 'rgba(41, 182, 246, 0.1)'; }
                elseif (str_contains($opName, 'CORE')) { $icon = 'ti-box-model'; $color = '#ab47bc'; $bg_color = 'rgba(171, 71, 188, 0.1)'; }
                elseif (str_contains($opName, 'ASS')) { $icon = 'ti-packages'; $color = '#ec407a'; $bg_color = 'rgba(236, 64, 122, 0.1)'; }
                elseif (str_contains($opName, 'FOUNDRY')) { $icon = 'ti-building-factory-2'; $color = '#d32f2f'; $bg_color = 'rgba(211, 47, 47, 0.1)'; }
                elseif (str_contains($opName, 'BUTTERFLY')) { $icon = 'ti-butterfly'; $color = '#7e57c2'; $bg_color = 'rgba(126, 87, 194, 0.1)'; }
                elseif (str_contains($opName, 'FLEXIBLE')) { $icon = 'ti-arrows-diagonal'; $color = '#26c6da'; $bg_color = 'rgba(38, 198, 218, 0.1)'; }
                elseif (str_contains($opName, 'RRL')) { $icon = 'ti-refresh'; $color = '#5c6bc0'; $bg_color = 'rgba(92, 107, 192, 0.1)'; }
            @endphp
            <div class="col-12 col-sm-6 col-md-4 col-xl-2 mb-4">
                <a href="{{ route('operations-rate-list.index') }}?operation={{ urlencode($stat->operation) }}" class="operation-tile-link">
                    <div class="card h-100 shadow-none border-0 stat-card" style="border-left: 4px solid {{ $color }} !important; background: #fff; box-shadow: 0 2px 6px 0 rgba(67, 89, 113, 0.12);">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="avatar flex-shrink-0 me-3">
                                    <span class="avatar-initial rounded" style="background-color: {{ $bg_color }}; color: {{ $color }};">
                                        <i class="ti {{ $icon }} fs-3"></i>
                                    </span>
                                </div>
                                <div class="card-info">
                                    <h4 class="mb-0 fw-bold" style="color: {{ $color }};">₹{{ number_format($stat->total_amount, 2) }}</h4>
                                    <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.65rem;">Total Amount</small>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-dark" style="font-size: 0.85rem;">{{ $stat->operation }}</span>
                                <i class="ti ti-chevron-right text-muted" style="font-size: 0.8rem;"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
@endif --}}

{{-- Attendance Statistics --}}
@if (isset($attendanceStats) && is_array($attendanceStats))
    <div class="row mb-1 mt-4">
        <div class="col-12 mb-3">
            <h5 class="mb-3">Today's Attendance Statistics</h5>
        </div>

        {{-- Present Count --}}
        <div class="col-12 col-sm-6 col-md-3 col-xl-2 mb-4">
            <a href="{{ route('daily-attendance-report.index') }}?date={{ $attendanceStats['date'] ?? date('Y-m-d') }}&status=present"
                target="_blank" class="daily-attendance-stat-link">
                <div class="card h-100 shadow-sm stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div class="badge bg-label-success rounded">
                                <i class="ti ti-check ti-md"></i>
                            </div>
                            <h1 class="mb-0 fw-bold text-success">{{ $attendanceStats['present'] ?? '0' }}</h1>
                        </div>
                        <h5 class="card-title mb-1 pt-2">Present</h5>
                        <small class="text-muted">Employees present today</small>
                    </div>
                </div>
            </a>
        </div>

        {{-- Absent Count --}}
        <div class="col-12 col-sm-6 col-md-3 col-xl-2 mb-4">
            <a href="{{ route('daily-attendance-report.index') }}?date={{ $attendanceStats['date'] ?? date('Y-m-d') }}&status=absent"
                target="_blank" class="daily-attendance-stat-link">
                <div class="card h-100 shadow-sm stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div class="badge bg-label-danger rounded">
                                <i class="ti ti-x ti-md"></i>
                            </div>
                            <h1 class="mb-0 fw-bold text-danger">{{ $attendanceStats['absent'] ?? '0' }}</h1>
                        </div>
                        <h5 class="card-title mb-1 pt-2">Absent</h5>
                        <small class="text-muted">Employees absent today</small>
                    </div>
                </div>
            </a>
        </div>

        {{-- Leave Count --}}
        <div class="col-12 col-sm-6 col-md-3 col-xl-2 mb-4">
            <a href="{{ route('leave-application.index') }}?filter_date={{ $attendanceStats['date'] ?? date('Y-m-d') }}"
                target="_blank">
                <div class="card h-100 shadow-sm stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div class="badge bg-label-info rounded">
                                <i class="ti ti-calendar-off ti-md"></i>
                            </div>
                            <h1 class="mb-0 fw-bold text-info">{{ $attendanceStats['leave'] ?? '0' }}</h1>
                        </div>
                        <h5 class="card-title mb-1 pt-2">Leave</h5>
                        <small class="text-muted">Employees on leave today</small>
                    </div>
                </div>
            </a>
        </div>

        {{-- Late Punch Count --}}
        <div class="col-12 col-sm-6 col-md-3 col-xl-2 mb-4">
            <a href="{{ route('late-punch-report.index') }}?date={{ $attendanceStats['date'] ?? date('Y-m-d') }}"
                target="_blank">
                <div class="card h-100 shadow-sm stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div class="badge bg-label-warning rounded">
                                <i class="ti ti-clock-hour-4 ti-md"></i>
                            </div>
                            <h1 class="mb-0 fw-bold text-warning">{{ $attendanceStats['late_punch'] ?? '0' }}</h1>
                        </div>
                        <h5 class="card-title mb-1 pt-2">Late Punch</h5>
                        <small class="text-muted">Employees with late punch</small>
                    </div>
                </div>
            </a>
        </div>

        {{-- Early Go Count --}}
        <div class="col-12 col-sm-6 col-md-3 col-xl-2 mb-4">
            <a href="{{ route('early-going-report.index') }}?date={{ $attendanceStats['date'] ?? date('Y-m-d') }}"
                target="_blank">
                <div class="card h-100 shadow-sm stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div class="badge bg-label-danger rounded">
                                <i class="ti ti-arrow-left ti-md"></i>
                            </div>
                            <h1 class="mb-0 fw-bold text-danger">{{ $attendanceStats['early_go'] ?? '0' }}</h1>
                        </div>
                        <h5 class="card-title mb-1 pt-2">Early Go</h5>
                        <small class="text-muted">Employees who left early</small>
                    </div>
                </div>
            </a>
        </div>

        {{-- Total Employees --}}
        <div class="col-12 col-sm-6 col-md-3 col-xl-2 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="badge bg-label-primary rounded">
                            <i class="ti ti-users ti-md"></i>
                        </div>
                        <h1 class="mb-0 fw-bold text-primary">{{ $attendanceStats['total_employees'] ?? '0' }}</h1>
                    </div>
                    <h5 class="card-title mb-1 pt-2">Total Employees</h5>
                    
                    <div class="mt-2 pt-1 border-top">
                        <a href="{{ route('employees.index') }}" class="total-regular-employees-link d-flex justify-content-between align-items-center mb-1 text-decoration-none p-1 rounded" style="transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#f1f0ff'" onmouseout="this.style.backgroundColor='transparent'">
                            <small class="text-muted">Total Employee:</small>
                            <span class="badge bg-label-success fw-bold">{{ $attendanceStats['total_regular_employees'] ?? '0' }}</span>
                        </a>
                        <a href="{{ route('contractor-employees.index') }}" class="total-contractors-link d-flex justify-content-between align-items-center text-decoration-none p-1 rounded" style="transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#f1f0ff'" onmouseout="this.style.backgroundColor='transparent'">
                            <small class="text-muted">Total Contractor:</small>
                            <span class="badge bg-label-warning fw-bold">{{ $attendanceStats['total_contractors'] ?? '0' }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@else
    <div class="row mb-1 mt-4">
        <div class="col-12">
            <div class="p-4 text-center">
                <p class="mb-0 fs-5">No attendance data available. Please apply filters to see statistics.</p>
            </div>
        </div>
    </div>
@endif

{{-- Late Punch Employee List --}}
@if (isset($attendanceStats['late_punch_employees']) && count($attendanceStats['late_punch_employees']) > 0)
    <div class="row mb-1 mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-clock-hour-4 me-2"></i> Late Punch Employees
                        ({{ count($attendanceStats['late_punch_employees']) }})
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th width="5%">Sr No.</th>
                                    <th width="15%">Employee Code</th>
                                    <th width="25%">Employee Name</th>
                                    <th width="15%">Punch In Time</th>
                                    <th width="15%">Expected Time</th>
                                    <th width="15%">Shift</th>
                                    <th width="10%">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($attendanceStats['late_punch_employees'] as $index => $employee)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $employee['employee_code'] }}</td>
                                        <td>{{ $employee['employee_name'] ?: '-' }}</td>
                                        <td>
                                            <span class="badge bg-label-warning">
                                                {{ $employee['punch_in_time'] ? \Carbon\Carbon::parse($employee['punch_in_time'])->format('h:i A') : '-' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-label-info">
                                                {{ $employee['expected_time'] ? \Carbon\Carbon::parse($employee['expected_time'])->format('h:i A') : '-' }}
                                            </span>
                                        </td>
                                        <td>{{ $employee['shift_name'] }}</td>
                                        <td>
                                            <span class="badge bg-label-danger">Late</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- Absent Employee List --}}
@if (isset($attendanceStats['absent_employees']) && count($attendanceStats['absent_employees']) > 0)
    <div class="row mb-1 mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-x me-2"></i> Absent Employees ({{ count($attendanceStats['absent_employees']) }})
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th width="5%">Sr No.</th>
                                    <th width="20%">Employee Code</th>
                                    <th width="35%">Employee Name</th>
                                    <th width="20%">Status</th>
                                    <th width="20%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($attendanceStats['absent_employees'] as $index => $employee)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $employee['employee_code'] }}</td>
                                        <td>{{ $employee['employee_name'] ?: '-' }}</td>
                                        <td>
                                            <span class="badge bg-label-danger">Absent</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('attendance.index') }}?filter_company={{ $employee['company_id'] }}&filter_employee={{ $employee['employee_id'] }}"
                                                class="btn btn-sm btn-primary" target="_blank">
                                                <i class="ti ti-eye me-1"></i> View Attendance
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif