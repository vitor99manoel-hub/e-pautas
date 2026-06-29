<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PautaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:255'],
            'nicho' => ['required', 'string', 'max:100'],
            'descricao' => ['required', 'string'],
            'cidade' => ['required', 'string', 'max:120'],
            'estado' => ['required', 'string', 'max:2'],
            'arquivo' => ['nullable'],
            'valor' => ['required', 'numeric', 'min:1'],
            'negociavel' => ['nullable'],
            'nome' => ['required', 'string', 'max:255'],
            'telefone' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:255'],
        ];
    }
}