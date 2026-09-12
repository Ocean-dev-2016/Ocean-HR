@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : 'Contractor Employee';
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $company_id = isset($modules['company_id']) ? $modules['company_id'] : null;
@endphp

@section('title', $page_title . ' Detail')

@section('page_leavel_style')
    <style>
        /* Modern UI Glassmorphism & Custom Elements */
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 12px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.05);
            transition: all 0.3s ease;
        }
        
        .kpi-card {
            border: none;
            border-radius: 10px;
            padding: 18px;
            color: #fff;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .kpi-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .kpi-card .card-icon {
            position: absolute;
            right: 15px;
            bottom: 10px;
            font-size: 3.5rem;
            opacity: 0.15;
            transition: transform 0.3s ease;
        }
        
        .kpi-card:hover .card-icon {
            transform: scale(1.15) rotate(-5deg);
        }
        
        .kpi-present { background: linear-gradient(135deg, #28a745, #1e7e34); }
        .kpi-absent { background: linear-gradient(135deg, #dc3545, #bd2130); }
        .kpi-halfday { background: linear-gradient(135deg, #17a2b8, #117a8b); }
        .kpi-leave { background: linear-gradient(135deg, #6f42c1, #563d7c); }
        .kpi-holiday { background: linear-gradient(135deg, #343a40, #212529); }
        .kpi-misspunch { background: linear-gradient(135deg, #ffc107, #d39e00); color: #212529 !important; }
        .kpi-misspunch .card-icon { opacity: 0.25; }

        /* Skeleton Loading Animation */
        .skeleton {
            background-color: #e2e8f0;
            background-image: linear-gradient(
                90deg,
                rgba(255, 255, 255, 0) 0%,
                rgba(255, 255, 255, 0.6) 50%,
                rgba(255, 255, 255, 0) 100%
            );
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }
        
        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        .skeleton-text {
            height: 16px;
            border-radius: 4px;
            margin-bottom: 8px;
        }
        
        .skeleton-td {
            height: 20px;
            border-radius: 4px;
        }

        /* Profile Circular Avatar styling */
        .profile-avatar {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            font-weight: 700;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 3px solid #fff;
        }

        /* Badges & Tables styling */
        .badge-pill-custom {
            padding: 5px 12px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 0.75rem;
            letter-spacing: 0.3px;
        }
        
        .table-custom th {
            font-weight: 600;
            color: #4a5568;
            background-color: #f7fafc;
            border-bottom-width: 1px;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }
        
        .table-custom td {
            vertical-align: middle;
        }

        .modal-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
        }
    </style>
@endsection

@section('content')
    <div class="px-1">
        <div class="d-lg-flex justify-content-lg-between flex-column flex-lg-row">
            @include('software.inlcudes.breadcrumb', [
                'breadcrumbArray' => [
                    ['title' => $page_title, 'url' => route($route . '.index')],
                    ['title' => 'Employee Profile & Attendance', 'url' => ''],
                ],
                'route' => $route,
                'show_back_btn' => true,
            ])
        </div>
    </div>

    <div class="row mt-3">
        <!-- Sidebar / Employee Basic Info Card -->
        <div class="col-xl-4 col-lg-5 mb-4">
            <div class="card glass-card h-100">
                <div class="card-body text-center pt-4">
                    <!-- Profile Picture or initials -->
                    <div class="d-flex justify-content-center mb-3">
                        @if($show?->employee_photo)
                            <img src="{{ asset($show->employee_photo) }}" alt="User image"
                                 class="rounded-circle img-thumbnail" style="width: 100px; height: 100px; object-fit: cover; box-shadow: 0 4px 15px rgba(0,0,0,0.1);" />
                        @else
                            <div class="profile-avatar">
                                {{ strtoupper(substr($show->first_name ?? $show->full_name ?? 'E', 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    
                    <h4 class="mb-1 fw-bold text-dark">{{ $show?->full_name }}</h4>
                    <p class="text-muted mb-2"><i class="ti ti-id"></i> Code: <strong>{{ $show?->employee_code ?? '--' }}</strong></p>
                    
                    <span class="badge badge-pill-custom {{ $show?->status == 'active' ? 'bg-label-success' : 'bg-label-danger' }} mb-4">
                        {{ ucfirst($show?->status ?? 'active') }}
                    </span>

                    <hr class="my-4" style="opacity: 0.1;">

                    <!-- Info Details -->
                    <div class="text-start">
                        <h6 class="text-uppercase text-muted mb-3" style="font-size: 0.75rem; letter-spacing: 0.5px;">About Details</h6>
                        <ul class="list-unstyled mb-4">
                            <li class="d-flex align-items-center mb-3">
                                <i class="ti ti-building text-primary me-2"></i>
                                <span>Company: <strong>{{ $show?->company?->company_name ?? '--' }}</strong></span>
                            </li>
                            <li class="d-flex align-items-center mb-3">
                                <i class="ti ti-git-branch text-primary me-2"></i>
                                <span>Branch: <strong>{{ $show?->branch?->branch_name ?? '--' }}</strong></span>
                            </li>
                            <li class="d-flex align-items-center mb-3">
                                <i class="ti ti-briefcase text-primary me-2"></i>
                                <span>Designation: <strong>{{ $show?->employment_details?->first()?->designation?->designation_name ?? '--' }}</strong></span>
                            </li>
                            <li class="d-flex align-items-center mb-3">
                                <i class="ti ti-hierarchy-2 text-primary me-2"></i>
                                <span>Department: <strong>{{ $show?->employment_details?->first()?->department?->department_name ?? '--' }}</strong></span>
                            </li>
                        </ul>

                        <h6 class="text-uppercase text-muted mb-3" style="font-size: 0.75rem; letter-spacing: 0.5px;">Contacts & System</h6>
                        <ul class="list-unstyled mb-0">
                            <li class="d-flex align-items-center mb-3">
                                <i class="ti ti-phone text-primary me-2"></i>
                                <span>Mobile: <strong>{{ $show?->contact_number ?? '--' }}</strong></span>
                            </li>
                            <li class="d-flex align-items-center mb-3">
                                <i class="ti ti-mail text-primary me-2"></i>
                                <span class="text-truncate">Email: <strong>{{ $show?->email ?? '--' }}</strong></span>
                            </li>
                            <li class="d-flex align-items-center mb-0">
                                <i class="ti ti-user-check text-primary me-2"></i>
                                <span>Username: <strong>{{ $show?->username ?? '--' }}</strong></span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Registry Dashboard Column -->
        <div class="col-xl-8 col-lg-7 mb-4">
            <div class="card glass-card h-100">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between pb-3 border-bottom border-light">
                    <div class="d-flex align-items-center gap-2">
                        <i class="ti ti-calendar-event text-primary fs-3"></i>
                        <h5 class="m-0 fw-bold text-dark">Monthly Attendance Registry</h5>
                    </div>
                    
                    <!-- Month & Year Selectors -->
                    <div class="d-flex gap-2 align-items-center mt-2 mt-md-0">
                        <select id="select-month" class="form-select form-select-sm" style="width: 120px;">
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $m == now()->month ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                </option>
                            @endfor
                        </select>
                        <select id="select-year" class="form-select form-select-sm" style="width: 100px;">
                            @for ($y = now()->year; $y >= now()->year - 3; $y--)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </select>
                        <button id="btn-load" class="btn btn-sm btn-primary">
                            <i class="ti ti-refresh me-1"></i> Load
                        </button>
                    </div>
                </div>

                <div class="card-body pt-4">
                    <!-- KPI statistics -->
                    <div class="row g-3 mb-4" id="kpi-container">
                        <div class="col-md-3 col-6">
                            <div class="kpi-card kpi-present">
                                <div class="card-icon"><i class="ti ti-checkbox"></i></div>
                                <h6 class="m-0" style="font-size: 0.8rem; font-weight: 500;">Presents</h6>
                                <h3 class="m-0 fw-bold mt-1" id="stat-present">0</h3>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="kpi-card kpi-halfday">
                                <div class="card-icon"><i class="ti ti-hourglass"></i></div>
                                <h6 class="m-0" style="font-size: 0.8rem; font-weight: 500;">Half Days</h6>
                                <h3 class="m-0 fw-bold mt-1" id="stat-halfday">0</h3>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="kpi-card kpi-leave">
                                <div class="card-icon"><i class="ti ti-plane-departure"></i></div>
                                <h6 class="m-0" style="font-size: 0.8rem; font-weight: 500;">Leaves</h6>
                                <h3 class="m-0 fw-bold mt-1" id="stat-leave">0</h3>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="kpi-card kpi-absent">
                                <div class="card-icon"><i class="ti ti-circle-x"></i></div>
                                <h6 class="m-0" style="font-size: 0.8rem; font-weight: 500;">Absents</h6>
                                <h3 class="m-0 fw-bold mt-1" id="stat-absent">0</h3>
                            </div>
                        </div>
                        <div class="col-md-4 col-6 mt-3">
                            <div class="kpi-card kpi-holiday">
                                <div class="card-icon"><i class="ti ti-confetti"></i></div>
                                <h6 class="m-0" style="font-size: 0.8rem; font-weight: 500;">Holidays</h6>
                                <h3 class="m-0 fw-bold mt-1" id="stat-holiday">0</h3>
                            </div>
                        </div>
                        <div class="col-md-4 col-6 mt-3">
                            <div class="kpi-card kpi-misspunch">
                                <div class="card-icon"><i class="ti ti-alert-triangle"></i></div>
                                <h6 class="m-0" style="font-size: 0.8rem; font-weight: 500;">Missed Punches</h6>
                                <h3 class="m-0 fw-bold mt-1" id="stat-misspunch">0</h3>
                            </div>
                        </div>
                        <div class="col-md-4 col-12 mt-3">
                            <div class="kpi-card bg-primary text-white" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                                <div class="card-icon"><i class="ti ti-calendar"></i></div>
                                <h6 class="m-0" style="font-size: 0.8rem; font-weight: 500;">Total Days</h6>
                                <h3 class="m-0 fw-bold mt-1" id="stat-total-days">0</h3>
                            </div>
                        </div>
                    </div>

                    <!-- Attendance Logs Table -->
                    <div class="table-responsive border rounded" style="max-height: 480px;">
                        <table class="table table-hover table-custom m-0 align-middle" id="attendance-table">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 60px;">Day</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th class="text-center">First In</th>
                                    <th class="text-center">Last Out</th>
                                    <th class="text-center">Working Hours</th>
                                    <th class="text-center" style="width: 80px;">Punches</th>
                                </tr>
                            </thead>
                            <tbody id="attendance-rows">
                                <!-- Skeleton loaders initially -->
                                @for ($k = 0; $k < 5; $k++)
                                    <tr class="skeleton-tr">
                                        <td class="text-center"><div class="skeleton skeleton-td" style="width: 30px; margin: auto;"></div></td>
                                        <td><div class="skeleton skeleton-td" style="width: 120px;"></div></td>
                                        <td><div class="skeleton skeleton-td" style="width: 80px;"></div></td>
                                        <td><div class="skeleton skeleton-td" style="width: 60px; margin: auto;"></div></td>
                                        <td><div class="skeleton skeleton-td" style="width: 60px; margin: auto;"></div></td>
                                        <td><div class="skeleton skeleton-td" style="width: 60px; margin: auto;"></div></td>
                                        <td><div class="skeleton skeleton-td" style="width: 40px; margin: auto;"></div></td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 Punches Log Details Modal -->
    <div class="modal fade" id="punchDetailsModal" tabindex="-1" aria-labelledby="punchDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
                <div class="modal-header bg-primary text-white border-0 py-3">
                    <h5 class="modal-title fw-bold" id="punchDetailsModalLabel"><i class="ti ti-alarm-clock me-1"></i> Punch Logs</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" id="modal-content-area">
                    <!-- Dynamic content injected here -->
                </div>
                <div class="modal-footer border-0 bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page_leavel_js')
    <script>
        $(document).ready(function() {
            var employeeId = "{{ $show->id }}";
            
            // Function to load attendance details via AJAX
            function loadAttendance(month, year) {
                // Show skeletons
                var skeletons = '';
                for (var k = 0; k < 10; k++) {
                    skeletons += '<tr class="skeleton-tr">' +
                        '<td class="text-center"><div class="skeleton skeleton-td" style="width: 30px; margin: auto;"></div></td>' +
                        '<td><div class="skeleton skeleton-td" style="width: 120px;"></div></td>' +
                        '<td><div class="skeleton skeleton-td" style="width: 80px;"></div></td>' +
                        '<td><div class="skeleton skeleton-td" style="width: 60px; margin: auto;"></div></td>' +
                        '<td><div class="skeleton skeleton-td" style="width: 60px; margin: auto;"></div></td>' +
                        '<td><div class="skeleton skeleton-td" style="width: 60px; margin: auto;"></div></td>' +
                        '<td class="text-center"><div class="skeleton skeleton-td" style="width: 40px; margin: auto;"></div></td>' +
                        '</tr>';
                }
                $('#attendance-rows').html(skeletons);
                
                // Fetch data
                $.ajax({
                    url: "{{ route($route . '.show', $show->id) }}",
                    type: 'GET',
                    data: {
                        month: month,
                        year: year
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Populate KPIs
                            $('#stat-present').text(response.stats.presents);
                            $('#stat-halfday').text(response.stats.half_days);
                            $('#stat-leave').text(response.stats.leaves);
                            $('#stat-absent').text(response.stats.absents);
                            $('#stat-holiday').text(response.stats.holidays);
                            $('#stat-misspunch').text(response.stats.miss_punches);
                            $('#stat-total-days').text(response.stats.total_days);
                            
                            // Populate Table rows
                            var rows = '';
                            if (response.records && response.records.length > 0) {
                                $.each(response.records, function(index, record) {
                                    var dayClass = '';
                                    if (record.day_name === 'Sunday') {
                                        dayClass = 'table-danger table-opacity';
                                    }
                                    
                                    var actionBtn = '-';
                                    if (record.all_punches && record.all_punches.length > 0) {
                                        actionBtn = '<button class="btn btn-xs btn-outline-primary btn-icon view-punches" ' +
                                            'data-date="' + record.date + '" ' +
                                            'data-day-name="' + record.day_name + '" ' +
                                            'data-punches=\'' + JSON.stringify(record.all_punches) + '\' ' +
                                            'data-wh="' + record.working_hours + '" ' +
                                            'title="View Punches Log">' +
                                            '<i class="ti ti-eye"></i></button>';
                                    }
                                    
                                    var firstIn = record.in_time;
                                    if (firstIn && firstIn !== '-') {
                                        firstIn = '<span class="text-success fw-bold"><i class="ti ti-arrow-bar-to-down me-1"></i>' + firstIn + '</span>';
                                    }
                                    
                                    var lastOut = record.out_time;
                                    if (lastOut && lastOut !== '-') {
                                        lastOut = '<span class="text-danger fw-bold"><i class="ti ti-arrow-bar-to-up me-1"></i>' + lastOut + '</span>';
                                    }
                                    
                                    var whDisplay = record.working_hours;
                                    if (whDisplay && whDisplay !== '-') {
                                        whDisplay = '<span class="fw-bold"><i class="ti ti-clock me-1 text-muted"></i>' + whDisplay + '</span>';
                                    }
                                    
                                    rows += '<tr class="' + dayClass + '">' +
                                        '<td class="text-center font-monospace fw-semibold">' + record.day + '</td>' +
                                        '<td>' + record.date + ' <small class="text-muted">(' + record.day_name.substring(0, 3) + ')</small></td>' +
                                        '<td><span class="badge badge-pill-custom ' + record.badge_class + '">' + record.status + '</span></td>' +
                                        '<td class="text-center">' + firstIn + '</td>' +
                                        '<td class="text-center">' + lastOut + '</td>' +
                                        '<td class="text-center">' + whDisplay + '</td>' +
                                        '<td class="text-center">' + actionBtn + '</td>' +
                                        '</tr>';
                                });
                            } else {
                                rows = '<tr><td colspan="7" class="text-center py-4 text-muted">No attendance logs found for this period.</td></tr>';
                            }
                            $('#attendance-rows').html(rows);
                        } else {
                            toastr.error('Failed to parse attendance records.');
                        }
                    },
                    error: function(xhr) {
                        toastr.error('Error fetching attendance details. Please try again.');
                    }
                });
            }

            // Initial load for current month & year
            var currentMonth = $('#select-month').val();
            var currentYear = $('#select-year').val();
            loadAttendance(currentMonth, currentYear);

            // Load on button click
            $('#btn-load').click(function() {
                var m = $('#select-month').val();
                var y = $('#select-year').val();
                loadAttendance(m, y);
            });

            // Action: View Punch logs in Modal
            $(document).on('click', '.view-punches', function() {
                var dateStr = $(this).data('date');
                var dayName = $(this).data('day-name');
                var punches = $(this).data('punches');
                var wh = $(this).data('wh');
                
                var title = '<i class="ti ti-clock me-1"></i> Punch Details - ' + dateStr + ' (' + dayName + ')';
                $('#punchDetailsModalLabel').html(title);
                
                var html = '<div class="mb-3 text-secondary" style="font-size: 0.9rem;">' +
                    'Detailed logs of all biometric punches registered on this day:' +
                    '</div>';
                
                html += '<table class="table table-bordered table-sm modal-table mb-4 align-middle text-center">' +
                    '<thead>' +
                    '<tr>' +
                    '<th style="width: 60px;">Sr No</th>' +
                    '<th>Type</th>' +
                    '<th>Punch Time</th>' +
                    '<th>Device Source</th>' +
                    '</tr>' +
                    '</thead>' +
                    '<tbody>';
                
                var pairs = [];
                var currentIn = null;
                
                $.each(punches, function(idx, punch) {
                    var typeLabel = '';
                    var typeLower = punch.type.toLowerCase();
                    if (typeLower === 'in') {
                        typeLabel = '<span class="badge bg-label-success"><i class="ti ti-login me-1"></i>IN</span>';
                        if (currentIn !== null) {
                            pairs.push({ in: currentIn, out: null });
                        }
                        currentIn = punch.time;
                    } else {
                        typeLabel = '<span class="badge bg-label-danger"><i class="ti ti-logout me-1"></i>OUT</span>';
                        if (currentIn !== null) {
                            pairs.push({ in: currentIn, out: punch.time });
                            currentIn = null;
                        } else {
                            pairs.push({ in: null, out: punch.time });
                        }
                    }
                    
                    html += '<tr>' +
                        '<td>' + (idx + 1) + '</td>' +
                        '<td>' + typeLabel + '</td>' +
                        '<td class="font-monospace fw-bold">' + punch.time + '</td>' +
                        '<td><small class="text-muted"><i class="ti ti-cpu me-1"></i>' + punch.device + '</small></td>' +
                        '</tr>';
                });
                
                if (currentIn !== null) {
                    pairs.push({ in: currentIn, out: null });
                }
                
                html += '</tbody></table>';
                
                // Time summary
                if (pairs.length > 0) {
                    html += '<h6 class="fw-bold text-dark border-bottom pb-2 mb-3"><i class="ti ti-chart-pie-2 me-1"></i>Session Calculations</h6>';
                    html += '<table class="table table-sm text-center align-middle">' +
                        '<thead>' +
                        '<tr>' +
                        '<th>IN Time</th>' +
                        '<th>OUT Time</th>' +
                        '<th>Duration</th>' +
                        '</tr>' +
                        '</thead>' +
                        '<tbody>';
                        
                    $.each(pairs, function(pIdx, pair) {
                        var inDisp = pair.in ? '<span class="text-success font-monospace fw-semibold">' + pair.in + '</span>' : '<span class="text-muted">-</span>';
                        var outDisp = pair.out ? '<span class="text-danger font-monospace fw-semibold">' + pair.out + '</span>' : '<span class="text-muted">-</span>';
                        
                        var durationDisp = '<span class="text-muted">-</span>';
                        if (pair.in && pair.out) {
                            var inParts = pair.in.split(':');
                            var outParts = pair.out.split(':');
                            if (inParts.length >= 2 && outParts.length >= 2) {
                                var inMins = parseInt(inParts[0]) * 60 + parseInt(inParts[1]);
                                var outMins = parseInt(outParts[0]) * 60 + parseInt(outParts[1]);
                                if (outMins < inMins) outMins += 24 * 60;
                                var diff = outMins - inMins;
                                var h = Math.floor(diff / 60);
                                var m = diff % 60;
                                durationDisp = '<span class="badge bg-secondary font-monospace">' + 
                                    (h < 10 ? '0' + h : h) + ':' + (m < 10 ? '0' + m : m) + '</span>';
                            }
                        }
                        
                        html += '<tr>' +
                            '<td>' + inDisp + '</td>' +
                            '<td>' + outDisp + '</td>' +
                            '<td>' + durationDisp + '</td>' +
                            '</tr>';
                    });
                    
                    if (wh && wh !== '-') {
                        html += '<tr class="table-light fw-bold">' +
                            '<td colspan="2" class="text-start">Total Accumulated Hours:</td>' +
                            '<td><span class="badge bg-primary fs-6">' + wh + '</span></td>' +
                            '</tr>';
                    }
                    
                    html += '</tbody></table>';
                }
                
                $('#modal-content-area').html(html);
                $('#punchDetailsModal').modal('show');
            });
        });
    </script>
@endsection
