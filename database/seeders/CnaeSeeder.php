<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CnaeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cnaes = [
            ['descricao' => 'Cultivo de arroz', 'situacao' => true],
            ['descricao' => 'Criação de bovinos para corte', 'situacao' => true],
            ['descricao' => 'Fabricação de produtos de padaria e confeitaria', 'situacao' => true],
            ['descricao' => 'Comércio varejista de mercadorias em geral', 'situacao' => true],
            ['descricao' => 'Transporte rodoviário de carga', 'situacao' => true],
            ['descricao' => 'Serviços de engenharia', 'situacao' => true],
            ['descricao' => 'Atividades de consultoria em gestão empresarial', 'situacao' => true],
            ['descricao' => 'Ensino fundamental', 'situacao' => true],
            ['descricao' => 'Atividades de atendimento hospitalar', 'situacao' => true],
            ['descricao' => 'Desenvolvimento de programas de computador', 'situacao' => true],
        ];

        foreach ($cnaes as $cnae) {
            \App\Models\Cnae::create($cnae);
        }
    }
}
