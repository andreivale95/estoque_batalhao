<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModuloSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (!DB::table('modulos')->where('id_modulo', 1)->exists()) {
            DB::table('modulos')->insert(['id_modulo' => '1', 'nome' => 'Dashboard']);
        }
        if (!DB::table('modulos')->where('id_modulo', 2)->exists()) {
            DB::table('modulos')->insert(['id_modulo' => '2', 'nome' => 'Administração']);
        }
        if (!DB::table('modulos')->where('id_modulo', 3)->exists()) {
            DB::table('modulos')->insert(['id_modulo' => '3', 'nome' => 'Segurança']);
        }
        if (!DB::table('modulos')->where('id_modulo', 4)->exists()) {
            DB::table('modulos')->insert(['id_modulo' => '4', 'nome' => 'Registros']);
        }
        if (!DB::table('modulos')->where('id_modulo', 5)->exists()) {
            DB::table('modulos')->insert(['id_modulo' => '5', 'nome' => 'Relatórios']);
        }
        if (!DB::table('modulos')->where('id_modulo', 6)->exists()) {
            DB::table('modulos')->insert(['id_modulo' => '6', 'nome' => 'Cautelas']);
        }

    }
}
