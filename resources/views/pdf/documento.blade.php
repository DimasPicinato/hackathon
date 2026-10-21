<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9.5pt; line-height: 1.45; color: #1F2937; }
        h1 { font-size: 17pt; color: #15803D; border-bottom: 2px solid #EA580C; padding-bottom: 6px; }
        h2 { font-size: 13pt; color: #15803D; margin-top: 18px; page-break-after: avoid; }
        h3 { font-size: 11pt; color: #111827; page-break-after: avoid; }
        table { width: 100%; border-collapse: collapse; font-size: 8.5pt; margin: 8px 0; }
        th, td { border: 1px solid #D1D5DB; padding: 4px 6px; text-align: left; vertical-align: top; }
        th { background: #ECFDF5; }
        pre, code { font-family: 'DejaVu Sans Mono', monospace; font-size: 8pt; background: #F3F4F6; }
        pre { padding: 8px; white-space: pre-wrap; }
        blockquote { border-left: 3px solid #EA580C; margin: 8px 0; padding: 4px 10px; color: #374151; background: #F9FAFB; }
        .capa { text-align: center; margin-top: 210px; }
        .capa h1 { font-size: 26pt; border: none; }
        .capa .sub { font-size: 12pt; color: #6B7280; }
        .rodape { position: fixed; bottom: -40px; left: 0; right: 0; font-size: 7.5pt; color: #9CA3AF; text-align: center; }
        .secao-nova { page-break-before: always; }
    </style>
</head>
<body>
    <div class="capa">
        <h1>Presenza</h1>
        <p class="sub">Presenças reais, certificados à prova</p>
        <p class="sub">{{ $titulo }}</p>
        <p class="sub">Hackathon FATEC &amp; ETEC Usina Lins · Problema 1 · 21/10/2026</p>
        <p class="sub">Dimas Matheus Gomes Picinato · Sara Silva Marsola · João Marcel dos Santos Silva</p>
    </div>

    @foreach ($secoes as $secao)
        <div class="secao-nova">
            {!! $secao['html'] !!}
        </div>
    @endforeach

    <div class="rodape">
        Gerado por IA via comando `presenza:pdf-prompts` — evidência do processo (AT01/AT03), fonte em docs/*.md
    </div>
</body>
</html>
