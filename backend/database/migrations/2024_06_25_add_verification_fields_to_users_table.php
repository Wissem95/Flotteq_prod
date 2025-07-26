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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('reset_code')->nullable()->after('remember_token');
            $table->timestamp('reset_code_expires_at')->nullable()->after('reset_code');
            $table->string('verification_code')->nullable()->after('reset_code_expires_at');
            $table->timestamp('verification_code_expires_at')->nullable()->after('verification_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'reset_code',
                'reset_code_expires_at',
                'verification_code',
                'verification_code_expires_at'
            ]);
        });
    }
};
