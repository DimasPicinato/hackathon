<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Certificado imutável com código único (RF19/RF20); correção = nova versão
        Schema::create('certificados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('treinamento_id')->constrained('treinamentos')->cascadeOnDelete();
            $table->string('codigo', 30)->unique();             // chave da verificação pública (RF21)
            $table->unsignedInteger('versao')->default(1);
            $table->foreignId('substituido_por_id')->nullable() // aponta p/ versão vigente
                ->constrained('certificados')->nullOnDelete();
            $table->foreignId('emitido_por_id')->constrained('users')->restrictOnDelete();
            $table->dateTime('emitido_em')->nullable();
            $table->unsignedSmallInteger('carga_horaria')->nullable();
            $table->date('concluido_em')->nullable();
            $table->date('valido_ate')->nullable();             // RF22 — alimenta EXPIRADO
            $table->timestamps();

            $table->unique(['user_id', 'treinamento_id', 'versao']);
        });

        // Log da verificação pública (RF21/RNF01) — mínimo de dados, sem dados sensíveis
        Schema::create('verificacoes', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 30);
            $table->string('resultado', 20);                    // valido|expirado|nao_encontrado
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamps();

            $table->index('codigo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verificacoes');
        Schema::dropIfExists('certificados');
    }
};
