@extends('layouts.app', ['title' => 'e-Pautas'])
@section('content')
<section class="hero">
    <div>
        <h1 class="title">Marketplace de pautas jornalísticas</h1>
        <p class="subtitle">Conecte pauteiros a portais, páginas e veículos que precisam de boas histórias para publicar.</p>
        <div class="actions">
            <a class="btn primary" href="{{ route('login', ['perfil' => 'pauteiro']) }}">Sou Pauteiro</a>
            <a class="btn" href="{{ route('login', ['perfil' => 'comprador']) }}">Sou Portal ou Página</a>
            <a class="btn green" href="{{ route('loja') }}">Ver loja</a>
        </div>
    </div>
    <div class="card">
        <img src="{{ asset('epautas-assets/images/icone-epautas.png') }}" alt="Ícone e-Pautas" style="width:90px;display:block;margin:0 auto 18px" onerror="this.style.display='none'">
        <h3>Como funciona</h3>
        <p class="muted">O pauteiro cadastra uma pauta. O comprador filtra, analisa detalhes, adiciona ao carrinho e finaliza a compra.</p>
    </div>
</section>
<section class="grid">
    <div class="card"><h3>Pauteiro</h3><p class="muted">Cadastre, edite e acompanhe suas pautas.</p></div>
    <div class="card"><h3>Comprador</h3><p class="muted">Encontre pautas por nicho, cidade e valor.</p></div>
    <div class="card"><h3>Banco de dados</h3><p class="muted">Dados reais em PostgreSQL, com usuários, pautas e compras persistentes.</p></div>
</section>
@endsection
