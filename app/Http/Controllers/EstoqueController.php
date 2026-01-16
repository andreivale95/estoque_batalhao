<?php

namespace App\Http\Controllers;


use App\Models\Categoria;
use App\Models\HistoricoMovimentacao;
use App\Models\Itens_estoque;
use App\Models\Unidade;
use App\Models\Produto;
use App\Models\Secao;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use App\Models\EfetivoMilitar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class EstoqueController extends Controller
{
    public function listarEstoque(Request $request)
    {
        // Lógica de redirecionamento:
        // 1. Se vem de 'from=parametros' (menu Parâmetros), mostra sem filtro de unidade específica
        // 2. Se acessa sem parâmetros de filtro (direto na URL), redireciona com a unidade do usuário
        $fromParametros = $request->query('from') === 'parametros';
        $hasNome = $request->query('nome') !== null;
        $hasCategoria = $request->query('categoria') !== null;
        $hasUnidade = $request->query('unidade') !== null;

        // Se não vem de parâmetros e não tem filtros, redireciona com a unidade do usuário
        if (!$fromParametros && !$hasNome && !$hasCategoria && !$hasUnidade) {
            return redirect()->route('estoque.listar', [
                'nome' => '',
                'categoria' => '',
                'unidade' => Auth::user()->fk_unidade
            ]);
        }

        $request['unidade'] = empty($request['unidade']) ? '' : $request->get('unidade');
        $request['categoria'] = empty($request['categoria']) ? '' : $request->get('categoria');
        $request['nome'] = empty($request['nome']) ? '' : $request->get('nome');

        $categorias = Categoria::all();
        $unidades = Unidade::all();
        $militares = EfetivoMilitar::all();

        try {
            Log::info('Iniciando consulta de estoque', [
                'filtros' => $request->all()
            ]);

            // Query base com filtros aplicados
            $query = Itens_estoque::query()
                ->select(
                    'produtos.id',
                    'produtos.nome',
                    'produtos.patrimonio',
                    DB::raw('MAX(itens_estoque.valor_unitario) as valor'),
                    'itens_estoque.unidade',
                    'unidades.nome as unidade_nome',
                    'produtos.fk_categoria as categoria_id',
                    DB::raw('SUM(itens_estoque.quantidade) as quantidade_total')
                )
                ->join('produtos', 'produtos.id', '=', 'itens_estoque.fk_produto')
                ->join('unidades', 'unidades.id', '=', 'itens_estoque.unidade')
                ->leftJoin('categorias', 'categorias.id', '=', 'produtos.fk_categoria')
                ->when(filled(request()->get('unidade')), function (Builder $query) use ($request) {
                    return $query->where('itens_estoque.unidade', $request->get('unidade'));
                })
                ->when(filled($request->get('nome')), function (Builder $query) use ($request) {
                    return $query->where('produtos.nome', 'like', '%' . $request->get('nome') . '%');
                })
                ->when(filled($request->get('patrimonio')), function (Builder $query) use ($request) {
                    return $query->where('produtos.patrimonio', 'like', '%' . $request->get('patrimonio') . '%');
                })
                ->when(filled($request->get('categoria')), function (Builder $query) use ($request) {
                    return $query->where('produtos.fk_categoria', $request->get('categoria'));
                })
                ->groupBy(
                    'produtos.id',
                    'produtos.nome',
                    'produtos.patrimonio',
                    'itens_estoque.unidade',
                    'unidades.nome',
                    'produtos.fk_categoria'
                );

            // Executar a query principal
            $itens_estoque = $query->paginate(10);

            // Calcular o total geral
            $totalGeral = $itens_estoque->sum(function ($item) {
                return $item->quantidade_total * ($item->valor ?? 0);
            });

            return view('estoque/listarEstoque', compact('itens_estoque', 'unidades', 'categorias', 'totalGeral', 'militares',));
        } catch (\Exception $e) {
            Log::error('Erro ao consultar estoque', [
                'erro' => $e->getMessage(),
                'linha' => $e->getLine(),
                'arquivo' => $e->getFile(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('warning', 'Houve um erro ao consultar estoque. Erro: ' . $e->getMessage());
        }
    }
    public function transferir(Request $request)
    {
        $tipoTransferencia = $request->input('tipo_transferencia');

        // Rota para novo tipo: transferências entre seções e containers
        if ($tipoTransferencia) {
            return $this->transferirInterno($request);
        }

        // Rota antiga: transferências entre unidades (mantém compatibilidade)
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

    /**
     * Transferência interna: seção para seção, seção para container, container para seção
     */
    private function transferirInterno(Request $request)
    {
        try {
            $tipoTransferencia = $request->input('tipo_transferencia');

            $request->validate([
                'fk_produto' => 'required|exists:produtos,id',
                'tipo_transferencia' => 'required|in:secao_para_secao,secao_para_container,container_para_secao',
                'quantidade' => 'required|integer|min:1',
                'observacao' => 'nullable|string',
            ]);

            $fkProduto = $request->input('fk_produto');
            $quantidade = $request->input('quantidade');

            switch ($tipoTransferencia) {
                case 'secao_para_secao':
                    return $this->transferirSecaoParaSecao($request, $fkProduto, $quantidade);
                    break;
                case 'secao_para_container':
                    return $this->transferirSecaoParaContainer($request, $fkProduto, $quantidade);
                    break;
                case 'container_para_secao':
                    return $this->transferirContainerParaSecao($request, $fkProduto, $quantidade);
                    break;
            }
        } catch (Exception $e) {
            Log::error('Erro ao transferir item', ['exception' => $e->getMessage()]);
            return back()->with('error', 'Erro ao transferir: ' . $e->getMessage());
        }
    }

    private function transferirSecaoParaSecao(Request $request, $fkProduto, $quantidade)
    {
        $secaoOrigem = $request->input('secao_origem');
        $secaoDestino = $request->input('secao_destino');

        if ($secaoOrigem == $secaoDestino) {
            return back()->with('warning', 'A seção de origem deve ser diferente da destino.');
        }

        // Busca item na seção origem (solto, sem container pai)
        $itemOrigem = Itens_estoque::where('fk_produto', $fkProduto)
            ->where('fk_secao', $secaoOrigem)
            ->whereNull('fk_item_pai')
            ->first();

        if (!$itemOrigem || $itemOrigem->quantidade < $quantidade) {
            return back()->with('warning', 'Quantidade insuficiente na seção de origem.');
        }

        DB::beginTransaction();
        try {
            // Deduz da origem
            $itemOrigem->quantidade -= $quantidade;
            if ($itemOrigem->quantidade == 0) {
                $itemOrigem->delete();
            } else {
                $itemOrigem->save();
            }

            // Busca ou cria item na seção destino
            $itemDestino = Itens_estoque::where('fk_produto', $fkProduto)
                ->where('fk_secao', $secaoDestino)
                ->whereNull('fk_item_pai')
                ->first();

            if ($itemDestino) {
                $itemDestino->quantidade += $quantidade;
                $itemDestino->save();
            } else {
                Itens_estoque::create([
                    'fk_produto' => $fkProduto,
                    'fk_secao' => $secaoDestino,
                    'unidade' => Auth::user()->fk_unidade,
                    'quantidade' => $quantidade,
                ]);
            }

            DB::commit();
            return redirect()->route('estoque.listar')->with('success', 'Transferência realizada com sucesso!');
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function transferirSecaoParaContainer(Request $request, $fkProduto, $quantidade)
    {
        $secaoOrigem = $request->input('secao_origem');
        $containerDestino = $request->input('container_destino');

        // Busca item na seção origem
        $itemOrigem = Itens_estoque::where('fk_produto', $fkProduto)
            ->where('fk_secao', $secaoOrigem)
            ->whereNull('fk_item_pai')
            ->first();

        if (!$itemOrigem || $itemOrigem->quantidade < $quantidade) {
            return back()->with('warning', 'Quantidade insuficiente na seção de origem.');
        }

        DB::beginTransaction();
        try {
            // Deduz da origem
            $itemOrigem->quantidade -= $quantidade;
            if ($itemOrigem->quantidade == 0) {
                $itemOrigem->delete();
            } else {
                $itemOrigem->save();
            }

            // Cria ou atualiza item dentro do container
            $itemNoContainer = Itens_estoque::where('fk_produto', $fkProduto)
                ->where('fk_item_pai', $containerDestino)
                ->first();

            if ($itemNoContainer) {
                $itemNoContainer->quantidade += $quantidade;
                $itemNoContainer->save();
            } else {
                Itens_estoque::create([
                    'fk_produto' => $fkProduto,
                    'fk_item_pai' => $containerDestino,
                    'unidade' => Auth::user()->fk_unidade,
                    'quantidade' => $quantidade,
                ]);
            }

            DB::commit();
            return redirect()->route('estoque.listar')->with('success', 'Item transferido para o container com sucesso!');
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function transferirContainerParaSecao(Request $request, $fkProduto, $quantidade)
    {
        $containerOrigem = $request->input('container_origem');
        $secaoDestino = $request->input('secao_destino');

        // Busca item dentro do container
        $itemOrigem = Itens_estoque::where('fk_produto', $fkProduto)
            ->where('fk_item_pai', $containerOrigem)
            ->first();

        if (!$itemOrigem || $itemOrigem->quantidade < $quantidade) {
            return back()->with('warning', 'Quantidade insuficiente no container de origem.');
        }

        DB::beginTransaction();
        try {
            // Deduz do container
            $itemOrigem->quantidade -= $quantidade;
            if ($itemOrigem->quantidade == 0) {
                $itemOrigem->delete();
            } else {
                $itemOrigem->save();
            }

            // Cria ou atualiza item na seção destino
            $itemDestino = Itens_estoque::where('fk_produto', $fkProduto)
                ->where('fk_secao', $secaoDestino)
                ->whereNull('fk_item_pai')
                ->first();

            if ($itemDestino) {
                $itemDestino->quantidade += $quantidade;
                $itemDestino->save();
            } else {
                Itens_estoque::create([
                    'fk_produto' => $fkProduto,
                    'fk_secao' => $secaoDestino,
                    'unidade' => Auth::user()->fk_unidade,
                    'quantidade' => $quantidade,
                ]);
            }

            DB::commit();
            return redirect()->route('estoque.listar')->with('success', 'Item transferido para a seção com sucesso!');
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function transferirEntreSeccoes(Request $request)
    {
        try {
            $request->validate([
                'fk_produto' => 'required|exists:produtos,id',
                'fk_secao_origem' => 'required|integer', // allow 0 for 'Sem seção'
                'fk_secao_destino' => 'required|exists:secaos,id',
                'quantidade' => 'required|integer|min:1',
                'observacao' => 'nullable|string',
            ]);

            // If origem is not 0 (unassigned), ensure it exists
            if (intval($request->fk_secao_origem) !== 0) {
                $secaoOrig = Secao::find(intval($request->fk_secao_origem));
                if (!$secaoOrig) {
                    return back()->with('warning', 'Seção de origem inválida.');
                }
            }

            if ($request->fk_secao_origem === $request->fk_secao_destino) {
                return back()->with('warning', 'A seção de origem deve ser diferente da seção de destino.');
            }

            // Busca item na seção origem
            $queryOrigem = Itens_estoque::where('fk_produto', $request->fk_produto)
                ->where('unidade', Auth::user()->fk_unidade);

            if (intval($request->fk_secao_origem) === 0) {
                // itens sem seção (fk_secao IS NULL)
                $queryOrigem->whereNull('fk_secao');
            } else {
                $queryOrigem->where('fk_secao', $request->fk_secao_origem);
            }

            $itemOrigem = $queryOrigem->first();

            if (!$itemOrigem || $itemOrigem->quantidade < $request->quantidade) {
                return back()->with('warning', 'Quantidade insuficiente na seção de origem.');
            }

            // Deduz da origem
            $itemOrigem->quantidade -= $request->quantidade;
            $itemOrigem->save();

            // Busca ou cria item na seção destino
            $itemDestino = Itens_estoque::where('fk_produto', $request->fk_produto)
                ->where('fk_secao', $request->fk_secao_destino)
                ->where('unidade', Auth::user()->fk_unidade)
                ->first();

            if ($itemDestino) {
                $itemDestino->quantidade += $request->quantidade;
                $itemDestino->save();
            } else {
                Itens_estoque::create([
                    'fk_produto' => $request->fk_produto,
                    'fk_secao' => $request->fk_secao_destino,
                    'unidade' => Auth::user()->fk_unidade,
                    'quantidade' => $request->quantidade,
                    'data_entrada' => now(),
                ]);
            }

            // Log de movimentação
            HistoricoMovimentacao::create([
                'fk_produto' => $request->fk_produto,
                'tipo_movimentacao' => 'transferencia_secoes',
                'quantidade' => $request->quantidade,
                'responsavel' => Auth::user()->nome,
                'observacao' => $request->observacao ?? 'Transferência entre seções',
                'data_movimentacao' => now(),
                'fk_unidade' => Auth::user()->fk_unidade,
            ]);

            return back()->with('success', 'Produto transferido entre seções com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao transferir entre seções', ['exception' => $e->getMessage()]);
            return back()->with('warning', 'Houve um erro ao transferir entre seções.');
        }
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
                'fk_secao' => 'required|exists:secaos,id',
                'fk_item_pai' => 'nullable|exists:itens_estoque,id',
                'lote' => 'nullable|string',
                'fornecedor' => 'nullable|string',
                'nota_fiscal' => 'nullable|string',
                'fonte' => 'nullable|string',
                'data_trp' => 'nullable|date',
                'sei' => 'nullable|string',
                'valor' => 'nullable|numeric',
                'observacao' => 'nullable|string',
            ]);

            $dataEntrada = Carbon::parse($request->data_entrada);

            // Busca o item no estoque pela seção específica, considerando se está em um container
            $itemEstoque = Itens_estoque::where('fk_produto', $request->fk_produto)
                ->where('unidade', $request->unidade)
                ->where('fk_secao', $request->fk_secao)
                ->where('fk_item_pai', $request->fk_item_pai ?? null)
                ->first();

            $novoValorMedio = $valorFinal;

            if ($itemEstoque) {
                // Calcula a nova média ponderada
                $quantidadeAtual = $itemEstoque->quantidade;
                $valorAtual = $itemEstoque->valor_unitario ?? 0;

                $novaQuantidade = $quantidadeAtual + $request->quantidade;
                $novoValorMedio = $novaQuantidade > 0
                    ? (($quantidadeAtual * $valorAtual) + ($request->quantidade * $valorFinal)) / $novaQuantidade
                    : $valorFinal;

                // Atualiza o estoque
                $itemEstoque->quantidade = $novaQuantidade;
                $itemEstoque->valor_total = $novoValorMedio * $novaQuantidade;
                $itemEstoque->valor_unitario = $valorFinal;
                $itemEstoque->fk_item_pai = $request->fk_item_pai ?? null;
                $itemEstoque->save();

                // Não mais atualiza campo 'produtos.valor' — média é mantida no estoque
            } else {
                // Cria novo registro de estoque na seção específica
                Itens_estoque::create([
                    'fk_produto' => $request->fk_produto,
                    'fk_secao' => $request->fk_secao,
                    'fk_item_pai' => $request->fk_item_pai ?? null,
                    'unidade' => $request->unidade,
                    'quantidade' => $request->quantidade,
                    'valor_total' => $valorFinal * $request->quantidade,
                    'valor_unitario' => $valorFinal,
                    'quantidade_inicial' => $request->quantidade,
                    'data_entrada' => $dataEntrada,
                    'lote' => $request->lote ?? null,
                    'fornecedor' => $request->fornecedor ?? null,
                    'nota_fiscal' => $request->nota_fiscal ?? null,
                    'fonte' => $request->fonte ?? null,
                    'data_trp' => $request->data_trp ?? null,
                    'sei' => $request->sei ?? null,
                ]);

                // não atualizamos campo 'produtos.valor'
            }

            // Não atualiza 'produtos.valor'; valor unitário fica em itens_estoque


            // Cria histórico de movimentação
            HistoricoMovimentacao::create([
                'fk_produto' => $request->fk_produto,
                'tipo_movimentacao' => 'entrada',
                'quantidade' => $request->quantidade,
                'valor_total' => $valorFinal * $request->quantidade,
                'valor_unitario' => $valorFinal,
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
    {
        try {
            if (Auth::user()->fk_unidade != $request->unidade) {
                return redirect()->back()->with('error', 'Você não tem permissão para movimentar produtos de outra unidade.');
            }
            // Validação dos dados únicos
            $request->validate([
                'data_entrada' => 'required|date',
                'lote' => 'nullable|string',
                'fornecedor' => 'nullable|string',
                'nota_fiscal' => 'nullable|string',
                'fonte' => 'nullable|string',
                'data_trp' => 'nullable|date',
                'sei' => 'nullable|string',
            ]);
            // Validação dos arrays
            $produtos = $request->fk_produto;
            $quantidades = $request->quantidade;
            $observacoes = $request->observacao;
            if (!is_array($produtos) || !is_array($quantidades)) {
                return back()->with('warning', 'Nenhum item foi adicionado.');
            }
            foreach ($produtos as $i => $produtoId) {
                if (empty($produtoId) || empty($quantidades[$i])) continue;
                // Validação individual
                $produto = Produto::find($produtoId);
                if (!$produto) continue;
                // Verifica se o produto já existe no estoque
                $itemEstoque = Itens_estoque::where('fk_produto', $produtoId)->where('unidade', $request->unidade)->first();
                if ($itemEstoque) {
                    // Produto já existe — soma a quantidade
                    $itemEstoque->quantidade += $quantidades[$i];
                    $itemEstoque->save();
                } else {
                    // Cria novo registro
                    Itens_estoque::create([
                        'unidade' => $request->unidade,
                        'quantidade' => $quantidades[$i],
                        'data_entrada' => $request->data_entrada,
                        'fk_produto' => $produtoId,
                        'lote' => $request->lote,
                        'fornecedor' => $request->fornecedor,
                        'nota_fiscal' => $request->nota_fiscal,
                        'observacao' => $observacoes[$i] ?? 'Entrada de novo produto',
                        'fonte' => $request->fonte,
                        'data_trp' => $request->data_trp,
                        'sei' => $request->sei,
                    ]);
                }
                HistoricoMovimentacao::create([
                    'fk_produto' => $produtoId,
                    'tipo_movimentacao' => 'entrada',
                    'quantidade' => $quantidades[$i],
                    'responsavel' => Auth::user()->nome,
                    'observacao' => $observacoes[$i] ?? 'Entrada de novo produto',
                    'data_movimentacao' => now(),
                    'fk_unidade' => $request->unidade,
                    'fonte' => $request->fonte,
                    'data_trp' => $request->data_trp,
                    'sei' => $request->sei,
                    'fornecedor' => $request->fornecedor,
                    'nota_fiscal' => $request->nota_fiscal,
                ]);
            }
            return redirect()->route('estoque.listar', [
                'nome' => '',
                'categoria' => '',
                'unidade' => Auth::user()->fk_unidade
            ])->with('success', 'Produtos cadastrados no estoque com sucesso!');
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
    public function formEntradaExistente(Request $request)
    {
        try {
            $produtos = Produto::where('ativo', 'Y')->orderBy('nome')->get();
            $secoes = Secao::all();
            $unidades = Unidade::all();
            $unidadeUsuario = Unidade::find(Auth::user()->fk_unidade);
            $isAdmin = Auth::user()->fk_perfil == 1;
            
            // Carrega todos os containers agrupados por seção usando eh_container
            $todosContainers = [];
            $containers = Itens_estoque::whereHas('produto', function($q) {
                    $q->where('eh_container', 1);
                })
                ->whereNull('fk_item_pai')
                ->with('produto', 'secao')
                ->get();
            
            // Agrupa por seção
            foreach ($containers as $container) {
                $secaoId = $container->fk_secao;
                if (!isset($todosContainers[$secaoId])) {
                    $todosContainers[$secaoId] = [];
                }
                $todosContainers[$secaoId][] = [
                    'id' => $container->id,
                    'produto' => [
                        'nome' => $container->produto->nome
                    ]
                ];
            }

            return view('estoque/estoque_form_entrada_existente', compact('produtos', 'secoes', 'unidades', 'unidadeUsuario', 'isAdmin', 'todosContainers'));
        } catch (Exception $e) {
            Log::error('Erro ao carregar formulário de entrada', ['exception' => $e->getMessage()]);
            return back()->with('warning', 'Houve um erro ao carregar o formulário de entrada.');
        }
    }

    public function formEntrada(Request $request, $id)
    {

        try {


            $produto = Itens_estoque::select('fk_produto', 'unidade', 'fk_secao')->where('id', $id)->first();
            $secoes = Secao::all();
            $unidadeUsuario = Unidade::find(Auth::user()->fk_unidade);
            $isAdmin = Auth::user()->fk_perfil == 1;
            
            // Carrega os containers (itens que podem conter outros itens)
            // Apenas containers da mesma seção
            $containers = Itens_estoque::where('fk_secao', $produto->fk_secao)
                ->whereNull('fk_item_pai')
                ->whereHas('itensFilhos')
                ->with('produto')
                ->get();

            return view('estoque/estoque_form_entrada', compact('produto', 'secoes', 'unidadeUsuario', 'isAdmin', 'containers'));
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
            $request->validate([
                'militar' => 'required|exists:efetivo_militar,id',
                'data_saida' => 'required|date',
                'fk_produto' => 'required|array',
                'quantidade' => 'required|array',
            ], [
                'militar.required' => 'Selecione o militar responsável pela saída.',
                'militar.exists' => 'Militar selecionado não encontrado.',
                'data_saida.required' => 'Informe a data da saída.',
                'data_saida.date' => 'Data da saída inválida.',
                'fk_produto.required' => 'Adicione pelo menos um item à lista.',
                'quantidade.required' => 'Informe a quantidade para cada item.',
            ]);
            $produtos = $request->fk_produto;
            $quantidades = $request->quantidade;
            $observacoes = $request->observacao;
            $militarId = $request->input('militar');
            $dataSaida = Carbon::parse($request->data_saida);
            $obsGeral = $request->input('observacao');
            $militar = EfetivoMilitar::findOrFail($militarId);
            $destinatario = $militar->nome;
            $loteSaida = uniqid('saida_');
            $erros = [];
            $itensProcessar = [];
            foreach ($produtos as $i => $estoqueId) {
                $quantidadeSolicitada = isset($quantidades[$i]) ? (int)$quantidades[$i] : 0;
                if (empty($estoqueId)) {
                    $erros[] = "Produto não selecionado na linha " . ($i+1);
                    continue;
                }
                if ($quantidadeSolicitada <= 0) {
                    $erros[] = "Quantidade inválida para o produto na linha " . ($i+1);
                    continue;
                }
                $estoque = Itens_estoque::where('id', $estoqueId)
                    ->where('unidade', Auth::user()->fk_unidade)
                    ->first();
                if (!$estoque) {
                    $erros[] = "Produto não encontrado no estoque (ID: $estoqueId) na linha " . ($i+1);
                    continue;
                }
                if ($quantidadeSolicitada > $estoque->quantidade) {
                    $erros[] = "Quantidade solicitada maior que a disponível para o produto " . ($estoque->produto->nome ?? $estoqueId) . " (Disponível: $estoque->quantidade) na linha " . ($i+1);
                    continue;
                }
                $itensProcessar[] = [
                    'estoque' => $estoque,
                    'quantidade' => $quantidadeSolicitada,
                    'observacao' => $observacoes[$i] ?? ''
                ];
            }
            if (!empty($erros)) {
                return back()->withErrors($erros)->withInput();
            }
            $motivo = $request->input('motivo');
            foreach ($itensProcessar as $item) {
                $estoque = $item['estoque'];
                $quantidadeSolicitada = $item['quantidade'];
                $estoque->quantidade -= $quantidadeSolicitada;
                $estoque->save();
                HistoricoMovimentacao::create([
                    'fk_produto' => $estoque->fk_produto,
                    'tipo_movimentacao' => 'saida_manual_multipla',
                    'quantidade' => $quantidadeSolicitada,
                    'responsavel' => Auth::user()->nome,
                    'observacao' => "Motivo: {$motivo}. Obs: {$obsGeral}",
                    'data_movimentacao' => $dataSaida,
                    'fk_unidade' => Auth::user()->fk_unidade,
                    'militar' => $destinatario,
                    'lote_saida' => $loteSaida,
                ]);
            }
            return redirect()->route('estoque.recibo', $loteSaida);
        } catch (\Exception $e) {
            Log::error('Erro ao realizar saída múltipla', [$e]);
            return back()->with('warning', 'Houve um erro ao realizar a saída múltipla: ' . $e->getMessage())->withInput();
        }
    }
    /**
     * Exibe o recibo de entrega de itens após saída múltipla
     */
    public function recibo($loteSaida)
    {
        $itens = HistoricoMovimentacao::where('lote_saida', $loteSaida)->get();
        // Pega o militar, data, etc, do primeiro item
        $militar = $itens->first()->militar ?? '';
        $data = $itens->first()->data_movimentacao ?? '';
        return view('estoque.recibo', compact('itens', 'militar', 'data'));
    }
    public function saidaMultiplosForm()
    {
        // Carrega todos os itens da unidade
        $rawItems = Itens_estoque::with('produto', 'secao')
            ->where('unidade', Auth::user()->fk_unidade)
            ->get();

        // Agrupa por produto+seção e soma quantidades, mas manteremos um representante (primeiro id) por combinação
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

            if ($quantidade <= 0) continue; // não incluir se estoque zero

            if (!isset($sectionsMap[$prodId])) $sectionsMap[$prodId] = [];
            $sectionsMap[$prodId][] = [
                'estoque_id' => $first->id,
                'secao_id' => $secaoId,
                'secao_nome' => optional($first->secao)->nome ?? 'Sem seção',
                'quantidade' => $quantidade,
            ];
        }

        // Monta lista de produtos únicos com estoque total > 0
        $productsGrouped = [];
        foreach ($sectionsMap as $prodId => $secs) {
            $produto = $rawItems->firstWhere('fk_produto', $prodId)->produto ?? null;
            if (!$produto) continue;
            $total = array_sum(array_column($secs, 'quantidade'));
            if ($total <= 0) continue;
            $productsGrouped[] = [
                'id' => $produto->id,
                'nome' => $produto->nome,
                'quantidade_total' => $total,
            ];
        }

        $itens_estoque = collect($productsGrouped);
        $militares = \App\Models\EfetivoMilitar::all();
        // Passa o mapa de seções como JSON para a view
        return view('estoque.saidaMultiplos', compact('itens_estoque', 'militares'))->with('sectionsMap', $sectionsMap);
    }

    public function formCadastrarContainer()
    {
        try {
            $secoes = Secao::all();
            $categorias = Categoria::all();
            $unidadeUsuario = Unidade::find(Auth::user()->fk_unidade);
            $isAdmin = Auth::user()->fk_perfil == 1;

            return view('estoque.cadastrar_container', compact('secoes', 'categorias', 'unidadeUsuario', 'isAdmin'));
        } catch (Exception $e) {
            Log::error('Erro ao abrir formulário de container', [$e]);
            return back()->with('warning', 'Houve um erro ao abrir o formulário');
        }
    }

    public function salvarContainer(Request $request)
    {
        try {
            $validated = $request->validate([
                'fk_categoria' => 'required|exists:categorias,id',
                'fk_secao' => 'required|exists:secaos,id',
                'nome_container' => 'required|string|max:255',
                'patrimonio' => 'nullable|string|max:255|unique:produtos,patrimonio',
                'valor' => 'nullable|numeric|min:0',
                'unidade' => 'required|exists:unidades,id',
            ]);

            // Verifica se já existe um container com esse nome na mesma seção
            $containerExistente = Itens_estoque::whereHas('produto', function($query) use ($validated) {
                $query->where('nome', $validated['nome_container']);
            })
            ->where('fk_secao', $validated['fk_secao'])
            ->whereNull('fk_item_pai')
            ->first();

            if ($containerExistente) {
                return back()->withErrors(['nome_container' => 'Já existe um container com este nome nesta seção.'])->withInput();
            }

            // Verifica se já existe um produto com esse nome de container
            $produtoExistente = Produto::where('nome', $validated['nome_container'])->first();
            
            if ($produtoExistente) {
                // Usa o produto existente
                $fkProdutoContainer = $produtoExistente->id;
            } else {
                // Cria um novo produto para o container
                $novoProduto = Produto::create([
                    'nome' => $validated['nome_container'],
                    'fk_categoria' => $validated['fk_categoria'],
                    'patrimonio' => $validated['patrimonio'] ?? null,
                    'ativo' => 1,
                ]);
                $fkProdutoContainer = $novoProduto->id;
            }

            // Cria o item de estoque para o container (quantidade sempre = 1)
            $valorUnitario = $validated['valor'] ?? 0;
            Itens_estoque::create([
                'fk_produto' => $fkProdutoContainer,
                'fk_secao' => $validated['fk_secao'],
                'quantidade' => 1, // Sempre 1 para container
                'unidade' => $validated['unidade'],
                'valor_unitario' => $valorUnitario,
                'valor_total' => $valorUnitario,
                'fk_item_pai' => null, // Container não tem pai
                'data_entrada' => now(),
            ]);

            // Registra a movimentação
            HistoricoMovimentacao::create([
                'tipo_movimentacao' => 'entrada',
                'quantidade' => 1,
                'fk_produto' => $fkProdutoContainer,
                'fk_unidade' => $validated['unidade'],
                'responsavel' => Auth::user()->nome ?? 'Sistema',
                'observacao' => 'Container/Bolsa cadastrado: ' . $validated['nome_container'],
                'data_movimentacao' => now(),
                'valor_unitario' => $valorUnitario,
                'valor_total' => $valorUnitario,
            ]);

            return redirect()->route('estoque.listar')->with('success', 'Container cadastrado com sucesso!');
        } catch (Exception $e) {
            Log::error('Erro ao salvar container', [$e]);
            return back()->with('error', 'Houve um erro ao cadastrar o container')->withInput();
        }
    }

    public function verConteudoContainer($id)
    {
        try {
            // Busca o item container
            $container = Itens_estoque::with(['produto', 'secao', 'unidade'])->findOrFail($id);
            
            // Busca todos os itens dentro deste container
            $itensFilhos = Itens_estoque::where('fk_item_pai', $id)
                ->with(['produto.categoria', 'secao'])
                ->get();
            
            // Calcula totais
            $quantidadeItens = $itensFilhos->count();
            $quantidadeTotalItens = $itensFilhos->sum('quantidade');
            
            return view('estoque.container_conteudo', compact('container', 'itensFilhos', 'quantidadeItens', 'quantidadeTotalItens'));
        } catch (Exception $e) {
            Log::error('Erro ao visualizar conteúdo do container', [$e]);
            return back()->with('error', 'Houve um erro ao visualizar o container');
        }
    }

    public function formMoverItem($id)
    {
        try {
            $item = Itens_estoque::with(['produto', 'secao', 'itemPai.produto'])->findOrFail($id);
            
            // Busca todos os containers da mesma seção usando eh_container
            $containers = Itens_estoque::whereHas('produto', function($q) {
                    $q->where('eh_container', 1);
                })
                ->where('fk_secao', $item->fk_secao)
                ->whereNull('fk_item_pai')
                ->with('produto')
                ->get()
                ->filter(function($cont) use ($id) {
                    return $cont->id !== $id;
                })
                ->map(function($cont) {
                    return [
                        'id' => $cont->id,
                        'nome' => $cont->produto->nome . ' - ' . $cont->secao->nome
                    ];
                });
            
            return view('estoque.mover_item', compact('item', 'containers'));
        } catch (Exception $e) {
            Log::error('Erro ao carregar formulário de mover item', [$e]);
            return back()->with('error', 'Houve um erro ao carregar o formulário');
        }
    }

    public function moverItem(Request $request, $id)
    {
        try {
            $item = Itens_estoque::findOrFail($id);
            
            $validated = $request->validate([
                'destino' => 'required|in:secao,container',
                'fk_item_pai' => 'nullable|integer|exists:itens_estoque,id'
            ]);
            
            $itemAnterior = $item->itemPai;
            $localizacaoAnterior = $itemAnterior ? $itemAnterior->produto->nome : "Seção '{$item->secao->nome}'";
            
            if ($validated['destino'] === 'secao') {
                // Move para a seção (remove do container)
                $item->fk_item_pai = null;
                $item->save();
                
                $descricao = "Movido de '{$localizacaoAnterior}' para Seção '{$item->secao->nome}'";
            } else {
                // Move para outro container
                $novoContainer = Itens_estoque::findOrFail($validated['fk_item_pai']);
                
                if ($novoContainer->fk_secao !== $item->fk_secao) {
                    throw new Exception('O container deve estar na mesma seção do item');
                }
                
                $item->fk_item_pai = $validated['fk_item_pai'];
                $item->save();
                
                $descricao = "Movido de '{$localizacaoAnterior}' para '{$novoContainer->produto->nome}'";
            }
            
            // Registra a movimentação
            HistoricoMovimentacao::create([
                'tipo_movimentacao' => 'transferencia',
                'descricao' => $descricao,
                'fk_item_estoque' => $id,
                'responsavel' => Auth::user()->name,
                'unidade' => $item->unidade,
            ]);
            
            return back()->with('success', 'Item movido com sucesso!');
        } catch (Exception $e) {
            Log::error('Erro ao mover item', ['id' => $id, 'error' => $e->getMessage()]);
            return back()->with('error', 'Houve um erro ao mover o item: ' . $e->getMessage());
        }
    }
}
