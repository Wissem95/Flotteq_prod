<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_partner_relations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('partner_id')->constrained()->onDelete('cascade');
            
            // Relation spécifique
            $table->decimal('distance', 8, 2)->nullable(); // Distance en km
            $table->boolean('is_preferred')->default(false);
            $table->decimal('tenant_rating', 3, 2)->nullable(); // Note donnée par ce tenant
            $table->text('tenant_comment')->nullable();
            
            // Historique des interactions
            $table->integer('booking_count')->default(0);
            $table->timestamp('last_booking_at')->nullable();
            $table->timestamp('last_interaction_at')->nullable();
            
            // Tarification spécifique (négociée)
            $table->json('custom_pricing')->nullable();
            
            $table->timestamps();
            
            // Contraintes et index
            $table->unique(['tenant_id', 'partner_id']);
            $table->index(['tenant_id', 'is_preferred']);
            $table->index(['partner_id', 'tenant_rating']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_partner_relations');
    }
};