<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Auteur du ticket
            $table->foreignId('agent_id')->nullable()->constrained('users')->onDelete('set null'); // Agent assigné
            
            // Informations du ticket
            $table->string('subject');
            $table->text('description');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['open', 'in_progress', 'waiting_user', 'resolved', 'closed'])->default('open');
            $table->enum('category', ['technical', 'billing', 'feature_request', 'bug_report', 'other'])->default('other');
            
            // Conversation
            $table->json('messages')->nullable(); // Historique des échanges
            
            // Métadonnées
            $table->json('metadata')->nullable(); // Infos contextuelles (page, erreur, etc.)
            $table->string('internal_notes')->nullable(); // Notes internes agents
            
            // Timestamps importants
            $table->timestamp('first_response_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            
            $table->timestamps();
            
            // Index pour performances
            $table->index(['tenant_id', 'status']);
            $table->index(['agent_id', 'status']);
            $table->index(['priority', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};