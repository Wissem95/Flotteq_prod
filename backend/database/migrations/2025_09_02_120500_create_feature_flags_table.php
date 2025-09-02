<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feature_flags', function (Blueprint $table) {
            $table->id();
            
            // Identification de la feature flag
            $table->string('key')->unique(); // Ex: new_dashboard, advanced_analytics, beta_feature
            $table->string('name'); // Nom lisible
            $table->text('description')->nullable();
            $table->string('category')->index(); // ui, api, billing, experimental, etc.
            
            // État et activation
            $table->boolean('is_active')->default(false)->index(); // Feature activée globalement
            $table->enum('status', ['draft', 'active', 'paused', 'deprecated', 'removed'])->default('draft')->index();
            $table->decimal('rollout_percentage', 5, 2)->default(0); // Pourcentage de déploiement (0-100)
            $table->boolean('sticky_rollout')->default(true); // Utilisateurs restent dans leur groupe
            
            // Type et comportement
            $table->enum('flag_type', [
                'boolean',          // Simple on/off
                'multivariate',     // Plusieurs variants
                'percentage',       // Déploiement graduel
                'kill_switch',      // Arrêt d'urgence
                'configuration'     // Configuration dynamique
            ])->default('boolean');
            
            // Variants et configuration
            $table->json('variants')->nullable(); // Variants disponibles (pour multivariate)
            $table->json('variant_weights')->nullable(); // Poids des variants
            $table->string('default_variant')->nullable(); // Variant par défaut
            $table->json('configuration')->nullable(); // Configuration JSON
            
            // Règles de ciblage
            $table->json('targeting_rules')->nullable(); // Règles de ciblage
            $table->json('audience_criteria')->nullable(); // Critères d'audience
            $table->json('user_attributes')->nullable(); // Attributs utilisateur requis
            $table->json('tenant_criteria')->nullable(); // Critères tenant
            
            // Environnements
            $table->json('environments')->nullable(); // Environnements actifs
            $table->boolean('production_ready')->default(false); // Prêt pour production
            $table->boolean('development_only')->default(false); // Développement uniquement
            $table->json('environment_config')->nullable(); // Config par environnement
            
            // Planification et durée
            $table->timestamp('start_date')->nullable(); // Date de début
            $table->timestamp('end_date')->nullable(); // Date de fin
            $table->boolean('has_expiry')->default(false); // A une date d'expiration
            $table->integer('duration_days')->nullable(); // Durée en jours
            
            // Dépendances et prérequis
            $table->json('dependencies')->nullable(); // Features dépendantes
            $table->json('prerequisites')->nullable(); // Prérequis
            $table->json('conflicts')->nullable(); // Features incompatibles
            $table->boolean('requires_restart')->default(false); // Nécessite redémarrage
            
            // Monitoring et métriques
            $table->boolean('track_usage')->default(true); // Suivre l'utilisation
            $table->json('metrics_to_track')->nullable(); // Métriques à suivre
            $table->json('success_criteria')->nullable(); // Critères de succès
            $table->json('kpis')->nullable(); // KPIs à mesurer
            
            // Sécurité et permissions
            $table->json('required_permissions')->nullable(); // Permissions requises
            $table->enum('access_level', ['public', 'internal', 'admin', 'super_admin'])->default('public');
            $table->boolean('requires_authentication')->default(false);
            $table->json('security_constraints')->nullable(); // Contraintes de sécurité
            
            // Attribution et approbation
            $table->foreignId('created_by')->constrained('internal_employees')->onDelete('cascade');
            $table->foreignId('owned_by')->constrained('internal_employees')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('internal_employees')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            
            // Historique et versions
            $table->integer('version')->default(1); // Version de la feature flag
            $table->json('changelog')->nullable(); // Journal des modifications
            $table->timestamp('last_modified_at')->nullable();
            $table->foreignId('last_modified_by')->nullable()->constrained('internal_employees')->onDelete('set null');
            
            // Statistiques d'utilisation
            $table->integer('total_evaluations')->default(0); // Nombre d'évaluations
            $table->integer('positive_evaluations')->default(0); // Évaluations positives
            $table->decimal('adoption_rate', 5, 2)->default(0); // Taux d'adoption
            $table->timestamp('last_evaluation_at')->nullable();
            
            // A/B Testing
            $table->boolean('is_experiment')->default(false); // Est un test A/B
            $table->string('experiment_hypothesis')->nullable(); // Hypothèse du test
            $table->json('experiment_metrics')->nullable(); // Métriques d'expérimentation
            $table->decimal('statistical_significance', 5, 4)->nullable(); // Significativité statistique
            
            // Alertes et notifications
            $table->boolean('enable_alerts')->default(false); // Activer les alertes
            $table->json('alert_conditions')->nullable(); // Conditions d'alerte
            $table->json('notification_channels')->nullable(); // Canaux de notification
            $table->timestamp('last_alert_sent')->nullable();
            
            // Kill switch et urgence
            $table->boolean('is_kill_switch')->default(false); // Peut être désactivée en urgence
            $table->boolean('emergency_disabled')->default(false); // Désactivée en urgence
            $table->timestamp('emergency_disabled_at')->nullable();
            $table->foreignId('emergency_disabled_by')->nullable()->constrained('internal_employees')->onDelete('set null');
            $table->text('emergency_reason')->nullable();
            
            // Documentation et aide
            $table->text('implementation_guide')->nullable(); // Guide d'implémentation
            $table->json('code_examples')->nullable(); // Exemples de code
            $table->string('documentation_url')->nullable(); // URL documentation
            $table->json('troubleshooting_guide')->nullable(); // Guide de dépannage
            
            // Impact et analyse
            $table->enum('business_impact', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->text('impact_analysis')->nullable(); // Analyse d'impact
            $table->json('rollback_plan')->nullable(); // Plan de retour en arrière
            $table->boolean('can_rollback')->default(true); // Peut être annulée
            
            // Métadonnées
            $table->json('tags')->nullable(); // Tags pour classification
            $table->json('custom_properties')->nullable(); // Propriétés personnalisées
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            // Index pour performances
            $table->index(['key', 'is_active']);
            $table->index(['status', 'is_active']);
            $table->index(['category', 'status']);
            $table->index(['flag_type', 'is_active']);
            $table->index(['created_by', 'owned_by']);
            $table->index(['production_ready', 'development_only']);
            $table->index(['start_date', 'end_date', 'has_expiry']);
            $table->index(['is_experiment', 'statistical_significance']);
            $table->index(['emergency_disabled', 'is_kill_switch']);
            $table->index(['business_impact', 'status']);
            $table->index(['rollout_percentage', 'sticky_rollout']);
            $table->index(['track_usage', 'last_evaluation_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_flags');
    }
};