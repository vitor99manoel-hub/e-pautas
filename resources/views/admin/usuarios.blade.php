@extends('layouts.app', ['title' => 'Usuários - Admin'])
@section('content')
<section class="section">
    <div class="topo">
        <div>
            <h1>Usuários Cadastrados</h1>
            <p class="muted">Gerenciamento de usuários da plataforma</p>
        </div>
        <div class="topo-direita">
            <a class="btn" href="{{ route('admin.dashboard') }}">Voltar</a>
        </div>
    </div>

    @if(count($usuarios) > 0)
    <div class="list">
        @foreach($usuarios as $usuario)
            <div class="card usuario-card">
                <div class="usuario-info">
                    <h3>{{ $usuario->nome }}</h3>
                    <p class="muted">{{ $usuario->email }}</p>
                    <p class="muted">Telefone: {{ $usuario->telefone }}</p>
                    <p class="muted">CPF: {{ $usuario->cpf }}</p>
                    <span class="pill {{ $usuario->perfil === 'pauteiro' ? 'pauteiro' : ($usuario->perfil === 'admin' ? 'admin' : 'comprador') }}">
                        {{ ucfirst($usuario->perfil) }}
                    </span>
                </div>
            </div>
        @endforeach
    </div>
    @else
    <div class="card">
        <p class="muted">Nenhum usuário cadastrado ainda.</p>
    </div>
    @endif
</section>

<style>
.topo {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 24px;
}

.topo-direita {
    display: flex;
    gap: 8px;
}

.usuario-card {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.usuario-info {
    flex: 1;
}

.pill.pauteiro {
    background: #d1fae5;
    color: #059669;
}

.pill.comprador {
    background: #dbeafe;
    color: #1d4ed8;
}

.pill.admin {
    background: #1e293b;
    color: #fff;
}

@media (max-width: 768px) {
    .topo {
        flex-direction: column;
        gap: 12px;
    }
    
    .usuario-card {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
}
</style>
@endsection
