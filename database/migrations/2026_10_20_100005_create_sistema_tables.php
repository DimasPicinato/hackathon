<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Notificações in-app (RF23/RF24) — sino/toast; e-mail segue em paralelo
        Schema::create('notificacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('titulo', 150);
            $table->text('corpo')->nullable();
            $table->string('tipo', 40)->nullable();  // ex.: aula_iniciada, justificativa_alterada
            $table->string('link', 255)->nullable();
            $table->dateTime('lida_em')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'lida_em']);
        });

        // Configurações do sistema — painel do Admin (RF23/RF26: SMTP etc.)
        Schema::create('configuracoes', function (Blueprint $table) {
            $table->id();
            $table->string('chave', 80)->unique();   // ex.: smtp_host, smtp_porta
            $table->text('valor')->nullable();
            $table->foreignId('atualizado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Trilha de auditoria de eventos sensíveis (RNF01/RNF03)
        Schema::create('auditorias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // ator
            $table->string('evento', 60);            // ex.: presenca_homologada, justificativa_alterada
            $table->string('sujeito_type', 60)->nullable();
            $table->unsignedBigInteger('sujeito_id')->nullable();
            $table->jsonb('dados')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();

            $table->index(['evento', 'created_at']);
            $table->index(['sujeito_type', 'sujeito_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditorias');
        Schema::dropIfExists('configuracoes');
        Schema::dropIfExists('notificacoes');
    }
};
