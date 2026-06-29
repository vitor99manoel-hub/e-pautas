<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CompradorMiddleware
{
    public function handle($request, Closure $next)
    {
        if (!Auth::check() || !Auth::user()->isComprador()) {
            return redirect()->route('home')->with('success', 'Acesso negado. Apenas compradores podem acessar.');
        }

        return $next($request);
    }
}
