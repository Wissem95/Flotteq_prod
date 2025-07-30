<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            // Plan details
            $table->string('name')->after('id');
            $table->text('description')->nullable()->after('name');
            $table->decimal('price', 10, 2)->after('description');
            $table->string('currency')->default('EUR')->after('price');
            $table->enum('billing_cycle', ['monthly', 'yearly'])->default('monthly')->after('currency');
            
            // Limitations et fonctionnalités
            $table->json('features')->nullable()->after('billing_cycle'); // Liste des features incluses
            $table->json('limits')->nullable()->after('features'); // Limites (véhicules, utilisateurs, etc.)
            
            // Configuration
            $table->boolean('is_active')->default(true)->after('limits');
            $table->boolean('is_popular')->default(false)->after('is_active');
            $table->integer('sort_order')->default(0)->after('is_popular');
            
            // Métadonnées
            $table->json('metadata')->nullable()->after('sort_order');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'name', 'description', 'price', 'currency', 'billing_cycle',
                'features', 'limits', 'is_active', 'is_popular', 'sort_order', 'metadata'
            ]);
        });
    }
};