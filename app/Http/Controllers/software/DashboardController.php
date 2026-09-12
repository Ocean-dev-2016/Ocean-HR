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
                            $q->where('name', 'Company Payroll');
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


                $returnResponse = [];
                // dd("showNumericModule 413", $showNumericModule);
                $statisticsHtml = view('software._utils.dashboarad_inquiry_statistics', compact(
                    'request',
                    'showNumericModule',
                    'attendanceStats',
                    'operationStats'
                ))->render();

                $returnResponse['inquiryStatistics'] = $statisticsHtml;
                $returnResponse['showNumericModule'] = $showNumericModule;
                $returnResponse['attendanceStats'] = $attendanceStats;
                return $this->sendResponse($returnResponse, 'Statistics updated');

                dd("L-110 Dashboard Ajax");
            }
        } catch (\Exception $e) {
            abort(403, $e?->getMessage());
        }
        return view('software.dashboard');
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
                $company = Company::with(['plan'])->findOrFail($user->company_id);
            } else {
                $company = null;
            }
            View::share('company', $company);
            View::share('userName', $user->name);
            View::share('roleName', $user->role->name ?? 'User');
            View::share('profileIcon', $user->profile_photo ?? 'default.png');
            View::share('edit', $user);

            return view('software.profile');
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

            // Distinguish between regular employees and contractors using exact types from controllers
            $payrollTypeIds = \App\Models\EmployeeType::where('name', 'Company Payroll')->pluck('id');
            $contractorTypeIds = \App\Models\EmployeeType::where('name', 'Contractor Salary')->pluck('id');

            $regularEmployeeQuery = (clone $employeeQuery);
            if ($payrollTypeIds->isNotEmpty()) {
                $regularEmployeeQuery->whereHas('employmentDetail', function ($q) use ($payrollTypeIds) {
                    $q->whereIn('employment_type', $payrollTypeIds);
                });
            } else {
                $regularEmployeeQuery->whereRaw('1 = 0');
            }
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
}
