<?php

namespace App\Http\Controllers;



use App\Models\Itens_estoque;
use App\Models\Unidade;
use App\Models\Produto;
use App\Models\Kit;
use App\Models\KitProduto;
use App\Models\Estoque;
use Illuminate\Support\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\HistoricoMovimentacao;
use Illuminate\Support\Facades\Auth;


class MovimentacaoController extends Controller
{

    public function index(Request $request)
    {
        $query = HistoricoMovimentacao::with(['produto', 'origem', 'destino'])
            ->where(function ($query) {
                $query->where('unidade_origem', Auth::user()->fk_unidade)
                    ->orWhereNull('unidade_origem');
            });


        if ($request->filled('produto')) {
            $query->where('fk_produto', $request->produto);
        }

        if ($request->filled('tipo')) {
            $query->where('tipo_movimentacao', $request->tipo);
        }

        if ($request->filled('data_inicio')) {
            $query->whereDate('data_movimentacao', '>=', $request->data_inicio);
        }

        if ($request->filled('data_fim')) {
            $query->whereDate('data_movimentacao', '<=', $request->data_fim);
        }

        if ($request->filled('responsavel')) {
            $query->where('responsavel', 'like', '%' . $request->responsavel . '%');
        }
        if ($request->filled('militar')) {
            $query->where('militar', 'like', '%' . $request->militar . '%');
        }

        if ($request->filled('fonte')) {
            $query->where('fonte', 'like', '%' . $request->fonte . '%');
        }

        if ($request->filled('estoque')) {
            $query->whereHas('unidade', function ($q) use ($request) {
                $q->where('nome', 'like', '%' . $request->estoque . '%');
            });
        }

        if ($request->filled('sei')) {
            $query->where('sei', 'like', '%' . $request->sei . '%');
        }


        $movimentacoes = $query->orderByDesc('data_movimentacao')->paginate(20);

        $produtos = Produto::all();

        return view('movimentacoes.index', compact('movimentacoes', 'produtos'));
    }


    public function desfazer($id)
    {
        try {
            $movimentacao = HistoricoMovimentacao::findOrFail($id);

            // Não permitir desfazer se já for uma reversão
            if (str_contains($movimentacao->observacao, 'Desfeito movimentação')) {
                return back()->with('error', 'Não é possível desfazer uma movimentação já desfeita.');
            }

            // Não permitir desfazer se já existe uma reversão para esta movimentação
            $jaDesfeita = HistoricoMovimentacao::where('movimentacao_origem_id', $movimentacao->id)->exists();
            if ($jaDesfeita) {
                return back()->with('error', 'Esta movimentação já foi desfeita anteriormente.');
            }

            // Verificar o tipo de movimentação
            $tipoMovimentacao = $movimentacao->tipo_movimentacao;

            // Buscar o item no estoque
            $itemEstoque = Itens_estoque::where('fk_produto', $movimentacao->fk_produto)
                ->where('unidade', $movimentacao->fk_unidade)
                ->first();

            if (!$itemEstoque) {
                return back()->with('error', 'Produto não encontrado no estoque.');
            }

            // Se for uma saída, devolve a quantidade ao estoque
            if ($tipoMovimentacao === 'saida') {
                $itemEstoque->quantidade += $movimentacao->quantidade;
            }

            if ($tipoMovimentacao === 'saida_manual_multipla') {
                $itemEstoque->quantidade += $movimentacao->quantidade;
            }


            // Se for uma entrada, retira a quantidade do estoque
            if ($tipoMovimentacao === 'entrada') {
                $itemEstoque->quantidade -= $movimentacao->quantidade;
            }

            // Se for uma transferência
            if ($tipoMovimentacao === 'transferencia') {
                if ($movimentacao->origem) {
                    $itemEstoqueOrigem = Itens_estoque::where('fk_produto', $movimentacao->fk_produto)
                        ->where('unidade', $movimentacao->origem->id)
                        ->first();
                    if ($itemEstoqueOrigem) {
                        $itemEstoqueOrigem->quantidade += $movimentacao->quantidade;
                        $itemEstoqueOrigem->save();
                    }
                }

                if ($movimentacao->destino) {
                    $itemEstoqueDestino = Itens_estoque::where('fk_produto', $movimentacao->fk_produto)
                        ->where('unidade', $movimentacao->destino->id)
                        ->first();
                    if ($itemEstoqueDestino) {
                        $itemEstoqueDestino->quantidade -= $movimentacao->quantidade;
                        $itemEstoqueDestino->save();
                    }
                }
            }

            // Salvar as alterações no estoque
            $itemEstoque->save();

            // Criar uma nova movimentação de reversão no histórico
            HistoricoMovimentacao::create([
                'fk_produto' => $movimentacao->fk_produto,
                'tipo_movimentacao' => $tipoMovimentacao === 'entrada' ? 'saida' : 'entrada', // Inverter o tipo
                'quantidade' => $movimentacao->quantidade,
                'valor_total' => $movimentacao->valor_total,
                'valor_unitario' => $movimentacao->valor_unitario,
                'responsavel' => Auth::user()->nome,
                'observacao' => 'Desfeito movimentação de ' . $tipoMovimentacao,
                'data_movimentacao' => Carbon::now(),
                'fk_unidade' => $movimentacao->fk_unidade,
                'fonte' => $movimentacao->fonte,
                'data_trp' => $movimentacao->data_trp,
                'sei' => $movimentacao->sei,
                'fornecedor' => $movimentacao->fornecedor,
                'nota_fiscal' => $movimentacao->nota_fiscal,
                'movimentacao_origem_id' => $movimentacao->id,
            ]);

            return back()->with('success', 'Movimentação desfeita com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao desfazer a movimentação', ['exception' => $e->getMessage()]);
            return back()->with('error', 'Houve um erro ao desfazer a movimentação.');
        }
    }
}
