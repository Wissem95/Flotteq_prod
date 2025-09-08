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
        // Vérifier et ajouter les champs manquants s'ils n'existent pas
        Schema::table('vehicles', function (Blueprint $table) {
            // S'assurer que tous les champs du modèle existent
            if (!Schema::hasColumn('vehicles', 'insurance_start_date')) {
                $table->date('insurance_start_date')->nullable()->after('next_ct_date');
            }
            
            if (!Schema::hasColumn('vehicles', 'insurance_expiry_date')) {
                $table->date('insurance_expiry_date')->nullable()->after('insurance_start_date');
            }
            
            if (!Schema::hasColumn('vehicles', 'insurance_company')) {
                $table->string('insurance_company')->nullable()->after('insurance_expiry_date');
            }
            
            if (!Schema::hasColumn('vehicles', 'insurance_policy_number')) {
                $table->string('insurance_policy_number')->nullable()->after('insurance_company');
            }
        });

        // Ajouter les indexes manquants s'ils n'existent pas
        Schema::table('vehicles', function (Blueprint $table) {
            // Vérifier si l'index n'existe pas avant de l'ajouter
            if (!$this->indexExists('vehicles', 'vehicles_insurance_expiry_date_index')) {
                $table->index('insurance_expiry_date');
            }
            
            if (!$this->indexExists('vehicles', 'vehicles_insurance_expiry_date_status_index')) {
                $table->index(['insurance_expiry_date', 'status']);
            }
            
            // Index pour les requêtes de performance communes
            if (!$this->indexExists('vehicles', 'vehicles_tenant_id_status_index')) {
                $table->index(['tenant_id', 'status']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            // Supprimer les indexes ajoutés
            try {
                $table->dropIndex(['tenant_id', 'status']);
                $table->dropIndex(['insurance_expiry_date', 'status']);
                $table->dropIndex(['insurance_expiry_date']);
            } catch (\Exception $e) {
                // Ignore if indexes don't exist
            }
        });
    }

    /**
     * Check if index exists
     */
    private function indexExists($table, $index): bool
    {
        try {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = $sm->listTableIndexes($table);
            
            return array_key_exists($index, $indexes);
        } catch (\Exception $e) {
            return false;
        }
    }
};