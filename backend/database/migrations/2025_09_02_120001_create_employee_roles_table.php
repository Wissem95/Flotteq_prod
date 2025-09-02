<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Ex: Super Admin, Admin, Manager, Developer, Support Agent
            $table->string('slug')->unique(); // Ex: super_admin, admin, manager
            $table->text('description')->nullable();
            
            // Hiérarchie des rôles
            $table->integer('level')->default(1); // 1 = plus bas, 10 = plus haut
            $table->foreignId('parent_role_id')->nullable()->constrained('employee_roles')->onDelete('set null');
            
            // Configuration
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system_role')->default(false); // Rôles système non modifiables
            
            // Permissions et accès
            $table->json('permissions')->nullable(); // Permissions spécifiques au rôle
            $table->json('dashboard_access')->nullable(); // Sections du dashboard accessibles
            $table->json('api_access')->nullable(); // Endpoints API autorisés
            
            // Restrictions
            $table->json('restrictions')->nullable(); // Limitations spécifiques
            $table->integer('max_tenants_access')->nullable(); // Limite d'accès aux tenants
            
            // Métadonnées
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            // Index
            $table->index(['is_active', 'level']);
            $table->index(['slug', 'is_active']);
            $table->index(['parent_role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_roles');
    }
};