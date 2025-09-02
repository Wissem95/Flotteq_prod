<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            
            // Configuration générale
            $table->string('name'); // Ex: Stripe, PayPal, Virement bancaire
            $table->string('slug')->unique(); // Ex: stripe, paypal, bank_transfer
            $table->enum('type', ['card', 'bank_transfer', 'digital_wallet', 'cryptocurrency', 'cash'])->default('card');
            $table->text('description')->nullable();
            
            // Statut et disponibilité
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->json('supported_currencies')->nullable(); // ['EUR', 'USD', 'GBP']
            $table->json('supported_countries')->nullable(); // Pays supportés
            
            // Configuration technique
            $table->json('configuration')->nullable(); // Clés API, webhooks, etc. (crypté)
            $table->string('provider'); // stripe, paypal, mollie, etc.
            $table->string('provider_version')->nullable();
            $table->boolean('sandbox_mode')->default(false);
            
            // Frais et limites
            $table->decimal('fixed_fee', 8, 4)->default(0); // Frais fixes
            $table->decimal('percentage_fee', 5, 4)->default(0); // Frais en pourcentage
            $table->decimal('minimum_amount', 8, 2)->nullable(); // Montant minimum
            $table->decimal('maximum_amount', 10, 2)->nullable(); // Montant maximum
            
            // Délais et fonctionnalités
            $table->integer('processing_time_hours')->default(0); // Temps de traitement
            $table->boolean('supports_recurring')->default(false); // Paiements récurrents
            $table->boolean('supports_refunds')->default(false); // Remboursements
            $table->boolean('supports_partial_refunds')->default(false); // Remboursements partiels
            $table->boolean('supports_webhooks')->default(false); // Notifications automatiques
            
            // Interface utilisateur
            $table->string('logo_url')->nullable();
            $table->string('icon_class')->nullable(); // Classe CSS pour icône
            $table->json('display_settings')->nullable(); // Paramètres d'affichage
            $table->integer('sort_order')->default(0); // Ordre d'affichage
            
            // Sécurité
            $table->boolean('requires_3ds')->default(false); // 3D Secure requis
            $table->json('security_features')->nullable(); // Fonctionnalités de sécurité
            $table->string('certification_level')->nullable(); // PCI DSS, etc.
            
            // Statistiques
            $table->integer('total_transactions')->default(0);
            $table->decimal('total_volume', 12, 2)->default(0);
            $table->decimal('success_rate', 5, 2)->default(100); // Pourcentage de succès
            $table->timestamp('last_transaction_at')->nullable();
            
            // Monitoring
            $table->enum('health_status', ['healthy', 'warning', 'critical', 'maintenance'])->default('healthy');
            $table->timestamp('last_health_check_at')->nullable();
            $table->json('error_logs')->nullable(); // Dernières erreurs
            
            // Métadonnées
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            // Index
            $table->index(['is_active', 'sort_order']);
            $table->index(['type', 'is_active']);
            $table->index(['provider', 'is_active']);
            $table->index(['slug', 'is_active']);
            $table->index(['health_status', 'last_health_check_at']);
            $table->index(['supports_recurring']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};