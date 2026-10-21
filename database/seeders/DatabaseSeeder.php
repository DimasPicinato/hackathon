<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed da aplicação — ordem respeita dependências de FK.
     */
    public function run(): void
    {
        $this->call([
            SetorSeeder::class,        // setores da usina
            UserSeeder::class,         // SEED01/SEED02 — time completo + biometria
            CatalogoSeeder::class,     // SEED02 — termo, motivos, tipos, configurações
            TreinamentoSeeder::class,  // SEED03 — treinamentos, aulas, presenças, certificados
            DadosExtrasSeeder::class,  // SEED04 — dados de exemplo adicionais (demo)
        ]);
    }
}
