<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Secao;
use App\Models\Unidade;
use App\Models\Itens_estoque;

class SecaoController extends Controller
{
    public function transferirLoteForm($unidadeId, $secaoId)
    {
        $secao = Secao::findOrFail($secaoId);
        $itens = Itens_estoque::where('fk_secao', $secaoId)->get();
        $outrasSecoes = Secao::where('fk_unidade', $unidadeId)->where('id', '!=', $secaoId)->get();
        return view('secoes.transferir_lote', compact('secao', 'itens', 'outrasSecoes'));
    }

    public function transferirLote(Request $request, $unidadeId, $secaoId)
    {
        $request->validate([
            'item_id' => 'required|array',
            'nova_secao' => 'required|integer',
        ]);
        $quantidades = $request->input('quantidade_transferir', []);
        foreach ($request->item_id as $idx => $itemId) {
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
                $item->save();
            }
        }
        return redirect()->route('secoes.ver', ['unidade' => $unidadeId, 'secao' => $secaoId])->with('success', 'Itens transferidos com sucesso!');
    }
    public function ver($unidadeId, $secaoId)
    {
        $secao = Secao::with(['unidade'])->findOrFail($secaoId);
        $itens = Itens_estoque::where('fk_secao', $secaoId)->get();
        $outrasSecoes = Secao::where('fk_unidade', $unidadeId)->where('id', '!=', $secaoId)->get();
        return view('secoes.ver', compact('secao', 'itens', 'outrasSecoes', 'unidadeId'));
    }

    public function transferirItens(Request $request, $unidadeId, $secaoId)
    {
        $request->validate([
            'item_id' => 'required|array',
            'nova_secao' => 'required|integer',
        ]);
        $quantidades = $request->input('quantidade_transferir', []);
        foreach ($request->item_id as $itemId) {
            $item = Itens_estoque::find($itemId);
            $qtdTransferir = isset($quantidades[$itemId]) ? intval($quantidades[$itemId]) : 0;
            if ($item && $qtdTransferir > 0 && $qtdTransferir <= $item->quantidade) {
                // Se já existe um item igual na seção destino, soma; senão, cria novo registro
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
                // Atualiza quantidade na seção origem
                $item->quantidade -= $qtdTransferir;
                $item->save();
            }
        }
        return redirect()->route('secoes.ver', ['unidade' => $unidadeId, 'secao' => $secaoId])->with('success', 'Itens transferidos com sucesso!');
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
}
