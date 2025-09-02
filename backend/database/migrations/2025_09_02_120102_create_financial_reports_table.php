<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_reports', function (Blueprint $table) {
            $table->id();
            
            // Informations du rapport
            $table->string('name');
            $table->string('type'); // revenue_summary, commission_report, tenant_billing, etc.
            $table->enum('status', ['generating', 'completed', 'failed', 'archived'])->default('generating');
            
            // Période et scope
            $table->date('period_start');
            $table->date('period_end');
            $table->enum('period_type', ['daily', 'weekly', 'monthly', 'quarterly', 'yearly', 'custom'])->default('monthly');
            $table->json('scope')->nullable(); // Filtres appliqués (tenants, partners, etc.)
            
            // Génération
            $table->foreignId('generated_by')->constrained('internal_employees')->onDelete('cascade');
            $table->timestamp('generated_at')->nullable();
            $table->integer('generation_time_seconds')->nullable();
            
            // Données du rapport
            $table->json('summary')->nullable(); // Résumé exécutif
            $table->json('data')->nullable(); // Données détaillées
            $table->json('metrics')->nullable(); // KPIs et métriques
            $table->json('charts_data')->nullable(); // Données pour graphiques
            
            // Totaux financiers
            $table->decimal('total_revenue', 12, 2)->default(0);
            $table->decimal('total_commissions', 12, 2)->default(0);
            $table->decimal('total_expenses', 12, 2)->default(0);
            $table->decimal('net_profit', 12, 2)->default(0);
            $table->string('currency', 3)->default('EUR');
            
            // Comparaison
            $table->json('previous_period_data')->nullable(); // Données période précédente
            $table->json('growth_metrics')->nullable(); // Métriques de croissance
            
            // Fichiers générés
            $table->string('pdf_path')->nullable();
            $table->string('excel_path')->nullable();
            $table->string('csv_path')->nullable();
            $table->integer('file_size_bytes')->nullable();
            
            // Configuration
            $table->json('format_settings')->nullable(); // Paramètres de formatage
            $table->boolean('is_automated')->default(false); // Rapport automatisé
            $table->string('cron_schedule')->nullable(); // Planning pour rapports automatiques
            
            // Partage et distribution
            $table->json('recipients')->nullable(); // Destinataires du rapport
            $table->timestamp('last_sent_at')->nullable();
            $table->boolean('is_public')->default(false);
            $table->string('share_token')->nullable();
            $table->timestamp('expires_at')->nullable();
            
            // Métadonnées
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            // Index
            $table->index(['generated_by', 'status']);
            $table->index(['type', 'period_start', 'period_end']);
            $table->index(['status', 'generated_at']);
            $table->index(['period_type', 'period_start']);
            $table->index(['is_automated', 'status']);
            $table->index(['share_token', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_reports');
    }
};