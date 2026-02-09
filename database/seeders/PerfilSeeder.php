<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PerfilSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (!DB::table('perfis')->where('nome', 'Administrador')->exists()) {
            DB::table('perfis')->insert(['nome' => 'Administrador', 'status' => 's']);
        }

        if (!DB::table('perfis')->where('nome', 'Usuário')->exists()) {
            DB::table('perfis')->insert(['nome' => 'Usuário', 'status' => 's']);
        }

        $adminId = DB::table('perfis')->where('nome', 'Administrador')->value('id_perfil');
        $usuarioId = DB::table('perfis')->where('nome', 'Usuário')->value('id_perfil');

        $permissoesAdmin = ['1', '2', '3', '4', '5', '8'];
        foreach ($permissoesAdmin as $permId) {
            if (!DB::table('perfil_permissao')->where('fk_perfil', $adminId)->where('fk_permissao', $permId)->exists()) {
                DB::table('perfil_permissao')->insert(['fk_perfil' => $adminId, 'fk_permissao' => $permId]);
            }
        }

        $permissoesUsuario = ['1', '5'];
        foreach ($permissoesUsuario as $permId) {
            if (!DB::table('perfil_permissao')->where('fk_perfil', $usuarioId)->where('fk_permissao', $permId)->exists()) {
                DB::table('perfil_permissao')->insert(['fk_perfil' => $usuarioId, 'fk_permissao' => $permId]);
            }
        }

    }
}
