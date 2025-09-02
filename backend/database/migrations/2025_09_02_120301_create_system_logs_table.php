<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_logs', function (Blueprint $table) {
            $table->id();
            
            // Informations de base du log
            $table->enum('level', ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'])->index();
            $table->string('channel')->index(); // Ex: auth, api, queue, scheduler, security
            $table->text('message');
            
            // Contexte de l'événement
            $table->string('event_type')->index(); // Ex: user_login, api_call, cron_job, error_occurred
            $table->string('source_component')->nullable(); // Composant source
            $table->string('source_file')->nullable(); // Fichier source
            $table->integer('source_line')->nullable(); // Ligne source
            
            // Entités impliquées
            $table->foreignId('tenant_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('internal_employee_id')->nullable()->constrained()->onDelete('set null');
            
            // Informations de session et requête
            $table->string('session_id')->nullable()->index();
            $table->string('request_id')->nullable()->index(); // UUID de la requête
            $table->string('correlation_id')->nullable()->index(); // ID de corrélation
            $table->string('trace_id')->nullable()->index(); // ID de trace distributed
            
            // Détails HTTP (si applicable)
            $table->string('http_method', 10)->nullable();
            $table->text('url')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('ip_address', 45)->nullable(); // IPv6 compatible
            $table->integer('response_code')->nullable();
            $table->integer('response_time_ms')->nullable();
            
            // Données contextuelles
            $table->json('context')->nullable(); // Données contextuelles structurées
            $table->json('extra_data')->nullable(); // Données supplémentaires
            $table->json('tags')->nullable(); // Tags pour filtrage et recherche
            
            // Détails d'erreur (si applicable)
            $table->string('exception_class')->nullable();
            $table->text('exception_message')->nullable();
            $table->json('stack_trace')->nullable(); // Stack trace complet
            $table->string('error_code')->nullable();
            
            // Métriques de performance
            $table->integer('memory_usage_mb')->nullable();
            $table->integer('peak_memory_mb')->nullable();
            $table->decimal('cpu_usage_percent', 5, 2)->nullable();
            $table->integer('query_count')->nullable();
            $table->integer('query_time_ms')->nullable();
            
            // Classification et filtrage
            $table->enum('environment', ['production', 'staging', 'development', 'testing'])->default('production')->index();
            $table->string('application_version')->nullable();
            $table->string('server_hostname')->nullable();
            
            // Sécurité et conformité
            $table->boolean('is_sensitive')->default(false); // Contient des données sensibles
            $table->boolean('is_personal_data')->default(false); // Contient des données personnelles
            $table->date('retention_until')->nullable(); // Date de rétention
            $table->boolean('is_archived')->default(false);
            
            // Alerting et monitoring
            $table->boolean('triggered_alert')->default(false);
            $table->string('alert_rule_id')->nullable();
            $table->integer('occurrence_count')->default(1); // Compteur d'occurrences similaires
            $table->timestamp('first_occurrence_at');
            $table->timestamp('last_occurrence_at');
            
            // Relations avec d'autres entités
            $table->string('related_entity_type')->nullable(); // Type d'entité liée
            $table->unsignedBigInteger('related_entity_id')->nullable(); // ID d'entité liée
            $table->json('related_entities')->nullable(); // Entités multiples liées
            
            // Géolocalisation (si applicable)
            $table->string('country_code', 2)->nullable();
            $table->string('region')->nullable();
            $table->string('city')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            // Compliance et audit
            $table->boolean('is_audit_log')->default(false); // Log d'audit
            $table->string('compliance_category')->nullable(); // GDPR, HIPAA, etc.
            $table->json('audit_metadata')->nullable(); // Métadonnées d'audit
            
            // Timestamps
            $table->timestamp('logged_at')->index(); // Timestamp précis de l'événement
            $table->timestamps(); // created_at, updated_at
            
            // Index pour performances et recherche
            $table->index(['level', 'channel', 'logged_at']);
            $table->index(['event_type', 'logged_at']);
            $table->index(['tenant_id', 'user_id', 'logged_at']);
            $table->index(['session_id', 'logged_at']);
            $table->index(['environment', 'level', 'logged_at']);
            $table->index(['is_sensitive', 'retention_until']);
            $table->index(['triggered_alert', 'alert_rule_id']);
            $table->index(['related_entity_type', 'related_entity_id']);
            $table->index(['first_occurrence_at', 'last_occurrence_at']);
            $table->index(['is_archived', 'logged_at']);
            
            // Index composites pour requêtes courantes
            $table->index(['channel', 'level', 'environment', 'logged_at'], 'logs_search_idx');
            $table->index(['tenant_id', 'event_type', 'logged_at'], 'tenant_events_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_logs');
    }
};