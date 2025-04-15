<?php

namespace App\Http\Controllers;


use App\Models\HistoricoMovimentacao;
use App\Models\Itens_estoque;
use App\Models\TipoProduto;
use App\Models\Unidade;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use App\Models\EfetivoMilitar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;


class EstoqueController extends Controller
{
    public function listarEstoque(Request $request)
    {
        $request['unidade'] = empty($request['unidade']) ? '' : $request->get('unidade');
        $request['tipoproduto'] = empty($request['tipoproduto']) ? '' : $request->get('tipoproduto');
        $request['nome'] = empty($request['nome']) ? '' : $request->get('nome');

        $tipoprodutos = TipoProduto::all();
        $unidades = Unidade::all();

        try {
            // Query base com filtros aplicados
            $query = Itens_estoque::query()
                ->with('produto')
                ->when(Auth::user()->fk_unidade != 14, function (Builder $query) {
                    return $query->where('unidade', Auth::user()->fk_unidade);
                })
                ->when(Auth::user()->fk_unidade == 14 && filled(request()->get('unidade')), function (Builder $query) use ($request) {
                    return $query->where('unidade', $request->get('unidade'));
                })
                ->when(filled($request->get('nome')), function (Builder $query) use ($request) {
                    return $query->whereHas('produto', function ($q) use ($request) {
                        $q->where('nome', 'like', '%' . $request->get('nome') . '%');
                    });
                })
                ->when(filled($request->get('tipoproduto')), function (Builder $query) use ($request) {
                    return $query->whereHas('produto.tipoProduto', function ($q) use ($request) {
                        $q->where('id', $request->get('tipoproduto'));
                    });
                });

            // Clona a query para calcular o total geral com base nos filtros
            $totalGeral = $query->get()->sum(function ($item) {
                return $item->quantidade * ($item->produto->valor ?? 0);
            });

            // Paginação com os mesmos filtros
            $itens_estoque = (clone $query)->paginate(10);

            return view('estoque/listarEstoque', compact('itens_estoque', 'unidades', 'tipoprodutos', 'totalGeral'));
        } catch (\Exception $e) {
            Log::error('Erro ao consultar estoque', [$e]);
            return back()->with('warning', 'Houve um erro ao consultar estoque.');
        }
    }

    public function transferir(Request $request)
    {
        //   dd($request->all());
        $request->validate([
            'estoque_id' => 'required|exists:itens_estoque,id',
            'nova_unidade' => 'required|exists:unidades,id',
            'unidade_atual' => 'required|exists:unidades,id',
            'quantidade' => 'required|integer|min:1',
            'observacao' => 'nullable|string|max:255',
        ]);

        $itemAtual = Itens_estoque::findOrFail($request->estoque_id);
        $unidadeAtual = $request->input('unidade_atual');
        $novaUnidade = $request->input('nova_unidade');


        if ($request->quantidade > $itemAtual->quantidade) {
            return back()->with('warning', 'A quantidade informada excede o estoque atual.');
        }

        // Subtrai do estoque atual
        $itemAtual->quantidade -= $request->quantidade;
        $itemAtual->save();

        // Verifica se já existe o produto na nova unidade
        $itemNovo = Itens_estoque::where('fk_produto', $itemAtual->fk_produto)
            ->where('unidade', $request->nova_unidade)
            ->first();

        if ($itemNovo) {
            $itemNovo->quantidade += $request->quantidade;
            $itemNovo->save();
        } else {
            Itens_estoque::create([
                'fk_produto' => $itemAtual->fk_produto,
                'quantidade' => $request->quantidade,
                'unidade' => $request->nova_unidade,
            ]);
        }

        // Registrar no histórico
        HistoricoMovimentacao::create([
            'fk_produto' => $itemAtual->fk_produto,
            'tipo_movimentacao' => 'transferencia',
            'quantidade' => $request->quantidade,
            'responsavel' => Auth::user()->nome,
            'observacao' => $request->observacao ?? 'Transferência entre Unidades',
            'data_movimentacao' => now(),
            'unidade_origem' => $unidadeAtual,
            'unidade_destino' => $novaUnidade,
        ]);

        return redirect()->route('estoque.listar')->with('success', 'Produto transferido com sucesso!');
    }
    public function entradaEstoque(Request $request)
    {


        try {


            // Validação dos dados
            $request->validate([
                'quantidade' => 'required|integer|min:1',
                'data_entrada' => 'required|date',
                'fk_produto' => 'required|exists:produtos,id',
            ]);

            // Verifica se o produto já existe no estoque
            $itemEstoque = Itens_estoque::where('fk_produto', $request->fk_produto)->where('unidade', $request->unidade)->first();
            $dataEntrada = Carbon::parse($request->data_entrada);

            if ($itemEstoque) {
                // Se já existe, apenas soma a quantidade
                $itemEstoque->quantidade += $request->quantidade;
                $itemEstoque->save();
            } else {
                // Se não existe, cria um novo registro
                Itens_estoque::create([
                    'quantidade' => $request->quantidade,
                    'unidade' => $request->unidade,
                    'data_entrada' => $dataEntrada,
                    'fk_produto' => $request->fk_produto,
                    'lote' => $request->lote,
                    'fornecedor' => $request->fornecedor,
                    'nota_fiscal' => $request->nota_fiscal,
                    'observacao' => $request->observacao ?? 'Entrada de novo produto',
                ])->save();

            }

            HistoricoMovimentacao::create([
                'fk_produto' => $request->fk_produto,
                'tipo_movimentacao' => 'entrada',
                'quantidade' => $request->quantidade,
                'responsavel' => Auth::user()->nome,
                'observacao' => 'Entrada de novo produto',
                'data_movimentacao' => $dataEntrada,
                'fk_unidade' => $request->unidade,
            ])->save();


            return redirect()->route('estoque.listar')->with('success', 'Produto cadastrado no estoque com sucesso!');

        } catch (\Exception $e) {
            Log::error('Erro ao dar entrada no Estoque', [$e]);
            return back()->with('warning', 'Houve um erro ao dar entrada no Estoque.');
        }
    }
    public function entradaProdutoEstoque(Request $request)
    {
        // dd($request->all());
        try {
            // Validação dos dados
            $request->validate([
                'quantidade' => 'required|integer|min:1',
                'data_entrada' => 'required|date',
                'fk_produto' => 'required|exists:produtos,id',
            ]);

            // Verifica se o produto já existe no estoque
            $itemEstoque = Itens_estoque::where('fk_produto', $request->fk_produto)->where('unidade', $request->unidade)->first();

            if ($itemEstoque) {
                // Produto já existe — interrompe e avisa
                return redirect()->back()->with('warning', 'Este produto já foi cadastrado no estoque.');
            }

            // Se não existe, cria um novo registro
            Itens_estoque::create([
                'unidade' => $request->unidade,
                'quantidade' => $request->quantidade,
                'data_entrada' => $request->data_entrada,
                'fk_produto' => $request->fk_produto,
                'lote' => $request->lote,
                'fornecedor' => $request->fornecedor,
                'nota_fiscal' => $request->nota_fiscal,
                'observacao' => $request->observacao ?? 'Entrada de novo produto',
            ]);

            // Registrar no histórico
            HistoricoMovimentacao::create([
                'fk_produto' => $request->fk_produto,
                'tipo_movimentacao' => 'entrada',
                'quantidade' => $request->quantidade,
                'responsavel' => Auth::user()->nome,
                'observacao' => 'Entrada de novo produto',
                'data_movimentacao' => now(),
                'fk_unidade' => $request->unidade,
            ]);

            return redirect()->route('estoque.listar')->with('success', 'Produto cadastrado no estoque com sucesso!');
        } catch (Exception $e) {
            Log::error('Erro ao dar entrada no Estoque', [$e]);
            return back()->with('warning', 'Houve um erro ao dar entrada no Estoque.');
        }
    }
    public function saidaEstoque(Request $request)
    {
        try {
            // Validação dos dados
            $request->validate([
                'quantidade' => 'required|integer|min:1',

                'fk_produto' => 'required|exists:produtos,id',
            ]);

            // Verifica se o produto existe no estoque
            $itemEstoque = Itens_estoque::where('fk_produto', $request->fk_produto)->where('unidade', $request->unidade)->first();
            $dataSaida = Carbon::parse($request->data_saida);



            if ($itemEstoque) {
                // Verifica se há estoque suficiente para a saída
                if ($itemEstoque->quantidade >= $request->quantidade) {
                    $itemEstoque->quantidade -= $request->quantidade;


                    $itemEstoque->save();
                    $militar = EfetivoMilitar::where('id', $request->militar)->first();


                    HistoricoMovimentacao::create([
                        'fk_produto' => $request->fk_produto,
                        'tipo_movimentacao' => 'saida',
                        'quantidade' => $request->quantidade,
                        'responsavel' => Auth::user()->nome,
                        'observacao' => $request->observacao ?? 'Saída de produto',
                        'data_movimentacao' => $dataSaida,
                        'fk_unidade' => $request->unidade,
                        'militar' => $militar->nome,
                    ]);


                    return redirect()->route('estoque.listar')->with('success', 'Saída de produto registrada com sucesso!');
                } else {
                    return back()->with('warning', 'Estoque insuficiente para essa saída.');
                }
            } else {
                return back()->with('warning', 'Produto não encontrado no estoque.');
            }
        } catch (\Exception $e) {
            Log::error('Erro ao registrar saída no Estoque', [$e]);
            return back()->with('warning', 'Houve um erro ao registrar a saída do Estoque.');
        }
    }
    public function formEntrada(Request $request, $id)
    {

        try {


            $produto = Itens_estoque::select('fk_produto', 'unidade')->where('id', $id)->first();

            return view('estoque/estoque_form_entrada', compact('produto'));



        } catch (Exception $e) {
            Log::error('Error ao consultar formulario', [$e]);
            return back()->with('warning', 'Houve um erro ao abrir Formulário');
        }
    }
    public function formSaida(Request $request, $id)
    {

        try {


            $produto = Itens_estoque::select('fk_produto', 'unidade')->where('id', $id)->first();
            $militares = EfetivoMilitar::all();

            return view('estoque/estoque_form_saida', compact('produto','militares'));



        } catch (Exception $e) {
            Log::error('Error ao consultar formulario', [$e]);
            return back()->with('warning', 'Houve um erro ao abrir Formulário');
        }
    }
}
