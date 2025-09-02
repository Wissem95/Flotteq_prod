<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_programs', function (Blueprint $table) {
            $table->id();
            
            // Informations du programme
            $table->string('name');
            $table->string('code')->unique(); // Code du programme (ex: REFER2024)
            $table->text('description');
            $table->enum('status', ['draft', 'active', 'paused', 'ended', 'archived'])->default('draft');
            
            // Période de validité
            $table->timestamp('start_date');
            $table->timestamp('end_date')->nullable();
            $table->boolean('is_unlimited')->default(false); // Programme sans fin
            
            // Configuration des récompenses
            $table->enum('reward_type', ['discount', 'credit', 'cash', 'upgrade', 'custom'])->default('discount');
            $table->decimal('referrer_reward_value', 8, 2); // Récompense parrain
            $table->decimal('referee_reward_value', 8, 2); // Récompense filleul
            $table->string('reward_unit', 20)->default('percent'); // percent, euro, month, etc.
            
            // Conditions de qualification
            $table->boolean('referee_must_pay')->default(true); // Le filleul doit payer
            $table->integer('minimum_subscription_months')->default(1); // Durée min d'abonnement
            $table->decimal('minimum_order_value', 8, 2)->nullable(); // Valeur min de commande
            
            // Limites
            $table->integer('max_referrals_per_user')->nullable(); // Max parrainages par utilisateur
            $table->integer('max_total_referrals')->nullable(); // Max parrainages total
            $table->integer('current_referral_count')->default(0); // Compteur actuel
            
            // Délais et validité
            $table->integer('referral_link_valid_days')->default(30); // Validité du lien
            $table->integer('reward_delay_days')->default(0); // Délai avant versement récompense
            $table->integer('reward_expiry_days')->nullable(); // Expiration de la récompense
            
            // Configuration de tracking
            $table->enum('tracking_method', ['link', 'code', 'email', 'both'])->default('link');
            $table->boolean('requires_email_verification')->default(true);
            $table->boolean('allow_self_referral')->default(false);
            
            // Audience cible
            $table->json('eligible_plans')->nullable(); // Plans éligibles
            $table->json('excluded_users')->nullable(); // Utilisateurs exclus
            $table->boolean('new_users_only')->default(true); // Nouveaux utilisateurs seulement
            
            // Communication et marketing
            $table->json('email_templates')->nullable(); // Templates email
            $table->string('landing_page_url')->nullable();
            $table->json('social_share_settings')->nullable(); // Paramètres partage social
            $table->string('success_message')->nullable();
            
            // Gamification
            $table->integer('points_per_referral')->default(0); // Points de fidélité
            $table->json('milestone_rewards')->nullable(); // Récompenses par paliers
            $table->json('leaderboard_settings')->nullable(); // Configuration classement
            
            // Analytics
            $table->integer('total_referrals')->default(0);
            $table->integer('successful_referrals')->default(0);
            $table->integer('pending_referrals')->default(0);
            $table->decimal('conversion_rate', 5, 2)->default(0);
            $table->decimal('total_rewards_paid', 10, 2)->default(0);
            
            // Attribution
            $table->foreignId('created_by')->constrained('internal_employees')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('internal_employees')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            
            // Métadonnées
            $table->json('terms_and_conditions')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            // Index
            $table->index(['code', 'status']);
            $table->index(['status', 'start_date', 'end_date']);
            $table->index(['created_by', 'status']);
            $table->index(['is_unlimited', 'status']);
            $table->index(['new_users_only', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_programs');
    }
};