@extends('layouts.app', ['title' => 'Dashboard Admin'])
@section('content')
<section class="section">
    <div class="topo">
        <div>
            <h1>Dashboard Admin</h1>
            <p class="muted">Visão geral da plataforma e-Pautas</p>
        </div>
        <div class="topo-direita">
            <form method="post" action="{{ route('admin.logout') }}" style="display:inline">
                @csrf
                <button class="btn" type="submit">Sair</button>
            </form>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background:#dbeafe;color:#1d4ed8">👥</div>
            <div class="stat-content">
                <p class="stat-value">{{ $stats['total_usuarios'] }}</p>
                <p class="stat-label">Usuários</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background:#d1fae5;color:#059669">📋</div>
            <div class="stat-content">
                <p class="stat-value">{{ $stats['total_pautas'] }}</p>
                <p class="stat-label">Pautas</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background:#fef3c7;color:#d97706">⏳</div>
            <div class="stat-content">
                <p class="stat-value">{{ $stats['pendentes'] }}</p>
                <p class="stat-label">Pendentes</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background:#dcfce7;color:#166534">✅</div>
            <div class="stat-content">
                <p class="stat-value">{{ $stats['aprovadas'] }}</p>
                <p class="stat-label">Aprovadas</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background:#fee2e2;color:#dc2626">❌</div>
            <div class="stat-content">
                <p class="stat-value">{{ $stats['reprovadas'] }}</p>
                <p class="stat-label">Reprovadas</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background:#f3e8ff;color:#7c3aed">⭐</div>
            <div class="stat-content">
                <p class="stat-value">{{ $stats['destacadas'] }}</p>
                <p class="stat-label">Destacadas</p>
            </div>
        </div>
    </div>

    <div class="actions-grid">
        <a class="btn primary" href="{{ route('admin.pautas') }}">Gerenciar Pautas</a>
        <a class="btn" href="{{ route('admin.usuarios') }}">Ver Usuários</a>
    </div>
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

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}

.stat-card {
    background: #fff;
    border: 1px solid #fecaca;
    border-radius: 16px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.stat-icon {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.stat-content {
    flex: 1;
}

.stat-value {
    font-size: 32px;
    font-weight: 800;
    color: #111827;
    margin: 0;
}

.stat-label {
    color: #64748b;
    font-size: 14px;
    margin: 4px 0 0 0;
}

.actions-grid {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

@media (max-width: 768px) {
    .topo {
        flex-direction: column;
        gap: 12px;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .actions-grid {
        flex-direction: column;
    }
    
    .actions-grid .btn {
        width: 100%;
    }
}
</style>
@endsection
