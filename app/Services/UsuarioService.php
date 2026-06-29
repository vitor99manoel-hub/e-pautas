<?php

namespace App\Services;

class UsuarioService
{
    public function mascararNome($nome): string
    {
        if (!$nome) {
            return '';
        }

        return mb_substr($nome, 0, 2) . '***';
    }

    public function mascararTelefone($telefone): string
    {
        if (!$telefone) {
            return '';
        }

        $numeros = preg_replace('/\D/', '', $telefone);

        if (strlen($numeros) < 4) {
            return '***';
        }

        return '(' . substr($numeros, 0, 2) . ') *****-' . substr($numeros, -2);
    }

    public function mascararEmail($email): string
    {
        if (!$email || !str_contains($email, '@')) {
            return '';
        }

        [$local, $dominio] = explode('@', $email);

        return mb_substr($local, 0, 3) . '***@' . mb_substr($dominio, 0, 3) . '***';
    }
}