<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PauteiroController;
use App\Http\Controllers\CompradorController;
use App\Http\Controllers\AdminController;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/login/{perfil?}', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'loginPost'])->name('login.entrar');
    Route::get('/cadastro/{perfil?}', [AuthController::class, 'cadastro'])->name('cadastro');
    Route::post('/cadastro', [AuthController::class, 'cadastroPost'])->name('cadastro.salvar');
    Route::get('/admin/login', [AdminController::class, 'login'])->name('admin.login');
    Route::post('/admin/login', [AdminController::class, 'loginPost'])->name('admin.login.post');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/conta', [AuthController::class, 'conta'])->name('conta');
    Route::post('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');
});

// Pauteiro routes
Route::middleware(['auth', 'pauteiro'])->prefix('/pauteiro')->name('pauteiro.')->group(function () {
    Route::get('/', [PauteiroController::class, 'home'])->name('home');
    Route::get('/pautas', [PauteiroController::class, 'minhasPautas'])->name('pautas');
    Route::get('/pautas/criar', [PauteiroController::class, 'criarPauta'])->name('pautas.criar');
    Route::post('/pautas/criar', [PauteiroController::class, 'criarPautaPost'])->name('pautas.salvar');
    Route::get('/pautas/{id}/editar', [PauteiroController::class, 'editarPauta'])->name('pautas.editar');
    Route::post('/pautas/{id}/editar', [PauteiroController::class, 'editarPautaPost'])->name('pautas.atualizar');
    Route::post('/pautas/{id}/excluir', [PauteiroController::class, 'excluirPauta'])->name('pautas.excluir');
    Route::post('/pautas/{id}/vender', [PauteiroController::class, 'marcarComoVendida'])->name('pautas.vender');
});

// Comprador routes
Route::middleware(['auth', 'comprador'])->group(function () {
    Route::get('/comprador', [CompradorController::class, 'home'])->name('comprador.home');
    Route::get('/loja', [CompradorController::class, 'loja'])->name('loja');
    Route::get('/pauta/{id}', [CompradorController::class, 'detalhePauta'])->name('pauta.detalhe');
    Route::get('/carrinho', [CompradorController::class, 'carrinho'])->name('carrinho');
    Route::post('/carrinho/adicionar/{id}', [CompradorController::class, 'adicionarAoCarrinho'])->name('carrinho.adicionar');
    Route::post('/carrinho/remover/{id}', [CompradorController::class, 'removerDoCarrinho'])->name('carrinho.remover');
    Route::post('/carrinho/finalizar', [CompradorController::class, 'finalizarCompra'])->name('carrinho.finalizar');
    Route::get('/minhas-compras', [CompradorController::class, 'minhasCompras'])->name('compras');
});

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('/admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/usuarios', [AdminController::class, 'usuarios'])->name('usuarios');
    Route::get('/pautas', [AdminController::class, 'pautas'])->name('pautas');
    Route::get('/pautas/{id}', [AdminController::class, 'detalhePauta'])->name('pautas.detalhe');
    Route::post('/pautas/{id}/aprovar', [AdminController::class, 'aprovarPauta'])->name('pautas.aprovar');
    Route::post('/pautas/{id}/reprovar', [AdminController::class, 'reprovarPauta'])->name('pautas.reprovar');
    Route::post('/pautas/{id}/destacar', [AdminController::class, 'destacarPauta'])->name('pautas.destacar');
    Route::post('/pautas/{id}/remover', [AdminController::class, 'removerPauta'])->name('pautas.remover');
});