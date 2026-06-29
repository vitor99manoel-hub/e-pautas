<?php

namespace App\Services;

use App\Models\Compra;
use App\Models\Pauta;
use App\Models\User;

class CompraService
{
    public function criarCompra(User $comprador, Pauta $pauta, array $taxa, ?string $formaPagamento = null): Compra
    {
        return Compra::create([
            'comprador_id' => $comprador->id,
            'pauta_id' => $pauta->id,
            'valor_pauta' => $pauta->valor,
            'valor_taxa' => $taxa['valorTaxa'],
            'valor_total' => $taxa['valorFinal'],
            'forma_pagamento' => $formaPagamento,
            'status' => 'paga',
        ]);
    }
}