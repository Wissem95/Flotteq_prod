<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_alerts', function (Blueprint $table) {
            $table->id();
            
            // Informations de base
            $table->string('title');
            $table->text('message');
            $table->enum('type', ['info', 'warning', 'error', 'critical', 'maintenance'])->default('info')->index();
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium')->index();
            
            // Source et contexte
            $table->string('source')->index(); // Ex: database, api, cron, manual, monitoring
            $table->string('component')->nullable(); // Composant concerné
            $table->string('category')->index(); // Ex: performance, security, maintenance, billing
            
            // Entités affectées
            $table->foreignId('tenant_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->json('affected_entities')->nullable(); // IDs des entités affectées
            
            // État et résolution
            $table->enum('status', ['active', 'acknowledged', 'resolved', 'suppressed', 'closed'])->default('active')->index();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            
            // Attribution
            $table->foreignId('assigned_to')->nullable()->constrained('internal_employees')->onDelete('set null');
            $table->foreignId('acknowledged_by')->nullable()->constrained('internal_employees')->onDelete('set null');
            $table->foreignId('resolved_by')->nullable()->constrained('internal_employees')->onDelete('set null');
            
            // Temps de résolution
            $table->integer('acknowledgment_time_minutes')->nullable(); // Temps jusqu'à accusé réception
            $table->integer('resolution_time_minutes')->nullable(); // Temps jusqu'à résolution
            $table->timestamp('target_resolution_time')->nullable(); // Objectif de résolution
            
            // Notifications
            $table->json('notification_channels')->nullable(); // email, slack, sms, webhook
            $table->json('recipients')->nullable(); // Destinataires des notifications
            $table->boolean('notifications_sent')->default(false);
            $table->timestamp('notifications_sent_at')->nullable();
            $table->integer('notification_count')->default(0);
            
            // Règles d'escalade
            $table->json('escalation_rules')->nullable(); // Règles d'escalade automatique
            $table->integer('escalation_level')->default(0); // Niveau d'escalade actuel
            $table->timestamp('next_escalation_at')->nullable();
            
            // Suppression et filtrage
            $table->boolean('is_suppressed')->default(false);
            $table->timestamp('suppressed_until')->nullable();
            $table->text('suppression_reason')->nullable();
            $table->json('suppression_conditions')->nullable(); // Conditions de suppression automatique
            
            // Données techniques
            $table->json('error_details')->nullable(); // Détails de l'erreur
            $table->json('stack_trace')->nullable(); // Stack trace si applicable
            $table->json('context_data')->nullable(); // Données contextuelles
            $table->string('correlation_id')->nullable()->index(); // ID de corrélation
            
            // Métriques et impact
            $table->integer('occurrences_count')->default(1); // Nombre d'occurrences
            $table->timestamp('first_occurrence_at'); // Première occurrence
            $table->timestamp('last_occurrence_at'); // Dernière occurrence
            $table->integer('affected_users_count')->default(0);
            $table->integer('affected_tenants_count')->default(0);
            
            // Actions et remédiation
            $table->json('suggested_actions')->nullable(); // Actions suggérées
            $table->json('taken_actions')->nullable(); // Actions prises
            $table->text('resolution_notes')->nullable(); // Notes de résolution
            $table->boolean('requires_manual_intervention')->default(false);
            
            // Automatisation
            $table->boolean('auto_resolve_enabled')->default(false);
            $table->json('auto_resolve_conditions')->nullable();
            $table->timestamp('auto_resolve_attempted_at')->nullable();
            $table->integer('auto_resolve_attempts')->default(0);
            
            // Classification et tagging
            $table->json('tags')->nullable(); // Tags pour classification
            $table->boolean('is_public')->default(false); // Visible pour les tenants
            $table->string('public_message')->nullable(); // Message public pour les tenants
            
            // Métadonnées
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            // Index pour performances
            $table->index(['status', 'severity', 'created_at']);
            $table->index(['category', 'status']);
            $table->index(['source', 'component', 'status']);
            $table->index(['assigned_to', 'status']);
            $table->index(['tenant_id', 'status', 'created_at']);
            $table->index(['is_suppressed', 'suppressed_until']);
            $table->index(['correlation_id', 'created_at']);
            $table->index(['first_occurrence_at', 'last_occurrence_at']);
            $table->index(['requires_manual_intervention', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_alerts');
    }
};