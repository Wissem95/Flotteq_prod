<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            
            // Informations de base
            $table->string('name');
            $table->string('code')->unique(); // Code promo (ex: WELCOME2024)
            $table->text('description');
            $table->enum('type', ['discount_percentage', 'discount_fixed', 'free_trial', 'upgrade_bonus', 'custom'])->default('discount_percentage');
            
            // Validité
            $table->timestamp('start_date');
            $table->timestamp('end_date');
            $table->enum('status', ['draft', 'active', 'paused', 'expired', 'cancelled'])->default('draft');
            
            // Valeur de la promotion
            $table->decimal('discount_value', 8, 2)->nullable(); // Montant ou pourcentage
            $table->string('discount_unit', 10)->nullable(); // 'percent', 'euro', 'month'
            $table->decimal('maximum_discount', 8, 2)->nullable(); // Réduction maximale
            $table->decimal('minimum_order', 8, 2)->nullable(); // Commande minimale
            
            // Limitations
            $table->integer('usage_limit_total')->nullable(); // Limite d'usage total
            $table->integer('usage_limit_per_user')->default(1); // Limite par utilisateur
            $table->integer('current_usage_count')->default(0); // Nombre d'utilisations actuelles
            
            // Ciblage
            $table->json('target_audience')->nullable(); // Critères de ciblage
            $table->json('applicable_plans')->nullable(); // Plans concernés
            $table->json('excluded_tenants')->nullable(); // Tenants exclus
            $table->boolean('new_customers_only')->default(false); // Nouveaux clients seulement
            
            // Configuration avancée
            $table->boolean('stackable')->default(false); // Cumulable avec d'autres promos
            $table->boolean('auto_apply')->default(false); // Application automatique
            $table->integer('priority')->default(0); // Priorité d'application
            
            // Durée d'application
            $table->integer('trial_extension_days')->nullable(); // Prolongation d'essai
            $table->integer('billing_cycles_affected')->nullable(); // Cycles de facturation affectés
            
            // Suivi et analytics
            $table->integer('views_count')->default(0); // Nombre de vues
            $table->integer('clicks_count')->default(0); // Nombre de clics
            $table->integer('conversions_count')->default(0); // Nombre de conversions
            $table->decimal('conversion_rate', 5, 2)->default(0); // Taux de conversion
            
            // Attribution et approbation
            $table->foreignId('created_by')->constrained('internal_employees')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('internal_employees')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            
            // Communication
            $table->string('landing_page_url')->nullable();
            $table->json('marketing_materials')->nullable(); // Matériel marketing
            $table->json('email_templates')->nullable(); // Templates email
            
            // Métadonnées
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            // Index
            $table->index(['code', 'status']);
            $table->index(['status', 'start_date', 'end_date']);
            $table->index(['type', 'status']);
            $table->index(['created_by', 'status']);
            $table->index(['new_customers_only', 'status']);
            $table->index(['auto_apply', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};