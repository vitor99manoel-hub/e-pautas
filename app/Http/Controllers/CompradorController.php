<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\Pauta;
use App\Services\CompraService;
use App\Services\PautaService;
use App\Services\UsuarioService;
use Illuminate\Support\Facades\Auth;

class CompradorController extends Controller
{
    protected $pautaService;
    protected $compraService;
    protected $usuarioService;

    public function __construct(PautaService $pautaService, CompraService $compraService, UsuarioService $usuarioService)
    {
        $this->pautaService = $pautaService;
        $this->compraService = $compraService;
        $this->usuarioService = $usuarioService;
    }

    public function home()
    {
        $user = Auth::user();
        $pautas = Pauta::aprovadas()->latest()->get();

        return view('comprador.home', [
            'user' => $user,
            'carrinho' => session()->get('carrinho', []),
            'compras' => $user->compras,
            'pautas' => $pautas,
            'nichos' => $this->pautaService->nichos(),
            'calcularTaxa' => [$this->pautaService, 'calcularTaxaIntermediacao'],
            'formatarValor' => [$this->pautaService, 'formatarValor'],
            'mascararNome' => [$this->usuarioService, 'mascararNome'],
            'mascararTelefone' => [$this->usuarioService, 'mascararTelefone'],
            'mascararEmail' => [$this->usuarioService, 'mascararEmail'],
        ]);
    }

    public function loja()
    {
        $pautas = Pauta::aprovadas()->latest()->get();

        return view('comprador.loja', [
            'pautas' => $pautas,
            'carrinho' => session()->get('carrinho', []),
            'nichos' => $this->pautaService->nichos(),
            'calcularTaxa' => [$this->pautaService, 'calcularTaxaIntermediacao'],
            'formatarValor' => [$this->pautaService, 'formatarValor'],
        ]);
    }

    public function detalhePauta($id)
    {
        $pauta = Pauta::findOrFail($id);

        return view('comprador.detalhe-pauta', [
            'pauta' => $pauta,
            'carrinho' => session()->get('carrinho', []),
            'calcularTaxa' => [$this->pautaService, 'calcularTaxaIntermediacao'],
            'formatarValor' => [$this->pautaService, 'formatarValor'],
            'mascararNome' => [$this->usuarioService, 'mascararNome'],
            'mascararTelefone' => [$this->usuarioService, 'mascararTelefone'],
            'mascararEmail' => [$this->usuarioService, 'mascararEmail'],
        ]);
    }

    public function carrinho()
    {
        $carrinhoIds = session()->get('carrinho', []);
        $itensCarrinho = Pauta::whereIn('id', $carrinhoIds)->get();

        return view('comprador.carrinho', [
            'itens' => $itensCarrinho,
            'calcularTaxa' => [$this->pautaService, 'calcularTaxaIntermediacao'],
            'formatarValor' => [$this->pautaService, 'formatarValor'],
            'mascararNome' => [$this->usuarioService, 'mascararNome'],
        ]);
    }

    public function adicionarAoCarrinho($id)
    {
        $carrinho = session()->get('carrinho', []);

        if (!in_array((int) $id, $carrinho)) {
            $carrinho[] = (int) $id;
        }

        session()->put('carrinho', $carrinho);

        return redirect()->route('carrinho')->with('success', 'Pauta adicionada ao carrinho!');
    }

    public function removerDoCarrinho($id)
    {
        $carrinho = collect(session()->get('carrinho', []))
            ->reject(fn ($pautaId) => $pautaId == (int) $id)
            ->values()
            ->all();

        session()->put('carrinho', $carrinho);

        return redirect()->route('carrinho')->with('success', 'Pauta removida do carrinho!');
    }

    public function finalizarCompra()
    {
        $user = Auth::user();
        $carrinho = session()->get('carrinho', []);

        if (empty($carrinho)) {
            return redirect()->route('carrinho')->with('success', 'Seu carrinho está vazio.');
        }

        $pautas = Pauta::whereIn('id', $carrinho)->get();

        foreach ($pautas as $pauta) {
            $taxa = $this->pautaService->calcularTaxaIntermediacao($pauta->valor);
            
            $this->compraService->criarCompra($user, $pauta, $taxa, request('forma_pagamento'));
            
            $pauta->update(['vendida' => true]);
        }

        session()->forget('carrinho');

        return redirect()->route('compras')->with('success', 'Compra finalizada com sucesso!');
    }

    public function minhasCompras()
    {
        $user = Auth::user();
        $compras = $user->compras()->with('pauta')->latest()->get();

        return view('comprador.minhas-compras', [
            'pautas' => $compras->pluck('pauta'),
        ]);
    }
}
