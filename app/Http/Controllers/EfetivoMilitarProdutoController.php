<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EfetivoMilitar;
use App\Models\Unidade;
use App\Models\Kit;
use App\Models\Produto;
use Illuminate\Support\Facades\DB;

class EfetivoMilitarProdutoController extends Controller
{

    public function listar(Request $request)
    {
        $query = EfetivoMilitar::query();

        if ($request->filled('nome')) {
            $query->where('nome', 'like', '%' . $request->nome . '%');
        }

        if ($request->filled('fk_unidade')) {
            $query->where('fk_unidade', $request->fk_unidade);
        }

        if ($request->filled('posto_graduacao')) {
            $query->where('posto_graduacao', $request->posto_graduacao);
        }

        $militares = $query->paginate(10); // Aqui é o paginate(10)

        $unidades = Unidade::all();
        $postos = EfetivoMilitar::select('posto_graduacao')->distinct()->pluck('posto_graduacao');

        return view('efetivo.listar_efetivo', compact('militares', 'unidades', 'postos'));
    }
    public function atribuirProdutos($militarId)
    {


        $militar = EfetivoMilitar::findOrFail($militarId);
        $produtosSelecionados = $militar->produtos->pluck('id')->toArray();
        $kits = Kit::with('produtos')->where('disponivel', 'S')->get();

        return view('efetivo.atribuir_produtos', compact('militar', 'kits', 'produtosSelecionados'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'militar_id' => 'required|exists:efetivo_militar,id',
            'produtos' => 'required|array',
        ]);

        try {
            $militar = EfetivoMilitar::findOrFail($request->militar_id);

            // Extrai os IDs dos produtos selecionados (ignorando valores vazios)
            $produtosSelecionados = collect($request->produtos)
                ->flatten()
                ->filter()
                ->values()
                ->all();

            // Associa os produtos (substitui os antigos)
            $militar->produtos()->sync($produtosSelecionados);

            return redirect()->route('efetivo_produtos.visualizar', $request->militar_id)->with('success', 'Produtos vinculados com sucesso!');
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao vincular produtos: ' . $e->getMessage());
        }
    }
    public function visualizar($id)
    {
        $militar = EfetivoMilitar::with('unidade')->findOrFail($id);

        // Carrega os kits disponíveis com seus produtos
        $kits = Kit::with('produtos')->where('disponivel', 'S')->get();

        // Carrega os produtos atribuídos ao militar via tabela pivot
        $produtosAtribuidos = DB::table('efetivo_militar_produto')
            ->where('fk_efetivo_militar', $militar->id)
            ->pluck('fk_produto')
            ->toArray();

        return view('efetivo.visualizar_produtos', compact('militar', 'kits', 'produtosAtribuidos'));
    }




    public function edit($id)
    {
        $militar = EfetivoMilitar::findOrFail($id);
        $kits = Kit::with('produtos')->get();
        $produtosSelecionados = $militar->produtos->pluck('id')->toArray();

        return view('efetivo_militar_produto.edit', compact('militar', 'kits', 'produtosSelecionados'));
    }

    /**
     * Atualiza os produtos do militar
     */
    public function update(Request $request, $id)
    {
        $militar = EfetivoMilitar::findOrFail($id);

        $request->validate([
            'produtos' => 'required|array',
        ]);

        $militar->produtos()->sync(array_keys($request->produtos));

        return redirect()->route('efetivo_militares.show', $militar->id)->with('success', 'Produtos atualizados com sucesso!');
    }
}
