@extends('layouts.app', ['title' => 'Loja de Pautas'])
@section('content')
<section class="section">
    <h1>Loja de pautas</h1>
    <div class="filters">
        @foreach($nichos as $nicho)
            <a class="btn {{ request()->query('nicho') === $nicho ? 'primary' : '' }}" href="{{ route('loja', ['nicho' => $nicho]) }}">{{ $nicho }}</a>
        @endforeach
    </div>
    @php
        $nichoSelecionado = request()->query('nicho', 'Todos');
        $pautasFiltradas = $nichoSelecionado === 'Todos' ? $pautas : collect($pautas)->where('nicho', $nichoSelecionado)->values()->all();
    @endphp
    <div class="grid">
        @forelse($pautasFiltradas as $pauta)
            @php
                $taxa = $calcularTaxa($pauta->valor);
            @endphp
            <div class="card">
                <span class="pill">{{ $pauta->nicho }}</span>
                <h3>{{ $pauta->titulo }}</h3>
                <p class="muted">{{ $pauta->cidade }}/{{ $pauta->estado }} · {{ $pauta->negociavel ? 'Negociável' : 'Valor fixo' }}</p>
                <p>{{ $pauta->descricao }}</p>
                <p class="price">R$ {{ number_format($pauta->valor, 2, ',', '.') }}</p>
                <p class="muted" style="font-size:12px">Valor final com taxa: R$ {{ number_format($taxa['valorFinal'], 2, ',', '.') }}</p>
                <div class="actions">
                    <a class="btn" href="{{ route('pauta.detalhe', $pauta->id) }}">Detalhes</a>
                    <form method="post" action="{{ route('carrinho.adicionar', $pauta->id) }}" style="display:inline">
                        @csrf
                        <button class="btn primary" type="submit">Adicionar</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="card">Nenhuma pauta encontrada para este nicho.</div>
        @endforelse
    </div>
</section>
@endsection
