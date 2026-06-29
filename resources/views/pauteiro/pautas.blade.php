@extends('layouts.app', ['title' => 'Minhas Pautas'])
@section('content')
<section class="section">
    <h1>Minhas pautas</h1>
    <div class="actions"><a class="btn primary" href="{{ route('pauteiro.pautas.criar') }}">Nova pauta</a></div>
    
    <h2>Pautas publicadas</h2>
    @if(count($publicadas) > 0)
    <div class="list">
        @foreach($publicadas as $pauta)
            <div class="card">
                <span class="pill">{{ $pauta->nicho }}</span>
                <h3>{{ $pauta->titulo }}</h3>
                <p class="muted">{{ $pauta->cidade }}/{{ $pauta->estado }} · Disponível</p>
                <p>{{ $pauta->descricao }}</p>
                <strong>R$ {{ number_format($pauta->valor, 2, ',', '.') }}</strong>
                @if($pauta->negociavel)<span class="pill" style="margin-left:8px">Negociável</span>@endif
                <div class="actions">
                    <a class="btn" href="{{ route('pauteiro.pautas.editar', $pauta->id) }}">Editar</a>
                    <form method="post" action="{{ route('pauteiro.pautas.excluir', $pauta->id) }}" style="display:inline">
                        @csrf
                        <button type="submit" class="btn" style="color:var(--red);border-color:var(--red)" onclick="return confirm('Tem certeza que deseja excluir esta pauta?')">Excluir</button>
                    </form>
                    <form method="post" action="{{ route('pauteiro.pautas.vender', $pauta->id) }}" style="display:inline">
                        @csrf
                        <button type="submit" class="btn primary" onclick="return confirm('Tem certeza que deseja marcar esta pauta como vendida?')">Marcar como vendida</button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
    @else
    <div class="card">
        <p class="muted">Nenhuma pauta publicada ainda.</p>
    </div>
    @endif

    <h2>Pautas vendidas</h2>
    @if(count($vendidas) > 0)
    <div class="list">
        @foreach($vendidas as $pauta)
            <div class="card">
                <span class="pill" style="background:#059669;color:#fff">{{ $pauta->nicho }}</span>
                <h3>{{ $pauta->titulo }}</h3>
                <p class="muted">{{ $pauta->cidade }}/{{ $pauta->estado }} · Vendida</p>
                <p>{{ $pauta->descricao }}</p>
                <strong style="color:#059669">R$ {{ number_format($pauta->valor, 2, ',', '.') }}</strong>
            </div>
        @endforeach
    </div>
    @else
    <div class="card">
        <p class="muted">Nenhuma pauta vendida ainda.</p>
    </div>
    @endif
</section>
@endsection
