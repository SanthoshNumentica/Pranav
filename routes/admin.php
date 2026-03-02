<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CaseReportController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\RefererController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\FileController;
use App\Http\Controllers\Admin\MasterController;
use App\Http\Controllers\Admin\PatientController;
use App\Http\Controllers\Admin\WhatsappController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PrintController;

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
    Route::get('print/{type}/{id}', [PrintController::class, 'print']);

    // Protected Routes
    Route::middleware(['auth:sanctum'])->group(function () {

        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::put('me', [AuthController::class, 'updateProfile']);

        // Dashboard
        Route::get('dashboard', [DashboardController::class, 'index']);

        // Case Reports
        Route::get('case-reports/next-id', [CaseReportController::class, 'getNextCaseId']);
        Route::get('case-report-items/next-id/{scan_type_id}', [CaseReportController::class, 'getNextItemCustomId']);
        Route::get('case-reports', [CaseReportController::class, 'index']);
        Route::post('/case-reports', [CaseReportController::class, 'store']);
        Route::get('/case-reports/{id}', [CaseReportController::class, 'show']);
        Route::put('/case-reports/{id}', [CaseReportController::class, 'update']);
        Route::delete('/case-reports/{id}', [CaseReportController::class, 'destroy']);
        Route::post('/case-reports/{id}/status', [CaseReportController::class, 'updateStatus']);
        Route::post('/case-reports/{id}/whatsapp', [CaseReportController::class, 'notifyWhatsApp']);

        // File Uploads
        Route::post('files/upload', [FileController::class, 'upload']);
        Route::delete('files/delete', [FileController::class, 'destroy']);

        // Patients
        Route::get('patients', [PatientController::class, 'index']);
        Route::post('patients', [PatientController::class, 'store']);
        Route::get('patients/{id}', [PatientController::class, 'show']);
        Route::put('patients/{id}', [PatientController::class, 'update']);
        Route::delete('patients/{id}', [PatientController::class, 'destroy']);
        Route::post('patients/{id}/status', [PatientController::class, 'updateStatus']);

        // Referers
        Route::get('referers', [RefererController::class, 'index']);
        Route::post('referers', [RefererController::class, 'store']);
        Route::put('referers/{id}', [RefererController::class, 'update']);
        Route::delete('referers/{id}', [RefererController::class, 'destroy']);
        Route::post('referers/{id}/status', [RefererController::class, 'updateStatus']);

        // Users
        Route::get('users', [UserController::class, 'index']);
        Route::post('users', [UserController::class, 'store']);
        Route::get('users/{user}', [UserController::class, 'show']);
        Route::put('users/{user}', [UserController::class, 'update']);
        Route::delete('users/{user}', [UserController::class, 'destroy']);
        Route::post('users/{user}/status', [UserController::class, 'updateStatus']);

        // Roles
        Route::get('roles', [RoleController::class, 'index']);
        Route::post('roles', [RoleController::class, 'store']);
        Route::get('roles/{id}', [RoleController::class, 'show']);
        Route::put('roles/{id}', [RoleController::class, 'update']);
        Route::delete('roles/{id}', [RoleController::class, 'destroy']);

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
        Route::get('masters/referers', [RefererController::class, 'index']); // Reusing index as it already returns list
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

        Route::get('masters/referer-types', [MasterController::class, 'refererTypes']);
        Route::post('masters/referer-types', [MasterController::class, 'storeRefererType']);
        Route::put('masters/referer-types/{id}', [MasterController::class, 'updateRefererType']);
        Route::delete('masters/referer-types/{id}', [MasterController::class, 'destroyRefererType']);
        Route::post('masters/referer-types/{id}/status', [MasterController::class, 'updateRefererTypeStatus']);

        Route::get('masters/discounts', [MasterController::class, 'discounts']);
        Route::post('masters/discounts', [MasterController::class, 'storeDiscount']);
        Route::put('masters/discounts/{id}', [MasterController::class, 'updateDiscount']);
        Route::delete('masters/discounts/{id}', [MasterController::class, 'destroyDiscount']);
        Route::post('masters/discounts/{id}/status', [MasterController::class, 'updateDiscountStatus']);

        // Invoices
        Route::get('invoices/next-no', [InvoiceController::class, 'getNextInvoiceNo']);
        Route::apiResource('invoices', InvoiceController::class);

        // Payments
        Route::apiResource('payments', PaymentController::class)->only(['index', 'store']);

        // Master Data for Invoices
        Route::get('masters/payment-methods', [MasterController::class, 'paymentMethods']);
        Route::post('masters/payment-methods', [MasterController::class, 'storePaymentMethod']);
        Route::put('masters/payment-methods/{id}', [MasterController::class, 'updatePaymentMethod']);
        Route::delete('masters/payment-methods/{id}', [MasterController::class, 'destroyPaymentMethod']);
        Route::post('masters/payment-methods/{id}/status', [MasterController::class, 'updatePaymentMethodStatus']);

        // Branches
        Route::post('branches/{id}/status', [BranchController::class, 'updateStatus']);
        Route::get('branches/next-code', [BranchController::class, 'getNextCode']);
        Route::apiResource('branches', BranchController::class);

        Route::get('masters/roles', [MasterController::class, 'roles']);
        Route::get('masters/branches', [UserController::class, 'branches']);
    });
});
