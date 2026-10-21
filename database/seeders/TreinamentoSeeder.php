<?php

namespace Database\Seeders;

use App\Enums\MetodoPresenca;
use App\Enums\StatusAula;
use App\Enums\StatusInscricao;
use App\Enums\StatusJustificativa;
use App\Enums\StatusTicket;
use App\Models\Auditoria;
use App\Models\Aula;
use App\Models\Certificado;
use App\Models\Inscricao;
use App\Models\MotivoHomologacao;
use App\Models\Notificacao;
use App\Models\Presenca;
use App\Models\Ticket;
use App\Models\Treinamento;
use App\Models\TipoTicket;
use App\Models\User;
use App\Models\Verificacao;
use Illuminate\Database\Seeder;

// SEED03 + cenários da demo: NR10 (concluído), NR35 (aula ativa + fila), SegInfo (expirado)
class TreinamentoSeeder extends Seeder
{
    public function run(): void
    {
        $u = fn (string $nome): User => User::where('name', $nome)->firstOrFail();
        $motivo = fn (string $nome): MotivoHomologacao => MotivoHomologacao::where('nome', $nome)->firstOrFail();
        $tipo = fn (string $nome): TipoTicket => TipoTicket::where('nome', $nome)->firstOrFail();

        $marco = $u('Marco Antônio Ribeiro');
        $klaus = $u('Klaus Werner Steiner');
        $isabela = $u('Isabela Cristina Fonseca');
        $paulo = $u('Paulo Sérgio Miranda');
        $heloisa = $u('Heloísa Fernandes Peixoto');

        // ─── NR10 · concluído há 14 meses · certificados VÁLIDOS ────────────────────
        $nr10 = Treinamento::create([
            'nome' => 'NR10 — Segurança em Instalações e Serviços em Eletricidade',
            'descricao' => 'Curso obrigatório para trabalhos com eletricidade. 1 aula única.',
            'carga_horaria' => 8,
            'validade_meses' => 24,
        ]);

        $nr10Aula = $this->aula($nr10, 1, 'Aula única — fundamentos e práticas',
            now()->subMonths(14)->setTime(8, 0), now()->subMonths(14)->setTime(17, 0),
            'CT Usina', 12, $marco, StatusAula::Encerrada, true);

        $turmaNr10 = ['Carlos Eduardo Ramos', 'Fernando Alves Costa', 'Gustavo Henrique Souza',
            'Jonathan Pereira Lima', 'Marcelo Tavares Dias', 'Rafael Monteiro Castro',
            'Thiago Barros Mendes', 'Vinícius Cardoso Almeida', 'Wesley Duarte Nogueira',
            'Rodrigo Antunes Silva'];
        foreach ($turmaNr10 as $nome) {
            $this->inscrever($nr10Aula, $u($nome), $isabela);
        }
        foreach ($turmaNr10 as $i => $nome) {
            if ($nome === 'Wesley Duarte Nogueira') {
                continue; // falta → sem certificado (RF18)
            }
            $manual = $nome === 'Marcelo Tavares Dias';
            $presenca = Presenca::create([
                'aula_id' => $nr10Aula->id,
                'user_id' => $u($nome)->id,
                'metodo' => $manual ? MetodoPresenca::Manual : MetodoPresenca::Facial,
                'motivo_id' => $manual ? $motivo('Câmera desligada ou indisponível')->id : null,
                'registrado_por_id' => $manual ? $marco->id : null,
                'senha_colaborador_validada' => $manual,
            ]);
            if ($manual) {
                Auditoria::registrar('presenca_homologada', $presenca,
                    ['aula' => 'NR10', 'metodo' => 'manual'], $marco->id, '10.0.0.10');
            }
        }
        $concluidoNr10 = $nr10Aula->data_inicio->copy()->toDateString();
        foreach ([
            'Carlos Eduardo Ramos' => 'PZ-2025-000101',
            'Fernando Alves Costa' => 'PZ-2025-000102',
            'Thiago Barros Mendes' => 'PZ-2025-000103',
        ] as $nome => $codigo) {
            Certificado::create([
                'user_id' => $u($nome)->id,
                'treinamento_id' => $nr10->id,
                'codigo' => $codigo,
                'emitido_por_id' => $isabela->id,
                'carga_horaria' => 8,
                'concluido_em' => $concluidoNr10,
                'valido_ate' => $nr10Aula->data_inicio->copy()->addMonths(24),
            ]);
        }

        // ─── NR35 · 3 aulas · AULA 2 ATIVA AGORA (demo facial) + fila de espera ─────
        $nr35 = Treinamento::create([
            'nome' => 'NR35 — Trabalho em Altura',
            'descricao' => 'Curso multi-dia (Treinamento 1:N Aula). Certificado só com presença nas 3 aulas.',
            'carga_horaria' => 24,
            'validade_meses' => 24,
        ]);

        $nr35A1 = $this->aula($nr35, 1, 'Aula 1 — normas e ancoragem',
            now()->subDays(3)->setTime(8, 0), now()->subDays(3)->setTime(17, 0),
            'Galpão de Treinamentos', 6, $klaus, StatusAula::Encerrada, true);
        $nr35A2 = $this->aula($nr35, 2, 'Aula 2 — práticas em altura',
            now()->setTime(22, 0), now()->setTime(23, 50),
            'Galpão de Treinamentos', 6, $klaus, StatusAula::EmAndamento, false, now()->subMinutes(30));
        $nr35A3 = $this->aula($nr35, 3, 'Aula 3 — resgate e avaliação',
            now()->addDays(7)->setTime(8, 0), now()->addDays(7)->setTime(17, 0),
            'Galpão de Treinamentos', 6, $klaus, StatusAula::Agendada);

        $turmaNr35 = ['Alexandre Nunes Freitas', 'Bruno Siqueira Machado', 'Diego Fontes Ribeiro',
            'Edson Carvalho Prado', 'Fabrício Lopes Moura', 'Leandro Pires Rosa'];
        foreach ([$nr35A1, $nr35A2, $nr35A3] as $aula) {
            foreach ($turmaNr35 as $nome) {
                $this->inscrever($aula, $u($nome), $heloisa); // líder reservou para o time (RF13/RF08)
            }
            $this->inscrever($aula, $u('Otávio Martins Gomes'), $heloisa, StatusInscricao::ListaEspera, 1);
        }

        foreach (['Alexandre Nunes Freitas', 'Bruno Siqueira Machado', 'Diego Fontes Ribeiro', 'Fabrício Lopes Moura'] as $nome) {
            Presenca::create(['aula_id' => $nr35A1->id, 'user_id' => $u($nome)->id, 'metodo' => MetodoPresenca::Facial]);
        }
        $manual = Presenca::create([
            'aula_id' => $nr35A1->id, 'user_id' => $u('Leandro Pires Rosa')->id,
            'metodo' => MetodoPresenca::Manual, 'motivo_id' => $motivo('Falha no reconhecimento facial')->id,
            'registrado_por_id' => $klaus->id, 'senha_colaborador_validada' => true,
        ]);
        Auditoria::registrar('presenca_homologada', $manual,
            ['aula' => 'NR35 A1', 'metodo' => 'manual'], $klaus->id, '10.0.0.11');
        // Edson faltou → ticket + justificativa pendente (RF10/RF12)

        $ticketEdson = Ticket::create([
            'user_id' => $u('Edson Carvalho Prado')->id,
            'treinamento_id' => $nr35->id,
            'tipo_ticket_id' => $tipo('Justificar falta')->id,
            'texto' => 'Gripe — atestado médico entregue no RH.',
            'status' => StatusTicket::Aberto,
        ]);
        Auditoria::registrar('justificativa_criada', $ticketEdson,
            ['treinamento' => 'NR35', 'origem' => 'ticket'], $u('Edson Carvalho Prado')->id, '10.0.0.12');

        // ─── Segurança da Informação · concluído há 30 meses · certificado EXPIRADO ─
        $segInfo = Treinamento::create([
            'nome' => 'Segurança da Informação',
            'descricao' => 'Boas práticas, phishing e uso de e-mail corporativo.',
            'carga_horaria' => 4,
            'validade_meses' => 12,
            'setor_id' => null, // todos os setores (matriz de conformidade — RF28)
        ]);

        $segA1 = $this->aula($segInfo, 1, 'Aula 1 — ameaças e phishing',
            now()->subMonths(30)->setTime(14, 0), now()->subMonths(30)->setTime(18, 0),
            'Sala CetaCeo', 15, $marco, StatusAula::Encerrada, true);
        $segA2 = $this->aula($segInfo, 2, 'Aula 2 — prática segura',
            now()->subMonths(30)->addDays(7)->setTime(14, 0), now()->subMonths(30)->addDays(7)->setTime(18, 0),
            'Sala CetaCeo', 15, $marco, StatusAula::Encerrada, true);

        $turmaSeg = ['Beatriz Almeida Castro', 'Gabriel Tavares Moraes', 'Larissa Coelho Pimentel', 'Renata Souza Azevedo'];
        foreach ([$segA1, $segA2] as $aula) {
            foreach ($turmaSeg as $nome) {
                $this->inscrever($aula, $u($nome), $u('Rogério Sampaio Real'));
                Presenca::create(['aula_id' => $aula->id, 'user_id' => $u($nome)->id, 'metodo' => MetodoPresenca::Facial]);
            }
        }
        Certificado::create([
            'user_id' => $u('Beatriz Almeida Castro')->id,
            'treinamento_id' => $segInfo->id,
            'codigo' => 'PZ-2024-000087',
            'emitido_por_id' => $paulo->id,
            'carga_horaria' => 4,
            'concluido_em' => $segA2->data_inicio->toDateString(),
            'valido_ate' => $segA2->data_inicio->copy()->addMonths(12), // venceu → EXPIRADO (demo)
        ]);

        // ─── Log de verificações públicas (RF21) ────────────────────────────────────
        foreach ([
            ['PZ-2024-000087', 'expirado'],
            ['PZ-2025-000101', 'valido'],
            ['PZ-9999-999999', 'nao_encontrado'],
        ] as [$codigo, $resultado]) {
            Verificacao::create(['codigo' => $codigo, 'resultado' => $resultado, 'ip' => '127.0.0.1']);
        }

        // ─── Notificações in-app de exemplo (RF23/RF24) ─────────────────────────────
        Notificacao::create([
            'user_id' => $u('Otávio Martins Gomes')->id,
            'titulo' => 'Você está na fila de espera',
            'corpo' => 'NR35 — Aula 2: assim que uma vaga abrir, você é promovido automaticamente.',
            'tipo' => 'fila_espera',
        ]);
        Notificacao::create([
            'user_id' => $u('Alexandre Nunes Freitas')->id,
            'titulo' => 'Vaga reservada: NR35 — Aula 2',
            'corpo' => 'Reserva nominal feita pela líder Heloísa Fernandes Peixoto.',
            'tipo' => 'vaga_reservada',
        ]);
        Notificacao::create([
            'user_id' => $isabela->id,
            'titulo' => 'Presença homologada manualmente',
            'corpo' => 'NR35 — Aula 1: Leandro Pires Rosa homologado pelo instrutor Klaus Steiner.',
            'tipo' => 'presenca_homologada',
        ]);
        Notificacao::create([
            'user_id' => $u('Edson Carvalho Prado')->id,
            'titulo' => 'Justificativa recebida',
            'corpo' => 'Seu ticket de justificativa de falta (NR35 — Aula 1) aguarda análise do RH.',
            'tipo' => 'justificativa',
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
        StatusAula $status = StatusAula::Agendada,
        bool $encerrar = false,
        $iniciadaEm = null,
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
            'status' => $status->value,
            'iniciada_em' => $iniciadaEm ?? ($encerrar ? $inicio : null),
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
}
