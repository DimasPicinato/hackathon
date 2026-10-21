<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Setores da usina — escopo de atuação do Líder (RF02/RF05)
        Schema::create('setores', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 80)->unique();
            $table->string('sigla', 15)->nullable();
            $table->timestamps();
        });

        // Extensão de users: identidade interna + papéis acumuláveis (RF02/RF05)
        Schema::table('users', function (Blueprint $table) {
            $table->string('matricula', 30)->unique()->after('id');
            $table->foreignId('setor_id')->nullable()->after('name')
                ->constrained('setores')->nullOnDelete();
            $table->string('turno', 10)->nullable()->after('setor_id'); // manha|tarde|noite
            $table->boolean('ativo')->default(true)->after('turno');
            $table->jsonb('papeis')->default('[]')->after('ativo');     // ex.: ["colaborador","lider"]
        });

        // Versões do termo LGPD de biometria (RF04)
        Schema::create('termos', function (Blueprint $table) {
            $table->id();
            $table->string('versao', 20)->unique();      // ex.: v1.0
            $table->string('titulo', 150);
            $table->text('conteudo');
            $table->boolean('ativo')->default(true);     // versão vigente
            $table->timestamps();
        });

        // Aceites versionados do termo (RNF01)
        Schema::create('termo_aceites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('termo_id')->constrained('termos')->cascadeOnDelete();
            $table->string('ip', 45)->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'termo_id']);
        });

        // Metadados biométricos — embeddings pgvector (RF03/SEED01, RNF01)
        Schema::create('biometrias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('modelo', 60)->default('mock-facenet-v1');
            $table->boolean('ativo')->default(true);
            $table->dateTime('capturado_em')->nullable();
            $table->timestamps();

            $table->unique('user_id'); // 1 rosto ativo por pessoa
        });

        DB::statement('CREATE EXTENSION IF NOT EXISTS vector');
        DB::statement('ALTER TABLE biometrias ADD COLUMN embedding vector(128) NOT NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('biometrias');
        Schema::dropIfExists('termo_aceites');
        Schema::dropIfExists('termos');
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('setor_id');
            $table->dropColumn(['matricula', 'turno', 'ativo', 'papeis']);
        });
        Schema::dropIfExists('setores');
    }
};
