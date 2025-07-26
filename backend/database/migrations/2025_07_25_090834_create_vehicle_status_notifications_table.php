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
        Schema::create('vehicle_status_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            $table->foreignId('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreignId('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->string('old_status', 50);
            $table->string('new_status', 50);
            $table->text('message');
            $table->text('reason')->nullable();
            $table->boolean('is_read')->default(false);
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->string('type', 50)->default('status_change');
            $table->timestamps();

            $table->index(['user_id', 'tenant_id', 'is_read']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_status_notifications');
    }
};