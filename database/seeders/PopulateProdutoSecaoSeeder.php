<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Produto;
use App\Models\Itens_estoque;

class PopulateProdutoSecaoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $produtos = Produto::all();
        foreach ($produtos as $produto) {
            $row = Itens_estoque::select('fk_secao', DB::raw('SUM(quantidade) as total'))
                ->where('fk_produto', $produto->id)
                ->groupBy('fk_secao')
                ->orderByDesc('total')
                ->first();

            if ($row && isset($row->fk_secao) && intval($row->fk_secao) > 0) {
                $produto->fk_secao = intval($row->fk_secao);
                $produto->save();
                Log::info('PopulateProdutoSecaoSeeder: set produto '.$produto->id.' fk_secao '.$produto->fk_secao);
            } else {
                Log::info('PopulateProdutoSecaoSeeder: produto '.$produto->id.' has no section with positive id, skipping');
            }
        }
    }
}
