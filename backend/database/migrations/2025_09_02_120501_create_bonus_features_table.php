<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bonus_features', function (Blueprint $table) {
            $table->id();
            
            // Identification de la fonctionnalité bonus
            $table->string('key')->unique(); // Ex: advanced_reporting, priority_support, api_access
            $table->string('name'); // Nom de la fonctionnalité
            $table->text('description');
            $table->text('short_description')->nullable(); // Description courte pour UI
            $table->string('category')->index(); // reporting, support, integration, storage, etc.
            
            // Configuration et type
            $table->enum('feature_type', [
                'limit_increase',   // Augmentation de limite
                'premium_access',   // Accès premium
                'advanced_tool',    // Outil avancé
                'priority_service', // Service prioritaire
                'integration',      // Intégration externe
                'customization'     // Personnalisation
            ])->default('premium_access');
            
            // Statut et disponibilité
            $table->boolean('is_active')->default(true)->index();
            $table->enum('status', ['draft', 'active', 'deprecated', 'retired'])->default('active')->index();
            $table->boolean('is_beta')->default(false); // Fonctionnalité en bêta
            $table->date('availability_start')->nullable(); // Date de disponibilité
            $table->date('availability_end')->nullable(); // Date de fin (si limitée)
            
            // Tarification et plans
            $table->decimal('base_price', 8, 2)->default(0); // Prix de base
            $table->string('pricing_currency', 3)->default('EUR');
            $table->enum('pricing_model', ['fixed', 'per_user', 'per_usage', 'tiered', 'custom'])->default('fixed');
            $table->json('pricing_tiers')->nullable(); // Paliers de prix
            $table->json('included_in_plans')->nullable(); // Plans qui incluent cette feature
            
            // Limites et quotas
            $table->json('usage_limits')->nullable(); // Limites d'utilisation
            $table->json('quota_rules')->nullable(); // Règles de quotas
            $table->boolean('has_usage_tracking')->default(false); // Suivi d'utilisation
            $table->enum('limit_reset_period', ['daily', 'weekly', 'monthly', 'yearly', 'never'])->default('monthly');
            
            // Éligibilité et prérequis
            $table->json('eligibility_criteria')->nullable(); // Critères d'éligibilité
            $table->json('required_plan_features')->nullable(); // Fonctionnalités plan requises
            $table->json('minimum_plan_level')->nullable(); // Niveau de plan minimum
            $table->boolean('requires_approval')->default(false); // Nécessite approbation
            
            // Configuration technique
            $table->json('technical_config')->nullable(); // Configuration technique
            $table->json('api_endpoints')->nullable(); // Endpoints API associés
            $table->json('permissions_required')->nullable(); // Permissions nécessaires
            $table->boolean('requires_integration_setup')->default(false); // Setup d'intégration requis
            
            // Interface utilisateur
            $table->string('icon')->nullable(); // Icône de la fonctionnalité
            $table->string('color_theme', 7)->nullable(); // Couleur thématique (hex)
            $table->json('ui_components')->nullable(); // Composants UI associés
            $table->boolean('show_in_marketplace')->default(true); // Afficher dans marketplace
            $table->integer('display_order')->default(0); // Ordre d'affichage
            
            // Marketing et promotion
            $table->text('marketing_headline')->nullable(); // Titre marketing
            $table->json('benefits')->nullable(); // Bénéfices clés
            $table->json('use_cases')->nullable(); // Cas d'usage
            $table->string('demo_url')->nullable(); // URL de démonstration
            $table->json('screenshots')->nullable(); // Captures d'écran
            $table->json('video_demos')->nullable(); // Vidéos de démo
            
            // Activation et provisioning
            $table->enum('activation_method', ['automatic', 'manual', 'api', 'self_service'])->default('automatic');
            $table->integer('activation_delay_hours')->default(0); // Délai d'activation
            $table->json('provisioning_steps')->nullable(); // Étapes de provisioning
            $table->boolean('requires_user_action')->default(false); // Action utilisateur requise
            
            // Support et documentation
            $table->string('documentation_url')->nullable(); // Documentation
            $table->string('support_level')->default('standard'); // Niveau de support
            $table->json('training_resources')->nullable(); // Ressources de formation
            $table->boolean('has_dedicated_support')->default(false); // Support dédié
            
            // Métriques et analytics
            $table->integer('total_activations')->default(0); // Activations totales
            $table->integer('active_subscriptions')->default(0); // Abonnements actifs
            $table->decimal('revenue_generated', 12, 2)->default(0); // Revenus générés
            $table->decimal('adoption_rate', 5, 2)->default(0); // Taux d'adoption
            $table->json('usage_analytics')->nullable(); // Analytics d'usage
            
            // Feedback et évaluation
            $table->decimal('user_rating', 3, 2)->nullable(); // Note utilisateurs
            $table->integer('rating_count')->default(0); // Nombre d'évaluations
            $table->json('feedback_summary')->nullable(); // Résumé des feedbacks
            $table->json('feature_requests')->nullable(); // Demandes d'amélioration
            
            // Lifecycle et évolution
            $table->enum('lifecycle_stage', ['concept', 'development', 'beta', 'ga', 'mature', 'deprecated'])->default('ga');
            $table->json('roadmap_items')->nullable(); // Éléments roadmap
            $table->date('next_update_planned')->nullable(); // Prochaine mise à jour
            $table->json('version_history')->nullable(); // Historique versions
            
            // Intégrations et dépendances
            $table->json('integration_partners')->nullable(); // Partenaires d'intégration
            $table->json('dependent_features')->nullable(); // Fonctionnalités dépendantes
            $table->json('external_dependencies')->nullable(); // Dépendances externes
            $table->boolean('requires_third_party_service')->default(false); // Service tiers requis
            
            // Compliance et sécurité
            $table->json('compliance_certifications')->nullable(); // Certifications conformité
            $table->json('security_features')->nullable(); // Fonctionnalités de sécurité
            $table->boolean('processes_sensitive_data')->default(false); // Traite données sensibles
            $table->json('data_retention_policy')->nullable(); // Politique de rétention
            
            // Attribution et gouvernance
            $table->foreignId('product_owner_id')->constrained('internal_employees')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('internal_employees')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('internal_employees')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            
            // Contrôle qualité
            $table->boolean('qa_approved')->default(false); // QA approuvé
            $table->timestamp('qa_approved_at')->nullable();
            $table->foreignId('qa_approved_by')->nullable()->constrained('internal_employees')->onDelete('set null');
            $table->json('qa_notes')->nullable(); // Notes QA
            
            // Métadonnées
            $table->json('tags')->nullable(); // Tags pour classification
            $table->json('custom_attributes')->nullable(); // Attributs personnalisés
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            // Index pour performances et recherche
            $table->index(['key', 'is_active']);
            $table->index(['status', 'is_active']);
            $table->index(['category', 'feature_type', 'status']);
            $table->index(['product_owner_id', 'status']);
            $table->index(['is_beta', 'availability_start', 'availability_end']);
            $table->index(['pricing_model', 'base_price']);
            $table->index(['show_in_marketplace', 'display_order']);
            $table->index(['lifecycle_stage', 'status']);
            $table->index(['has_usage_tracking', 'limit_reset_period']);
            $table->index(['requires_approval', 'activation_method']);
            $table->index(['user_rating', 'rating_count']);
            $table->index(['adoption_rate', 'active_subscriptions']);
            $table->index(['qa_approved', 'qa_approved_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bonus_features');
    }
};