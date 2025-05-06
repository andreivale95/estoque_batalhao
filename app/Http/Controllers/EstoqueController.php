<?php

namespace App\Http\Controllers;


use App\Models\Categoria;
use App\Models\HistoricoMovimentacao;
use App\Models\Itens_estoque;
use App\Models\Unidade;
use App\Models\Produto;
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
        $request['categoria'] = empty($request['categoria']) ? '' : $request->get('categoria');
        $request['nome'] = empty($request['nome']) ? '' : $request->get('nome');

        $categorias = Categoria::all();
        $unidades = Unidade::all();
        $militares = EfetivoMilitar::all();

        try {
            // Query base com filtros aplicados
            $query = Itens_estoque::query()
                ->with('produto')
                ->when(filled(request()->get('unidade')), function (Builder $query) use ($request) {
                    return $query->where('unidade', $request->get('unidade'));
                })
                ->when(filled($request->get('nome')), function (Builder $query) use ($request) {
                    return $query->whereHas('produto', function ($q) use ($request) {
                        $q->where('nome', 'like', '%' . $request->get('nome') . '%');
                    });
                })
                ->when(filled($request->get('categoria')), function (Builder $query) use ($request) {
                    return $query->whereHas('produto.categoria', function ($q) use ($request) {
                        $q->where('id', $request->get('categoria'));
                    });
                });

            // Clona a query para calcular o total geral com base nos filtros
            $totalGeral = $query->get()->sum(function ($item) {
                return $item->quantidade * ($item->produto->valor ?? 0);
            });

            // Paginação com os mesmos filtros
            $itens_estoque = (clone $query)->paginate(10);

            return view('estoque/listarEstoque', compact('itens_estoque', 'unidades', 'categorias', 'totalGeral', 'militares', ));
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
            'fk_unidade' => $novaUnidade,
        ]);


        return redirect()->route('estoque.listar', [
            'nome' => '',
            'categoria' => '',
            'unidade' => Auth::user()->fk_unidade
        ])->with('success', 'Produto transferido com sucesso!');

    }
    public function entradaEstoque(Request $request)
    {
        try {
            // Verifica se o usuário tem permissão
            if (Auth::user()->fk_unidade != $request->unidade) {
                return redirect()->back()->with('error', 'Você não tem permissão para movimentar produtos de outra unidade.');
            }

            $valorBr = $request->get('valor'); // "250000"
            $valorFinal = ((float) $valorBr) / 100; // resultado: 250.00

            //dd($valorFinal);

            // Validação dos dados recebidos
            $request->validate([
                'quantidade' => 'required|integer|min:1',
                'data_entrada' => 'required|date',
                'fk_produto' => 'required|exists:produtos,id',

            ]);

            $dataEntrada = Carbon::parse($request->data_entrada);

            // Busca o item no estoque
            $itemEstoque = Itens_estoque::where('fk_produto', $request->fk_produto)
                ->where('unidade', $request->unidade)
                ->first();

            if ($itemEstoque) {
                // Calcula a nova média ponderada
                $quantidadeAtual = $itemEstoque->quantidade;
                $valorAtual = $itemEstoque->produto->valor ?? 0;

                $novaQuantidade = $quantidadeAtual + $request->quantidade;
                $novoValorMedio = $novaQuantidade > 0
                    ? (($quantidadeAtual * $valorAtual) + ($request->quantidade * $valorFinal)) / $novaQuantidade
                    : $valorFinal;

                // Atualiza o estoque
                $itemEstoque->quantidade = $novaQuantidade;

                $itemEstoque->save();

                // Atualiza o valor médio do produto
                $produto = $itemEstoque->produto;
                $produto->valor = $novoValorMedio;
                $produto->save();
            }

            // Atualiza valor do produto com o valor da primeira entrada
            $produto = Produto::find($request->fk_produto);
            $produto->valor = $novoValorMedio;
            $produto->save();


            // Cria histórico de movimentação
            HistoricoMovimentacao::create([
                'fk_produto' => $request->fk_produto,
                'tipo_movimentacao' => 'entrada',
                'quantidade' => $request->quantidade,
                'valor_entrada' => $valorFinal * $request->quantidade,
                'responsavel' => Auth::user()->nome,
                'observacao' => $request->observacao ?? 'Entrada no estoque',
                'data_movimentacao' => $dataEntrada,
                'fk_unidade' => $request->unidade,
                'fonte' => $request->fonte,
                'data_trp' => $request->data_trp,
                'sei' => $request->sei,
                'fornecedor' => $request->fornecedor,
                'nota_fiscal' => $request->nota_fiscal,
            ]);

            return redirect()->route('estoque.listar', [
                'nome' => '',
                'categoria' => '',
                'unidade' => Auth::user()->fk_unidade
            ])->with('success', 'Produto atualizado no estoque com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao dar entrada no Estoque', ['exception' => $e->getMessage()]);
            return back()->with('warning', 'Houve um erro ao dar entrada no Estoque.');
        }
    }
    public function entradaProdutoEstoque(Request $request)
    {  //dd($request->all());

        try {
            if (Auth::user()->fk_unidade != $request->unidade) {
                return redirect()->back()->with('error', 'Você não tem permissão para movimentar produtos de outra unidade.');
            }
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
                'fonte' => $request->fonte,
                'data_trp' => $request->data_trp,
                'sei' => $request->sei,
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
                'fonte' => $request->fonte,
                'data_trp' => $request->data_trp,
                'sei' => $request->sei,
                'fornecedor' => $request->fornecedor,
                'nota_fiscal' => $request->nota_fiscal,

            ]);

            return redirect()->route('estoque.listar', [
                'nome' => '',
                'categoria' => '',
                'unidade' => Auth::user()->fk_unidade
            ])->with('success', 'Produto cadastrado no estoque com sucesso!');
        } catch (Exception $e) {
            Log::error('Erro ao dar entrada no Estoque', [$e]);
            return back()->with('warning', 'Houve um erro ao dar entrada no Estoque.');
        }
    }
    public function saidaEstoque(Request $request)
    {
        try {
            if (Auth::user()->fk_unidade != $request->unidade) {
                return redirect()->back()->with('error', 'Você não tem permissão para movimentar produtos de outra unidade.');
            }

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
                        'setor' => $request->setor ?? 'Setor não informado',
                    ]);


                    return redirect()->route('estoque.listar', [
                        'nome' => '',
                        'categoria' => '',
                        'unidade' => Auth::user()->fk_unidade
                    ])->with('success', 'Saída realizada com sucesso.');

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

            return view('estoque/estoque_form_saida', compact('produto', 'militares'));



        } catch (Exception $e) {
            Log::error('Error ao consultar formulario', [$e]);
            return back()->with('warning', 'Houve um erro ao abrir Formulário');
        }
    }
    public function saidaMultiplos(Request $request)
    {
        try {
            // Valida os dados recebidos
            $request->validate([
                'militar' => 'required|exists:efetivo_militar,id',
                'data_saida' => 'required|date',
                'produtos' => 'required|array',
            ]);

            $produtos = $request->input('produtos', []);
            $militarId = $request->input('militar');
            $dataSaida = Carbon::parse($request->data_saida);
            $obsGeral = $request->input('observacao');

            // Busca o nome do militar pelo ID
            $militar = EfetivoMilitar::findOrFail($militarId);
            $destinatario = $militar->nome;

            foreach ($produtos as $estoqueId => $dados) {
                // Verifica se a quantidade foi informada
                if (empty($dados['quantidade'])) {
                    continue;
                }
                // Verifica se o produto existe no estoque


                $quantidadeSolicitada = (int) $dados['quantidade'];
                $estoque = Itens_estoque::where('id', $estoqueId)
                    ->where('unidade', Auth::user()->fk_unidade)
                    ->firstOrFail();


                if ($quantidadeSolicitada > 0 && $quantidadeSolicitada <= $estoque->quantidade) {
                    // Atualiza o estoque
                    $estoque->quantidade -= $quantidadeSolicitada;
                    $estoque->save();

                    // Registra no histórico de movimentação
                    HistoricoMovimentacao::create([
                        'fk_produto' => $estoque->fk_produto,
                        'tipo_movimentacao' => 'saida_manual_multipla',
                        'quantidade' => $quantidadeSolicitada,
                        'responsavel' => Auth::user()->nome,
                        'observacao' => "Saída para {$destinatario}. Obs: {$obsGeral}",
                        'data_movimentacao' => $dataSaida,
                        'fk_unidade' => Auth::user()->fk_unidade,
                        'militar' => $destinatario,
                    ]);
                } else {
                    // Se a quantidade solicitada for maior que a disponível, retorna um erro
                    return back()->with('warning', 'Quantidade solicitada maior que a disponível no estoque.');
                }
            }

            return redirect()->route('estoque.listar', [
                'nome' => '',
                'categoria' => '',
                'unidade' => Auth::user()->fk_unidade
            ])->with('success', 'Saída realizada com sucesso.');

        } catch (\Exception $e) {
            Log::error('Erro ao realizar saída múltipla', [$e]);
            return back()->with('warning', 'Houve um erro ao realizar a saída múltipla.');
        }
    }
}
