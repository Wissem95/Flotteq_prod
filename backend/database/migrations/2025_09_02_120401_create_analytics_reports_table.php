<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_reports', function (Blueprint $table) {
            $table->id();
            
            // Informations de base
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('type', ['standard', 'custom', 'scheduled', 'ad_hoc', 'template'])->default('custom')->index();
            $table->string('category')->index(); // performance, usage, financial, security, etc.
            
            // Propriété et accès
            $table->foreignId('created_by')->constrained('internal_employees')->onDelete('cascade');
            $table->foreignId('owner_id')->constrained('internal_employees')->onDelete('cascade');
            $table->enum('visibility', ['private', 'team', 'organization', 'public'])->default('private');
            $table->json('shared_with')->nullable(); // IDs avec accès
            
            // Configuration du rapport
            $table->json('query_config')->nullable(); // Configuration des requêtes
            $table->json('data_sources')->nullable(); // Sources de données
            $table->json('filters')->nullable(); // Filtres appliqués
            $table->json('grouping')->nullable(); // Regroupements
            $table->json('sorting')->nullable(); // Tri des données
            $table->json('aggregations')->nullable(); // Agrégations (sum, avg, count, etc.)
            
            // Période et temporalité
            $table->enum('time_period_type', ['fixed', 'relative', 'custom'])->default('relative');
            $table->timestamp('period_start')->nullable(); // Début période fixe
            $table->timestamp('period_end')->nullable(); // Fin période fixe
            $table->string('relative_period')->nullable(); // '7d', '30d', '3m', '1y', etc.
            $table->enum('granularity', ['hour', 'day', 'week', 'month', 'quarter', 'year'])->default('day');
            
            // Génération et exécution
            $table->enum('status', ['draft', 'generating', 'completed', 'failed', 'scheduled', 'archived'])->default('draft')->index();
            $table->json('generation_config')->nullable(); // Config de génération
            $table->integer('estimated_rows')->nullable(); // Nombre de lignes estimé
            $table->integer('actual_rows')->nullable(); // Nombre de lignes réel
            $table->integer('generation_time_seconds')->nullable();
            $table->timestamp('generated_at')->nullable();
            
            // Résultats et données
            $table->longText('results_data')->nullable(); // Données du rapport (JSON compressé)
            $table->json('summary_metrics')->nullable(); // Métriques de résumé
            $table->json('charts_data')->nullable(); // Données pour graphiques
            $table->json('tables_data')->nullable(); // Données tabulaires
            $table->decimal('data_size_mb', 8, 2)->default(0); // Taille des données
            
            // Formats et export
            $table->json('output_formats')->nullable(); // Formats de sortie supportés
            $table->string('default_format', 10)->default('json'); // Format par défaut
            $table->json('export_settings')->nullable(); // Paramètres d'export
            $table->boolean('allow_download')->default(true);
            
            // Planification et automatisation
            $table->boolean('is_scheduled')->default(false);
            $table->string('schedule_cron')->nullable(); // Expression cron
            $table->json('schedule_config')->nullable(); // Config détaillée de planification
            $table->timestamp('next_run_at')->nullable();
            $table->timestamp('last_run_at')->nullable();
            $table->integer('run_count')->default(0);
            
            // Distribution et notifications
            $table->json('recipients')->nullable(); // Destinataires du rapport
            $table->json('notification_channels')->nullable(); // Canaux de notification
            $table->boolean('auto_distribute')->default(false);
            $table->json('distribution_rules')->nullable(); // Règles de distribution
            
            // Performance et optimisation
            $table->json('query_performance')->nullable(); // Métriques de performance des requêtes
            $table->integer('cache_duration_seconds')->default(3600); // Durée de cache
            $table->boolean('use_cache')->default(true);
            $table->timestamp('cache_expires_at')->nullable();
            $table->json('optimization_hints')->nullable(); // Conseils d'optimisation
            
            // Comparaison et benchmarking
            $table->boolean('enable_comparison')->default(false);
            $table->json('comparison_periods')->nullable(); // Périodes de comparaison
            $table->json('benchmark_targets')->nullable(); // Objectifs de benchmark
            $table->json('variance_analysis')->nullable(); // Analyse des écarts
            
            // Alertes et seuils
            $table->boolean('has_alerts')->default(false);
            $table->json('alert_rules')->nullable(); // Règles d'alerte
            $table->json('thresholds')->nullable(); // Seuils d'alerte
            $table->timestamp('last_alert_sent')->nullable();
            
            // Historique et versions
            $table->integer('version')->default(1);
            $table->json('version_history')->nullable();
            $table->foreignId('last_modified_by')->nullable()->constrained('internal_employees')->onDelete('set null');
            $table->timestamp('last_modified_at')->nullable();
            
            // Usage et analytics
            $table->integer('view_count')->default(0);
            $table->integer('download_count')->default(0);
            $table->timestamp('last_viewed_at')->nullable();
            $table->json('viewer_analytics')->nullable(); // Analytics des consultations
            $table->decimal('average_rating', 3, 2)->nullable(); // Note moyenne
            $table->integer('rating_count')->default(0);
            
            // Sécurité et compliance
            $table->boolean('contains_pii')->default(false); // Contient des données personnelles
            $table->json('data_classification')->nullable(); // Classification des données
            $table->json('access_controls')->nullable(); // Contrôles d'accès
            $table->date('retention_until')->nullable(); // Date de rétention
            
            // Collaboration et commentaires
            $table->json('comments')->nullable(); // Commentaires sur le rapport
            $table->json('annotations')->nullable(); // Annotations sur les données
            $table->boolean('allow_comments')->default(true);
            
            // Intégration et API
            $table->boolean('api_accessible')->default(false);
            $table->string('api_token')->nullable(); // Token d'accès API
            $table->json('webhook_urls')->nullable(); // URLs de notification
            
            // Fichiers générés
            $table->json('generated_files')->nullable(); // Chemins des fichiers générés
            $table->integer('total_file_size_bytes')->default(0);
            $table->timestamp('files_expire_at')->nullable();
            
            // Métadonnées
            $table->json('tags')->nullable(); // Tags pour classification
            $table->json('custom_attributes')->nullable(); // Attributs personnalisés
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            // Index pour performances
            $table->index(['created_by', 'status']);
            $table->index(['owner_id', 'visibility', 'status']);
            $table->index(['type', 'category', 'status']);
            $table->index(['is_scheduled', 'next_run_at']);
            $table->index(['status', 'generated_at']);
            $table->index(['cache_expires_at', 'use_cache']);
            $table->index(['last_run_at', 'run_count']);
            $table->index(['view_count', 'last_viewed_at']);
            $table->index(['retention_until', 'contains_pii']);
            $table->index(['api_accessible', 'api_token']);
            $table->index(['version', 'last_modified_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_reports');
    }
};