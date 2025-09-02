<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_dashboards', function (Blueprint $table) {
            $table->id();
            
            // Informations de base
            $table->string('name');
            $table->string('slug')->unique(); // URL-friendly identifier
            $table->text('description')->nullable();
            $table->enum('type', ['system', 'custom', 'template', 'shared'])->default('custom')->index();
            
            // Propriété et accès
            $table->foreignId('owner_id')->constrained('internal_employees')->onDelete('cascade');
            $table->enum('visibility', ['private', 'shared', 'public', 'organization'])->default('private');
            $table->json('shared_with')->nullable(); // IDs des employés avec accès
            $table->json('permissions')->nullable(); // Permissions granulaires
            
            // Configuration du dashboard
            $table->json('layout')->nullable(); // Configuration de la mise en page (grille, colonnes)
            $table->json('widgets')->nullable(); // Configuration des widgets
            $table->json('filters')->nullable(); // Filtres par défaut
            $table->json('time_range')->nullable(); // Plage temporelle par défaut
            
            // Thème et style
            $table->string('theme', 20)->default('default'); // Theme du dashboard
            $table->json('color_scheme')->nullable(); // Schéma de couleurs personnalisé
            $table->json('styling_options')->nullable(); // Options de style avancées
            
            // Données et sources
            $table->json('data_sources')->nullable(); // Sources de données utilisées
            $table->json('metrics_config')->nullable(); // Configuration des métriques
            $table->json('chart_configurations')->nullable(); // Config des graphiques
            $table->boolean('real_time_updates')->default(false); // Mises à jour temps réel
            $table->integer('refresh_interval_seconds')->default(300); // Intervalle de rafraîchissement
            
            // État et statut
            $table->enum('status', ['draft', 'active', 'archived', 'error'])->default('draft')->index();
            $table->boolean('is_favorite')->default(false);
            $table->integer('usage_count')->default(0); // Nombre d'accès
            $table->timestamp('last_accessed_at')->nullable();
            
            // Versions et historique
            $table->integer('version')->default(1);
            $table->json('version_history')->nullable(); // Historique des versions
            $table->foreignId('last_modified_by')->nullable()->constrained('internal_employees')->onDelete('set null');
            $table->timestamp('last_modified_at')->nullable();
            
            // Performance et cache
            $table->json('cache_settings')->nullable(); // Paramètres de cache
            $table->integer('load_time_ms')->nullable(); // Temps de chargement moyen
            $table->json('performance_metrics')->nullable(); // Métriques de performance
            
            // Export et partage
            $table->boolean('allow_export')->default(true);
            $table->json('export_formats')->nullable(); // Formats d'export supportés
            $table->string('share_token')->nullable(); // Token de partage public
            $table->timestamp('share_expires_at')->nullable();
            
            // Alertes et notifications
            $table->boolean('has_alerts')->default(false);
            $table->json('alert_conditions')->nullable(); // Conditions d'alerte
            $table->json('notification_settings')->nullable(); // Paramètres de notification
            
            // Planification et automatisation
            $table->boolean('is_scheduled')->default(false);
            $table->json('schedule_config')->nullable(); // Configuration de planification
            $table->json('automation_rules')->nullable(); // Règles d'automatisation
            $table->timestamp('next_scheduled_run')->nullable();
            
            // Intégration et embedding
            $table->boolean('embeddable')->default(false);
            $table->string('embed_token')->nullable(); // Token pour embedding
            $table->json('embed_settings')->nullable(); // Paramètres d'intégration
            
            // Analytics sur l'usage
            $table->json('usage_analytics')->nullable(); // Analytics d'usage du dashboard
            $table->integer('unique_viewers')->default(0);
            $table->decimal('average_session_duration', 8, 2)->default(0); // En minutes
            $table->json('popular_widgets')->nullable(); // Widgets les plus consultés
            
            // Compliance et audit
            $table->boolean('audit_access')->default(false); // Auditer les accès
            $table->json('audit_log')->nullable(); // Log d'audit récent
            $table->json('data_retention_rules')->nullable(); // Règles de rétention
            
            // Catégorisation et tags
            $table->string('category')->nullable()->index(); // Catégorie du dashboard
            $table->json('tags')->nullable(); // Tags pour classification
            $table->integer('priority')->default(0); // Priorité d'affichage
            
            // Métadonnées et personnalisation
            $table->string('icon')->nullable(); // Icône du dashboard
            $table->string('thumbnail_path')->nullable(); // Aperçu miniature
            $table->json('custom_fields')->nullable(); // Champs personnalisables
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            // Index pour performances
            $table->index(['owner_id', 'status']);
            $table->index(['type', 'visibility', 'status']);
            $table->index(['slug', 'status']);
            $table->index(['category', 'status']);
            $table->index(['is_favorite', 'owner_id']);
            $table->index(['usage_count', 'last_accessed_at']);
            $table->index(['real_time_updates', 'refresh_interval_seconds']);
            $table->index(['is_scheduled', 'next_scheduled_run']);
            $table->index(['share_token', 'share_expires_at']);
            $table->index(['embed_token', 'embeddable']);
            $table->index(['priority', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_dashboards');
    }
};