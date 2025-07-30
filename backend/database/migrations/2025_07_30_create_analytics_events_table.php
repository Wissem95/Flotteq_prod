<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            
            // Informations de l'événement
            $table->string('event_type'); // page_view, action, error, etc.
            $table->string('feature')->nullable(); // Feature utilisée
            $table->string('page')->nullable(); // Page concernée
            $table->string('action')->nullable(); // Action spécifique
            
            // Données contextuelles
            $table->json('metadata')->nullable(); // Données spécifiques à l'événement
            $table->json('properties')->nullable(); // Propriétés additionnelles
            
            // Session et navigation
            $table->string('session_id')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('referrer')->nullable();
            
            // Métriques de performance
            $table->integer('duration_ms')->nullable(); // Durée en ms
            $table->boolean('is_error')->default(false);
            $table->text('error_message')->nullable();
            
            $table->timestamp('occurred_at');
            $table->timestamps();
            
            // Index pour analyses rapides
            $table->index(['tenant_id', 'event_type', 'occurred_at']);
            $table->index(['user_id', 'occurred_at']);
            $table->index(['feature', 'occurred_at']);
            $table->index(['session_id', 'occurred_at']);
            $table->index(['is_error', 'occurred_at']);
            
            // Partitioning par date pour performances
            // Note: Nécessiterait une configuration spécifique selon la DB
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
    }
};