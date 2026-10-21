<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Catálogo de tipos de ticket mantido pelo Admin (RF11/RF26)
        Schema::create('tipos_ticket', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 80)->unique();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        // Cancelamento/ausência NUNCA é ação direta — passa por ticket (RF10)
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // aberto por
            $table->foreignId('treinamento_id')->nullable()
                ->constrained('treinamentos')->nullOnDelete();
            $table->foreignId('tipo_ticket_id')->constrained('tipos_ticket')->restrictOnDelete();
            $table->text('texto')->nullable();                  // texto livre (RF11)
            $table->string('status', 12)->default('aberto');    // aberto|resolvido
            $table->foreignId('resolvido_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('resolvido_em')->nullable();
            $table->text('resultado')->nullable();              // resumo da resolução (RF12)
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        // Justificativa de falta — status alterável a qualquer momento por RH/Líder (RF12/RF13)
        Schema::create('justificativas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // o faltoso
            $table->foreignId('aula_id')->constrained('aulas')->cascadeOnDelete();
            $table->foreignId('ticket_id')->nullable()
                ->constrained('tickets')->nullOnDelete();       // origem: ticket ou líder direto (RF13)
            $table->string('status', 10)->default('pendente');  // pendente|aprovado|recusado
            $table->text('texto')->nullable();
            $table->foreignId('alterado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('alterado_em')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'aula_id']);             // 1 justificativa por falta
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('justificativas');
        Schema::dropIfExists('tickets');
        Schema::dropIfExists('tipos_ticket');
    }
};
