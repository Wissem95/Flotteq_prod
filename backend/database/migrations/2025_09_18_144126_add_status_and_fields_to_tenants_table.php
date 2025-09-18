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
        Schema::table('tenants', function (Blueprint $table) {
            if (!Schema::hasColumn('tenants', 'status')) {
                $table->string('status')->default('active')->after('domain');
            }
            if (!Schema::hasColumn('tenants', 'industry')) {
                $table->string('industry')->nullable()->after('status');
            }
            if (!Schema::hasColumn('tenants', 'company_size')) {
                $table->string('company_size')->nullable()->after('industry');
            }
            if (!Schema::hasColumn('tenants', 'phone')) {
                $table->string('phone')->nullable()->after('company_size');
            }
            if (!Schema::hasColumn('tenants', 'address')) {
                $table->text('address')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('tenants', 'description')) {
                $table->text('description')->nullable()->after('address');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            if (Schema::hasColumn('tenants', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('tenants', 'address')) {
                $table->dropColumn('address');
            }
            if (Schema::hasColumn('tenants', 'phone')) {
                $table->dropColumn('phone');
            }
            if (Schema::hasColumn('tenants', 'company_size')) {
                $table->dropColumn('company_size');
            }
            if (Schema::hasColumn('tenants', 'industry')) {
                $table->dropColumn('industry');
            }
            if (Schema::hasColumn('tenants', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
