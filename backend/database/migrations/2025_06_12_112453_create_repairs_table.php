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
        Schema::create('repairs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->text('description');
            $table->date('repair_date');
            $table->decimal('total_cost', 10, 2);
            $table->string('workshop');
            $table->integer('mileage');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'warranty'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['vehicle_id', 'repair_date']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repairs');
    }
};
