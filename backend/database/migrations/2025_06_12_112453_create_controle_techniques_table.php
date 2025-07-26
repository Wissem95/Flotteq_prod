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
        Schema::create('technical_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->date('inspection_date');
            $table->date('expiration_date');
            $table->enum('result', ['favorable', 'favorable_with_minor_defects', 'unfavorable']);
            $table->string('organization');
            $table->string('report_number')->nullable();
            $table->text('observations')->nullable();
            $table->decimal('cost', 8, 2);
            $table->string('report_file')->nullable();
            $table->timestamps();

            $table->index(['vehicle_id', 'expiration_date']);
            $table->index('result');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('technical_inspections');
    }
};
