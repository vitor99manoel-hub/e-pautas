<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'e-Pautas' }}</title>
    <style>
        :root { --red:#dc2626; --dark-red:#b91c1c; --green:#059669; --bg:#f8fafc; --text:#111827; --muted:#64748b; --border:#fecaca; --card:#fff; }
        * { box-sizing: border-box; }
        body { margin:0; font-family: Arial, Helvetica, sans-serif; background:var(--bg); color:var(--text); }
        a { color:inherit; text-decoration:none; }
        .wrap { width:min(1120px, 92vw); margin:0 auto; }
        .topbar { background:#fff; border-bottom:1px solid #fee2e2; position:sticky; top:0; z-index:10; }
        .nav { min-height:72px; display:flex; align-items:center; justify-content:space-between; gap:20px; }
        .brand { display:flex; align-items:center; gap:12px; font-weight:800; color:var(--dark-red); font-size:22px; }
        .brand img { height:42px; object-fit:contain; }
        .menu { display:flex; gap:10px; flex-wrap:wrap; align-items:center; }
        .menu a, .btn { border:1px solid var(--border); color:var(--dark-red); padding:10px 14px; border-radius:10px; font-weight:700; background:#fff; display:inline-block; cursor:pointer; }
        .btn.primary, .menu a.primary { background:var(--red); color:#fff; border-color:var(--red); }
        .btn.green { background:var(--green); color:#fff; border-color:var(--green); }
        .hero { padding:56px 0; display:grid; grid-template-columns:1.2fr .8fr; gap:28px; align-items:center; }
        .card { background:var(--card); border:1px solid var(--border); border-radius:16px; padding:22px; box-shadow:0 10px 24px rgba(15,23,42,.04); }
        .title { font-size:clamp(28px, 4vw, 46px); line-height:1.05; margin:0 0 12px; color:var(--text); }
        .subtitle { color:var(--muted); font-size:18px; line-height:1.5; margin:0 0 22px; }
        .grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:18px; margin:22px 0; }
        .card h3 { color:var(--dark-red); margin:0 0 8px; }
        .muted { color:var(--muted); }
        .form { max-width:560px; margin:42px auto; }
        label { display:block; font-weight:700; margin:12px 0 6px; }
        input, textarea, select { width:100%; border:1px solid var(--border); border-radius:12px; padding:13px 14px; font:inherit; background:#fff; }
        textarea { min-height:130px; resize:vertical; }
        .alert { padding:14px 16px; border-radius:12px; margin:16px 0; background:#dcfce7; color:#166534; border:1px solid #bbf7d0; }
        .pill { display:inline-block; padding:6px 10px; border-radius:999px; background:#fee2e2; color:var(--dark-red); font-weight:700; font-size:13px; }
        .price { font-size:22px; font-weight:800; color:var(--green); }
        .actions { display:flex; gap:10px; flex-wrap:wrap; margin-top:16px; }
        .list { display:grid; gap:14px; }
        .section { padding:34px 0; }
        .filters { display:flex; gap:8px; overflow:auto; padding:6px 0 18px; }
        .filters a { white-space:nowrap; }
        footer { padding:30px 0; color:var(--muted); text-align:center; }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .hero { grid-template-columns:1fr; padding:32px 0; }
            .nav { align-items:flex-start; flex-direction:column; padding:14px 0; min-height:auto; }
            .menu { flex-direction:column; align-items:stretch; width:100%; }
            .menu a, .btn { width:100%; text-align:center; padding:12px 14px; }
            .grid { grid-template-columns:1fr; }
            .form { margin:24px auto; padding:0 16px; }
            .section { padding:24px 0; }
            .filters { flex-wrap:nowrap; overflow-x:auto; -webkit-overflow-scrolling:touch; }
            .filters a { flex:0 0 auto; }
            .card { padding:16px; }
            .actions { flex-direction:column; }
            .actions .btn, .actions a { width:100%; }
        }
        
        @media (min-width: 769px) and (max-width: 1024px) {
            .grid { grid-template-columns:repeat(2, 1fr); }
            .hero { grid-template-columns:1fr; }
        }
        
        @media (min-width: 1025px) {
            .grid { grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); }
        }
    </style>
</head>
<body>
<header class="topbar">
    <div class="wrap nav">
        <a class="brand" href="{{ route('home') }}">
            <img src="{{ asset('epautas-assets/images/logo-epautas.png') }}" onerror="this.style.display='none'" alt="e-Pautas">
            <span>e-Pautas</span>
        </a>
        <nav class="menu">
            @if(Auth::check() && Auth::user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="primary">Dashboard</a>
                <a href="{{ route('admin.pautas') }}">Pautas</a>
                <a href="{{ route('admin.usuarios') }}">Usuários</a>
                <form method="post" action="{{ route('admin.logout') }}" style="display:inline">
                    @csrf
                    <button class="btn" type="submit">Sair</button>
                </form>
            @elseif(Auth::check())
                @if(Auth::user()->isPauteiro())
                    <a href="{{ route('pauteiro.home') }}">Home</a>
                    <a href="{{ route('pauteiro.pautas') }}">Minhas Pautas</a>
                    <a href="{{ route('pauteiro.pautas.criar') }}">Nova Pauta</a>
                @else
                    <a href="{{ route('comprador.home') }}">Home</a>
                    <a href="{{ route('loja') }}">Loja</a>
                    <a href="{{ route('carrinho') }}">Carrinho</a>
                    <a href="{{ route('compras') }}">Minhas Compras</a>
                @endif
                <a href="{{ route('conta') }}">Minha Conta</a>
                <form method="post" action="{{ route('logout') }}" style="display:inline">
                    @csrf
                    <button class="btn" type="submit">Sair</button>
                </form>
            @else
                <a href="{{ route('loja') }}">Loja</a>
                <a href="{{ route('pauteiro.home') }}">Pauteiro</a>
                <a href="{{ route('comprador.home') }}">Comprador</a>
                <a href="{{ route('admin.login') }}" style="background:#1e293b;color:#fff;border-color:#1e293b">Admin</a>
                <a class="primary" href="{{ route('login', ['perfil' => 'comprador']) }}">Entrar</a>
            @endif
        </nav>
    </div>
</header>
<main class="wrap">
    @if (session('success'))
        <div class="alert">{{ session('success') }}</div>
    @endif
    @yield('content')
</main>
<footer>
    <div class="wrap">Aplicação Laravel com banco de dados, autenticação por perfil e fluxo de compra para apresentação.</div>
</footer>
</body>
</html>
