@extends('layouts.app', ['title' => 'Home do Pauteiro'])
@section('content')
<section class="section">
    <h1>Home do Pauteiro</h1>
    <p class="muted">Bem-vindo, {{ $user->nome }}! Painel inicial com ranking e atalhos.</p>
    <div class="actions"><a class="btn primary" href="{{ route('pauteiro.pautas.criar') }}">Cadastrar pauta</a><a class="btn" href="{{ route('pauteiro.pautas') }}">Minhas pautas</a><a class="btn" href="{{ route('conta') }}">Minha conta</a></div>
    <div class="grid">
        <div class="card"><h3>Mais visualizadas</h3><p>1. Pauta sobre política regional</p><p>2. Evento cultural em Palmas</p><p>3. Destaques do agronegócio</p></div>
        <div class="card"><h3>Mais vendidas</h3><p>1. Especial de saúde pública</p><p>2. Educação no interior</p><p>3. Segurança e cidades</p></div>
    </div>
    @if(count($minhasPautas) > 0)
    <h2>Minhas pautas recentes</h2>
    <div class="list">
        @foreach($minhasPautas as $pauta)
            <div class="card">
                <span class="pill">{{ $pauta->nicho }}</span>
                <h3>{{ $pauta->titulo }}</h3>
                <p class="muted">{{ $pauta->cidade }}/{{ $pauta->estado }}</p>
                <p>{{ $pauta->descricao }}</p>
                <strong>R$ {{ number_format($pauta->valor, 2, ',', '.') }}</strong>
                <div class="actions"><a class="btn" href="{{ route('pauteiro.pautas.editar', $pauta->id) }}">Editar</a></div>
            </div>
        @endforeach
    </div>
    @endif
</section>
@endsection
