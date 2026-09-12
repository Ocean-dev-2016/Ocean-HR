<!-- Menu -->
@php
    $sidebar_active = Route::current()->getName();
    $sidebar_active_full_name = Route::currentRouteName();
    $expand = explode('.', $sidebar_active);
    $sidebar_active = count($expand) > 0 ? $expand[0] : '';

    $module_menu = [];
    if (isset($modules) && count($modules) > 0) {
        // isset($module_menu['company_id'])
        $module_menu = \App\Helpers\Helper::getModuleMenu('active', ['platform' => 'panel']);
    } elseif (Auth::guard('admin_software')->check()) {
        $module_menu = \App\Helpers\Helper::getModuleMenu('all', ['platform' => 'panel']);
    }
    // dd("L-24", $sidebar_active, $module_menu->toArray());

    $dashboard_main_menu = ['software', 'roles', 'permissions'];
    $company_id = null;
    $sidebarMainLogo = asset('software/img/logo.png');
    $sidebarSmallLogo = asset('software/img/ring.png');

    if (isset($modules) && isset($modules['company_id'])) {
        $company_id = $modules['company_id'];
        $company = app\Helpers\Helper::getCompanyDetailById($company_id);
        if ($company && $company?->id) {
            $sidebarMainLogo = $company?->company_logo_url ?? $sidebarMainLogo;
            $sidebarSmallLogo = $company?->company_favicon_url ?? $sidebarSmallLogo;
        }
    }
@endphp
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{ route('software.dashboard') }}" class="app-brand-link">

            <img src="{{ $sidebarMainLogo }}" class="main-logo" />
            <img src="{{ $sidebarSmallLogo }}" class="small-logo" />
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="ti menu-toggle-icon d-none d-xl-block ti-sm align-middle"></i>
            <i class="ti ti-x d-block d-xl-none ti-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner mt-1 mb-5">
        @if (Auth::guard('admin_software')->check() || Auth::guard('employees')->check())
            @foreach ($module_menu as $row_menu)
                <li class="menu-item {{ in_array($sidebar_active, $row_menu->main_menu_routes) ? 'active open' : '' }}">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon {{ $row_menu->menu_icon }}"></i>
                        <div data-i18n="{{ $row_menu->name }}">{{ $row_menu->name }}</div>
                    </a>
                    <ul class="menu-sub">
                        @foreach ($row_menu->sub_menu as $sub_menu)
                            @php
                                // $active = ($sub_menu->route_name == $sidebar_active_full_name) ? 'active' : '';

                                $active = '';
                                if ($sub_menu->route_name) {
                                    $getRouteName = explode('.', $sub_menu->route_name);
                                    $firstPart = $getRouteName[0];
                                    $active = request()->routeIs($firstPart . '.*') ? 'active' : '';
                                }
                                $normalizedSubMenuName = strtolower(preg_replace('/\s+/', ' ', trim($sub_menu->name ?? '')));

                                $isEmployeeDocumentsMenu =
                                    str_contains($normalizedSubMenuName, 'employee') &&
                                    str_contains($normalizedSubMenuName, 'document');

                                if (!$active && $isEmployeeDocumentsMenu) {
                                    $active = request()->routeIs('employee-documents.*') ? 'active' : '';
                                }
                                $menuLink =
                                    !empty($sub_menu->route_name) && Route::has($sub_menu->route_name)
                                        ? route($sub_menu->route_name)
                                        : null;

                                if (!$menuLink && $sub_menu?->url) {
                                    $menuLink = URL($sub_menu?->url);
                                }

                                if (!$menuLink && $isEmployeeDocumentsMenu) {
                                    $menuLink = Route::has('employee-documents.index')
                                        ? route('employee-documents.index')
                                        : url('/software/employee-documents');
                                }
                            @endphp

                            <li class="menu-item {{ $active }}">
                                {{-- !empty($sub_menu->route_name) ? route($sub_menu->route_name) : 'javascript:void(0);' --}}
                                <a href="{{ $menuLink }}" class="menu-link">
                                    <div>{{ $sub_menu->name }}</div>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </li>
            @endforeach
        @endif

        {{-- <li class="menu-item">
            <div class="divider">
                <div class="divider-text">Static Menu</div>
            </div>
        </li>
        <li class="menu-item {{ $sidebar_active == '#' ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon icon-base ti tabler-mail"></i>
                <div data-i18n="Static Menu">Static Menu</div>
            </a>
            <ul class="menu-sub">
            </ul>
        </li> --}}

        @if (Auth::guard('admin_software')->check())
            <li class="menu-item">
                <div class="divider">
                    <div class="divider-text">Only Office</div>
                </div>
            </li>
            {{-- Admin  Setting  - Start --}}
            <li
                class="menu-item {{ $sidebar_active == 'plan-master' || $sidebar_active == 'company' || $sidebar_active == 'company-registration' ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon icon-base ti ti-settings-star"></i>
                    {{-- <i class="ti ti-settings-spark"></i> --}}
                    <div data-i18n="Company Setting">Company Setting </div>
                    {{-- <div class="badge bg-primary rounded-pill ms-auto">5</div> --}}
                </a>
                <ul class="menu-sub">
                    <li class="menu-item {{ $sidebar_active == 'plan-master' ? 'active' : '' }}">
                        <a href="{{ route('plan-master.index') }}" class="menu-link">
                            <div data-i18n="Plan Master">Plan Master</div>
                        </a>
                    </li>
                    <li class="menu-item {{ $sidebar_active == 'company' ? 'active' : '' }}">
                        <a href="{{ route('company.index') }}" class="menu-link">
                            <div data-i18n="Register Company">Register Company</div>
                        </a>
                    </li>
                    <li class="menu-item {{ $sidebar_active == 'company-registration' ? 'active' : '' }}">
                        <a href="{{ route('software.company-registration.index') }}" class="menu-link">
                            <div data-i18n="Company Registration">Company Registration</div>
                        </a>
                    </li>
                    <li class="menu-item {{ $sidebar_active == 'team-role' ? 'active' : '' }}">
                        <a href="{{ route('team-role.index') }}" class="menu-link">
                            <div data-i18n="Team Role">Team Role</div>
                        </a>
                    </li>
                </ul>
            </li>
            {{-- Admin  Setting  - End --}}

            {{-- Manage Email --}}
            <li class="menu-item {{ $sidebar_active == '#' ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    {{-- <i class="menu-icon icon-base fa fa-envelope"></i> --}}
                    <i class="menu-icon icon-base ti ti-settings"></i>
                    <div data-i18n="Settings">Settings</div>
                </a>
                <ul class="menu-sub">
                    <li class="menu-item {{ $sidebar_active == 'manage-email' ? 'active' : '' }}">
                        <a href="{{ route('manage-email.index') }}" class="menu-link">
                            <div data-i18n="Manage Email">Manage Email</div>
                        </a>
                    </li>
                    <li class="menu-item {{ $sidebar_active == 'user' ? 'active' : '' }}">
                        <a href="{{ route('user.index') }}" class="menu-link">
                            <div data-i18n="Admin User">Admin User </div>
                        </a>
                    </li>
                    <li class="menu-item {{ $sidebar_active == 'application-version' ? 'active' : '' }}">
                        <a href="{{ route('application-version.index') }}" class="menu-link">
                            <div data-i18n="Application Version">Application Version</div>
                        </a>
                    </li>
                </ul>
            </li>
        @endif
    </ul>
</aside>
