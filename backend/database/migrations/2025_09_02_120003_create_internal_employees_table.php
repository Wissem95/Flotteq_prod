<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internal_employees', function (Blueprint $table) {
            $table->id();
            
            // Informations personnelles
            $table->string('employee_id')->unique(); // ID employé unique (ex: FLT-001)
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->date('date_of_birth')->nullable();
            
            // Informations professionnelles
            $table->foreignId('department_id')->constrained('employee_departments')->onDelete('restrict');
            $table->foreignId('role_id')->constrained('employee_roles')->onDelete('restrict');
            $table->foreignId('manager_id')->nullable()->constrained('internal_employees')->onDelete('set null');
            
            // Emploi
            $table->string('job_title');
            $table->enum('employment_type', ['full_time', 'part_time', 'contractor', 'intern'])->default('full_time');
            $table->date('hire_date');
            $table->date('termination_date')->nullable();
            $table->enum('status', ['active', 'inactive', 'on_leave', 'terminated'])->default('active');
            
            // Salaire et avantages
            $table->decimal('salary', 10, 2)->nullable();
            $table->string('salary_currency', 3)->default('EUR');
            $table->enum('salary_frequency', ['monthly', 'yearly'])->default('monthly');
            $table->json('benefits')->nullable(); // Avantages sociaux
            
            // Accès et sécurité
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->boolean('two_factor_enabled')->default(false);
            $table->json('two_factor_recovery_codes')->nullable();
            $table->timestamp('password_changed_at')->nullable();
            
            // Sessions et accès
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip')->nullable();
            $table->integer('failed_login_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
            
            // Permissions spécifiques
            $table->json('permissions')->nullable(); // Permissions individuelles
            $table->json('tenant_access')->nullable(); // Accès spécifique à des tenants
            $table->boolean('can_access_all_tenants')->default(false);
            
            // Informations complémentaires
            $table->text('bio')->nullable();
            $table->string('avatar')->nullable();
            $table->json('emergency_contact')->nullable();
            $table->json('work_schedule')->nullable(); // Horaires de travail
            
            // Métadonnées
            $table->json('metadata')->nullable();
            $table->rememberToken();
            $table->timestamps();
            
            // Index pour performances
            $table->index(['department_id', 'status']);
            $table->index(['role_id', 'status']);
            $table->index(['manager_id', 'status']);
            $table->index(['status', 'hire_date']);
            $table->index(['employee_id', 'status']);
            $table->index(['email', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internal_employees');
    }
};