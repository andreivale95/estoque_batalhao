<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Secao;
use App\Models\Unidade;

class SecaoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Primeiro, vamos garantir que existe pelo menos uma unidade
        $unidade = Unidade::firstOrCreate(
            ['nome' => 'Unidade Principal'],
            ['sigla' => 'UP']
        );

        // Lista de seções comuns em uma unidade militar
        $secoes = [
            'Armamento',
            'Munição',
            'Equipamento Individual',
            'Material de Comunicações',
            'Material de Informática',
            'Material de Expediente',
            'Material de Intendência',
            'Material de Saúde',
            'Material de Engenharia',
            'Viaturas',
            'Manutenção',
            'Almoxarifado Geral'
        ];

        foreach ($secoes as $secao) {
            Secao::firstOrCreate([
                'nome' => $secao,
                'fk_unidade' => $unidade->id
            ]);
        }
    }
}