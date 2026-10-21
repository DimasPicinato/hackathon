<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// RF33 — registro/auditoria das conversas do Assistente público (pergunta, resposta, IP, data)
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assistente_mensagens', function (Blueprint $table) {
            $table->id();
            $table->string('sessao', 64)->index();      // thread da aba (RF34: histórico só no navegador)
            $table->text('pergunta');
            $table->text('resposta')->nullable();
            $table->string('estado', 20)->default('ok'); // ok | fallback (RNF08)
            $table->string('ip', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assistente_mensagens');
    }
};
