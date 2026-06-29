<?php

namespace App\Services;

class PautaService
{
    public function nichos(): array
    {
        return [
            'Todos',
            'Política',
            'Economia',
            'Cultura',
            'Esporte',
            'Segurança',
            'Saúde',
            'Educação',
            'Tecnologia',
            'Meio ambiente',
            'Outros',
        ];
    }

    public function calcularTaxaIntermediacao($valor): array
    {
        $valor = (float) $valor;

        if ($valor <= 100) {
            $porcentagem = 20;
        } elseif ($valor <= 300) {
            $porcentagem = 15;
        } elseif ($valor <= 700) {
            $porcentagem = 12;
        } else {
            $porcentagem = 10;
        }

        $valorTaxa = $valor * ($porcentagem / 100);

        return [
            'porcentagem' => $porcentagem,
            'valorTaxa' => $valorTaxa,
            'valorFinal' => $valor + $valorTaxa,
        ];
    }

    public function formatarValor($valor): string
    {
        return 'R$ ' . number_format((float) $valor, 2, ',', '.');
    }
}