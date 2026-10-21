<?php

namespace Database\Seeders;

use App\Models\Configuracao;
use App\Models\MotivoHomologacao;
use App\Models\Termo;
use App\Models\TipoTicket;
use App\Models\User;
use Illuminate\Database\Seeder;

// SEED02 — catálogos e configuração base (RF11/RF15/RF23/RF26)
class CatalogoSeeder extends Seeder
{
    public function run(): void
    {
        // Termo LGPD de biometria — versão vigente (RF04)
        Termo::create([
            'versao' => 'v1.0',
            'titulo' => 'Termo de Consentimento LGPD — Biometria Facial',
            'conteudo' => 'A Usina Lins utiliza reconhecimento facial EXCLUSIVAMENTE para validar a sua '
                . 'presença em treinamentos internos. Os dados biométricos (vetores de características) '
                . 'são armazenados de forma segregada, não identificam você fora dos portais Presenza, '
                . 'não são usados para controle de acesso, ponto ou monitoramento, e você pode solicitar '
                . 'a exclusão a qualquer momento ao RH. Base legal: consentimento (art. 7º, I, LGPD). '
                . 'Sem o aceite, a presença continua possível por homologação manual.',
            'ativo' => true,
        ]);

        // Motivos de homologação manual (RF15) — Admin/RH mantêm; instrutor pode add "no ato" (RF27)
        $admin = User::whereJsonContains('papeis', 'admin')->first();
        foreach (['Câmera desligada ou indisponível', 'Falha no reconhecimento facial', 'Iluminação inadequada no local'] as $motivo) {
            MotivoHomologacao::create(['nome' => $motivo, 'ativo' => true, 'criado_por_id' => $admin?->id]);
        }

        // Tipos de ticket (RF11) — seed mínimo exigido
        foreach (['Cancelar vaga', 'Justificar falta', 'Informação'] as $tipo) {
            TipoTicket::create(['nome' => $tipo, 'ativo' => true]);
        }

        // Configurações — painel do Admin (RF23/RF26); .env continua na frente (RNF04)
        foreach ([
            'smtp_host' => 'smtp.usinalins.local',
            'smtp_porta' => '587',
            'smtp_seguranca' => 'tls',
            'email_remetente' => 'presenza@usinalins.local',
            'nome_remetente' => 'Presenza — Usina Lins',
            'lembrete_antecedencia_horas' => '24',
        ] as $chave => $valor) {
            Configuracao::create(['chave' => $chave, 'valor' => $valor, 'atualizado_por_id' => $admin?->id]);
        }
    }
}
