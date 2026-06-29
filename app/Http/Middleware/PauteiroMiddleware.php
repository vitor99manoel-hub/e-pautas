<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class PauteiroMiddleware
{
    public function handle($request, Closure $next)
    {
        if (!Auth::check() || !Auth::user()->isPauteiro()) {
            return redirect()->route('home')->with('success', 'Acesso negado. Apenas pauteiros podem acessar.');
        }

        return $next($request);
    }
}
