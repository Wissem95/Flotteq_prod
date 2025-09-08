<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            // Ajouter la colonne status pour suivre l'état de la maintenance
            if (!Schema::hasColumn('maintenances', 'status')) {
                $table->enum('status', [
                    'pending',      // En attente
                    'scheduled',    // Planifiée
                    'in_progress',  // En cours
                    'completed',    // Terminée
                    'cancelled',    // Annulée
                    'overdue'       // En retard
                ])->default('pending')->after('type');
            }
            
            // Ajouter scheduled_date pour la planification (différent de date qui est la date réelle)
            if (!Schema::hasColumn('maintenances', 'scheduled_date')) {
                $table->date('scheduled_date')->nullable()->after('date')
                    ->comment('Date planifiée de la maintenance');
            }
            
            // Ajouter completed_at pour savoir quand c'est terminé
            if (!Schema::hasColumn('maintenances', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('updated_at')
                    ->comment('Date de completion de la maintenance');
            }
            
            // Ajouter notes pour commentaires additionnels
            if (!Schema::hasColumn('maintenances', 'notes')) {
                $table->text('notes')->nullable()->after('reason')
                    ->comment('Notes additionnelles sur la maintenance');
            }
            
            // Ajouter next_maintenance_km pour le kilométrage de la prochaine maintenance
            if (!Schema::hasColumn('maintenances', 'next_maintenance_km')) {
                $table->integer('next_maintenance_km')->nullable()->after('mileage')
                    ->comment('Kilométrage pour la prochaine maintenance');
            }
            
            // Ajouter priority pour gérer l'urgence
            if (!Schema::hasColumn('maintenances', 'priority')) {
                $table->enum('priority', ['low', 'medium', 'high', 'urgent'])
                    ->default('medium')->after('status');
            }
            
            // Ajouter des index pour améliorer les performances
            $table->index(['status', 'scheduled_date']);
            $table->index(['vehicle_id', 'status']);
            $table->index('scheduled_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            // Supprimer les index d'abord
            $table->dropIndex(['status', 'scheduled_date']);
            $table->dropIndex(['vehicle_id', 'status']);
            $table->dropIndex(['scheduled_date']);
            
            // Supprimer les colonnes
            if (Schema::hasColumn('maintenances', 'status')) {
                $table->dropColumn('status');
            }
            
            if (Schema::hasColumn('maintenances', 'scheduled_date')) {
                $table->dropColumn('scheduled_date');
            }
            
            if (Schema::hasColumn('maintenances', 'completed_at')) {
                $table->dropColumn('completed_at');
            }
            
            if (Schema::hasColumn('maintenances', 'notes')) {
                $table->dropColumn('notes');
            }
            
            if (Schema::hasColumn('maintenances', 'next_maintenance_km')) {
                $table->dropColumn('next_maintenance_km');
            }
            
            if (Schema::hasColumn('maintenances', 'priority')) {
                $table->dropColumn('priority');
            }
        });
    }
};