@extends('layouts.app', ['title' => 'Minha Conta'])
@section('content')
<section class="form card">
    <h1>Minha conta</h1>
    <p class="muted">Perfil: <strong>{{ $user['perfil'] === 'pauteiro' ? 'Pauteiro' : 'Comprador' }}</strong></p>
    <label>Nome</label><input value="{{ $user['nome'] }}" readonly>
    <label>Telefone</label><input value="{{ $user['telefone'] }}" readonly>
    <label>E-mail</label><input value="{{ $user['email'] }}" readonly>
    <label>CPF/CNPJ</label><input value="{{ $user['cpf'] }}" readonly>
    <div class="actions"><a class="btn primary" href="{{ route($user['perfil'] === 'pauteiro' ? 'pauteiro.home' : 'comprador.home') }}">Voltar ao painel</a></div>
</section>
@endsection
