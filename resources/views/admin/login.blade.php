@extends('layouts.app', ['title' => 'Login Admin'])
@section('content')
<section class="section">
    <div class="form card">
        <h1>Login Admin</h1>
        <p class="muted">Acesso restrito à administração do e-Pautas</p>
        
        @if($errors->any())
            <div class="alert" style="background:#fee2e2;color:#b91c1c;border-color:#fecaca">
                {{ $errors->first() }}
            </div>
        @endif
        
        <form method="post" action="{{ route('admin.login.post') }}">
            @csrf
            <label>E-mail</label>
            <input type="email" name="email" required value="admin@epautas.com">
            
            <label>Senha</label>
            <input type="password" name="senha" required value="admin123">
            
            <div class="actions">
                <button class="btn primary" type="submit">Entrar</button>
                <a class="btn" href="{{ route('home') }}">Voltar</a>
            </div>
        </form>
        
        <div style="margin-top:20px;padding:12px;background:#fef3c7;border-radius:10px;font-size:12px">
            <strong>Credenciais de teste:</strong><br>
            Email: admin@epautas.com<br>
            Senha: admin123
        </div>
    </div>
</section>
@endsection
