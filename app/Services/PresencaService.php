<?php

namespace App\Services;

use App\Enums\MetodoPresenca;
use App\Models\Auditoria;
use App\Models\Aula;
use App\Models\MotivoHomologacao;
use App\Models\Notificacao;
use App\Models\Presenca;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

// RF03, RF14–RF17 — regras centralizadas da chamada (facial + homologação manual)
class PresencaService
{
    public function __construct(private BiometriaService $biometria)
    {
    }

    /**
     * RF14 — presença facial REAL: o embedding capturado no navegador precisa
     * reconhecer o colaborador (pgvector, cosseno >= limiar). A identidade vem do
     * rosto, não do parâmetro; divergência é recusada e auditada (RF16/RNF01).
     */
    public function registrarFacial(Aula $aula, User $colaborador, ?int $atorId = null, ?string $ip = null, ?array $embedding = null): Presenca
    {
        $this->garantirJanelaAtiva($aula);           // RF03
        $this->garantirConsentimento($colaborador);  // RF04
        $this->garantirAusenciaDuplicidade($aula, $colaborador, $atorId); // RF16

        $match = $embedding ? $this->biometria->identificar($embedding) : null;

        if ($match === null) {
            Auditoria::registrar('presenca_facial_rejeitada', $aula, [
                'colaborador' => $colaborador->matricula,
                'motivo' => $embedding ? 'nenhum cadastro acima do limiar' : 'captura sem embedding',
            ], $atorId ?? $colaborador->id, $ip);

            throw ValidationException::withMessages([
                'facial' => 'Rosto não reconhecido. Verifique se esta pessoa já registrou o rosto no cadastro facial ou registre a presença manualmente (com senha e motivo).',
            ]);
        }

        if ($match['user']->id !== $colaborador->id) {
            // Anti-fraude: o rosto reconhecido NÃO é o do colaborador reivindicado.
            Auditoria::registrar('presenca_facial_divergente', $aula, [
                'reivindicado' => $colaborador->matricula,
                'reconhecido' => $match['user']->matricula,
                'similaridade' => $match['similaridade'],
            ], $atorId ?? $colaborador->id, $ip);

            throw ValidationException::withMessages([
                'facial' => 'O rosto capturado não corresponde ao cadastro da pessoa indicada. A presença não foi registrada.',
            ]);
        }

        $presenca = Presenca::create([
            'aula_id' => $aula->id,
            'user_id' => $colaborador->id,
            'metodo' => MetodoPresenca::Facial,
            'registrado_por_id' => null, // facial autônoma; via instrutor fica na auditoria
        ]);

        Auditoria::registrar('presenca_facial', $presenca, [
            'aula' => $aula->titulo,
            'similaridade' => number_format($match['similaridade'] * 100, 1, ',', '').'% (cosseno/pgvector)',
            'modelo' => BiometriaService::MODELO,
        ], $atorId ?? $colaborador->id, $ip);

        return $presenca;
    }

    public function registrarManual(
        Aula $aula,
        User $colaborador,
        User $instrutor,
        ?int $motivoId,
        ?string $novoMotivo,
        ?string $senha,
        ?string $ip = null,
    ): Presenca {
        // Anti-fraude do RF15: senha do PRÓPRIO colaborador no ato
        if (! $senha || ! Hash::check($senha, $colaborador->password)) {
            Auditoria::registrar('homologacao_senha_incorreta', $aula,
                ['colaborador' => $colaborador->matricula], $instrutor->id, $ip);
            throw ValidationException::withMessages([
                'senha' => 'A senha informada não é a senha de acesso da própria pessoa.',
            ]);
        }

        $this->garantirAusenciaDuplicidade($aula, $colaborador, $instrutor->id); // RF16

        if ($novoMotivo) { // RF27 — motivo novo no ato alimenta o catálogo
            $motivo = MotivoHomologacao::create([
                'nome' => $novoMotivo,
                'ativo' => true,
                'criado_por_id' => $instrutor->id,
            ]);
        } else {
            $motivo = MotivoHomologacao::find($motivoId);
        }

        if (! $motivo) {
            throw ValidationException::withMessages(['motivo_id' => 'Informe o motivo do registro manual.']);
        }

        $presenca = Presenca::create([
            'aula_id' => $aula->id,
            'user_id' => $colaborador->id,
            'metodo' => MetodoPresenca::Manual,
            'motivo_id' => $motivo->id,
            'registrado_por_id' => $instrutor->id,
            'senha_colaborador_validada' => true,
        ]);

        Auditoria::registrar('presenca_homologada', $presenca, [
            'aula' => $aula->titulo,
            'motivo' => $motivo->nome,
        ], $instrutor->id, $ip);

        $this->notificarRh($aula, $colaborador, $motivo->nome, $instrutor); // RF24

        return $presenca;
    }

    private function garantirJanelaAtiva(Aula $aula): void
    {
        if (! $aula->estaAtiva()) {
            throw ValidationException::withMessages([
                'aula' => 'A presença facial só pode ser registrada com a aula em andamento — o instrutor precisa iniciá-la e ainda não ter encerrado.',
            ]);
        }
    }

    private function garantirConsentimento(User $colaborador): void
    {
        if (! $colaborador->aceitouTermoVigente()) {
            throw ValidationException::withMessages([
                'termo' => "{$colaborador->name} ainda não aceitou o termo de privacidade: a presença precisa ser registrada manualmente pelo instrutor.",
            ]);
        }
    }

    private function garantirAusenciaDuplicidade(
        Aula $aula,
        User $colaborador,
        ?int $atorId,
    ): void {
        if ($aula->presencas()->where('user_id', $colaborador->id)->exists()) {
            Auditoria::registrar('presenca_duplicada', $aula,
                ['colaborador' => $colaborador->matricula], $atorId);
            throw ValidationException::withMessages([
                'duplicidade' => 'Presença já registrada nesta aula para essa pessoa.',
            ]);
        }
    }

    private function notificarRh(Aula $aula, User $colaborador, string $motivo, User $instrutor): void
    {
        User::query()->whereJsonContains('papeis', 'rh')->get()->each(function (User $rh) use ($aula, $colaborador, $motivo, $instrutor): void {
            Notificacao::create([
                'user_id' => $rh->id,
                'titulo' => 'Presença homologada manualmente',
                'corpo' => "{$aula->treinamento->nome} — {$aula->titulo}: {$colaborador->name} homologado(a) "
                    . "por motivo '{$motivo}', pelo instrutor {$instrutor->name}.",
                'tipo' => 'presenca_homologada',
            ]);
        });
    }
}
