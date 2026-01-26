<?php

namespace App\Services;

use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class EstoqueUnificadoService
{
    /**
     * Obter estoque unificado (consumo + permanente) com cálculos
     */
    public function obterEstoqueUnificado($filtros = [])
    {
        $query = $this->construirQueryUnificada();

        // Aplicar filtros
        if (!empty($filtros['tipo'])) {
            $query->where('tipo', $filtros['tipo']);
        }

        if (!empty($filtros['fk_categoria'])) {
            $query->where('fk_categoria', $filtros['fk_categoria']);
        }

        if (!empty($filtros['fk_secao'])) {
            $query->where('fk_secao', $filtros['fk_secao']);
        }

        if (!empty($filtros['search'])) {
            $search = "%{$filtros['search']}%";
            $query->where('nome_produto', 'like', $search)
                  ->orWhere('patrimonio', 'like', $search);
        }

        // Ordenar por data entrada descrescente
        $itens = $query->orderBy('data_entrada', 'DESC')->get();

        // Agrupar por fk_produto e agregar quantidades
        $itensAgrupados = collect();
        $produtosVistos = [];

        foreach ($itens as $item) {
            $chave = $item->fk_produto;
            if (!isset($produtosVistos[$chave])) {
                // Pegar o primeiro item deste produto
                $produtoItems = $itens->filter(function($i) use ($chave) {
                    return $i->fk_produto === $chave;
                });

                // Somar quantidades
                $totalQuantidade = $produtoItems->sum('quantidade_total');
                $totalCautelado = $produtoItems->sum('quantidade_cautelada');
                $totalDisponivel = $produtoItems->sum('disponivel');
                
                // Calcular valor total agregado
                $totalValor = $produtoItems->sum('valor_total');
                
                // Calcular valor unitário médio
                $valorMedio = $totalQuantidade > 0 ? $totalValor / $totalQuantidade : 0;

                // Usar o primeiro item como base
                $itemBase = $produtoItems->first();
                $itemBase->quantidade_total = $totalQuantidade;
                $itemBase->quantidade_cautelada = $totalCautelado;
                $itemBase->disponivel = $totalDisponivel;
                $itemBase->quantidade = $totalQuantidade;
                $itemBase->preco_unitario = $valorMedio;
                $itemBase->valor_total = $totalValor;
                $itemBase->valor = $valorMedio;

                $produtosVistos[$chave] = true;
                $itensAgrupados->push($itemBase);
            }
        }

        // Mapear para adicionar aliases compatíveis com a view antiga
        $itensAgrupados = $itensAgrupados->map(function($item) {
            $item->nome = $item->nome_produto;
            $item->categoria_id = $item->fk_categoria;
            $item->unidade_nome = $item->unidade_nome_label ?? 'N/A';
            return $item;
        });

        // Paginar
        $perPage = $filtros['per_page'] ?? 15;
        $page = Paginator::resolveCurrentPage('page') ?: 1;
        $items = $itensAgrupados->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $itensAgrupados->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );
    }

    /**
     * Construir query UNION entre itens_estoque e itens_patrimoniais
     */
    private function construirQueryUnificada()
    {
        // Query para consumo
        $consumo = DB::table('itens_estoque as ie')
            ->select(
                DB::raw("'consumo' as tipo"),
                'ie.id',
                'ie.fk_produto',
                'p.nome as nome_produto',
                'p.fk_categoria',
                'c.nome as categoria_nome',
                'ie.quantidade',
                'ie.quantidade_cautelada',
                DB::raw('(ie.quantidade - ie.quantidade_cautelada) as disponivel'),
                DB::raw('ie.quantidade as quantidade_total'),
                'ie.preco_unitario',
                'ie.valor_total',
                'ie.unidade',
                'u.nome as unidade_nome_label',
                's.nome as secao_nome',
                'ie.data_entrada',
                'ie.data_saida',
                DB::raw('NULL as patrimonio'),
                DB::raw('NULL as serie'),
                DB::raw('NULL as condicao'),
                'ie.observacao',
                'ie.created_at'
            )
            ->join('produtos as p', 'p.id', '=', 'ie.fk_produto')
            ->leftJoin('categorias as c', 'c.id', '=', 'p.fk_categoria')
            ->leftJoin('secaos as s', 's.id', '=', 'ie.fk_secao')
            ->leftJoin('unidades as u', 'u.id', '=', 'ie.unidade');

        // Query para permanente
        $permanente = DB::table('itens_patrimoniais as ip')
            ->select(
                DB::raw("'permanente' as tipo"),
                'ip.id',
                'ip.fk_produto',
                'p.nome as nome_produto',
                'p.fk_categoria',
                'c.nome as categoria_nome',
                DB::raw('NULL as quantidade'),
                'ip.quantidade_cautelada',
                DB::raw('CASE WHEN ip.quantidade_cautelada > 0 THEN 0 ELSE 1 END as disponivel'),
                DB::raw('1 as quantidade_total'),
                DB::raw('NULL as preco_unitario'),
                DB::raw('NULL as valor_total'),
                DB::raw('NULL as unidade'),
                DB::raw('NULL as unidade_nome_label'),
                's.nome as secao_nome',
                'ip.data_entrada',
                'ip.data_saida',
                'ip.patrimonio',
                'ip.serie',
                'ip.condicao',
                'ip.observacao',
                'ip.created_at'
            )
            ->join('produtos as p', 'p.id', '=', 'ip.fk_produto')
            ->leftJoin('categorias as c', 'c.id', '=', 'p.fk_categoria')
            ->leftJoin('secaos as s', 's.id', '=', 'ip.fk_secao');

        // Combinar com UNION
        return $consumo->union($permanente);
    }

    /**
     * Obter quantidade disponível formatada
     */
    public function formatarDisponivel($item)
    {
        if ($item->tipo === 'consumo') {
            return $item->disponivel ?? 0;
        }
        return $item->disponivel ? '1 Unidade' : 'Cautelado';
    }

    /**
     * Obter total formatado
     */
    public function formatarTotal($item)
    {
        if ($item->tipo === 'consumo') {
            return $item->total . ' ' . ($item->unidade ?? 'UN');
        }
        return '1 Unidade';
    }
}
