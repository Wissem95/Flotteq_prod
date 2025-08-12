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
            $table->string('email')->unique()->nullable()->after('name');
            $table->string('phone', 20)->nullable()->after('email');
            $table->text('address')->nullable()->after('phone');
            $table->enum('status', ['active', 'inactive', 'pending'])->default('pending')->after('address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['email', 'phone', 'address', 'status']);
        });
    }
};
