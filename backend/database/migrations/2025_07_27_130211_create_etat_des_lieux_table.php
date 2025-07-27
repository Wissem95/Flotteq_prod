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
        Schema::create('etat_des_lieux', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->enum('type', ['depart', 'retour'])->default('depart');
            $table->string('conducteur')->nullable();
            $table->integer('kilometrage');
            $table->text('notes')->nullable();
            $table->json('photos')->nullable(); // Stockage des URLs des 9 photos obligatoires
            $table->boolean('is_validated')->default(false);
            $table->timestamp('validated_at')->nullable();
            $table->foreignId('validated_by')->nullable()->constrained('users');
            $table->timestamps();
            
            $table->index(['vehicle_id', 'created_at']);
            $table->index(['tenant_id', 'created_at']);
            $table->index(['type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('etat_des_lieux');
    }
};