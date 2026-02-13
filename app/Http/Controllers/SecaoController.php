<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Secao;
use App\Models\Unidade;
use App\Models\Itens_estoque;
use App\Models\ItenPatrimonial;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SecaoController extends Controller
{
    public function transferirLoteForm($unidadeId, $secaoId)
    {
        $secao = Secao::findOrFail($secaoId);
        $itens = Itens_estoque::where('fk_secao', $secaoId)->with('produto')->get();
        $itensPatrimoniais = ItenPatrimonial::where('fk_secao', $secaoId)
            ->whereNull('data_saida')
            ->with('produto')
            ->get();
        $todasSecoes = Secao::where('fk_unidade', $unidadeId)->orderBy('nome')->get();
        return view('secoes.transferir_lote', compact('secao', 'itens', 'itensPatrimoniais', 'todasSecoes'));
    }

    public function transferirLote(Request $request, $unidadeId, $secaoId)
    {
        $request->validate([
            'item_id' => 'nullable|array',
            'patrimonio_id' => 'nullable|array',
            'nova_secao' => 'required|integer',
        ]);
        
        DB::beginTransaction();
        try {
            // Transferir itens de consumo
            if ($request->has('item_id') && is_array($request->item_id)) {
                $quantidades = $request->input('quantidade_transferir', []);
                foreach ($request->item_id as $idx => $itemId) {
                    if (!$itemId) continue;
                    
                    $item = Itens_estoque::find($itemId);
                    $qtdTransferir = isset($quantidades[$idx]) ? intval($quantidades[$idx]) : 0;
                    
                    if ($item && $qtdTransferir > 0 && $qtdTransferir <= $item->quantidade) {
                        $itemDestino = Itens_estoque::where('fk_secao', $request->nova_secao)
                            ->where('fk_produto', $item->fk_produto)
                            ->where('lote', $item->lote)
                            ->first();
                        
                        if ($itemDestino) {
                            $itemDestino->quantidade += $qtdTransferir;
                            $itemDestino->save();
                        } else {
                            $novoItem = $item->replicate();
                            $novoItem->fk_secao = $request->nova_secao;
                            $novoItem->quantidade = $qtdTransferir;
                            $novoItem->save();
                        }
                        
                        $item->quantidade -= $qtdTransferir;
                        if ($item->quantidade == 0) {
                            $item->delete();
                        } else {
                            $item->save();
                        }
                    }
                }
            }
            
            // Transferir itens patrimoniais
            if ($request->has('patrimonio_id') && is_array($request->patrimonio_id)) {
                foreach ($request->patrimonio_id as $patrimonioId) {
                    if (!$patrimonioId) continue;
                    
                    $patrimonio = ItenPatrimonial::find($patrimonioId);
                    if ($patrimonio && is_null($patrimonio->data_saida)) {
                        $patrimonio->fk_secao = $request->nova_secao;
                        $patrimonio->save();
                        
                        Log::info("Patrimônio transferido", [
                            'patrimonio' => $patrimonio->patrimonio,
                            'nova_secao' => $request->nova_secao
                        ]);
                    }
                }
            }
            
            DB::commit();
            return redirect()->route('secoes.ver', ['unidade' => $unidadeId, 'secao' => $secaoId])
                ->with('success', 'Itens transferidos com sucesso!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao transferir itens', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Erro ao transferir itens: ' . $e->getMessage());
        }
    }
    /**
     * Get the items for a specific section.
     *
     * @param Secao $secao
     * @return \Illuminate\Http\JsonResponse
     */
    public function getItems(Secao $secao)
    {
        $items = $secao->produtos()
            ->select('produtos.*')
            ->selectRaw('COALESCE(
                (SELECT SUM(quantidade) 
                FROM produto_secao 
                WHERE produto_id = produtos.id 
                AND secao_id = ?), 0) - 
                COALESCE(
                    (SELECT SUM(quantidade) 
                    FROM cautela_produto cp 
                    JOIN cautelas c ON c.id = cp.cautela_id 
                    WHERE cp.produto_id = produtos.id 
                    AND c.data_devolucao IS NULL), 0) as quantidade', 
                [$secao->id]
            )
            ->having('quantidade', '>', 0)
            ->get();

        return response()->json($items);
    }

    public function ver($unidadeId, $secaoId)
    {
        $secao = Secao::with(['unidade'])->findOrFail($secaoId);
        
        // Busca itens de consumo da secao
        $itensConsumo = Itens_estoque::where('fk_secao', $secaoId)
            ->with(['produto'])
            ->orderBy('ordem_pdf', 'asc')
            ->get();

        // Agrupa consumo por produto para exibir quantidade total
        $consumoAgrupado = $itensConsumo->groupBy('fk_produto')->map(function($grupo) {
            return [
                'produto' => $grupo->first()->produto,
                'quantidade' => $grupo->sum('quantidade'),
            ];
        });

        // Busca itens patrimoniais da secao (um por patrimonio)
        $itensPatrimoniais = ItenPatrimonial::where('fk_secao', $secaoId)
            ->whereNull('data_saida')
            ->with(['produto'])
            ->orderBy('ordem_pdf', 'asc')
            ->get();

        $totalItensSecao = $consumoAgrupado->count() + $itensPatrimoniais->count();
        
        $outrasSecoes = Secao::where('fk_unidade', $unidadeId)->where('id', '!=', $secaoId)->get();
        
        return view('secoes.ver', compact('secao', 'itensConsumo', 'consumoAgrupado', 'itensPatrimoniais', 'totalItensSecao', 'outrasSecoes', 'unidadeId'));
    }

    public function gerarPDF($unidadeId, $secaoId)
    {
        try {
            $secao = Secao::with(['unidade'])->findOrFail($secaoId);

            $itensConsumo = Itens_estoque::where('fk_secao', $secaoId)
                ->with(['produto'])
                ->orderBy('ordem_pdf', 'asc')
                ->get();

            $consumoAgrupado = $itensConsumo->groupBy('fk_produto')->map(function($grupo) {
                return [
                    'produto' => $grupo->first()->produto,
                    'quantidade' => $grupo->sum('quantidade'),
                ];
            });

            $itensPatrimoniais = ItenPatrimonial::where('fk_secao', $secaoId)
                ->whereNull('data_saida')
                ->with(['produto'])
                ->orderBy('ordem_pdf', 'asc')
                ->get();

            $totalItensSecao = $consumoAgrupado->count() + $itensPatrimoniais->count();

            $pdf = \PDF::loadView('secoes.pdf', compact('secao', 'consumoAgrupado', 'itensPatrimoniais', 'totalItensSecao'));

            return $pdf->download('Secao-' . $secao->nome . '-itens.pdf');
        } catch (\Exception $e) {
            \Log::error('Erro ao gerar PDF da secao', [$e]);
            return back()->with('error', 'Erro ao gerar PDF da secao');
        }
    }

    public function transferirItens(Request $request, $unidadeId, $secaoId)
    {
        $request->validate([
            'item_id' => 'required|array',
            'nova_secao' => 'required|integer',
        ]);

        $quantidades = $request->input('quantidade_transferir', []);

        foreach ($request->item_id as $idx => $itemId) {
            $item = Itens_estoque::find($itemId);
            $qtdTransferir = isset($quantidades[$idx]) ? intval($quantidades[$idx]) : 0;

            Log::info("Processando item", [
                'item_id' => $itemId,
                'quantidade_transferir' => $qtdTransferir,
                'nova_secao' => $request->nova_secao,
            ]);

            if ($item && $qtdTransferir > 0 && $qtdTransferir <= $item->quantidade) {
                Log::info("Item encontrado e quantidade válida", [
                    'item' => $item,
                    'quantidade_atual' => $item->quantidade,
                ]);

                // Verifica se já existe um item igual na seção destino
                $itemDestino = Itens_estoque::where('fk_secao', $request->nova_secao)
                    ->where('fk_produto', $item->fk_produto)
                    ->where('lote', $item->lote)
                    ->first();

                if ($itemDestino) {
                    Log::info("Item já existe na seção destino", ['item_destino' => $itemDestino]);
                    // Atualiza a quantidade na seção destino
                    $itemDestino->quantidade += $qtdTransferir;
                    $itemDestino->save();
                } else {
                    Log::info("Criando novo item na seção destino", ['item' => $item]);
                    // Cria um novo registro na seção destino
                    $novoItem = $item->replicate();
                    $novoItem->fk_secao = $request->nova_secao;
                    $novoItem->quantidade = $qtdTransferir;
                    $novoItem->save();
                }

                // Atualiza a quantidade na seção origem
                $item->quantidade -= $qtdTransferir;
                $item->save();

                Log::info("Quantidade atualizada na seção origem", ['item' => $item]);
            } else {
                Log::warning("Item não encontrado ou quantidade inválida", [
                    'item_id' => $itemId,
                    'quantidade_transferir' => $qtdTransferir,
                    'quantidade_disponivel' => $item ? $item->quantidade : null,
                ]);
            }
        }

        return redirect()->route('secoes.ver', ['unidade' => $unidadeId, 'secao' => $secaoId])
            ->with('success', 'Itens transferidos com sucesso!');
    }
    public function index($unidadeId)
    {
        $unidade = Unidade::with('secoes')->findOrFail($unidadeId);
        return view('secoes.index', compact('unidade'));
    }

    public function create($unidadeId)
    {
        $unidade = Unidade::findOrFail($unidadeId);
        return view('secoes.create', compact('unidade'));
    }

    public function store(Request $request, $unidadeId)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
        ]);
        Secao::create([
            'nome' => $request->nome,
            'fk_unidade' => $unidadeId,
        ]);
        return redirect()->route('secoes.index', $unidadeId)->with('success', 'Seção cadastrada com sucesso!');
    }

    public function edit($unidadeId, $id)
    {
        $unidade = Unidade::findOrFail($unidadeId);
        $secao = Secao::findOrFail($id);
        return view('secoes.edit', compact('unidade', 'secao'));
    }

    public function update(Request $request, $unidadeId, $id)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
        ]);
        $secao = Secao::findOrFail($id);
        $secao->update(['nome' => $request->nome]);
        return redirect()->route('secoes.index', $unidadeId)->with('success', 'Seção atualizada com sucesso!');
    }

    public function destroy($unidadeId, $id)
    {
        $secao = Secao::findOrFail($id);
        $secao->delete();
        return redirect()->route('secoes.index', $unidadeId)->with('success', 'Seção excluída com sucesso!');
    }
    public function vincularItensForm($secaoId)
    {
        $secao = Secao::findOrFail($secaoId);
        // Apenas itens que não estão vinculados a nenhuma seção
        $itens = Itens_estoque::where('unidade', $secao->fk_unidade)
            ->whereNull('fk_secao')
            ->get();
        return view('secoes.vincular_itens', compact('secao', 'itens'));
    }

    public function vincularItens(Request $request, $secaoId)
    {
        $secao = Secao::findOrFail($secaoId);
        $itens = $request->input('itens', []);
        $quantidades = $request->input('quantidades', []);
        $erros = [];

        foreach ($itens as $idx => $itemId) {
            $qtdSolicitada = isset($quantidades[$idx]) ? intval($quantidades[$idx]) : 0;
            if (!$itemId || $qtdSolicitada <= 0) {
                continue;
            }
            $item = Itens_estoque::find($itemId);
            if (!$item) {
                $erros[] = "Item (ID: {$itemId}) não encontrado.";
                continue;
            }
            if ($qtdSolicitada > $item->quantidade) {
                $erros[] = "Quantidade solicitada ({$qtdSolicitada}) maior que disponível ({$item->quantidade}) para o produto {$item->fk_produto}.";
                continue;
            }

            // Procura item igual na seção destino (mesmo produto e lote)
            $itemDestino = Itens_estoque::where('fk_secao', $secaoId)
                ->where('fk_produto', $item->fk_produto)
                ->where('lote', $item->lote)
                ->first();

            if ($itemDestino) {
                $itemDestino->quantidade += $qtdSolicitada;
                $itemDestino->save();
            } else {
                $novoItem = $item->replicate();
                $novoItem->fk_secao = $secaoId;
                $novoItem->quantidade = $qtdSolicitada;
                $novoItem->save();
            }

            // Deduz do item origem
            $item->quantidade -= $qtdSolicitada;
            $item->save();
        }

        $redirect = redirect()->route('secoes.vincular_itens_form', ['unidade' => $secao->fk_unidade, 'secao' => $secao->id]);
        if (!empty($erros)) {
            return $redirect->with('warning', implode('; ', $erros));
        }
        return $redirect->with('success', 'Itens vinculados à seção com sucesso!');
    }

    public function reordenarItens(Request $request, $unidadeId, $secaoId)
    {
        try {
            $ordens = $request->input('ordens', []);

            foreach ($ordens as $item) {
                $tipo = $item['tipo'] ?? null;
                $id = $item['id'] ?? null;
                $ordem = $item['ordem'] ?? null;

                if ($tipo === 'consumo' && $id) {
                    Itens_estoque::where('id', $id)->update(['ordem_pdf' => $ordem]);
                } elseif ($tipo === 'patrimonial' && $id) {
                    ItenPatrimonial::where('id', $id)->update(['ordem_pdf' => $ordem]);
                }
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('Erro ao reordenar itens', [$e]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
