<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commission_records', function (Blueprint $table) {
            $table->id();
            
            // Relations
            $table->foreignId('partner_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('transaction_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('revenue_record_id')->nullable()->constrained()->onDelete('set null');
            
            // Informations de commission
            $table->decimal('base_amount', 10, 2); // Montant de base pour le calcul
            $table->decimal('commission_rate', 5, 4); // Taux de commission (ex: 0.0500 = 5%)
            $table->decimal('commission_amount', 10, 2); // Montant calculé
            $table->string('currency', 3)->default('EUR');
            
            // Type et contexte
            $table->enum('type', ['maintenance', 'control_technique', 'insurance', 'rental', 'sale'])->default('maintenance');
            $table->enum('status', ['pending', 'approved', 'paid', 'cancelled', 'disputed'])->default('pending');
            
            // Période et facturation
            $table->date('commission_date'); // Date de génération de la commission
            $table->date('service_date')->nullable(); // Date du service rendu
            $table->integer('period_month'); // Mois de facturation
            $table->integer('period_year'); // Année de facturation
            
            // Paiement
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            
            // Validation
            $table->foreignId('approved_by')->nullable()->constrained('internal_employees')->onDelete('set null');
            $table->foreignId('processed_by')->nullable()->constrained('internal_employees')->onDelete('set null');
            
            // Informations complémentaires
            $table->text('description')->nullable();
            $table->json('service_details')->nullable(); // Détails du service
            $table->json('calculation_details')->nullable(); // Détails du calcul
            
            // Réconciliation
            $table->string('invoice_reference')->nullable();
            $table->boolean('is_reconciled')->default(false);
            $table->timestamp('reconciled_at')->nullable();
            
            // Métadonnées
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            // Index pour performances et reporting
            $table->index(['partner_id', 'status', 'commission_date']);
            $table->index(['tenant_id', 'period_year', 'period_month']);
            $table->index(['status', 'commission_date']);
            $table->index(['type', 'status']);
            $table->index(['period_year', 'period_month', 'status']);
            $table->index(['approved_by', 'approved_at']);
            $table->index(['is_reconciled', 'reconciled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_records');
    }
};