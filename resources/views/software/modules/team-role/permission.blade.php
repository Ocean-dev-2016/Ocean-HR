@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : null;
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $company_id = isset($modules['company_id']) ? $modules['company_id'] : null;
    $authLoginUserDetail = isset($modules['authLoginUserDetail']) ? $modules['authLoginUserDetail'] : null;
    $loginUserId = isset($authLoginUserDetail?->id) ? $authLoginUserDetail?->id : null;
    $parent_type_id = isset($modules['parent_type_id']) ? $modules['parent_type_id'] : null;

    $permissions = ['view', 'add', 'update', 'delete'];
    if (count(config('constants.permissions'))) {
        $permissions = config('constants.permissions');
    }
@endphp

@section('title', $page_title . ' - Assign Permissions')

@section('content')
<style>
    .permission-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 24px 0 rgba(34, 41, 47, 0.08);
        overflow: hidden;
    }

    .role-info-banner {
        background: linear-gradient(135deg, #f8f9fa 0%, #eef2f7 100%);
        border-radius: 10px;
        padding: 16px 20px;
        border: 1px solid #e2e8f0;
    }

    .info-pill {
        display: inline-flex;
        align-items: center;
        background: #ffffff;
        padding: 6px 14px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        font-size: 0.875rem;
    }

    .info-pill i {
        font-size: 1.15rem;
        margin-right: 6px;
    }

    .perm-table-container {
        border: 1px solid #e9ecef;
        border-radius: 10px;
        overflow: hidden;
        background: #ffffff;
        max-height: 70vh;
        overflow-y: auto;
        position: relative;
    }

    .perm-table {
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
    }

    .perm-table thead th {
        position: sticky;
        top: 0;
        z-index: 10;
        background: #f1f5f9 !important;
        color: #334155;
        font-size: 0.76rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        padding: 12px 10px;
        border-bottom: 2px solid #cbd5e1 !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.03);
        white-space: nowrap;
        vertical-align: middle;
    }

    .perm-table thead th .header-cell-wrap {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
    }

    .module-header-row td {
        background: #eef2ff !important;
        border-top: 1px solid #c7d2fe !important;
        border-bottom: 1px solid #c7d2fe !important;
        border-left: 4px solid #6366f1 !important;
        padding: 10px 18px !important;
    }

    .module-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #312e81;
        letter-spacing: 0.3px;
    }

    .perm-row {
        transition: background-color 0.15s ease-in-out;
    }

    .perm-row:hover {
        background-color: #f8fafc !important;
    }

    .perm-row td {
        padding: 9px 10px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
    }

    .perm-row td:first-child {
        border-left: 3px solid transparent;
        transition: border-color 0.15s ease;
    }

    .perm-row:hover td:first-child {
        border-left-color: #6366f1;
    }

    .submenu-title-wrap {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .submenu-title-wrap .sub-icon {
        color: #94a3b8;
        font-size: 0.9rem;
    }

    .submenu-title {
        font-size: 0.88rem;
        font-weight: 500;
        color: #1e293b;
    }

    /* Custom Checkbox Enhancements */
    .perm-checkbox, .col-check-all, .check-all, .check-all-master {
        width: 18px !important;
        height: 18px !important;
        cursor: pointer;
        border-radius: 4px !important;
        border: 1.5px solid #cbd5e1 !important;
        transition: all 0.15s ease-in-out;
    }

    .perm-checkbox:checked, .col-check-all:checked, .check-all:checked, .check-all-master:checked {
        background-color: #6366f1 !important;
        border-color: #6366f1 !important;
        box-shadow: 0 2px 4px rgba(99, 102, 241, 0.25);
    }

    .check-all:checked {
        background-color: #0ea5e9 !important;
        border-color: #0ea5e9 !important;
    }

    .col-check-all:checked, .check-all-master:checked {
        background-color: #4f46e5 !important;
        border-color: #4f46e5 !important;
    }

    /* Action bar footer */
    .sticky-action-bar {
        position: sticky;
        bottom: 0;
        z-index: 20;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(8px);
        border-top: 1px solid #e2e8f0;
        padding: 14px 24px;
        box-shadow: 0 -4px 16px rgba(0, 0, 0, 0.05);
        border-radius: 0 0 12px 12px;
    }

    .table-toolbar {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px 16px;
        margin-bottom: 14px;
    }

    .btn-select-module {
        font-size: 0.78rem;
        padding: 3px 10px;
        border-radius: 6px;
        font-weight: 600;
    }
</style>

<div class="px-1">
    <!-- Breadcrumb & Top Bar -->
    <div class="d-flex flex-column flex-lg-row justify-content-lg-between align-items-start align-items-lg-center mb-3">
        @include('software.inlcudes.breadcrumb', [
            'breadcrumbArray' => [
                ['title' => $page_title, 'url' => route($route . '.index')],
                ['title' => (isset($edit) && $edit?->id) ? 'Edit Permissions' : 'Assign Permission', 'url' => ''],
            ],
        ])
        <a class="btn btn-outline-secondary waves-effect shadow-sm" href="{{ route($route . '.index') }}">
            <i class="ti ti-arrow-left me-1"></i> Back to List
        </a>
    </div>

    <div class="card permission-card mb-4">
        <div class="card-body p-4">

            <!-- Role Details Banner -->
            <div class="role-info-banner mb-4">
                <div class="row g-3 align-items-center">
                    <div class="col-md-4">
                        <div class="info-pill w-100">
                            <i class="ti ti-building text-primary"></i>
                            <div>
                                <small class="text-muted d-block" style="font-size: 0.72rem; line-height: 1;">COMPANY</small>
                                <span class="fw-bold text-dark">{{ $team_role?->company?->company_name ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-pill w-100">
                            <i class="ti ti-git-branch text-info"></i>
                            <div>
                                <small class="text-muted d-block" style="font-size: 0.72rem; line-height: 1;">PARENT ROLE</small>
                                <span class="fw-bold text-dark">{{ $team_role?->parent_name ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-pill w-100">
                            <i class="ti ti-shield-check text-success"></i>
                            <div>
                                <small class="text-muted d-block" style="font-size: 0.72rem; line-height: 1;">TEAM ROLE</small>
                                <span class="fw-bold text-dark">{{ $team_role?->name ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Controls Toolbar -->
            <div class="table-toolbar d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <div class="input-group input-group-merge" style="max-width: 380px;">
                    <span class="input-group-text"><i class="ti ti-search text-muted"></i></span>
                    <input type="text" id="permissionSearch" class="form-control" placeholder="Search module or submenu..." autocomplete="off">
                    <button class="btn btn-outline-secondary btn-sm" type="button" id="clearSearchBtn" title="Clear search" style="display: none;">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <button type="button" class="btn btn-outline-primary btn-sm waves-effect" id="btnSelectAllGlobal">
                        <i class="ti ti-checks me-1"></i> Select All
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm waves-effect" id="btnClearAllGlobal">
                        <i class="ti ti-rotate-clockwise me-1"></i> Deselect All
                    </button>
                </div>
            </div>

            <form action="{{ route($route . '.store-permission') }}" method="POST" id="forminfo">
                @csrf
                @isset($edit)
                    @method('PUT')
                @endisset

                <input type="hidden" name="id" value="{{ $edit->id ?? '' }}">
                <input type="hidden" id="team_role_id" name="team_role_id" value="{{ $team_role->id ?? '' }}">
                <input type="hidden" id="company_id" name="company_id" value="{{ $team_role->company_id ?? '' }}">

                <!-- Permissions Matrix Table -->
                <div class="perm-table-container mb-3">
                    <table class="table perm-table align-middle" id="submenuTable">
                        <thead>
                            <tr>
                                <th scope="col" style="min-width: 230px;" class="ps-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="ti ti-list-details text-primary fs-5"></i>
                                        <span>Submenu / Feature</span>
                                    </div>
                                </th>
                                @if (isset($permissions) && count($permissions))
                                    @foreach ($permissions as $key => $value)
                                        <th class="text-center" style="min-width: 82px;">
                                            <div class="header-cell-wrap">
                                                <span>{{ str_replace('_', ' ', $value) }}</span>
                                                <input type="checkbox"
                                                    class="form-check-input col-check-all"
                                                    data-col="{{ $key }}"
                                                    title="Check all {{ str_replace('_', ' ', $value) }}">
                                            </div>
                                        </th>
                                    @endforeach
                                    <th class="text-center" style="min-width: 86px;">
                                        <div class="header-cell-wrap">
                                            <span>Row All</span>
                                            <input type="checkbox"
                                                class="form-check-input check-all-master"
                                                title="Toggle All Rows">
                                        </div>
                                    </th>
                                @else
                                    <th class="text-center">View</th>
                                    <th class="text-center">Add</th>
                                    <th class="text-center">Update</th>
                                    <th class="text-center">Delete</th>
                                    <th class="text-center">Row All</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @if (isset($panel_sub_modules))
                                @foreach ($panel_sub_modules as $item)
                                    @if ($item && $item?->sub_menus && count($item?->sub_menus))
                                        <!-- Module Header Row -->
                                        <tr class="module-header-row" data-module-id="{{ $item?->id }}">
                                            <td colspan="{{ count($permissions) + 2 }}">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <i class="ti ti-folders text-primary fs-5"></i>
                                                        <span class="module-title">{{ $item?->name ?? 'Main Menu' }}</span>
                                                        <span class="badge bg-white text-primary border rounded-pill px-2 py-1 ms-1" style="font-size: 0.72rem;">
                                                            {{ count($item?->sub_menus) }} Submenus
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <button type="button" class="btn btn-sm btn-outline-primary btn-select-module" data-module-id="{{ $item?->id }}">
                                                            <i class="ti ti-checks me-1"></i> Select Module
                                                        </button>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Submenu Rows -->
                                        @foreach ($item?->sub_menus as $item_sub_menu)
                                            @php
                                                $item_sub_menu = (object) $item_sub_menu;
                                                $checked_all = count($permissions);
                                            @endphp
                                            <tr class="perm-row" data-module-id="{{ $item?->id }}">
                                                <td class="ps-3"
                                                    data-company_id="{{ $team_role?->company_id ?? '-' }}"
                                                    data-team_role_id="{{ $team_role?->id ?? '' }}"
                                                    data-main_menu_id="{{ $item_sub_menu?->main_menu_id }}"
                                                    data-id="{{ $item_sub_menu?->id }}">
                                                    <div class="submenu-title-wrap">
                                                        <i class="ti ti-corner-down-right sub-icon"></i>
                                                        <span class="submenu-title">{{ $item_sub_menu?->name ?? 'Sub Menu' }}</span>
                                                    </div>
                                                </td>

                                                @foreach ($permissions as $permission_key => $permission_value)
                                                    @php
                                                        $tr_uuid = [];
                                                        $tr_uuid[] = $team_role?->company_id;
                                                        $tr_uuid[] = $team_role?->id;
                                                        $tr_uuid[] = $item_sub_menu?->main_menu_id;
                                                        $tr_uuid[] = $item_sub_menu?->id;
                                                        $tr_uuid = implode('-', $tr_uuid);

                                                        $tr_permission_check = null;
                                                        $permission_checked = false;
                                                        if (isset($assignedPermission[$tr_uuid])) {
                                                            $tr_permission_check = $assignedPermission[$tr_uuid];
                                                            if ($tr_permission_check && $tr_permission_check?->id) {
                                                                $tr_array = $tr_permission_check->toArray();
                                                                $flag_key = $permission_key . '_flag';
                                                                if (isset($tr_array[$flag_key]) && $tr_array[$flag_key]) {
                                                                    $checked_all--;
                                                                    $permission_checked = true;
                                                                }
                                                            }
                                                        }
                                                    @endphp
                                                    <td class="text-center align-middle">
                                                        <input type="hidden"
                                                            name="permissions[{{ $item_sub_menu?->id }}][uuid]"
                                                            value="{{ $tr_uuid }}">
                                                        <input type="hidden"
                                                            name="permissions[{{ $item_sub_menu?->id }}][{{ $permission_key }}]"
                                                            value="0">
                                                        <div class="d-flex justify-content-center align-items-center">
                                                            <input type="checkbox"
                                                                id="{{ $tr_uuid . '_' . $permission_key }}"
                                                                class="form-check-input perm-checkbox perm-col-{{ $permission_key }} {{ !in_array($permission_key, ['personal_data']) ? 'permission-checkbox' : '' }}"
                                                                name="permissions[{{ $item_sub_menu?->id }}][{{ $permission_key }}]"
                                                                data-name="{{ $permission_value }}"
                                                                data-col="{{ $permission_key }}"
                                                                data-module-id="{{ $item?->id }}"
                                                                value="1"
                                                                title="{{ $item_sub_menu?->name }} : {{ str_replace('_', ' ', $permission_key) }}"
                                                                {{ $permission_checked ? 'checked' : '' }} />
                                                        </div>
                                                    </td>
                                                @endforeach

                                                <td class="text-center align-middle">
                                                    <div class="d-flex justify-content-center align-items-center">
                                                        <input type="checkbox"
                                                            class="form-check-input check-all"
                                                            data-module-id="{{ $item?->id }}"
                                                            title="Toggle all for {{ $item_sub_menu?->name }}"
                                                            {{ $checked_all == 0 ? 'checked' : '' }}>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>

                <!-- Sticky Action Footer -->
                <div class="sticky-action-bar d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
                    <div class="text-muted d-flex align-items-center">
                        <i class="ti ti-info-circle me-1 text-primary"></i>
                        <span>Changes will update user access rights immediately.</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route($route . '.index') }}" class="btn btn-outline-secondary waves-effect">
                            <i class="ti ti-x me-1"></i> Cancel
                        </a>
                        <button type="submit" value="submit" class="btn btn-success waves-effect waves-light shadow-sm">
                            <i class="ti ti-device-floppy me-1"></i> {{ isset($edit) ? 'Update' : 'Save Changes' }}
                        </button>
                        <button type="submit" name="btn_submit" value="submit_and_exit" class="btn btn-primary waves-effect waves-light shadow-sm">
                            <i class="ti ti-check me-1"></i> Save & Exit
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection

@push('page_scripts')
<script>
    $(document).ready(function() {
        // Update active permissions count
        function updateStats() {
            var active = $('.perm-checkbox:checked').length;
            $('#activeCountBadge').text(active);
        }
        updateStats();

        // Row Check-All change handler
        $(document).on('change', '.check-all', function() {
            var isChecked = $(this).prop('checked');
            var row = $(this).closest('tr');
            row.find('.permission-checkbox').prop('checked', isChecked);
            updateStats();
        });

        // Individual permission checkbox change handler: sync row check-all
        $(document).on('change', '.perm-checkbox', function() {
            var row = $(this).closest('tr');
            var totalInRow = row.find('.permission-checkbox').length;
            var checkedInRow = row.find('.permission-checkbox:checked').length;
            row.find('.check-all').prop('checked', totalInRow === checkedInRow);
            updateStats();
        });

        // Column Check-All handler
        $(document).on('change', '.col-check-all', function() {
            var col = $(this).data('col');
            var isChecked = $(this).prop('checked');
            $('.perm-col-' + col + ':visible').prop('checked', isChecked);

            // Re-sync each visible row's check-all
            $('.perm-row:visible').each(function() {
                var total = $(this).find('.permission-checkbox').length;
                var checked = $(this).find('.permission-checkbox:checked').length;
                $(this).find('.check-all').prop('checked', total === checked);
            });

            updateStats();
        });

        // Global master toggle (in header row)
        $(document).on('change', '.check-all-master', function() {
            var isChecked = $(this).prop('checked');
            $('.permission-checkbox:visible').prop('checked', isChecked);
            $('.check-all:visible').prop('checked', isChecked);
            $('.col-check-all').prop('checked', isChecked);
            updateStats();
        });

        // Select Module button handler
        $(document).on('click', '.btn-select-module', function() {
            var moduleId = $(this).data('module-id');
            var rows = $('.perm-row[data-module-id="' + moduleId + '"]');
            var checkboxes = rows.find('.permission-checkbox');
            var anyUnchecked = checkboxes.filter(':not(:checked)').length > 0;

            checkboxes.prop('checked', anyUnchecked);
            rows.find('.check-all').prop('checked', anyUnchecked);

            if (anyUnchecked) {
                $(this).html('<i class="ti ti-x me-1"></i> Deselect Module').removeClass('btn-outline-primary').addClass('btn-outline-warning');
            } else {
                $(this).html('<i class="ti ti-checks me-1"></i> Select Module').removeClass('btn-outline-warning').addClass('btn-outline-primary');
            }

            updateStats();
        });

        // Toolbar: Select All button
        $('#btnSelectAllGlobal').on('click', function() {
            $('.permission-checkbox:visible').prop('checked', true);
            $('.check-all:visible').prop('checked', true);
            $('.col-check-all').prop('checked', true);
            $('.check-all-master').prop('checked', true);
            updateStats();
        });

        // Toolbar: Deselect All button
        $('#btnClearAllGlobal').on('click', function() {
            $('.permission-checkbox:visible').prop('checked', false);
            $('.check-all:visible').prop('checked', false);
            $('.col-check-all').prop('checked', false);
            $('.check-all-master').prop('checked', false);
            updateStats();
        });

        // Live Search filter
        $('#permissionSearch').on('input', function() {
            var term = $(this).val().toLowerCase().trim();

            if (term.length > 0) {
                $('#clearSearchBtn').show();
            } else {
                $('#clearSearchBtn').hide();
            }

            $('.module-header-row').each(function() {
                var moduleId = $(this).data('module-id');
                var moduleName = $(this).find('.module-title').text().toLowerCase();
                var rows = $('.perm-row[data-module-id="' + moduleId + '"]');

                if (moduleName.indexOf(term) > -1) {
                    $(this).show();
                    rows.show();
                } else {
                    var visibleRows = 0;
                    rows.each(function() {
                        var subName = $(this).find('.submenu-title').text().toLowerCase();
                        if (subName.indexOf(term) > -1) {
                            $(this).show();
                            visibleRows++;
                        } else {
                            $(this).hide();
                        }
                    });

                    if (visibleRows > 0) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                }
            });
        });

        // Clear search button
        $('#clearSearchBtn').on('click', function() {
            $('#permissionSearch').val('').trigger('input');
        });
    });
</script>
@endpush
