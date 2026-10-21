<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Treinamento 1:N Aula (RF06)
        Schema::create('treinamentos', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 150)->unique();
            $table->text('descricao')->nullable();
            $table->unsignedSmallInteger('carga_horaria')->nullable(); // horas
            $table->unsignedSmallInteger('validade_meses')->nullable(); // RF22 (ex.: NR ~24)
            $table->foreignId('setor_id')->nullable()                  // null = todos os setores
                ->constrained('setores')->nullOnDelete();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        // Aula = sessão do treinamento com janela controlada pelo instrutor (RF06/RF14)
        Schema::create('aulas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('treinamento_id')->constrained('treinamentos')->cascadeOnDelete();
            $table->foreignId('instrutor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('ordem');            // 1ª, 2ª, 3ª aula...
            $table->string('titulo', 150)->nullable();
            $table->dateTime('data_inicio');                 // horário previsto
            $table->dateTime('data_fim')->nullable();
            $table->string('local', 120)->nullable();
            $table->unsignedSmallInteger('vagas')->default(20); // capacidade nominal (RF08)
            $table->string('status', 15)->default('agendada');  // agendada|em_andamento|encerrada
            $table->dateTime('iniciada_em')->nullable();        // janela facial aberta (RF03)
            $table->dateTime('encerrada_em')->nullable();
            $table->timestamps();

            $table->unique(['treinamento_id', 'ordem']);
            $table->index('status');
        });

        // Vagas sempre nominais + fila de espera automática (RF08/RF09)
        Schema::create('inscricoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aula_id')->constrained('aulas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 15)->default('reservada'); // reservada|lista_espera|cancelada
            $table->unsignedInteger('posicao_fila')->nullable();
            $table->foreignId('reservado_por_id')->nullable()   // líder/RH reservou para o time
                ->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['aula_id', 'user_id']); // re-reserva reativa o registro
        });

        // Catálogo de motivos de homologação manual (RF15/RF26/RF27)
        Schema::create('motivos_homologacao', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 120)->unique();
            $table->boolean('ativo')->default(true);
            $table->foreignId('criado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Presença: facial (autônoma/via instrutor) ou homologação manual (RF14–RF17)
        Schema::create('presencas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aula_id')->constrained('aulas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('metodo', 10);                       // facial|manual
            $table->foreignId('motivo_id')->nullable()          // obrigatório se manual (RF15)
                ->constrained('motivos_homologacao')->nullOnDelete();
            $table->foreignId('registrado_por_id')->nullable()  // null = facial autônoma
                ->constrained('users')->nullOnDelete();
            $table->boolean('senha_colaborador_validada')->default(false); // anti-fraude (RF15)
            $table->text('observacao')->nullable();
            $table->timestamps();

            $table->unique(['aula_id', 'user_id']); // RF16: 1 presença por pessoa por aula
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presencas');
        Schema::dropIfExists('motivos_homologacao');
        Schema::dropIfExists('inscricoes');
        Schema::dropIfExists('aulas');
        Schema::dropIfExists('treinamentos');
    }
};
