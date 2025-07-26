<?php

declare(strict_types=1);

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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_internal')) {
                $table->boolean('is_internal')->default(false)->after('is_active');
            }
            if (!Schema::hasColumn('users', 'role_interne')) {
                $table->string('role_interne')->nullable()->after('role');
            }
            $table->foreignId('tenant_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_internal')) {
                $table->dropColumn('is_internal');
            }
            if (Schema::hasColumn('users', 'role_interne')) {
                $table->dropColumn('role_interne');
            }
            $table->foreignId('tenant_id')->nullable(false)->change();
        });
    }
};
