<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotion_rules', function (Blueprint $table) {
            $table->id();
            
            // Relation avec la promotion
            $table->foreignId('promotion_id')->constrained()->onDelete('cascade');
            
            // Type de règle
            $table->enum('rule_type', [
                'user_attribute',     // Attribut utilisateur
                'subscription_type',  // Type d'abonnement
                'usage_count',       // Nombre d'utilisations
                'date_range',        // Plage de dates
                'geographic',        // Géographique
                'company_size',      // Taille d'entreprise
                'referral_source',   // Source de parrainage
                'custom'             // Règle personnalisée
            ])->index();
            
            // Configuration de la règle
            $table->string('attribute_name')->nullable(); // Nom de l'attribut à vérifier
            $table->enum('operator', ['equals', 'not_equals', 'greater_than', 'less_than', 'contains', 'in', 'not_in', 'between'])->default('equals');
            $table->json('value'); // Valeur(s) à comparer
            
            // Logique booléenne
            $table->enum('logic_operator', ['AND', 'OR'])->default('AND'); // Opérateur avec la règle suivante
            $table->integer('group_id')->default(0); // Groupement de règles
            $table->integer('priority')->default(0); // Priorité d'évaluation
            
            // Configuration avancée
            $table->boolean('is_active')->default(true);
            $table->boolean('is_required')->default(true); // Règle obligatoire ou optionnelle
            $table->text('error_message')->nullable(); // Message d'erreur personnalisé
            
            // Contexte d'application
            $table->enum('applies_to', ['qualification', 'activation', 'both'])->default('both');
            $table->boolean('check_at_signup')->default(true);
            $table->boolean('check_at_renewal')->default(false);
            
            // Plages de valeurs pour règles numériques
            $table->decimal('min_value', 10, 2)->nullable();
            $table->decimal('max_value', 10, 2)->nullable();
            $table->string('unit')->nullable(); // Unité (days, months, users, vehicles, etc.)
            
            // Géolocalisation pour règles géographiques
            $table->json('geographic_data')->nullable(); // Pays, régions, codes postaux
            $table->json('coordinates')->nullable(); // Coordonnées géographiques
            $table->integer('radius_km')->nullable(); // Rayon en kilomètres
            
            // Dates pour règles temporelles
            $table->timestamp('rule_start_date')->nullable();
            $table->timestamp('rule_end_date')->nullable();
            $table->json('time_slots')->nullable(); // Créneaux horaires
            $table->json('weekdays')->nullable(); // Jours de la semaine
            
            // Expressions personnalisées
            $table->text('custom_expression')->nullable(); // Expression logique personnalisée
            $table->json('expression_variables')->nullable(); // Variables pour l'expression
            
            // Statistiques
            $table->integer('evaluation_count')->default(0); // Nombre d'évaluations
            $table->integer('success_count')->default(0); // Nombre de succès
            $table->integer('failure_count')->default(0); // Nombre d'échecs
            $table->decimal('success_rate', 5, 2)->default(0); // Taux de succès
            
            // Métadonnées
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            // Index pour performances
            $table->index(['promotion_id', 'is_active']);
            $table->index(['rule_type', 'is_active']);
            $table->index(['group_id', 'priority']);
            $table->index(['applies_to', 'is_active']);
            $table->index(['attribute_name', 'operator']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_rules');
    }
};