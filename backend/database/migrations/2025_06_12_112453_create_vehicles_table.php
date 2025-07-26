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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('marque');
            $table->string('modele');
            $table->string('immatriculation')->unique();
            $table->string('vin')->unique()->nullable();
            $table->integer('annee');
            $table->string('couleur')->nullable();
            $table->integer('kilometrage')->default(0);
            $table->enum('carburant', ['essence', 'diesel', 'electrique', 'hybride', 'gpl']);
            $table->enum('transmission', ['manuelle', 'automatique']);
            $table->integer('puissance')->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->enum('status', ['active', 'vendu', 'en_reparation', 'en_maintenance', 'hors_service'])->default('active');
            $table->date('last_ct_date')->nullable(); // Date du dernier contrôle technique
            $table->date('next_ct_date')->nullable(); // Date du prochain contrôle technique
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'tenant_id']);
            $table->index('immatriculation');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
