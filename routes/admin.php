<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CaseReportController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DoctorController;
use App\Http\Controllers\Admin\FileController;
use App\Http\Controllers\Admin\MasterController;
use App\Http\Controllers\Admin\PatientController;
use App\Http\Controllers\Admin\WhatsappController;

/*
|--------------------------------------------------------------------------
| Admin API Routes
|--------------------------------------------------------------------------
|
| Prefix: /api/admin
*/

Route::prefix('v1')->group(function () {

    // Public Routes
    Route::post('login', [AuthController::class, 'login']);
    Route::get('public/case-reports/{token}', [CaseReportController::class, 'showPublic']);

    // Protected Routes
    Route::middleware(['auth:sanctum'])->group(function () {

        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::put('me', [AuthController::class, 'updateProfile']);

        // Dashboard
        Route::get('dashboard', [DashboardController::class, 'index']);

        // Case Reports
        Route::get('case-reports', [CaseReportController::class, 'index']);
        Route::post('/case-reports', [CaseReportController::class, 'store']);
        Route::get('/case-reports/{id}', [CaseReportController::class, 'show']);
        Route::put('/case-reports/{id}', [CaseReportController::class, 'update']);
        Route::delete('/case-reports/{id}', [CaseReportController::class, 'destroy']);
        Route::post('/case-reports/{id}/status', [CaseReportController::class, 'updateStatus']);
        Route::post('/case-reports/{id}/whatsapp', [CaseReportController::class, 'notifyWhatsApp']);

        // File Uploads
        Route::post('files/upload', [FileController::class, 'upload']);

        // Patients
        Route::get('patients', [PatientController::class, 'index']);
        Route::post('patients', [PatientController::class, 'store']);
        Route::get('patients/{id}', [PatientController::class, 'show']);
        Route::put('patients/{id}', [PatientController::class, 'update']);
        Route::delete('patients/{id}', [PatientController::class, 'destroy']);
        Route::post('patients/{id}/status', [PatientController::class, 'updateStatus']);

        // Doctors
        Route::get('doctors', [DoctorController::class, 'index']);
        Route::post('doctors', [DoctorController::class, 'store']);
        Route::get('doctors/{id}', [DoctorController::class, 'show']);
        Route::put('doctors/{id}', [DoctorController::class, 'update']);
        Route::delete('doctors/{id}', [DoctorController::class, 'destroy']);
        Route::post('doctors/{id}/status', [DoctorController::class, 'updateStatus']);

        // WhatsApp Logs
        Route::get('whatsapp-logs/count', [WhatsappController::class, 'count']);
        Route::get('whatsapp-logs', [WhatsappController::class, 'index']);
        Route::post('whatsapp-logs/resend/{id}', [WhatsappController::class, 'resend']);

        // Masters
        Route::get('masters/scan-types', [MasterController::class, 'scanTypes']);
        Route::post('masters/scan-types', [MasterController::class, 'storeScanType']);
        Route::put('masters/scan-types/{id}', [MasterController::class, 'updateScanType']);
        Route::delete('masters/scan-types/{id}', [MasterController::class, 'destroyScanType']);
        Route::post('masters/scan-types/{id}/status', [MasterController::class, 'updateScanTypeStatus']);

        Route::get('masters/patients', [MasterController::class, 'patients']);
        Route::get('masters/doctors', [MasterController::class, 'doctors']);
        Route::get('masters/users', [MasterController::class, 'users']);

        Route::get('masters/genders', [MasterController::class, 'genders']);
        Route::post('masters/genders', [MasterController::class, 'storeGender']);
        Route::put('masters/genders/{id}', [MasterController::class, 'updateGender']);
        Route::delete('masters/genders/{id}', [MasterController::class, 'destroyGender']);
        Route::post('masters/genders/{id}/status', [MasterController::class, 'updateGenderStatus']);

        Route::get('masters/blood-groups', [MasterController::class, 'bloodGroups']);
        Route::post('masters/blood-groups', [MasterController::class, 'storeBloodGroup']);
        Route::put('masters/blood-groups/{id}', [MasterController::class, 'updateBloodGroup']);
        Route::delete('masters/blood-groups/{id}', [MasterController::class, 'destroyBloodGroup']);
        Route::post('masters/blood-groups/{id}/status', [MasterController::class, 'updateBloodGroupStatus']);

        Route::get('masters/titles', [MasterController::class, 'titles']);
        Route::post('masters/titles', [MasterController::class, 'storeTitle']);
        Route::put('masters/titles/{id}', [MasterController::class, 'updateTitle']);
        Route::delete('masters/titles/{id}', [MasterController::class, 'destroyTitle']);
        Route::post('masters/titles/{id}/status', [MasterController::class, 'updateTitleStatus']);
    });
});
