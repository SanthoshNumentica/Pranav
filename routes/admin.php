<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CaseReportController;
use App\Http\Controllers\Admin\FileController;
use App\Http\Controllers\Admin\PatientController;
use App\Http\Controllers\Admin\DoctorController;
use App\Http\Controllers\Admin\MasterController;

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

    // Protected Routes
    Route::middleware(['auth:sanctum'])->group(function () {

        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::put('me', [AuthController::class, 'updateProfile']);

        // Case Reports
        Route::get('case-reports', [CaseReportController::class, 'index']);
        Route::post('/case-reports', [CaseReportController::class, 'store']);
        Route::get('/case-reports/{id}', [CaseReportController::class, 'show']);
        Route::put('/case-reports/{id}', [CaseReportController::class, 'update']);
        Route::post('/case-reports/{id}/status', [CaseReportController::class, 'updateStatus']);

        // File Uploads
        Route::post('files/upload', [FileController::class, 'upload']);

        // Patients
        Route::get('patients', [PatientController::class, 'index']);
        Route::get('patients/{id}', [PatientController::class, 'show']);

        // Doctors
        Route::get('doctors', [DoctorController::class, 'index']);
        Route::get('doctors/{id}', [DoctorController::class, 'show']);

        // Masters
        Route::get('masters/scan-types', [MasterController::class, 'scanTypes']);
        Route::get('masters/patients', [MasterController::class, 'patients']);
        Route::get('masters/doctors', [MasterController::class, 'doctors']);
        Route::get('masters/users', [MasterController::class, 'users']);
    });
});
