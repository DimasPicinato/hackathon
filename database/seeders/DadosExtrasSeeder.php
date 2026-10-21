<?php

namespace Database\Seeders;

use App\Enums\MetodoPresenca;
use App\Enums\Papel;
use App\Enums\StatusAula;
use App\Enums\StatusInscricao;
use App\Enums\StatusJustificativa;
use App\Enums\StatusTicket;
use App\Enums\Turno;
use App\Models\Auditoria;
use App\Models\Aula;
use App\Models\Certificado;
use App\Models\Inscricao;
use App\Models\Justificativa;
use App\Models\MotivoHomologacao;
use App\Models\Notificacao;
use App\Models\Presenca;
use App\Models\Setor;
use App\Models\Ticket;
use App\Models\Treinamento;
use App\Models\TipoTicket;
use App\Models\User;
use App\Models\Verificacao;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// SEED04 — volume de exemplo além dos cenários da demo ("pode encher"):
// setores/pessoas novas, motivos, 5 treinamentos, fila, tickets abertos/resolvidos,
// justificativas aprovadas/recusadas, certificados novos + reemissão (RF20).
class DadosExtrasSeeder extends Seeder
{
    private int $matriculaSeq = 7100;
    private int $codigoSeq = 101; // PZ-2026-000101, 000102...

    public function run(): void
    {
        $u = fn (string $nome): User => User::where('name', $nome)->firstOrFail();
        $tipo = fn (string $nome): TipoTicket => TipoTicket::where('nome', $nome)->firstOrFail();
        $motivo = fn (string $nome): MotivoHomologacao => MotivoHomologacao::where('nome', $nome)->firstOrFail();

        $isabela = $u('Isabela Cristina Fonseca');
        $paulo = $u('Paulo Sérgio Miranda');
        $marco = $u('Marco Antônio Ribeiro');
        $heloisa = $u('Heloísa Fernandes Peixoto');
        $debora = $u('Débora Cristina Vaz');
        $rogerio = $u('Rogério Sampaio Real');

        // ─── 1 · Motivos extras de homologação (RF26/RF27) ─────────────────────────
        foreach ([
            'Atestado médico no dia', 'Viagem a serviço', 'Troca de turno de última hora',
            'Acesso bloqueado ao local da aula', 'Queda de energia no local',
            'Transporte interno indisponível',
        ] as $nome) {
            MotivoHomologacao::create(['nome' => $nome, 'ativo' => true, 'criado_por_id' => $u('Fernanda Carvalho Duarte')->id]);
        }

        // ─── 2 · Setores e pessoas novas (RF05: contas só via RH/Líder) ────────────
        $expedicao = Setor::create(['nome' => 'Expedição', 'sigla' => 'EXP']);
        $laboratorio = Setor::create(['nome' => 'Laboratório', 'sigla' => 'LAB']);

        $this->pessoa('Wesley Cardoso Ferreira', $expedicao->id, Turno::Manha, [Papel::Lider, Papel::Colaborador]);
        $this->pessoa('Simone Andrade Bastos', $laboratorio->id, Turno::Manha, [Papel::Lider, Papel::Colaborador]);
        $renato = $this->pessoa('Renato Alencar Duarte', $marco->setor_id, Turno::Tarde, [Papel::Instrutor, Papel::Colaborador]);

        foreach ([
            'Jonas Freire Santana' => $expedicao, 'Lucas Matheus Pires' => $expedicao,
            'Isadora Melo Campos' => $expedicao, 'Danilo Rocha Vidal' => $expedicao,
            'Priscila Nunes Bastos' => $laboratorio, 'Henrique Lopes de Sá' => $laboratorio,
            'Giovana Ferreira Munhoz' => $laboratorio,
        ] as $nome => $setor) {
            $this->pessoa($nome, $setor->id, Turno::Tarde, [Papel::Colaborador]);
        }
        $this->pessoa('Talita Sanches Ferro', $u('Sérgio Ricardo Bandeira')->setor_id, Turno::Noite, [Papel::Colaborador]);
        $this->pessoa('Murilo Duarte Pereira', $u('Sérgio Ricardo Bandeira')->setor_id, Turno::Manha, [Papel::Colaborador]);
        $this->pessoa('Alan Cardoso Ribeiro', $debora->setor_id, Turno::Tarde, [Papel::Colaborador]);

        // biometria de fachada (128 dims) para quem ainda não tem (RNF06)
        User::query()->whereDoesntHave('biometria')->chunkById(50, function ($users): void {
            foreach ($users as $user) {
                DB::statement(
                    'INSERT INTO biometrias (user_id, embedding, modelo, ativo, capturado_em, created_at, updated_at)
                     VALUES (?, ?::vector, ?, true, NOW(), NOW(), NOW())',
                    [$user->id, $this->embedding(), 'mock-facenet-v1'],
                );
            }
        });

        // ─── 3 · NR-12 · 2 aulas encerradas · 4 certificados + 1 reemissão (RF20) ──
        $nr12 = Treinamento::create([
            'nome' => 'NR-12 — Segurança em Máquinas e Equipamentos',
            'descricao' => 'Proteções fixas, dispositivos de parada e bloqueio tag-out.',
            'carga_horaria' => 16,
            'validade_meses' => 24,
        ]);
        $nr12A1 = $this->aula($nr12, 1, 'Aula 1 — riscos mecânicos e proteções',
            now()->subMonths(2)->setTime(8, 0), now()->subMonths(2)->setTime(12, 0), 'CT Usina', 14, $marco, true);
        $nr12A2 = $this->aula($nr12, 2, 'Aula 2 — bloqueio tag-out e operação segura',
            now()->subMonths(2)->addDays(7)->setTime(8, 0), now()->subMonths(2)->addDays(7)->setTime(12, 0), 'CT Usina', 14, $marco, true);

        // Concluídos: presença nas 2 aulas → certificado; Fernando falta na A2
        // (justificativa APROVADA); Wesley não aparece (justificativa RECUSADA).
        $concluidosNr12 = ['Carlos Eduardo Ramos', 'Jonathan Pereira Lima', 'Talita Sanches Ferro', 'Murilo Duarte Pereira'];
        $fer = $u('Fernando Alves Costa');
        $wesleyU = $u('Wesley Duarte Nogueira');
        foreach ([$nr12A1, $nr12A2] as $aula) {
            foreach ($concluidosNr12 as $nome) {
                $this->inscrever($aula, $u($nome), $isabela);
            }
            foreach ([$fer, $wesleyU] as $falta) {
                $this->inscrever($aula, $falta, $isabela);
            }
        }
        foreach ($concluidosNr12 as $nome) {
            foreach ([$nr12A1, $nr12A2] as $aula) {
                $manual = $nome === 'Murilo Duarte Pereira' && $aula->ordem === 2;
                $presenca = Presenca::create([
                    'aula_id' => $aula->id, 'user_id' => $u($nome)->id,
                    'metodo' => $manual ? MetodoPresenca::Manual : MetodoPresenca::Facial,
                    'motivo_id' => $manual ? $motivo('Transporte interno indisponível')->id : null,
                    'registrado_por_id' => $manual ? $marco->id : null,
                    'senha_colaborador_validada' => $manual,
                ]);
                if ($manual) {
                    Auditoria::registrar('presenca_homologada', $presenca,
                        ['aula' => 'NR-12 A2', 'metodo' => 'manual'], $marco->id, '10.0.0.21');
                }
            }
            $this->certificado($u($nome), $nr12, $isabela, $nr12A2->data_inicio->copy());
        }
        Presenca::create(['aula_id' => $nr12A1->id, 'user_id' => $fer->id, 'metodo' => MetodoPresenca::Facial]);

        $ticketFernando = Ticket::create([
            'user_id' => $fer->id, 'treinamento_id' => $nr12->id,
            'tipo_ticket_id' => $tipo('Justificar falta')->id,
            'texto' => 'Faltei na Aula 2 porque o transporte interno não passou no portão.',
            'status' => StatusTicket::Resolvido->value,
            'resolvido_por_id' => $isabela->id, 'resolvido_em' => now()->subMonths(1),
            'resultado' => 'Justificativa APROVADA — refazer a Aula 2 na próxima turma (sem certificado até lá).',
        ]);
        $this->justificativa($fer, $nr12A2, $ticketFernando, StatusJustificativa::Aprovado,
            'Transporte interno indisponível no dia — confirmado pela portaria.', $isabela, now()->subMonths(1));

        $wesleyA1 = $this->justificativa($wesleyU, $nr12A1, null, StatusJustificativa::Recusado,
            'Sem apresentação de motivo ou atestado para nenhuma das duas aulas — flicando com o líder.', $paulo, now()->subDays(20));
        $ticketWesley = Ticket::create([
            'user_id' => $wesleyU->id, 'treinamento_id' => $nr12->id,
            'tipo_ticket_id' => $tipo('Justificar falta')->id,
            'texto' => 'Tive problemas em casa nas duas semanas, prometo compensar.',
            'status' => StatusTicket::Resolvido->value,
            'resolvido_por_id' => $paulo->id, 'resolvido_em' => now()->subDays(20),
            'resultado' => 'Justificativa RECUSADA — sem atestado ou comprovação.',
        ]);
        $wesleyA1->update(['ticket_id' => $ticketWesley->id]);

        // ─── 4 · NR-33 · Aula 1 encerrada (progresso 1/2) + Aula 2 agendada c/ fila ─
        $nr33 = Treinamento::create([
            'nome' => 'NR-33 — Espaço Confinado',
            'descricao' => 'Entrada segura em silos e caçambas com vigia e procedimento de resgate.',
            'carga_horaria' => 8,
            'validade_meses' => 12,
        ]);
        $nr33A1 = $this->aula($nr33, 1, 'Aula 1 — teoria e permissão de entrada',
            now()->subWeeks(2)->setTime(13, 0), now()->subWeeks(2)->setTime(17, 0), 'Silo Treinamento', 4, $renato, true);
        $nr33A2 = $this->aula($nr33, 2, 'Aula 2 — prática no silo com vigia',
            now()->addDays(9)->setTime(13, 0), now()->addDays(9)->setTime(17, 0), 'Silo Treinamento', 4, $renato);

        foreach (['Anderson Vieira Brito', 'Caio Oliveira Pinto', 'Jéssica Nascimento Cruz', 'Éverton Silva Ramos'] as $nome) {
            foreach ([$nr33A1, $nr33A2] as $aula) {
                $this->inscrever($aula, $u($nome), $debora);
            }
        }
        $this->inscrever($nr33A2, $u('Marcos Antônio Correia'), $debora, StatusInscricao::ListaEspera, 1);
        foreach (['Anderson Vieira Brito', 'Caio Oliveira Pinto'] as $nome) {
            Presenca::create(['aula_id' => $nr33A1->id, 'user_id' => $u($nome)->id, 'metodo' => MetodoPresenca::Facial]);
        }

        // Everton faltou na A1 → justificativa RECUSADA; Jéssica falta → ticket ABERTO (demo ao vivo)
        $ticketEverton = Ticket::create([
            'user_id' => $u('Éverton Silva Ramos')->id, 'treinamento_id' => $nr33->id,
            'tipo_ticket_id' => $tipo('Justificar falta')->id,
            'texto' => 'Atrapalhei-me com a janela de turnos e não consegui sair.',
            'status' => StatusTicket::Resolvido->value,
            'resolvido_por_id' => $isabela->id, 'resolvido_em' => now()->subWeek(),
            'resultado' => 'RECUSADO — sem comprovação; acompanhar a Aula 2 pela lista.',
        ]);
        $this->justificativa($u('Éverton Silva Ramos'), $nr33A1, $ticketEverton, StatusJustificativa::Recusado,
            'Sem comprovação fornecida.', $isabela, now()->subWeek());
        $ticketJessica = Ticket::create([
            'user_id' => $u('Jéssica Nascimento Cruz')->id, 'treinamento_id' => $nr33->id,
            'tipo_ticket_id' => $tipo('Justificar falta')->id,
            'texto' => 'Atestado odontológico — extração de emergência no dia da aula.',
            'status' => StatusTicket::Aberto,
        ]);

        // ─── 5 · Brigada de Emergência · agendada p/ +5 dias · fila pronta p/ promo ─
        $brigada = Treinamento::create([
            'nome' => 'Formação de Brigada de Emergência',
            'descricao' => 'Brigada interna: evacuação, combate a incêndio iniciador e primeiros socorros.',
            'carga_horaria' => 12,
            'validade_meses' => 36,
            'setor_id' => $heloisa->setor_id, // Manutenção (RF06/RF28)
        ]);
        $brigadaA1 = $this->aula($brigada, 1, 'Turno único — simulado completo',
            now()->addDays(5)->setTime(8, 0), now()->addDays(5)->setTime(17, 0), 'Parque da usina', 6, $marco);
        foreach (['Alexandre Nunes Freitas', 'Bruno Siqueira Machado', 'Diego Fontes Ribeiro',
            'Edson Carvalho Prado', 'Leandro Pires Rosa', 'Rafael Monteiro Castro'] as $nome) {
            $this->inscrever($brigadaA1, $u($nome), $heloisa);
        }
        $this->inscrever($brigadaA1, $u('Rodrigo Antunes Silva'), $heloisa, StatusInscricao::ListaEspera, 1);

        // Rafael pede para DEVOLVER a vaga → RH resolve ao vivo e promove a fila (RF08/RF09/RF11)
        $ticketRafael = Ticket::create([
            'user_id' => $u('Rafael Monteiro Castro')->id, 'treinamento_id' => $brigada->id,
            'tipo_ticket_id' => $tipo('Cancelar vaga')->id,
            'texto' => 'Cirurgia marcada — devolvo a vaga para o time de Manutenção.',
            'status' => StatusTicket::Aberto,
        ]);
        $ticketNatalia = Ticket::create([
            'user_id' => $u('Natália Assunção Reis')->id, 'treinamento_id' => $brigada->id,
            'tipo_ticket_id' => $tipo('Informação')->id,
            'texto' => 'Posso entrar na lista da Brigada mesmo sendo do setor Qualidade?',
            'status' => StatusTicket::Resolvido->value,
            'resolvido_por_id' => $isabela->id, 'resolvido_em' => now()->subDays(2),
            'resultado' => 'Sim — esta turma é do setor Manutenção; na próxima edição entramos com vaga para você.',
        ]);

        // ─── 6 · Saúde Mental (Administrativo) · concluído há 1 mês ────────────────
        $saudeMental = Treinamento::create([
            'nome' => 'Saúde Mental no Trabalho — Conversas que Protegem',
            'descricao' => 'Sinais de burnout, escuta ativa e canais de apoio.',
            'carga_horaria' => 4,
            'validade_meses' => 12,
            'setor_id' => $rogerio->setor_id,
        ]);
        $smA1 = $this->aula($saudeMental, 1, 'Aula única — oficinas práticas',
            now()->subMonth()->setTime(14, 0), now()->subMonth()->setTime(18, 0), 'Sala de Ideias', 12, $marco, true);
        foreach (['Beatriz Almeida Castro', 'Gabriel Tavares Moraes', 'Larissa Coelho Pimentel', 'Vitor Hugo Ferreira Lopes'] as $nome) {
            $this->inscrever($smA1, $u($nome), $rogerio);
            Presenca::create(['aula_id' => $smA1->id, 'user_id' => $u($nome)->id, 'metodo' => MetodoPresenca::Facial]);
        }
        $gabrielV1 = $this->certificado($u('Gabriel Tavares Moraes'), $saudeMental, $paulo, $smA1->data_inicio->copy());
        $this->certificado($u('Vitor Hugo Ferreira Lopes'), $saudeMental, $paulo, $smA1->data_inicio->copy());

        // ─── 7 · NR-11 · 3 aulas concluídas há 5 meses · 4 certificados ─────────────
        $nr11 = Treinamento::create([
            'nome' => 'NR-11 — Transporte e Movimentação de Materiais',
            'descricao' => 'Empilhadeiras, pontes rolantes e plano de armazenagem em rack.',
            'carga_horaria' => 8,
            'validade_meses' => 24,
            'setor_id' => $debora->setor_id, // Logística
        ]);
        $aulasNr11 = [];
        foreach ([1, 2, 3] as $ordem) {
            $aulasNr11[] = $this->aula($nr11, $ordem, "Aula {$ordem} — módulo prático",
                now()->subMonths(5)->addDays($ordem * 7)->setTime(7, 0),
                now()->subMonths(5)->addDays($ordem * 7)->setTime(15, 0), 'Galpão G', 10, $marco, true);
        }
        $turmaNr11 = ['Anderson Vieira Brito', 'Jéssica Nascimento Cruz', 'Marcos Antônio Correia', 'Patrícia Gomes Andrade'];
        foreach ($aulasNr11 as $aula) {
            foreach ($turmaNr11 as $nome) {
                $this->inscrever($aula, $u($nome), $debora);
            }
        }
        foreach ($turmaNr11 as $indice => $nome) {
            foreach ($aulasNr11 as $aula) {
                $manual = $indice % 3 === 2; // Marcos: homologação manual em todas as aulas (RF15)
                $presenca = Presenca::create([
                    'aula_id' => $aula->id, 'user_id' => $u($nome)->id,
                    'metodo' => $manual ? MetodoPresenca::Manual : MetodoPresenca::Facial,
                    'motivo_id' => $manual ? $motivo('Queda de energia no local')->id : null,
                    'registrado_por_id' => $manual ? $marco->id : null,
                    'senha_colaborador_validada' => $manual,
                ]);
                if ($manual) {
                    Auditoria::registrar('presenca_homologada', $presenca,
                        ['aula' => 'NR-11 A' . $aula->ordem, 'metodo' => 'manual'], $marco->id, '10.0.0.30');
                }
            }
            $this->certificado($u($nome), $nr11, $isabela, $aulasNr11[2]->data_inicio->copy());
        }

        // ─── 8 · Reemissão REAL (RF20): v2corrige carga; v1 NUNCA mais verifica ────
        $gabrielV2 = $this->certificado($u('Gabriel Tavares Moraes'), $saudeMental, $paulo, $gabrielV1->concluido_em->copy(), 2);
        $gabrielV1->update(['substituido_por_id' => $gabrielV2->id]);
        Auditoria::registrar('certificado_reemitido', $gabrielV2,
            ['original' => $gabrielV1->codigo, 'novo' => $gabrielV2->codigo], $paulo->id, '10.0.0.40');
        Notificacao::create([
            'user_id' => $u('Gabriel Tavares Moraes')->id,
            'titulo' => 'Certificado reemitido',
            'corpo' => "A versão {$gabrielV1->codigo} foi substituída — use {$gabrielV2->codigo}.",
            'tipo' => 'certificado_reemitido',
        ]);

        // ─── 9 · Log de verificações públicas (RF21) ───────────────────────────────
        foreach ([
            [$gabrielV1->codigo, 'nao_encontrado'],            // versão antiga: morreu
            [$gabrielV2->codigo, 'valido'],
            ['PZ-2025-000101', 'valido'],
            ['PZ-9999-000555', 'nao_encontrado'],
            ['PZ-2024-000087', 'expirado'],
        ] as [$codigo, $resultado]) {
            Verificacao::create([
                'codigo' => $codigo, 'resultado' => $resultado,
                'ip' => '201.34.9.80', 'user_agent' => 'Mozilla/5.0 (QR Reader Mobile)',
            ]);
        }

        // ─── 10 · Notificações in-app (RF23/RF24) ──────────────────────────────────
        foreach ([
            [$isabela->id, 'Novo ticket: Justificar falta', 'Jéssica Nascimento Cruz enviou atestado odontológico (NR-33).', 'ticket_novo'],
            [$isabela->id, 'Novo ticket: Cancelar vaga', 'Rafael Monteiro Castro quer devolver a vaga na Brigada de Emergência.', 'ticket_novo'],
            [$isabela->id, 'Novo ticket: Informação', 'Natália Assunção Reis perguntou sobre a lista da Brigada.', 'ticket_novo'],
            [$u('Rodrigo Antunes Silva')->id, 'Você está na fila de espera', 'Brigada de Emergência: posição 1 — avisaremos quando uma vaga abrir.', 'fila_espera'],
            [$u('Marcos Antônio Correia')->id, 'Você entrou na fila de espera', 'NR-33 — Aula 2: posição 1 — vamos avisar quando uma vaga abrir.', 'fila_espera'],
            [$u('Vitor Hugo Ferreira Lopes')->id, 'Certificado emitido', 'Saúde Mental no Trabalho — certificado pronto para verificar.', 'certificado_emitido'],
            [$fer->id, 'Justificativa aprovada', 'NR-12 — Aula 2: refazer na próxima turma para receber o certificado.', 'justificativa_alterada'],
        ] as [$user, $titulo, $corpo, $tipoNot]) {
            Notificacao::create(['user_id' => $user, 'titulo' => $titulo, 'corpo' => $corpo, 'tipo' => $tipoNot]);
        }
    }

    // ── helpers ───────────────────────────────────────────────────────────────────

    private function pessoa(string $nome, int $setorId, Turno $turno, array $papeis): User
    {
        return User::create([
            'name' => $nome,
            'email' => str_replace(' ', '.', strtolower(Str::ascii($nome))) . '@exemplo.com',
            'password' => 'Password@123', // padrão da demo
            'matricula' => 'USI-2026-' . str_pad((string) $this->matriculaSeq++, 4, '0', STR_PAD_LEFT),
            'setor_id' => $setorId,
            'turno' => $turno->value,
            'ativo' => true,
            'papeis' => array_map(fn (Papel $p) => $p->value, $papeis),
        ]);
    }

    private function aula(
        Treinamento $treinamento,
        int $ordem,
        string $titulo,
        $inicio,
        $fim,
        string $local,
        int $vagas,
        User $instrutor,
        bool $encerrar = false,
    ): Aula {
        return Aula::create([
            'treinamento_id' => $treinamento->id,
            'instrutor_id' => $instrutor->id,
            'ordem' => $ordem,
            'titulo' => $titulo,
            'data_inicio' => $inicio,
            'data_fim' => $fim,
            'local' => $local,
            'vagas' => $vagas,
            'status' => $encerrar ? StatusAula::Encerrada->value : StatusAula::Agendada->value,
            'iniciada_em' => $encerrar ? $inicio : null,
            'encerrada_em' => $encerrar ? $fim : null,
        ]);
    }

    private function inscrever(
        Aula $aula,
        User $user,
        User $reservadoPor,
        StatusInscricao $status = StatusInscricao::Reservada,
        ?int $posicao = null,
    ): Inscricao {
        return Inscricao::create([
            'aula_id' => $aula->id,
            'user_id' => $user->id,
            'status' => $status->value,
            'posicao_fila' => $posicao,
            'reservado_por_id' => $reservadoPor->id,
        ]);
    }

    private function justificativa(
        User $user,
        Aula $aula,
        ?Ticket $ticket,
        StatusJustificativa $status,
        string $texto,
        User $alteradoPor,
        $alteradoEm,
    ): Justificativa {
        return Justificativa::create([
            'user_id' => $user->id,
            'aula_id' => $aula->id,
            'ticket_id' => $ticket?->id,
            'status' => $status->value,
            'texto' => $texto,
            'alterado_por_id' => $alteradoPor->id,
            'alterado_em' => $alteradoEm,
        ]);
    }

    private function certificado(User $user, Treinamento $treinamento, User $emitidoPor, $conclusao, int $versao = 1): Certificado
    {
        return Certificado::create([
            'user_id' => $user->id,
            'treinamento_id' => $treinamento->id,
            'codigo' => 'PZ-2026-' . str_pad((string) $this->codigoSeq++, 6, '0', STR_PAD_LEFT),
            'versao' => $versao,
            'emitido_por_id' => $emitidoPor->id,
            'emitido_em' => $conclusao->copy()->addDays(3),
            'carga_horaria' => $treinamento->carga_horaria,
            'concluido_em' => $conclusao->toDateString(),
            'valido_ate' => $conclusao->copy()->addMonths((int) $treinamento->validade_meses),
        ]);
    }

    private function embedding(int $dimensoes = 128): string
    {
        $valores = [];
        for ($i = 0; $i < $dimensoes; $i++) {
            $valores[] = round(mt_rand() / mt_getrandmax(), 4);
        }

        return '[' . implode(',', $valores) . ']';
    }
}
