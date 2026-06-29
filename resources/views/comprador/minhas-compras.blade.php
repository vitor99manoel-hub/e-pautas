@extends('layouts.app', ['title' => 'Pautas Adquiridas'])
@section('content')
<section class="section">
    <h1>Pautas adquiridas</h1>
    <div class="list">
        @forelse($pautas as $pauta)
            <div class="card"><span class="pill">Comprada</span><h3>{{ $pauta['titulo'] }}</h3><p>{{ $pauta['descricao'] }}</p>@if($pauta['arquivo'])<p><strong>Arquivo liberado:</strong> {{ $pauta['arquivo'] }}</p>@else<p><strong>Arquivo:</strong> Não informado</p>@endif</div>
        @empty
            <div class="card">Você ainda não comprou nenhuma pauta neste protótipo.</div>
        @endforelse
    </div>
</section>
@endsection
