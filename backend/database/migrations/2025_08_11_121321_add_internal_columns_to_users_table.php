x<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ajoute les colonnes nécessaires pour les utilisateurs internes
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Colonne pour le rôle interne (admin, manager, etc.)
            $table->string('role_interne', 50)->nullable()->after('role');

            // Colonne pour identifier les utilisateurs internes
            $table->boolean('is_internal')->default(false)->after('role_interne');
        });
    }

    /**
     * Supprime les colonnes ajoutées
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role_interne', 'is_internal']);
        });
    }
};
