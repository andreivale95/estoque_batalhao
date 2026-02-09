<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EstoqueLocalizacaoExport
{
    public function __construct(private readonly ?int $unidadeId = null)
    {
    }

    public function collection(): Collection
    {
        $consumo = DB::table('itens_estoque as ie')
            ->join('produtos as p', 'p.id', '=', 'ie.fk_produto')
            ->leftJoin('secaos as s', 's.id', '=', 'ie.fk_secao')
            ->leftJoin('categorias as c', 'c.id', '=', 'p.fk_categoria')
            ->leftJoin('unidades as u', 'u.id', '=', 'ie.unidade')
            ->where('ie.quantidade', '>', 0)
            ->when($this->unidadeId, function ($q) {
                $q->where('ie.unidade', $this->unidadeId);
            })
            ->select([
                DB::raw("'consumo' as tipo"),
                'p.nome as produto',
                DB::raw("NULL as patrimonio"),
                DB::raw('SUM(ie.quantidade) as quantidade'),
                DB::raw("COALESCE(s.nome, 'Sem seção') as secao"),
                DB::raw("COALESCE(u.nome, 'Sem unidade') as unidade"),
                DB::raw("COALESCE(c.nome, 'Sem categoria') as categoria")
            ])
            ->groupBy('p.id', 'p.nome', 's.nome', 'ie.fk_secao', 'u.nome', 'c.nome');

        $permanente = DB::table('itens_patrimoniais as ip')
            ->join('produtos as p', 'p.id', '=', 'ip.fk_produto')
            ->leftJoin('secaos as s', 's.id', '=', 'ip.fk_secao')
            ->leftJoin('categorias as c', 'c.id', '=', 'p.fk_categoria')
            ->leftJoin('unidades as u', 'u.id', '=', 'p.unidade')
            ->whereNull('ip.data_saida')
            ->when($this->unidadeId, function ($q) {
                $q->where('p.unidade', $this->unidadeId);
            })
            ->select([
                DB::raw("'permanente' as tipo"),
                'p.nome as produto',
                'ip.patrimonio as patrimonio',
                DB::raw('1 as quantidade'),
                DB::raw("COALESCE(s.nome, 'Sem seção') as secao"),
                DB::raw("COALESCE(u.nome, 'Sem unidade') as unidade"),
                DB::raw("COALESCE(c.nome, 'Sem categoria') as categoria")
            ]);

        $query = DB::query()
            ->fromSub($consumo->unionAll($permanente), 't')
            ->orderBy('produto')
            ->orderBy('secao');

        return $query->get();
    }

    public function headings(): array
    {
        return ['tipo', 'produto', 'patrimonio', 'quantidade', 'secao', 'unidade', 'categoria'];
    }
}
