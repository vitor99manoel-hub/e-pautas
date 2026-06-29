<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@epautas.com'],
            [
                'nome' => 'Administrador e-Pautas',
                'telefone' => '(63) 99999-0000',
                'cpf' => '00000000000',
                'perfil' => 'admin',
                'password' => Hash::make('admin123'),
            ]
        );
    }
}