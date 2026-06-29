<?php

namespace App\Http\Controllers;

use App\Http\Requests\CadastroRequest;
use App\Http\Requests\LoginRequest;
use App\Services\UsuarioService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    protected $usuarioService;

    public function __construct(UsuarioService $usuarioService)
    {
        $this->usuarioService = $usuarioService;
    }

    public function login($perfil = 'comprador')
    {
        return view('auth.login', compact('perfil'));
    }

    public function loginPost(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            if ($user->perfil !== $request->perfil) {
                Auth::logout();
                return back()->withErrors(['email' => 'Este usuário não tem perfil de ' . $request->perfil]);
            }

            $request->session()->regenerate();

            if ($user->isAdmin()) {
                return redirect()->route('admin.dashboard')->with('success', 'Login realizado com sucesso!');
            } elseif ($user->isPauteiro()) {
                return redirect()->route('pauteiro.home')->with('success', 'Login realizado com sucesso!');
            } else {
                return redirect()->route('comprador.home')->with('success', 'Login realizado com sucesso!');
            }
        }

        return back()->withErrors(['email' => 'Credenciais inválidas']);
    }

    public function cadastro($perfil = 'comprador')
    {
        return view('auth.cadastro', compact('perfil'));
    }

    public function cadastroPost(CadastroRequest $request)
    {
        $user = \App\Models\User::create([
            'nome' => $request->nome,
            'email' => $request->email,
            'telefone' => $request->telefone,
            'cpf' => $request->cpf,
            'perfil' => $request->perfil,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);

        if ($user->isPauteiro()) {
            return redirect()->route('pauteiro.home')->with('success', 'Conta criada com sucesso!');
        } else {
            return redirect()->route('comprador.home')->with('success', 'Conta criada com sucesso!');
        }
    }

    public function logout()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Logout realizado com sucesso!');
    }

    public function conta()
    {
        if (!Auth::check()) {
            return redirect()->route('login', ['perfil' => 'comprador'])->with('success', 'Faça login para acessar sua conta.');
        }

        $user = Auth::user();

        return view('conta', compact('user'));
    }
}
