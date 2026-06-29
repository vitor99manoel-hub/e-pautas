<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'nome',
        'email',
        'telefone',
        'cpf',
        'perfil',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function pautas()
    {
        return $this->hasMany(Pauta::class);
    }

    public function compras()
    {
        return $this->hasMany(Compra::class, 'comprador_id');
    }

    public function isAdmin()
    {
        return $this->perfil === 'admin';
    }

    public function isPauteiro()
    {
        return $this->perfil === 'pauteiro';
    }

    public function isComprador()
    {
        return $this->perfil === 'comprador';
    }
}