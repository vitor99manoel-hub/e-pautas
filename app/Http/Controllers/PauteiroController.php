<?php

namespace App\Http\Controllers;

use App\Http\Requests\PautaRequest;
use App\Models\Pauta;
use App\Services\PautaService;
use App\Services\UsuarioService;
use Illuminate\Support\Facades\Auth;

class PauteiroController extends Controller
{
    protected $pautaService;
    protected $usuarioService;

    public function __construct(PautaService $pautaService, UsuarioService $usuarioService)
    {
        $this->pautaService = $pautaService;
        $this->usuarioService = $usuarioService;
    }

    public function home()
    {
        $user = Auth::user();
        $minhasPautas = $user->pautas()->latest()->get();

        return view('pauteiro.home', [
            'user' => $user,
            'minhasPautas' => $minhasPautas,
            'nichos' => $this->pautaService->nichos(),
            'calcularTaxa' => [$this->pautaService, 'calcularTaxaIntermediacao'],
            'formatarValor' => [$this->pautaService, 'formatarValor'],
            'mascararNome' => [$this->usuarioService, 'mascararNome'],
            'mascararTelefone' => [$this->usuarioService, 'mascararTelefone'],
            'mascararEmail' => [$this->usuarioService, 'mascararEmail'],
        ]);
    }

    public function minhasPautas()
    {
        $user = Auth::user();
        $pautas = $user->pautas()->latest()->get();

        return view('pauteiro.pautas', [
            'publicadas' => $pautas->where('vendida', false),
            'vendidas' => $pautas->where('vendida', true),
            'nichos' => $this->pautaService->nichos(),
            'calcularTaxa' => [$this->pautaService, 'calcularTaxaIntermediacao'],
            'formatarValor' => [$this->pautaService, 'formatarValor'],
        ]);
    }

    public function criarPauta()
    {
        return view('pauteiro.criar-pauta', [
            'nichos' => $this->pautaService->nichos(),
            'calcularTaxa' => [$this->pautaService, 'calcularTaxaIntermediacao'],
            'formatarValor' => [$this->pautaService, 'formatarValor'],
        ]);
    }

    public function criarPautaPost(PautaRequest $request)
    {
        $user = Auth::user();

        Pauta::create([
            'user_id' => $user->id,
            'titulo' => $request->titulo,
            'nicho' => $request->nicho,
            'descricao' => $request->descricao,
            'cidade' => $request->cidade,
            'estado' => $request->estado,
            'valor' => $request->valor,
            'negociavel' => $request->has('negociavel'),
            'arquivo' => $request->file('arquivo') ? $request->file('arquivo')->getClientOriginalName() : $request->arquivo,
            'nome' => $request->nome,
            'telefone' => $request->telefone,
            'email' => $request->email,
            'vendida' => false,
            'status' => 'pendente',
            'relevante' => false,
            'motivo_reprovacao' => null,
        ]);

        return redirect()->route('pauteiro.pautas')->with('success', 'Pauta criada com sucesso!');
    }

    public function editarPauta($id)
    {
        $user = Auth::user();
        $pauta = Pauta::findOrFail($id);

        if ($pauta->user_id !== $user->id) {
            return redirect()->route('pauteiro.pautas')->with('success', 'Você não tem permissão para editar esta pauta.');
        }

        return view('pauteiro.editar-pauta', [
            'pauta' => $pauta,
            'nichos' => $this->pautaService->nichos(),
            'calcularTaxa' => [$this->pautaService, 'calcularTaxaIntermediacao'],
            'formatarValor' => [$this->pautaService, 'formatarValor'],
        ]);
    }

    public function editarPautaPost(PautaRequest $request, $id)
    {
        $user = Auth::user();
        $pauta = Pauta::findOrFail($id);

        if ($pauta->user_id !== $user->id) {
            return redirect()->route('pauteiro.pautas')->with('success', 'Você não tem permissão para editar esta pauta.');
        }

        $pauta->update([
            'titulo' => $request->titulo,
            'nicho' => $request->nicho,
            'descricao' => $request->descricao,
            'cidade' => $request->cidade,
            'estado' => $request->estado,
            'valor' => $request->valor,
            'negociavel' => $request->has('negociavel'),
            'arquivo' => $request->file('arquivo') ? $request->file('arquivo')->getClientOriginalName() : $request->arquivo,
            'nome' => $request->nome,
            'telefone' => $request->telefone,
            'email' => $request->email,
        ]);

        return redirect()->route('pauteiro.pautas')->with('success', 'Pauta atualizada com sucesso!');
    }

    public function excluirPauta($id)
    {
        $user = Auth::user();
        $pauta = Pauta::findOrFail($id);

        if ($pauta->user_id !== $user->id) {
            return redirect()->route('pauteiro.pautas')->with('success', 'Você não tem permissão para excluir esta pauta.');
        }

        $pauta->delete();

        return redirect()->route('pauteiro.pautas')->with('success', 'Pauta excluída com sucesso!');
    }

    public function marcarComoVendida($id)
    {
        $user = Auth::user();
        $pauta = Pauta::findOrFail($id);

        if ($pauta->user_id !== $user->id) {
            return redirect()->route('pauteiro.pautas')->with('success', 'Você não tem permissão para editar esta pauta.');
        }

        $pauta->update(['vendida' => true]);

        return redirect()->route('pauteiro.pautas')->with('success', 'Pauta marcada como vendida!');
    }
}
