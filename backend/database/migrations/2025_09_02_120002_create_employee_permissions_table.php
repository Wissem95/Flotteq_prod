<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Ex: manage_tenants, view_analytics, edit_subscriptions
            $table->string('slug')->unique(); // Ex: manage_tenants, view_analytics
            $table->text('description')->nullable();
            
            // Catégorisation
            $table->string('category'); // Ex: tenants, analytics, subscriptions, system
            $table->string('resource'); // Ex: tenant, user, subscription, report
            $table->string('action'); // Ex: create, read, update, delete, manage
            
            // Hiérarchie et groupement
            $table->string('group')->nullable(); // Regroupement logique
            $table->integer('priority')->default(1); // Ordre d'affichage
            
            // Configuration
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system_permission')->default(false); // Permissions système
            $table->boolean('requires_super_admin')->default(false); // Nécessite super admin
            
            // Restrictions contextuelles
            $table->json('conditions')->nullable(); // Conditions d'application
            $table->json('restrictions')->nullable(); // Restrictions spécifiques
            
            // Relations avec ressources
            $table->json('applies_to')->nullable(); // Types de ressources concernées
            
            // Métadonnées
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            // Index
            $table->index(['category', 'is_active']);
            $table->index(['resource', 'action']);
            $table->index(['group', 'priority']);
            $table->index(['slug', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_permissions');
    }
};