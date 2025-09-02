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
        Schema::table('vehicles', function (Blueprint $table) {
            // Champs pour la gestion des assurances
            $table->date('insurance_start_date')->nullable()->after('next_ct_date');
            $table->date('insurance_expiry_date')->nullable()->after('insurance_start_date');
            $table->string('insurance_company')->nullable()->after('insurance_expiry_date');
            $table->string('insurance_policy_number')->nullable()->after('insurance_company');
            
            // Indexes pour optimiser les requêtes d'alertes
            $table->index('insurance_expiry_date');
            $table->index(['insurance_expiry_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropIndex(['insurance_expiry_date']);
            $table->dropIndex(['insurance_expiry_date', 'status']);
            
            $table->dropColumn([
                'insurance_start_date',
                'insurance_expiry_date', 
                'insurance_company',
                'insurance_policy_number'
            ]);
        });
    }
};