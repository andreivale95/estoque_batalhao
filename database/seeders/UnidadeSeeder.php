<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnidadeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $unidades = [
            ['id' => 1, 'nome' => '1º BEPCIF'],
            ['id' => 2, 'nome' => '2º BEPCIF'],
            ['id' => 3, 'nome' => '3º BEPCIF'],
            ['id' => 4, 'nome' => '4º BEPCIF'],
            ['id' => 5, 'nome' => '5º BEPCIF'],
            ['id' => 6, 'nome' => '6º BEPCIF'],
            ['id' => 7, 'nome' => '7º BEPCIF'],
            ['id' => 8, 'nome' => '8º BEPCIF'],
            ['id' => 9, 'nome' => '9º BEPCIF'],
            ['id' => 10, 'nome' => 'COMANDO GERAL'],
            ['id' => 11, 'nome' => 'DAT'],
            ['id' => 12, 'nome' => 'DIRETORIA DE SAUDE'],
            ['id' => 13, 'nome' => 'DIRETORIA DE ENSINO'],
        ];

        DB::table('unidades')->upsert($unidades, ['id'], ['nome']);

    }
}
