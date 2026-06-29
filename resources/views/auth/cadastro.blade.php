@extends('layouts.app', ['title' => 'Criar conta'])
@section('content')
<section class="form card">
    <h1>Criar conta</h1>
    <p class="muted">Perfil: <strong>{{ $perfil === 'pauteiro' ? 'pauteiro' : 'portal ou página' }}</strong></p>
    
    @if($errors->any())
        <div class="alert" style="background:#fee2e2;color:#b91c1c;border-color:#fecaca">
            {{ $errors->first() }}
        </div>
    @endif
    
    <form method="post" action="{{ route('cadastro.salvar') }}">
        @csrf
        <input type="hidden" name="perfil" value="{{ $perfil }}">
        <label>Nome</label><input name="nome" value="{{ old('nome') }}">
        <label>Telefone</label><input name="telefone" value="{{ old('telefone') }}">
        <label>E-mail</label><input name="email" type="email" value="{{ old('email') }}">
        <label>CPF ou CNPJ</label><input name="cpf" value="{{ old('cpf') }}">
        <label>Senha</label><input name="password" type="password">
        <div class="actions"><button class="btn primary" type="submit">Criar conta</button><a class="btn" href="{{ route('login', ['perfil' => $perfil]) }}">Já tenho conta</a></div>
    </form>
</section>
@endsection
