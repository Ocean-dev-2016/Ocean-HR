<?php

namespace App\Http\Controllers\software;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Dispatch;
use App\Models\DispatchItems;
use App\Models\Company;
use App\Models\MainMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Models\SubMenu;
use App\Models\Employee;
use App\Models\AdminSoftware;
use App\Models\CompanySubscriptionPlan;
use App\Models\PlanMaster;
use App\Models\Attendance;
use App\Models\LeaveApplication;
use App\Models\Shift;
use App\Models\OperationsRateList;
use App\Models\Department;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public $modules = [];
    public function __construct()
    {
        parent::__construct();
        // You can add middleware here if needed
        $this->modules = [
            'company_id' => ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null,
            'route' => 'dashboard',
        ];
    }

    public function sidebar_menu(Request $request)
    {

        $modules = $this->modules;
        $modules['currentGuard'] = ($this->currentGuard) ? $this->currentGuard : null;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;
        $modules['parent_type_id'] = ($this->authenticateLoginUserDetails?->parent_type_id) ? $this->authenticateLoginUserDetails?->parent_type_id : null;
        $loginUserId = ($modules['authLoginUserDetail'] && $modules['authLoginUserDetail']?->id) ? $modules['authLoginUserDetail']?->id : null;

        try {
            if ($modules['currentGuard'] != "admin_software") {
                abort(403, 'Unauthorized');
            }

            if ($request?->_token) {
                // dd("L-60", $request->all());
                $validateData = Validator::make($request->all(), [
                    'platform' => ['required', 'in:panel,app'],
                    'main_menu_id' => ['nullable', Rule::exists((new MainMenu())->getTable(), 'id')],
                    'name' => ['required'],
                    'menu_icon' => ['nullable'],
                    'status' => ['required', 'in:active,inactive'],
                ]);

                if ($validateData->fails()) {
                    return Redirect::back()->withErrors($validateData)->withInput();
                }

                // Add Main Menu
                if (!$request?->main_menu_id) {
                    $validateFormData = $validateData->validated();
                    $validateFormData['order_by'] = $request?->order_by;

                    $mainMenu = new MainMenu();
                    $mainMenuQuery = MainMenu::query();
                    if ($request->edit_id) {
                        $mainMenu = $mainMenuQuery->where('id', $request->edit_id)->first();
                        $validateFormData['updated_by'] = $loginUserId;
                        // For main menu, we might not strictly enforce company_id if it's a global setting,
                        // but if MainMenu has company_id it should be filtered.
                        // Assuming MainMenu is global or admin only based on 'admin_software' check at start.
                        if (!$mainMenu) {
                            return Redirect::back()->withErrors("Main menu not found.")->withInput();
                        }
                        $mainMenu->update($validateFormData);
                    } else {
                        $validateFormData['created_by'] = $loginUserId;
                        $mainMenu->create($validateFormData);
                    }

                    /*
                    if ($request?->order_by && $request?->order_by != $mainMenu?->order_by) {
                    $orderBy = (int)$request?->order_by;
                    $updateOrders = MainMenu::where('order_by', ">=", $orderBy)->get();
                    // dd("L-89", $request->all(), $mainMenu->toArray(), $updateOrders, $orderBy);
                    foreach ($updateOrders as $key => $value) {
                    $updateOrderBy = MainMenu::where('id', $value?->id)->first();
                    if($updateOrderBy){
                    $updateOrderBy->order_by = $orderBy;
                    $updateOrderBy->save();
                    $orderBy = $orderBy+1;
                    }
                    }
                    }
                    */

                    Session::flash('success', "Main menu update successfully.");
                    return Redirect::route('sidebar.menu');
                    dd("L-94", $request->all(), $mainMenu->toArray());
                }
                // Add Sub Main Menu
                else if ($request?->main_menu_id) {
                    $validateFormData = $validateData->validated();
                    $validateFormData['main_menu_id'] = $request?->main_menu_id;
                    $validateFormData['route_name'] = $request?->route_name;
                    $validateFormData['url'] = $request?->url;
                    if ($request?->order_by && !empty($request?->order_by)) {
                        $validateFormData['order_by'] = $request?->order_by;
                    }

                    $normalizedMenuName = strtolower(preg_replace('/\s+/', ' ', trim($request?->name ?? '')));
                    if (
                        str_contains($normalizedMenuName, 'employee') &&
                        str_contains($normalizedMenuName, 'document')
                    ) {
                        $validateFormData['route_name'] = 'employee-documents.index';
                        $validateFormData['url'] = null;
                    }
                    // dd("L-127", $request->all(), $validateFormData);

                    $subMenu = new SubMenu();
                    if ($request->edit_id) {
                        // ->where('main_menu_id', $request->main_menu_id)
                        $subMenu = SubMenu::where('id', $request->edit_id)->first();
                        $validateFormData['updated_by'] = $loginUserId;
                        if (!$subMenu) {
                            return Redirect::back()->withErrors("Sub menu not found.")->withInput();
                        }
                        $subMenu->update($validateFormData);
                    } else {
                        $validateFormData['created_by'] = $loginUserId;
                        $subMenu->create($validateFormData);
                    }

                    /*
                    if ($request?->order_by && $request?->order_by != $subMenu?->order_by) {
                    $orderBy = (int)$request?->order_by;
                    $updateOrders = SubMenu::where('main_menu_id', $request->main_menu_id)->where('order_by', ">=", $orderBy)->get();
                    foreach ($updateOrders as $key => $value) {
                    $updateOrderBy = SubMenu::where('id', $value?->id)->first();
                    if($updateOrderBy){
                    $updateOrderBy->order_by = $orderBy;
                    $updateOrderBy->save();
                    $orderBy = $orderBy+1;
                    }
                    }
                    // dd("L-126", $request->all(), $subMenu->toArray(), $updateOrders);
                    }
                    */

                    Session::flash('success', "Sub menu update successfully.");
                    return Redirect::route('sidebar.menu');

                    dd("L-70", $request->all(), $validateData->failed());
                }
                dd("L-146", $request->all(), $validateData->failed());
            }

            $orderBy = ['platform' => 'desc'];
            $module_menu = \App\Helpers\Helper::getModuleMenu('all', $wheres = [], $orderBy);
            View::share('module_menu', $module_menu);
            // return $module_menu;

            self::checkTheMenuMap($request);

            return view('software.sidebar-menu-view');

            $request->all();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function sidebar_menu_delete(Request $request)
    {
        $modules = $this->modules;
        $modules['currentGuard'] = ($this->currentGuard) ? $this->currentGuard : null;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;
        $modules['parent_type_id'] = ($this->authenticateLoginUserDetails?->parent_type_id) ? $this->authenticateLoginUserDetails?->parent_type_id : null;
        $loginUserId = ($modules['authLoginUserDetail'] && $modules['authLoginUserDetail']?->id) ? $modules['authLoginUserDetail']?->id : null;

        try {
            if ($modules['currentGuard'] != "admin_software") {
                abort(403, 'Unauthorized');
            }
            $validateData = Validator::make($request->all(), [
                'delete_id' => ['required'],
                'model' => ['required', 'in:sub_menu,main_menu'],
                'main_menu_id' => ['required_if:model,sub_menu'],
            ]);

            if ($validateData->fails()) {
                return Redirect::back()->withErrors($validateData)->withInput();
            }

            if ($request?->model == "main_menu") {
                $deletedMenu = MainMenu::where('id', $request?->delete_id)->first();
                if ($deletedMenu) {
                    $deletedMenu->update([
                        'deleted_by' => $loginUserId,
                        'deleted_at' => Carbon::now(),
                    ]);
                    $deletedMenu->delete();
                    return $this->sendResponse([], $deletedMenu?->name . " Main menu deleted successfully.");
                } else {
                    return $this->sendError('This sub menu not found. contact to develoer.', ['This sub menu not found. contact to develoer.']);
                }
            } else if ($request?->model == "sub_menu") {
                $deletedMenu = SubMenu::where('id', $request?->delete_id)->where('main_menu_id', $request?->main_menu_id)->first();
                if ($deletedMenu) {
                    $deletedMenu->update([
                        'deleted_by' => $loginUserId,
                        'deleted_at' => Carbon::now(),
                    ]);
                    $deletedMenu->delete();
                    return $this->sendResponse([], $deletedMenu?->name . " Sub menu deleted successfully.");
                } else {
                    return $this->sendError('This sub menu not found. contact to develoer.', ['This sub menu not found. contact to develoer.']);
                }
            }
            return $this->sendError('Your Menu type not found.', $request->all());
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendError("Some thing want to wrong.");
    }

    public function checkTheMenuMap(Request $request)
    {
        $allRoutes = Route::getRoutes();
        $menuFile = config_path('menu_map.php');
        $currenMenuList = file_exists($menuFile) ? config('menu_map') : [];

        // List of routes to ignore
        $excludedRoutes = [
            'company.index',
            'manage-email.index',
            'plan-master.index',
            'company-subscription-plan.index',
            'user.index',
            'application-version.index',
            'software.company-registration.index',
            'website-company-registration.index',
        ];

        // Step 1: Remove excluded routes from database (SubMenu)
        SubMenu::whereIn('route_name', $excludedRoutes)->delete();

        // Step 2: Remove excluded routes from config file
        foreach ($currenMenuList as $menuIndex => $menu) {
            if (isset($menu['sub_menu']) && is_array($menu['sub_menu'])) {
                $currenMenuList[$menuIndex]['sub_menu'] = array_filter(
                    $menu['sub_menu'],
                    function ($subMenu) use ($excludedRoutes) {
                        return !(is_array($subMenu) && isset($subMenu['route_name']) && in_array($subMenu['route_name'], $excludedRoutes));
                    }
                );
                // Re-index array after filtering
                $currenMenuList[$menuIndex]['sub_menu'] = array_values($currenMenuList[$menuIndex]['sub_menu']);
            }
        }

        // Step 3: Ensure "Other Menu" exists exactly once in config
        $otherMenuIndex = null;
        foreach ($currenMenuList as $index => $menu) {
            if (isset($menu['name']) && $menu['name'] === 'Other Menu') {
                $otherMenuIndex = $index;
                break;
            }
        }

        if ($otherMenuIndex === null) {
            // Add "Other Menu" if missing
            $currenMenuList[] = [
                'name' => 'Other Menu',
                'menu_icon' => 'tf-icons ti ti-dots',
                'created_by' => '1',
                'updated_by' => '1',
                'status' => 'active',
                'sub_menu' => [],
            ];
            $otherMenuIndex = count($currenMenuList) - 1;
        }

        // Step 4: Ensure "Other Menu" exists in database
        $otherMainMenu = MainMenu::firstOrCreate(
            ['name' => 'Other Menu'],
            [
                'menu_icon' => 'tf-icons ti ti-dots',
                'created_by' => '1',
                'updated_by' => '1',
                'status' => 'active',
                'platform' => 'panel',
            ]
        );
        $mainMenuId = $otherMainMenu->id;

        // Step 5: Get all existing route names from database (excluding excluded routes)
        $existingDbRouteNames = SubMenu::whereNotIn('route_name', $excludedRoutes)
            ->pluck('route_name')
            ->toArray();

        // Step 6: Load existing route_names from config file (Other Menu) to prevent duplicates
        $existingSubMenuRouteNames = [];
        if (isset($currenMenuList[$otherMenuIndex]['sub_menu'])) {
            foreach ($currenMenuList[$otherMenuIndex]['sub_menu'] as $subMenu) {
                if (is_array($subMenu) && isset($subMenu['route_name']) && !in_array($subMenu['route_name'], $excludedRoutes)) {
                    $existingSubMenuRouteNames[] = $subMenu['route_name'];
                }
            }
        }

        // Step 7: Process all routes
        foreach ($allRoutes as $route) {
            $action = $route->getAction();

            if (isset($action['controller']) && strpos($action['controller'], '@index') !== false) {
                $targetRouteName = $action['as'] ?? null;

                if (!$targetRouteName)
                    continue;

                // Skip excluded routes
                if (in_array($targetRouteName, $excludedRoutes))
                    continue;

                // Check if route exists in database (excluding excluded routes)
                $existsInDb = in_array($targetRouteName, $existingDbRouteNames);

                // Check if route exists globally in config file
                $foundGlobally = false;
                foreach ($currenMenuList as $menu) {
                    if (isset($menu['sub_menu'])) {
                        foreach ($menu['sub_menu'] as $subMenu) {
                            if (is_array($subMenu) && isset($subMenu['route_name']) && $subMenu['route_name'] === $targetRouteName) {
                                $foundGlobally = true;
                                break 2;
                            }
                        }
                    }
                }

                // If exists in DB but not in config, sync to config
                if ($existsInDb && !$foundGlobally) {
                    $dbSubMenu = SubMenu::where('route_name', $targetRouteName)->first();
                    if ($dbSubMenu && $dbSubMenu->main_menu_id == $mainMenuId) {
                        $currenMenuList[$otherMenuIndex]['sub_menu'][] = [
                            'name' => $dbSubMenu->name,
                            'route_name' => $dbSubMenu->route_name,
                            'created_by' => $dbSubMenu->created_by ?? 0,
                            'updated_by' => $dbSubMenu->updated_by ?? 0,
                            'status' => $dbSubMenu->status ?? 'active',
                        ];
                        $existingSubMenuRouteNames[] = $targetRouteName;
                    }
                    continue;
                }

                // If exists in config but not in DB, sync to DB
                if ($foundGlobally && !$existsInDb) {
                    foreach ($currenMenuList as $menu) {
                        if (isset($menu['sub_menu'])) {
                            foreach ($menu['sub_menu'] as $subMenu) {
                                if (is_array($subMenu) && isset($subMenu['route_name']) && $subMenu['route_name'] === $targetRouteName) {
                                    $subMenuMainMenuId = null;
                                    if ($menu['name'] === 'Other Menu') {
                                        $subMenuMainMenuId = $mainMenuId;
                                    } else {
                                        $subMenuMainMenu = MainMenu::firstOrCreate(
                                            ['name' => $menu['name']],
                                            [
                                                'menu_icon' => $menu['menu_icon'] ?? 'tf-icons ti ti-dots',
                                                'created_by' => $menu['created_by'] ?? '1',
                                                'updated_by' => $menu['updated_by'] ?? '1',
                                                'status' => $menu['status'] ?? 'active',
                                                'platform' => $menu['platform'] ?? 'panel',
                                            ]
                                        );
                                        $subMenuMainMenuId = $subMenuMainMenu->id;
                                    }

                                    SubMenu::updateOrCreate(
                                        ['route_name' => $targetRouteName],
                                        [
                                            'name' => $subMenu['name'] ?? ucwords(explode(".", str_replace("-", " ", $targetRouteName))[0]),
                                            'main_menu_id' => $subMenuMainMenuId,
                                            'created_by' => $subMenu['created_by'] ?? 0,
                                            'updated_by' => $subMenu['updated_by'] ?? 0,
                                            'status' => $subMenu['status'] ?? 'active',
                                            'platform' => $subMenu['platform'] ?? 'panel',
                                        ]
                                    );
                                    $existingDbRouteNames[] = $targetRouteName;
                                    break 2;
                                }
                            }
                        }
                    }
                    continue;
                }

                // Skip if already exists in both config and DB
                if ($foundGlobally || $existsInDb)
                    continue;

                // Add new submenu only if unique in Other Menu
                if (!in_array($targetRouteName, $existingSubMenuRouteNames)) {
                    $menuName = explode(".", str_replace("-", " ", $targetRouteName))[0];
                    $newSubMenu = [
                        'name' => ucwords($menuName),
                        'route_name' => $targetRouteName,
                        'created_by' => 0,
                        'updated_by' => 0,
                        'status' => 'active',
                    ];

                    $currenMenuList[$otherMenuIndex]['sub_menu'][] = $newSubMenu;

                    // Sync to database
                    SubMenu::updateOrCreate(
                        ['route_name' => $targetRouteName],
                        [
                            'name' => $newSubMenu['name'],
                            'main_menu_id' => $mainMenuId,
                            'created_by' => $newSubMenu['created_by'],
                            'updated_by' => $newSubMenu['updated_by'],
                            'status' => $newSubMenu['status'],
                            'platform' => 'panel',
                        ]
                    );

                    $existingSubMenuRouteNames[] = $targetRouteName;
                    $existingDbRouteNames[] = $targetRouteName;
                }
            }
        }

        // Step 8: Final synchronization - Remove routes from config that don't exist in DB
        // (Routes in excludedRoutes are already removed, so we only keep routes that exist in DB)
        foreach ($currenMenuList as $menuIndex => $menu) {
            if (isset($menu['sub_menu']) && is_array($menu['sub_menu'])) {
                $currenMenuList[$menuIndex]['sub_menu'] = array_filter(
                    $menu['sub_menu'],
                    function ($subMenu) use ($existingDbRouteNames) {
                        if (!is_array($subMenu) || !isset($subMenu['route_name'])) {
                            return false;
                        }
                        // Keep only if exists in DB (excluded routes are already filtered out from $existingDbRouteNames)
                        return in_array($subMenu['route_name'], $existingDbRouteNames);
                    }
                );
                // Re-index array after filtering
                $currenMenuList[$menuIndex]['sub_menu'] = array_values($currenMenuList[$menuIndex]['sub_menu']);
            }
        }

        // Step 9: Save updated menu map
        file_put_contents($menuFile, '<?php return ' . var_export($currenMenuList, true) . ';');
    }

    public function dashboard(Request $request)
    {

        $modules = $this->modules;
        $modules['module_name'] = 'Dashboard';
        $modules['currentGuard'] = ($this->currentGuard) ? $this->currentGuard : null;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;
        $modules['parent_type_id'] = isset($this->authenticateLoginUserDetails?->parent_type_id) ? $this->authenticateLoginUserDetails->parent_type_id : null;
        $loginUser = $modules['authLoginUserDetail'];
        $loginUserId = ($modules['authLoginUserDetail'] && $modules['authLoginUserDetail']?->id) ? $modules['authLoginUserDetail']?->id : null;

        try {
            /** Inquiry Module Permmission */
            $modules['inquiry_view'] = (isset($modules['company_id']) && !$modules['company_id']) ? true : Gate::check('hasPermission', [config('constants.permissions.view'), 'Inquiry']);
            $modules['inquiry_all_data'] = (isset($modules['company_id']) && !$modules['company_id']) ? true : Gate::check('hasPermission', [config('constants.permissions.all_data'), 'Inquiry']);
            $modules['inquiry_personal_data'] = (isset($modules['company_id']) && !$modules['company_id']) ? true : Gate::check('hasPermission', [config('constants.permissions.personal_data'), 'Inquiry']);

            $modules['followup_view'] = (isset($modules['company_id']) && !$modules['company_id']) ? true : Gate::check('hasPermission', [config('constants.permissions.view'), 'Follow Up']);
            $modules['followup_all_data'] = (isset($modules['company_id']) && !$modules['company_id']) ? true : Gate::check('hasPermission', [config('constants.permissions.all_data'), 'Follow Up']);
            $modules['followup_personal_data'] = (isset($modules['company_id']) && !$modules['company_id']) ? true : Gate::check('hasPermission', [config('constants.permissions.personal_data'), 'Follow Up']);

            $modules['order_view'] = (isset($modules['company_id']) && !$modules['company_id']) ? true : Gate::check('hasPermission', [config('constants.permissions.view'), 'Sales Orders']);
            $modules['order_all_data'] = (isset($modules['company_id']) && !$modules['company_id']) ? true : Gate::check('hasPermission', [config('constants.permissions.all_data'), 'Sales Orders']);
            $modules['order_personal_data'] = (isset($modules['company_id']) && !$modules['company_id']) ? true : Gate::check('hasPermission', [config('constants.permissions.personal_data'), 'Sales Orders']);

            $modules['dispatch_view'] = (isset($modules['company_id']) && !$modules['company_id']) ? true : Gate::check('hasPermission', [config('constants.permissions.view'), 'Dispatch']);
            $modules['dispatch_all_data'] = (isset($modules['company_id']) && !$modules['company_id']) ? true : Gate::check('hasPermission', [config('constants.permissions.all_data'), 'Dispatch']);
            $modules['dispatch_personal_data'] = (isset($modules['company_id']) && !$modules['company_id']) ? true : Gate::check('hasPermission', [config('constants.permissions.personal_data'), 'Dispatch']);

            $modules['operations_rate_list_view'] = (isset($modules['company_id']) && !$modules['company_id']) ? true : Gate::check('hasPermission', [config('constants.permissions.view'), 'Operations Rate List']);
            $modules['salary_calculation_view'] = (isset($modules['company_id']) && !$modules['company_id']) ? true : Gate::check('hasPermission', [config('constants.permissions.view'), 'Salary Calculation']);

            View::share('modules', $modules);
            // dd("L-266", $modules, $this->currentGuard, $modules['currentGuard']);

            $loginType = $modules['company_id'] ? 'team' : 'admin';
            View::share('loginType', $loginType);

            if ($request->ajax()) {
                ($request->company_id) ? $modules['company_id'] = $request->company_id : null;

                $startOfMonth = (isset($request->start_date) && $request?->start_date) ? $request->input('start_date') : null;
                $endOfMonth = (isset($request->end_date) && $request?->end_date) ? $request->input('end_date') : null;

                if ($request?->filter_by_daterange && !$startOfMonth && !$endOfMonth) {
                    $filter_by_daterange = str_replace(' - ', ' to ', $request->filter_by_daterange);
                    $filter_by_daterange = explode(" to ", $filter_by_daterange);
                    $fromDate = null;
                    $toDate = null;

                    if (count($filter_by_daterange) == 2) {
                        $startOfMonth = Helper::convert_date($filter_by_daterange[0], "d/m/Y", "Y-m-d");
                        $endOfMonth = Helper::convert_date($filter_by_daterange[1], "d/m/Y", "Y-m-d");
                    }
                } else {
                    // Default to today if no date range provided
                    $today = Carbon::today();
                    $startOfMonth = $today->format('Y-m-d');
                    $endOfMonth = $today->format('Y-m-d');
                }
                $dateRangeArray = [$startOfMonth, $endOfMonth];
                $operationStats = [];

                $totalContractorSalary = 0;
                $totalCompanyPayroll = 0;

                if (isset($modules['operations_rate_list_view']) && $modules['operations_rate_list_view']) {
                    // Get operation-wise statistics
                    $operationStatsQuery = OperationsRateList::query();
                    $payrollQuery = \App\Models\Salary::query()
                        ->whereHas('employee.employmentDetail.employee_type', function ($q) {
                            $q->where('name', 'not like', '%contract%');
                        });

                    if ($modules['company_id']) {
                        $operationStatsQuery->where('operations_rate_lists.company_id', $modules['company_id']);
                        $payrollQuery->where('company_id', $modules['company_id']);
                    }

                    // If date range is provided, try to filter by specific days if it's within a month
                    $dayFilterSql = "SUM(total_amount)"; // Default to whole month sum if logic below doesn't apply

                    if ($startOfMonth && $endOfMonth) {
                        $start = Carbon::parse($startOfMonth);
                        $end = Carbon::parse($endOfMonth);

                        $startMonth = (int) $start->format('n');
                        $startYear = (int) $start->format('Y');
                        $endMonth = (int) $end->format('n');
                        $endYear = (int) $end->format('Y');

                        // For Payroll, we filter by month and year
                        $payrollQuery->where(function ($q) use ($startMonth, $startYear, $endMonth, $endYear) {
                            $q->whereRaw("(year > ? OR (year = ? AND month >= ?))", [$startYear, $startYear, $startMonth])
                                ->whereRaw("(year < ? OR (year = ? AND month <= ?))", [$endYear, $endYear, $endMonth]);
                        });

                        if ($start->format('Y-m') == $end->format('Y-m')) {
                            $operationStatsQuery->where('operations_rate_lists.month', $startMonth)
                                ->where('operations_rate_lists.year', $startYear);

                            $startDay = (int) $start->format('j');
                            $endDay = (int) $end->format('j');

                            $sumParts = [];
                            for ($d = $startDay; $d <= $endDay; $d++) {
                                // Normal rate
                                $sumParts[] = "(operations_rate_lists.day_{$d} * operations_rate_lists.rate)";

                                // Rejection rate: For FOUNDRY use base rate, otherwise use product's rejection_rate
                                $sumParts[] = "(operations_rate_lists.day_{$d}_r * IF(operations_rate_lists.operation = 'FOUNDRY', operations_rate_lists.rate, IFNULL(products.rejection_rate, 0)))";

                                // OT rate: Use product's ot_text (stored as rate)
                                $sumParts[] = "(operations_rate_lists.day_{$d}_ot * IFNULL(products.ot_text, 0))";
                            }
                            $dayFilterSql = "SUM(" . implode(" + ", $sumParts) . ")";
                        } else {
                            // If multi-month, we filter by the range of months/years
                            $operationStatsQuery->where(function ($q) use ($startMonth, $startYear, $endMonth, $endYear) {
                                $q->whereRaw("(operations_rate_lists.year > ? OR (operations_rate_lists.year = ? AND operations_rate_lists.month >= ?))", [$startYear, $startYear, $startMonth])
                                    ->whereRaw("(operations_rate_lists.year < ? OR (operations_rate_lists.year = ? AND operations_rate_lists.month <= ?))", [$endYear, $endYear, $endMonth]);
                            });
                        }
                    }

                    $operationResults = $operationStatsQuery
                        ->leftJoin('products', 'products.id', '=', 'operations_rate_lists.product_id')
                        ->select('operations_rate_lists.operation', DB::raw("$dayFilterSql as total_amount"))
                        ->groupBy('operations_rate_lists.operation')
                        ->pluck('total_amount', 'operation')
                        ->toArray();

                    // Ensure all statuses from model are present
                    $allOperations = OperationsRateList::$statuses;
                    foreach ($allOperations as $op) {
                        $amount = $operationResults[$op] ?? 0;
                        $operationStats[] = (object) [
                            'operation' => $op,
                            'total_amount' => $amount
                        ];
                        $totalContractorSalary += $amount;
                    }

                    $totalCompanyPayroll = $payrollQuery->sum('net_bank_pay');
                }

                // Get today's attendance statistics
                $attendanceStats = $this->getTodayAttendanceStatistics($modules['company_id'], $startOfMonth, $endOfMonth);

                $companies = Company::get();
                $inactiveCompanies = Company::where('status', 'inactive')->count();

                $expiredCompanies = $companies->filter(function ($row) {
                    $latestPlan = CompanySubscriptionPlan::where('company_id', $row->id)
                        ->where('plan_id', $row->plan_id)
                        ->orderBy('id', 'desc')
                        ->first();

                    return $latestPlan && $latestPlan?->subscription_status === 'expired';
                })->count();
                $showNumericModule['totalCompanies'] = $companies->count();
                $showNumericModule['expiredCompanies'] = $expiredCompanies;
                $showNumericModule['inactiveCompanies'] = $inactiveCompanies;
                $showNumericModule['totalContractorSalary'] = $totalContractorSalary;
                $showNumericModule['totalCompanyPayroll'] = $totalCompanyPayroll;
                $showNumericModule['totalSalary'] = $totalContractorSalary + $totalCompanyPayroll;

                $totalPlans = PlanMaster::count();
                $inactivePlans = PlanMaster::where('status', 'inactive')->count();
                $generalPlans = PlanMaster::where('plan_type', 'general')->count();
                $privatePlans = PlanMaster::where('plan_type', 'private')->count();

                $showNumericModule['totalPlans'] = $totalPlans;
                $showNumericModule['inactivePlans'] = $inactivePlans;
                $showNumericModule['generalPlans'] = $generalPlans;
                $showNumericModule['privatePlans'] = $privatePlans;

                // Resolve Employee Profile
                $currentEmployee = null;
                $isEmployeeOnly = false;

                if (Auth::guard('employees')->check()) {
                    $currentEmployee = Auth::guard('employees')->user();
                } elseif (Auth::guard('admin_software')->check()) {
                    $adminUser = Auth::guard('admin_software')->user();
                    $currentEmployee = Employee::where('email', $adminUser->email)->orWhere('username', $adminUser->username)->first();
                    if (!$currentEmployee && $request->filled('employee_id')) {
                        $currentEmployee = Employee::find($request->employee_id);
                    }
                }

                // Check if current user is Company Main Admin
                $isCompanyAdmin = false;
                $currentCompanyId = $modules['company_id'] ?? ($currentEmployee?->company_id ?? 0);
                if (Auth::guard('admin_software')->check()) {
                    $isCompanyAdmin = true;
                } elseif ($currentEmployee) {
                    $roleName = strtolower(trim($currentEmployee->current_role?->name ?? ''));
                    $company = $currentCompanyId ? Company::find($currentCompanyId) : null;
                    $companyName = $company ? strtolower(trim($company->company_name)) : '';

                    $isParentZero = ($currentEmployee->parent_id === 0 || $currentEmployee->parent_id === '0' || $currentEmployee->parent_id === null);
                    $isAdminRole = in_array($roleName, ['super-admin', 'admin', 'main hr', 'owner', $companyName]) || str_contains($roleName, 'super-admin');

                    if (($isParentZero || $isAdminRole || !empty($modules['all_data_permission'])) && !in_array($roleName, ['user role', 'employee', 'supervisor', 'department head', 'main hr', 'hr'])) {
                        $isCompanyAdmin = true;
                    }
                }

                if (!$isCompanyAdmin && $currentEmployee) {
                    $isEmployeeOnly = true;
                }

                // Calculate Employee Personal Attendance & Leave Balance Stats (for non-admin or employee punch view)
                $employeeStats = null;
                $subordinateEmployees = [];
                if ($currentEmployee) {
                    $empId = $currentEmployee->id;
                    $todayDate = Carbon::today()->format('Y-m-d');
                    $currMonthStart = Carbon::now()->startOfMonth()->format('Y-m-d');
                    $currMonthEnd = Carbon::now()->endOfMonth()->format('Y-m-d');

                    // 1. Total Attendance in filtered date range & current month
                    $totalPresentRange = Attendance::where('employee_id', $empId)
                        ->whereBetween('attendance_date', [$startOfMonth, $endOfMonth])
                        ->where('attendace_type', 'in')
                        ->distinct('attendance_date')
                        ->count('attendance_date');

                    $totalPresentMonth = Attendance::where('employee_id', $empId)
                        ->whereBetween('attendance_date', [$currMonthStart, $currMonthEnd])
                        ->where('attendace_type', 'in')
                        ->distinct('attendance_date')
                        ->count('attendance_date');

                    // Punch In & Punch Out Counts
                    $totalPunchInCount = Attendance::where('employee_id', $empId)
                        ->whereBetween('attendance_date', [$startOfMonth, $endOfMonth])
                        ->where('attendace_type', 'in')
                        ->count();

                    $totalPunchOutCount = Attendance::where('employee_id', $empId)
                        ->whereBetween('attendance_date', [$startOfMonth, $endOfMonth])
                        ->where('attendace_type', 'out')
                        ->count();

                    // 2. Today's Punch In & Punch Out Time
                    $todayPunches = Attendance::where('employee_id', $empId)
                        ->where('attendance_date', $todayDate)
                        ->orderBy('id', 'asc')
                        ->get();

                    $todayIn = $todayPunches->where('attendace_type', 'in')->first();
                    $todayOut = $todayPunches->where('attendace_type', 'out')->last();

                    $punchInTime = $todayIn && $todayIn->punch_in_time ? Carbon::parse($todayIn->punch_in_time)->format('h:i A') : null;
                    $punchOutTime = $todayOut && $todayOut->punch_in_time ? Carbon::parse($todayOut->punch_in_time)->format('h:i A') : null;

                    // Shift details
                    $shift = $currentEmployee->employmentDetail?->shiftDetail ?? ($currentEmployee->company_id ? Shift::where('company_id', $currentEmployee->company_id)->first() : null);
                    $shiftName = $shift ? $shift->name : 'General Shift';
                    $shiftTiming = ($shift && $shift->punch_in_minimum && $shift->punch_out)
                        ? (Carbon::parse($shift->punch_in_minimum)->format('h:i A') . ' - ' . Carbon::parse($shift->punch_out)->format('h:i A'))
                        : '-';

                    // 3. Leave Balance Type-wise
                    $leaveBalances = [];
                    $leaveTypes = \App\Models\LeaveType::where('company_id', $currentEmployee->company_id)
                        ->where('status', 'active')
                        ->get();

                    $colorPalettes = [
                        ['bg' => 'rgba(115, 103, 240, 0.1)', 'border' => '#7367f0', 'text' => '#7367f0'],
                        ['bg' => 'rgba(40, 199, 111, 0.1)', 'border' => '#28c76f', 'text' => '#28c76f'],
                        ['bg' => 'rgba(0, 207, 232, 0.1)', 'border' => '#00cfe8', 'text' => '#00cfe8'],
                        ['bg' => 'rgba(255, 159, 67, 0.1)', 'border' => '#ff9f43', 'text' => '#ff9f43'],
                    ];

                    $colorIndex = 0;
                    foreach ($leaveTypes as $lt) {
                        $availableBal = $currentEmployee->getAvailableLeaveBalance($lt->id);
                        $usedInFY = $currentEmployee->getUsedLeaveCountForReport($lt->id, (int) Carbon::now()->year, (int) Carbon::now()->month);
                        $palette = $colorPalettes[$colorIndex % count($colorPalettes)];
                        $colorIndex++;

                        $leaveBalances[] = [
                            'id' => $lt->id,
                            'name' => $lt->full_name,
                            'code' => $lt->sort_name ?: $lt->full_name,
                            'allocated' => (float) $lt->count,
                            'balance' => (float) $availableBal,
                            'used_year' => (float) $usedInFY,
                            'carry_forward' => $lt->carry_forward == 1,
                            'palette' => $palette,
                        ];
                    }

                    $latestPunchToday = Attendance::where('employee_id', $empId)
                        ->where('attendance_date', $todayDate)
                        ->orderBy('id', 'desc')
                        ->first();
                    $punchStateVal = ($latestPunchToday && $latestPunchToday->attendace_type === 'in') ? 'in' : 'out';

                    $employeeStats = [
                        'employee' => $currentEmployee,
                        'proper_name' => $currentEmployee->proper_name ?: ($currentEmployee->full_name ?: $currentEmployee->first_name),
                        'employee_code' => $currentEmployee->employee_code ?: 'EMP-' . $currentEmployee->id,
                        'total_present_range' => $totalPresentRange,
                        'total_present_month' => $totalPresentMonth,
                        'total_punch_in_count' => $totalPunchInCount,
                        'total_punch_out_count' => $totalPunchOutCount,
                        'punch_in_time' => $punchInTime,
                        'punch_out_time' => $punchOutTime,
                        'shift_name' => $shiftName,
                        'shift_timing' => $shiftTiming,
                        'leave_balances' => $leaveBalances,
                        'punch_state' => $punchStateVal,
                    ];

                    // Multi-tier Hierarchy Processing (Main HR -> Department Head -> Supervisor -> Employee)
                    $directSubordinates = Employee::where('company_id', $currentEmployee->company_id)
                        ->where('parent_id', $currentEmployee->id)
                        ->where('status', 'active')
                        ->with(['employmentDetail.department', 'employmentDetail.designation', 'teamRole', 'current_role'])
                        ->get();

                    $compLeaveTypes = \App\Models\LeaveType::where('company_id', $currentEmployee->company_id)
                        ->where('status', 'active')
                        ->get();

                    $departmentHeadsHierarchy = [];
                    $hierarchySupervisors = [];
                    $directEmployees = [];

                    if ($directSubordinates->isNotEmpty()) {
                        foreach ($directSubordinates as $sub) {
                            $subCard = $this->getEmployeeDashboardCardData($sub, $todayDate, $currMonthStart, $currMonthEnd, $compLeaveTypes);
                            $subordinateEmployees[] = $subCard;

                            $subRoleName = strtolower(trim($sub->teamRole?->name ?? ($sub->current_role?->name ?? '')));
                            $subDesigName = strtolower(trim($sub->employmentDetail?->designation?->name ?? ''));
                            $isDeptHeadRole = str_contains($subRoleName, 'department head') || str_contains($subRoleName, 'dept head') || str_contains($subRoleName, 'hod')
                                || str_contains($subDesigName, 'department head') || str_contains($subDesigName, 'dept head') || str_contains($subDesigName, 'hod');
                            $isSupervisorRole = str_contains($subRoleName, 'supervisor') || str_contains($subDesigName, 'supervisor');

                            // Level-2 Subordinates (Under $sub)
                            $level2Subordinates = Employee::where('company_id', $currentEmployee->company_id)
                                ->where('parent_id', $sub->id)
                                ->where('status', 'active')
                                ->with(['employmentDetail.department', 'employmentDetail.designation', 'teamRole', 'current_role'])
                                ->get();

                            // Check if any level-2 subordinate is a Supervisor or has their own subordinates
                            $hasSupervisorsUnderneath = false;
                            if ($level2Subordinates->isNotEmpty()) {
                                foreach ($level2Subordinates as $l2) {
                                    $l2RoleName = strtolower(trim($l2->teamRole?->name ?? ($l2->current_role?->name ?? '')));
                                    $l2DesigName = strtolower(trim($l2->employmentDetail?->designation?->name ?? ''));
                                    if (str_contains($l2RoleName, 'supervisor') || str_contains($l2DesigName, 'supervisor')) {
                                        $hasSupervisorsUnderneath = true;
                                        break;
                                    }
                                    $hasL3 = Employee::where('company_id', $currentEmployee->company_id)->where('parent_id', $l2->id)->where('status', 'active')->exists();
                                    if ($hasL3) {
                                        $hasSupervisorsUnderneath = true;
                                        break;
                                    }
                                }
                            }

                            if ($isDeptHeadRole || $hasSupervisorsUnderneath) {
                                // === DEPARTMENT HEAD LEVEL ===
                                $deptSupervisors = [];
                                $deptDirectEmployees = [];

                                foreach ($level2Subordinates as $l2) {
                                    $l2Card = $this->getEmployeeDashboardCardData($l2, $todayDate, $currMonthStart, $currMonthEnd, $compLeaveTypes);
                                    $l2RoleName = strtolower(trim($l2->teamRole?->name ?? ($l2->current_role?->name ?? '')));
                                    $l2DesigName = strtolower(trim($l2->employmentDetail?->designation?->name ?? ''));
                                    $isL2Sup = str_contains($l2RoleName, 'supervisor') || str_contains($l2DesigName, 'supervisor');

                                    // Level-3 Subordinates (Employees under supervisor $l2)
                                    $level3Subordinates = Employee::where('company_id', $currentEmployee->company_id)
                                        ->where('parent_id', $l2->id)
                                        ->where('status', 'active')
                                        ->with(['employmentDetail.department', 'employmentDetail.designation', 'teamRole', 'current_role'])
                                        ->get();

                                    if ($level3Subordinates->isNotEmpty() || $isL2Sup) {
                                        $l3Cards = [];
                                        foreach ($level3Subordinates as $l3) {
                                            $l3Card = $this->getEmployeeDashboardCardData($l3, $todayDate, $currMonthStart, $currMonthEnd, $compLeaveTypes);
                                            $l3Cards[] = $l3Card;
                                        }

                                        $deptSupervisors[] = [
                                            'supervisor' => $l2Card,
                                            'employees' => $l3Cards,
                                            'employee_count' => count($l3Cards),
                                            'in_count' => collect($l3Cards)->where('status_type', 'present_in')->count(),
                                            'out_count' => collect($l3Cards)->where('status_type', 'present_out')->count(),
                                            'leave_count' => collect($l3Cards)->where('status_type', 'leave')->count(),
                                            'not_punched_count' => collect($l3Cards)->where('status_type', 'not_punched')->count(),
                                        ];
                                    } else {
                                        $deptDirectEmployees[] = $l2Card;
                                    }
                                }

                                $allDeptEmployees = collect($deptSupervisors)->pluck('employees')->flatten(1)->concat($deptDirectEmployees);

                                $departmentHeadsHierarchy[] = [
                                    'dept_head' => $subCard,
                                    'supervisors' => $deptSupervisors,
                                    'direct_employees' => $deptDirectEmployees,
                                    'supervisor_count' => count($deptSupervisors),
                                    'total_employees_count' => $allDeptEmployees->count(),
                                    'in_count' => $allDeptEmployees->where('status_type', 'present_in')->count(),
                                    'out_count' => $allDeptEmployees->where('status_type', 'present_out')->count(),
                                    'leave_count' => $allDeptEmployees->where('status_type', 'leave')->count(),
                                    'not_punched_count' => $allDeptEmployees->where('status_type', 'not_punched')->count(),
                                ];
                            } elseif ($isSupervisorRole || $level2Subordinates->isNotEmpty()) {
                                // === DIRECT SUPERVISOR LEVEL ===
                                $childCards = [];
                                foreach ($level2Subordinates as $child) {
                                    $childCard = $this->getEmployeeDashboardCardData($child, $todayDate, $currMonthStart, $currMonthEnd, $compLeaveTypes);
                                    $childCards[] = $childCard;
                                }

                                $hierarchySupervisors[] = [
                                    'supervisor' => $subCard,
                                    'employees' => $childCards,
                                    'employee_count' => count($childCards),
                                    'in_count' => collect($childCards)->where('status_type', 'present_in')->count(),
                                    'out_count' => collect($childCards)->where('status_type', 'present_out')->count(),
                                    'leave_count' => collect($childCards)->where('status_type', 'leave')->count(),
                                    'not_punched_count' => collect($childCards)->where('status_type', 'not_punched')->count(),
                                ];
                            } else {
                                // === DIRECT EMPLOYEE LEVEL ===
                                $directEmployees[] = $subCard;
                            }
                        }
                    }
                }

                // Company Main Admin / HR Head / Owner — Full Organization Overview
                $adminDashboardData = null;
                if ($isCompanyAdmin && $currentCompanyId) {
                    $now = Carbon::now();
                    $todayDate = $now->format('Y-m-d');
                    $monthStart = $now->copy()->startOfMonth()->format('Y-m-d');
                    $monthEnd = $now->copy()->endOfMonth()->format('Y-m-d');
                    $weekAhead = $now->copy()->addDays(7)->format('Y-m-d');

                    $companyEmployees = Employee::where('company_id', $currentCompanyId)->where('status', 'active')->get();
                    $totalCompanyEmployees = $companyEmployees->count();

                    $contractorTypeIds = \App\Models\EmployeeType::where('name', 'like', '%contract%')
                        ->orWhere('name', 'like', '%contractor%')
                        ->pluck('id');
                    $contractorsCount = 0;
                    if ($contractorTypeIds->isNotEmpty()) {
                        $contractorsCount = Employee::where('company_id', $currentCompanyId)
                            ->where('status', 'active')
                            ->whereHas('employmentDetail', function ($q) use ($contractorTypeIds) {
                                $q->whereIn('employment_type', $contractorTypeIds);
                            })->count();
                    }
                    $regularEmployeesCount = max(0, $totalCompanyEmployees - $contractorsCount);

                    $newThisMonth = DB::table('employment_details')
                        ->where('company_id', $currentCompanyId)
                        ->whereBetween('date_of_joining', [$monthStart, $monthEnd])
                        ->count();

                    $exitsThisMonth = Employee::where('company_id', $currentCompanyId)
                        ->where('status', 'inactive')
                        ->whereBetween('updated_at', [$monthStart . ' 00:00:00', $monthEnd . ' 23:59:59'])
                        ->count();

                    $presentEmployeeIds = Attendance::where('company_id', $currentCompanyId)
                        ->where('attendance_date', $todayDate)
                        ->where('attendace_type', 'in')
                        ->distinct('employee_id')
                        ->pluck('employee_id')
                        ->toArray();
                    $presentTodayCount = count($presentEmployeeIds);

                    $onLeaveEmployeeIds = LeaveApplication::where('company_id', $currentCompanyId)
                        ->where('status', 'approved')
                        ->where(function ($q) use ($todayDate) {
                            $q->where(function ($sub) use ($todayDate) {
                                $sub->whereNotNull('todate_time')
                                    ->whereDate('fromdate_time', '<=', $todayDate)
                                    ->whereDate('todate_time', '>=', $todayDate);
                            })->orWhere(function ($sub) use ($todayDate) {
                                $sub->whereNull('todate_time')
                                    ->whereDate('fromdate_time', '=', $todayDate);
                            });
                        })
                        ->distinct('employee_id')
                        ->pluck('employee_id')
                        ->toArray();
                    $onLeaveTodayCount = count($onLeaveEmployeeIds);

                    $absentTodayCount = max(0, $totalCompanyEmployees - $presentTodayCount - $onLeaveTodayCount);
                    $presentPercentage = $totalCompanyEmployees > 0 ? round(($presentTodayCount / $totalCompanyEmployees) * 100, 1) : 0;
                    $absentPercentage = $totalCompanyEmployees > 0 ? round(($absentTodayCount / $totalCompanyEmployees) * 100, 1) : 0;
                    $onLeavePercentage = $totalCompanyEmployees > 0 ? round(($onLeaveTodayCount / $totalCompanyEmployees) * 100, 1) : 0;

                    $lateTodayCount = (int) ($attendanceStats['late_punch'] ?? 0);
                    $earlyGoCount = (int) ($attendanceStats['early_go'] ?? 0);

                    $pendingLeavesCount = LeaveApplication::where('company_id', $currentCompanyId)
                        ->where('status', 'pending')->count();
                    $pendingExpensesCount = \App\Models\Expense::where('company_id', $currentCompanyId)
                        ->where('status', 'pending')->count();
                    $pendingLoansCount = \App\Models\Loan::where('company_id', $currentCompanyId)
                        ->where('status', 'pending')->count();
                    $pendingApprovalsTotal = $pendingLeavesCount + $pendingExpensesCount + $pendingLoansCount;

                    $pendingExpenseAmount = (float) \App\Models\Expense::where('company_id', $currentCompanyId)
                        ->where('status', 'pending')->sum('req_amount');
                    $monthExpensePassed = (float) \App\Models\Expense::where('company_id', $currentCompanyId)
                        ->where('status', 'pass')
                        ->whereBetween('date', [$monthStart, $monthEnd])
                        ->sum('pass_amount');

                    $activeLoansCount = \App\Models\Loan::where('company_id', $currentCompanyId)
                        ->where('status', 'approved')->count();
                    $activeLoanBalance = (float) \App\Models\Loan::where('company_id', $currentCompanyId)
                        ->where('status', 'approved')->sum('balance_amount');

                    $monthPayroll = (float) \App\Models\Salary::where('company_id', $currentCompanyId)
                        ->where('month', (int) $now->format('n'))
                        ->where('year', (int) $now->format('Y'))
                        ->sum('net_bank_pay');
                    if ($monthPayroll <= 0) {
                        $monthPayroll = (float) ($totalCompanyPayroll ?? 0);
                    }
                    $monthContractorPay = (float) ($totalContractorSalary ?? 0);
                    $monthTotalPayroll = $monthPayroll + $monthContractorPay;

                    // Department-wise headcount (real data)
                    $deptColors = ['#2563eb', '#ef4444', '#10b981', '#f59e0b', '#06b6d4', '#8b5cf6', '#f43f5e', '#14b8a6', '#eab308', '#6366f1'];
                    $departments = Department::where('company_id', $currentCompanyId)->where('status', 'active')->get();
                    $deptStats = [];
                    $cIdx = 0;
                    foreach ($departments as $d) {
                        $count = DB::table('employment_details')
                            ->join('employees', 'employees.id', '=', 'employment_details.employee_id')
                            ->where('employees.company_id', $currentCompanyId)
                            ->where('employees.status', 'active')
                            ->where('employment_details.department_id', $d->id)
                            ->count();
                        if ($count > 0) {
                            $deptStats[] = [
                                'name' => $d->name,
                                'count' => $count,
                                'color' => $deptColors[$cIdx % count($deptColors)],
                            ];
                            $cIdx++;
                        }
                    }
                    usort($deptStats, fn($a, $b) => $b['count'] <=> $a['count']);
                    $deptStats = array_slice($deptStats, 0, 8);
                    $deptMax = !empty($deptStats) ? max(array_column($deptStats, 'count')) : 1;

                    // 7-day attendance trend
                    $attendanceTrend = [];
                    for ($i = 6; $i >= 0; $i--) {
                        $d = $now->copy()->subDays($i);
                        $dStr = $d->format('Y-m-d');
                        $pCount = Attendance::where('company_id', $currentCompanyId)
                            ->where('attendance_date', $dStr)
                            ->where('attendace_type', 'in')
                            ->distinct('employee_id')
                            ->count('employee_id');
                        $attendanceTrend[] = [
                            'label' => $d->format('D'),
                            'date' => $d->format('d M'),
                            'present' => $pCount,
                        ];
                    }
                    $trendMax = max(1, max(array_column($attendanceTrend, 'present')));

                    // Live attendance (real only)
                    $liveAttendanceList = [];
                    $todayAttendanceQuery = Attendance::where('company_id', $currentCompanyId)
                        ->where('attendance_date', $todayDate)
                        ->where('attendace_type', 'in')
                        ->with('employee.employmentDetail.department')
                        ->orderBy('punch_in_time', 'asc')
                        ->take(6)
                        ->get();

                    foreach ($todayAttendanceQuery as $tp) {
                        $emp = $tp->employee;
                        if (!$emp) continue;
                        $eName = $emp->proper_name ?: ($emp->full_name ?: $emp->first_name);
                        $hasProfile = !empty($emp->profile_image) && file_exists(public_path($emp->profile_image));
                        $liveAttendanceList[] = [
                            'name' => $eName,
                            'department' => $emp->employmentDetail?->department?->name ?? '—',
                            'time' => $tp->punch_in_time ? Carbon::parse($tp->punch_in_time)->format('h:i A') : '—',
                            'status' => 'Present',
                            'badge' => 'present',
                            'avatar' => $emp->employee_photo_url,
                            'has_avatar' => $hasProfile,
                        ];
                    }

                    // Upcoming / recent leaves
                    $upcomingLeavesList = [];
                    $recentLeavesQuery = LeaveApplication::where('company_id', $currentCompanyId)
                        ->whereIn('status', ['approved', 'pending'])
                        ->where(function ($q) use ($todayDate, $weekAhead) {
                            $q->whereDate('fromdate_time', '>=', $todayDate)
                                ->whereDate('fromdate_time', '<=', $weekAhead);
                        })
                        ->with(['employee.employmentDetail.department', 'leave_type'])
                        ->orderBy('fromdate_time', 'asc')
                        ->take(6)
                        ->get();

                    if ($recentLeavesQuery->isEmpty()) {
                        $recentLeavesQuery = LeaveApplication::where('company_id', $currentCompanyId)
                            ->with(['employee.employmentDetail.department', 'leave_type'])
                            ->orderBy('id', 'desc')
                            ->take(6)
                            ->get();
                    }

                    foreach ($recentLeavesQuery as $lv) {
                        $emp = $lv->employee;
                        if (!$emp) continue;
                        $eName = $emp->proper_name ?: ($emp->full_name ?: $emp->first_name);
                        $hasProfile = !empty($emp->profile_image) && file_exists(public_path($emp->profile_image));
                        $upcomingLeavesList[] = [
                            'name' => $eName,
                            'department' => $emp->employmentDetail?->department?->name ?? '—',
                            'leave_type' => $lv->leave_type?->full_name ?? ($lv->leave_type?->sort_name ?? 'Leave'),
                            'date' => $lv->fromdate_time ? Carbon::parse($lv->fromdate_time)->format('d M Y') : '—',
                            'status' => $lv->status ?? 'pending',
                            'avatar' => $emp->employee_photo_url,
                            'has_avatar' => $hasProfile,
                        ];
                    }

                    // Pending leave items for action center
                    $pendingLeaveItems = [];
                    $pendingLeaveRows = LeaveApplication::where('company_id', $currentCompanyId)
                        ->where('status', 'pending')
                        ->with(['employee', 'leave_type'])
                        ->orderBy('id', 'desc')
                        ->take(5)
                        ->get();
                    foreach ($pendingLeaveRows as $lv) {
                        $emp = $lv->employee;
                        if (!$emp) continue;
                        $eName = $emp->proper_name ?: ($emp->full_name ?: $emp->first_name);
                        $hasProfile = !empty($emp->profile_image) && file_exists(public_path($emp->profile_image));
                        $pendingLeaveItems[] = [
                            'name' => $eName,
                            'type' => $lv->leave_type?->sort_name ?? ($lv->leave_type?->full_name ?? 'Leave'),
                            'date' => $lv->fromdate_time ? Carbon::parse($lv->fromdate_time)->format('d M') : '—',
                            'avatar' => $emp->employee_photo_url,
                            'has_avatar' => $hasProfile,
                        ];
                    }

                    // Birthdays & work anniversaries (next 7 days)
                    $anniversariesList = [];
                    $todayMd = $now->format('m-d');
                    $endMd = $now->copy()->addDays(7)->format('m-d');
                    foreach ($companyEmployees as $emp) {
                        if (!empty($emp->date_of_birth)) {
                            try {
                                $dob = Carbon::parse($emp->date_of_birth);
                                $md = $dob->format('m-d');
                                $inWindow = ($todayMd <= $endMd)
                                    ? ($md >= $todayMd && $md <= $endMd)
                                    : ($md >= $todayMd || $md <= $endMd);
                                if ($inWindow) {
                                    $eName = $emp->proper_name ?: ($emp->full_name ?: $emp->first_name);
                                    $hasProfile = !empty($emp->profile_image) && file_exists(public_path($emp->profile_image));
                                    $anniversariesList[] = [
                                        'name' => $eName,
                                        'event' => 'Birthday',
                                        'subtitle' => $dob->format('d M') . ' · Birthday',
                                        'sort' => $md,
                                        'icon' => 'ti-cake',
                                        'color' => '#ef4444',
                                        'bg' => '#fef2f2',
                                        'avatar' => $emp->employee_photo_url,
                                        'has_avatar' => $hasProfile,
                                    ];
                                }
                            } catch (\Exception $e) {
                            }
                        }
                    }
                    $joinRows = DB::table('employment_details')
                        ->join('employees', 'employees.id', '=', 'employment_details.employee_id')
                        ->where('employees.company_id', $currentCompanyId)
                        ->where('employees.status', 'active')
                        ->whereNotNull('employment_details.date_of_joining')
                        ->select('employees.id', 'employees.full_name', 'employees.first_name', 'employees.profile_image', 'employment_details.date_of_joining')
                        ->get();
                    foreach ($joinRows as $jr) {
                        try {
                            $doj = Carbon::parse($jr->date_of_joining);
                            $md = $doj->format('m-d');
                            $inWindow = ($todayMd <= $endMd)
                                ? ($md >= $todayMd && $md <= $endMd)
                                : ($md >= $todayMd || $md <= $endMd);
                            if ($inWindow && $doj->year < $now->year) {
                                $years = $now->year - $doj->year;
                                $eName = $jr->full_name ?: $jr->first_name;
                                $hasProfile = !empty($jr->profile_image) && file_exists(public_path($jr->profile_image));
                                $anniversariesList[] = [
                                    'name' => $eName,
                                    'event' => 'Work Anniversary',
                                    'subtitle' => $doj->format('d M') . ' · ' . $years . ' yr',
                                    'sort' => $md,
                                    'icon' => 'ti-award',
                                    'color' => '#10b981',
                                    'bg' => '#ecfdf5',
                                    'avatar' => $hasProfile ? asset($jr->profile_image) : ('https://ui-avatars.com/api/?name=' . urlencode($eName)),
                                    'has_avatar' => $hasProfile,
                                ];
                            }
                        } catch (\Exception $e) {
                        }
                    }
                    usort($anniversariesList, fn($a, $b) => strcmp($a['sort'], $b['sort']));
                    $anniversariesList = array_slice($anniversariesList, 0, 6);

                    $adminUser = Auth::guard('admin_software')->user();
                    $adminName = $currentEmployee
                        ? ($currentEmployee->proper_name ?: ($currentEmployee->full_name ?: $currentEmployee->first_name))
                        : ($adminUser->name ?? 'Admin');
                    $company = Company::find($currentCompanyId);

                    $hour = (int) $now->format('H');
                    $greeting = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');

                    // Company master counts
                    $branchesCount = \App\Models\Branch::where('company_id', $currentCompanyId)->where('status', 'active')->count();
                    $departmentsCount = Department::where('company_id', $currentCompanyId)->where('status', 'active')->count();
                    $shiftsCount = Shift::where('company_id', $currentCompanyId)->where('status', 'active')->count();
                    $designationsCount = \App\Models\Designation::where('company_id', $currentCompanyId)->where('status', 'active')->count();

                    // Gender split
                    $maleCount = Employee::where('company_id', $currentCompanyId)->where('status', 'active')
                        ->where(function ($q) {
                            $q->whereRaw('LOWER(gender) IN (?, ?, ?)', ['male', 'm', 'man']);
                        })->count();
                    $femaleCount = Employee::where('company_id', $currentCompanyId)->where('status', 'active')
                        ->where(function ($q) {
                            $q->whereRaw('LOWER(gender) IN (?, ?, ?)', ['female', 'f', 'woman']);
                        })->count();
                    $otherGenderCount = max(0, $totalCompanyEmployees - $maleCount - $femaleCount);

                    // New joiners this month (list)
                    $newJoinersList = [];
                    $newJoinerRows = DB::table('employment_details')
                        ->join('employees', 'employees.id', '=', 'employment_details.employee_id')
                        ->leftJoin('departments', 'departments.id', '=', 'employment_details.department_id')
                        ->where('employees.company_id', $currentCompanyId)
                        ->whereBetween('employment_details.date_of_joining', [$monthStart, $monthEnd])
                        ->select('employees.full_name', 'employees.first_name', 'employees.employee_code', 'employees.profile_image', 'employment_details.date_of_joining', 'departments.name as dept_name')
                        ->orderBy('employment_details.date_of_joining', 'desc')
                        ->take(5)
                        ->get();
                    foreach ($newJoinerRows as $nj) {
                        $hasProfile = !empty($nj->profile_image) && file_exists(public_path($nj->profile_image));
                        $newJoinersList[] = [
                            'name' => $nj->full_name ?: $nj->first_name,
                            'code' => $nj->employee_code ?: '—',
                            'department' => $nj->dept_name ?: '—',
                            'date' => $nj->date_of_joining ? Carbon::parse($nj->date_of_joining)->format('d M Y') : '—',
                            'avatar' => $hasProfile ? asset($nj->profile_image) : ('https://ui-avatars.com/api/?name=' . urlencode($nj->full_name ?: $nj->first_name)),
                            'has_avatar' => $hasProfile,
                        ];
                    }

                    // Absent employees today (sample list)
                    $absentList = [];
                    $absentEmpIds = Employee::where('company_id', $currentCompanyId)
                        ->where('status', 'active')
                        ->whereNotIn('id', array_merge($presentEmployeeIds, $onLeaveEmployeeIds))
                        ->with('employmentDetail.department')
                        ->take(5)
                        ->get();
                    foreach ($absentEmpIds as $ae) {
                        $hasProfile = !empty($ae->profile_image) && file_exists(public_path($ae->profile_image));
                        $absentList[] = [
                            'name' => $ae->proper_name ?: ($ae->full_name ?: $ae->first_name),
                            'department' => $ae->employmentDetail?->department?->name ?? '—',
                            'code' => $ae->employee_code ?: '—',
                            'avatar' => $ae->employee_photo_url,
                            'has_avatar' => $hasProfile,
                        ];
                    }

                    // Pending expense items
                    $pendingExpenseItems = [];
                    $pendingExpenseRows = \App\Models\Expense::where('company_id', $currentCompanyId)
                        ->where('status', 'pending')
                        ->with('employees')
                        ->orderBy('id', 'desc')
                        ->take(4)
                        ->get();
                    foreach ($pendingExpenseRows as $ex) {
                        $emp = $ex->employees;
                        $pendingExpenseItems[] = [
                            'name' => $emp ? ($emp->proper_name ?: ($emp->full_name ?: $emp->first_name)) : '—',
                            'amount' => (float) ($ex->req_amount ?? 0),
                            'date' => $ex->date ? Carbon::parse($ex->date)->format('d M') : '—',
                        ];
                    }

                    // Seat / capacity utilization
                    $maxEmployees = (int) ($company->max_employee_user_count ?? 0);
                    $seatUsedPct = $maxEmployees > 0 ? min(100, round(($totalCompanyEmployees / $maxEmployees) * 100, 1)) : 0;

                    // Plan expiry
                    $planTo = null;
                    $planDaysLeft = null;
                    if (!empty($company->plan_to)) {
                        try {
                            $planTo = Carbon::parse($company->plan_to);
                            $planDaysLeft = (int) Carbon::today()->diffInDays($planTo, false);
                        } catch (\Exception $e) {
                        }
                    }

                    // Month leave applications approved
                    $monthLeavesApproved = LeaveApplication::where('company_id', $currentCompanyId)
                        ->where('status', 'approved')
                        ->whereBetween('fromdate_time', [$monthStart . ' 00:00:00', $monthEnd . ' 23:59:59'])
                        ->count();

                    // Late punch employee names (for filling attendance card)
                    $lateList = [];
                    foreach (($attendanceStats['late_punch_employees'] ?? []) as $lp) {
                        $lateList[] = [
                            'name' => $lp['employee_name'] ?? '—',
                            'time' => !empty($lp['punch_in_time']) ? Carbon::parse($lp['punch_in_time'])->format('h:i A') : '—',
                            'code' => $lp['employee_code'] ?? '—',
                        ];
                        if (count($lateList) >= 4) break;
                    }

                    // Designation headcount
                    $desigStats = [];
                    $desigColors = ['#2563eb', '#f28c28', '#0d9f6e', '#e11d48', '#7c3aed', '#0891b2'];
                    $designations = \App\Models\Designation::where('company_id', $currentCompanyId)->where('status', 'active')->get();
                    $dIdx = 0;
                    foreach ($designations as $des) {
                        $cnt = DB::table('employment_details')
                            ->join('employees', 'employees.id', '=', 'employment_details.employee_id')
                            ->where('employees.company_id', $currentCompanyId)
                            ->where('employees.status', 'active')
                            ->where('employment_details.designation_id', $des->id)
                            ->count();
                        if ($cnt > 0) {
                            $desigStats[] = [
                                'name' => $des->name,
                                'count' => $cnt,
                                'color' => $desigColors[$dIdx % count($desigColors)],
                            ];
                            $dIdx++;
                        }
                    }
                    usort($desigStats, fn($a, $b) => $b['count'] <=> $a['count']);
                    $desigStats = array_slice($desigStats, 0, 5);
                    $desigMax = !empty($desigStats) ? max(array_column($desigStats, 'count')) : 1;

                    // Not punched yet = active - present - leave
                    $notPunchedCount = max(0, $totalCompanyEmployees - $presentTodayCount - $onLeaveTodayCount);

                    // Strength vs capacity
                    $inactiveEmployees = Employee::where('company_id', $currentCompanyId)->where('status', 'inactive')->count();

                    $adminDashboardData = [
                        'admin_name' => $adminName,
                        'greeting' => $greeting,
                        'company_name' => $company ? $company->company_name : 'Ocean HR',
                        'company_email' => $company->email ?? null,
                        'company_phone' => $company->whatsapp_number ?? null,
                        'company_logo' => $company ? ($company->company_logo_url ?? null) : null,
                        'current_date' => $now->format('D, d M Y'),
                        'current_time' => $now->format('h:i:s A'),
                        'total_employees' => $totalCompanyEmployees,
                        'regular_employees' => $regularEmployeesCount,
                        'contractors' => $contractorsCount,
                        'new_this_month' => $newThisMonth,
                        'exits_this_month' => $exitsThisMonth,
                        'present_today' => $presentTodayCount,
                        'present_pct' => $presentPercentage,
                        'absent_today' => $absentTodayCount,
                        'absent_pct' => $absentPercentage,
                        'on_leave_today' => $onLeaveTodayCount,
                        'on_leave_pct' => $onLeavePercentage,
                        'late_today' => $lateTodayCount,
                        'early_go' => $earlyGoCount,
                        'pending_leaves' => $pendingLeavesCount,
                        'pending_expenses' => $pendingExpensesCount,
                        'pending_loans' => $pendingLoansCount,
                        'pending_approvals' => $pendingApprovalsTotal,
                        'pending_expense_amount' => $pendingExpenseAmount,
                        'month_expense_passed' => $monthExpensePassed,
                        'active_loans' => $activeLoansCount,
                        'active_loan_balance' => $activeLoanBalance,
                        'month_payroll' => $monthPayroll,
                        'month_contractor_pay' => $monthContractorPay,
                        'month_total_payroll' => $monthTotalPayroll,
                        'department_stats' => $deptStats,
                        'dept_max' => $deptMax,
                        'attendance_trend' => $attendanceTrend,
                        'trend_max' => $trendMax,
                        'today_live_attendance' => $liveAttendanceList,
                        'upcoming_leaves' => $upcomingLeavesList,
                        'pending_leave_items' => $pendingLeaveItems,
                        'pending_expense_items' => $pendingExpenseItems,
                        'birthdays_anniversaries' => $anniversariesList,
                        'branches_count' => $branchesCount,
                        'departments_count' => $departmentsCount,
                        'shifts_count' => $shiftsCount,
                        'designations_count' => $designationsCount,
                        'male_count' => $maleCount,
                        'female_count' => $femaleCount,
                        'other_gender_count' => $otherGenderCount,
                        'new_joiners' => $newJoinersList,
                        'absent_list' => $absentList,
                        'max_employees' => $maxEmployees,
                        'seat_used_pct' => $seatUsedPct,
                        'plan_to' => $planTo ? $planTo->format('d M Y') : null,
                        'plan_days_left' => $planDaysLeft,
                        'month_leaves_approved' => $monthLeavesApproved,
                        'late_list' => $lateList,
                        'designation_stats' => $desigStats,
                        'desig_max' => $desigMax,
                        'not_punched' => $notPunchedCount,
                        'inactive_employees' => $inactiveEmployees,
                    ];
                }

                $returnResponse = [];
                // dd("showNumericModule 413", $showNumericModule);
                $statisticsHtml = view('software._utils.dashboarad_inquiry_statistics', compact(
                    'request',
                    'showNumericModule',
                    'attendanceStats',
                    'operationStats',
                    'employeeStats',
                    'subordinateEmployees',
                    'departmentHeadsHierarchy',
                    'hierarchySupervisors',
                    'directEmployees',
                    'adminDashboardData',
                    'isCompanyAdmin',
                    'isEmployeeOnly'
                ))->render();

                $returnResponse['inquiryStatistics'] = $statisticsHtml;
                $returnResponse['showNumericModule'] = $showNumericModule;
                $returnResponse['attendanceStats'] = $attendanceStats;
                $returnResponse['employeeStats'] = $employeeStats;
                $returnResponse['subordinateEmployees'] = $subordinateEmployees;
                $returnResponse['departmentHeadsHierarchy'] = $departmentHeadsHierarchy ?? [];
                $returnResponse['hierarchySupervisors'] = $hierarchySupervisors ?? [];
                $returnResponse['directEmployees'] = $directEmployees ?? [];
                return $this->sendResponse($returnResponse, 'Statistics updated');

                dd("L-110 Dashboard Ajax");
            }
        } catch (\Exception $e) {
            abort(403, $e?->getMessage());
        }

        $today = Carbon::today()->format('Y-m-d');
        $currentEmployee = null;
        if (Auth::guard('employees')->check()) {
            $currentEmployee = Auth::guard('employees')->user();
        } elseif (Auth::guard('admin_software')->check()) {
            $adminUser = Auth::guard('admin_software')->user();
            $currentEmployee = Employee::where('email', $adminUser->email)->orWhere('username', $adminUser->username)->first();
            if (!$currentEmployee) {
                $selectedCompanyId = session('selected_company_id') ?? $adminUser->company_id;
                $currentEmployee = Employee::where('company_id', $selectedCompanyId)->first();
            }
        }

        $todayPunch = null;
        $punchState = 'out';
        $punchTimeFormatted = '';

        if ($currentEmployee) {
            $todayPunch = Attendance::where('employee_id', $currentEmployee->id)
                ->where('attendance_date', $today)
                ->orderBy('id', 'desc')
                ->first();
            if ($todayPunch && $todayPunch->attendace_type === 'in') {
                $punchState = 'in';
                $punchTimeFormatted = $todayPunch->punch_in_time ? Carbon::parse($todayPunch->punch_in_time)->format('h:i A') : '';
            }
        }

        return view('software.dashboard', compact('punchState', 'punchTimeFormatted', 'currentEmployee'));
    }

    public function punchAction(Request $request)
    {
        try {
            $employee = null;
            if (Auth::guard('employees')->check()) {
                $employee = Auth::guard('employees')->user();
            } elseif (Auth::guard('admin_software')->check()) {
                $adminUser = Auth::guard('admin_software')->user();
                $employee = Employee::where('email', $adminUser->email)->orWhere('username', $adminUser->username)->first();
                if (!$employee) {
                    $selectedCompanyId = session('selected_company_id') ?? $adminUser->company_id;
                    $employee = Employee::where('company_id', $selectedCompanyId)->first();
                }
            }

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active employee account found for this session.'
                ], 404);
            }

            $now = Carbon::now();
            $attendanceDate = $now->format('Y-m-d');
            $punchTime24 = $now->format('H:i:s');
            $punchTime12 = $now->format('h:i A');

            // Find last punch for today
            $lastPunch = Attendance::where('employee_id', $employee->id)
                ->where('attendance_date', $attendanceDate)
                ->orderBy('id', 'desc')
                ->first();

            $nextType = ($lastPunch && $lastPunch->attendace_type == 'in') ? 'out' : 'in';
            if ($request->filled('punch_type') && in_array($request->punch_type, ['in', 'out'])) {
                $nextType = $request->punch_type;
            }

            // Get Shift
            $shift_id = $employee->employmentDetail?->shift ?? 0;
            $shift = $shift_id ? Shift::find($shift_id) : Shift::where('company_id', $employee->company_id)->first();
            $shift_id = $shift ? $shift->id : 0;

            // Validate Late Punch on Punch-In
            if ($nextType === 'in' && $shift && !empty($shift->punch_in_minimum)) {
                $graceMin = (int)($shift->in_out_grace_period ?? $shift->grace_period ?? 0);
                $shiftStart = Carbon::parse($shift->punch_in_minimum);
                $cutoffTime = (clone $shiftStart)->addMinutes($graceMin);
                $currentTime = Carbon::parse($punchTime24);

                if ($currentTime->gt($cutoffTime)) {
                    $currentTimeFormatted = $currentTime->format('h:i A');
                    return response()->json([
                        'success' => false,
                        'message' => "Late punch is not allowed! Shift start time is " . $shiftStart->format('h:i A') . " (Allowed cutoff with {$graceMin} min grace period was " . $cutoffTime->format('h:i A') . "). Current time: {$currentTimeFormatted}."
                    ], 422);
                }
            }

            $attendance = Attendance::create([
                'company_id' => $employee->company_id,
                'employee_id' => $employee->id,
                'shift_id' => $shift_id,
                'attendance_date' => $attendanceDate,
                'create_date' => $now->toDateTimeString(),
                'punch_in_time' => $punchTime24,
                'attendace_type' => $nextType,
                'remark' => 'Dashboard Web Quick Punch',
                'status' => 'active',
                'records_source' => 'web_dashboard',
                'device_serial' => $request->header('User-Agent'),
                'device_ip' => $request->ip(),
                'created_by' => $employee->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => $nextType === 'in' ? 'Punched In successfully!' : 'Punched Out successfully!',
                'punch_state' => $nextType,
                'punch_time' => $punchTime12,
                'employee_name' => $employee->full_name ?? $employee->first_name
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getPunchStatus(Request $request)
    {
        try {
            $employee = null;
            if (Auth::guard('employees')->check()) {
                $employee = Auth::guard('employees')->user();
            } elseif (Auth::guard('admin_software')->check()) {
                $adminUser = Auth::guard('admin_software')->user();
                $employee = Employee::where('email', $adminUser->email)->orWhere('username', $adminUser->username)->first();
            }

            $today = Carbon::today()->format('Y-m-d');
            $punchState = 'out';
            $punchTimeFormatted = '';

            if ($employee) {
                $lastPunch = Attendance::where('employee_id', $employee->id)
                    ->where('attendance_date', $today)
                    ->orderBy('id', 'desc')
                    ->first();
                if ($lastPunch && $lastPunch->attendace_type === 'in') {
                    $punchState = 'in';
                    $punchTimeFormatted = $lastPunch->punch_in_time ? Carbon::parse($lastPunch->punch_in_time)->format('h:i A') : '';
                }
            }

            return response()->json([
                'success' => true,
                'punch_state' => $punchState,
                'punch_time' => $punchTimeFormatted,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }


    public function tracking_dashboard()
    {
        try {
            $modules = [];
            $modules['module_name'] = 'TrackingDashboard';

            $loginUser = null;
            if (Auth::guard('admin_software')->check()) {
                $loginUser = Auth::guard('admin_software')->user();
            } elseif (Auth::guard('employees')->check()) {
                $loginUser = Auth::guard('employees')->user();
            }

            $modules['viewPermission'] = Gate::check('hasPermission', ['view', $modules['module_name']]);
            $modules['addPermission'] = Gate::check('hasPermission', ['add', $modules['module_name']]);
            $modules['editPermission'] = Gate::check('hasPermission', ['update', $modules['module_name']]);
            $modules['deletePermission'] = Gate::check('hasPermission', ['delete', $modules['module_name']]);
            $modules['personalDataPermission'] = Gate::check('hasPermission', ['personal_data', $modules['module_name']]);
            $modules['allDataPermission'] = Gate::check('hasPermission', ['all_data', $modules['module_name']]);

            if (!$modules['viewPermission']) {
                abort(404);
            }

            return view('software.tracking-dashboard');
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }



    public function reports()
    {
        try {
            $modules = [];
            $modules['module_name'] = 'Reports';

            $loginUser = null;
            if (Auth::guard('admin_software')->check()) {
                $loginUser = Auth::guard('admin_software')->user();
            } elseif (Auth::guard('employees')->check()) {
                $loginUser = Auth::guard('employees')->user();
            }

            $modules['viewPermission'] = Gate::check('hasPermission', ['view', $modules['module_name']]);
            $modules['addPermission'] = Gate::check('hasPermission', ['add', $modules['module_name']]);
            $modules['editPermission'] = Gate::check('hasPermission', ['update', $modules['module_name']]);
            $modules['deletePermission'] = Gate::check('hasPermission', ['delete', $modules['module_name']]);
            $modules['personalDataPermission'] = Gate::check('hasPermission', ['personal_data', $modules['module_name']]);
            $modules['allDataPermission'] = Gate::check('hasPermission', ['all_data', $modules['module_name']]);

            if (!$modules['viewPermission']) {
                abort(403, 'Unauthorized');
            }

            return view('software.reports');
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function profile()
    {
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;
        $modules['currentGuard'] = ($this->currentGuard) ? $this->currentGuard : null;
        $modules['parent_type_id'] = ($this->authenticateLoginUserDetails?->parent_type_id) ? $this->authenticateLoginUserDetails?->parent_type_id : null;
        $loginUserId = ($modules['authLoginUserDetail'] && $modules['authLoginUserDetail']?->id) ? $modules['authLoginUserDetail']?->id : null;
        // dd($modules);
        try {
            View::share('modules', $modules);

            $user = Auth::user();

            if (!empty($user?->company_id)) {
                $company = Company::with(['plan'])->find($user->company_id);
            } else {
                $company = null;
            }

            if ($modules['currentGuard'] == 'employees' && $user instanceof Employee) {
                $user->loadMissing(['role', 'employmentDetail.department', 'employmentDetail.designation', 'branch', 'company']);
            }

            $userName = $user->proper_name ?? $user->full_name ?? $user->name ?? 'User';
            $roleName = $user->role->name ?? ($user->type ?? 'User');

            View::share('company', $company);
            View::share('userName', $userName);
            View::share('roleName', $roleName);
            View::share('profileIcon', $user->profile_photo ?? 'default.png');
            View::share('edit', $user);
            View::share('user', $user);

            return view('software.profile', compact('user', 'company', 'userName', 'roleName'));
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function permissions()
    {
        $user = Auth::user();
        return view('software.permissions-view');
    }

    public function updateProfile(Request $request)
    {
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['currentGuard'] = ($this->currentGuard) ? $this->currentGuard : null;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;
        $modules['parent_type_id'] = ($this->authenticateLoginUserDetails?->parent_type_id) ? $this->authenticateLoginUserDetails?->parent_type_id : null;
        $loginUserId = ($modules['authLoginUserDetail'] && $modules['authLoginUserDetail']?->id) ? $modules['authLoginUserDetail']?->id : null;

        try {
            View::share('modules', $modules);

            $request->validate([
                'old_password' => 'required',
                'new_password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/',
                ],
            ], [
                'new_password.confirmed' => 'New password and confirm password do not match.',
                'new_password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
            ], [
                'new_password' => 'New Password',
            ]);

            if (!Hash::check($request->old_password, $modules['authLoginUserDetail']->password)) {
                return back()->withErrors(['old_password' => 'Old password does not match our records.']);
            }

            if ($modules['currentGuard'] == 'admin_software') {
                $updateData = AdminSoftware::findOrFail($loginUserId);
                $validated['sp'] = $request->new_password;
                $validated['password'] = Hash::make($request->new_password);
                $validated['updated_by'] = $loginUserId;
                if ($updateData) {
                    unset($validated['id']);
                    $updateData->update($validated);
                }
            } else {
                $empQuery = Employee::query();
                if (!empty($modules['company_id'])) {
                    $empQuery->where('company_id', $modules['company_id']);
                }
                $updateData = $empQuery->findOrFail($loginUserId);
                $validated['sp'] = Helper::generateSP($request->new_password);
                $validated['password'] = Hash::make($request->new_password);
                $validated['updated_by'] = $loginUserId;
                if ($updateData) {
                    unset($validated['id']);
                    $updateData->update($validated);
                }
            }

            return Redirect::back()->withSuccess(' Password updated successfully.');
            //return back()->with('success', 'Password updated successfully.');
        } catch (\Exception $e) {
            return Redirect::back()->with('error', $e->getMessage())->withInput();
        }
    }


    public function updateProfileImage(Request $request)
    {
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;
        $modules['currentGuard'] = ($this->currentGuard) ? $this->currentGuard : null;
        $loginUserId = ($modules['authLoginUserDetail'] && $modules['authLoginUserDetail']?->id) ? $modules['authLoginUserDetail']?->id : null;
        try {
            $request->validate([
                'profile_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            if ($modules['currentGuard'] == 'admin_software') {
                $updateData = AdminSoftware::findOrFail($loginUserId);
            } else {
                $empQuery = Employee::query();
                if (!empty($modules['company_id'])) {
                    $empQuery->where('company_id', $modules['company_id']);
                }
                $updateData = $empQuery->findOrFail($loginUserId);
            }

            // ✅ Profile Image Upload
            if ($request->hasFile('profile_image')) {
                $company = Company::find($modules['company_id']);
                $company_name = $company->company_name ?? 'default-company';
                $company_slug = Str::slug($company_name);

                $file = $request->file('profile_image');
                $extension = $file->getClientOriginalExtension();
                $image_name = 'profile_' . $loginUserId . '_' . date('Ymd-His');
                $filename = $image_name . '.' . $extension;

                $year = now()->format('Y');
                $month = now()->format('m');
                $folder = "uploads/" . $modules['company_id'] . "-" . $company_slug . "/profile/{$year}-{$month}/";
                $uploadedPath = public_path($folder);

                if (!file_exists($uploadedPath)) {
                    mkdir($uploadedPath, 0777, true);
                }

                if ($file->move($uploadedPath, $filename)) {
                    $uploadedImage = $folder . $filename;
                    if (in_array($extension, ["jpeg", "png", "jpg"])) {
                        $webP = Helper::existingImageConverToWebp($extension, $uploadedPath, $filename, $image_name, 80, false);
                        if ($webP) {
                            $uploadedImage = $folder . $webP;
                        }
                    }
                    $updateData->profile_image = $uploadedImage;
                }
            }

            $updateData->save();

            return Redirect::back()->withSuccess('Profile image updated successfully.');
        } catch (\Exception $e) {
            return Redirect::back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Get today's attendance statistics
     */
    private function getTodayAttendanceStatistics($companyId, $startDate, $endDate)
    {
        try {
            $today = Carbon::parse($startDate)->format('Y-m-d');

            // Build base query
            $attendanceQuery = Attendance::where('attendance_date', $today);
            $employeeQuery = Employee::where('status', 'active');
            $leaveQuery = LeaveApplication::where('status', 'approved');

            if ($companyId) {
                $attendanceQuery->where('company_id', $companyId);
                $employeeQuery->where('company_id', $companyId);
                $leaveQuery->where('company_id', $companyId);
            }

            // Distinguish between regular employees and contractors using employee types
            $contractorTypeIds = \App\Models\EmployeeType::where('name', 'like', '%contract%')->orWhere('name', 'like', '%contractor%')->pluck('id');

            $regularEmployeeQuery = (clone $employeeQuery)
                ->where(function ($q) use ($contractorTypeIds) {
                    $q->whereHas('employmentDetail', function ($innerQ) use ($contractorTypeIds) {
                        $innerQ->whereNotIn('employment_type', $contractorTypeIds)
                            ->orWhereNull('employment_type');
                    })
                        ->orDoesntHave('employmentDetail');
                });
            $totalRegularEmployees = $regularEmployeeQuery->count();

            $contractorQuery = (clone $employeeQuery);
            if ($contractorTypeIds->isNotEmpty()) {
                $contractorQuery->whereHas('employmentDetail', function ($q) use ($contractorTypeIds) {
                    $q->whereIn('employment_type', $contractorTypeIds);
                });
            } else {
                $contractorQuery->whereRaw('1 = 0');
            }
            $totalContractors = $contractorQuery->count();

            // Total Employees count is ALL active employees (not just the two types)
            $totalEmployees = $employeeQuery->count();

            // Get employees with attendance (present) - those who have at least one punch (in or out)
            $presentEmployees = (clone $attendanceQuery)
                ->select('employee_id')
                ->distinct()
                ->count('employee_id');

            // Get employees on leave today
            $leaveEmployees = (clone $leaveQuery)
                ->where(function ($q) use ($today) {
                    $q->where(function ($sub) use ($today) {
                        $sub->whereNotNull('todate_time')
                            ->whereDate('fromdate_time', '<=', $today)
                            ->whereDate('todate_time', '>=', $today);
                    })->orWhere(function ($sub) use ($today) {
                        $sub->whereNull('todate_time')
                            ->whereDate('fromdate_time', '=', $today);
                    });
                })
                ->select('employee_id')
                ->distinct()
                ->count('employee_id');

            // Get late punch count - employees who punched in after (punch_in_minimum + in_out_grace_period)
            // UNIQUE PER EMPLOYEE: Count each employee only once per day (check first 'in' punch)
            $latePunchCount = 0;
            $latePunchEmployees = []; // Track unique employees to avoid duplicate counts
            $latePunchEmployeeList = []; // Store employee details for late punch list
            $attendanceInRecords = (clone $attendanceQuery)
                ->where('attendace_type', 'in')
                ->with(['employee.employmentDetail'])
                ->orderBy('punch_in_time', 'ASC') // Order by time ascending to get first punch first
                ->get()
                ->groupBy('employee_id'); // Group by employee to get all punches per employee

            foreach ($attendanceInRecords as $employeeId => $employeeRecords) {
                // Get the first (earliest) 'in' punch of the day for this employee
                $firstPunch = $employeeRecords->first();

                // Skip if already counted for late punch
                if (in_array($employeeId, $latePunchEmployees)) {
                    continue;
                }

                if ($firstPunch->employee && $firstPunch->employee->employmentDetail) {
                    $employmentDetail = $firstPunch->employee->employmentDetail;
                    $shiftId = $employmentDetail->shift ?? null;

                    if ($shiftId) {
                        $shift = Shift::find($shiftId);
                        if ($shift && $shift->punch_in_minimum) {
                            try {
                                // Parse shift start time and punch time relative to the same date
                                $shiftStartTimeStr = $shift->punch_in_minimum;
                                $punchTimeStr = $firstPunch->punch_in_time;

                                // Extract time part only if it's a datetime
                                if (strpos($punchTimeStr, ' ') !== false) {
                                    $parts = explode(' ', $punchTimeStr);
                                    $punchTimeStr = $parts[1];
                                }

                                $shiftStartTime = Carbon::parse($today . ' ' . $shiftStartTimeStr);
                                $punchTime = Carbon::parse($today . ' ' . $punchTimeStr);

                                // Use in_out_grace_period instead of grace_period
                                $gracePeriod = (int) ($shift->in_out_grace_period ?? 0);
                                $shiftStartTime->addMinutes($gracePeriod);

                                // Check if first punch is late
                                if ($punchTime->gt($shiftStartTime)) {
                                    $latePunchEmployees[] = $employeeId; // Track this employee
                                    $latePunchCount++;

                                    // Store employee details for list
                                    $latePunchEmployeeList[] = [
                                        'employee_id' => $employeeId,
                                        'company_id' => $firstPunch->employee->company_id,
                                        'employee_code' => $firstPunch->employee->employee_code ?? '-',
                                        'employee_name' => $firstPunch->employee->proper_name ?? '-',
                                        'punch_in_time' => $firstPunch->punch_in_time,
                                        'shift_name' => $shift->name ?? '-',
                                        'expected_time' => $shiftStartTime->format('H:i:s'),
                                    ];
                                }
                            } catch (\Exception $e) {
                                // Skip if time parsing fails
                            }
                        }
                    }
                }
            }

            // Get early go count - employees who punched out before (punch_out - in_out_grace_period)
            // CONDITION: Only count if employee did NOT punch in on time (was late or didn't punch in)
            // UNIQUE PER EMPLOYEE: Count each employee only once per day (check last 'out' punch)
            $earlyGoCount = 0;
            $earlyGoEmployees = []; // Track unique employees to avoid duplicate counts
            $attendanceOutRecords = (clone $attendanceQuery)
                ->where('attendace_type', 'out')
                ->whereNotNull('punch_in_time') // For 'out' records, punch_in_time stores the punch out time
                ->with(['employee.employmentDetail'])
                ->orderBy('punch_in_time', 'DESC') // Order by time descending to get last punch first
                ->get()
                ->groupBy('employee_id'); // Group by employee to get all punches per employee

            // Also get all 'in' records to check if employee punched in on time
            $attendanceInRecordsForEarlyGo = (clone $attendanceQuery)
                ->where('attendace_type', 'in')
                ->orderBy('punch_in_time', 'ASC')
                ->get()
                ->groupBy('employee_id');

            foreach ($attendanceOutRecords as $employeeId => $employeeRecords) {
                // Get the last (latest) 'out' punch of the day for this employee
                $lastPunch = $employeeRecords->first();

                // Skip if already counted for early go
                if (in_array($employeeId, $earlyGoEmployees)) {
                    continue;
                }

                if ($lastPunch->employee && $lastPunch->employee->employmentDetail) {
                    $employmentDetail = $lastPunch->employee->employmentDetail;
                    $shiftId = $employmentDetail->shift ?? null;

                    if ($shiftId) {
                        $shift = Shift::find($shiftId);
                        if ($shift && $shift->punch_out) {
                            try {
                                // Check if punch out is early
                                $punchOutTimeStr = $lastPunch->punch_in_time; // For 'out' records, punch_in_time is the punch out time
                                if (strpos($punchOutTimeStr, ' ') !== false) {
                                    $parts = explode(' ', $punchOutTimeStr);
                                    $punchOutTimeStr = $parts[1];
                                }

                                $shiftEndTime = Carbon::parse($today . ' ' . $shift->punch_out);
                                $punchOutTime = Carbon::parse($today . ' ' . $punchOutTimeStr);

                                // Use in_out_grace_period
                                $gracePeriod = (int) ($shift->in_out_grace_period ?? 0);
                                $shiftEndTime->subMinutes($gracePeriod);

                                // Check if punch out is early
                                $isPunchOutEarly = $punchOutTime->lt($shiftEndTime);

                                // Check if employee punched in on time (was NOT late)
                                $punchInOnTime = false;
                                if (isset($attendanceInRecordsForEarlyGo[$employeeId]) && $shift->punch_in_minimum) {
                                    $employeeInRecords = $attendanceInRecordsForEarlyGo[$employeeId];
                                    $firstInPunch = $employeeInRecords->first();

                                    if ($firstInPunch && $firstInPunch->punch_in_time) {
                                        $firstPunchInTimeStr = $firstInPunch->punch_in_time;
                                        if (strpos($firstPunchInTimeStr, ' ') !== false) {
                                            $parts = explode(' ', $firstPunchInTimeStr);
                                            $firstPunchInTimeStr = $parts[1];
                                        }

                                        $shiftStartTime = Carbon::parse($today . ' ' . $shift->punch_in_minimum);
                                        $firstPunchTime = Carbon::parse($today . ' ' . $firstPunchInTimeStr);

                                        $shiftStartTime->addMinutes($gracePeriod);

                                        // Employee punched in on time if first punch is <= (shift start + grace period)
                                        $punchInOnTime = $firstPunchTime->lte($shiftStartTime);
                                    }
                                }

                                // Early Go: Punch out is early AND employee did NOT punch in on time (was late or didn't punch in)
                                if ($isPunchOutEarly && !$punchInOnTime) {
                                    $earlyGoEmployees[] = $employeeId; // Track this employee
                                    $earlyGoCount++;
                                }
                            } catch (\Exception $e) {
                                // Skip if time parsing fails
                            }
                        }
                    }
                }
            }

            // Calculate absent: Total employees - Present - Leave
            $absentEmployees = max(0, $totalEmployees - $presentEmployees - $leaveEmployees);

            // Get absent employee list
            $presentEmployeeIds = (clone $attendanceQuery)
                ->select('employee_id')
                ->distinct()
                ->pluck('employee_id')
                ->toArray();

            $leaveEmployeeIds = (clone $leaveQuery)
                ->where(function ($q) use ($today) {
                    $q->where(function ($sub) use ($today) {
                        $sub->whereNotNull('todate_time')
                            ->whereDate('fromdate_time', '<=', $today)
                            ->whereDate('todate_time', '>=', $today);
                    })->orWhere(function ($sub) use ($today) {
                        $sub->whereNull('todate_time')
                            ->whereDate('fromdate_time', '=', $today);
                    });
                })
                ->select('employee_id')
                ->distinct()
                ->pluck('employee_id')
                ->toArray();

            $absentEmployeeIds = (clone $employeeQuery)
                ->whereNotIn('id', array_merge($presentEmployeeIds, $leaveEmployeeIds))
                ->pluck('id')
                ->toArray();

            // Get absent employee details
            $absentEmployeeList = [];
            if (!empty($absentEmployeeIds)) {
                $absentEmployeesData = Employee::whereIn('id', $absentEmployeeIds)
                    ->select('id', 'company_id', 'employee_code', 'full_name', 'middle_name')
                    ->get();

                foreach ($absentEmployeesData as $employee) {
                    $absentEmployeeList[] = [
                        'employee_id' => $employee->id,
                        'company_id' => $employee->company_id,
                        'employee_code' => $employee->employee_code ?? '-',
                        'employee_name' => $employee->proper_name ?? '-',
                    ];
                }
            }

            return [
                'present' => $presentEmployees,
                'absent' => $absentEmployees,
                'leave' => $leaveEmployees,
                'late_punch' => $latePunchCount,
                'early_go' => $earlyGoCount,
                'total_employees' => $totalEmployees,
                'total_regular_employees' => $totalRegularEmployees,
                'total_contractors' => $totalContractors,
                'date' => $today,
                'late_punch_employees' => $latePunchEmployeeList,
                'absent_employees' => $absentEmployeeList,
            ];
        } catch (\Exception $e) {
            return [
                'present' => 0,
                'absent' => 0,
                'leave' => 0,
                'late_punch' => 0,
                'early_go' => 0,
                'total_employees' => 0,
                'total_regular_employees' => 0,
                'total_contractors' => 0,
                'date' => $today ?? Carbon::today()->format('Y-m-d'),
                'late_punch_employees' => [],
                'absent_employees' => [],
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Format an employee's live attendance, punches, leave balances for dashboard display.
     */
    private function getEmployeeDashboardCardData(Employee $sub, string $todayDate, string $currMonthStart, string $currMonthEnd, $compLeaveTypes): array
    {
        $subId = $sub->id;

        // Today's punches
        $subTodayPunches = Attendance::where('employee_id', $subId)
            ->where('attendance_date', $todayDate)
            ->orderBy('id', 'asc')
            ->get();

        $subInPunch = $subTodayPunches->where('attendace_type', 'in')->first();
        $subOutPunch = $subTodayPunches->where('attendace_type', 'out')->last();
        $subLatestPunch = $subTodayPunches->last();

        $subPunchState = ($subLatestPunch && $subLatestPunch->attendace_type === 'in') ? 'in' : 'out';
        $subInTime = $subInPunch && $subInPunch->punch_in_time ? Carbon::parse($subInPunch->punch_in_time)->format('h:i A') : null;
        $subOutTime = $subOutPunch && $subOutPunch->punch_in_time ? Carbon::parse($subOutPunch->punch_in_time)->format('h:i A') : null;

        // Check approved leave today
        $subOnLeave = LeaveApplication::where('employee_id', $subId)
            ->where('status', 'approved')
            ->where(function ($q) use ($todayDate) {
                $q->where(function ($subQ) use ($todayDate) {
                    $subQ->whereNotNull('todate_time')
                        ->whereDate('fromdate_time', '<=', $todayDate)
                        ->whereDate('todate_time', '>=', $todayDate);
                })->orWhere(function ($subQ) use ($todayDate) {
                    $subQ->whereNull('todate_time')
                        ->whereDate('fromdate_time', '=', $todayDate);
                });
            })
            ->with('leave_type')
            ->first();

        if ($subOnLeave) {
            $subStatusType = 'leave';
            $subStatusLabel = 'On Leave';
            $subStatusClass = 'warning';
            $subStatusIcon = 'ti-calendar';
        } elseif ($subInPunch) {
            if ($subPunchState === 'in') {
                $subStatusType = 'present_in';
                $subStatusLabel = 'Present (IN)';
                $subStatusClass = 'success';
                $subStatusIcon = 'ti-check';
            } else {
                $subStatusType = 'present_out';
                $subStatusLabel = 'Punched OUT';
                $subStatusClass = 'secondary';
                $subStatusIcon = 'ti-logout';
            }
        } else {
            $subStatusType = 'not_punched';
            $subStatusLabel = 'Not Punched';
            $subStatusClass = 'danger';
            $subStatusIcon = 'ti-alert-circle';
        }

        // Current month present days & punch counts
        $subMonthPresent = Attendance::where('employee_id', $subId)
            ->whereBetween('attendance_date', [$currMonthStart, $currMonthEnd])
            ->where('attendace_type', 'in')
            ->distinct('attendance_date')
            ->count('attendance_date');

        $subTotalPunchIn = Attendance::where('employee_id', $subId)
            ->whereBetween('attendance_date', [$currMonthStart, $currMonthEnd])
            ->where('attendace_type', 'in')
            ->count();

        $subTotalPunchOut = Attendance::where('employee_id', $subId)
            ->whereBetween('attendance_date', [$currMonthStart, $currMonthEnd])
            ->where('attendace_type', 'out')
            ->count();

        // Leave balances summary
        $subLeaves = [];
        $colorPalettes = [
            ['bg' => 'rgba(115, 103, 240, 0.1)', 'border' => '#7367f0', 'text' => '#7367f0'],
            ['bg' => 'rgba(40, 199, 111, 0.1)', 'border' => '#28c76f', 'text' => '#28c76f'],
            ['bg' => 'rgba(0, 207, 232, 0.1)', 'border' => '#00cfe8', 'text' => '#00cfe8'],
            ['bg' => 'rgba(255, 159, 67, 0.1)', 'border' => '#ff9f43', 'text' => '#ff9f43'],
        ];
        $cIdx = 0;
        foreach ($compLeaveTypes as $lt) {
            $avail = $sub->getAvailableLeaveBalance($lt->id);
            $usedInFY = $sub->getUsedLeaveCountForReport($lt->id, (int) Carbon::now()->year, (int) Carbon::now()->month);
            $palette = $colorPalettes[$cIdx % count($colorPalettes)];
            $cIdx++;

            $subLeaves[] = [
                'id' => $lt->id,
                'name' => $lt->full_name,
                'code' => $lt->sort_name ?: substr($lt->full_name, 0, 4),
                'allocated' => (float) $lt->count,
                'balance' => (float) $avail,
                'used_year' => (float) $usedInFY,
                'carry_forward' => $lt->carry_forward == 1,
                'palette' => $palette,
            ];
        }

        $roleName = $sub->teamRole?->name ?? ($sub->current_role?->name ?? 'Employee');
        $desigName = $sub->employmentDetail?->designation?->name ?? $roleName;

        return [
            'id' => $sub->id,
            'employee' => $sub,
            'name' => $sub->proper_name ?: ($sub->full_name ?: $sub->first_name),
            'code' => $sub->employee_code ?: 'EMP-' . $sub->id,
            'department' => $sub->employmentDetail?->department?->name ?? '—',
            'designation' => $desigName,
            'role' => $roleName,
            'avatar' => $sub->employee_photo_url,
            'punch_state' => $subPunchState,
            'punch_in_time' => $subInTime,
            'punch_out_time' => $subOutTime,
            'status_type' => $subStatusType,
            'status_label' => $subStatusLabel,
            'status_class' => $subStatusClass,
            'status_icon' => $subStatusIcon,
            'month_present' => $subMonthPresent,
            'total_punch_in_count' => $subTotalPunchIn,
            'total_punch_out_count' => $subTotalPunchOut,
            'leave_balances' => $subLeaves,
        ];
    }
}
