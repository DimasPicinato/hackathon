<h1 style="font-size:26px;font-family:Lexend,DejaVu Sans,sans-serif;color:#15803D;margin:0;">
    Presenza · Certificado
</h1>
<div style="height:6px;background:#EA580C;width:160px;margin:6px 0 24px;"></div>

<p style="color:{{ $certificado->resultadoVerificacao()->value === 'valido' ? '#16A34A' : '#DC2626' }};
    font-weight:700;font-size:18px;letter-spacing:.08em;">
    {{ strtoupper($certificado->resultadoVerificacao()->value) }}
</p>

<div style="border:2px solid #15803D;border-radius:10px;padding:24px;font-size:14px;color:#1f2937;">
    <p style="margin:0 0 4px;"><b>Código de verificação:</b> {{ $certificado->codigo }} (v{{ $certificado->versao }})</p>
    <p style="margin:0 0 4px;"><b>Nome:</b> {{ $certificado->user->name }}</p>
    <p style="margin:0 0 4px;"><b>Treinamento:</b> {{ $certificado->treinamento->nome }}</p>
    <p style="margin:0 0 4px;"><b>Carga horária:</b> {{ $certificado->carga_horaria ?? $certificado->treinamento->carga_horaria }} horas</p>
    <p style="margin:0 0 4px;"><b>Conclusão:</b> {{ $certificado->concluido_em?->format('d/m/Y') }}</p>
    <p style="margin:0 0 4px;"><b>Válido até:</b> {{ $certificado->valido_ate?->format('d/m/Y') }}</p>
    <p style="margin:0 0 16px;"><b>Instrutor(es):</b> {{ $instrutores }}</p>

    <p style="margin:0;color:#475569;font-size:12px;">
        Emitido em {{ $certificado->emitido_em?->format('d/m/Y') ?? '—' }} pelo RH
        ({{ $certificado->emitidoPor->name }}) via Presenza — Usina Lins.
        Verifique autenticidade no sitePresenza com este código, e-mail ou link público.
    </p>
</div>
