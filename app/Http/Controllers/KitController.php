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








}
