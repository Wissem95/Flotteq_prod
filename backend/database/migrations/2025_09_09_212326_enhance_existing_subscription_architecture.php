<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations - Enhance existing subscription architecture for tenant-centric approach
     */
    public function up(): void
    {
        // 1. Enhance subscriptions table with missing columns
        Schema::table('subscriptions', function (Blueprint $table) {
            // Add missing limit columns if they don't exist
            if (!Schema::hasColumn('subscriptions', 'max_vehicles')) {
                $table->integer('max_vehicles')->default(1)->after('price');
            }
            if (!Schema::hasColumn('subscriptions', 'max_users')) {
                $table->integer('max_users')->default(1)->after('max_vehicles');
            }
            if (!Schema::hasColumn('subscriptions', 'code')) {
                $table->string('code')->nullable()->unique()->after('name');
            }
            if (!Schema::hasColumn('subscriptions', 'is_default')) {
                $table->boolean('is_default')->default(false)->after('is_active');
            }
        });

        // 2. Enhance user_subscriptions table for tenant-based queries
        Schema::table('user_subscriptions', function (Blueprint $table) {
            // Add tenant_id if it doesn't exist (makes queries more efficient)
            if (!Schema::hasColumn('user_subscriptions', 'tenant_id')) {
                $table->foreignId('tenant_id')->nullable()->after('user_id')
                      ->constrained('tenants')->onDelete('cascade');
            }
            
            // Add status column for better subscription management
            if (!Schema::hasColumn('user_subscriptions', 'status')) {
                $table->enum('status', ['active', 'cancelled', 'expired', 'trial'])
                      ->default('active')->after('is_active');
            }
            
            // Add billing info
            if (!Schema::hasColumn('user_subscriptions', 'billing_cycle')) {
                $table->enum('billing_cycle', ['monthly', 'yearly'])
                      ->default('monthly')->after('status');
            }
            
            if (!Schema::hasColumn('user_subscriptions', 'amount_paid')) {
                $table->decimal('amount_paid', 10, 2)->nullable()->after('billing_cycle');
            }
        });

        // 3. Update subscription plans with correct limits
        $this->updateSubscriptionPlans();

        // 4. Migrate existing data to tenant-based approach
        $this->migrateToTenantBased();

        // 5. Add indexes for performance
        Schema::table('user_subscriptions', function (Blueprint $table) {
            if (!$this->indexExists('user_subscriptions', 'user_subscriptions_tenant_id_status_index')) {
                $table->index(['tenant_id', 'status']);
            }
            if (!$this->indexExists('user_subscriptions', 'user_subscriptions_tenant_id_is_active_index')) {
                $table->index(['tenant_id', 'is_active']);
            }
        });
    }

    /**
     * Update subscription plans with correct limits
     */
    private function updateSubscriptionPlans(): void
    {
        $plans = [
            ['pattern' => 'Gratuit', 'code' => 'free', 'max_vehicles' => 1, 'max_users' => 1],
            ['pattern' => 'Free', 'code' => 'free', 'max_vehicles' => 1, 'max_users' => 1],
            ['pattern' => 'Starter', 'code' => 'starter', 'max_vehicles' => 5, 'max_users' => 3],
            ['pattern' => 'Professional', 'code' => 'professional', 'max_vehicles' => 20, 'max_users' => 10],
            ['pattern' => 'Enterprise', 'code' => 'enterprise', 'max_vehicles' => 100, 'max_users' => 50],
        ];

        foreach ($plans as $plan) {
            DB::table('subscriptions')
                ->where('name', 'LIKE', '%' . $plan['pattern'] . '%')
                ->update([
                    'code' => $plan['code'],
                    'max_vehicles' => $plan['max_vehicles'],
                    'max_users' => $plan['max_users'],
                    'updated_at' => now()
                ]);
        }

        // Set default plan
        DB::table('subscriptions')
            ->where('name', 'LIKE', '%Gratuit%')
            ->orWhere('name', 'LIKE', '%Free%')
            ->update(['is_default' => true]);
    }

    /**
     * Migrate existing user subscriptions to tenant-based approach
     */
    private function migrateToTenantBased(): void
    {
        // Update tenant_id for existing user_subscriptions
        $subscriptionsWithoutTenant = DB::table('user_subscriptions')
            ->whereNull('tenant_id')
            ->get();

        foreach ($subscriptionsWithoutTenant as $subscription) {
            // Get tenant_id from user
            $user = DB::table('users')->find($subscription->user_id);
            if ($user && $user->tenant_id) {
                DB::table('user_subscriptions')
                    ->where('id', $subscription->id)
                    ->update([
                        'tenant_id' => $user->tenant_id,
                        'status' => $subscription->is_active ? 'active' : 'cancelled',
                        'updated_at' => now()
                    ]);
            }
        }

        // Ensure only one active subscription per tenant
        $this->consolidateTenantSubscriptions();
    }

    /**
     * Consolidate multiple subscriptions per tenant to keep only the latest active one
     */
    private function consolidateTenantSubscriptions(): void
    {
        $tenants = DB::table('user_subscriptions')
            ->whereNotNull('tenant_id')
            ->select('tenant_id')
            ->groupBy('tenant_id')
            ->get();

        foreach ($tenants as $tenant) {
            $subscriptions = DB::table('user_subscriptions')
                ->where('tenant_id', $tenant->tenant_id)
                ->where('is_active', true)
                ->orderBy('created_at', 'desc')
                ->get();

            if ($subscriptions->count() > 1) {
                // Keep the latest subscription active, deactivate others
                $latestSubscription = $subscriptions->first();
                
                DB::table('user_subscriptions')
                    ->where('tenant_id', $tenant->tenant_id)
                    ->where('id', '!=', $latestSubscription->id)
                    ->update([
                        'is_active' => false,
                        'status' => 'cancelled',
                        'ends_at' => now(),
                        'updated_at' => now()
                    ]);
            }
        }
    }

    /**
     * Check if index exists
     */
    private function indexExists(string $table, string $index): bool
    {
        try {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = $sm->listTableIndexes($table);
            return array_key_exists($index, $indexes);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove indexes
        Schema::table('user_subscriptions', function (Blueprint $table) {
            try {
                $table->dropIndex(['tenant_id', 'status']);
                $table->dropIndex(['tenant_id', 'is_active']);
            } catch (\Exception $e) {
                // Ignore if indexes don't exist
            }
        });

        // Remove columns from user_subscriptions
        Schema::table('user_subscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('user_subscriptions', 'amount_paid')) {
                $table->dropColumn('amount_paid');
            }
            if (Schema::hasColumn('user_subscriptions', 'billing_cycle')) {
                $table->dropColumn('billing_cycle');
            }
            if (Schema::hasColumn('user_subscriptions', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('user_subscriptions', 'tenant_id')) {
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            }
        });

        // Remove columns from subscriptions
        Schema::table('subscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('subscriptions', 'is_default')) {
                $table->dropColumn('is_default');
            }
            if (Schema::hasColumn('subscriptions', 'code')) {
                $table->dropColumn('code');
            }
            if (Schema::hasColumn('subscriptions', 'max_users')) {
                $table->dropColumn('max_users');
            }
            if (Schema::hasColumn('subscriptions', 'max_vehicles')) {
                $table->dropColumn('max_vehicles');
            }
        });
    }
};