<?php

namespace App\Console\Commands;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use League\CommonMark\CommonMarkConverter;

// Entregável do hackathon — compila os prompts/documentos do processo IA em um PDF
class GerarPdfPrompts extends Command
{
    protected $signature = 'presenza:pdf-prompts
        {--fonte=* : docs markdown (padrão: questionário + requisitos + stack + acordos + design)}
        {--saida=docs/prompts-ia.pdf}';

    protected $description = 'Gera o PDF com evidência dos prompts e decisões IA do projeto';

    public function handle(): int
    {
        $fontes = $this->option('fonte') ?: [
            'docs/questionario-requisitos-p1.md',
            'docs/requisitos-p1.md',
            'docs/stack.md',
            'docs/acordos-de-trabalho.md',
            'docs/design-p1.md',
        ];

        $secoes = [];
        foreach ($fontes as $fonte) {
            $caminho = base_path($fonte);

            if (! File::exists($caminho)) {
                $this->warn("Ignorado (não encontrado): {$fonte}");

                continue;
            }

            $secoes[] = [
                'titulo' => File::lines($caminho)->first(fn ($l) => str_starts_with(trim($l), '# ')) ?: $fonte,
                'html' => (new CommonMarkConverter(['html_input' => 'strip', 'allow_unsafe_links' => false]))
                    ->convert(File::get($caminho))->getContent(),
            ];
        }

        if ($secoes === []) {
            $this->error('Nenhuma seção encontrada.');

            return self::FAILURE;
        }

        $pdf = Pdf::loadView('pdf.documento', [
            'titulo' => 'Presenza — Prompts e decisões IA (Problema 1)',
            'secoes' => $secoes,
        ])->setPaper('a4');

        $destino = base_path($this->option('saida'));
        File::ensureDirectoryExists(dirname($destino));
        $pdf->save($destino);

        $this->info('PDF gerado: ' . $this->option('saida'));

        return self::SUCCESS;
    }
}
