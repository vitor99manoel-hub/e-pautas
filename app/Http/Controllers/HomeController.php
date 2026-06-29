<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            if ($user->isAdmin()) {
                return redirect()->route('admin.dashboard');
            } elseif ($user->isPauteiro()) {
                return redirect()->route('pauteiro.home');
            } else {
                return redirect()->route('comprador.home');
            }
        }

        return view('home');
    }
}
