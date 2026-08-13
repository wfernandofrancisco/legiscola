<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class NaturezaJuridicaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $naturezasJuridicas = [
            ['descricao' => 'Pessoa Física', 'situacao' => true],
            ['descricao' => 'Empresa Individual de Responsabilidade Limitada (EIRELI)', 'situacao' => true],
            ['descricao' => 'Sociedade Limitada (Ltda)', 'situacao' => true],
            ['descricao' => 'Sociedade Anônima (S.A.)', 'situacao' => true],
            ['descricao' => 'Sociedade Simples', 'situacao' => true],
            ['descricao' => 'Sociedade Simples Limitada', 'situacao' => true],
            ['descricao' => 'Sociedade Empresária Limitada', 'situacao' => true],
            ['descricao' => 'Cooperativa', 'situacao' => true],
            ['descricao' => 'Associação', 'situacao' => true],
            ['descricao' => 'Fundação', 'situacao' => true],
            ['descricao' => 'Órgão Público do Poder Executivo Federal', 'situacao' => true],
            ['descricao' => 'Órgão Público do Poder Executivo Estadual ou do Distrito Federal', 'situacao' => true],
            ['descricao' => 'Órgão Público do Poder Executivo Municipal', 'situacao' => true],
            ['descricao' => 'Órgão Público do Poder Legislativo Federal', 'situacao' => true],
            ['descricao' => 'Órgão Público do Poder Legislativo Estadual ou do Distrito Federal', 'situacao' => true],
            ['descricao' => 'Órgão Público do Poder Legislativo Municipal', 'situacao' => true],
            ['descricao' => 'Órgão Público do Poder Judiciário Federal', 'situacao' => true],
            ['descricao' => 'Órgão Público do Poder Judiciário Estadual', 'situacao' => true],
            ['descricao' => 'Autarquia Federal', 'situacao' => true],
            ['descricao' => 'Autarquia Estadual ou do Distrito Federal', 'situacao' => true],
        ];

        foreach ($naturezasJuridicas as $naturezaJuridica) {
            \App\Models\NaturezaJuridica::create($naturezaJuridica);
        }
    }
}
