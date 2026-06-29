<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    protected $fillable = [
        'comprador_id',
        'pauta_id',
        'valor_pauta',
        'valor_taxa',
        'valor_total',
        'forma_pagamento',
        'status',
    ];

    protected $casts = [
        'valor_pauta' => 'decimal:2',
        'valor_taxa' => 'decimal:2',
        'valor_total' => 'decimal:2',
    ];

    public function comprador()
    {
        return $this->belongsTo(User::class, 'comprador_id');
    }

    public function pauta()
    {
        return $this->belongsTo(Pauta::class);
    }
}