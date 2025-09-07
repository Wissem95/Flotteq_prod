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

    // Analytics routes (subscription required for advanced analytics)
    Route::prefix('analytics')->middleware('require_subscription')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\API\AnalyticsController::class, 'getDashboardStats']);
        Route::get('/vehicles', [App\Http\Controllers\API\AnalyticsController::class, 'getVehicleStats']);
        Route::get('/users', [App\Http\Controllers\API\AnalyticsController::class, 'getUserStats']);
        Route::get('/system', [App\Http\Controllers\API\AnalyticsController::class, 'getSystemHealth']);
        Route::post('/export', [App\Http\Controllers\API\AnalyticsController::class, 'exportAnalytics']);
    });

    // Finances routes (subscription required for financial management)
    Route::prefix('finances')->middleware('require_subscription')->group(function () {
        Route::get('/overview', [App\Http\Controllers\API\FinancesController::class, 'getOverview']);
        Route::get('/monthly-chart', [App\Http\Controllers\API\FinancesController::class, 'getMonthlyChart']);
        Route::get('/expense-breakdown', [App\Http\Controllers\API\FinancesController::class, 'getExpenseBreakdown']);
        Route::get('/top-expensive-vehicles', [App\Http\Controllers\API\FinancesController::class, 'getTopExpensiveVehicles']);
        Route::get('/maintenance-stats', [App\Http\Controllers\API\FinancesController::class, 'getMaintenanceStats']);
        Route::get('/expense-history', [App\Http\Controllers\API\FinancesController::class, 'getExpenseHistory']);
        Route::get('/alerts', [App\Http\Controllers\API\FinancesController::class, 'getFinancialAlerts']);
    });

    // Transactions routes (subscription required for transaction management)
    Route::prefix('transactions')->middleware('require_subscription')->group(function () {
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
Route::middleware(['auth:internal'])->prefix('internal')->group(function () {
    // Auth routes for internal users
    Route::prefix('auth')->group(function () {
        Route::get('/me', [App\Http\Controllers\API\InternalAuthController::class, 'me']);
        Route::post('/logout', [App\Http\Controllers\API\InternalAuthController::class, 'logout']);
        Route::put('/profile', [App\Http\Controllers\API\InternalAuthController::class, 'updateProfile']);
    });
});

// Internal admin routes (FlotteQ employees only)  
Route::middleware(['auth:internal'])->prefix('internal')->group(function () {
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
        Route::post('/plans/{plan}/toggle-status', [App\Http\Controllers\API\SubscriptionsController::class, 'togglePlanStatus']);
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

    // Promotions management (Internal only)
    Route::prefix('promotions')->group(function () {
        Route::get('/', [App\Http\Controllers\API\PromotionsController::class, 'index']);
        Route::post('/', [App\Http\Controllers\API\PromotionsController::class, 'store']);
        Route::get('/statistics', [App\Http\Controllers\API\PromotionsController::class, 'statistics']);
        Route::post('/validate-code', [App\Http\Controllers\API\PromotionsController::class, 'validateCode']);
        Route::get('/{promotion}', [App\Http\Controllers\API\PromotionsController::class, 'show']);
        Route::put('/{promotion}', [App\Http\Controllers\API\PromotionsController::class, 'update']);
        Route::delete('/{promotion}', [App\Http\Controllers\API\PromotionsController::class, 'destroy']);
        Route::post('/{promotion}/activate', [App\Http\Controllers\API\PromotionsController::class, 'activate']);
        Route::post('/{promotion}/deactivate', [App\Http\Controllers\API\PromotionsController::class, 'deactivate']);
    });

    // Payment Methods management (Internal only)
    Route::prefix('payment-methods')->group(function () {
        Route::get('/', [App\Http\Controllers\API\PaymentMethodsController::class, 'index']);
        Route::post('/', [App\Http\Controllers\API\PaymentMethodsController::class, 'store']);
        Route::get('/available', [App\Http\Controllers\API\PaymentMethodsController::class, 'available']);
        Route::get('/statistics', [App\Http\Controllers\API\PaymentMethodsController::class, 'statistics']);
        Route::get('/{paymentMethod}', [App\Http\Controllers\API\PaymentMethodsController::class, 'show']);
        Route::put('/{paymentMethod}', [App\Http\Controllers\API\PaymentMethodsController::class, 'update']);
        Route::delete('/{paymentMethod}', [App\Http\Controllers\API\PaymentMethodsController::class, 'destroy']);
        Route::post('/{paymentMethod}/test', [App\Http\Controllers\API\PaymentMethodsController::class, 'testConnection']);
        Route::post('/{paymentMethod}/toggle', [App\Http\Controllers\API\PaymentMethodsController::class, 'toggleStatus']);
        Route::post('/{paymentMethod}/set-default', [App\Http\Controllers\API\PaymentMethodsController::class, 'setDefault']);
    });

    // Permissions and Roles management (Internal only)
    Route::prefix('permissions')->group(function () {
        // Permissions
        Route::get('/', [App\Http\Controllers\API\PermissionsController::class, 'getPermissions']);
        Route::post('/', [App\Http\Controllers\API\PermissionsController::class, 'storePermission']);
        Route::put('/{permission}', [App\Http\Controllers\API\PermissionsController::class, 'updatePermission']);
        Route::get('/categories', [App\Http\Controllers\API\PermissionsController::class, 'getCategories']);
        Route::get('/modules', [App\Http\Controllers\API\PermissionsController::class, 'getModules']);
        Route::get('/matrix', [App\Http\Controllers\API\PermissionsController::class, 'getPermissionMatrix']);

        // Roles
        Route::prefix('roles')->group(function () {
            Route::get('/', [App\Http\Controllers\API\PermissionsController::class, 'getRoles']);
            Route::post('/', [App\Http\Controllers\API\PermissionsController::class, 'storeRole']);
            Route::get('/{role}', [App\Http\Controllers\API\PermissionsController::class, 'showRole']);
            Route::put('/{role}', [App\Http\Controllers\API\PermissionsController::class, 'updateRole']);
            Route::delete('/{role}', [App\Http\Controllers\API\PermissionsController::class, 'destroyRole']);
            Route::post('/{role}/permissions', [App\Http\Controllers\API\PermissionsController::class, 'assignPermissionToRole']);
            Route::delete('/{role}/permissions/{permission}', [App\Http\Controllers\API\PermissionsController::class, 'removePermissionFromRole']);
        });

        // User permissions
        Route::get('/users/{employee}/permissions', [App\Http\Controllers\API\PermissionsController::class, 'getUserPermissions']);
    });

    // Feature Flags management (Internal only)
    Route::prefix('feature-flags')->group(function () {
        Route::get('/', [App\Http\Controllers\API\FeatureFlagsController::class, 'index']);
        Route::post('/', [App\Http\Controllers\API\FeatureFlagsController::class, 'store']);
        Route::get('/categories', [App\Http\Controllers\API\FeatureFlagsController::class, 'getCategories']);
        Route::get('/statistics', [App\Http\Controllers\API\FeatureFlagsController::class, 'statistics']);
        Route::post('/check', [App\Http\Controllers\API\FeatureFlagsController::class, 'checkFlag']);
        Route::post('/bulk-check', [App\Http\Controllers\API\FeatureFlagsController::class, 'bulkCheck']);
        Route::get('/{featureFlag}', [App\Http\Controllers\API\FeatureFlagsController::class, 'show']);
        Route::put('/{featureFlag}', [App\Http\Controllers\API\FeatureFlagsController::class, 'update']);
        Route::delete('/{featureFlag}', [App\Http\Controllers\API\FeatureFlagsController::class, 'destroy']);
        Route::post('/{featureFlag}/enable', [App\Http\Controllers\API\FeatureFlagsController::class, 'enable']);
        Route::post('/{featureFlag}/disable', [App\Http\Controllers\API\FeatureFlagsController::class, 'disable']);
        Route::post('/{featureFlag}/clone', [App\Http\Controllers\API\FeatureFlagsController::class, 'clone']);
    });
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

    // Tenant subscription management
    Route::prefix('tenant/subscription')->group(function () {
        Route::get('/plans', [App\Http\Controllers\API\TenantSubscriptionController::class, 'getAvailablePlans']);
        Route::get('/current', [App\Http\Controllers\API\TenantSubscriptionController::class, 'getCurrentSubscription']);
        Route::post('/subscribe', [App\Http\Controllers\API\TenantSubscriptionController::class, 'subscribe']);
        Route::post('/cancel', [App\Http\Controllers\API\TenantSubscriptionController::class, 'cancelSubscription']);
        Route::get('/history', [App\Http\Controllers\API\TenantSubscriptionController::class, 'getSubscriptionHistory']);
        Route::get('/feature/{feature}/check', [App\Http\Controllers\API\TenantSubscriptionController::class, 'checkFeatureAccess']);
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
