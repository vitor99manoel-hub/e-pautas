@extends('layouts.app', ['title' => 'Pautas - Admin'])
@section('content')
<section class="section">
    <div class="topo">
        <div>
            <h1>Gerenciar Pautas</h1>
            <p class="muted">Curadoria e moderação de pautas</p>
        </div>
        <div class="topo-direita">
            <a class="btn" href="{{ route('admin.dashboard') }}">Dashboard</a>
        </div>
    </div>

    <div class="filters">
        <a class="btn {{ request()->query('status') === '' ? 'primary' : '' }}" href="{{ route('admin.pautas') }}">Todas</a>
        <a class="btn {{ request()->query('status') === 'pendente' ? 'primary' : '' }}" href="{{ route('admin.pautas', ['status' => 'pendente']) }}">Pendentes</a>
        <a class="btn {{ request()->query('status') === 'aprovada' ? 'primary' : '' }}" href="{{ route('admin.pautas', ['status' => 'aprovada']) }}">Aprovadas</a>
        <a class="btn {{ request()->query('status') === 'reprovada' ? 'primary' : '' }}" href="{{ route('admin.pautas', ['status' => 'reprovada']) }}">Reprovadas</a>
        <a class="btn {{ request()->query('status') === 'destacadas' ? 'primary' : '' }}" href="{{ route('admin.pautas', ['status' => 'destacadas']) }}">Destacadas</a>
    </div>

    @php
        $statusFiltro = request()->query('status', '');
        $pautasFiltradas = $pautas;
        
        if ($statusFiltro === 'pendente') {
            $pautasFiltradas = collect($pautas)->where('status', 'pendente')->values()->all();
        } elseif ($statusFiltro === 'aprovada') {
            $pautasFiltradas = collect($pautas)->where('status', 'aprovada')->values()->all();
        } elseif ($statusFiltro === 'reprovada') {
            $pautasFiltradas = collect($pautas)->where('status', 'reprovada')->values()->all();
        } elseif ($statusFiltro === 'destacadas') {
            $pautasFiltradas = collect($pautas)->where('relevante', true)->values()->all();
        }
    @endphp

    @if(count($pautasFiltradas) > 0)
    <div class="list">
        @foreach($pautasFiltradas as $pauta)
            <div class="card pauta-card">
                <div class="pauta-header">
                    <span class="pill status-{{ $pauta->status }}">{{ ucfirst($pauta->status) }}</span>
                    @if($pauta->relevante)
                    <span class="pill destacada">⭐ Destacada</span>
                    @endif
                    <span class="pill nicho">{{ $pauta->nicho }}</span>
                </div>
                
                <h3>{{ $pauta->titulo }}</h3>
                <p class="muted">{{ $pauta->cidade }}/{{ $pauta->estado }}</p>
                <p>{{ $pauta->descricao }}</p>
                
                <div class="pauta-meta">
                    <p class="muted">Pauteiro: {{ $pauta->nome }}</p>
                    <p class="muted">Email: {{ $pauta->email }}</p>
                    <p class="muted">Telefone: {{ $pauta->telefone }}</p>
                    <p class="price">R$ {{ number_format($pauta->valor, 2, ',', '.') }}</p>
                    @if($pauta->negociavel)
                    <p class="negociavel">Valor negociável</p>
                    @endif
                </div>
                
                @if($pauta->motivo_reprovacao)
                <div class="motivo-reprovacao">
                    <strong>Motivo da reprovação:</strong> {{ $pauta->motivo_reprovacao }}
                </div>
                @endif
                
                <div class="actions">
                    <a class="btn" href="{{ route('admin.pautas.detalhe', $pauta->id) }}">Ver Detalhes</a>
                    
                    @if($pauta->status === 'pendente')
                        <form method="post" action="{{ route('admin.pautas.aprovar', $pauta->id) }}" style="display:inline">
                            @csrf
                            <button class="btn green" type="submit" onclick="return confirm('Aprovar esta pauta?')">Aprovar</button>
                        </form>
                    @endif
                    
                    @if($pauta->status !== 'reprovada')
                        <button class="btn" style="background:#dc2626;color:#fff;border-color:#b91c1c" onclick="promptReprovar({{ $pauta->id }})">Reprovar</button>
                    @endif
                    
                    <form method="post" action="{{ route('admin.pautas.destacar', $pauta->id) }}" style="display:inline">
                        @csrf
                        <button class="btn" type="submit">{{ $pauta->relevante ? 'Remover destaque' : 'Destacar' }}</button>
                    </form>
                    
                    <form method="post" action="{{ route('admin.pautas.remover', $pauta->id) }}" style="display:inline">
                        @csrf
                        <button class="btn" style="background:#dc2626;color:#fff;border-color:#b91c1c" type="submit" onclick="return confirm('Remover esta pauta permanentemente?')">Remover</button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
    @else
    <div class="card">
        <p class="muted">Nenhuma pauta encontrada para este filtro.</p>
    </div>
    @endif
</section>

<script>
function promptReprovar(id) {
    const motivo = prompt('Digite o motivo da reprovação:');
    if (motivo && motivo.trim()) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route('admin.pautas.reprovar', ':id') }}'.replace(':id', id);
        
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';
        form.appendChild(csrf);
        
        const motivoInput = document.createElement('input');
        motivoInput.type = 'hidden';
        motivoInput.name = 'motivo';
        motivoInput.value = motivo;
        form.appendChild(motivoInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

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

.pauta-card {
    margin-bottom: 16px;
}

.pauta-header {
    display: flex;
    gap: 8px;
    margin-bottom: 12px;
    flex-wrap: wrap;
}

.pill.status-pendente {
    background: #fef3c7;
    color: #d97706;
}

.pill.status-aprovada {
    background: #dcfce7;
    color: #059669;
}

.pill.status-reprovada {
    background: #fee2e2;
    color: #dc2626;
}

.pill.destacada {
    background: #f3e8ff;
    color: #7c3aed;
}

.pill.nicho {
    background: #dbeafe;
    color: #1d4ed8;
}

.pauta-meta {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 12px;
    margin: 12px 0;
}

.motivo-reprovacao {
    background: #fee2e2;
    border: 1px solid #fecaca;
    border-radius: 10px;
    padding: 12px;
    margin: 12px 0;
    color: #b91c1c;
}

.negociavel {
    color: #059669;
    font-weight: 600;
    font-size: 12px;
}

@media (max-width: 768px) {
    .topo {
        flex-direction: column;
        gap: 12px;
    }
    
    .pauta-header {
        flex-direction: column;
    }
    
    .actions {
        flex-direction: column;
    }
    
    .actions .btn {
        width: 100%;
    }
}
</style>
@endsection
