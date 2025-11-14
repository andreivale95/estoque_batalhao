<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call([



            ModuloSeeder::class,
            PermissaoSeeder::class,
            SecaoSeeder::class,
            PerfilSeeder::class,
            UserSeeder::class,
            ParametrosSeeder::class,
            TipoSeeder::class,
            ModeloSeeder::class,
            UnidadeSeeder::class,


        ]);
    }
}
