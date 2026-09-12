<?php

use App\Http\Controllers\Api\MinopAttendanceController;
use App\Http\Controllers\Webhook\WebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Webhook Routes
|--------------------------------------------------------------------------
|
| This file handles webhook routes that accept raw data and return JSON only.
| All routes require webhook key authentication via middleware.
|
*/

// Apply middleware to all webhook routes
// 'handle.raw.data', 
Route::middleware(['webhook.key.auth'])->group(function () {
    
    // Example webhook endpoints
    Route::post('/receive', [WebhookController::class, 'receive']);

    // Existing webhook controller routes
    Route::post('/get-company-detail', [WebhookController::class, 'getCompanyDetail']);
    Route::post('/verify-user', [WebhookController::class, 'verifyStaticUser']);
    Route::post('/form-wise-fields', [WebhookController::class, 'formWiseFields']);

    // Biometric machine webhooks
    Route::post('/biometric-machine/add_edit', [WebhookController::class, 'biometricMachineAddEdit']);
    Route::post('/biometric-machine/list', [WebhookController::class, 'biometricMachineList']);

    // Test endpoint
    Route::get('/test', [WebhookController::class, 'test']);
}); 


/** Biometric providers */
// Say Hello endpoint
Route::post('biometric/minop/say-hello/{app_key?}', [MinopAttendanceController::class, 'say_hello'])->name('biometric.minop.say_hello');

// Devices sync endpoint - Minop Cloud should call this to register/refresh devices for a company
Route::post('biometric/devices/{biomax_company_name}', [MinopAttendanceController::class, 'devices'])->name('biometric.minop.devices');

// Attendance endpoint - Minop Cloud should POST attendance to this route. The {biomax_company_name} is optional but helps scope employee lookup.
Route::post('biometric/attendance/{biomax_company_name?}', [MinopAttendanceController::class, 'receive_attendance'])->name('biometric.minop.attendance');

Route::post('3rd-party/attendance', [MinopAttendanceController::class, 'attendance_receive_3rd_party'])->name('attendance.receive.3rd_party');