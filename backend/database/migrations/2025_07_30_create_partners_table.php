<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['garage', 'controle_technique', 'assurance'])->index();
            $table->text('description')->nullable();
            
            // Informations contact
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            
            // Géolocalisation
            $table->text('address');
            $table->string('city');
            $table->string('postal_code');
            $table->string('country')->default('France');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            
            // Services et configuration
            $table->json('services')->nullable(); // Types de services proposés
            $table->json('pricing')->nullable(); // Grille tarifaire
            $table->json('availability')->nullable(); // Créneaux disponibles
            $table->json('service_zone')->nullable(); // Zone de couverture (polygon)
            
            // Évaluation et statut
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('rating_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_verified')->default(false);
            
            // Métadonnées
            $table->json('metadata')->nullable(); // Infos spécifiques par type
            $table->timestamps();
            
            // Index pour performances
            $table->index(['type', 'is_active', 'is_verified']);
            $table->index(['city', 'postal_code']);
            $table->index(['latitude', 'longitude']); // Index normal pour SQLite
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partners');
    }
};