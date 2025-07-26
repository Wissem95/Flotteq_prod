<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Health check
Route::get('/health', fn() => ['status' => 'ok', 'timestamp' => now()]);

// Public auth routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [App\Http\Controllers\API\AuthController::class, 'login']);
    Route::post('/register', [App\Http\Controllers\API\AuthController::class, 'register']);

    // Google OAuth routes
    Route::post('/google/redirect', [App\Http\Controllers\API\SocialAuthController::class, 'redirectToGoogle']);
    Route::get('/google/callback', [App\Http\Controllers\API\SocialAuthController::class, 'handleGoogleCallback']);

    // Password reset routes
    Route::post('/password/send-code', [App\Http\Controllers\API\PasswordResetController::class, 'sendResetCode']);
    Route::post('/password/verify-code', [App\Http\Controllers\API\PasswordResetController::class, 'verifyResetCode']);

    // Verification routes
    Route::post('/verification/send-code', [App\Http\Controllers\API\VerificationController::class, 'sendVerificationCode']);
    Route::post('/verification/verify-code', [App\Http\Controllers\API\VerificationController::class, 'verifyCode']);
    Route::get('/verification/status', [App\Http\Controllers\API\VerificationController::class, 'checkVerificationStatus']);
});

// Profile routes (without tenant middleware - handled manually in controller)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/profile/me', [App\Http\Controllers\API\AuthController::class, 'me']);
    Route::put('/profile/me', [App\Http\Controllers\API\AuthController::class, 'updateProfile']);
});

// Protected tenant-aware routes
Route::middleware(['auth:sanctum', 'tenant'])->group(function () {
    Route::prefix('auth')->group(function () {
        Route::get('/me', [App\Http\Controllers\API\AuthController::class, 'me']);
        Route::post('/logout', [App\Http\Controllers\API\AuthController::class, 'logout']);

        // Google OAuth protected routes
        Route::post('/google/link', [App\Http\Controllers\API\SocialAuthController::class, 'linkGoogleAccount']);
        Route::post('/google/unlink', [App\Http\Controllers\API\SocialAuthController::class, 'unlinkGoogleAccount']);
    });

    // Vehicle routes
    Route::get('/vehicles/history', [App\Http\Controllers\API\VehicleController::class, 'history']);
    Route::apiResource('vehicles', App\Http\Controllers\API\VehicleController::class);

    // Media routes for vehicles
    Route::prefix('vehicles/{vehicle}/media')->group(function () {
        Route::get('/', [App\Http\Controllers\API\MediaController::class, 'getVehicleMedia']);
        Route::post('/image', [App\Http\Controllers\API\MediaController::class, 'uploadVehicleImage']);
        Route::post('/document', [App\Http\Controllers\API\MediaController::class, 'uploadVehicleDocument']);
        Route::post('/multiple', [App\Http\Controllers\API\MediaController::class, 'uploadMultiple']);
    });

    // General media routes
    Route::prefix('media')->group(function () {
        Route::get('/{media}/download', [App\Http\Controllers\API\MediaController::class, 'downloadMedia']);
        Route::delete('/{media}', [App\Http\Controllers\API\MediaController::class, 'deleteMedia']);
    });

    // Facture routes
    Route::apiResource('factures', App\Http\Controllers\API\FactureController::class);

    // Maintenance routes
    Route::apiResource('maintenances', App\Http\Controllers\API\MaintenanceController::class);

    // Analytics routes
    Route::prefix('analytics')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\API\AnalyticsController::class, 'getDashboardStats']);
        Route::get('/vehicles', [App\Http\Controllers\API\AnalyticsController::class, 'getVehicleStats']);
        Route::get('/users', [App\Http\Controllers\API\AnalyticsController::class, 'getUserStats']);
        Route::get('/system', [App\Http\Controllers\API\AnalyticsController::class, 'getSystemHealth']);
        Route::post('/export', [App\Http\Controllers\API\AnalyticsController::class, 'exportAnalytics']);
    });

    // Finances routes
    Route::prefix('finances')->group(function () {
        Route::get('/overview', [App\Http\Controllers\API\FinancesController::class, 'getOverview']);
        Route::get('/monthly-chart', [App\Http\Controllers\API\FinancesController::class, 'getMonthlyChart']);
        Route::get('/expense-breakdown', [App\Http\Controllers\API\FinancesController::class, 'getExpenseBreakdown']);
        Route::get('/top-expensive-vehicles', [App\Http\Controllers\API\FinancesController::class, 'getTopExpensiveVehicles']);
        Route::get('/maintenance-stats', [App\Http\Controllers\API\FinancesController::class, 'getMaintenanceStats']);
        Route::get('/expense-history', [App\Http\Controllers\API\FinancesController::class, 'getExpenseHistory']);
        Route::get('/alerts', [App\Http\Controllers\API\FinancesController::class, 'getFinancialAlerts']);
    });

    // Transactions routes
    Route::prefix('transactions')->group(function () {
        Route::get('/overview', [App\Http\Controllers\API\TransactionsController::class, 'getOverview']);
        Route::get('/vehicle-analysis', [App\Http\Controllers\API\TransactionsController::class, 'getVehicleAnalysis']);
        Route::get('/history', [App\Http\Controllers\API\TransactionsController::class, 'getHistory']);
        Route::post('/', [App\Http\Controllers\API\TransactionsController::class, 'store']);
        Route::put('/{transaction}', [App\Http\Controllers\API\TransactionsController::class, 'update']);
        Route::delete('/{transaction}', [App\Http\Controllers\API\TransactionsController::class, 'destroy']);
    });

    // Notification routes
    Route::prefix('notifications')->group(function () {
        Route::get('/', [App\Http\Controllers\API\NotificationController::class, 'index']);
        Route::get('/counts', [App\Http\Controllers\API\NotificationController::class, 'getCounts']);
        Route::post('/{notificationId}/read', [App\Http\Controllers\API\NotificationController::class, 'markAsRead']);
    });
});

// Routes avec vérification du profil incomplet (pour les utilisateurs authentifiés)
Route::middleware(['auth:sanctum', 'check_incomplete_profile'])->group(function () {
    // Routes principales qui nécessitent un profil complet pour une meilleure expérience
    Route::get('/dashboard', function () {
        return response()->json(['message' => 'Dashboard data']);
    });

    // Ajoutez ici d'autres routes qui bénéficient des alertes de profil incomplet
});


Route::middleware(['auth:sanctum', 'is_super_admin_interne'])->prefix('admin')->group(function () {
    Route::apiResource('employes', App\Http\Controllers\API\Admin\InternalEmployeeController::class);
});

// Suppression des routes de test
// Route::post('/test-password', ...);
// Route::post('/reset-password', ...);
// Route::post('/debug-password', ...);
