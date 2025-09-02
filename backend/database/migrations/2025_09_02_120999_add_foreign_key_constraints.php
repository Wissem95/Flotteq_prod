<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ajouter la contrainte manager_id pour employee_departments
        Schema::table('employee_departments', function (Blueprint $table) {
            $table->foreign('manager_id')->references('id')->on('internal_employees')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('employee_departments', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
        });
    }
};