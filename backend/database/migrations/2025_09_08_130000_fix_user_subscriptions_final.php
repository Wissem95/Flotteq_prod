<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, check current structure and fix it
        Schema::table('user_subscriptions', function (Blueprint $table) {
            // If we have start_date/end_date, rename them to starts_at/ends_at
            if (Schema::hasColumn('user_subscriptions', 'start_date') && !Schema::hasColumn('user_subscriptions', 'starts_at')) {
                $table->renameColumn('start_date', 'starts_at');
            }
            
            if (Schema::hasColumn('user_subscriptions', 'end_date') && !Schema::hasColumn('user_subscriptions', 'ends_at')) {
                $table->renameColumn('end_date', 'ends_at');
            }
        });

        // Now ensure all required columns exist
        Schema::table('user_subscriptions', function (Blueprint $table) {
            // Make sure we have user_id (not tenant_id)
            if (Schema::hasColumn('user_subscriptions', 'tenant_id') && !Schema::hasColumn('user_subscriptions', 'user_id')) {
                $table->renameColumn('tenant_id', 'user_id');
            }
            
            // Ensure starts_at and ends_at are timestamps (not just dates)
            if (Schema::hasColumn('user_subscriptions', 'starts_at')) {
                $table->timestamp('starts_at')->nullable()->change();
            } else {
                $table->timestamp('starts_at')->nullable()->after('subscription_id');
            }
            
            if (Schema::hasColumn('user_subscriptions', 'ends_at')) {
                $table->timestamp('ends_at')->nullable()->change();
            } else {
                $table->timestamp('ends_at')->nullable()->after('starts_at');
            }
            
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('user_subscriptions', 'trial_ends_at')) {
                $table->timestamp('trial_ends_at')->nullable()->after('ends_at');
            }
            
            if (!Schema::hasColumn('user_subscriptions', 'auto_renew')) {
                $table->boolean('auto_renew')->default(false)->after('is_active');
            }
            
            if (!Schema::hasColumn('user_subscriptions', 'billing_cycle')) {
                $table->enum('billing_cycle', ['monthly', 'yearly'])->default('monthly')->after('auto_renew');
            }
            
            if (!Schema::hasColumn('user_subscriptions', 'price_at_subscription')) {
                $table->decimal('price_at_subscription', 10, 2)->nullable()->after('billing_cycle');
            }
            
            if (!Schema::hasColumn('user_subscriptions', 'metadata')) {
                $table->json('metadata')->nullable()->after('price_at_subscription');
            }
        });

        // Add proper indexes
        Schema::table('user_subscriptions', function (Blueprint $table) {
            if (!Schema::hasIndex('user_subscriptions', 'user_subscriptions_user_id_subscription_id_index')) {
                $table->index(['user_id', 'subscription_id']);
            }
            if (!Schema::hasIndex('user_subscriptions', 'user_subscriptions_is_active_ends_at_index')) {
                $table->index(['is_active', 'ends_at']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_subscriptions', function (Blueprint $table) {
            // Remove indexes - check if they exist first
            if (Schema::hasIndex('user_subscriptions', 'user_subscriptions_user_id_subscription_id_index')) {
                $table->dropIndex(['user_id', 'subscription_id']);
            }
            if (Schema::hasIndex('user_subscriptions', 'user_subscriptions_is_active_ends_at_index')) {
                $table->dropIndex(['is_active', 'ends_at']);
            }
            
            // Remove additional columns (keep core structure)
            if (Schema::hasColumn('user_subscriptions', 'metadata')) {
                $table->dropColumn('metadata');
            }
            if (Schema::hasColumn('user_subscriptions', 'price_at_subscription')) {
                $table->dropColumn('price_at_subscription');
            }
            if (Schema::hasColumn('user_subscriptions', 'billing_cycle')) {
                $table->dropColumn('billing_cycle');
            }
            if (Schema::hasColumn('user_subscriptions', 'auto_renew')) {
                $table->dropColumn('auto_renew');
            }
            if (Schema::hasColumn('user_subscriptions', 'trial_ends_at')) {
                $table->dropColumn('trial_ends_at');
            }
        });
    }

};