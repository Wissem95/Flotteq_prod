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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['purchase', 'sale']);
            $table->date('date');
            $table->decimal('price', 10, 2);
            $table->integer('mileage')->nullable();
            $table->string('seller_buyer_name');
            $table->string('seller_buyer_contact')->nullable();
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->string('contract_file_path')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'tenant_id']);
            $table->index(['type', 'status']);
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};