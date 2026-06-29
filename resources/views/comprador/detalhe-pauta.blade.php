@extends('layouts.app', ['title' => 'Detalhe da Pauta'])
@section('content')
<section class="section">
    <div class="card">
        <span class="pill">{{ $pauta->nicho }}</span>
        <h1>{{ $pauta->titulo }}</h1>
        <p class="muted">{{ $pauta->cidade }}/{{ $pauta->estado }}</p>
        <p>{{ $pauta->descricao }}</p>
        @if($pauta->arquivo)<p><strong>Arquivo:</strong> {{ $pauta->arquivo }}</p>@endif
        
        <div class="contato-mascarado">
            <p><strong>Contato mascarado:</strong></p>
            <p>{{ $mascararNome($pauta->nome) }} · {{ $mascararTelefone($pauta->telefone) }}</p>
            <p>{{ $mascararEmail($pauta->email) }}</p>
        </div>
        
        @php
            $taxa = $calcularTaxa($pauta->valor);
        @endphp
        <div class="valores-container">
            <p class="muted">Valor da pauta: R$ {{ number_format($pauta->valor, 2, ',', '.') }}</p>
            <p class="taxa">Taxa E-Pautas: {{ $formatarValor($taxa['valorTaxa']) }}</p>
            <p class="total">Total: {{ $formatarValor($taxa['valorFinal']) }}</p>
        </div>
        
        <p>{{ $pauta->negociavel ? 'Valor negociável' : 'Valor fixo' }}</p>
        
        <div class="actions">
            <form method="post" action="{{ route('carrinho.adicionar', $pauta->id) }}">
                @csrf
                <button class="btn primary" type="submit">Adicionar ao carrinho</button>
            </form>
            <a class="btn" href="{{ route('loja') }}">Voltar para loja</a>
        </div>
    </div>
</section>

<style>
.contato-mascarado {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 12px;
    margin: 12px 0;
}

.valores-container {
    background: #fef3c7;
    border: 1px solid #fca5a5;
    border-radius: 10px;
    padding: 12px;
    margin: 12px 0;
}

.taxa {
    color: #92400e;
    font-weight: 600;
    margin: 4px 0;
}

.total {
    color: #b91c1c;
    font-weight: 700;
    font-size: 18px;
    margin: 4px 0;
}
</style>
@endsection
