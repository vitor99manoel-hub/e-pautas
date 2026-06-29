<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CadastroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'telefone' => ['nullable', 'string', 'max:30'],
            'cpf' => ['nullable', 'string', 'max:20'],
            'perfil' => ['required', 'in:comprador,pauteiro'],
            'password' => ['required', 'string', 'min:6'],
        ];
    }
}