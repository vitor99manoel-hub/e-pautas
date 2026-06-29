<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EPautasController extends Controller
{
    private function nichos()
    {
        return ['Todos', 'Política', 'Economia', 'Cultura', 'Esporte', 'Segurança', 'Saúde', 'Educação', 'Tecnologia', 'Meio ambiente', 'Outros'];
    }

    private function pautas()
    {
        return session()->get('pautas', [
            [
                'id' => 1,
                'pauteiro_id' => 1,
                'titulo' => 'Impactos da tecnologia na educação',
                'nicho' => 'Educação',
                'cidade' => 'São Paulo',
                'estado' => 'SP',
                'descricao' => 'Pauta sobre como escolas estão usando tecnologia em sala de aula.',
                'valor' => '150',
                'negociavel' => true,
                'arquivo' => null,
                'nome' => 'João Silva',
                'telefone' => '(63) 99999-1234',
                'email' => 'joao@exemplo.com',
                'vendida' => false,
                'status' => 'aprovada',
                'relevante' => false,
                'motivo_reprovacao' => null,
                'createdAt' => time(),
            ],
            [
                'id' => 2,
                'pauteiro_id' => 2,
                'titulo' => 'Pequenos negócios no pós-pandemia',
                'nicho' => 'Economia',
                'cidade' => 'Curitiba',
                'estado' => 'PR',
                'descricao' => 'Histórias de comerciantes que reinventaram seus negócios.',
                'valor' => '220',
                'negociavel' => false,
                'arquivo' => null,
                'nome' => 'Maria Santos',
                'telefone' => '(41) 98888-5678',
                'email' => 'maria@exemplo.com',
                'vendida' => false,
                'status' => 'aprovada',
                'relevante' => false,
                'motivo_reprovacao' => null,
                'createdAt' => time(),
            ],
        ]);
    }

    private function getNextPautaId()
    {
        $pautas = $this->pautas();

        return count($pautas) > 0 ? max(array_column($pautas, 'id')) + 1 : 1;
    }

    public function calcularTaxaIntermediacao($valor)
    {
        $valorNumerico = is_numeric($valor)
            ? (float) $valor
            : (float) str_replace(['R$', '.', ',', ' '], ['', '', '.', ''], $valor);

        if ($valorNumerico <= 100) {
            $porcentagem = 20;
        } elseif ($valorNumerico <= 300) {
            $porcentagem = 15;
        } elseif ($valorNumerico <= 700) {
            $porcentagem = 12;
        } else {
            $porcentagem = 10;
        }

        $valorTaxa = $valorNumerico * ($porcentagem / 100);
        $valorFinal = $valorNumerico + $valorTaxa;

        return [
            'porcentagem' => $porcentagem,
            'valorTaxa' => $valorTaxa,
            'valorFinal' => $valorFinal,
        ];
    }

    public function formatarValor($valor)
    {
        return 'R$ ' . number_format((float) $valor, 2, ',', '.');
    }

    public function mascararNome($nome)
    {
        if (!$nome || strlen($nome) < 2) {
            return $nome;
        }

        $partes = explode(' ', $nome);
        $primeiroNome = $partes[0];

        if (strlen($primeiroNome) <= 3) {
            return $primeiroNome . '***';
        }

        $letrasVisiveis = min(2, strlen($primeiroNome) - 1);
        $parteVisivel = substr($primeiroNome, 0, $letrasVisiveis);
        $parteOculta = str_repeat('*', strlen($primeiroNome) - $letrasVisiveis);

        return $parteVisivel . $parteOculta;
    }

    public function mascararTelefone($telefone)
    {
        if (!$telefone) {
            return $telefone;
        }

        $apenasNumeros = preg_replace('/\D/', '', $telefone);

        if (strlen($apenasNumeros) < 4) {
            return '***';
        }

        if (strlen($apenasNumeros) >= 10) {
            $ddd = substr($apenasNumeros, 0, 2);
            $doisUltimos = substr($apenasNumeros, -2);

            return "($ddd) *****-$doisUltimos";
        }

        $doisUltimos = substr($apenasNumeros, -2);

        return "(**) *****-$doisUltimos";
    }

    public function mascararEmail($email)
    {
        if (!$email || !str_contains($email, '@')) {
            return $email;
        }

        [$parteLocal, $parteDominio] = explode('@', $email);

        if (strlen($parteLocal) <= 2) {
            return $parteLocal . '***@' . $parteDominio;
        }

        $caracteresVisiveis = min(3, strlen($parteLocal) - 1);
        $parteVisivel = substr($parteLocal, 0, $caracteresVisiveis);
        $parteOculta = str_repeat('*', strlen($parteLocal) - $caracteresVisiveis);

        $dominioPartes = explode('.', $parteDominio);
        $dominioBase = $dominioPartes[0];
        $dominioExtensao = implode('.', array_slice($dominioPartes, 1));

        if (strlen($dominioBase) > 3) {
            $dominioBase = substr($dominioBase, 0, 3) . str_repeat('*', strlen($dominioBase) - 3);
        }

        return $parteVisivel . $parteOculta . '@' . $dominioBase . '.' . $dominioExtensao;
    }

    private function helpers()
    {
        return [
            'nichos' => $this->nichos(),
            'calcularTaxa' => [$this, 'calcularTaxaIntermediacao'],
            'formatarValor' => [$this, 'formatarValor'],
            'mascararNome' => [$this, 'mascararNome'],
            'mascararTelefone' => [$this, 'mascararTelefone'],
            'mascararEmail' => [$this, 'mascararEmail'],
        ];
    }

    public function home()
    {
        $user = session()->get('user');

        if ($user) {
            return $user['perfil'] === 'pauteiro'
                ? redirect()->route('pauteiro.home')
                : redirect()->route('comprador.home');
        }

        return view('home', $this->helpers());
    }

    public function login(Request $request)
    {
        $perfil = $request->query('perfil', 'comprador');

        return view('auth.login', array_merge($this->helpers(), [
            'perfil' => $perfil,
        ]));
    }

    public function loginPost(Request $request)
    {
        $request->validate([
            'usuario' => 'required',
            'senha' => 'required',
            'perfil' => 'required|in:pauteiro,comprador',
        ]);

        $user = [
            'id' => rand(1000, 9999),
            'nome' => $request->usuario,
            'email' => $request->usuario,
            'telefone' => '',
            'cpf' => '',
            'perfil' => $request->perfil,
        ];

        session()->put('user', $user);

        return $user['perfil'] === 'pauteiro'
            ? redirect()->route('pauteiro.home')->with('success', 'Login realizado com sucesso!')
            : redirect()->route('comprador.home')->with('success', 'Login realizado com sucesso!');
    }

    public function cadastro(Request $request)
    {
        $perfil = $request->query('perfil', 'comprador');

        return view('auth.cadastro', array_merge($this->helpers(), [
            'perfil' => $perfil,
        ]));
    }

    public function cadastroPost(Request $request)
    {
        $request->validate([
            'nome' => 'required',
            'email' => 'required|email',
            'telefone' => 'required',
            'cpf' => 'required',
            'senha' => 'required',
            'perfil' => 'required|in:pauteiro,comprador',
        ]);

        $user = [
            'id' => rand(1000, 9999),
            'nome' => $request->nome,
            'email' => $request->email,
            'telefone' => $request->telefone,
            'cpf' => $request->cpf,
            'perfil' => $request->perfil,
            'senha' => $request->senha,
        ];

        session()->put('user', $user);

        // Save to usuarios session for admin
        $usuarios = session()->get('usuarios', []);
        $usuarios[] = $user;
        session()->put('usuarios', $usuarios);

        return $user['perfil'] === 'pauteiro'
            ? redirect()->route('pauteiro.home')->with('success', 'Conta criada com sucesso!')
            : redirect()->route('comprador.home')->with('success', 'Conta criada com sucesso!');
    }

    public function logout()
    {
        session()->forget('user');

        return redirect()->route('home')->with('success', 'Logout realizado com sucesso!');
    }

    public function conta()
    {
        $user = session()->get('user');

        if (!$user) {
            return redirect()->route('login')->with('success', 'Faça login para acessar sua conta.');
        }

        return view('conta', array_merge($this->helpers(), [
            'user' => $user,
        ]));
    }

    public function pauteiro()
    {
        $user = session()->get('user');

        if (!$user || $user['perfil'] !== 'pauteiro') {
            return redirect()->route('login', ['perfil' => 'pauteiro']);
        }

        $minhasPautas = collect($this->pautas())
            ->where('pauteiro_id', $user['id'])
            ->values()
            ->all();

        return view('pauteiro.home', array_merge($this->helpers(), [
            'user' => $user,
            'minhasPautas' => $minhasPautas,
        ]));
    }

    public function minhasPautas()
    {
        $user = session()->get('user');

        if (!$user || $user['perfil'] !== 'pauteiro') {
            return redirect()->route('login', ['perfil' => 'pauteiro']);
        }

        $pautas = collect($this->pautas())
            ->where('pauteiro_id', $user['id'])
            ->values()
            ->all();

        return view('pauteiro.pautas', array_merge($this->helpers(), [
            'publicadas' => collect($pautas)->where('vendida', false)->values()->all(),
            'vendidas' => collect($pautas)->where('vendida', true)->values()->all(),
        ]));
    }

    public function criarPauta()
    {
        $user = session()->get('user');

        if (!$user || $user['perfil'] !== 'pauteiro') {
            return redirect()->route('login', ['perfil' => 'pauteiro']);
        }

        return view('pauteiro.criar-pauta', $this->helpers());
    }

    public function criarPautaPost(Request $request)
    {
        $user = session()->get('user');

        if (!$user || $user['perfil'] !== 'pauteiro') {
            return redirect()->route('login', ['perfil' => 'pauteiro']);
        }

        $request->validate([
            'titulo' => 'required',
            'nicho' => 'required',
            'descricao' => 'required',
            'cidade' => 'required',
            'estado' => 'required',
            'valor' => 'required',
            'nome' => 'required',
            'telefone' => 'required',
            'email' => 'required|email',
        ]);

        $pautas = $this->pautas();

        $pautas[] = [
            'id' => $this->getNextPautaId(),
            'pauteiro_id' => $user['id'],
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
            'createdAt' => time(),
        ];

        session()->put('pautas', $pautas);

        return redirect()->route('pauteiro.pautas')->with('success', 'Pauta criada com sucesso!');
    }

    public function editarPauta($id)
    {
        $user = session()->get('user');

        if (!$user || $user['perfil'] !== 'pauteiro') {
            return redirect()->route('login', ['perfil' => 'pauteiro']);
        }

        $pauta = collect($this->pautas())->firstWhere('id', (int) $id);

        abort_if(!$pauta, 404);

        if (($pauta['pauteiro_id'] ?? null) !== $user['id']) {
            return redirect()->route('pauteiro.pautas')->with('success', 'Você não tem permissão para editar esta pauta.');
        }

        return view('pauteiro.editar-pauta', array_merge($this->helpers(), [
            'pauta' => $pauta,
        ]));
    }

    public function editarPautaPost(Request $request, $id)
    {
        $user = session()->get('user');

        if (!$user || $user['perfil'] !== 'pauteiro') {
            return redirect()->route('login', ['perfil' => 'pauteiro']);
        }

        $request->validate([
            'titulo' => 'required',
            'nicho' => 'required',
            'descricao' => 'required',
            'cidade' => 'required',
            'estado' => 'required',
            'valor' => 'required',
            'nome' => 'required',
            'telefone' => 'required',
            'email' => 'required|email',
        ]);

        $pautas = $this->pautas();
        $index = collect($pautas)->search(fn ($pauta) => $pauta['id'] == (int) $id);

        abort_if($index === false, 404);

        if (($pautas[$index]['pauteiro_id'] ?? null) !== $user['id']) {
            return redirect()->route('pauteiro.pautas')->with('success', 'Você não tem permissão para editar esta pauta.');
        }

        $pautas[$index] = array_merge($pautas[$index], [
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

        session()->put('pautas', $pautas);

        return redirect()->route('pauteiro.pautas')->with('success', 'Pauta atualizada com sucesso!');
    }

    public function excluirPauta($id)
    {
        $user = session()->get('user');

        if (!$user || $user['perfil'] !== 'pauteiro') {
            return redirect()->route('login', ['perfil' => 'pauteiro']);
        }

        $pautas = $this->pautas();
        $index = collect($pautas)->search(fn ($pauta) => $pauta['id'] == (int) $id);

        abort_if($index === false, 404);

        if (($pautas[$index]['pauteiro_id'] ?? null) !== $user['id']) {
            return redirect()->route('pauteiro.pautas')->with('success', 'Você não tem permissão para excluir esta pauta.');
        }

        unset($pautas[$index]);
        session()->put('pautas', array_values($pautas));

        return redirect()->route('pauteiro.pautas')->with('success', 'Pauta excluída com sucesso!');
    }

    public function marcarComoVendida($id)
    {
        $user = session()->get('user');

        if (!$user || $user['perfil'] !== 'pauteiro') {
            return redirect()->route('login', ['perfil' => 'pauteiro']);
        }

        $pautas = $this->pautas();
        $index = collect($pautas)->search(fn ($pauta) => $pauta['id'] == (int) $id);

        abort_if($index === false, 404);

        if (($pautas[$index]['pauteiro_id'] ?? null) !== $user['id']) {
            return redirect()->route('pauteiro.pautas')->with('success', 'Você não tem permissão para editar esta pauta.');
        }

        $pautas[$index]['vendida'] = true;
        session()->put('pautas', $pautas);

        return redirect()->route('pauteiro.pautas')->with('success', 'Pauta marcada como vendida!');
    }

    public function comprador(Request $request)
    {
        $user = session()->get('user');

        if (!$user || $user['perfil'] !== 'comprador') {
            return redirect()->route('login', ['perfil' => 'comprador']);
        }

        $pautas = collect($this->pautas())
            ->where('vendida', false)
            ->where('status', 'aprovada')
            ->values()
            ->all();

        return view('comprador.home', array_merge($this->helpers(), [
            'user' => $user,
            'carrinho' => session()->get('carrinho', []),
            'compras' => session()->get('compras', []),
            'pautas' => $pautas,
        ]));
    }

    public function loja()
    {
        $pautas = collect($this->pautas())
            ->where('vendida', false)
            ->where('status', 'aprovada')
            ->values()
            ->all();

        return view('comprador.loja', array_merge($this->helpers(), [
            'pautas' => $pautas,
            'carrinho' => session()->get('carrinho', []),
        ]));
    }

    public function detalhePauta($id)
    {
        $pauta = collect($this->pautas())->firstWhere('id', (int) $id);

        abort_if(!$pauta, 404);

        return view('comprador.detalhe-pauta', array_merge($this->helpers(), [
            'pauta' => $pauta,
            'carrinho' => session()->get('carrinho', []),
        ]));
    }

    public function carrinho()
    {
        $carrinhoIds = session()->get('carrinho', []);
        $itensCarrinho = collect($this->pautas())
            ->whereIn('id', $carrinhoIds)
            ->values()
            ->all();

        return view('comprador.carrinho', array_merge($this->helpers(), [
            'itens' => $itensCarrinho,
        ]));
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
        $user = session()->get('user');

        if (!$user) {
            return redirect()->route('login', ['perfil' => 'comprador']);
        }

        $carrinho = session()->get('carrinho', []);
        $compras = session()->get('compras', []);

        foreach ($carrinho as $pautaId) {
            if (!isset($compras[$user['id']])) {
                $compras[$user['id']] = [];
            }

            if (!in_array($pautaId, $compras[$user['id']])) {
                $compras[$user['id']][] = $pautaId;
            }
        }

        session()->put('compras', $compras);
        session()->forget('carrinho');

        return redirect()->route('compras')->with('success', 'Compra finalizada com sucesso!');
    }

    public function minhasCompras()
    {
        $user = session()->get('user');

        if (!$user) {
            return redirect()->route('login', ['perfil' => 'comprador']);
        }

        $compras = session()->get('compras', []);
        $ids = $compras[$user['id']] ?? [];

        $pautas = collect($this->pautas())
            ->whereIn('id', $ids)
            ->values()
            ->all();

        return view('comprador.minhas-compras', array_merge($this->helpers(), [
            'pautas' => $pautas,
        ]));
    }

    // Admin methods
    public function adminLogin()
    {
        if (session()->get('admin_logged_in')) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    public function adminLoginPost(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'senha' => 'required',
        ]);

        if ($request->email === 'admin@epautas.com' && $request->senha === 'admin123') {
            session()->put('admin_logged_in', true);

            return redirect()->route('admin.dashboard')->with('success', 'Login admin realizado com sucesso!');
        }

        return back()->withInput()->withErrors(['email' => 'Credenciais inválidas']);
    }

    public function adminLogout()
    {
        session()->forget('admin_logged_in');

        return redirect()->route('admin.login')->with('success', 'Logout realizado com sucesso!');
    }

    public function adminDashboard()
    {
        if (!session()->get('admin_logged_in')) {
            return redirect()->route('admin.login');
        }

        $pautas = $this->pautas();
        $usuarios = session()->get('usuarios', []);

        $stats = [
            'total_usuarios' => count($usuarios),
            'total_pautas' => count($pautas),
            'pendentes' => collect($pautas)->where('status', 'pendente')->count(),
            'aprovadas' => collect($pautas)->where('status', 'aprovada')->count(),
            'reprovadas' => collect($pautas)->where('status', 'reprovada')->count(),
            'destacadas' => collect($pautas)->where('relevante', true)->count(),
        ];

        return view('admin.dashboard', array_merge($this->helpers(), [
            'stats' => $stats,
        ]));
    }

    public function adminUsuarios()
    {
        if (!session()->get('admin_logged_in')) {
            return redirect()->route('admin.login');
        }

        $usuarios = session()->get('usuarios', []);

        return view('admin.usuarios', array_merge($this->helpers(), [
            'usuarios' => $usuarios,
        ]));
    }

    public function adminPautas()
    {
        if (!session()->get('admin_logged_in')) {
            return redirect()->route('admin.login');
        }

        $pautas = collect($this->pautas())->sortByDesc('createdAt')->values()->all();

        return view('admin.pautas', array_merge($this->helpers(), [
            'pautas' => $pautas,
        ]));
    }

    public function adminDetalhePauta($id)
    {
        if (!session()->get('admin_logged_in')) {
            return redirect()->route('admin.login');
        }

        $pauta = collect($this->pautas())->firstWhere('id', (int) $id);

        abort_if(!$pauta, 404);

        return view('admin.detalhe-pauta', array_merge($this->helpers(), [
            'pauta' => $pauta,
        ]));
    }

    public function adminAprovarPauta($id)
    {
        if (!session()->get('admin_logged_in')) {
            return redirect()->route('admin.login');
        }

        $pautas = $this->pautas();
        $index = collect($pautas)->search(fn ($pauta) => $pauta['id'] == (int) $id);

        abort_if($index === false, 404);

        $pautas[$index]['status'] = 'aprovada';
        $pautas[$index]['motivo_reprovacao'] = null;
        session()->put('pautas', $pautas);

        return redirect()->route('admin.pautas')->with('success', 'Pauta aprovada com sucesso!');
    }

    public function adminReprovarPauta(Request $request, $id)
    {
        if (!session()->get('admin_logged_in')) {
            return redirect()->route('admin.login');
        }

        $request->validate([
            'motivo' => 'required',
        ]);

        $pautas = $this->pautas();
        $index = collect($pautas)->search(fn ($pauta) => $pauta['id'] == (int) $id);

        abort_if($index === false, 404);

        $pautas[$index]['status'] = 'reprovada';
        $pautas[$index]['motivo_reprovacao'] = $request->motivo;
        session()->put('pautas', $pautas);

        return redirect()->route('admin.pautas')->with('success', 'Pauta reprovada com sucesso!');
    }

    public function adminDestacarPauta($id)
    {
        if (!session()->get('admin_logged_in')) {
            return redirect()->route('admin.login');
        }

        $pautas = $this->pautas();
        $index = collect($pautas)->search(fn ($pauta) => $pauta['id'] == (int) $id);

        abort_if($index === false, 404);

        $pautas[$index]['relevante'] = !$pautas[$index]['relevante'];
        session()->put('pautas', $pautas);

        $acao = $pautas[$index]['relevante'] ? 'destacada' : 'removida destaque';

        return redirect()->route('admin.pautas')->with('success', "Pauta {$acao} com sucesso!");
    }

    public function adminRemoverPauta($id)
    {
        if (!session()->get('admin_logged_in')) {
            return redirect()->route('admin.login');
        }

        $pautas = $this->pautas();
        $index = collect($pautas)->search(fn ($pauta) => $pauta['id'] == (int) $id);

        abort_if($index === false, 404);

        unset($pautas[$index]);
        session()->put('pautas', array_values($pautas));

        return redirect()->route('admin.pautas')->with('success', 'Pauta removida com sucesso!');
    }
}