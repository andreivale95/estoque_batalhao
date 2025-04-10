<?php

namespace App\Http\Controllers;



use App\Models\Itens_estoque;
use App\Models\Unidade;
use App\Models\Produto;
use App\Models\Kit;
use App\Models\KitProduto;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\HistoricoMovimentacao;
use Illuminate\Support\Facades\Auth;


class KitController extends Controller
{

    public function listarKits()
    {
        $kits = Kit::all();
        return view('kits.listarKits', compact('kits'));
    }

    public function toggleDisponibilidade($id)
    {
        $kit = Kit::findOrFail($id);
        $kit->disponivel = $kit->disponivel === 'S' ? 'N' : 'S';
        $kit->save();

        return redirect()->back()->with('success', 'Disponibilidade do kit atualizada com sucesso!');
    }


    public function formKit()
    {
        $unidades = Unidade::all();
        $produtos = Produto::all();
        return view('kits.formKit', compact('produtos', 'unidades'));
    }

    public function criarKit(Request $request)
    {
        //dd($request->all());
        $request->validate([
            'nome' => 'required|string|max:255',
            'produtos' => 'required|array',
            'unidade' => 'required|exists:unidades,id',
        ]);

        DB::beginTransaction();
        try {
            // Criar o kit
            $kit = Kit::create([
                'nome' => $request->nome,
                'fk_unidade' => $request->unidade,
            ]);

            foreach ($request->produtos as $index => $produtoId) {
                $quantidade = $request->quantidades[$index];

                // Verifica e altera estoque
                $estoque = Itens_estoque::where('fk_produto', $produtoId)
                    ->where('unidade', $request->unidade)
                    ->first();

                if (!$estoque || $estoque->quantidade < $quantidade) {
                    DB::rollBack();
                    return back()->with('warning', 'Quantidade insuficiente para o produto ID ' . $produtoId);
                }

                $estoque->quantidade -= $quantidade;
                $estoque->save();

                KitProduto::create([
                    'fk_kit' => $kit->id,
                    'fk_produto' => $produtoId,
                    'quantidade' => $quantidade,
                ]);

                HistoricoMovimentacao::create([
                    'fk_produto' => $produtoId,
                    'tipo_movimentacao' => 'saida',
                    'quantidade' => $quantidade,
                    'responsavel' => Auth::user()->nome,
                    'observacao' => 'Saida para kit',
                    'data_movimentacao' => now(),
                    'fk_unidade' => $request->unidade,
                ]);
            }

            DB::commit();
            return redirect()->route('kits.listar')->with('success', 'Kit criado e produtos removidos do estoque com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar kit: ', [$e]);
            return back()->with('warning', 'Erro ao criar kit.');
        }
    }

    public function show(Kit $kit)
    {
        return view('kits.show', compact('kit'));
    }

    public function edit(Kit $kit)
    {
        $produtos = Produto::all();
        return view('kits.edit', compact('kit', 'produtos'));
    }

    public function update(Request $request, Kit $kit)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'produtos' => 'required|array',
            'quantidades' => 'required|array',
        ]);

        $kit->update([
            'nome' => $request->nome,
            'descricao' => $request->descricao,
        ]);

        $kit->produtos()->detach();
        foreach ($request->produtos as $index => $produto_id) {
            $kit->produtos()->attach($produto_id, [
                'quantidade' => $request->quantidades[$index]
            ]);
        }

        return redirect()->route('kits.index')->with('success', 'Kit atualizado com sucesso!');
    }

    public function desfazerKit($kitId)
    {
        DB::beginTransaction();
        try {
            $kit = Kit::findOrFail($kitId); // precisamos disso por causa da unidade

            $itens = KitProduto::where('fk_kit', $kitId)->get();

            foreach ($itens as $item) {
                // Devolve os itens ao estoque
                $estoque = Itens_estoque::where('fk_produto', $item->fk_produto)
                    ->where('unidade', $kit->fk_unidade)
                    ->first();

                if ($estoque) {
                    $estoque->quantidade += $item->quantidade;
                    $estoque->save();
                } else {
                    Itens_estoque::create([
                        'fk_produto' => $item->fk_produto,
                        'unidade' => $kit->fk_unidade,
                        'quantidade' => $item->quantidade,
                        'data_entrada' => now(),
                    ]);
                }

                HistoricoMovimentacao::create([
                    'fk_produto' => $item->fk_produto,
                    'tipo_movimentacao' => 'entrada',
                    'quantidade' => $item->quantidade,
                    'responsavel' => Auth::user()->nome,
                    'observacao' => 'Devolução de kit',
                    'data_movimentacao' => now(),
                    'fk_unidade' => $kit->fk_unidade,
                ]);
            }

            // Apaga os itens relacionados
            KitProduto::where('fk_kit', $kitId)->delete();

            // Agora pode excluir o kit
            $kit->delete();

            DB::commit();
            return redirect()->route('kits.listar')->with('success', 'Kit desfeito e produtos devolvidos ao estoque!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao desfazer kit: ', [$e]);
            return back()->with('warning', 'Erro ao desfazer kit.');
        }
    }

    public function entregarKit($id)
    {
        $kit = Kit::with('produtos')->findOrFail($id);

        // Aqui você pode registrar a saída, por exemplo:
        HistoricoMovimentacao::create([
            'fk_produto' => null,
            'tipo_movimentacao' => 'saida_kit',
            'quantidade' => 1,
            'responsavel' => Auth::user()->nome,
            'observacao' => "Saída do kit: {$kit->nome}",
            'data_movimentacao' => now(),
            'unidade_origem' => $kit->unidade_id,
            'unidade_destino' => null,
        ]);


        $kit->update(['entregue' => 'sim']);



        return redirect()->back()->with('success', 'Saída do kit registrada com sucesso!');
    }






}
