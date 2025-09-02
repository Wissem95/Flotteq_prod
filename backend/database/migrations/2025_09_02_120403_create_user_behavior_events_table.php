<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_behavior_events', function (Blueprint $table) {
            $table->id();
            
            // Identification de l'événement
            $table->string('event_id')->unique()->index(); // UUID de l'événement
            $table->string('event_type')->index(); // page_view, click, form_submit, etc.
            $table->string('event_category')->index(); // navigation, engagement, conversion, etc.
            $table->string('event_action')->index(); // Action spécifique
            $table->string('event_label')->nullable(); // Label descriptif
            
            // Acteurs de l'événement
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('session_id')->index(); // ID de session
            $table->string('visitor_id')->index(); // ID visiteur (pour utilisateurs anonymes)
            
            // Contexte temporel
            $table->timestamp('occurred_at'); // Timestamp précis de l'événement
            $table->date('event_date')->index(); // Date pour agrégations
            $table->integer('event_hour')->index(); // Heure (0-23) pour analyses horaires
            $table->integer('event_weekday')->index(); // Jour semaine (1-7) pour analyses
            
            // Contexte de session
            $table->integer('session_duration_seconds')->nullable(); // Durée session à ce moment
            $table->integer('session_page_views')->default(1); // Nombre pages vues dans session
            $table->integer('session_events_count')->default(1); // Nombre événements dans session
            $table->boolean('is_first_session')->default(false); // Première session utilisateur
            $table->boolean('is_bounce_session')->default(false); // Session bounce (1 page)
            
            // Contexte de page/écran
            $table->text('page_url')->nullable(); // URL de la page
            $table->string('page_title')->nullable(); // Titre de la page
            $table->string('page_path')->nullable()->index(); // Chemin de la page
            $table->text('referrer_url')->nullable(); // URL de référence
            $table->string('referrer_domain')->nullable(); // Domaine de référence
            
            // Élément interagit
            $table->string('element_id')->nullable(); // ID de l'élément
            $table->string('element_class')->nullable(); // Classes CSS
            $table->string('element_tag')->nullable(); // Tag HTML
            $table->text('element_text')->nullable(); // Texte de l'élément
            $table->json('element_attributes')->nullable(); // Attributs de l'élément
            
            // Position et interaction
            $table->integer('click_x')->nullable(); // Position X du clic
            $table->integer('click_y')->nullable(); // Position Y du clic
            $table->integer('scroll_depth_percent')->nullable(); // Profondeur de scroll
            $table->integer('time_on_page_seconds')->nullable(); // Temps sur la page
            $table->integer('viewport_width')->nullable(); // Largeur viewport
            $table->integer('viewport_height')->nullable(); // Hauteur viewport
            
            // Données formulaire (si applicable)
            $table->json('form_data')->nullable(); // Données de formulaire (anonymisées)
            $table->string('form_id')->nullable(); // ID du formulaire
            $table->boolean('form_validation_passed')->nullable(); // Validation réussie
            $table->json('form_errors')->nullable(); // Erreurs de validation
            
            // Contexte technique
            $table->string('user_agent')->nullable(); // User agent
            $table->string('browser_name')->nullable(); // Nom du navigateur
            $table->string('browser_version')->nullable(); // Version navigateur
            $table->string('os_name')->nullable(); // Système d'exploitation
            $table->string('device_type')->nullable(); // desktop, mobile, tablet
            $table->boolean('is_mobile')->default(false); // Appareil mobile
            
            // Géolocalisation
            $table->string('ip_address', 45)->nullable(); // Adresse IP (IPv6 compatible)
            $table->string('country_code', 2)->nullable(); // Code pays
            $table->string('region')->nullable(); // Région/État
            $table->string('city')->nullable(); // Ville
            $table->string('timezone')->nullable(); // Fuseau horaire
            
            // Attribution et source de trafic
            $table->string('utm_source')->nullable(); // Source UTM
            $table->string('utm_medium')->nullable(); // Medium UTM
            $table->string('utm_campaign')->nullable(); // Campagne UTM
            $table->string('utm_term')->nullable(); // Terme UTM
            $table->string('utm_content')->nullable(); // Contenu UTM
            $table->string('traffic_source')->nullable(); // Source de trafic
            $table->string('traffic_medium')->nullable(); // Medium de trafic
            
            // Expérimentation et tests A/B
            $table->json('experiments')->nullable(); // Tests A/B actifs
            $table->json('variants')->nullable(); // Variantes testées
            $table->string('cohort')->nullable(); // Cohorte utilisateur
            
            // Engagement et valeur
            $table->decimal('engagement_score', 8, 4)->nullable(); // Score d'engagement
            $table->decimal('event_value', 10, 2)->nullable(); // Valeur de l'événement
            $table->string('conversion_type')->nullable(); // Type de conversion
            $table->boolean('is_conversion_event')->default(false); // Événement de conversion
            
            // Contexte applicatif
            $table->string('app_version')->nullable(); // Version application
            $table->string('feature_flags')->nullable(); // Feature flags actifs
            $table->json('user_properties')->nullable(); // Propriétés utilisateur
            $table->json('custom_properties')->nullable(); // Propriétés personnalisées
            
            // Performance
            $table->integer('page_load_time_ms')->nullable(); // Temps chargement page
            $table->integer('dom_ready_time_ms')->nullable(); // Temps DOM ready
            $table->integer('first_paint_time_ms')->nullable(); // Temps first paint
            $table->json('performance_metrics')->nullable(); // Métriques de performance
            
            // Erreurs et problèmes
            $table->boolean('has_javascript_error')->default(false); // Erreur JavaScript
            $table->json('javascript_errors')->nullable(); // Détails erreurs JS
            $table->boolean('has_network_error')->default(false); // Erreur réseau
            $table->integer('http_status_code')->nullable(); // Code statut HTTP
            
            // Flux utilisateur
            $table->string('previous_event_type')->nullable(); // Type événement précédent
            $table->integer('sequence_number')->default(1); // Numéro dans séquence
            $table->string('user_journey_stage')->nullable(); // Étape du parcours utilisateur
            $table->json('funnel_data')->nullable(); // Données d'entonnoir
            
            // Traitement et anonymisation
            $table->boolean('is_processed')->default(false); // Événement traité
            $table->timestamp('processed_at')->nullable(); // Timestamp traitement
            $table->boolean('is_anonymized')->default(false); // Données anonymisées
            $table->date('anonymize_after')->nullable(); // Date d'anonymisation
            
            // Qualité et validation
            $table->boolean('is_bot_traffic')->default(false); // Trafic de bot détecté
            $table->decimal('spam_score', 5, 4)->nullable(); // Score de spam (0-1)
            $table->enum('data_quality', ['high', 'medium', 'low', 'suspicious'])->default('high');
            $table->text('quality_notes')->nullable(); // Notes sur la qualité
            
            // Métadonnées
            $table->json('raw_data')->nullable(); // Données brutes originales
            $table->json('metadata')->nullable(); // Métadonnées supplémentaires
            $table->timestamps();
            
            // Index composites pour performances analytiques
            $table->index(['tenant_id', 'event_date', 'event_type'], 'tenant_events_idx');
            $table->index(['user_id', 'occurred_at'], 'user_timeline_idx');
            $table->index(['session_id', 'occurred_at'], 'session_events_idx');
            $table->index(['event_category', 'event_action', 'event_date'], 'events_analysis_idx');
            $table->index(['page_path', 'event_date'], 'page_analytics_idx');
            $table->index(['is_conversion_event', 'conversion_type', 'occurred_at'], 'conversions_idx');
            $table->index(['traffic_source', 'traffic_medium', 'event_date'], 'attribution_idx');
            $table->index(['device_type', 'is_mobile', 'event_date'], 'device_analysis_idx');
            $table->index(['country_code', 'region', 'event_date'], 'geo_analysis_idx');
            $table->index(['is_processed', 'processed_at'], 'processing_idx');
            
            // Index pour requêtes fréquentes
            $table->index(['event_type', 'occurred_at']);
            $table->index(['event_category', 'event_date']);
            $table->index(['visitor_id', 'occurred_at']);
            $table->index(['is_bounce_session', 'session_id']);
            $table->index(['is_bot_traffic', 'data_quality']);
            $table->index(['user_journey_stage', 'sequence_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_behavior_events');
    }
};