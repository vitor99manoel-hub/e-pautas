<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'joao@epautas.com'],
            [
                'nome' => 'João Silva',
                'telefone' => '(63) 99999-9999',
                'cpf' => '11111111111',
                'perfil' => 'pauteiro',
                'password' => Hash::make('123456'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'portal@epautas.com'],
            [
                'nome' => 'Portal Tocantins',
                'telefone' => '(63) 98888-8888',
                'cpf' => '22222222222',
                'perfil' => 'comprador',
                'password' => Hash::make('123456'),
            ]
        );
    }
}