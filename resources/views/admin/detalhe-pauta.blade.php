@extends('layouts.app', ['title' => 'Detalhe da Pauta - Admin'])
@section('content')
<section class="section">
    <div class="topo">
        <div>
            <h1>Detalhe da Pauta</h1>
            <p class="muted">Informações completas da pauta</p>
        </div>
        <div class="topo-direita">
            <a class="btn" href="{{ route('admin.pautas') }}">Voltar</a>
        </div>
    </div>

    <div class="card">
        <div class="pauta-header">
            <span class="pill status-{{ $pauta['status'] }}">{{ ucfirst($pauta['status']) }}</span>
            @if($pauta['relevante'])
            <span class="pill destacada">⭐ Destacada</span>
            @endif
            <span class="pill nicho">{{ $pauta['nicho'] }}</span>
            @if($pauta['vendida'])
            <span class="pill vendida">Vendida</span>
            @endif
        </div>
        
        <h2>{{ $pauta['titulo'] }}</h2>
        <p class="muted">{{ $pauta['cidade'] }}/{{ $pauta['estado'] }}</p>
        
        <div class="section-block">
            <h3>Descrição</h3>
            <p>{{ $pauta['descricao'] }}</p>
        </div>
        
        <div class="section-block">
            <h3>Dados do Pauteiro</h3>
            <p><strong>Nome:</strong> {{ $pauta['nome'] }}</p>
            <p><strong>Email:</strong> {{ $pauta['email'] }}</p>
            <p><strong>Telefone:</strong> {{ $pauta['telefone'] }}</p>
            <p><strong>ID Pauteiro:</strong> {{ $pauta['pauteiro_id'] ?? 'N/A' }}</p>
        </div>
        
        <div class="section-block">
            <h3>Informações da Pauta</h3>
            <p><strong>Valor:</strong> R$ {{ number_format($pauta['valor'], 2, ',', '.') }}</p>
            <p><strong>Negociável:</strong> {{ $pauta['negociavel'] ? 'Sim' : 'Não' }}</p>
            <p><strong>Vendida:</strong> {{ $pauta['vendida'] ? 'Sim' : 'Não' }}</p>
            <p><strong>Relevante:</strong> {{ $pauta['relevante'] ? 'Sim' : 'Não' }}</p>
            @if($pauta['arquivo'])
            <p><strong>Arquivo:</strong> {{ $pauta['arquivo'] }}</p>
            @endif
            <p><strong>Criada em:</strong> {{ date('d/m/Y H:i', $pauta['createdAt']) }}</p>
        </div>
        
        @if($pauta['motivo_reprovacao'])
        <div class="motivo-reprovacao">
            <h3>Motivo da Reprovação</h3>
            <p>{{ $pauta['motivo_reprovacao'] }}</p>
        </div>
        @endif
        
        <div class="actions">
            @if($pauta['status'] === 'pendente')
                <form method="post" action="{{ route('admin.pautas.aprovar', $pauta['id']) }}" style="display:inline">
                    @csrf
                    <button class="btn green" type="submit" onclick="return confirm('Aprovar esta pauta?')">✓ Aprovar</button>
                </form>
            @endif
            
            @if($pauta['status'] !== 'reprovada')
                <button class="btn" style="background:#dc2626;color:#fff;border-color:#b91c1c" onclick="promptReprovar({{ $pauta['id'] }})">✗ Reprovar</button>
            @endif
            
            <form method="post" action="{{ route('admin.pautas.destacar', $pauta['id']) }}" style="display:inline">
                @csrf
                <button class="btn" type="submit">{{ $pauta['relevante'] ? '⭐ Remover destaque' : '⭐ Destacar' }}</button>
            </form>
            
            <form method="post" action="{{ route('admin.pautas.remover', $pauta['id']) }}" style="display:inline">
                @csrf
                <button class="btn" style="background:#dc2626;color:#fff;border-color:#b91c1c" type="submit" onclick="return confirm('Remover esta pauta permanentemente?')">🗑️ Remover</button>
            </form>
        </div>
    </div>
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

.pauta-header {
    display: flex;
    gap: 8px;
    margin-bottom: 16px;
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

.pill.vendida {
    background: #e5e7eb;
    color: #374151;
}

.section-block {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 16px;
    margin: 16px 0;
}

.section-block h3 {
    margin: 0 0 12px 0;
    font-size: 16px;
    color: #111827;
}

.section-block p {
    margin: 4px 0;
}

.motivo-reprovacao {
    background: #fee2e2;
    border: 1px solid #fecaca;
    border-radius: 10px;
    padding: 16px;
    margin: 16px 0;
    color: #b91c1c;
}

.motivo-reprovacao h3 {
    margin: 0 0 8px 0;
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
