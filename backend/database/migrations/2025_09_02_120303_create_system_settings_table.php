<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            
            // Identification du paramètre
            $table->string('key')->unique(); // Ex: app.name, mail.from_address, billing.tax_rate
            $table->string('group')->index(); // Ex: app, mail, billing, security, api
            $table->string('name'); // Nom lisible
            $table->text('description')->nullable();
            
            // Valeur et type
            $table->text('value')->nullable(); // Valeur actuelle (peut être JSON)
            $table->text('default_value')->nullable(); // Valeur par défaut
            $table->enum('type', [
                'string', 'integer', 'float', 'boolean', 'json', 
                'array', 'text', 'email', 'url', 'password', 
                'file', 'color', 'date', 'datetime', 'enum'
            ])->default('string');
            
            // Configuration du type
            $table->json('type_config')->nullable(); // Config spécifique au type (enum values, validation rules, etc.)
            $table->json('validation_rules')->nullable(); // Règles de validation Laravel
            $table->text('validation_message')->nullable(); // Message d'erreur personnalisé
            
            // Interface utilisateur
            $table->string('input_type')->nullable(); // text, textarea, select, checkbox, etc.
            $table->string('label')->nullable(); // Label pour l'interface
            $table->text('help_text')->nullable(); // Texte d'aide
            $table->string('placeholder')->nullable(); // Placeholder
            $table->json('ui_options')->nullable(); // Options d'interface (classes CSS, etc.)
            
            // Hiérarchie et organisation
            $table->string('section')->nullable(); // Section dans l'interface
            $table->integer('sort_order')->default(0); // Ordre d'affichage
            $table->foreignId('parent_setting_id')->nullable()->constrained('system_settings')->onDelete('cascade');
            
            // Contraintes et limites
            $table->text('min_value')->nullable(); // Valeur minimum (pour nombres/dates)
            $table->text('max_value')->nullable(); // Valeur maximum (pour nombres/dates)
            $table->integer('max_length')->nullable(); // Longueur maximale (pour chaînes)
            $table->json('allowed_values')->nullable(); // Valeurs autorisées (pour enum)
            
            // Sécurité et accès
            $table->boolean('is_sensitive')->default(false); // Paramètre sensible (masqué dans l'interface)
            $table->boolean('is_encrypted')->default(false); // Valeur chiffrée en base
            $table->enum('access_level', ['public', 'internal', 'admin', 'super_admin'])->default('admin');
            $table->json('required_permissions')->nullable(); // Permissions requises pour modification
            
            // Statut et contrôle
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false); // Paramètre système (non modifiable par UI)
            $table->boolean('is_required')->default(false); // Paramètre obligatoire
            $table->boolean('requires_restart')->default(false); // Nécessite redémarrage de l'app
            
            // Versioning et historique
            $table->json('value_history')->nullable(); // Historique des valeurs précédentes
            $table->integer('version')->default(1); // Version du paramètre
            $table->timestamp('last_modified_at')->nullable();
            $table->foreignId('last_modified_by')->nullable()->constrained('internal_employees')->onDelete('set null');
            
            // Environment et déploiement
            $table->enum('environment_scope', ['all', 'production', 'staging', 'development'])->default('all');
            $table->json('environment_values')->nullable(); // Valeurs par environnement
            $table->boolean('sync_across_environments')->default(false);
            
            // Cache et performance
            $table->boolean('is_cacheable')->default(true);
            $table->integer('cache_ttl_seconds')->default(3600); // TTL du cache
            $table->string('cache_key')->nullable(); // Clé de cache personnalisée
            $table->timestamp('cached_at')->nullable();
            
            // Validation et contraintes métier
            $table->json('business_rules')->nullable(); // Règles métier spécifiques
            $table->text('dependency_expression')->nullable(); // Expression de dépendance
            $table->json('dependent_settings')->nullable(); // Paramètres dépendants
            $table->json('impact_analysis')->nullable(); // Analyse d'impact des modifications
            
            // Audit et compliance
            $table->boolean('audit_changes')->default(false); // Auditer les modifications
            $table->json('compliance_tags')->nullable(); // Tags de conformité (GDPR, etc.)
            $table->text('purpose')->nullable(); // Finalité du paramètre
            $table->date('retention_until')->nullable(); // Date de rétention des logs
            
            // Import/Export et sauvegarde
            $table->boolean('is_exportable')->default(true); // Peut être exporté
            $table->boolean('is_importable')->default(true); // Peut être importé
            $table->json('export_filters')->nullable(); // Filtres d'export
            $table->string('backup_frequency')->nullable(); // Fréquence de sauvegarde
            
            // API et intégration
            $table->boolean('api_accessible')->default(false); // Accessible via API
            $table->json('api_permissions')->nullable(); // Permissions API
            $table->string('webhook_event')->nullable(); // Événement webhook à déclencher
            
            // Monitoring et alerting
            $table->json('monitoring_rules')->nullable(); // Règles de monitoring
            $table->boolean('alert_on_change')->default(false); // Alerter lors des modifications
            $table->json('alert_conditions')->nullable(); // Conditions d'alerte
            $table->json('alert_recipients')->nullable(); // Destinataires des alertes
            
            // Documentation et aide
            $table->text('documentation_url')->nullable();
            $table->json('examples')->nullable(); // Exemples de valeurs
            $table->text('change_log')->nullable(); // Journal des modifications
            $table->json('related_settings')->nullable(); // Paramètres liés
            
            // Métadonnées
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            // Index pour performances
            $table->index(['group', 'is_active']);
            $table->index(['key', 'is_active']);
            $table->index(['type', 'group']);
            $table->index(['access_level', 'is_active']);
            $table->index(['environment_scope', 'is_active']);
            $table->index(['is_system', 'is_required']);
            $table->index(['parent_setting_id', 'sort_order']);
            $table->index(['last_modified_at', 'last_modified_by']);
            $table->index(['is_cacheable', 'cached_at']);
            $table->index(['section', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};