<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pauta extends Model
{
    protected $fillable = [
        'user_id',
        'titulo',
        'nicho',
        'descricao',
        'cidade',
        'estado',
        'arquivo',
        'valor',
        'negociavel',
        'nome',
        'telefone',
        'email',
        'vendida',
        'status',
        'relevante',
        'motivo_reprovacao',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'negociavel' => 'boolean',
        'vendida' => 'boolean',
        'relevante' => 'boolean',
    ];

    public function pauteiro()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function compras()
    {
        return $this->hasMany(Compra::class);
    }

    public function scopeAprovadas($query)
    {
        return $query->where('status', 'aprovada')->where('vendida', false);
    }
}