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
        Schema::table('user_subscriptions', function (Blueprint $table) {
            // Rename user_id to tenant_id if it exists
            if (Schema::hasColumn('user_subscriptions', 'user_id') && !Schema::hasColumn('user_subscriptions', 'tenant_id')) {
                $table->renameColumn('user_id', 'tenant_id');
            }
            
            // Rename date columns to match model expectations
            if (Schema::hasColumn('user_subscriptions', 'starts_at') && !Schema::hasColumn('user_subscriptions', 'start_date')) {
                $table->renameColumn('starts_at', 'start_date');
            }
            
            if (Schema::hasColumn('user_subscriptions', 'ends_at') && !Schema::hasColumn('user_subscriptions', 'end_date')) {
                $table->renameColumn('ends_at', 'end_date');
            }
        });

        // Now add the missing columns
        Schema::table('user_subscriptions', function (Blueprint $table) {
            // Add missing fields that are in the UserSubscription model
            if (!Schema::hasColumn('user_subscriptions', 'trial_ends_at')) {
                $table->timestamp('trial_ends_at')->nullable()->after('end_date');
            }
            
            if (!Schema::hasColumn('user_subscriptions', 'auto_renew')) {
                $table->boolean('auto_renew')->default(false)->after('is_active');
            }
            
            if (!Schema::hasColumn('user_subscriptions', 'metadata')) {
                $table->json('metadata')->nullable()->after('auto_renew');
            }
            
            // Add useful additional fields for better subscription management
            if (!Schema::hasColumn('user_subscriptions', 'billing_cycle')) {
                $table->enum('billing_cycle', ['monthly', 'yearly'])->default('monthly')->after('auto_renew');
            }
            
            if (!Schema::hasColumn('user_subscriptions', 'price_at_subscription')) {
                $table->decimal('price_at_subscription', 10, 2)->nullable()->after('billing_cycle')->comment('Price when subscription was created');
            }
            
            // Add indexes for better query performance
            if (!Schema::hasColumn('user_subscriptions', 'tenant_id')) {
                $table->index(['tenant_id', 'subscription_id']);
                $table->index(['is_active', 'end_date']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_subscriptions', function (Blueprint $table) {
            // Remove indexes first (only if they exist)
            try {
                $table->dropIndex(['tenant_id', 'subscription_id']);
                $table->dropIndex(['is_active', 'end_date']);
            } catch (\Exception $e) {
                // Ignore if indexes don't exist
            }
            
            // Drop columns in reverse order
            if (Schema::hasColumn('user_subscriptions', 'price_at_subscription')) {
                $table->dropColumn('price_at_subscription');
            }
            
            if (Schema::hasColumn('user_subscriptions', 'billing_cycle')) {
                $table->dropColumn('billing_cycle');
            }
            
            if (Schema::hasColumn('user_subscriptions', 'metadata')) {
                $table->dropColumn('metadata');
            }
            
            if (Schema::hasColumn('user_subscriptions', 'auto_renew')) {
                $table->dropColumn('auto_renew');
            }
            
            if (Schema::hasColumn('user_subscriptions', 'trial_ends_at')) {
                $table->dropColumn('trial_ends_at');
            }
        });

        // Rename columns back
        Schema::table('user_subscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('user_subscriptions', 'tenant_id')) {
                $table->renameColumn('tenant_id', 'user_id');
            }
            
            if (Schema::hasColumn('user_subscriptions', 'start_date')) {
                $table->renameColumn('start_date', 'starts_at');
            }
            
            if (Schema::hasColumn('user_subscriptions', 'end_date')) {
                $table->renameColumn('end_date', 'ends_at');
            }
        });
    }
};