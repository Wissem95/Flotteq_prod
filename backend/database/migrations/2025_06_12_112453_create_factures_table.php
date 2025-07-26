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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->string('invoice_number')->unique();
            $table->string('supplier');
            $table->decimal('amount', 10, 2);
            $table->date('invoice_date');
            $table->enum('expense_type', ['fuel', 'repair', 'maintenance', 'insurance', 'technical_inspection', 'other']);
            $table->text('description')->nullable();
            $table->string('file_path')->nullable();
            $table->enum('status', ['pending', 'validated', 'reimbursed'])->default('pending');
            $table->timestamps();

            $table->index(['vehicle_id', 'invoice_date']);
            $table->index('expense_type');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
