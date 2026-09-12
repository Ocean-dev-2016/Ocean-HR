<?php

use App\Http\Controllers\Api\AuthenticateController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\LeaveController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\EmployeeTypeController;
use App\Http\Controllers\Api\ApplicationVersionController;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\Api\NotificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::any('test-api', function () {
    return response()->json(['status' => true, 'message' => 'API is working!']);
});

Route::any('test', function () {
    return "API Test";
});

Route::controller(CommonController::class)->group(function () {
    Route::any('get-country', 'get_country')->name('get-country');
    Route::any('get-state', 'get_state')->name('get-state');
    Route::any('get-city', 'get_city')->name('get-city');
    Route::any('get-area', 'get_area')->name('get-area');
    Route::any('get-companies', 'get_companies')->name('get-companies');

    Route::any('get-warehouse', 'get_warehouse')->name('get-warehouse');

    Route::any('get-price-lists', 'get_price_lists')->name('get-price-lists');
    Route::get('get-location-by-pincode', [CommonController::class, 'getLocationByPincode'])->name('get-location-by-pincode');

    Route::any('get-designation', 'get_designation')->name('get-designation');
    Route::any('get-team-role', 'get_team_role')->name('get-team-role');
    Route::any('generate-employee-code', 'generate_employee_code')->name('generate-employee-code');
    Route::any('get-company-setting', 'get_company_setting')->name('get-company-setting');
    Route::any('check-pincode', 'check_pincode')->name('check-pincode');


    /** Master Module */
    Route::any('get-document-type', 'get_document_type')->name('get-document-type');

    Route::any('get-subscription-plan', 'get_subscription_plan')->name('get-subscription-plan');
    Route::any('get-plans', 'get_plans')->name('get-plans');

    Route::any('get-department', [CommonController::class, 'get_department'])->name('get-department');
    Route::any('get-branch', [CommonController::class, 'get_branch'])->name('get-branch');
    Route::any('get-assets', [CommonController::class, 'get_assets'])->name('get-assets');
    Route::any('get-loan-types', [CommonController::class, 'get_loan_types'])->name('get-loan-types');

    Route::any('get-employee', [CommonController::class, 'get_employee'])->name('get-employee');

    Route::any('get-employee-type', [CommonController::class, 'get_employee_type'])->name('get-employee-type');
    Route::any('get-shift', [CommonController::class, 'get_shift'])->name('get-shift');
    Route::any('get-subdepartment', [CommonController::class, 'get_subdepartment'])->name('get-subdepartment');
    Route::any('get-process', [CommonController::class, 'get_process'])->name('get-process');
    Route::any('get-employedetails', [CommonController::class, 'get_employedetails'])->name('get-employedetails');

    // routes/web.php or routes/api.php
    Route::post('/loan/calculation', [CommonController::class, 'loan_calculation'])->name('loan.calculation');
    Route::post('/salary/calculation', [CommonController::class, 'salary_calculation'])->name('salary.calculation');





});


/** Only use in application */
Route::post('verify-appkey', [AuthenticateController::class, 'verify_appkey'])->name('verify-appkey');
Route::post('login', [\App\Http\Controllers\Api\EmployeeAuthController::class, 'login'])->name('login');

Route::controller(ExpenseController::class)->group(function () {
    Route::POST('/expense/category/list', 'expense_category_list');
    Route::POST('/expense/sub-category/list', 'expense_sub_category_list');
});

Route::POST('/leave/type/list', [LeaveController::class, 'leave_type_list'])->name('api.leave_type.list');

Route::match(['get', 'post'], 'add-current-location', [CommonController::class, 'add_current_location'])->name('api.add_current_location');
Route::match(['get', 'post'], 'get-expo-category', [CommonController::class, 'get_expo_category'])->name('api.get_expo_category');
Route::match(['get', 'post'], 'get-expo-marketing-slider', [CommonController::class, 'get_expo_marketing_slider'])->name('api.get_expo_marketing_slider');


Route::group(['middleware' => ['auth:employee-api']], function () {
    Route::get('/get-profile', [\App\Http\Controllers\Api\EmployeeAuthController::class, 'getProfile']);
    Route::post('/get-profile', [\App\Http\Controllers\Api\EmployeeAuthController::class, 'getProfile']);
    Route::POST('/update-profile', [\App\Http\Controllers\Api\EmployeeAuthController::class, 'updateProfile']);
    Route::POST('/logout', [\App\Http\Controllers\Api\EmployeeAuthController::class, 'logout']);
    Route::match(['get', 'post'], 'add-current-location', [CommonController::class, 'add_current_location']);


    /** Attendances */
    Route::POST('/punch-in', [EmployeeController::class, 'punch_in']);
    Route::POST('/punch-out', [EmployeeController::class, 'punch_out']);
    Route::POST('/punch-in-out', [EmployeeController::class, 'punch_in_out']);
    Route::POST('/punch-history', [EmployeeController::class, 'punch_history']);
    Route::POST('/active-punch-in-records', [EmployeeController::class, 'active_punch_in_records']);

    /** Attendances */
    Route::POST('/leave/add_edit', [LeaveController::class, 'leave_add_edit'])->name('api.leave.add_edit');
    Route::POST('/leave/list', [LeaveController::class, 'leave_list'])->name('api.leave.list');
    Route::POST('/leave/delete', [LeaveController::class, 'leave_delete'])->name('api.leave.delete');

    Route::POST('/expense/add_edit', [ExpenseController::class, 'expense_add_edit'])->name('api.expense.add');
    Route::POST('/expense/list', [ExpenseController::class, 'expense_list'])->name('api.expense.list');
    Route::POST('/expense/delete', [ExpenseController::class, 'expense_delete'])->name('api.expense.delete');
    Route::POST('/expense/chart', [ExpenseController::class, 'expense_chart'])->name('api.expense.chart');
    Route::POST('/expense/status_update', [ExpenseController::class, 'expense_status_update'])->name('api.expense.status_update');

    Route::POST('/expense/category/add_edit', [ExpenseController::class, 'expense_category_add_edit']);
    Route::POST('/expense/category/delete', [ExpenseController::class, 'expense_category_delete']);
    Route::POST('/expense/sub-category/add_edit', [ExpenseController::class, 'expense_sub_category_add_edit']);
    Route::POST('/expense/sub-category/delete', [ExpenseController::class, 'expense_sub_category_delete']);

    Route::POST('/leave/available-leaves', [LeaveController::class, 'available_leave_list'])->name('api.leave.available_leaves');
    Route::POST('/leave/summary-report', [LeaveController::class, 'leave_summary_report'])->name('api.leave.summary_report');

    /** Notifications */
    Route::POST('/notification/list', [NotificationController::class, 'notification_list'])->name('api.notification.list');
    Route::POST('/notification/list-expense', [NotificationController::class, 'notification_expense_list'])->name('api.notification.expense_list');
    Route::POST('/notification/list-expance', [NotificationController::class, 'notification_expense_list'])->name('api.notification.expense_list_typo');
    Route::POST('/notification/mark-as-read', [NotificationController::class, 'mark_as_read'])->name('api.notification.mark_as_read');
    Route::POST('/notification/update-device-token', [NotificationController::class, 'update_device_token'])->name('api.notification.update_device_token');

    /** Application Version */
    Route::get('check-app-version', [ApplicationVersionController::class, 'checkVersion']);
});
