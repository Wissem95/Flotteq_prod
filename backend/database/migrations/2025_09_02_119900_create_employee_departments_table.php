<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique(); // Ex: DEV, SUPPORT, FINANCE, MARKETING
            $table->text('description')->nullable();
            
            // Hiérarchie
            $table->foreignId('parent_department_id')->nullable()->constrained('employee_departments')->onDelete('set null');
            
            // Responsable du département (sera ajouté après création internal_employees)
            $table->unsignedBigInteger('manager_id')->nullable();
            
            // Configuration
            $table->boolean('is_active')->default(true);
            $table->integer('max_employees')->nullable();
            $table->json('budget_allocation')->nullable(); // Budget alloué par type
            
            // Métadonnées
            $table->json('permissions')->nullable(); // Permissions par défaut du département
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            // Index
            $table->index(['parent_department_id', 'is_active']);
            $table->index(['manager_id']);
            $table->index(['code', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_departments');
    }
};