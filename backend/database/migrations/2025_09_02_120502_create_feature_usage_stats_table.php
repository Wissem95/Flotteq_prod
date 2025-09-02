<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feature_usage_stats', function (Blueprint $table) {
            $table->id();
            
            // Identification de l'usage
            $table->string('feature_key')->index(); // Clé de la fonctionnalité
            $table->enum('feature_source', ['core', 'bonus', 'flag', 'integration'])->default('core')->index();
            $table->foreignId('tenant_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            
            // Contexte temporel
            $table->timestamp('used_at'); // Timestamp d'utilisation
            $table->date('usage_date')->index(); // Date pour agrégations
            $table->integer('usage_hour')->index(); // Heure (0-23) pour analyses
            $table->integer('usage_weekday')->index(); // Jour de la semaine (1-7)
            $table->integer('usage_week')->index(); // Numéro de semaine
            $table->integer('usage_month')->index(); // Mois (1-12)
            $table->integer('usage_year')->index(); // Année
            
            // Type d'utilisation
            $table->enum('usage_type', [
                'access',           // Accès à la fonctionnalité
                'action',           // Action spécifique
                'view',             // Consultation
                'configuration',    // Configuration
                'api_call',         // Appel API
                'export',           // Export de données
                'import',           // Import de données
                'custom'            // Usage personnalisé
            ])->default('access')->index();
            
            // Détails de l'usage
            $table->string('action_type')->nullable()->index(); // Type d'action spécifique
            $table->text('action_details')->nullable(); // Détails de l'action
            $table->json('parameters')->nullable(); // Paramètres utilisés
            $table->integer('duration_seconds')->nullable(); // Durée d'utilisation
            $table->boolean('successful')->default(true); // Usage réussi
            
            // Contexte de session
            $table->string('session_id')->nullable()->index(); // ID de session
            $table->string('request_id')->nullable(); // ID de requête
            $table->integer('sequence_in_session')->default(1); // Numéro dans la session
            
            // Métriques quantitatives
            $table->integer('count_value')->default(1); // Valeur de comptage
            $table->decimal('numeric_value', 12, 4)->nullable(); // Valeur numérique (si applicable)
            $table->string('unit')->nullable(); // Unité de mesure
            $table->decimal('cost_incurred', 8, 4)->default(0); // Coût encouru
            
            // Contexte technique
            $table->string('user_agent')->nullable(); // User agent
            $table->string('ip_address', 45)->nullable(); // Adresse IP
            $table->string('platform')->nullable(); // Plateforme (web, mobile, api)
            $table->string('device_type')->nullable(); // Type d'appareil
            $table->string('browser_name')->nullable(); // Navigateur
            $table->string('os_name')->nullable(); // Système d'exploitation
            
            // Géolocalisation
            $table->string('country_code', 2)->nullable(); // Code pays
            $table->string('region')->nullable(); // Région
            $table->string('city')->nullable(); // Ville
            $table->string('timezone')->nullable(); // Fuseau horaire
            
            // Attribution et source
            $table->string('referrer_source')->nullable(); // Source de référence
            $table->string('utm_source')->nullable(); // Source UTM
            $table->string('utm_campaign')->nullable(); // Campagne UTM
            $table->string('feature_discovery_method')->nullable(); // Comment trouvé la feature
            
            // Expérience utilisateur
            $table->enum('user_experience', ['excellent', 'good', 'average', 'poor', 'error'])->nullable();
            $table->integer('response_time_ms')->nullable(); // Temps de réponse
            $table->boolean('encountered_error')->default(false); // Erreur rencontrée
            $table->text('error_message')->nullable(); // Message d'erreur
            $table->integer('error_code')->nullable(); // Code d'erreur
            
            // Contexte business
            $table->string('subscription_plan')->nullable(); // Plan d'abonnement
            $table->decimal('account_value', 10, 2)->nullable(); // Valeur du compte
            $table->integer('account_age_days')->nullable(); // Âge du compte
            $table->boolean('is_trial_user')->default(false); // Utilisateur en essai
            $table->integer('user_tenure_days')->nullable(); // Ancienneté utilisateur
            
            // Limites et quotas
            $table->integer('quota_before_usage')->nullable(); // Quota avant usage
            $table->integer('quota_after_usage')->nullable(); // Quota après usage
            $table->boolean('exceeded_quota')->default(false); // Quota dépassé
            $table->decimal('quota_percentage_used', 5, 2)->nullable(); // % quota utilisé
            
            // Contexte de fonctionnalité
            $table->string('feature_version')->nullable(); // Version de la fonctionnalité
            $table->json('feature_config')->nullable(); // Configuration active
            $table->boolean('is_premium_feature')->default(false); // Fonctionnalité premium
            $table->boolean('requires_upgrade')->default(false); // Nécessite upgrade
            
            // Efficacité et valeur
            $table->decimal('time_saved_seconds', 10, 2)->nullable(); // Temps économisé
            $table->decimal('productivity_gain', 8, 4)->nullable(); // Gain de productivité
            $table->decimal('business_value', 10, 2)->nullable(); // Valeur business générée
            $table->enum('user_satisfaction', ['very_satisfied', 'satisfied', 'neutral', 'dissatisfied', 'very_dissatisfied'])->nullable();
            
            // A/B Testing et experiments
            $table->json('active_experiments')->nullable(); // Tests A/B actifs
            $table->string('variant_group')->nullable(); // Groupe de variant
            $table->json('experiment_data')->nullable(); // Données d'expérimentation
            
            // Comportement utilisateur
            $table->enum('usage_frequency', ['first_time', 'occasional', 'regular', 'power_user'])->nullable();
            $table->integer('previous_usage_count')->default(0); // Utilisations précédentes
            $table->timestamp('last_usage_before')->nullable(); // Dernière utilisation avant
            $table->integer('days_since_last_usage')->nullable(); // Jours depuis dernier usage
            
            // Flux et parcours
            $table->string('previous_action')->nullable(); // Action précédente
            $table->string('next_action')->nullable(); // Action suivante (si connue)
            $table->string('entry_point')->nullable(); // Point d'entrée dans la feature
            $table->string('exit_point')->nullable(); // Point de sortie
            $table->json('user_journey_stage')->nullable(); // Étape du parcours
            
            // Performance et optimisation
            $table->integer('memory_usage_mb')->nullable(); // Usage mémoire
            $table->integer('cpu_usage_percent')->nullable(); // Usage CPU
            $table->integer('network_requests_count')->nullable(); // Nombre requêtes réseau
            $table->integer('database_queries_count')->nullable(); // Nombre requêtes DB
            
            // Agrégations et rollup
            $table->boolean('is_aggregated')->default(false); // Enregistrement agrégé
            $table->enum('aggregation_level', ['raw', 'hourly', 'daily', 'weekly', 'monthly'])->default('raw');
            $table->integer('aggregated_count')->default(1); // Nombre d'événements agrégés
            
            // Qualité des données
            $table->enum('data_quality', ['high', 'medium', 'low', 'unknown'])->default('high');
            $table->boolean('is_synthetic')->default(false); // Donnée synthétique/test
            $table->boolean('is_bot_usage')->default(false); // Usage par bot détecté
            
            // Confidentialité et conformité
            $table->boolean('anonymized')->default(false); // Données anonymisées
            $table->date('anonymize_after')->nullable(); // Date d'anonymisation
            $table->boolean('contains_pii')->default(false); // Contient données personnelles
            $table->json('compliance_tags')->nullable(); // Tags de conformité
            
            // Métadonnées et contexte
            $table->json('custom_dimensions')->nullable(); // Dimensions personnalisées
            $table->json('business_context')->nullable(); // Contexte business spécifique
            $table->json('technical_context')->nullable(); // Contexte technique
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            // Index composites pour analyses performantes
            $table->index(['feature_key', 'usage_date', 'tenant_id'], 'feature_daily_tenant_idx');
            $table->index(['tenant_id', 'user_id', 'usage_date'], 'user_daily_usage_idx');
            $table->index(['feature_source', 'feature_key', 'usage_type'], 'feature_usage_type_idx');
            $table->index(['usage_date', 'usage_hour', 'successful'], 'temporal_success_idx');
            $table->index(['subscription_plan', 'is_premium_feature', 'usage_date'], 'plan_feature_idx');
            $table->index(['country_code', 'usage_date'], 'geo_usage_idx');
            $table->index(['platform', 'device_type', 'usage_date'], 'platform_usage_idx');
            $table->index(['is_trial_user', 'feature_key', 'usage_date'], 'trial_feature_idx');
            $table->index(['exceeded_quota', 'quota_percentage_used'], 'quota_analysis_idx');
            $table->index(['active_experiments', 'variant_group'], 'experiment_idx');
            
            // Index pour requêtes fréquentes
            $table->index(['feature_key', 'used_at']);
            $table->index(['usage_type', 'successful']);
            $table->index(['session_id', 'sequence_in_session']);
            $table->index(['encountered_error', 'error_code']);
            $table->index(['is_aggregated', 'aggregation_level']);
            $table->index(['anonymized', 'anonymize_after']);
            $table->index(['data_quality', 'is_synthetic']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_usage_stats');
    }
};