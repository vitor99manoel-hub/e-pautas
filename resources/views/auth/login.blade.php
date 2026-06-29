@extends('layouts.app', ['title' => 'Login'])
@section('content')
<section class="form card">
    <h1>Login</h1>
    <p class="muted">Entrando como: <strong>{{ $perfil === 'pauteiro' ? 'pauteiro' : 'portal ou página' }}</strong></p>
    
    @if($errors->any())
        <div class="alert" style="background:#fee2e2;color:#b91c1c;border-color:#fecaca">
            {{ $errors->first() }}
        </div>
    @endif
    
    <form method="post" action="{{ route('login.entrar') }}">
        @csrf
        <input type="hidden" name="perfil" value="{{ $perfil }}">
        <label>E-mail</label>
        <input name="email" type="email" placeholder="Digite seu e-mail">
        <label>Senha</label>
        <input name="password" type="password" placeholder="Digite sua senha">
        <div class="actions">
            <button class="btn primary" type="submit">Entrar</button>
            <a class="btn green" href="{{ route('cadastro', ['perfil' => $perfil]) }}">Criar conta</a>
            <a class="btn" href="{{ route('home') }}">Voltar</a>
        </div>
    </form>
</section>
@endsection
