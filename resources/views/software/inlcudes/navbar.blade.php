@php
    $currentUserProfile = \App\Helpers\Helper::getLoginUser('admin_software');
    $userName = 'User Name';
    $profileIcon = asset('software/img/default/profile.png');
    $roleName = 'Admin';
    $isAdminSoftware = false;
    $allCompanies = [];
    $selectedCompanyId = session('selected_company_id');
    $selectedCompanyName = 'Select Company';
    
    if ($currentUserProfile?->profile) {
        $profileIcon = null;
    }
    $userName = $currentUserProfile?->name;
    $u_name = $currentUserProfile?->username;
    $parent_type_id = '';
    $company_id = '';
    
    // Check if admin_software user
    if ($currentUserProfile && Auth::guard('admin_software')->check()) {
        $isAdminSoftware = true;
        // Get all active companies for dropdown
        $allCompanies = \App\Models\Company::where('status', 'active')
            ->orderBy('company_name', 'asc')
            ->get(['id', 'company_name']);
        
        // Get selected company name
        if ($selectedCompanyId) {
            $selectedCompany = $allCompanies->firstWhere('id', $selectedCompanyId);
            $selectedCompanyName = $selectedCompany?->company_name ?? 'Select Company';
        }
    }
    
    // dd($currentUserProfile, Auth::guard('employees')->user());
    if (!$currentUserProfile && Auth::guard('employees')->check()) {
        $currentUserProfile = Auth::guard('employees')->user();
        $userName = $currentUserProfile->full_name ?? 'Team Person';
        $parent_type_id = $currentUserProfile->parent_type_id ?? '';
        $company_id = $currentUserProfile->company_id ?? '';
        $roleName = $currentUserProfile->team_role->name ?? 'Team Member';
        $u_name = $currentUserProfile->username ?? 'Team Member';
        $profileIcon = asset('software/img/default/profile.png');
    } else {
        $userName = $currentUserProfile?->name ?? 'User Name';
        if ($currentUserProfile && method_exists($currentUserProfile, 'team_role')) {
            $roleName = $currentUserProfile->team_role->name ?? 'Admin';
        }
    }
@endphp


<nav class="layout-navbar container-fuild navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
    id="layout-navbar">
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
            <i class="ti ti-menu-2 ti-sm"></i>
        </a>
    </div>

    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
        <!-- Search -->
        <div class="navbar-nav align-items-center">
            <div class="nav-item navbar-search-wrapper mb-0">
                <div class="d-flex align-items-center">
                    <i class="ti ti-search ti-md me-2 text-muted"></i>
                    <input type="text" class="form-control border-0 shadow-none bg-transparent ps-0" id="sidebar-menu-search" placeholder="Search (Ctrl+/)" style="width: 260px;" autocomplete="off">
                    <span id="clear-menu-search" class="d-none text-muted" style="cursor: pointer;" title="Clear"><i class="ti ti-x ti-xs"></i></span>
                </div>
            </div>
        </div>
        <!-- /Search -->


        <ul class="navbar-nav flex-row align-items-center ms-auto">

            {{-- Company Selection Dropdown - Only for admin_software users --}}
            @if ($isAdminSoftware && count($allCompanies) > 0)
                <li class="nav-item dropdown me-3">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="javascript:;" 
                        id="companyDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ti ti-building ti-md me-1"></i>
                        <span class="d-none d-md-inline-block company-selected-name" style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            {{ $selectedCompanyName }}
                        </span>
                        @if (!$selectedCompanyId)
                            <span class="badge bg-warning ms-1">!</span>
                        @endif
                    </a>
                    <ul class="dropdown-menu dropdown-menu-start shadow-sm" id="company-dropdown-menu"
                        style="min-width: 280px; max-height: 400px; overflow-y: auto;">
                        <li class="dropdown-header px-3 py-2 border-bottom">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="ti ti-search"></i></span>
                                <input type="text" class="form-control" id="company-search" placeholder="Search company...">
                            </div>
                        </li>
                        <li>
                            <a href="javascript:void(0);" class="dropdown-item company-select-item {{ !$selectedCompanyId ? 'active' : '' }}" 
                                data-company-id="" data-company-name="All Companies">
                                <i class="ti ti-building-community me-2"></i> All Companies
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        @foreach ($allCompanies as $comp)
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item company-select-item {{ $selectedCompanyId == $comp->id ? 'active' : '' }}" 
                                    data-company-id="{{ $comp->id }}" data-company-name="{{ $comp->company_name }}">
                                    <i class="ti ti-building me-2"></i> {{ $comp->company_name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </li>
            @endif

            @if (isset($punchType) && !empty($punchType) && ($punchType == 'in' || $punchType == 'out'))
                @php $punchText = $punchType == 'in' ? 'Punch In' : 'Punch Out'; @endphp

                <span class="punch_in_time_dis">
                    @if (isset($punchInTime) && !empty($punchInTime))
                        Punch In Time : {{ $punchInTime }}
                    @endif
                </span>&nbsp;&nbsp;
            @endif
            <!-- User -->

            <li class="nav-item dropdown me-3 position-relative">
                <a class="nav-link position-relative" href="javascript:;" id="notif-bell" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <i class="ti ti-bell ti-md"></i>
                    <span id="notif-count"
                        class="badge bg-danger rounded-pill position-absolute start-100 translate-middle p-1"
                        style="top: 12px !important;left: 32px !important"></span>
                </a>

                <ul class="dropdown-menu dropdown-menu-end shadow-sm p-0" id="notif-dropdown"
                    style="width: 360px; max-height: 500px; overflow-y: auto;" data-bs-popper="static">
                    <li
                        class="dropdown-header d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                        <h6 class="mb-0 fw-bold">Notifications</h6>
                        <button class="btn btn-sm text-success d-flex align-items-center gap-1" id="notif-mark-read"
                            style="font-size: 0.85rem;box-shadow: none;">
                            <i class="ti ti-check-double"></i> Mark all as read
                        </button>
                    </li>

                    <li>
                        <div id="notif-list" class="list-group list-group-flush">
                            {{-- Example notification item --}}
                            {{-- <a href="#" class="list-group-item list-group-item-action d-flex gap-3 align-items-start border-0">
                                <div class="bg-light rounded-circle p-2">
                                    <i class="ti ti-home fs-5 text-secondary"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <h6 class="mb-1 fw-semibold text-dark">Maintenance request update</h6>
                                        <small class="text-muted">5h ago</small>
                                    </div>
                                    <div class="text-muted small">
                                        The request for <strong>John Doe</strong> in <strong>Apartment 301</strong> was <span class="text-success fw-semibold">Completed</span>.
                                    </div>
                                </div>
                            </a> --}}

                            {{-- More items dynamically injected via JS --}}
                        </div>
                    </li>

                    <li>
                        <a href="{{ route('notification.index') }}"
                            class="dropdown-item text-center text-primary fw-medium border-top">
                            View all notifications
                        </a>
                    </li>
                </ul>

            </li>



            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                        <img src="{{ !empty($authLoginUserDetail->profile_image) ? asset($authLoginUserDetail->profile_image) : asset('software/img/default/profile.png') }}"
                            alt="profile" class="rounded-circle" />
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="{{ route('profile') }}">

                            <div class="d-flex">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar avatar-online">
                                        <img src="{{ !empty($authLoginUserDetail->profile_image) ? asset($authLoginUserDetail->profile_image) : asset('software/img/default/profile.png') }}"
                            alt="profile" class="rounded-circle" />
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <span class="fw-medium d-block">{{ $userName ?? '' }}</span>
                                    <small class="text-muted">{{ $roleName }}</small>

                                </div>

                            </div>
                        </a>
                    </li>
                    @php
                        $effectiveCompanyId = !empty($company_id) ? $company_id : ($currentUserProfile->company_id ?? ($isAdminSoftware ? $selectedCompanyId : null));
                    @endphp
                    @if (!empty($effectiveCompanyId))
                        <li>
                            <a class="dropdown-item" href="{{ route('company.detail', $effectiveCompanyId) }}?tab=profile-tab">
                                <i class="ti ti-building me-2 ti-sm"></i>
                                <span class="align-middle">Company Details</span>
                            </a>
                        </li>
                    @endif
                    {{-- <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li>
                        <a class="dropdown-item" href="pages-profile-user.html">
                            <i class="ti ti-user-check me-2 ti-sm"></i>
                            <span class="align-middle">My Profile</span>
                        </a>
                    </li> --}}
                    {{-- <li>
                        <a class="dropdown-item" href="pages-account-settings-account.html">
                            <i class="ti ti-settings me-2 ti-sm"></i>
                            <span class="align-middle">Settings</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="pages-account-settings-billing.html">
                            <span class="d-flex align-items-center align-middle">
                                <i class="flex-shrink-0 ti ti-credit-card me-2 ti-sm"></i>
                                <span class="flex-grow-1 align-middle">Billing</span>
                                <span
                                    class="flex-shrink-0 badge badge-center rounded-pill bg-label-danger w-px-20 h-px-20">2</span>
                            </span>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li>
                        <a class="dropdown-item" href="pages-faq.html">
                            <i class="ti ti-help me-2 ti-sm"></i>
                            <span class="align-middle">FAQ</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="pages-pricing.html">
                            <i class="ti ti-currency-dollar me-2 ti-sm"></i>
                            <span class="align-middle">Pricing</span>
                        </a>
                    </li> --}}
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li>
                        <a class="dropdown-item logout-btn" href="{{ route('software.logout') }}"
                            onclick="event.preventDefault();
                                document.getElementById('logout-form').submit();">
                            <i class="ti ti-logout me-2 ti-sm"></i>
                            <span class="align-middle">Log Out</span>
                            <form id="logout-form" action="{{ route('software.logout') }}" method="POST"
                                class="d-none">
                                @csrf
                            </form>
                        </a>
                    </li>
                </ul>
            </li>
            <!--/ User -->
        </ul>
    </div>

    {{-- <!-- Search Small Screens -->
    <div class="navbar-search-wrapper search-input-wrapper d-none">
        <input type="text" class="form-control search-input container-fuild border-0" placeholder="Search..."
            aria-label="Search..." />
        <i class="ti ti-x ti-sm search-toggler cursor-pointer"></i>
    </div> --}}
</nav>

@if (isset($is_plan_expired) && $is_plan_expired == true && isset($mainPlanExpiryDate) && !empty($mainPlanExpiryDate))
    {{-- <div id="plan-expiry-banner" class="py-0 mt-2 d-flex justify-content-between align-items-center"
        style="clear: both;">
        <div class="scrolling-text">
            <h5 class="mt-3">Your {{ $mainPlanName ?? '' }} expires on
                {{ \Carbon\Carbon::parse($mainPlanExpiryDate)->format('d-m-Y') }}.
        </div>
    </div> --}}
@endif

{{-- Company Selection JavaScript - Only for admin_software --}}
@if ($isAdminSoftware && count($allCompanies) > 0)
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Company search filter
        const companySearch = document.getElementById('company-search');
        if (companySearch) {
            companySearch.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const items = document.querySelectorAll('.company-select-item');
                
                items.forEach(function(item) {
                    const companyName = item.getAttribute('data-company-name').toLowerCase();
                    if (companyName.includes(searchTerm) || searchTerm === '') {
                        item.parentElement.style.display = '';
                    } else {
                        item.parentElement.style.display = 'none';
                    }
                });
            });

            // Prevent dropdown from closing when clicking on search
            companySearch.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }

        // Company selection
        document.querySelectorAll('.company-select-item').forEach(function(item) {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                
                const companyId = this.getAttribute('data-company-id');
                const companyName = this.getAttribute('data-company-name');
                
                // Show loading
                document.querySelector('.company-selected-name').innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';
                
                // Send AJAX request to set company session
                fetch('{{ route("company.set_company_session") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        company_id: companyId || null
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status || data.success) {
                        // Update the dropdown text
                        document.querySelector('.company-selected-name').textContent = companyName || 'Select Company';
                        
                        // Update active state
                        document.querySelectorAll('.company-select-item').forEach(function(el) {
                            el.classList.remove('active');
                        });
                        item.classList.add('active');
                        
                        // Reload page to apply company filter
                        window.location.reload();
                    } else {
                        alert('Failed to select company. Please try again.');
                        document.querySelector('.company-selected-name').textContent = '{{ $selectedCompanyName }}';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                    document.querySelector('.company-selected-name').textContent = '{{ $selectedCompanyName }}';
                });
            });
        });
    });
</script>

<style>
    #company-dropdown-menu .dropdown-item.active {
        background-color: #667eea;
        color: white;
    }
    #company-dropdown-menu .dropdown-item:hover:not(.active) {
        background-color: #f8f9fa;
    }
    #company-dropdown-menu .dropdown-item i {
        opacity: 0.7;
    }
    #companyDropdown {
        background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%);
        border-radius: 8px;
        padding: 8px 12px;
    }
    #companyDropdown:hover {
        background: linear-gradient(135deg, #667eea25 0%, #764ba225 100%);
    }
</style>
@endif

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('sidebar-menu-search');
        const clearBtn = document.getElementById('clear-menu-search');
        const sidebarMenu = document.querySelector('#layout-menu .menu-inner');

        if (!searchInput || !sidebarMenu) return;

        function filterSidebar(query) {
            query = query.trim().toLowerCase();
            const mainMenuItems = sidebarMenu.querySelectorAll(':scope > li.menu-item');
            
            // Remove previous no result message if any
            const existingNoResult = sidebarMenu.querySelector('.menu-search-no-results');
            if (existingNoResult) existingNoResult.remove();

            if (query === '') {
                if (clearBtn) clearBtn.classList.add('d-none');
                mainMenuItems.forEach(function(item) {
                    item.style.display = '';
                    const subMenu = item.querySelector('.menu-sub');
                    if (subMenu) {
                        const subItems = subMenu.querySelectorAll('li.menu-item');
                        subItems.forEach(function(sub) {
                            sub.style.display = '';
                        });
                        if (!item.classList.contains('active')) {
                            item.classList.remove('open');
                        }
                    }
                });
                return;
            }

            if (clearBtn) clearBtn.classList.remove('d-none');
            let totalMatches = 0;

            mainMenuItems.forEach(function(item) {
                if (item.querySelector('.divider')) {
                    item.style.display = 'none';
                    return;
                }

                const toggleLink = item.querySelector(':scope > a.menu-link');
                const parentDiv = toggleLink ? toggleLink.querySelector('div') : null;
                const parentText = parentDiv ? parentDiv.textContent.toLowerCase() : (toggleLink ? toggleLink.textContent.toLowerCase() : '');
                const subMenu = item.querySelector('.menu-sub');

                if (subMenu) {
                    const subItems = subMenu.querySelectorAll('li.menu-item');
                    let childMatched = false;

                    subItems.forEach(function(sub) {
                        const childDiv = sub.querySelector('div');
                        const childText = childDiv ? childDiv.textContent.toLowerCase() : sub.textContent.toLowerCase();

                        if (childText.includes(query) || parentText.includes(query)) {
                            sub.style.display = '';
                            childMatched = true;
                            totalMatches++;
                        } else {
                            sub.style.display = 'none';
                        }
                    });

                    if (childMatched || parentText.includes(query)) {
                        item.style.display = '';
                        item.classList.add('open');
                    } else {
                        item.style.display = 'none';
                    }
                } else {
                    if (parentText.includes(query)) {
                        item.style.display = '';
                        totalMatches++;
                    } else {
                        item.style.display = 'none';
                    }
                }
            });

            if (totalMatches === 0) {
                const noResultLi = document.createElement('li');
                noResultLi.className = 'menu-item menu-search-no-results text-center py-3 px-2 text-muted';
                noResultLi.innerHTML = '<small><i class="ti ti-search-off me-1"></i> No menu found</small>';
                sidebarMenu.appendChild(noResultLi);
            }
        }

        searchInput.addEventListener('input', function() {
            filterSidebar(this.value);
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                searchInput.value = '';
                filterSidebar('');
                searchInput.focus();
            });
        }

        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === '/') {
                e.preventDefault();
                searchInput.focus();
                searchInput.select();
            } else if (e.key === 'Escape' && document.activeElement === searchInput) {
                searchInput.value = '';
                filterSidebar('');
                searchInput.blur();
            }
        });
    });
</script>
