<?php

namespace Tests\Concerns;

use App\Enums\StatusAula;
use App\Enums\Turno;
use App\Models\Aula;
use App\Models\Certificado;
use App\Models\Inscricao;
use App\Models\Termo;
use App\Models\TermoAceite;
use App\Models\Treinamento;
use App\Models\User;

// Marcos reutilizáveis da suíte — cenários dos RFs em poucas linhas por teste
trait CriaFixtures
{
    protected function criarColaborador(array $atributos = []): User
    {
        return User::factory()->comPapeis('colaborador')->create($atributos);
    }

    protected function criarInstrutor(array $atributos = []): User
    {
        return User::factory()->comPapeis('instrutor')->create($atributos);
    }

    protected function criarRh(array $atributos = []): User
    {
        return User::factory()->comPapeis('rh')->create($atributos);
    }

    protected function criarTermo(): Termo
    {
        return Termo::create([
            'versao' => 'v' . fake()->unique()->numerify('.#'),
            'titulo' => 'Termo LGPD',
            'conteudo' => 'Uso facial exclusivo para presença.',
            'ativo' => true,
        ]);
    }

    protected function aceitarTermo(User $user, ?Termo $termo = null): Termo
    {
        $termo = $termo
            ?? Termo::query()->where('ativo', true)->orderByDesc('id')->first()
            ?? $this->criarTermo();
        TermoAceite::create(['user_id' => $user->id, 'termo_id' => $termo->id]);

        return $termo;
    }

    protected function criarTreinamento(): \App\Models\Treinamento
    {
        return \App\Models\Treinamento::create([
            'nome' => fake()->unique()->company() . ' — treinamento',
            'validade_meses' => 24,
        ]);
    }

    protected function criarSetor(string $prefixo = 'Setor'): \App\Models\Setor
    {
        return \App\Models\Setor::create(['nome' => $prefixo . ' ' . fake()->unique()->numerify('###')]);
    }

    protected function criarAula(StatusAula $status = StatusAula::Agendada, ?User $instrutor = null, array $atributos = []): Aula
    {
        $treinamento = $this->criarTreinamento();

        return Aula::create(array_merge([
            'treinamento_id' => $treinamento->id,
            'instrutor_id' => $instrutor?->id,
            'ordem' => 1,
            'titulo' => 'Aula de teste',
            'data_inicio' => now()->addDays(3),
            'data_fim' => now()->addDays(3)->addHours(8),
            'vagas' => 10,
            'status' => $status->value,
            'iniciada_em' => $status === StatusAula::Agendada ? null : now()->subMinutes(30),
            'encerrada_em' => $status === StatusAula::Encerrada ? now()->subMinutes(5) : null,
        ], $atributos));
    }

    protected function inscrever(Aula $aula, User $user): Inscricao
    {
        return Inscricao::create([
            'aula_id' => $aula->id,
            'user_id' => $user->id,
            'status' => 'reservada',
        ]);
    }

    /** Inscrição direta: reservada (vaga nominal) ou lista_espera (com posição). */
    protected function inscreverComStatus(Aula $aula, User $user, string $status = 'reservada', ?int $posicao = null): Inscricao
    {
        return Inscricao::create([
            'aula_id' => $aula->id,
            'user_id' => $user->id,
            'status' => $status,
            'posicao_fila' => $posicao ?? ($status === 'lista_espera' ? 1 : null),
        ]);
    }

    protected function darPresenca(Aula $aula, User $user, string $metodo = 'facial'): \App\Models\Presenca
    {
        return \App\Models\Presenca::create([
            'aula_id' => $aula->id,
            'user_id' => $user->id,
            'metodo' => $metodo,
        ]);
    }

    protected function criarTicket(User $user, Treinamento $treinamento, string $tipoNome = 'Cancelar vaga', ?string $texto = null): \App\Models\Ticket
    {
        $tipo = \App\Models\TipoTicket::query()->where('nome', $tipoNome)->first()
            ?? \App\Models\TipoTicket::create(['nome' => $tipoNome, 'ativo' => true]);

        return \App\Models\Ticket::create([
            'user_id' => $user->id,
            'treinamento_id' => $treinamento->id,
            'tipo_ticket_id' => $tipo->id,
            'texto' => $texto ?? 'Ticket de teste',
            'status' => 'aberto',
        ]);
    }

    protected function criarJustificativa(Aula $aula, User $user, string $status = 'pendente'): \App\Models\Justificativa
    {
        return \App\Models\Justificativa::create([
            'user_id' => $user->id,
            'aula_id' => $aula->id,
            'status' => $status,
        ]);
    }

    protected function criarCertificado(
        User $user,
        Treinamento $treinamento,
        ?User $emitidoPor = null,
        ?\Carbon\Carbon $validoAte = null,
    ): Certificado {
        return Certificado::create([
            'user_id' => $user->id,
            'treinamento_id' => $treinamento->id,
            'codigo' => 'PZ-' . fake()->unique()->numerify('#########'),
            'emitido_por_id' => $emitidoPor?->id ?? $user->id,
            'emitido_em' => now(),
            'carga_horaria' => 8,
            'concluido_em' => now(),
            'valido_ate' => $validoAte ?? now()->addMonths(24),
        ]);
    }
}
