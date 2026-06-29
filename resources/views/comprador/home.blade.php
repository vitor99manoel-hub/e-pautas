@extends('layouts.app', ['title' => 'Home do Comprador'])
@section('content')
<section class="section">
    <div class="topo">
        <div>
            <h1>Pautas recentes</h1>
            <p class="muted">Selecione uma pauta para visualizar</p>
        </div>
        <div class="topo-direita">
            <a class="btn" href="{{ route('carrinho') }}">
                Carrinho ({{ count($carrinho) }})
            </a>
            <form method="post" action="{{ route('logout') }}" style="display:inline">
                @csrf
                <button class="btn" type="submit">Sair</button>
            </form>
        </div>
    </div>

    <div class="menu-nicho">
        <h3>Nichos</h3>
        <div class="chips">
            @foreach($nichos as $nicho)
                <a class="chip {{ request()->query('nicho') === $nicho ? 'active' : '' }}" href="{{ route('comprador.home', ['nicho' => $nicho]) }}">{{ $nicho }}</a>
            @endforeach
        </div>
    </div>

    @php
        $nichoSelecionado = request()->query('nicho', 'Todos');
        $pautasFiltradas = $nichoSelecionado === 'Todos' ? $pautas : collect($pautas)->where('nicho', $nichoSelecionado)->values()->all();
    @endphp

    @if(count($pautasFiltradas) > 0)
    <div class="list">
        @foreach($pautasFiltradas as $pauta)
            @php
                $taxa = $calcularTaxa($pauta->valor);
                $jaNoCarrinho = in_array($pauta->id, $carrinho);
            @endphp
            <div class="card pauta-card" onclick="togglePreview({{ $pauta->id }})">
                <span class="pill">{{ $pauta->nicho }}</span>
                <h3>{{ $pauta->titulo }}</h3>
                <p class="muted">Nicho: {{ $pauta->nicho }}</p>
                <p class="muted">{{ $pauta->descricao }}</p>
                <p class="muted">Pauteiro: {{ $mascararNome($pauta->nome) }}</p>
                <p class="price">Valor: {{ $formatarValor($taxa['valorFinal']) }}</p>
                <p class="muted">{{ $pauta->cidade }} - {{ $pauta->estado }}</p>

                <div id="preview-{{ $pauta->id }}" class="preview-container" style="display:none">
                    <div class="preview-header">
                        <h4>Preview da Pauta</h4>
                    </div>
                    <p>{{ $pauta->descricao }}</p>
                    <p class="muted">Local: {{ $pauta->cidade }} - {{ $pauta->estado }}</p>
                    <p class="muted">Contato: {{ $mascararTelefone($pauta->telefone) }}</p>
                    <p class="muted">Email: {{ $mascararEmail($pauta->email) }}</p>
                    
                    <div class="valores-container">
                        <p class="muted">Valor da pauta: R$ {{ number_format($pauta->valor, 2, ',', '.') }}</p>
                        <p class="taxa">Taxa E-Pautas: {{ $formatarValor($taxa['valorTaxa']) }}</p>
                        <p class="total">Total: {{ $formatarValor($taxa['valorFinal']) }}</p>
                    </div>
                    @if($pauta->negociavel)
                    <p class="negociavel">✓ Valor negociável</p>
                    @endif

                    <div class="preview-actions">
                        @if($jaNoCarrinho)
                            <form method="post" action="{{ route('carrinho.remover', $pauta->id) }}" style="display:inline">
                                @csrf
                                <button type="submit" class="btn remover">Remover do Carrinho</button>
                            </form>
                        @else
                            <form method="post" action="{{ route('carrinho.adicionar', $pauta->id) }}" style="display:inline">
                                @csrf
                                <button type="submit" class="btn primary">Adicionar ao Carrinho</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    @else
    <div class="card">
        <h3>Nenhuma pauta encontrada</h3>
        <p class="muted">Não há pautas neste nicho no momento.</p>
    </div>
    @endif
</section>

<script>
function togglePreview(id) {
    const preview = document.getElementById('preview-' + id);
    if (preview.style.display === 'none') {
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }
}
</script>

<style>
.topo {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 18px;
}

.topo-direita {
    display: flex;
    gap: 8px;
}

.menu-nicho {
    background: #fff;
    border: 1px solid #fecaca;
    border-radius: 14px;
    padding: 14px;
    margin-bottom: 14px;
}

.menu-nicho h3 {
    margin: 0 0 10px 0;
    font-weight: 700;
    color: #111827;
}

.chips {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.chip {
    padding: 8px 12px;
    border-radius: 20px;
    background: #fee2e2;
    color: #b91c1c;
    font-weight: 600;
    font-size: 12px;
    text-decoration: none;
}

.chip.active {
    background: #d32f2f;
    color: #fff;
}

.pauta-card {
    cursor: pointer;
}

.preview-container {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 12px;
    margin-top: 12px;
}

.preview-header {
    border-bottom: 1px solid #e5e7eb;
    padding-bottom: 8px;
    margin-bottom: 8px;
}

.preview-header h4 {
    margin: 0;
    font-size: 14px;
    font-weight: 700;
    color: #111827;
}

.valores-container {
    background: #fef3c7;
    border: 1px solid #fca5a5;
    border-radius: 8px;
    padding: 8px;
    margin: 8px 0;
}

.taxa {
    color: #92400e;
    font-weight: 600;
    font-size: 12px;
    margin: 4px 0;
}

.total {
    color: #b91c1c;
    font-weight: 700;
    font-size: 14px;
    margin: 4px 0;
}

.negociavel {
    color: #059669;
    font-weight: 600;
    font-size: 12px;
    margin-top: 4px;
}

.preview-actions {
    margin-top: 12px;
}

.remover {
    background: #dc2626;
    color: #fff;
    border-color: #b91c1c;
}
</style>
@endsection
