<?php

declare(strict_types=1);

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

// Health check endpoint for monitoring
Route::get('/health', fn() => ['status' => 'ok', 'timestamp' => now()]);

// Public auth routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [App\Http\Controllers\API\AuthController::class, 'login']);
    Route::post('/register', [App\Http\Controllers\API\AuthController::class, 'register']);
    Route::post('/register-tenant-user', [App\Http\Controllers\API\AuthController::class, 'registerTenantUser']);
    Route::post('/resolve-tenant', [App\Http\Controllers\API\AuthController::class, 'resolveTenant']);
    Route::get('/tenant-from-host', [App\Http\Controllers\API\AuthController::class, 'getTenantFromHost']);

    // Google OAuth routes
    Route::get('/google/redirect', [App\Http\Controllers\API\SocialAuthController::class, 'redirectToGoogle']);
    Route::get('/google/callback', [App\Http\Controllers\API\SocialAuthController::class, 'handleGoogleCallback']);
    
    // Firebase Auth route (nouvelle méthode)
    Route::post('/firebase', [App\Http\Controllers\API\SocialAuthController::class, 'handleFirebaseAuth']);

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

// Protected tenant-aware routes with loueur permissions check
Route::middleware(['auth:sanctum', 'tenant', 'check_loueur_permissions'])->group(function () {
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

    // User management routes
    Route::apiResource('users', App\Http\Controllers\API\UserController::class);

    // État des lieux routes
    Route::prefix('etat-des-lieux')->group(function () {
        Route::get('/', [App\Http\Controllers\API\EtatDesLieuxController::class, 'index']);
        Route::post('/', [App\Http\Controllers\API\EtatDesLieuxController::class, 'store']);
        Route::get('/photo-positions', [App\Http\Controllers\API\EtatDesLieuxController::class, 'getPhotoPositions']);
        Route::get('/{etatDesLieux}', [App\Http\Controllers\API\EtatDesLieuxController::class, 'show']);
        Route::put('/{etatDesLieux}', [App\Http\Controllers\API\EtatDesLieuxController::class, 'update']);
        Route::delete('/{etatDesLieux}', [App\Http\Controllers\API\EtatDesLieuxController::class, 'destroy']);
        Route::post('/{etatDesLieux}/upload-photo', [App\Http\Controllers\API\EtatDesLieuxController::class, 'uploadPhoto']);
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


// Internal authentication routes (public)
Route::prefix('internal/auth')->group(function () {
    Route::post('/login', [App\Http\Controllers\API\InternalAuthController::class, 'login']);
    Route::get('/health/database', [App\Http\Controllers\API\InternalAuthController::class, 'healthDatabase']);
});

// Internal protected routes
Route::middleware(['auth:sanctum'])->prefix('internal')->group(function () {
    // Auth routes for internal users
    Route::prefix('auth')->group(function () {
        Route::get('/me', [App\Http\Controllers\API\InternalAuthController::class, 'me']);
        Route::post('/logout', [App\Http\Controllers\API\InternalAuthController::class, 'logout']);
        Route::put('/profile', [App\Http\Controllers\API\InternalAuthController::class, 'updateProfile']);
    });
});

// Internal admin routes (FlotteQ employees only)
Route::middleware(['auth:sanctum', 'is_super_admin_interne'])->prefix('internal')->group(function () {
    Route::apiResource('employes', App\Http\Controllers\API\Admin\InternalEmployeeController::class);

    // Tenants management (Internal only)
    Route::prefix('tenants')->group(function () {
        Route::get('/', [App\Http\Controllers\API\TenantController::class, 'index']);
        Route::get('/stats', [App\Http\Controllers\API\TenantController::class, 'getStats']);
        Route::get('/{tenant}', [App\Http\Controllers\API\TenantController::class, 'show']);
        Route::post('/', [App\Http\Controllers\API\TenantController::class, 'store']);
        Route::put('/{tenant}', [App\Http\Controllers\API\TenantController::class, 'update']);
        Route::delete('/{tenant}', [App\Http\Controllers\API\TenantController::class, 'destroy']);
    });

    // System alerts management
    Route::prefix('alerts')->group(function () {
        Route::get('/', [App\Http\Controllers\API\AlertsController::class, 'index']);
        Route::post('/', [App\Http\Controllers\API\AlertsController::class, 'store']);
        Route::put('/{alert}', [App\Http\Controllers\API\AlertsController::class, 'update']);
        Route::delete('/{alert}', [App\Http\Controllers\API\AlertsController::class, 'destroy']);
    });

    // Internal Dashboard routes
    Route::prefix('dashboard')->group(function () {
        Route::get('/stats', [App\Http\Controllers\API\InternalDashboardController::class, 'getGlobalStats']);
        Route::get('/upcoming-maintenances', [App\Http\Controllers\API\InternalDashboardController::class, 'getUpcomingMaintenances']);
        Route::get('/alerts', [App\Http\Controllers\API\InternalDashboardController::class, 'getSystemAlerts']);
        Route::get('/revenue', [App\Http\Controllers\API\InternalDashboardController::class, 'getGlobalRevenue']);
        Route::get('/partners-distribution', [App\Http\Controllers\API\InternalDashboardController::class, 'getPartnerDistribution']);
        Route::get('/system-health', [App\Http\Controllers\API\InternalDashboardController::class, 'getSystemHealth']);
        Route::get('/tenants-list', [App\Http\Controllers\API\InternalDashboardController::class, 'getTenantsList']);
    });

    // Additional internal routes
    Route::get('/tickets', [App\Http\Controllers\API\SupportController::class, 'index']);
    Route::get('/employees', [App\Http\Controllers\API\Admin\InternalEmployeeController::class, 'index']);
    Route::get('/stats', [App\Http\Controllers\API\InternalAnalyticsController::class, 'getStats']);
    Route::get('/statistics', [App\Http\Controllers\API\InternalAnalyticsController::class, 'getStatistics']);

    // Partners management (Internal only)
    Route::prefix('partners')->group(function () {
        Route::get('/', [App\Http\Controllers\API\PartnersController::class, 'index']);
        Route::post('/', [App\Http\Controllers\API\PartnersController::class, 'store']);
        Route::get('/statistics', [App\Http\Controllers\API\PartnersController::class, 'statistics']);
        Route::get('/{partner}', [App\Http\Controllers\API\PartnersController::class, 'show']);
        Route::put('/{partner}', [App\Http\Controllers\API\PartnersController::class, 'update']);
        Route::delete('/{partner}', [App\Http\Controllers\API\PartnersController::class, 'destroy']);
    });

    // Tenant Users management (Internal only) - Vue globale de tous les utilisateurs des tenants
    Route::prefix('tenant-users')->group(function () {
        Route::get('/', [App\Http\Controllers\API\Admin\TenantUsersController::class, 'index']);
        Route::get('/export', [App\Http\Controllers\API\Admin\TenantUsersController::class, 'export']);
        Route::get('/{user}', [App\Http\Controllers\API\Admin\TenantUsersController::class, 'show']);
        Route::put('/{user}', [App\Http\Controllers\API\Admin\TenantUsersController::class, 'update']);
        Route::post('/{user}/toggle-status', [App\Http\Controllers\API\Admin\TenantUsersController::class, 'toggleStatus']);
        Route::delete('/{user}', [App\Http\Controllers\API\Admin\TenantUsersController::class, 'destroy']);
    });

    // Support management (Internal only)
    Route::prefix('support')->group(function () {
        Route::get('/tickets', [App\Http\Controllers\API\SupportController::class, 'index']);
        Route::get('/statistics', [App\Http\Controllers\API\SupportController::class, 'statistics']);
        Route::get('/stats', [App\Http\Controllers\API\SupportController::class, 'statistics']); // Alias for statistics
        Route::post('/tickets/{ticket}/assign', [App\Http\Controllers\API\SupportController::class, 'assign']);
        Route::get('/tenants/{tenantId}/metrics', [App\Http\Controllers\API\SupportController::class, 'tenantMetrics']);
    });

    // Analytics (Internal only)
    Route::prefix('analytics')->group(function () {
        Route::get('/global', [App\Http\Controllers\API\InternalAnalyticsController::class, 'globalMetrics']);
        Route::get('/tenants/{tenantId}', [App\Http\Controllers\API\InternalAnalyticsController::class, 'tenantAnalytics']);
        Route::get('/user-behavior', [App\Http\Controllers\API\InternalAnalyticsController::class, 'userBehavior']);
        Route::get('/performance', [App\Http\Controllers\API\InternalAnalyticsController::class, 'performanceMetrics']);
        Route::get('/platform-metrics', [App\Http\Controllers\API\InternalAnalyticsController::class, 'platformMetrics']);
        Route::get('/usage', [App\Http\Controllers\API\InternalAnalyticsController::class, 'usageMetrics']);
        Route::get('/realtime', [App\Http\Controllers\API\InternalAnalyticsController::class, 'realtimeMetrics']);
    });

    // Subscriptions management (Internal only)
    Route::prefix('subscriptions')->group(function () {
        Route::get('/', [App\Http\Controllers\API\SubscriptionsController::class, 'index']);
        Route::get('/stats', [App\Http\Controllers\API\SubscriptionsController::class, 'getStats']);
        Route::get('/plans', [App\Http\Controllers\API\SubscriptionsController::class, 'getPlans']);
        Route::post('/plans', [App\Http\Controllers\API\SubscriptionsController::class, 'createPlan']);
        Route::put('/plans/{plan}', [App\Http\Controllers\API\SubscriptionsController::class, 'updatePlan']);
        Route::delete('/plans/{plan}', [App\Http\Controllers\API\SubscriptionsController::class, 'deletePlan']);
    });

    // Financial management (Internal only)
    Route::prefix('financial')->group(function () {
        Route::get('/revenue', [App\Http\Controllers\API\FinancialController::class, 'getRevenue']);
        Route::get('/commissions', [App\Http\Controllers\API\FinancialController::class, 'getCommissions']);
        Route::get('/reports', [App\Http\Controllers\API\FinancialController::class, 'getReports']);
        Route::post('/reports', [App\Http\Controllers\API\FinancialController::class, 'generateReport']);
    });

    // Employees management (Internal only) - alias for employes route
    Route::get('/employees/stats', [App\Http\Controllers\API\Admin\InternalEmployeeController::class, 'getStats']);
    Route::get('/employees', [App\Http\Controllers\API\Admin\InternalEmployeeController::class, 'index']);
    Route::get('/employees/{id}', [App\Http\Controllers\API\Admin\InternalEmployeeController::class, 'show']);
    Route::post('/employees', [App\Http\Controllers\API\Admin\InternalEmployeeController::class, 'store']);
    Route::put('/employees/{id}', [App\Http\Controllers\API\Admin\InternalEmployeeController::class, 'update']);
    Route::delete('/employees/{id}', [App\Http\Controllers\API\Admin\InternalEmployeeController::class, 'destroy']);
});

// Tenant-specific routes
Route::middleware(['auth:sanctum', 'tenant'])->group(function () {
    // Partners for tenants (find and interact)
    Route::prefix('partners')->group(function () {
        Route::post('/find-nearby', [App\Http\Controllers\API\PartnersController::class, 'findNearby']);
        Route::post('/{partner}/rate', [App\Http\Controllers\API\PartnersController::class, 'rate']);
        Route::post('/{partner}/book', [App\Http\Controllers\API\PartnersController::class, 'book']);
    });

    // Support for tenants
    Route::prefix('support')->group(function () {
        Route::get('/tickets', [App\Http\Controllers\API\SupportController::class, 'index']);
        Route::post('/tickets', [App\Http\Controllers\API\SupportController::class, 'store']);
        Route::get('/tickets/{ticket}', [App\Http\Controllers\API\SupportController::class, 'show']);
        Route::post('/tickets/{ticket}/messages', [App\Http\Controllers\API\SupportController::class, 'addMessage']);
        Route::patch('/tickets/{ticket}/status', [App\Http\Controllers\API\SupportController::class, 'updateStatus']);
    });
});

// Analytics tracking (available for both Internal and Tenant)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/analytics/events', [App\Http\Controllers\API\InternalAnalyticsController::class, 'recordEvent']);
});

// Suppression des routes de test
// Route::post('/test-password', ...);
// Route::post('/reset-password', ...);
// Route::post('/debug-password', ...);
