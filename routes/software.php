<?php

use App\Http\Controllers\software\AccountHeadController;
use App\Http\Controllers\software\AccountLeagerController;
use App\Http\Controllers\software\AttendanceReportController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\software\AttendanceController;
use App\Http\Controllers\software\CompanyController;
use App\Http\Controllers\software\CompanyRegistrationController;
use App\Http\Controllers\software\DashboardController;
use App\Http\Controllers\software\EmployeeAssignAssetsController;
use App\Http\Controllers\software\EmployeeIncrementDetailsController;
use App\Http\Controllers\software\EmployeeEducationExperienceDetailController;
use App\Http\Controllers\software\EmployeeWiseSalaryDetailController;
use App\Http\Controllers\software\SalaryController;
use App\Http\Controllers\software\SalaryCalculationController;
use App\Http\Controllers\software\IncentiveController;
use App\Http\Controllers\software\MasterStateController;
use App\Http\Controllers\software\MasterCityController;
use App\Http\Controllers\software\MasterCountryController;
use App\Http\Controllers\software\MasterAreaController;
use App\Http\Controllers\software\OutstandingReportController;
use App\Http\Controllers\software\PaymentReceiptController;
use App\Http\Controllers\software\RequestFormController;
use App\Http\Controllers\software\SalarySlipController;
use App\Http\Controllers\software\SoftwareAuthController;
use App\Http\Controllers\software\GoogleAuthController;
use App\Http\Controllers\software\LeaveTypeController;
use App\Http\Controllers\software\DesignationController;
use App\Http\Controllers\software\PlanMasterController;
use App\Http\Controllers\software\DocumentListController;
use App\Http\Controllers\software\DocumentTypeController;
use App\Http\Controllers\software\SubDepartmentController;
use App\Http\Controllers\software\TeamRoleController;
use App\Http\Controllers\software\ManageEmailController;
use App\Http\Controllers\software\CompanySubscriptionPlanController;
use App\Http\Controllers\software\NotificationController;
use App\Http\Controllers\software\UserController;
use App\Http\Controllers\software\ApplicationVersionController;
use App\Http\Controllers\software\AssetsAllocationMasterController;
use App\Http\Controllers\software\MissPunchReportController;
use App\Http\Controllers\software\LatePunchReportController;
use App\Http\Controllers\software\EarlyGoingReportController;
use App\Http\Controllers\software\PunchInRecordReportController;
use App\Http\Controllers\software\LeaveSummaryReportController;
use App\Http\Controllers\software\DailyAttendanceReportController;
use App\Http\Controllers\software\BonusController;
use App\Http\Controllers\software\BranchController;
use App\Http\Controllers\software\BiometricMachineController;
use App\Http\Controllers\software\DepartmentController;
use App\Http\Controllers\software\EmployeeTypeController;
use App\Http\Controllers\software\HolidayController;
use App\Http\Controllers\software\ReferenceMasterController;
use App\Http\Controllers\software\ShiftController;
use App\Http\Controllers\software\EmployeeController;
use App\Http\Controllers\software\OnboardingController;
use App\Http\Controllers\software\EmployeeDocumentController;
use App\Http\Controllers\software\EmployementDetailController;
use App\Http\Controllers\software\LeaveApplicationController;
use App\Http\Controllers\software\LoanController;
use App\Http\Controllers\software\LoanTypeController;
use App\Http\Controllers\software\ProcessController;
use App\Http\Controllers\software\ExpenseCategoryController;
use App\Http\Controllers\software\ExpenseSubCategoryController;
use App\Http\Controllers\software\ExpenseController;
use App\Http\Controllers\software\ContractProcessController;
use App\Http\Controllers\software\OperationRateController;
use App\Http\Controllers\software\OperationEntryController;
use App\Http\Controllers\software\ContractorLeaveApplicationController;
use App\Http\Controllers\software\ContractorEmployeeController;
use App\Http\Controllers\software\ContractorEmployementDetailController;
use App\Http\Controllers\software\OperationsRateListController;
use App\Http\Middleware\SoftwareAuthMiddleware;
use Illuminate\Support\Facades\Route;
// Route::prefix('/software')->group(function () {
// Route::get('/', [DashboardController::class, 'dashboard'])->name('software.landing');

/** Guest routes */
Route::GET('/login', [SoftwareAuthController::class, 'showLoginForm'])->name('software.login');
Route::POST('/login-submit', [SoftwareAuthController::class, 'submitLoginForm'])->name('software.submit.login');
Route::match(['get', 'post'], '/logout', [SoftwareAuthController::class, 'logout'])->name('software.logout');

// Google OAuth Authentication
Route::get('/auth/google/signin', [GoogleAuthController::class, 'redirectToGoogleSignIn'])->name('software.auth.google.signin');
Route::get('/auth/google/signup', [GoogleAuthController::class, 'redirectToGoogleSignUp'])->name('software.auth.google.signup');
Route::get('/auth/google', [GoogleAuthController::class, 'redirectToGoogle'])->name('software.auth.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback'])->name('software.auth.google.callback');
Route::get('/auth/google/verify-otp', [GoogleAuthController::class, 'showOtpForm'])->name('software.auth.google.verify-otp-form');
Route::post('/auth/google/verify-otp', [GoogleAuthController::class, 'verifyOtp'])->name('software.auth.google.verify-otp-submit');
Route::post('/auth/google/resend-otp', [GoogleAuthController::class, 'resendOtp'])->name('software.auth.google.resend-otp');
Route::get('/auth/google/company-setup', [GoogleAuthController::class, 'showCompanySetupForm'])->name('software.auth.google.company-setup-form');
Route::post('/auth/google/company-setup', [GoogleAuthController::class, 'submitCompanySetup'])->name('software.auth.google.company-setup-submit');

// Public Company Registration
Route::get('/register-company', [SoftwareAuthController::class, 'showCompanyRegisterForm'])->name('software.register.company');
Route::post('/register-company', [SoftwareAuthController::class, 'submitCompanyRegisterForm'])->name('software.register.company.submit');
Route::post('company/check-company-exists', [CompanyController::class, 'check_company_exists'])->name('company.check-company-exists');

// Forgot Password
Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('software.forgot.password');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('software.forgot.password.submit');
Route::get('reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
// Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
// Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])->name('password.update');
Route::post('/reset-password-direct', [ForgotPasswordController::class, 'resetPasswordDirect'])->name('software.reset.password.direct');


/** Protected routes */
Route::group(['middleware' => [SoftwareAuthMiddleware::class]], function () {

    /**
     * Start =====>  This route use for only Developer
     * */
    // manage email
    Route::resource('manage-email', ManageEmailController::class);
    Route::match(['get', 'post'], 'manage-email/restore/{id}', [ManageEmailController::class, 'restore'])->name('manage-email.restore');
    Route::match(['get', 'post'], 'manage-email-update-status', [ManageEmailController::class, 'status_update'])->name('manage-email.status-update');

    //Plan Master
    Route::resource('plan-master', PlanMasterController::class);
    Route::match(['get', 'post'], 'plan-master-update-status', [PlanMasterController::class, 'status_update'])->name('plan-master.status-update');
    Route::match(['get', 'post'], 'plan-master/restore/{id}', [PlanMasterController::class, 'restore'])->name('plan-master.restore');

    // Company
    Route::resource('company', CompanyController::class);
    Route::post('company/check-company-exists', [CompanyController::class, 'check_company_exists'])->name('company.check-company-exists');
    Route::match(['get', 'post'], 'company-update-status', [CompanyController::class, 'status_update'])->name('company.status-update');

    Route::match(['get', 'post'], 'company/mail-setting/{id}', [CompanyController::class, 'mail_setting'])->name('company.mail_setting');
    Route::match(['get', 'post'], 'company/license-setting/{id}', [CompanyController::class, 'license_setting'])->name('company.license_setting');
    Route::match(['get', 'post'], 'company/upgrade_plan/{id}', [CompanyController::class, 'upgrade_plan'])->name('company.upgrade_plan');
    Route::match(['get', 'post'], 'company/detail/{id}', [CompanyController::class, 'detail'])->name('company.detail');
    Route::match(['get', 'post'], 'set_company_session', [CompanyController::class, 'set_company_session'])->name('company.set_company_session');
    Route::match(['get', 'post'], 'verify_website_api_code', [CompanyController::class, 'verify_website_api_code'])->name('company.verify_website_api_code');
    Route::match(['get', 'post'], 'master_config/{platform?}/{id?}', [CompanyController::class, 'set_master_config'])->name('company.master_config');
    // Company Registration / Website Company Registration
    Route::match(['get', 'post'], 'website-company-registration-update-status', [CompanyRegistrationController::class, 'status_update'])->name('website-company-registration.status-update');
    Route::resource('website-company-registration', CompanyRegistrationController::class, ['names' => 'website-company-registration']);
    Route::resource('company-registration', CompanyRegistrationController::class, ['names' => 'software.company-registration']);


    // Sidebar Menu
    Route::match(['GET', 'POST'], 'sidebar-menu', [DashboardController::class, 'sidebar_menu'])->name('sidebar.menu');
    Route::delete('sidebar-menu-delete', [DashboardController::class, 'sidebar_menu_delete'])->name('sidebar.menu.delete');
    Route::match(['get', 'post'], 'permissions', [DashboardController::class, 'permissions'])->name('permissions.punchinout');

    Route::resource('company-subscription-plan', CompanySubscriptionPlanController::class);
    // Route::get('company-subscription-plan/{id}', [CompanySubscriptionPlanController::class, 'show'])->name('company-subscription-plan.show');
    Route::match(['get', 'post'], 'update_subscription_plan', [CompanySubscriptionPlanController::class, 'update_subscription_plan'])->name('company-subscription-plan.update_subscription_plan');
    Route::match(['get', 'post'], 'get_subscription_addons', [CompanySubscriptionPlanController::class, 'get_subscription_addons'])->name('company-subscription-plan.get_subscription_addons');


    Route::resource('user', UserController::class);
    Route::match(['get', 'post'], 'user-update-status', [UserController::class, 'status_update'])->name('user.status-update');
    Route::get('user/export/excel', [UserController::class, 'exportExcel'])->name('user.export.excel');
    Route::get('user/export/print', [UserController::class, 'print'])->name('user.print');

    Route::resource('application-version', ApplicationVersionController::class);

    /**
     * End =====>  This route use for only Developer
     * */

    // Dashboard Route
    Route::get('/dashboard/chart-data', [DashboardController::class, 'getChartData'])->name('dashboard.chart-data');

    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('software.dashboard');

    // Route::POST('/live-tracking-data', [TrackingDashboardController::class, 'live_tracking_data'])->name('live-tracking-data');

    Route::get('/reports', [DashboardController::class, 'reports'])->name('reports.index');

    Route::get('/profile', [DashboardController::class, 'profile'])->name('profile');
    Route::post('/dashboard/update-profile', [DashboardController::class, 'updateProfile'])->name('dashboard.updateProfile');
    Route::post('/dashboard/update-profile-image', [DashboardController::class, 'updateProfileImage'])->name('dashboard.updateProfileImage');

    Route::get('/dashboard/profile', [DashboardController::class, 'showProfile'])->name('dashboard.showProfile');

    // Region
    Route::resource('master-country', MasterCountryController::class);
    Route::match(['get', 'post'], 'master-country/restore/{id}', [MasterCountryController::class, 'restore'])->name('master-country.restore');
    Route::match(['get', 'post'], 'master-country-update-status', [MasterCountryController::class, 'status_update'])->name('master-country.status-update');
    Route::get('master-country/export/excel', [MasterCountryController::class, 'exportExcel'])->name('master-country.export.excel');
    Route::get('master-country/export/print', [MasterCountryController::class, 'print'])->name('master-country.print');

    // State
    Route::resource('master-state', MasterStateController::class);
    Route::match(['get', 'post'], 'master-state/restore/{id}', [MasterStateController::class, 'restore'])->name('master-state.restore');
    Route::match(['get', 'post'], 'master-state-update-status', [MasterStateController::class, 'status_update'])->name('master-state.status-update');
    Route::get('master-state/export/excel', [MasterStateController::class, 'exportExcel'])->name('master-state.export.excel');
    Route::get('master-state/export/print', [MasterStateController::class, 'print'])->name('master-state.print');

    //City
    Route::resource('master-city', MasterCityController::class);
    Route::match(['get', 'post'], 'master-city/restore/{id}', [MasterCityController::class, 'restore'])->name('master-city.restore');
    Route::match(['get', 'post'], 'master-city-update-status', [MasterCityController::class, 'status_update'])->name('master-city.status-update');
    Route::get('master-city/export/excel', [MasterCityController::class, 'exportExcel'])->name('master-city.export.excel');
    Route::get('master-city/export/print', [MasterCityController::class, 'print'])->name('master-city.print');

    //Area
    Route::resource('master-area', MasterAreaController::class);
    Route::match(['get', 'post'], 'master-area/restore/{id}', [MasterAreaController::class, 'restore'])->name('master-area.restore');
    Route::match(['get', 'post'], 'master-area-update-status', [MasterAreaController::class, 'status_update'])->name('master-area.status-update');
    Route::get('master-area/export/excel', [MasterAreaController::class, 'exportExcel'])->name('master-area.export.excel');
    Route::get('master-area/export/print', [MasterAreaController::class, 'print'])->name('master-area.print');


    Route::resource('designation', DesignationController::class);
    Route::match(['get', 'post'], 'designation/restore/{id}', [DesignationController::class, 'restore'])->name('designation.restore');
    Route::match(['get', 'post'], 'designation-update-status', [DesignationController::class, 'status_update'])->name('designation.status-update');
    Route::get('designation/export/excel', [DesignationController::class, 'exportExcel'])->name('designation.export.excel');
    Route::get('designation/export/print', [DesignationController::class, 'print'])->name('designation.print');


    // Document Type
    Route::resource('document-type', DocumentTypeController::class);
    Route::match(['get', 'post'], 'document-type/restore/{id}', [DocumentTypeController::class, 'restore'])->name('document-type.restore');
    Route::match(['get', 'post'], 'document-type-update-status', [DocumentTypeController::class, 'status_update'])->name('document-type.status-update');
    Route::get('document-type/export/excel', [DocumentTypeController::class, 'exportExcel'])->name('document-type.export.excel');
    Route::get('document-type/export/print', [DocumentTypeController::class, 'print'])->name('document-type.print');


    // DocumentList
    Route::resource('document-list', DocumentListController::class);
    Route::match(['get', 'post'], 'document-list/restore/{id}', [DocumentListController::class, 'restore'])->name('document-list.restore');
    Route::match(['get', 'post'], 'document-list-update-status', [DocumentListController::class, 'status_update'])->name('document-list.status-update');
    Route::get('document-list/export/excel', [DocumentListController::class, 'exportExcel'])->name('document-list.export.excel');
    Route::get('document-list/export/print', [DocumentListController::class, 'print'])->name('document-list.print');

    //Department
    Route::resource('departments', DepartmentController::class);
    Route::match(['get', 'post'], 'departments-update-status', [DepartmentController::class, 'status_update'])->name('departments.status-update');
    Route::match(['get', 'post'], 'departments/restore/{id}', [DepartmentController::class, 'restore'])->name('departments.restore');
    Route::get('departments/export/excel', [DepartmentController::class, 'exportExcel'])->name('departments.export.excel');
    Route::get('departments/export/print', [DepartmentController::class, 'print'])->name('departments.print');

    //Sub Department
    Route::resource('sub-department', SubDepartmentController::class);
    Route::match(['get', 'post'], 'sub-department-update-status', [SubDepartmentController::class, 'status_update'])->name('sub-department.status-update');
    Route::match(['get', 'post'], 'sub-department/restore/{id}', [SubDepartmentController::class, 'restore'])->name('sub-department.restore');
    Route::get('sub-department/export/excel', [SubDepartmentController::class, 'exportExcel'])->name('sub-department.export.excel');
    Route::get('sub-department/export/print', [SubDepartmentController::class, 'print'])->name('sub-department.print');

    //Process
    Route::resource('process', ProcessController::class);
    Route::match(['get', 'post'], 'process-update-status', [ProcessController::class, 'status_update'])->name('process.status-update');
    Route::match(['get', 'post'], 'process/restore/{id}', [ProcessController::class, 'restore'])->name('process.restore');
    Route::get('process/export/excel', [ProcessController::class, 'exportExcel'])->name('process.export.excel');
    Route::get('process/export/print', [ProcessController::class, 'print'])->name('process.print');

    //incentive
    Route::resource('incentive', IncentiveController::class);
    Route::match(['get', 'post'], 'incentive-update-status', [IncentiveController::class, 'status_update'])->name('incentive.status-update');
    Route::match(['get', 'post'], 'incentive/restore/{id}', [IncentiveController::class, 'restore'])->name('incentive.restore');
    Route::get('incentive/export/excel', [IncentiveController::class, 'exportExcel'])->name('incentive.export.excel');
    Route::get('incentive/export/print', [IncentiveController::class, 'print'])->name('incentive.print');

    // team-role
    Route::resource('team-role', TeamRoleController::class);
    Route::match(['get', 'post'], 'team-role-update-status', [TeamRoleController::class, 'status_update'])->name('team-role.status-update');
    Route::match(['get', 'post'], 'team-role/restore/{id}', [TeamRoleController::class, 'restore'])->name('team-role.restore');
    Route::match(['get'], 'team-role/assign-permission/{id}', [TeamRoleController::class, 'assign_permission'])->name('team-role.assign-permission');
    Route::match(['post'], 'team-role/store-permission', [TeamRoleController::class, 'store_permission'])->name('team-role.store-permission');


    Route::resource('notification', NotificationController::class);
    Route::match(['GET', 'POST'], 'testNotification', [DashboardController::class, 'testNotification'])->name('testNotification');
    Route::post('/store-token', [NotificationController::class, 'updateDeviceToken'])->name('store.token');
    Route::post('/send-notification', [NotificationController::class, 'sendNotification'])->name('send.web-notification');
    Route::match(['GET', 'POST'], 'get_notification', [NotificationController::class, 'getNotification'])->name('notification.get_notification');
    Route::match(['GET', 'POST'], 'clear', [NotificationController::class, 'clearNotification'])->name('notification.clear');


    // Branch master
    Route::resource('branches', BranchController::class);
    Route::match(['get', 'post'], 'branches-update-status', [BranchController::class, 'status_update'])->name('branches.status-update');
    Route::match(['get', 'post'], 'branches/restore/{id}', [BranchController::class, 'restore'])->name('branches.restore');
    Route::get('branches/export/excel', [BranchController::class, 'exportExcel'])->name('branches.export.excel');
    Route::get('branches/export/print', [BranchController::class, 'print'])->name('branches.print');

    // Product master
    Route::resource('products', \App\Http\Controllers\software\ProductController::class);
    Route::match(['get', 'post'], 'products-update-status', [\App\Http\Controllers\software\ProductController::class, 'status_update'])->name('products.status-update');
    Route::match(['get', 'post'], 'products/restore/{id}', [\App\Http\Controllers\software\ProductController::class, 'restore'])->name('products.restore');
    Route::get('products/export/excel', [\App\Http\Controllers\software\ProductController::class, 'exportExcel'])->name('products.export.excel');
    Route::get('products/export/print', [\App\Http\Controllers\software\ProductController::class, 'print'])->name('products.print');
    Route::get('products/get-extra-data', [\App\Http\Controllers\software\ProductController::class, 'get_extra_data'])->name('products.get-extra-data');

    // Grade master
    Route::resource('grades', \App\Http\Controllers\software\GradeController::class);
    Route::match(['get', 'post'], 'grades-update-status', [\App\Http\Controllers\software\GradeController::class, 'status_update'])->name('grades.status-update');
    Route::match(['get', 'post'], 'grades/restore/{id}', [\App\Http\Controllers\software\GradeController::class, 'restore'])->name('grades.restore');
    Route::get('grades/export/excel', [\App\Http\Controllers\software\GradeController::class, 'exportExcel'])->name('grades.export.excel');
    Route::get('grades/export/print', [\App\Http\Controllers\software\GradeController::class, 'print'])->name('grades.print');

    // Contract Process master
    Route::resource('contract-processes', ContractProcessController::class);
    Route::match(['get', 'post'], 'contract-processes-update-status', [ContractProcessController::class, 'status_update'])->name('contract-processes.status-update');
    Route::match(['get', 'post'], 'contract-processes/restore/{id}', [ContractProcessController::class, 'restore'])->name('contract-processes.restore');
    Route::get('contract-processes/export/excel', [ContractProcessController::class, 'exportExcel'])->name('contract-processes.export.excel');
    Route::get('contract-processes/export/print', [ContractProcessController::class, 'print'])->name('contract-processes.print');

    // Operations Rate List
    Route::match(['get', 'post'], 'operations-rate-list-update-status', [OperationsRateListController::class, 'status_update'])->name('operations-rate-list.status-update');
    Route::get('operations-rate-list/export/excel', [OperationsRateListController::class, 'exportExcel'])->name('operations-rate-list.export.excel');
    Route::get('operations-rate-list/export/print', [OperationsRateListController::class, 'print'])->name('operations-rate-list.print');
    Route::get('operations-rate-list/get-products-grades', [OperationsRateListController::class, 'get_products_grades'])->name('operations-rate-list.get-products-grades');
    Route::get('operations-rate-list/get-week-off-days', [OperationsRateListController::class, 'getWeekOffDays'])->name('operations-rate-list.get-week-off-days');
    Route::get('operations-rate-list/{id}/export-row-excel', [OperationsRateListController::class, 'exportRowExcel'])->name('operations-rate-list.export-row-excel');
    Route::get('operations-rate-list/{id}/group-employees', [OperationsRateListController::class, 'getGroupEmployees'])->name('operations-rate-list.group-employees');
    Route::post('operations-rate-list/generate-salary', [OperationsRateListController::class, 'generateSalary'])->name('operations-rate-list.generate-salary');
    Route::post('operations-rate-list/check-duplicate', [OperationsRateListController::class, 'checkDuplicate'])->name('operations-rate-list.check-duplicate');
    Route::resource('operations-rate-list', OperationsRateListController::class);

    // Biometric Machine master
    Route::resource('biometric-machines', BiometricMachineController::class);
    Route::match(['get', 'post'], 'biometric-machines-update-status', [BiometricMachineController::class, 'status_update'])->name('biometric-machines.status-update');
    Route::post('biometric-machines/{id}/test-connection', [BiometricMachineController::class, 'testConnection'])->name('biometric-machines.test-connection');
    Route::post('biometric-machines/{id}/sync-attendance', [BiometricMachineController::class, 'syncAttendance'])->name('biometric-machines.sync-attendance');
    Route::post('biometric-machines/{id}/toggle-active', [BiometricMachineController::class, 'toggleActive'])->name('biometric-machines.toggle-active');

    // Employee Type master
    Route::resource('employee-types', EmployeeTypeController::class);
    Route::match(['get', 'post'], 'employee-types-update-status', [EmployeeTypeController::class, 'status_update'])->name('employee-types.status-update');
    Route::match(['get', 'post'], 'employee-types/restore/{id}', [EmployeeTypeController::class, 'restore'])->name('employee-types.restore');
    Route::get('employee-types/export/excel', [EmployeeTypeController::class, 'exportExcel'])->name('employee-types.export.excel');
    Route::get('employee-types/export/print', [EmployeeTypeController::class, 'print'])->name('employee-types.print');

    // Leave master
    Route::resource('leave-type', LeaveTypeController::class);
    Route::match(['get', 'post'], 'leave-type-update-status', [LeaveTypeController::class, 'status_update'])->name('leave-type.status-update');
    Route::match(['get', 'post'], 'leave-type/restore/{id}', [LeaveTypeController::class, 'restore'])->name('leave-type.restore');
    Route::get('leave-type/export/excel', [LeaveTypeController::class, 'exportExcel'])->name('leave-type.export.excel');
    Route::get('leave-type/export/print', [LeaveTypeController::class, 'print'])->name('leave-type.print');

    // Leave Application
    Route::resource('leave-application', LeaveApplicationController::class);
    Route::post('/submit-leave-request', [LeaveApplicationController::class, 'submitLeaveRequest'])->name("submit-leavel-application");

    Route::match(['get', 'post'], 'leave-application-update-status', [LeaveApplicationController::class, 'status_update'])->name('leave-application.status-update');
    Route::match(['get', 'post'], 'leave-application/restore/{id}', [LeaveApplicationController::class, 'restore'])->name('leave-application.restore');
    Route::get('leave-application/export/excel', [LeaveApplicationController::class, 'exportExcel'])->name('leave-application.export.excel');
    Route::get('leave-application/export/print', [LeaveApplicationController::class, 'print'])->name('leave-application.print');

    // Contractor Leave Application
    Route::resource('contractor-leave-application', ContractorLeaveApplicationController::class);
    Route::match(['get', 'post'], 'contractor-leave-application-update-status', [ContractorLeaveApplicationController::class, 'status_update'])->name('contractor-leave-application.status-update');
    Route::match(['get', 'post'], 'contractor-leave-application/restore/{id}', [ContractorLeaveApplicationController::class, 'restore'])->name('contractor-leave-application.restore');
    Route::get('contractor-leave-application/export/excel', [ContractorLeaveApplicationController::class, 'exportExcel'])->name('contractor-leave-application.export.excel');
    Route::get('contractor-leave-application/export/print', [ContractorLeaveApplicationController::class, 'print'])->name('contractor-leave-application.print');

    // Holidays master
    Route::resource('holidays', HolidayController::class);
    Route::match(['get', 'post'], 'holidays-update-status', [HolidayController::class, 'status_update'])->name('holidays.status-update');
    Route::match(['get', 'post'], 'holidays/restore/{id}', [HolidayController::class, 'restore'])->name('holidays.restore');
    Route::get('holidays/export/excel', [HolidayController::class, 'exportExcel'])->name('holidays.export.excel');
    Route::get('holidays/export/print', [HolidayController::class, 'print'])->name('holidays.print');

    // Shift master
    Route::resource('shifts', ShiftController::class);
    Route::match(['get', 'post'], 'shifts-update-status', [ShiftController::class, 'status_update'])->name('shifts.status-update');
    Route::match(['get', 'post'], 'shifts/restore/{id}', [ShiftController::class, 'restore'])->name('shifts.restore');
    Route::get('shifts/export/excel', [ShiftController::class, 'exportExcel'])->name('shifts.export.excel');
    Route::get('shifts/export/print', [ShiftController::class, 'print'])->name('shifts.print');

    // reference master
    Route::resource('reference-master', ReferenceMasterController::class);
    Route::match(['get', 'post'], 'reference-master-update-status', [ReferenceMasterController::class, 'status_update'])->name('reference-master.status-update');
    Route::match(['get', 'post'], 'reference-master/restore/{id}', [ReferenceMasterController::class, 'restore'])->name('reference-master.restore');
    Route::get('reference-master/export/excel', [ReferenceMasterController::class, 'exportExcel'])->name('reference-master.export.excel');
    Route::get('reference-master/export/print', [ReferenceMasterController::class, 'print'])->name('reference-master.print');
    Route::post('reference-master/update-displayorder', [ReferenceMasterController::class, 'updateDisplayOrder'])->name('reference-master.update-displayorder');

    // assets-allocation-master

    Route::resource('assets-allocation-master', AssetsAllocationMasterController::class);
    Route::match(['get', 'post'], 'assets-allocation-master-update-status', [AssetsAllocationMasterController::class, 'status_update'])->name('assets-allocation-master.status-update');
    Route::match(['get', 'post'], 'assets-allocation-master/restore/{id}', [AssetsAllocationMasterController::class, 'restore'])->name('assets-allocation-master.restore');
    Route::get('assets-allocation-master/export/excel', [AssetsAllocationMasterController::class, 'exportExcel'])->name('assets-allocation-master.export.excel');
    Route::get('assets-allocation-master/export/print', [AssetsAllocationMasterController::class, 'print'])->name('assets-allocation-master.print');
    Route::post('assets-allocation-master/update-displayorder', [AssetsAllocationMasterController::class, 'updateDisplayOrder'])->name('assets-allocation-master.update-displayorder');

    //Bonus
    Route::resource('bonus', BonusController::class);
    Route::match(['get', 'post'], 'bonus-update-status', [BonusController::class, 'status_update'])->name('bonus.status-update');
    Route::match(['get', 'post'], 'bonus/restore/{id}', [BonusController::class, 'restore'])->name('bonus.restore');
    Route::get('bonus/export/excel', [BonusController::class, 'exportExcel'])->name('bonus.export.excel');
    Route::get('bonus/export/print', [BonusController::class, 'print'])->name('bonus.print');

    // Expense Category
    Route::resource('expense-category', ExpenseCategoryController::class);
    Route::match(['get', 'post'], 'expense-category-update-status', [ExpenseCategoryController::class, 'status_update'])->name('expense-category.status-update');
    Route::match(['get', 'post'], 'expense-category/restore/{id}', [ExpenseCategoryController::class, 'restore'])->name('expense-category.restore');
    Route::get('expense-category/export/excel', [ExpenseCategoryController::class, 'exportExcel'])->name('expense-category.export.excel');
    Route::get('expense-category/export/print', [ExpenseCategoryController::class, 'print'])->name('expense-category.print');

    // Expense SubCategory
    Route::resource('expense-subcategory', ExpenseSubCategoryController::class);
    Route::match(['get', 'post'], 'expense-subcategory-update-status', [ExpenseSubCategoryController::class, 'status_update'])->name('expense-subcategory.status-update');
    Route::match(['get', 'post'], 'expense-subcategory/restore/{id}', [ExpenseSubCategoryController::class, 'restore'])->name('expense-subcategory.restore');
    Route::get('expense-subcategory/export/excel', [ExpenseSubCategoryController::class, 'exportExcel'])->name('expense-subcategory.export.excel');
    Route::get('expense-subcategory/export/print', [ExpenseSubCategoryController::class, 'print'])->name('expense-subcategory.print');

    // Expense
    Route::resource('expense', ExpenseController::class);
    Route::match(['get', 'post'], 'expense-update-status', [ExpenseController::class, 'status_update'])->name('expense.status-update');
    Route::match(['get', 'post'], 'expense/restore/{id}', [ExpenseController::class, 'restore'])->name('expense.restore');
    Route::get('expense/export/excel', [ExpenseController::class, 'exportExcel'])->name('expense.export.excel');
    Route::get('expense/export/print', [ExpenseController::class, 'print'])->name('expense.print');

    // employee-master
    Route::get('employees/import', [EmployeeController::class, 'import'])->name('import-employee.index');
    Route::post('employees/import', [EmployeeController::class, 'import_store'])->name('import-employee.store');

    Route::resource('employees', EmployeeController::class);
    Route::match(['get', 'post'], 'employees-update-status', [EmployeeController::class, 'status_update'])->name('employees.status-update');
    Route::match(['get', 'post'], 'employees/restore/{id}', [EmployeeController::class, 'restore'])->name('employees.restore');
    Route::post('employees/update-resign-date', [EmployeeController::class, 'updateResignDate'])->name('employees.update-resign-date');
    Route::get('employees/export/excel', [EmployeeController::class, 'exportExcel'])->name('employees.export.excel');
    Route::get('employees/export/print', [EmployeeController::class, 'print'])->name('employees.print');
    Route::post('employees/update-displayorder', [EmployeeController::class, 'updateDisplayOrder'])->name('employees.update-displayorder');

    // Onboarding Module Routes
    Route::get('onboarding/role-template/{key}', [OnboardingController::class, 'getRoleTemplate'])->name('onboarding.role.template');
    Route::get('onboarding/{id}/edit/{tab}', [OnboardingController::class, 'edit'])->name('onboarding.edit.tab')->where('tab', 'basic-details|documents|jd-kra|trainings|assets|reporting');
    Route::get('onboarding/{id}/{tab}', [OnboardingController::class, 'show'])->name('onboarding.show.tab')->where('tab', 'basic-details|documents|jd-kra|trainings|assets|reporting');
    Route::resource('onboarding', OnboardingController::class);
    Route::post('onboarding/{id}/step1-update', [OnboardingController::class, 'updateBasicDetails'])->name('onboarding.step1.update');
    Route::post('onboarding/{id}/document-upload', [OnboardingController::class, 'uploadDocument'])->name('onboarding.document.upload');
    Route::post('onboarding/{id}/handbook-upload', [OnboardingController::class, 'uploadHandbook'])->name('onboarding.handbook.upload');
    Route::post('onboarding/{id}/document-verify', [OnboardingController::class, 'verifyDocument'])->name('onboarding.document.verify');
    Route::post('onboarding/{id}/step3-update', [OnboardingController::class, 'updateCompanyOverview'])->name('onboarding.step3.update');
    Route::post('onboarding/{id}/training-status', [OnboardingController::class, 'updateTrainingStatus'])->name('onboarding.training.status');
    Route::post('onboarding/{id}/asset-assign', [OnboardingController::class, 'assignAsset'])->name('onboarding.asset.assign');
    Route::post('onboarding/{id}/step6-finalize', [OnboardingController::class, 'assignReportingAndFinalize'])->name('onboarding.step6.finalize');
    Route::post('onboarding/{id}/convert-to-employee', [OnboardingController::class, 'convertToEmployee'])->name('onboarding.employee.convert');

    //Employee Assets Allocation Details
    Route::resource('employee-assign-assets', EmployeeAssignAssetsController::class);
    Route::match(['get', 'post'], 'employee-assign-assets-update-status', [EmployeeAssignAssetsController::class, 'status_update'])->name('employee-assign-assets.status-update');
    Route::match(['get', 'post'], 'employee-assign-assets/restore/{id}', [EmployeeAssignAssetsController::class, 'restore'])->name('employee-assign-assets.restore');
    Route::get('employee-assign-assets/export/excel', [EmployeeAssignAssetsController::class, 'exportExcel'])->name('employee-assign-assets.export.excel');
    Route::get('employee-assign-assets/export/print', [EmployeeAssignAssetsController::class, 'print'])->name('employee-assign-assets.print');

    Route::get('employee-documents/print/{company_id?}/{employee_id?}/{document_type?}', [EmployeeDocumentController::class, 'print'])->name('employee-documents.print');
    Route::get('employee-documents/{company_id?}/{employee_id?}/{document_type?}', [EmployeeDocumentController::class, 'index'])->name('employee-documents.index');

    // employee-increment-details
    Route::get('employee-increment-details/get-salary-details', [EmployeeIncrementDetailsController::class, 'getEmployeeSalaryDetails'])->name('employee-increment-details.get-salary-details');
    Route::resource('employee-increment-details', EmployeeIncrementDetailsController::class);
    Route::match(['get', 'post'], 'employee-increment-details-update-status', [EmployeeIncrementDetailsController::class, 'status_update'])->name('employee-increment-details.status-update');
    Route::match(['get', 'post'], 'employee-increment-details/restore/{id}', [EmployeeIncrementDetailsController::class, 'restore'])->name('employee-increment-details.restore');
    Route::get('employee-increment-details/export/excel', [EmployeeIncrementDetailsController::class, 'exportExcel'])->name('employee-increment-details.export.excel');
    Route::get('employee-increment-details/export/print', [EmployeeIncrementDetailsController::class, 'print'])->name('employee-increment-details.print');

    //Employee Details
    Route::resource('employment-details', EmployementDetailController::class);
    Route::match(['get', 'post'], 'employment-details-update-status', [EmployementDetailController::class, 'status_update'])->name('employment-details.status-update');
    Route::match(['get', 'post'], 'employment-details/restore/{id}', [EmployementDetailController::class, 'restore'])->name('employment-details.restore');
    Route::get('employment-details/export/excel', [EmployementDetailController::class, 'exportExcel'])->name('employment-details.export.excel');
    Route::get('employment-details/export/print', [EmployementDetailController::class, 'print'])->name('employment-details.print');

    // Contractor employee-master
    Route::resource('contractor-employees', ContractorEmployeeController::class);
    Route::match(['get', 'post'], 'contractor-employees-update-status', [ContractorEmployeeController::class, 'status_update'])->name('contractor-employees.status-update');
    Route::match(['get', 'post'], 'contractor-employees/restore/{id}', [ContractorEmployeeController::class, 'restore'])->name('contractor-employees.restore');
    Route::get('contractor-employees/export/excel', [ContractorEmployeeController::class, 'exportExcel'])->name('contractor-employees.export.excel');
    Route::get('contractor-employees/export/print', [ContractorEmployeeController::class, 'print'])->name('contractor-employees.print');
    Route::post('contractor-employees-update-resign-date', [ContractorEmployeeController::class, 'update_resign_date'])->name('contractor-employees.update-resign-date');

    // Contractor Employee Details
    Route::resource('contractor-employment-details', ContractorEmployementDetailController::class);
    Route::match(['get', 'post'], 'contractor-employment-details-update-status', [ContractorEmployementDetailController::class, 'status_update'])->name('contractor-employment-details.status-update');
    Route::match(['get', 'post'], 'contractor-employment-details/restore/{id}', [ContractorEmployementDetailController::class, 'restore'])->name('contractor-employment-details.restore');
    Route::get('contractor-employment-details/export/excel', [ContractorEmployementDetailController::class, 'exportExcel'])->name('contractor-employment-details.export.excel');
    Route::get('contractor-employment-details/export/print', [ContractorEmployementDetailController::class, 'print'])->name('contractor-employment-details.print');

    //Employee Assets Allocation Details
    Route::resource('employee-education-experience', EmployeeEducationExperienceDetailController::class);
    Route::match(['get', 'post'], 'employee-education-experience-update-status', [EmployeeEducationExperienceDetailController::class, 'status_update'])->name('employee-education-experience.status-update');
    Route::match(['get', 'post'], 'employee-education-experience/restore/{id}', [EmployeeEducationExperienceDetailController::class, 'restore'])->name('employee-education-experience.restore');
    Route::get('employee-education-experience/export/excel', [EmployeeEducationExperienceDetailController::class, 'exportExcel'])->name('employee-education-experience.export.excel');
    Route::get('employee-education-experience/export/print', [EmployeeEducationExperienceDetailController::class, 'print'])->name('employee-education-experience.print');

    //Attendance
    Route::get('attendance/import', [AttendanceController::class, 'import'])->name('import-attendance.index');
    Route::post('attendance/import', [AttendanceController::class, 'import_store'])->name('import-attendance.store');
    Route::get('attendance/import/status/{importId}', [AttendanceController::class, 'import_status'])->name('import-attendance.status');
    Route::get('attendance-import-history', [AttendanceController::class, 'attendance_import_files_index'])->name('attendance-import-history');
    Route::resource('attendance', AttendanceController::class);
    Route::match(['get', 'post'], 'attendance-update-status', [AttendanceController::class, 'status_update'])->name('attendance.status-update');
    Route::match(['get', 'post'], 'attendance/restore/{id}', [AttendanceController::class, 'restore'])->name('attendance.restore');
    Route::get('attendance/export/excel', [AttendanceController::class, 'exportExcel'])->name('attendance.export.excel');
    Route::get('attendance/export/print', [AttendanceController::class, 'print'])->name('attendance.print');
    Route::post('attendance/sync-biometric', [AttendanceController::class, 'syncBiometric'])->name('attendance.sync-biometric');

    //Attendance - Report
    Route::get('attendance-report/muster', [AttendanceReportController::class, 'musterIndex'])->name('attendance-report.muster');
    Route::post('attendance-report/get-muster-report', [AttendanceReportController::class, 'getMusterReport'])->name('attendance-report.getMusterReport');
    Route::resource('attendance-report', AttendanceReportController::class);
    Route::post('attendance-report/get-report', [AttendanceReportController::class, 'getAttendanceReport'])->name('attendance-report.getReport');
    Route::get('attendance-report/day-wise/{employee_id}/{date}', [AttendanceReportController::class, 'dayWiseDetails'])->name('attendance-report.day-wise');
    Route::post('attendance-report/get-punch-details', [AttendanceReportController::class, 'getPunchDetails'])->name('attendance-report.getPunchDetails');
    Route::get('attendance-report/export/excel', [AttendanceReportController::class, 'exportExcel'])->name('attendance-report.export.excel');
    Route::get('attendance-report/export/print', [AttendanceReportController::class, 'exportPrint'])->name('attendance-report.export.print');

    // Miss Punch Report
    Route::resource('miss-punch-report', MissPunchReportController::class);
    Route::post('miss-punch-report/get-report', [MissPunchReportController::class, 'getReport'])->name('miss-punch-report.getReport');
    Route::get('miss-punch-report/export/excel', [MissPunchReportController::class, 'exportExcel'])->name('miss-punch-report.export-excel');
    Route::get('miss-punch-report/export/print', [MissPunchReportController::class, 'print'])->name('miss-punch-report.print');

    // Late Punch Report
    Route::resource('late-punch-report', LatePunchReportController::class);
    Route::post('late-punch-report/get-report', [LatePunchReportController::class, 'getReport'])->name('late-punch-report.getReport');
    Route::get('late-punch-report/export/excel', [LatePunchReportController::class, 'exportExcel'])->name('late-punch-report.export-excel');
    Route::get('late-punch-report/export/print', [LatePunchReportController::class, 'print'])->name('late-punch-report.print');

    // Early Going Report
    Route::resource('early-going-report', EarlyGoingReportController::class);
    Route::post('early-going-report/get-report', [EarlyGoingReportController::class, 'getReport'])->name('early-going-report.getReport');
    Route::get('early-going-report/export/excel', [EarlyGoingReportController::class, 'exportExcel'])->name('early-going-report.export-excel');
    Route::get('early-going-report/export/print', [EarlyGoingReportController::class, 'print'])->name('early-going-report.print');

    // Punch-In Record Report
    Route::resource('punch-in-record-report', PunchInRecordReportController::class);
    Route::post('punch-in-record-report/get-report', [PunchInRecordReportController::class, 'getReport'])->name('punch-in-record-report.getReport');
    Route::get('punch-in-record-report/export/excel', [PunchInRecordReportController::class, 'exportExcel'])->name('punch-in-record-report.export-excel');
    Route::get('punch-in-record-report/export/print', [PunchInRecordReportController::class, 'print'])->name('punch-in-record-report.print');
    // Leave Summary Report
    Route::resource('leave-summary-report', LeaveSummaryReportController::class);
    Route::post('leave-summary-report/get-report', [LeaveSummaryReportController::class, 'getReport'])->name('leave-summary-report.getReport');
    Route::get('leave-summary-report/export/excel', [LeaveSummaryReportController::class, 'exportExcel'])->name('leave-summary-report.export-excel');
    Route::get('leave-summary-report/export/print', [LeaveSummaryReportController::class, 'print'])->name('leave-summary-report.print');

    // Daily Attendance Report
    Route::resource('daily-attendance-report', DailyAttendanceReportController::class);
    Route::post('daily-attendance-report/get-report', [DailyAttendanceReportController::class, 'getReport'])->name('daily-attendance-report.getReport');
    Route::get('daily-attendance-report/export/excel', [DailyAttendanceReportController::class, 'exportExcel'])->name('daily-attendance-report.export-excel');
    Route::get('daily-attendance-report/export/print', [DailyAttendanceReportController::class, 'print'])->name('daily-attendance-report.print');


    //Request
    Route::resource('request', RequestFormController::class);
    Route::match(['get', 'post'], 'request-update-status', [RequestFormController::class, 'status_update'])->name('request.status-update');
    Route::match(['get', 'post'], 'request/restore/{id}', [RequestFormController::class, 'restore'])->name('request.restore');
    Route::get('request/export/excel', [RequestFormController::class, 'exportExcel'])->name('request.export.excel');
    Route::get('request/export/print', [RequestFormController::class, 'print'])->name('request.print');

    // Loan Type
    Route::resource('loan-types', LoanTypeController::class);
    Route::match(['get', 'post'], 'loan-types-update-status', [LoanTypeController::class, 'status_update'])->name('loan-types.status-update');
    Route::match(['get', 'post'], 'loan-types/restore/{id}', [LoanTypeController::class, 'restore'])->name('loan-types.restore');
    Route::get('loan-types/export/excel', [LoanTypeController::class, 'exportExcel'])->name('loan-types.export.excel');
    Route::get('loan-types/export/print', [LoanTypeController::class, 'print'])->name('loan-types.print');
    Route::post('loan-types/update-displayorder', [LoanTypeController::class, 'updateDisplayOrder'])->name('loan-types.update-displayorder');

    // Loan
    Route::resource('loan', LoanController::class);
    Route::match(['get', 'post'], 'loan-update-status', [LoanController::class, 'status_update'])->name('loan.status-update');
    Route::match(['get', 'post'], 'loan/restore/{id}', [LoanController::class, 'restore'])->name('loan.restore');
    Route::get('loan/export/excel', [LoanController::class, 'exportExcel'])->name('loan.export.excel');
    Route::get('loan/export/print', [LoanController::class, 'print'])->name('loan.print');
    Route::post('loan/pay/installment/{loan_id}/{loan_repayment_id}', [LoanController::class, 'payInstallment'])->name('loan.pay.installment');

    //Employee Wise Salary Details

    Route::resource('employee-wise-salary-details', EmployeeWiseSalaryDetailController::class);
    Route::match(['get', 'post'], 'employee-wise-salary-details-update-status', [EmployeeWiseSalaryDetailController::class, 'status_update'])->name('employee-wise-salary-details.status-update');
    Route::match(['get', 'post'], 'employee-wise-salary-details/restore/{id}', [EmployeeWiseSalaryDetailController::class, 'restore'])->name('employee-wise-salary-details.restore');
    Route::get('employee-wise-salary-details/export/excel', [EmployeeWiseSalaryDetailController::class, 'exportExcel'])->name('employee-wise-salary-details.export.excel');
    Route::get('employee-wise-salary-details/export/print', [EmployeeWiseSalaryDetailController::class, 'print'])->name('employee-wise-salary-details.print');

    // Salary Slip

    Route::resource('salary-slip', SalarySlipController::class);
    Route::post('salary-slip/get-report', [SalarySlipController::class, 'getSlipReport'])->name('salary-slip.getReport');
    Route::get('salary-slip/export/excel', [SalarySlipController::class, 'exportExcel'])->name('salary-slip.export.excel');
    Route::get('salary-slip/export/print', [SalarySlipController::class, 'print'])->name('salary-slip.print');
    Route::get('salary-slip/export/pdf', [SalarySlipController::class, 'pdf'])->name('salary-slip.pdf');


    //Account Leager

    Route::get('account-ledger/print', [AccountLeagerController::class, 'print'])->name('account-ledger.print');
    Route::resource('account-ledger', AccountLeagerController::class);
    Route::match(['get', 'post'], 'account-ledger/restore/{id}', [AccountLeagerController::class, 'restore'])->name('account-ledger.restore');
    Route::match(['get', 'post'], 'account-ledger-update-status', [AccountLeagerController::class, 'status_update'])->name('account-ledger.status-update');
    Route::post('account-ledger/get-report', [AccountLeagerController::class, 'getLedgerReport'])->name('account-ledger.getReport');
    Route::get('account-ledger/export/excel', [AccountLeagerController::class, 'exportExcel'])->name('account-ledger.export.excel');
    Route::post('account-ledger/get-status', [AccountLeagerController::class, 'get_status'])->name('account-ledger.get-status');
    // Route::match(['get', 'post'], 'account-ledger/print', [AccountLeagerController::class, 'print'])->name('account-ledger.print');



    //OutStanding Leager

    Route::resource('outstanding-report', OutstandingReportController::class);
    Route::match(['get', 'post'], 'outstanding-report/restore/{id}', [OutstandingReportController::class, 'restore'])->name('outstanding-report.restore');
    Route::match(['get', 'post'], 'outstanding-report-update-status', [OutstandingReportController::class, 'status_update'])->name('outstanding-report.status-update');
    Route::get('outstanding-report/export/excel', [OutstandingReportController::class, 'exportExcel'])->name('outstanding-report.export.excel');
    Route::post('outstanding-report/get-status', [OutstandingReportController::class, 'get_status'])->name('outstanding-report.get-status');
    Route::get('outstanding-report/export/print', [OutstandingReportController::class, 'print'])->name('outstanding-report.print');
    Route::post('outstanding-report/fetch', [OutstandingReportController::class, 'fetch'])->name('outstanding-report.fetch');

    //payment Receipt

    Route::resource('payment-receipt', PaymentReceiptController::class);
    Route::match(['get', 'post'], 'payment-receipt/generate-receipt-no', [PaymentReceiptController::class, 'generateReceiptNo'])->name('payment-receipt.generate-receipt-no');
    Route::match(['get', 'post'], 'payment-receipt/restore/{id}', [PaymentReceiptController::class, 'restore'])->name('payment-receipt.restore');
    Route::match(['get', 'post'], 'payment-receipt-update-status', [PaymentReceiptController::class, 'status_update'])->name('payment-receipt.status-update');
    Route::get('payment-receipt/export/excel', [PaymentReceiptController::class, 'exportExcel'])->name('payment-receipt.export.excel');
    Route::post('payment-receipt/get-status', [PaymentReceiptController::class, 'get_status'])->name('payment-receipt.get-status');
    Route::get('payment-receipt/export/print', [PaymentReceiptController::class, 'print'])->name('payment-receipt.print');
    Route::get('payment-receipt/export/pdf/{id}', [PaymentReceiptController::class, 'pdf'])->name('payment-receipt.export.pdf');
    Route::match(['get', 'post'], 'get_payment_receipt_type', [PaymentReceiptController::class, 'get_payment_receipt_type'])->name('payment-receipt.get_payment_receipt_type');

    // Account Heading Ledger

    Route::resource('account-head', AccountHeadController::class);
    Route::match(['get', 'post'], 'account-head-update-status', [AccountHeadController::class, 'status_update'])->name('account-head.status-update');
    Route::match(['get', 'post'], 'account-head/restore/{id}', [AccountHeadController::class, 'restore'])->name('account-head.restore');
    Route::get('account-head/export/excel', [AccountHeadController::class, 'exportExcel'])->name('account-head.export.excel');
    Route::get('account-head/export/print', [AccountHeadController::class, 'print'])->name('account-head.print');
    Route::post('/account-head/ledger-data', [AccountHeadController::class, 'getLedgerData'])
        ->name('account.head.ledger.data');

    //Salary
    Route::resource('salaries', SalaryController::class);
    Route::match(['get', 'post'], 'salaries-update-status', [SalaryController::class, 'status_update'])->name('salaries.status-update');
    Route::match(['get', 'post'], 'salaries/restore/{id}', [SalaryController::class, 'restore'])->name('salaries.restore');
    Route::get('salaries/export/excel', [SalaryController::class, 'exportExcel'])->name('salaries.export.excel');
    Route::get('salaries/export/print', [SalaryController::class, 'print'])->name('salaries.print');
    Route::post('salaries/get-attendance-days', [SalaryController::class, 'getAttendanceDays'])->name('salaries.getAttendanceDays');

    // Salary Calculation - Company & Branch wise
    Route::resource('salary-calculation', SalaryCalculationController::class);
    Route::match(['get', 'post'], 'salary-calculation-update-status', [SalaryCalculationController::class, 'status_update'])->name('salary-calculation.status-update');
    Route::match(['get', 'post'], 'salary-calculation/restore/{id}', [SalaryCalculationController::class, 'restore'])->name('salary-calculation.restore');
    Route::get('salary-calculation/export/excel', [SalaryCalculationController::class, 'exportExcel'])->name('salary-calculation.export.excel');
    Route::get('salary-calculation/export/bank-transfer-excel', [SalaryCalculationController::class, 'exportBankTransferExcel'])->name('salary-calculation.export.bank-transfer-excel');
    Route::get('salary-calculation/export/print', [SalaryCalculationController::class, 'print'])->name('salary-calculation.print');
    Route::post('salary-calculation/calculate', [SalaryCalculationController::class, 'calculateSalary'])->name('salary-calculation.calculate');
    Route::post('salary-calculation/bulk-calculate', [SalaryCalculationController::class, 'bulkCalculateSalary'])->name('salary-calculation.bulk-calculate');
    Route::post('salary-calculation/get-employees', [SalaryCalculationController::class, 'getEmployeesByDepartment'])->name('salary-calculation.get-employees');
    Route::post('salary-calculation/attendance-summary', [SalaryCalculationController::class, 'getAttendanceSummary'])->name('salary-calculation.attendance-summary');
    Route::post('salary-calculation/check-integrity', [SalaryCalculationController::class, 'checkDataIntegrity'])->name('salary-calculation.check-integrity');
    Route::post('salary-calculation/lock', [SalaryCalculationController::class, 'lockSalary'])->name('salary-calculation.lock');
    Route::post('salary-calculation/unlock', [SalaryCalculationController::class, 'unlockSalary'])->name('salary-calculation.unlock');

});

// });

Route::get('/software', function () {
    return redirect('software/dashboard', 301);
});
