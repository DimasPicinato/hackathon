@extends('layouts.portal')

@section('titulo', 'Cadastro facial — Presenza')

@section('conteudo')
<a class="btn-voltar" href="{{ route('portal') }}"><x-icon nome="seta-esquerda"/> Voltar</a>
<h1>Cadastro facial</h1>
<p class="muted">Registre o seu rosto uma única vez: a imagem é lida somente no seu navegador —
    nada de imagem é armazenado ou enviado. O servidor guarda apenas um código numérico que
    representa o seu rosto, usado exclusivamente para validar a sua presença em aula.</p>

<div class="card">
    <h2>Status do cadastro</h2>
    @if ($comCadastroReal)
        <p>
            <span class="badge badge-manual">rosto cadastrado</span>
            <span class="muted">capturado em {{ $atual->capturado_em?->format('d/m/Y H:i') }} — presença por reconhecimento facial liberada.</span>
        </p>
        <p class="mini muted">Você pode renovar a captura a qualquer momento — a nova captura substitui a anterior.</p>
    @elseif ($atual)
        <p>
            <span class="badge badge-fila">cadastro de demonstração</span>
            <span class="muted">este cadastro de exemplo não reconhece rostos reais ainda.</span>
        </p>
        <p class="mini muted">Faça o cadastro abaixo para habilitar a presença por reconhecimento facial.</p>
    @else
        <p class="muted">Nenhum rosto cadastrado ainda — faça o cadastro abaixo para habilitar a presença facial.</p>
    @endif
</div>

<div class="card">
    <h2>Capturar meu rosto</h2>
    @if ($comCadastroReal)
        <p class="mini muted">Atenção: a sua captura atual será substituída pela nova.</p>
    @endif
    <form method="POST" action="{{ route('portal.biometria.cadastrar') }}" data-facial data-modo="cadastro" data-nome="{{ auth()->user()->name }}">@csrf
        <button class="btn btn-accent">📷 Capturar meu rosto agora</button>
    </form>
    <p class="mini muted">Na primeira vez, o navegador vai pedir permissão para usar a câmera — escolha <strong>Permitir</strong>.</p>
    <p class="mini muted">Para uma boa captura: ambiente bem iluminado, rosto centralizado na câmera,
        sem óculos escuros ou boné. A imagem não sai do seu dispositivo.</p>
</div>
@endsection
