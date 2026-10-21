<?php

namespace Database\Seeders;

use App\Enums\Papel;
use App\Enums\Turno;
use App\Models\Biometria;
use App\Models\Setor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// SEED01/SEED02 — time completo com metadados biométricos para a demo
class UserSeeder extends Seeder
{
    private int $matriculaSeq = 0;

    public function run(): void
    {
        $senha = 'Password@123'; // padrão da demo (troca no 1º acesso em versões futuras)

        // Equipe central (SEED02)
        $this->criar('Fernanda Carvalho Duarte', null, Turno::Manha, [Papel::Admin], $senha);
        $this->criar('Isabela Cristina Fonseca', null, Turno::Manha, [Papel::Rh], $senha);
        $this->criar('Paulo Sérgio Miranda', null, Turno::Tarde, [Papel::Rh], $senha);
        $this->criar('Marco Antônio Ribeiro', 'Manutenção', Turno::Manha, [Papel::Instrutor], $senha);
        $this->criar('Klaus Werner Steiner', null, null, [Papel::Instrutor], $senha, 'EXT-9001'); // externo

        // Líderes — um por setor (permissões no escopo do próprio setor)
        $lideres = [
            'Usinagem' => 'Sérgio Ricardo Bandeira',
            'Manutenção' => 'Heloísa Fernandes Peixoto',
            'Qualidade' => 'Maurício Salles Grande',
            'Logística' => 'Débora Cristina Vaz',
            'Administrativo' => 'Rogério Sampaio Real',
        ];
        foreach ($lideres as $setor => $nome) {
            $this->criar($nome, $setor, Turno::Manha, [Papel::Lider, Papel::Colaborador], $senha);
        }

        // SEED01 — 34 colaboradores fictícios distribuídos por setor
        $colaboradores = [
            'Usinagem' => [
                'Carlos Eduardo Ramos', 'Fernando Alves Costa', 'Gustavo Henrique Souza',
                'Jonathan Pereira Lima', 'Marcelo Tavares Dias', 'Rafael Monteiro Castro',
                'Thiago Barros Mendes', 'Vinícius Cardoso Almeida', 'Wesley Duarte Nogueira',
            ],
            'Manutenção' => [
                'Alexandre Nunes Freitas', 'Bruno Siqueira Machado', 'Diego Fontes Ribeiro',
                'Edson Carvalho Prado', 'Fabrício Lopes Moura', 'Leandro Pires Rosa',
                'Otávio Martins Gomes', 'Rodrigo Antunes Silva',
            ],
            'Qualidade' => [
                'Amanda Ferreira Santos', 'Bianca Rocha Teixeira', 'Camila Duarte Freitas',
                'Elaine Cristina Campos', 'Letícia Moreira Braga', 'Natália Assunção Reis',
            ],
            'Logística' => [
                'Anderson Vieira Brito', 'Caio Oliveira Pinto', 'Éverton Silva Ramos',
                'Jéssica Nascimento Cruz', 'Marcos Antônio Correia', 'Patrícia Gomes Andrade',
            ],
            'Administrativo' => [
                'Beatriz Almeida Castro', 'Gabriel Tavares Moraes', 'Larissa Coelho Pimentel',
                'Renata Souza Azevedo', 'Vitor Hugo Ferreira Lopes',
            ],
        ];

        $contadorTurno = 0;
        foreach ($colaboradores as $setor => $nomes) {
            foreach ($nomes as $nome) {
                $turno = match ($contadorTurno++ % 3) {
                    0 => Turno::Manha,
                    1 => Turno::Tarde,
                    default => Turno::Noite,
                };
                $this->criar($nome, $setor, $turno, [Papel::Colaborador], $senha);
            }
        }

        // Metadados biométricos: embedding fake de 128 dimensões (fachada de demo — RNF06)
        User::query()->chunkById(50, function ($users): void {
            foreach ($users as $user) {
                DB::statement(
                    'INSERT INTO biometrias (user_id, embedding, modelo, ativo, capturado_em, created_at, updated_at)
                     VALUES (?, ?::vector, ?, true, NOW(), NOW(), NOW())',
                    [$user->id, $this->embedding(), 'mock-facenet-v1'],
                );
            }
        });
    }

    private function criar(
        string $nome,
        ?string $setor,
        ?Turno $turno,
        array $papeis,
        string $senha,
        ?string $matricula = null,
    ): User {
        return User::create([
            'name' => $nome,
            'email' => $this->email($nome),
            'password' => $senha,
            'matricula' => $matricula ?? $this->proximaMatricula(),
            'setor_id' => $setor !== null ? Setor::where('nome', $setor)->value('id') : null,
            'turno' => $turno?->value,
            'ativo' => true,
            'papeis' => array_map(fn (Papel $p) => $p->value, $papeis),
        ]);
    }

    private function proximaMatricula(): string
    {
        return 'USI-2026-' . str_pad((string) ++$this->matriculaSeq, 4, '0', STR_PAD_LEFT);
    }

    private function email(string $nome): string
    {
        return str_replace(' ', '.', strtolower(Str::ascii($nome))) . '@exemplo.com';
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
