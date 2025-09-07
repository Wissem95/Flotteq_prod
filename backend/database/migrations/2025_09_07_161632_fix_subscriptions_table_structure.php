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
        // Check if the table exists and if columns are missing
        if (Schema::hasTable('subscriptions')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                // Add missing columns if they don't exist
                if (!Schema::hasColumn('subscriptions', 'name')) {
                    $table->string('name')->after('id');
                }
                if (!Schema::hasColumn('subscriptions', 'description')) {
                    $table->text('description')->nullable()->after('name');
                }
                if (!Schema::hasColumn('subscriptions', 'price')) {
                    $table->decimal('price', 10, 2)->after('description');
                }
                if (!Schema::hasColumn('subscriptions', 'currency')) {
                    $table->string('currency')->default('EUR')->after('price');
                }
                if (!Schema::hasColumn('subscriptions', 'billing_cycle')) {
                    $table->enum('billing_cycle', ['monthly', 'yearly'])->default('monthly')->after('currency');
                }
                if (!Schema::hasColumn('subscriptions', 'features')) {
                    $table->json('features')->nullable()->after('billing_cycle');
                }
                if (!Schema::hasColumn('subscriptions', 'limits')) {
                    $table->json('limits')->nullable()->after('features');
                }
                if (!Schema::hasColumn('subscriptions', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('limits');
                }
                if (!Schema::hasColumn('subscriptions', 'is_popular')) {
                    $table->boolean('is_popular')->default(false)->after('is_active');
                }
                if (!Schema::hasColumn('subscriptions', 'sort_order')) {
                    $table->integer('sort_order')->default(0)->after('is_popular');
                }
                if (!Schema::hasColumn('subscriptions', 'metadata')) {
                    $table->json('metadata')->nullable()->after('sort_order');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $columns = ['name', 'description', 'price', 'currency', 'billing_cycle', 
                       'features', 'limits', 'is_active', 'is_popular', 'sort_order', 'metadata'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('subscriptions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
