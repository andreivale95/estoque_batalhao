<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cautela;
use App\Models\CautelaProduto;
use App\Models\Produto;
use App\Models\Secao;
use App\Models\Itens_estoque;
use Illuminate\Support\Facades\Auth;

class CautelaController extends Controller
{
    public function create()
    {
        // Carrega todos os itens de estoque da unidade do usuário agrupados por produto+seção
        $rawItems = Itens_estoque::with('produto', 'secao')
            ->where('unidade', Auth::user()->fk_unidade)
            ->where('quantidade', '>', 0)  // apenas com estoque > 0
            ->get();

        // Agrupa por produto+seção
        $groupedByProdSec = $rawItems->groupBy(function ($item) {
            return $item->fk_produto . '_' . ($item->fk_secao ?? 0);
        });

        $sectionsMap = [];
        foreach ($groupedByProdSec as $key => $group) {
            $first = $group->first();
            $parts = explode('_', $key);
            $prodId = (int)$parts[0];
            $secaoId = (int)$parts[1];
            $quantidade = $group->sum('quantidade');

            if ($quantidade <= 0) continue;

            if (!isset($sectionsMap[$prodId])) $sectionsMap[$prodId] = [];
            $sectionsMap[$prodId][] = [
                'estoque_id' => (int)$first->id,
                'secao_id' => $secaoId,
                'secao_nome' => optional($first->secao)->nome ?? 'Sem seção',
                'quantidade' => (int)$quantidade,
            ];
        }

        // Converte chaves para string para garantir compatibilidade com JS
        $sectionsMap = array_combine(
            array_map('strval', array_keys($sectionsMap)),
            array_values($sectionsMap)
        );

        // Lista de produtos únicos
        $productsGrouped = [];
        foreach ($sectionsMap as $prodId => $secs) {
            $produto = $rawItems->firstWhere('fk_produto', (int)$prodId)->produto ?? null;
            if (!$produto) continue;
            $total = array_sum(array_column($secs, 'quantidade'));
            if ($total <= 0) continue;
            $productsGrouped[] = [
                'id' => (int)$produto->id,
                'nome' => $produto->nome,
                'quantidade_total' => (int)$total,
            ];
        }

        $itens_estoque = collect($productsGrouped);
        $secoes = Secao::all();
        \Log::info('Debug Cautela Create', [
            'user_unidade' => Auth::user()->fk_unidade,
            'raw_items_count' => $rawItems->count(),
            'sections_map' => $sectionsMap,
            'produtos_count' => count($productsGrouped),
        ]);
        return view('cautelas.create', compact('itens_estoque', 'secoes'))->with('sectionsMap', $sectionsMap);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome_responsavel' => 'required|string',
            'telefone' => 'required|string',
            'instituicao' => 'required|string',
            'data_cautela' => 'required|date',
            'data_prevista_devolucao' => 'required|date',
            'produtos' => 'required|array',
            'quantidades' => 'required|array',
        ]);

        $cautela = Cautela::create([
            'nome_responsavel' => $request->nome_responsavel,
            'telefone' => $request->telefone,
            'instituicao' => $request->instituicao,
            'data_cautela' => $request->data_cautela,
            'data_prevista_devolucao' => $request->data_prevista_devolucao,
        ]);

        foreach ($request->produtos as $index => $produtoId) {
            CautelaProduto::create([
                'cautela_id' => $cautela->id,
                'produto_id' => $produtoId,
                'quantidade' => $request->quantidades[$index],
            ]);
        }

        return redirect()->route('cautelas.create')->with('success', 'Cautela cadastrada com sucesso!');
    }
}