@if ($employees->count() > 0)
    <div class="row g-3">
        @foreach ($employees as $emp)
            @php
                $statusClass = 'bg-label-success text-success';
                $statusDotClass = 'bg-success';
                if ($emp->status == 'inactive') {
                    $statusClass = 'bg-label-danger text-danger';
                    $statusDotClass = 'bg-danger';
                } elseif ($emp->status == 'resigned') {
                    $statusClass = 'bg-label-warning text-warning';
                    $statusDotClass = 'bg-warning';
                }

                $isDeleted = !empty($emp->deleted_at);
                $appKey = $emp->company->app_key ?? '-';
                $userSp = !empty($emp->sp) ? \App\Helpers\Helper::getSP($emp->sp) : '-';
                $username = $emp->username ?? '-';

                // Compute Initials and Palette
                $name = trim($emp->full_name ?? '');
                $initials = '';
                if ($name) {
                    $parts = preg_split('/\s+/', $name);
                    if (count($parts) >= 2) {
                        $initials = mb_strtoupper(mb_substr($parts[0], 0, 1) . mb_substr($parts[1], 0, 1));
                    } else {
                        $initials = mb_strtoupper(mb_substr($parts[0], 0, 2));
                    }
                }
                $initials = $initials ?: 'EM';

                $palette = [
                    ['bg' => '#eae8fd', 'color' => '#7367f0'], // Purple
                    ['bg' => '#e8fadf', 'color' => '#28c76f'], // Green
                    ['bg' => '#fce5e6', 'color' => '#ea5455'], // Red
                    ['bg' => '#fff0e1', 'color' => '#ff9f43'], // Orange
                    ['bg' => '#dff7f9', 'color' => '#00cfe8'], // Cyan
                    ['bg' => '#fbe6f2', 'color' => '#e83e8c'], // Pink
                    ['bg' => '#e2ecff', 'color' => '#0070ba'], // Blue
                ];
                $hash = crc32($name . $emp->id);
                $scheme = $palette[abs($hash) % count($palette)];
            @endphp
            <div class="col-xxl-3 col-xl-3 col-lg-4 col-md-6 col-sm-6 col-12">
                <div class="card h-100 employee-card border shadow-xs transition-all hover-shadow {{ $isDeleted ? 'bg-light opacity-75' : '' }}">
                    <div class="card-body p-3 d-flex flex-column justify-content-between position-relative">
                        
                        {{-- Top Header Section: Status & Dropdown --}}
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="dropdown">
                                <span class="badge {{ $statusClass }} rounded-pill px-2.5 py-1 d-inline-flex align-items-center cursor-pointer dropdown-toggle" 
                                      data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.72rem; font-weight: 600;">
                                    <span class="badge-dot {{ $statusDotClass }} me-1" style="width: 6px; height: 6px; border-radius: 50%; display: inline-block;"></span>
                                    {{ ucfirst($emp->status ?? 'Active') }}
                                </span>
                                <ul class="dropdown-menu shadow-sm border-0 py-1">
                                    <li>
                                        <a href="javascript:void(0)" class="dropdown-item update-status py-1 px-3 d-flex align-items-center"
                                           data-url="{{ route($modules['route'] . '.status-update') }}" data-id="{{ $emp->id }}" data-update_status="active">
                                            <span class="badge-dot bg-success me-2" style="width: 7px; height: 7px; border-radius: 50%;"></span> Active
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)" class="dropdown-item update-status py-1 px-3 d-flex align-items-center"
                                           data-url="{{ route($modules['route'] . '.status-update') }}" data-id="{{ $emp->id }}" data-update_status="inactive">
                                            <span class="badge-dot bg-danger me-2" style="width: 7px; height: 7px; border-radius: 50%;"></span> Inactive
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)" class="dropdown-item update-status py-1 px-3 d-flex align-items-center"
                                           data-url="{{ route($modules['route'] . '.status-update') }}" data-id="{{ $emp->id }}" data-update_status="resigned">
                                            <span class="badge-dot bg-warning me-2" style="width: 7px; height: 7px; border-radius: 50%;"></span> Resigned
                                        </a>
                                    </li>
                                </ul>
                            </div>

                            <div class="dropdown">
                                <button type="button" class="btn btn-sm btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow" data-bs-toggle="dropdown" aria-expanded="false" style="width: 28px; height: 28px;">
                                    <i class="ti ti-dots-vertical text-muted"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 py-1">
                                    <li>
                                        <a class="dropdown-item py-1.5 px-3" href="{{ route($modules['route'] . '.show', [$emp->id]) }}">
                                            <i class="fa-solid fa-eye me-2 text-info"></i> View Profile
                                        </a>
                                    </li>
                                    @if (!$isDeleted)
                                        @if (isset($modules['update_permission']) && $modules['update_permission'])
                                            <li>
                                                <a class="dropdown-item py-1.5 px-3" href="{{ route($modules['route'] . '.edit', [$emp->id]) }}">
                                                    <i class="fa-solid fa-pen-to-square me-2 text-primary"></i> Edit Employee
                                                </a>
                                            </li>
                                        @endif
                                        <li>
                                            <a href="javascript:void(0)" data-id="{{ $emp->id }}" data-resign-date="{{ $emp->resign_date ?? '' }}"
                                               class="dropdown-item py-1.5 px-3 resign-date-btn" data-bs-toggle="modal" data-bs-target="#resignDateModal">
                                                <i class="fa-solid fa-calendar-times me-2 text-warning"></i> Resign Date
                                            </a>
                                        </li>
                                        @if (isset($modules['currentGuard']) && $modules['currentGuard'] === 'admin_software')
                                            <li><hr class="dropdown-divider my-1"></li>
                                            <li>
                                                <a href="javascript:void(0)" class="dropdown-item py-1.5 px-3 copy-login-details"
                                                   data-app-key="{{ $appKey }}" data-username="{{ $username }}" data-password="{{ $userSp }}">
                                                    <i class="ti ti-copy me-2 text-secondary"></i> Copy Details
                                                </a>
                                            </li>
                                        @endif
                                        @if (isset($modules['delete_permission']) && $modules['delete_permission'])
                                            <li><hr class="dropdown-divider my-1"></li>
                                            <li>
                                                <a href="javascript:void(0)" data-id="{{ $emp->id }}" data-did="{{ route($modules['route'] . '.destroy', [$emp->id]) }}"
                                                   class="dropdown-item py-1.5 px-3 text-danger deletebutton">
                                                    <i class="fa-solid fa-trash me-2 text-danger"></i> Delete
                                                </a>
                                            </li>
                                        @endif
                                    @else
                                        <li>
                                            <a href="javascript:void(0)" data-restore="{{ route($modules['route'] . '.restore', ['id' => $emp->id]) }}"
                                               class="dropdown-item py-1.5 px-3 text-success record-restore">
                                                <i class="ti ti-history me-2 text-success"></i> Restore
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </div>

                        {{-- Middle Section: Avatar & Basic Details --}}
                        <div class="text-center mb-3">
                            <div class="position-relative d-inline-block mt-n2 mb-2">
                                <div class="avatar-initials-circle rounded-circle d-flex align-items-center justify-content-center shadow-xs mx-auto"
                                     style="width: 60px; height: 60px; background-color: {{ $scheme['bg'] }}; color: {{ $scheme['color'] }}; font-weight: 700; font-size: 1.25rem; border: 2.5px solid #fff; box-shadow: 0 3px 8px rgba(0,0,0,0.08);">
                                    {{ $initials }}
                                </div>
                            </div>
                            <h6 class="mb-1 text-truncate fw-bold">
                                <a href="{{ route($modules['route'] . '.show', [$emp->id]) }}" class="text-heading text-hover-primary" title="{{ $emp->full_name }}">
                                    {{ $emp->full_name ?? '-' }}
                                </a>
                            </h6>
                            <div class="text-muted small mb-2 text-truncate fw-medium" style="font-size: 0.8rem;">
                                <i class="ti ti-briefcase font-size-xs me-1 text-primary"></i>
                                {{ $emp->team_role_name ?? ($emp->current_role?->name ?? 'No Role Assigned') }}
                            </div>

                            <div class="d-flex flex-wrap justify-content-center gap-1.5 my-2">
                                <span class="badge bg-label-primary font-size-xs px-2.5 py-1" title="Employee Code">
                                    <i class="ti ti-id me-1"></i>{{ $emp->employee_code ?? '-' }}
                                </span>
                                @if (!empty($emp->biometric_user_id))
                                    <span class="badge bg-label-secondary font-size-xs px-2.5 py-1" title="Biometric ID">
                                        <i class="ti ti-fingerprint me-1"></i>{{ $emp->biometric_user_id }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Info Details (Company, Contact, Email, Login) --}}
                        <div class="employee-card-details mb-3">
                            <div class="d-flex flex-column gap-1.5">
                                @if (!empty($emp->company_name) || !empty($emp->company?->company_name))
                                    <div class="d-flex align-items-center text-truncate employee-detail-item" title="Company">
                                        <span class="employee-detail-icon bg-label-secondary text-secondary">
                                            <i class="ti ti-building"></i>
                                        </span>
                                        <span class="text-truncate text-body fw-medium">{{ $emp->company_name ?? $emp->company?->company_name }}</span>
                                    </div>
                                @endif

                                <div class="d-flex align-items-center text-truncate employee-detail-item" title="Phone">
                                    <span class="employee-detail-icon bg-label-primary text-primary">
                                        <i class="ti ti-phone"></i>
                                    </span>
                                    @if (!empty($emp->contact_number))
                                        <a href="tel:{{ $emp->contact_number }}" class="text-body text-truncate fw-medium text-hover-primary">
                                            {{ $emp->contact_number }}
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </div>

                                <div class="d-flex align-items-center text-truncate employee-detail-item" title="Email">
                                    <span class="employee-detail-icon bg-label-info text-info">
                                        <i class="ti ti-mail"></i>
                                    </span>
                                    @if (!empty($emp->email))
                                        <a href="mailto:{{ $emp->email }}" class="text-body text-truncate text-hover-primary" title="{{ $emp->email }}">
                                            {{ $emp->email }}
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </div>
                            </div>

                            @if (isset($modules['currentGuard']) && $modules['currentGuard'] === 'admin_software')
                                <div class="pt-2 mt-2 border-top border-dashed d-flex align-items-center justify-content-between" style="border-top-style: dashed !important; border-color: rgba(75, 70, 92, 0.12) !important;">
                                    <div class="text-truncate me-2" style="font-size: 0.72rem; line-height: 1.35;">
                                        <div class="text-truncate text-muted">
                                            <span>User:</span> <strong class="text-dark">{{ $username }}</strong>
                                        </div>
                                        <div class="text-truncate text-muted">
                                            <span>Pass:</span> <strong class="text-dark">{{ $userSp }}</strong>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-xs btn-outline-primary copy-login-details px-2 py-0.5 flex-shrink-0" 
                                            data-app-key="{{ $appKey }}" data-username="{{ $username }}" data-password="{{ $userSp }}" title="Copy Login Details">
                                        <i class="ti ti-copy font-size-xs"></i>
                                    </button>
                                </div>
                            @endif
                        </div>

                        {{-- Footer Actions: Same 4 buttons as standard Table view --}}
                        <div class="d-flex align-items-center justify-content-center pt-2 border-top gap-2">
                            <a href="{{ route($modules['route'] . '.show', [$emp->id]) }}" class="btn btn-sm btn-info btn-icon" title="View Profile">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            @if (!$isDeleted)
                                <a href="javascript:void(0)" data-id="{{ $emp->id }}" data-resign-date="{{ $emp->resign_date ?? '' }}"
                                   class="btn btn-sm btn-warning btn-icon resign-date-btn" data-bs-toggle="modal" data-bs-target="#resignDateModal" title="Resign Date">
                                    <i class="fa-solid fa-calendar-times"></i>
                                </a>
                                @if (isset($modules['update_permission']) && $modules['update_permission'])
                                    <a href="{{ route($modules['route'] . '.edit', [$emp->id]) }}" class="btn btn-sm btn-light btn-icon" title="Edit Employee">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                @endif
                                @if (isset($modules['delete_permission']) && $modules['delete_permission'])
                                    <a href="javascript:void(0)" data-id="{{ $emp->id }}" data-did="{{ route($modules['route'] . '.destroy', [$emp->id]) }}" 
                                       class="btn btn-sm btn-danger btn-icon deletebutton" title="Delete Employee">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                @endif
                            @else
                                <a href="javascript:void(0)" data-restore="{{ route($modules['route'] . '.restore', ['id' => $emp->id]) }}"
                                   class="btn btn-sm btn-light record-restore" title="Restore Employee">
                                    <i class="ti ti-history"></i> Restore
                                </a>
                            @endif
                        </div>

                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="card border-0 shadow-sm p-5 text-center my-4">
        <div class="mb-3">
            <span class="avatar avatar-xl bg-label-secondary rounded-circle p-3 d-inline-flex">
                <i class="ti ti-user-x text-secondary" style="font-size: 2.5rem;"></i>
            </span>
        </div>
        <h5 class="fw-bold mb-1">No Employees Found</h5>
        <p class="text-muted mb-3">No employee records match the selected filter criteria.</p>
        <div>
            <button type="button" id="grid_clear_filter" class="btn btn-primary btn-sm">
                <i class="ti ti-refresh me-1"></i> Clear Filters
            </button>
        </div>
    </div>
@endif
