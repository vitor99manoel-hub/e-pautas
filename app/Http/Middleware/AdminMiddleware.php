<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle($request, Closure $next)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            return redirect()->route('home')->with('success', 'Acesso negado. Apenas administradores podem acessar.');
        }

        return $next($request);
    }
}
