<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_metrics', function (Blueprint $table) {
            $table->id();
            
            // Identification de la métrique
            $table->string('metric_key')->index(); // Ex: revenue.mrr, users.active_count, churn.monthly_rate
            $table->string('metric_name'); // Nom lisible de la métrique
            $table->string('category')->index(); // revenue, users, performance, engagement, etc.
            $table->string('subcategory')->nullable()->index(); // Sous-catégorie pour organisation
            
            // Valeur et unité
            $table->decimal('value', 20, 8); // Valeur de la métrique (précision haute)
            $table->decimal('previous_value', 20, 8)->nullable(); // Valeur précédente
            $table->string('unit', 20)->nullable(); // Unité (%, €, count, seconds, etc.)
            $table->enum('data_type', ['integer', 'decimal', 'percentage', 'currency', 'ratio'])->default('decimal');
            
            // Temporalité
            $table->timestamp('measured_at'); // Timestamp de la mesure
            $table->date('metric_date'); // Date de la métrique (pour agrégations)
            $table->enum('time_grain', ['minute', 'hour', 'day', 'week', 'month', 'quarter', 'year'])->default('day');
            $table->integer('time_period_value')->nullable(); // Valeur de période (ex: 7 pour 7 jours)
            $table->string('time_period_unit')->nullable(); // Unité de période (days, months, etc.)
            
            // Contexte et dimensions
            $table->foreignId('tenant_id')->nullable()->constrained()->onDelete('cascade');
            $table->json('dimensions')->nullable(); // Dimensions de segmentation (pays, plan, etc.)
            $table->json('filters')->nullable(); // Filtres appliqués lors du calcul
            $table->string('segment')->nullable()->index(); // Segment spécifique
            
            // Calcul et source
            $table->enum('calculation_method', ['direct', 'computed', 'aggregated', 'derived'])->default('direct');
            $table->text('calculation_formula')->nullable(); // Formule de calcul
            $table->json('source_metrics')->nullable(); // Métriques sources pour calculs dérivés
            $table->string('data_source')->nullable(); // Source des données
            $table->json('query_metadata')->nullable(); // Métadonnées de la requête
            
            // Qualité et fiabilité
            $table->enum('quality_score', ['high', 'medium', 'low', 'unknown'])->default('unknown');
            $table->decimal('confidence_level', 5, 4)->nullable(); // Niveau de confiance (0-1)
            $table->boolean('is_estimated')->default(false); // Valeur estimée ou réelle
            $table->text('quality_notes')->nullable(); // Notes sur la qualité
            
            // Comparaisons et variations
            $table->decimal('change_absolute', 20, 8)->nullable(); // Changement absolu
            $table->decimal('change_percentage', 8, 4)->nullable(); // Changement en pourcentage
            $table->enum('trend', ['up', 'down', 'stable', 'unknown'])->default('unknown');
            $table->decimal('trend_strength', 5, 4)->nullable(); // Force de la tendance (0-1)
            
            // Objectifs et benchmarks
            $table->decimal('target_value', 20, 8)->nullable(); // Valeur cible
            $table->decimal('benchmark_value', 20, 8)->nullable(); // Valeur de référence
            $table->decimal('target_variance', 8, 4)->nullable(); // Écart à l'objectif (%)
            $table->enum('target_status', ['above', 'on_target', 'below', 'no_target'])->default('no_target');
            
            // Alertes et seuils
            $table->decimal('warning_threshold', 20, 8)->nullable(); // Seuil d'alerte
            $table->decimal('critical_threshold', 20, 8)->nullable(); // Seuil critique
            $table->boolean('alert_triggered')->default(false);
            $table->timestamp('alert_triggered_at')->nullable();
            $table->enum('alert_level', ['info', 'warning', 'critical'])->nullable();
            
            // Business impact
            $table->enum('business_impact', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->text('impact_description')->nullable();
            $table->json('related_metrics')->nullable(); // Métriques liées
            $table->decimal('revenue_impact', 12, 2)->nullable(); // Impact sur les revenus
            
            // Statistiques descriptives
            $table->decimal('min_value', 20, 8)->nullable(); // Valeur minimum sur la période
            $table->decimal('max_value', 20, 8)->nullable(); // Valeur maximum sur la période
            $table->decimal('avg_value', 20, 8)->nullable(); // Valeur moyenne sur la période
            $table->decimal('median_value', 20, 8)->nullable(); // Valeur médiane
            $table->decimal('std_deviation', 20, 8)->nullable(); // Écart-type
            
            // Saisonnalité et patterns
            $table->json('seasonal_pattern')->nullable(); // Patterns saisonniers
            $table->decimal('seasonality_factor', 8, 4)->nullable(); // Facteur de saisonnalité
            $table->boolean('is_anomaly')->default(false); // Détection d'anomalie
            $table->decimal('anomaly_score', 8, 4)->nullable(); // Score d'anomalie
            
            // Prévisions
            $table->decimal('forecast_value', 20, 8)->nullable(); // Valeur prévue
            $table->decimal('forecast_confidence', 5, 4)->nullable(); // Confiance de la prévision
            $table->string('forecast_model')->nullable(); // Modèle de prévision utilisé
            $table->timestamp('forecast_horizon')->nullable(); // Horizon de prévision
            
            // Reporting et visualisation
            $table->json('display_config')->nullable(); // Configuration d'affichage
            $table->string('chart_type')->nullable(); // Type de graphique recommandé
            $table->json('formatting_rules')->nullable(); // Règles de formatage
            $table->boolean('is_public')->default(false); // Visible publiquement
            
            // Audit et traçabilité
            $table->string('collection_method')->nullable(); // Méthode de collecte
            $table->foreignId('collected_by')->nullable()->constrained('internal_employees')->onDelete('set null');
            $table->timestamp('processed_at')->nullable(); // Timestamp de traitement
            $table->json('processing_metadata')->nullable(); // Métadonnées de traitement
            
            // Performance
            $table->integer('computation_time_ms')->nullable(); // Temps de calcul
            $table->integer('data_freshness_minutes')->nullable(); // Fraîcheur des données
            $table->timestamp('expires_at')->nullable(); // Expiration du cache
            
            // Métadonnées
            $table->text('description')->nullable();
            $table->json('tags')->nullable(); // Tags pour classification
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            // Index composites pour performances
            $table->index(['metric_key', 'metric_date', 'tenant_id'], 'metrics_lookup_idx');
            $table->index(['category', 'subcategory', 'measured_at'], 'category_time_idx');
            $table->index(['tenant_id', 'category', 'metric_date'], 'tenant_metrics_idx');
            $table->index(['time_grain', 'metric_date'], 'time_grain_idx');
            $table->index(['alert_triggered', 'alert_level', 'measured_at'], 'alerts_idx');
            $table->index(['target_status', 'business_impact'], 'targets_idx');
            $table->index(['is_anomaly', 'anomaly_score'], 'anomaly_idx');
            $table->index(['segment', 'category'], 'segment_category_idx');
            $table->index(['data_source', 'quality_score'], 'source_quality_idx');
            $table->index(['is_public', 'category', 'metric_date'], 'public_metrics_idx');
            
            // Index pour recherche et filtrage
            $table->index(['metric_key']);
            $table->index(['category', 'measured_at']);
            $table->index(['measured_at', 'time_grain']);
            $table->index(['trend', 'change_percentage']);
            $table->index(['expires_at', 'processed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_metrics');
    }
};