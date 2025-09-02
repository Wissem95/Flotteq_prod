<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('revenue_records', function (Blueprint $table) {
            $table->id();
            
            // Relations
            $table->foreignId('tenant_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('subscription_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_subscription_id')->nullable()->constrained()->onDelete('cascade');
            
            // Informations financières
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('EUR');
            $table->enum('type', ['subscription', 'commission', 'penalty', 'refund', 'bonus'])->default('subscription');
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'refunded'])->default('confirmed');
            
            // Période comptable
            $table->date('revenue_date'); // Date de comptabilisation
            $table->date('period_start')->nullable(); // Début de la période facturée
            $table->date('period_end')->nullable(); // Fin de la période facturée
            $table->integer('period_month'); // Mois de la période (1-12)
            $table->integer('period_year'); // Année de la période
            
            // Détails de facturation
            $table->string('invoice_number')->nullable();
            $table->timestamp('invoiced_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            
            // Taxes et frais
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('tax_rate', 5, 4)->default(0.2000); // 20% TVA par défaut
            $table->decimal('commission_amount', 10, 2)->default(0);
            $table->decimal('net_amount', 10, 2); // amount - tax - commission
            
            // Récurrence
            $table->boolean('is_recurring')->default(true);
            $table->string('billing_cycle')->nullable(); // monthly, yearly
            $table->date('next_billing_date')->nullable();
            
            // Métadonnées
            $table->text('description')->nullable();
            $table->json('metadata')->nullable(); // Infos supplémentaires
            $table->json('reconciliation_data')->nullable(); // Données de rapprochement comptable
            
            $table->timestamps();
            
            // Index pour performances et reporting
            $table->index(['tenant_id', 'period_year', 'period_month']);
            $table->index(['revenue_date', 'status']);
            $table->index(['period_year', 'period_month', 'status']);
            $table->index(['type', 'status', 'revenue_date']);
            $table->index(['subscription_id', 'status']);
            $table->index(['invoice_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revenue_records');
    }
};