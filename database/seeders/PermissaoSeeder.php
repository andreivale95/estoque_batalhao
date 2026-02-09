<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissaoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissoes = [
            ['id_permissao' => '1', 'modulo' => '1', 'nome' => 'Dashboard'],
            ['id_permissao' => '2', 'modulo' => '2', 'nome' => 'Parametros do Sistema'],
            ['id_permissao' => '3', 'modulo' => '3', 'nome' => 'Perfis de Acesso'],
            ['id_permissao' => '4', 'modulo' => '3', 'nome' => 'Usuário'],
            ['id_permissao' => '5', 'modulo' => '4', 'nome' => 'Consultar'],
            ['id_permissao' => '8', 'modulo' => '4', 'nome' => 'Inventário de outras unidades'],
        ];

        foreach ($permissoes as $permissao) {
            if (!DB::table('permissoes')->where('id_permissao', $permissao['id_permissao'])->exists()) {
                DB::table('permissoes')->insert($permissao);
            }
        }
    }
}
