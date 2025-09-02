<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_rewards', function (Blueprint $table) {
            $table->id();
            
            // Relations principales
            $table->foreignId('referral_program_id')->constrained()->onDelete('cascade');
            $table->foreignId('referrer_user_id')->constrained('users')->onDelete('cascade'); // Parrain
            $table->foreignId('referee_user_id')->constrained('users')->onDelete('cascade'); // Filleul
            $table->foreignId('referrer_tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('referee_tenant_id')->constrained('tenants')->onDelete('cascade');
            
            // Informations du parrainage
            $table->string('referral_code')->index(); // Code utilisé
            $table->string('tracking_token')->unique(); // Token de suivi
            $table->enum('source', ['direct_link', 'email', 'social_share', 'manual'])->default('direct_link');
            
            // Dates importantes
            $table->timestamp('referral_date'); // Date du parrainage
            $table->timestamp('signup_date')->nullable(); // Date d'inscription du filleul
            $table->timestamp('qualification_date')->nullable(); // Date de qualification
            $table->timestamp('reward_date')->nullable(); // Date d'attribution récompense
            
            // Statut du parrainage
            $table->enum('status', [
                'pending',      // En attente
                'qualified',    // Qualifié mais pas encore récompensé
                'rewarded',     // Récompensé
                'expired',      // Expiré
                'cancelled',    // Annulé
                'disputed'      // Contesté
            ])->default('pending')->index();
            
            // Récompenses
            $table->decimal('referrer_reward_amount', 8, 2)->default(0); // Montant parrain
            $table->decimal('referee_reward_amount', 8, 2)->default(0); // Montant filleul
            $table->string('reward_currency', 3)->default('EUR');
            $table->enum('reward_type', ['discount', 'credit', 'cash', 'upgrade', 'custom'])->default('discount');
            
            // Qualification
            $table->json('qualification_criteria')->nullable(); // Critères remplis
            $table->boolean('is_qualified')->default(false);
            $table->text('qualification_notes')->nullable();
            
            // Paiement des récompenses
            $table->enum('referrer_reward_status', ['pending', 'processed', 'paid', 'failed', 'cancelled'])->default('pending');
            $table->enum('referee_reward_status', ['pending', 'processed', 'paid', 'failed', 'cancelled'])->default('pending');
            $table->timestamp('referrer_paid_at')->nullable();
            $table->timestamp('referee_paid_at')->nullable();
            
            // Références de paiement
            $table->string('referrer_payment_reference')->nullable();
            $table->string('referee_payment_reference')->nullable();
            $table->json('payment_details')->nullable(); // Détails des paiements
            
            // Suivi d'engagement
            $table->integer('referee_login_count')->default(0); // Connexions du filleul
            $table->timestamp('referee_last_login')->nullable();
            $table->decimal('referee_total_spent', 10, 2)->default(0); // Total dépensé par filleul
            $table->integer('referee_subscription_months')->default(0); // Mois d'abonnement
            
            // Analytics et attribution
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('referer_url')->nullable();
            $table->json('browser_info')->nullable();
            $table->string('ip_address')->nullable();
            
            // Validation et contrôle
            $table->foreignId('validated_by')->nullable()->constrained('internal_employees')->onDelete('set null');
            $table->timestamp('validated_at')->nullable();
            $table->text('validation_notes')->nullable();
            
            // Expiration et révocation
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_revoked')->default(false);
            $table->timestamp('revoked_at')->nullable();
            $table->foreignId('revoked_by')->nullable()->constrained('internal_employees')->onDelete('set null');
            $table->text('revocation_reason')->nullable();
            
            // Communication
            $table->boolean('referrer_notified')->default(false);
            $table->boolean('referee_notified')->default(false);
            $table->timestamp('referrer_notification_sent_at')->nullable();
            $table->timestamp('referee_notification_sent_at')->nullable();
            
            // Métadonnées
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            // Index pour performances et analytics
            $table->index(['referrer_user_id', 'status']);
            $table->index(['referee_user_id', 'status']);
            $table->index(['referral_program_id', 'status']);
            $table->index(['status', 'qualification_date']);
            $table->index(['referral_date', 'status']);
            $table->index(['tracking_token']);
            $table->index(['referrer_reward_status', 'referrer_paid_at']);
            $table->index(['referee_reward_status', 'referee_paid_at']);
            $table->index(['is_qualified', 'qualification_date']);
            $table->index(['expires_at', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_rewards');
    }
};