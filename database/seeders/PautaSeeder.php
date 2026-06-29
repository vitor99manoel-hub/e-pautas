<?php

namespace Database\Seeders;

use App\Models\Pauta;
use App\Models\User;
use Illuminate\Database\Seeder;

class PautaSeeder extends Seeder
{
    public function run(): void
    {
        $pauteiro = User::where('email', 'joao@epautas.com')->first();

        if (!$pauteiro) {
            return;
        }

        $pautas = [
            ['Prefeitura inaugura nova escola municipal', 'Educação', 'Palmas', 'TO', 120, true, true],
            ['Novo hospital regional amplia atendimento', 'Saúde', 'Araguaína', 'TO', 250, false, true],
            ['Festival gastronômico movimenta turismo local', 'Cultura', 'Gurupi', 'TO', 180, true, false],
            ['Operação da Polícia Civil prende quadrilha', 'Segurança', 'Palmas', 'TO', 300, false, true],
            ['Pequenos negócios crescem no centro comercial', 'Economia', 'Porto Nacional', 'TO', 160, true, false],
            ['Tecnologia chega às escolas públicas', 'Tecnologia', 'Paraíso do Tocantins', 'TO', 220, true, true],
            ['Projeto ambiental recupera nascente urbana', 'Meio ambiente', 'Dianópolis', 'TO', 140, false, false],
            ['Time local conquista campeonato estadual', 'Esporte', 'Araguaína', 'TO', 130, true, false],
            ['Comunidade cobra melhorias no transporte', 'Política', 'Palmas', 'TO', 200, true, true],
            ['Feira de artesanato fortalece economia criativa', 'Cultura', 'Gurupi', 'TO', 110, true, false],
        ];

        foreach ($pautas as $item) {
            Pauta::updateOrCreate(
                ['titulo' => $item[0]],
                [
                    'user_id' => $pauteiro->id,
                    'titulo' => $item[0],
                    'nicho' => $item[1],
                    'descricao' => 'Pauta demonstrativa criada para testar o marketplace e-Pautas com banco de dados real.',
                    'cidade' => $item[2],
                    'estado' => $item[3],
                    'arquivo' => null,
                    'valor' => $item[4],
                    'negociavel' => $item[5],
                    'nome' => $pauteiro->nome,
                    'telefone' => $pauteiro->telefone,
                    'email' => $pauteiro->email,
                    'vendida' => false,
                    'status' => 'aprovada',
                    'relevante' => $item[6],
                    'motivo_reprovacao' => null,
                ]
            );
        }
    }
}