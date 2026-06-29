@extends('layouts.app', ['title' => 'Carrinho'])
@section('content')
<section class="section">
    <h1>Carrinho ({{ count($itens) }})</h1>
    
    @if(count($itens) === 0)
    <div class="card">
        <p class="muted">Seu carrinho está vazio.</p>
        <a class="btn primary" href="{{ route('loja') }}">Ver pautas</a>
    </div>
    @else
    <div class="list">
        @foreach($itens as $item)
            @php
                $taxa = $calcularTaxa($item->valor);
            @endphp
            <div class="card item-carrinho">
                <div class="item-info">
                    <h3>{{ $item->titulo }}</h3>
                    <p class="muted">{{ $item->nicho }}</p>
                    <p class="muted">Pauteiro: {{ $mascararNome($item->nome) }}</p>
                    <p class="muted">{{ $item->cidade }} - {{ $item->estado }}</p>
                    @if($item->negociavel)
                    <p class="negociavel">Valor negociável</p>
                    @endif
                </div>
                
                <div class="item-direita">
                    <div class="valores-item">
                        <p class="valor-base">R$ {{ number_format($item->valor, 2, ',', '.') }}</p>
                        <p class="valor-taxa">+{{ $formatarValor($taxa['valorTaxa']) }}</p>
                        <p class="valor-total">{{ $formatarValor($taxa['valorFinal']) }}</p>
                    </div>
                    <form method="post" action="{{ route('carrinho.remover', $item->id) }}" style="display:inline">
                        @csrf
                        <button class="btn remover" type="submit">✕</button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
    
    <div class="pagamento-container">
        <h3>Forma de Pagamento</h3>
        <div class="pagamento-opcoes">
            <label class="pagamento-opcao active">
                <input type="radio" name="pagamento" value="cartao" checked>
                <span>Cartão de Crédito/Débito</span>
            </label>
            <label class="pagamento-opcao">
                <input type="radio" name="pagamento" value="pix">
                <span>PIX</span>
            </label>
        </div>
    </div>
    
    <div class="resumo-container">
        @php
            $subtotal = collect($itens)->sum(function($item) {
                return (float) str_replace(',', '.', $item['valor']);
            });
            $totalTaxa = collect($itens)->sum(function($item) use ($calcularTaxa) {
                $taxa = $calcularTaxa($item['valor']);
                return $taxa['valorTaxa'];
            });
            $totalComTaxa = collect($itens)->sum(function($item) use ($calcularTaxa) {
                $taxa = $calcularTaxa($item['valor']);
                return $taxa['valorFinal'];
            });
        @endphp
        <div class="resumo-linha">
            <span>Subtotal (pautas)</span>
            <span>{{ $formatarValor($subtotal) }}</span>
        </div>
        <div class="resumo-linha">
            <span>Taxa E-Pautas</span>
            <span class="resumo-valor-taxa">{{ $formatarValor($totalTaxa) }}</span>
        </div>
        <div class="resumo-linha">
            <span class="resumo-texto-total">Total</span>
            <span class="resumo-valor-total">{{ $formatarValor($totalComTaxa) }}</span>
        </div>
    </div>
    
    <form method="post" action="{{ route('carrinho.finalizar') }}">
        @csrf
        <button class="btn primary" type="submit" onclick="return confirm('Deseja finalizar a compra no valor total de {{ $formatarValor($totalComTaxa) }}?')">Finalizar Compra</button>
    </form>
    @endif
</section>

<style>
.item-carrinho {
    display: flex;
    justify-content: space-between;
    margin-bottom: 12px;
}

.item-info {
    flex: 1;
}

.item-direita {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 8px;
}

.valores-item {
    text-align: right;
}

.valor-base {
    color: #6b7280;
    font-size: 12px;
    text-decoration: line-through;
    margin: 2px 0;
}

.valor-taxa {
    color: #059669;
    font-size: 11px;
    font-weight: 600;
    margin: 2px 0;
}

.valor-total {
    color: #b91c1c;
    font-size: 14px;
    font-weight: 700;
    margin: 2px 0;
}

.negociavel {
    color: #059669;
    font-weight: 600;
    font-size: 12px;
}

.remover {
    background: #ef4444;
    color: #fff;
    border-radius: 6px;
    width: 30px;
    height: 30px;
    padding: 0;
}

.pagamento-container {
    background: #fff;
    border: 1px solid #fecaca;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 16px;
}

.pagamento-container h3 {
    margin: 0 0 12px 0;
    font-size: 16px;
    font-weight: 700;
    color: #111827;
}

.pagamento-opcoes {
    display: flex;
    gap: 12px;
}

.pagamento-opcao {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8fafc;
    border: 1px solid #fecaca;
    border-radius: 10px;
    padding: 12px;
    cursor: pointer;
    gap: 8px;
}

.pagamento-opcao input {
    display: none;
}

.pagamento-opcao span {
    color: #b91c1c;
    font-weight: 600;
    font-size: 14px;
}

.pagamento-opcao.active {
    background: #b91c1c;
    border-color: #b91c1c;
}

.pagamento-opcao.active span {
    color: #fff;
}

.resumo-container {
    background: #fff;
    border: 1px solid #fecaca;
    border-radius: 14px;
    padding: 16px;
    margin-bottom: 20px;
}

.resumo-linha {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.resumo-texto-total {
    font-size: 16px;
    color: #111827;
    font-weight: 700;
}

.resumo-valor-total {
    font-size: 16px;
    color: #b91c1c;
    font-weight: 800;
}

.resumo-valor-taxa {
    color: #059669;
    font-weight: 600;
}
</style>
@endsection
