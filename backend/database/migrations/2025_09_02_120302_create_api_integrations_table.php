<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_integrations', function (Blueprint $table) {
            $table->id();
            
            // Informations de base
            $table->string('name');
            $table->string('slug')->unique(); // Ex: stripe-payments, mailgun-email, google-maps
            $table->text('description')->nullable();
            $table->enum('type', ['payment', 'email', 'sms', 'maps', 'analytics', 'storage', 'crm', 'custom'])->index();
            $table->string('provider'); // Ex: stripe, mailgun, twilio, google
            
            // Configuration de l'API
            $table->string('api_version')->nullable();
            $table->string('base_url');
            $table->json('endpoints')->nullable(); // Endpoints disponibles
            $table->enum('auth_type', ['api_key', 'oauth2', 'basic_auth', 'bearer_token', 'custom'])->default('api_key');
            
            // Credentials (chiffrés)
            $table->text('api_key')->nullable(); // Chiffré
            $table->text('api_secret')->nullable(); // Chiffré
            $table->text('access_token')->nullable(); // Chiffré
            $table->text('refresh_token')->nullable(); // Chiffré
            $table->timestamp('token_expires_at')->nullable();
            $table->json('additional_credentials')->nullable(); // Autres credentials chiffrés
            
            // Configuration avancée
            $table->json('headers')->nullable(); // Headers par défaut
            $table->json('parameters')->nullable(); // Paramètres par défaut
            $table->integer('timeout_seconds')->default(30);
            $table->integer('retry_attempts')->default(3);
            $table->integer('rate_limit_per_minute')->nullable();
            
            // Webhooks
            $table->boolean('supports_webhooks')->default(false);
            $table->text('webhook_url')->nullable();
            $table->string('webhook_secret')->nullable(); // Chiffré
            $table->json('webhook_events')->nullable(); // Événements écoutés
            $table->timestamp('webhook_verified_at')->nullable();
            
            // Statut et monitoring
            $table->enum('status', ['active', 'inactive', 'maintenance', 'error', 'suspended'])->default('active')->index();
            $table->enum('health_status', ['healthy', 'degraded', 'unhealthy', 'unknown'])->default('unknown');
            $table->timestamp('last_health_check_at')->nullable();
            $table->integer('health_check_interval_minutes')->default(5);
            
            // Métriques de performance
            $table->integer('total_requests')->default(0);
            $table->integer('successful_requests')->default(0);
            $table->integer('failed_requests')->default(0);
            $table->decimal('success_rate', 5, 2)->default(100); // Pourcentage de succès
            $table->integer('average_response_time_ms')->default(0);
            $table->timestamp('last_request_at')->nullable();
            
            // Limitations et quotas
            $table->integer('daily_quota')->nullable(); // Quota journalier
            $table->integer('monthly_quota')->nullable(); // Quota mensuel
            $table->integer('daily_usage')->default(0);
            $table->integer('monthly_usage')->default(0);
            $table->date('usage_reset_date')->nullable();
            
            // Erreurs et debugging
            $table->json('last_errors')->nullable(); // Dernières erreurs
            $table->integer('consecutive_failures')->default(0);
            $table->timestamp('last_failure_at')->nullable();
            $table->boolean('debug_mode')->default(false);
            $table->json('debug_logs')->nullable(); // Logs de debug récents
            
            // Configuration par environnement
            $table->enum('environment', ['production', 'staging', 'development', 'testing'])->default('production');
            $table->boolean('sandbox_mode')->default(false);
            $table->json('environment_configs')->nullable(); // Configs par environnement
            
            // Intégration et dépendances
            $table->json('required_features')->nullable(); // Fonctionnalités requises
            $table->json('optional_features')->nullable(); // Fonctionnalités optionnelles
            $table->json('dependencies')->nullable(); // Dépendances autres APIs
            $table->boolean('is_critical')->default(false); // Critique pour le système
            
            // Sécurité
            $table->boolean('requires_ip_whitelist')->default(false);
            $table->json('allowed_ips')->nullable(); // IPs autorisées
            $table->boolean('ssl_verify')->default(true);
            $table->string('ssl_cert_path')->nullable();
            $table->json('security_settings')->nullable();
            
            // Coûts et facturation
            $table->decimal('cost_per_request', 8, 6)->nullable(); // Coût par requête
            $table->decimal('monthly_cost', 10, 2)->default(0); // Coût mensuel
            $table->string('billing_currency', 3)->default('EUR');
            $table->json('pricing_tiers')->nullable(); // Paliers de prix
            
            // Documentation et support
            $table->string('documentation_url')->nullable();
            $table->string('support_email')->nullable();
            $table->string('status_page_url')->nullable();
            $table->json('contact_info')->nullable();
            
            // Alerting et notifications
            $table->json('alert_conditions')->nullable(); // Conditions d'alerte
            $table->json('notification_channels')->nullable(); // Canaux de notification
            $table->boolean('alerts_enabled')->default(true);
            $table->integer('alert_threshold_failures')->default(5);
            
            // Audit et compliance
            $table->json('data_usage_purposes')->nullable(); // Finalités d'utilisation
            $table->boolean('processes_personal_data')->default(false);
            $table->json('compliance_certifications')->nullable(); // SOC2, ISO27001, etc.
            $table->text('privacy_policy_url')->nullable();
            
            // Attribution et approbation
            $table->foreignId('created_by')->constrained('internal_employees')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('internal_employees')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('managed_by')->nullable()->constrained('internal_employees')->onDelete('set null');
            
            // Métadonnées
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            // Index pour performances
            $table->index(['slug', 'status']);
            $table->index(['type', 'status']);
            $table->index(['provider', 'status']);
            $table->index(['status', 'health_status']);
            $table->index(['environment', 'status']);
            $table->index(['is_critical', 'status']);
            $table->index(['last_health_check_at', 'health_status']);
            $table->index(['created_by', 'status']);
            $table->index(['webhook_verified_at']);
            $table->index(['token_expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_integrations');
    }
};