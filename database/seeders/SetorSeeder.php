<?php

namespace Database\Seeders;

use App\Models\Setor;
use Illuminate\Database\Seeder;

class SetorSeeder extends Seeder
{
    public function run(): void
    {
        $setores = [
            ['nome' => 'Usinagem', 'sigla' => 'USI'],
            ['nome' => 'Manutenção', 'sigla' => 'MAN'],
            ['nome' => 'Qualidade', 'sigla' => 'QUI'],
            ['nome' => 'Logística', 'sigla' => 'LOG'],
            ['nome' => 'Administrativo', 'sigla' => 'ADM'],
        ];

        foreach ($setores as $setor) {
            Setor::create($setor);
        }
    }
}
