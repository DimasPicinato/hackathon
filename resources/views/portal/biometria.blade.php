@extends('layouts.portal')

@section('titulo', 'Cadastro facial — Presenza')

@section('conteudo')
<a class="btn-voltar" href="{{ route('portal') }}"><x-icon nome="seta-esquerda"/> Voltar</a>
<h1>Cadastro facial</h1>
<p class="muted">RF03/RNF01 — autocadastro do próprio rosto: a imagem é lida somente no seu
    navegador (nada é enviado além do vetor de 128 números); o servidor guarda apenas o
    embedding, usado exclusivamente para validar presença em aula.</p>

<div class="card">
    <h2>Status do cadastro</h2>
    @if ($comCadastroReal)
        <p>
            <span class="badge badge-manual">rosto cadastrado</span>
            <span class="muted">
                capturado em {{ $atual->capturado_em?->format('d/m/Y H:i') }} · modelo {{ $atual->modelo }} (vetor de 128 dimensões)
            </span>
        </p>
        <p class="mini muted">Você pode renovar a captura a qualquer momento — o novo vetor substitui o anterior.</p>
    @elseif ($atual)
        <p>
            <span class="badge badge-fila">apenas cadastro de demonstração</span>
            <span class="muted">modelo {{ $atual->modelo }} — vetor fictício de seed, não reconhece rostos reais.</span>
        </p>
        <p class="mini muted">Faça o cadastro abaixo para habilitar a presença por reconhecimento facial.</p>
    @else
        <p class="muted">Nenhum rosto cadastrado ainda — faça o cadastro abaixo para habilitar a presença facial.</p>
    @endif
</div>

<div class="card">
    <h2>Capturar meu rosto</h2>
    @if ($comCadastroReal)
        <p class="mini muted">Renovação: o vetor atual será substituído pela nova captura (auditoria `biometria_cadastro`).</p>
    @endif
    {{-- RF04 — termo LGPD vigente é exigido pelo middleware `termo` em todo o portal --}}
    <form method="POST" action="{{ route('portal.biometria.cadastrar') }}" data-facial data-modo="cadastro" data-nome="{{ auth()->user()->name }}">@csrf
        <button class="btn btn-accent">📷 Capturar meu rosto agora</button>
    </form>
    <p class="mini muted">Requisitos da captura: ambiente bem iluminado, rosto centralizado na câmera,
        óculos escuros/boné fora. A imagem não sai do seu dispositivo.</p>
</div>
@endsection
