<?php

namespace App\Http\Controllers;

use App\Models\Pauta;
use App\Models\User;
use App\Services\PautaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    protected $pautaService;

    public function __construct(PautaService $pautaService)
    {
        $this->pautaService = $pautaService;
    }

    public function login()
    {
        if (Auth::check() && Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    public function loginPost(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if (!$user->isAdmin()) {
                Auth::logout();
                return back()->withErrors(['email' => 'Acesso negado. Apenas administradores podem acessar.']);
            }

            $request->session()->regenerate();

            return redirect()->route('admin.dashboard')->with('success', 'Login admin realizado com sucesso!');
        }

        return back()->withInput()->withErrors(['email' => 'Credenciais inválidas']);
    }

    public function logout()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('admin.login')->with('success', 'Logout realizado com sucesso!');
    }

    public function dashboard()
    {
        $stats = [
            'total_usuarios' => User::count(),
            'total_pautas' => Pauta::count(),
            'pendentes' => Pauta::where('status', 'pendente')->count(),
            'aprovadas' => Pauta::where('status', 'aprovada')->count(),
            'reprovadas' => Pauta::where('status', 'reprovada')->count(),
            'destacadas' => Pauta::where('relevante', true)->count(),
        ];

        return view('admin.dashboard', [
            'stats' => $stats,
        ]);
    }

    public function usuarios()
    {
        $usuarios = User::all();

        return view('admin.usuarios', [
            'usuarios' => $usuarios,
        ]);
    }

    public function pautas()
    {
        $pautas = Pauta::latest()->get();

        return view('admin.pautas', [
            'pautas' => $pautas,
            'nichos' => $this->pautaService->nichos(),
            'calcularTaxa' => [$this->pautaService, 'calcularTaxaIntermediacao'],
            'formatarValor' => [$this->pautaService, 'formatarValor'],
        ]);
    }

    public function detalhePauta($id)
    {
        $pauta = Pauta::findOrFail($id);

        return view('admin.detalhe-pauta', [
            'pauta' => $pauta,
            'calcularTaxa' => [$this->pautaService, 'calcularTaxaIntermediacao'],
            'formatarValor' => [$this->pautaService, 'formatarValor'],
        ]);
    }

    public function aprovarPauta($id)
    {
        $pauta = Pauta::findOrFail($id);

        $pauta->update([
            'status' => 'aprovada',
            'motivo_reprovacao' => null,
        ]);

        return redirect()->route('admin.pautas')->with('success', 'Pauta aprovada com sucesso!');
    }

    public function reprovarPauta(Request $request, $id)
    {
        $request->validate([
            'motivo' => 'required',
        ]);

        $pauta = Pauta::findOrFail($id);

        $pauta->update([
            'status' => 'reprovada',
            'motivo_reprovacao' => $request->motivo,
        ]);

        return redirect()->route('admin.pautas')->with('success', 'Pauta reprovada com sucesso!');
    }

    public function destacarPauta($id)
    {
        $pauta = Pauta::findOrFail($id);

        $pauta->update([
            'relevante' => !$pauta->relevante,
        ]);

        $acao = $pauta->relevante ? 'destacada' : 'removida destaque';

        return redirect()->route('admin.pautas')->with('success', "Pauta {$acao} com sucesso!");
    }

    public function removerPauta($id)
    {
        $pauta = Pauta::findOrFail($id);

        $pauta->delete();

        return redirect()->route('admin.pautas')->with('success', 'Pauta removida com sucesso!');
    }
}
