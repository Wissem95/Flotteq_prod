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
        Schema::create('maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->enum('maintenance_type', ['oil_change', 'revision', 'tires', 'brakes', 'belt', 'filters', 'other']);
            $table->text('description');
            $table->date('maintenance_date');
            $table->integer('mileage');
            $table->decimal('cost', 10, 2);
            $table->string('workshop');
            $table->date('next_maintenance')->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['vehicle_id', 'maintenance_date']);
            $table->index('maintenance_type');
            $table->index('status');
            $table->index('next_maintenance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenances');
    }
};
