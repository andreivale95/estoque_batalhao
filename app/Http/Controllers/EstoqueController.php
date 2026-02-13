<?php

namespace App\Http\Controllers;


use App\Models\Categoria;
use App\Models\HistoricoMovimentacao;
use App\Models\Itens_estoque;
use App\Models\ItenPatrimonial;
use App\Models\ItemFoto;
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
use App\Services\EstoqueUnificadoService;
use App\Exports\EstoqueLocalizacaoExport;
use Illuminate\Support\Facades\Gate;


class EstoqueController extends Controller
{
    public function listarEstoque(Request $request)
    {
        $service = new EstoqueUnificadoService();

        $podeVerOutrasUnidades = Gate::allows('autorizacao', 8);
        $unidadeFiltro = $podeVerOutrasUnidades
            ? $request->query('unidade', '')
            : Auth::user()->fk_unidade;
        
        $filtros = [
            'tipo' => $request->query('tipo', ''),
            'fk_categoria' => $request->query('categoria', ''),
            'fk_secao' => $request->query('secao', ''),
            'search' => $request->query('search', ''),
            'per_page' => $request->query('per_page', 15),
            'unidade' => $unidadeFiltro,
        ];

        $itens = $service->obterEstoqueUnificado($filtros);
        $categorias = Categoria::all();
        $secoes = Secao::all();
        $unidades = $podeVerOutrasUnidades
            ? Unidade::all()
            : Unidade::where('id', Auth::user()->fk_unidade)->get();

        return view('estoque.listarEstoque', [
            'itens' => $itens,
            'itens_estoque' => $itens,
            'categorias' => $categorias,
            'secoes' => $secoes,
            'unidades' => $unidades,
            'filtros' => $filtros,
            'service' => $service,
            'podeVerOutrasUnidades' => $podeVerOutrasUnidades,
        ]);
    }

    public function exportarEstoqueLocalizacao(Request $request)
    {
        $podeVerOutrasUnidades = Gate::allows('autorizacao', 8);
        $unidadeId = $request->query('unidade');
        $unidadeId = $unidadeId ? (int) $unidadeId : null;
        if (!$podeVerOutrasUnidades) {
            $unidadeId = Auth::user()->fk_unidade;
        }

        $export = new EstoqueLocalizacaoExport($unidadeId);
        $headers = $export->headings();
        $rows = $export->collection();

        $fileName = 'estoque_localizacao.csv';

        return response()->streamDownload(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, $headers, ';');

            foreach ($rows as $row) {
                $data = is_array($row) ? $row : (array) $row;
                fputcsv($handle, $data, ';');
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
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

            DB::beginTransaction();

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
                DB::rollBack();
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
                'tipo_movimentacao' => 'transferencia',
                'quantidade' => $request->quantidade,
                'responsavel' => Auth::user()->nome,
                'observacao' => $request->observacao ?? 'Transferência entre seções',
                'data_movimentacao' => now(),
                'fk_unidade' => Auth::user()->fk_unidade,
            ]);

            DB::commit();
            return back()->with('success', 'Produto transferido entre seções com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao transferir entre seções', ['exception' => $e->getMessage()]);
            return back()->with('warning', 'Houve um erro ao transferir entre seções: ' . $e->getMessage());
        }
    }

    /**
     * Transferir patrimônios entre seções
     */
    public function transferirPatrimonios(Request $request)
    {
        try {
            Log::info('Transferência de patrimônios iniciada', ['request' => $request->all()]);
            
            $validated = $request->validate([
                'fk_produto' => 'required|exists:produtos,id',
                'fk_secao_destino' => 'required|exists:secaos,id',
                'patrimonio_ids' => 'required|array|min:1',
                'patrimonio_ids.*' => 'required|exists:itens_patrimoniais,id',
            ], [
                'fk_produto.required' => 'Produto não informado.',
                'fk_secao_destino.required' => 'Seção de destino não informada.',
                'patrimonio_ids.required' => 'Nenhum patrimônio selecionado.',
                'patrimonio_ids.min' => 'Selecione pelo menos um patrimônio.',
            ]);

            DB::beginTransaction();

            $patrimoniostransferidos = 0;
            foreach ($validated['patrimonio_ids'] as $patrimonioId) {
                $patrimonio = ItenPatrimonial::find($patrimonioId);
                
                if ($patrimonio && is_null($patrimonio->data_saida)) {
                    // Verifica se a seção de destino é diferente
                    if ($patrimonio->fk_secao !== (int)$validated['fk_secao_destino']) {
                        $patrimonio->fk_secao = $validated['fk_secao_destino'];
                        $patrimonio->save();
                        $patrimoniostransferidos++;
                        
                        Log::info('Patrimônio transferido', [
                            'patrimonio_id' => $patrimonioId,
                            'patrimonio_numero' => $patrimonio->patrimonio,
                            'secao_destino' => $validated['fk_secao_destino']
                        ]);
                    }
                }
            }

            if ($patrimoniostransferidos === 0) {
                DB::rollBack();
                    Log::warning('Nenhum patrimônio foi transferido');
                return back()->with('warning', 'Nenhum patrimônio foi transferido.');
            }

            DB::commit();
                Log::info("$patrimoniostransferidos patrimônio(s) transferido(s) com sucesso");
            return back()->with('success', "$patrimoniostransferidos patrimônio(s) transferido(s) com sucesso!");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao transferir patrimônios', ['exception' => $e->getMessage()]);
            return back()->with('warning', 'Houve um erro ao transferir patrimônios: ' . $e->getMessage());
        }
    }

    public function entradaEstoque(Request $request)
    {
        try {
            // Verifica se o usuário tem permissão
            if (Auth::user()->fk_unidade != $request->unidade && !Gate::allows('autorizacao', 8)) {
                return redirect()->back()->with('error', 'Você não tem permissão para movimentar produtos de outra unidade.');
            }

            // Validação dos dados recebidos para múltiplos itens
            $request->validate([
                'produtos' => 'required|array|min:1',
                'produtos.*' => 'required|exists:produtos,id',
                'quantidades' => 'nullable|array',
                'quantidades.*' => 'nullable|integer|min:0',
                'secoes' => 'required|array',
                'secoes.*' => 'required|exists:secaos,id',
                'datas_entrada' => 'required|array',
                'datas_entrada.*' => 'required|date',
                'valores' => 'nullable|array',
                'valores_centavos' => 'nullable|array',
                'lotes' => 'nullable|array',
                'patrimonios' => 'nullable|array',
                'fornecedor' => 'nullable|string',
                'nota_fiscal' => 'nullable|string',
                'fonte' => 'nullable|string',
                'sei' => 'nullable|string',
                'observacao' => 'nullable|string',
            ]);

            $produtos = $request->input('produtos', []);
            $quantidades = $request->input('quantidades', []);
            $secoes = $request->input('secoes', []);
            $datas = $request->input('datas_entrada', []);
            $valoresCentavos = $request->input('valores_centavos', []);
            $lotes = $request->input('lotes', []);
            $patrimonios = $request->input('patrimonios', []);

            if (count($produtos) !== count($secoes) || count($produtos) !== count($datas)) {
                return back()->with('error', 'Dados inconsistentes. Verifique os itens.');
            }

            $itensProcessados = [];

            // Processa cada item
            for ($i = 0; $i < count($produtos); $i++) {
                $produtoId = $produtos[$i];
                $quantidade = $quantidades[$i] ?? 0;
                $secaoId = $secoes[$i];
                $dataEntrada = Carbon::parse($datas[$i]);
                $valorCentavos = $valoresCentavos[$i] ?? 0;
                $valorFinal = $valorCentavos / 100;
                $lote = $lotes[$i] ?? null;

                // Validar valor não pode ser zero
                if ($valorFinal <= 0) {
                    \Log::warning("Valor zero ou inválido para produto: $produtoId");
                    continue;
                }

                $produto = Produto::find($produtoId);
                if (!$produto) {
                    continue;
                }

                // Se é item permanente
                if ($produto->tipo_controle === 'permanente') {
                    // Forçar quantidade 1 para permanente
                    $quantidade = 1;

                    $patrimoniosStr = $patrimonios[$i] ?? '';
                    $listaPatrimonios = array_filter(array_map('trim', explode(',', $patrimoniosStr)));

                    if (count($listaPatrimonios) === 0) {
                        \Log::warning("Patrimônios não fornecidos para produto permanente: $produtoId");
                        continue;
                    }

                    // Verificar duplicatas
                    if (count($listaPatrimonios) !== count(array_unique($listaPatrimonios))) {
                        \Log::warning("Patrimônios duplicados para produto: $produtoId");
                        continue;
                    }

                    // Verificar se patrimônios já existem
                    $existentes = ItenPatrimonial::whereIn('patrimonio', $listaPatrimonios)->exists();
                    if ($existentes) {
                        \Log::warning("Um ou mais patrimônios já existem: produto $produtoId");
                        continue;
                    }

                    // Criar cada patrimônio
                    foreach ($listaPatrimonios as $patrimonio) {
                        ItenPatrimonial::create([
                            'fk_produto' => $produtoId,
                            'patrimonio' => $patrimonio,
                            'serie' => null,
                            'fk_secao' => $secaoId,
                            'condicao' => 'bom',
                            'data_entrada' => $dataEntrada,
                            'quantidade_cautelada' => 0,
                            'observacao' => $request->observacao ?? null,
                            'fornecedor' => $request->fornecedor,
                            'nota_fiscal' => $request->nota_fiscal,
                            'lote' => $lote,
                            'fonte' => $request->fonte,
                            'data_trp' => $request->data_trp ?? null,
                            'sei' => $request->sei,
                            'valor_unitario' => $valorFinal,
                            'valor_total' => $valorFinal,
                        ]);
                    }

                    // Criar histórico
                    HistoricoMovimentacao::create([
                        'fk_produto' => $produtoId,
                        'tipo_movimentacao' => 'entrada',
                        'quantidade' => count($listaPatrimonios),
                        'valor_total' => $valorFinal * count($listaPatrimonios),
                        'valor_unitario' => $valorFinal,
                        'responsavel' => Auth::user()->nome,
                        'observacao' => $request->observacao ?? 'Entrada de bens patrimoniais',
                        'data_movimentacao' => $dataEntrada,
                        'fk_unidade' => $request->unidade,
                        'fonte' => $request->fonte,
                        'sei' => $request->sei,
                        'fornecedor' => $request->fornecedor,
                        'nota_fiscal' => $request->nota_fiscal,
                    ]);

                    $itensProcessados[] = $produto->nome;
                    continue;
                }

                // Item de consumo
                $itemEstoque = Itens_estoque::where('fk_produto', $produtoId)
                    ->where('unidade', $request->unidade)
                    ->where('fk_secao', $secaoId)
                    ->first();

                if ($itemEstoque) {
                    // Atualiza quantidade e valor
                    $quantidadeAtual = $itemEstoque->quantidade;
                    $valorAtual = $itemEstoque->valor_unitario ?? 0;

                    $novaQuantidade = $quantidadeAtual + $quantidade;
                    $novoValorMedio = $novaQuantidade > 0
                        ? (($quantidadeAtual * $valorAtual) + ($quantidade * $valorFinal)) / $novaQuantidade
                        : $valorFinal;

                    $itemEstoque->quantidade = $novaQuantidade;
                    $itemEstoque->valor_total = $novoValorMedio * $novaQuantidade;
                    $itemEstoque->valor_unitario = $valorFinal;
                    $itemEstoque->save();
                } else {
                    // Cria novo registro de estoque
                    $itemEstoque = Itens_estoque::create([
                        'fk_produto' => $produtoId,
                        'fk_secao' => $secaoId,
                        'unidade' => $request->unidade,
                        'quantidade' => $quantidade,
                        'valor_total' => $valorFinal * $quantidade,
                        'valor_unitario' => $valorFinal,
                        'quantidade_inicial' => $quantidade,
                        'data_entrada' => $dataEntrada,
                        'lote' => $lote,
                        'fornecedor' => $request->fornecedor ?? null,
                        'nota_fiscal' => $request->nota_fiscal ?? null,
                        'fonte' => $request->fonte ?? null,
                        'data_trp' => null,
                        'sei' => $request->sei ?? null,
                    ]);
                }

                // Cria histórico de movimentação
                HistoricoMovimentacao::create([
                    'fk_produto' => $produtoId,
                    'tipo_movimentacao' => 'entrada',
                    'quantidade' => $quantidade,
                    'valor_total' => $valorFinal * $quantidade,
                    'valor_unitario' => $valorFinal,
                    'responsavel' => Auth::user()->nome,
                    'observacao' => $request->observacao ?? 'Entrada em lote',
                    'data_movimentacao' => $dataEntrada,
                    'fk_unidade' => $request->unidade,
                    'fonte' => $request->fonte,
                    'sei' => $request->sei,
                    'fornecedor' => $request->fornecedor,
                    'nota_fiscal' => $request->nota_fiscal,
                ]);

                $itensProcessados[] = $produto->nome;
            }

            $mensagem = count($itensProcessados) > 0 
                ? 'Entrada de ' . count($itensProcessados) . ' produto(s) registrada(s) com sucesso!'
                : 'Nenhum produto foi processado.';

            return redirect()->route('estoque.listar', [
                'nome' => '',
                'categoria' => '',
                'unidade' => Auth::user()->fk_unidade
            ])->with('success', $mensagem);
        } catch (\Exception $e) {
            Log::error('Erro ao dar entrada no Estoque', ['exception' => $e->getMessage()]);
            return back()->with('warning', 'Houve um erro ao dar entrada no Estoque: ' . $e->getMessage());
        }
    }

    private function salvarFotosEntrada($files, $itemEstoque = null, $max = 3)
    {
        if (empty($files)) {
            return;
        }

        $ordem = 1;
        foreach (array_slice($files, 0, $max) as $file) {
            if (!$file || !$file->isValid()) {
                continue;
            }

            $path = $file->store('itens_fotos', 'public');

            if ($itemEstoque) {
                ItemFoto::create([
                    'fk_itens_estoque' => $itemEstoque->id,
                    'fk_iten_patrimonial' => null,
                    'caminho_arquivo' => $path,
                    'nome_original' => $file->getClientOriginalName(),
                    'tipo_mime' => $file->getClientMimeType(),
                    'tamanho' => $file->getSize(),
                    'ordem' => $ordem,
                ]);
            }

            $ordem++;
        }
    }

    private function salvarFotosPatrimonio($files, $itenPatrimonial, $max = 2)
    {
        if (empty($files) || !$itenPatrimonial) {
            return;
        }

        $ordem = 1;
        foreach (array_slice($files, 0, $max) as $file) {
            if (!$file || !$file->isValid()) {
                continue;
            }

            $path = $file->store('itens_fotos', 'public');

            ItemFoto::create([
                'fk_itens_estoque' => null,
                'fk_iten_patrimonial' => $itenPatrimonial->id,
                'caminho_arquivo' => $path,
                'nome_original' => $file->getClientOriginalName(),
                'tipo_mime' => $file->getClientMimeType(),
                'tamanho' => $file->getSize(),
                'ordem' => $ordem,
            ]);

            $ordem++;
        }
    }
    public function entradaProdutoEstoque(Request $request)
    {
        try {
            if (Auth::user()->fk_unidade != $request->unidade && !Gate::allows('autorizacao', 8)) {
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
                if ($produto->tipo_controle === 'permanente') {
                    $patrimoniosRaw = $request->input("patrimonios_raw.$i", '');
                    $patrimonios = preg_split("/\r\n|\r|\n/", $patrimoniosRaw);
                    $patrimonios = array_values(array_filter(array_map('trim', $patrimonios)));
                    if (count($patrimonios) === 0) {
                        return back()->with('warning', 'Informe os patrimônios para itens permanentes.');
                    }
                    $quantidades[$i] = count($patrimonios);
                    if (count($patrimonios) !== count(array_unique($patrimonios))) {
                        return back()->with('warning', 'Há patrimônios duplicados na lista informada.');
                    }
                    $patrimonioExistente = ItenPatrimonial::whereIn('patrimonio', $patrimonios)->exists();
                    if ($patrimonioExistente) {
                        return back()->with('warning', 'Um ou mais patrimônios já estão cadastrados no sistema.');
                    }

                    $secaoPadrao = Secao::where('fk_unidade', $request->unidade)->first();
                    if (!$secaoPadrao) {
                        return back()->with('warning', 'Nenhuma seção disponível para esta unidade.');
                    }

                    foreach ($patrimonios as $patrimonio) {
                        ItenPatrimonial::create([
                            'fk_produto' => $produtoId,
                            'patrimonio' => $patrimonio,
                            'serie' => null,
                            'fk_secao' => $secaoPadrao->id,
                            'condicao' => 'bom',
                            'data_entrada' => $request->data_entrada,
                            'quantidade_cautelada' => 0,
                            'observacao' => $observacoes[$i] ?? 'Entrada de bens patrimoniais',
                            'fornecedor' => $request->fornecedor,
                            'nota_fiscal' => $request->nota_fiscal,
                            'lote' => $request->lote,
                            'fonte' => $request->fonte,
                            'data_trp' => $request->data_trp,
                            'sei' => $request->sei,
                        ]);
                    }

                    HistoricoMovimentacao::create([
                        'fk_produto' => $produtoId,
                        'tipo_movimentacao' => 'entrada',
                        'quantidade' => count($patrimonios),
                        'responsavel' => Auth::user()->nome,
                        'observacao' => $observacoes[$i] ?? 'Entrada de bens patrimoniais',
                        'data_movimentacao' => now(),
                        'fk_unidade' => $request->unidade,
                        'fonte' => $request->fonte,
                        'data_trp' => $request->data_trp,
                        'sei' => $request->sei,
                        'fornecedor' => $request->fornecedor,
                        'nota_fiscal' => $request->nota_fiscal,
                    ]);
                    continue;
                }
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
            if (Auth::user()->fk_unidade != $request->unidade && !Gate::allows('autorizacao', 8)) {
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
        // Redireciona para a nova entrada múltipla
        return redirect()->route('entrada.form');
    }

    public function formEntrada(Request $request, $id = null)
    {
        try {
            $secoes = Secao::all();
            $unidadeUsuario = Unidade::find(Auth::user()->fk_unidade);
            $isAdmin = Auth::user()->fk_perfil == 1;
            
            // Para manter compatibilidade, se houver ID, busca o item
            $produto = null;
            $containers = [];
            
            if ($id) {
                $produto = Itens_estoque::select('fk_produto', 'unidade', 'fk_secao')->where('id', $id)->first();
                // Carrega os containers (itens que podem conter outros itens)
                $containers = Itens_estoque::where('fk_secao', $produto->fk_secao)
                    ->whereNull('fk_item_pai')
                    ->whereHas('itensFilhos')
                    ->with('produto')
                    ->get();
            } else {
                // Passa um objeto vazio para compatibilidade com a view
                $produto = (object)['fk_produto' => null, 'unidade' => Auth::user()->fk_unidade, 'fk_secao' => null];
            }

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
                'tipo' => 'required|array',
            ], [
                'militar.required' => 'Selecione o militar responsável pela saída.',
                'militar.exists' => 'Militar selecionado não encontrado.',
                'data_saida.required' => 'Informe a data da saída.',
                'data_saida.date' => 'Data da saída inválida.',
                'fk_produto.required' => 'Adicione pelo menos um item à lista.',
                'quantidade.required' => 'Informe a quantidade para cada item.',
                'tipo.required' => 'Tipo de item não informado.',
            ]);
            
            $produtos = $request->fk_produto;
            $quantidades = $request->quantidade;
            $tipos = $request->tipo;
            $patrimoniosArray = $request->patrimonios ?? [];
            $observacoes = $request->observacao;
            $militarId = $request->input('militar');
            $dataSaida = Carbon::parse($request->data_saida);
            $obsGeral = $request->input('observacao');
            $militar = EfetivoMilitar::findOrFail($militarId);
            $destinatario = $militar->nome;
            $loteSaida = uniqid('saida_');
            $erros = [];
            $itensProcessar = [];

            // Reorganiza os patrimônios por índice
            $patrimoniosPorItem = [];
            $currentIndex = 0;
            foreach ($patrimoniosArray as $patrimonioId) {
                if (!isset($patrimoniosPorItem[$currentIndex])) {
                    $patrimoniosPorItem[$currentIndex] = [];
                }
                $patrimoniosPorItem[$currentIndex][] = $patrimonioId;
                
                // Quando o número de patrimônios corresponder à quantidade, avança para o próximo item
                if (isset($quantidades[$currentIndex]) && count($patrimoniosPorItem[$currentIndex]) >= $quantidades[$currentIndex]) {
                    $currentIndex++;
                }
            }

            foreach ($produtos as $i => $produtoIdOrEstoqueId) {
                $quantidadeSolicitada = isset($quantidades[$i]) ? (int)$quantidades[$i] : 0;
                $tipo = $tipos[$i] ?? 'consumo';

                if (empty($produtoIdOrEstoqueId)) {
                    $erros[] = "Produto não selecionado na linha " . ($i+1);
                    continue;
                }
                if ($quantidadeSolicitada <= 0) {
                    $erros[] = "Quantidade inválida para o produto na linha " . ($i+1);
                    continue;
                }

                if ($tipo === 'consumo') {
                    // Processa item de consumo
                    $estoque = Itens_estoque::where('id', $produtoIdOrEstoqueId)
                        ->where('unidade', Auth::user()->fk_unidade)
                        ->first();
                    
                    if (!$estoque) {
                        $erros[] = "Produto não encontrado no estoque (ID: $produtoIdOrEstoqueId) na linha " . ($i+1);
                        continue;
                    }
                    if ($quantidadeSolicitada > $estoque->quantidade) {
                        $erros[] = "Quantidade solicitada maior que a disponível para o produto " . ($estoque->produto->nome ?? $produtoIdOrEstoqueId) . " (Disponível: $estoque->quantidade) na linha " . ($i+1);
                        continue;
                    }
                    
                    $itensProcessar[] = [
                        'tipo' => 'consumo',
                        'estoque' => $estoque,
                        'quantidade' => $quantidadeSolicitada,
                        'observacao' => $observacoes[$i] ?? ''
                    ];
                } else {
                    // Processa item permanente
                    $patrimonios = $patrimoniosPorItem[$i] ?? [];
                    
                    if (empty($patrimonios)) {
                        $erros[] = "Nenhum patrimônio selecionado para o item permanente na linha " . ($i+1);
                        continue;
                    }
                    
                    if (count($patrimonios) != $quantidadeSolicitada) {
                        $erros[] = "Quantidade de patrimônios não corresponde à quantidade informada na linha " . ($i+1);
                        continue;
                    }
                    
                    // Valida os patrimônios
                    $itensPatrimoniais = ItenPatrimonial::whereIn('id', $patrimonios)
                        ->whereNull('data_saida')
                        ->get();
                    
                    if ($itensPatrimoniais->count() != count($patrimonios)) {
                        $erros[] = "Alguns patrimônios selecionados não estão disponíveis na linha " . ($i+1);
                        continue;
                    }
                    
                    $itensProcessar[] = [
                        'tipo' => 'permanente',
                        'patrimonios' => $itensPatrimoniais,
                        'quantidade' => $quantidadeSolicitada,
                        'observacao' => $observacoes[$i] ?? '',
                        'produto_id' => $itensPatrimoniais->first()->fk_produto
                    ];
                }
            }

            if (!empty($erros)) {
                return back()->withErrors($erros)->withInput();
            }

            $motivo = $request->input('motivo');
            
            DB::beginTransaction();
            
            try {
                foreach ($itensProcessar as $item) {
                    if ($item['tipo'] === 'consumo') {
                        // Processa saída de item de consumo
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
                    } else {
                        // Processa saída de itens permanentes
                        foreach ($item['patrimonios'] as $patrimonio) {
                            $patrimonio->data_saida = $dataSaida;
                            $patrimonio->save();

                            // Busca o valor unitário do último registro de entrada deste produto
                            $valorUnitario = HistoricoMovimentacao::where('fk_produto', $patrimonio->fk_produto)
                                ->where('tipo_movimentacao', 'entrada')
                                ->whereNotNull('valor_unitario')
                                ->orderBy('data_movimentacao', 'desc')
                                ->value('valor_unitario');

                            HistoricoMovimentacao::create([
                                'fk_produto' => $patrimonio->fk_produto,
                                'tipo_movimentacao' => 'saida_manual_multipla',
                                'quantidade' => 1,
                                'responsavel' => Auth::user()->nome,
                                'observacao' => "Patrimônio: {$patrimonio->patrimonio}. Motivo: {$motivo}. Obs: {$obsGeral}",
                                'data_movimentacao' => $dataSaida,
                                'fk_unidade' => Auth::user()->fk_unidade,
                                'militar' => $destinatario,
                                'lote_saida' => $loteSaida,
                                'valor_unitario' => $valorUnitario ?? 0,
                            ]);
                            
                            Log::info('Histórico de saída criado para patrimônio', [
                                'patrimonio' => $patrimonio->patrimonio,
                                'produto_id' => $patrimonio->fk_produto,
                                'lote_saida' => $loteSaida
                            ]);
                        }
                    }
                }
                
                DB::commit();
                
                return redirect()->route('estoque.recibo', $loteSaida);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Erro ao processar saída múltipla', [
                    'erro' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
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
        // Carrega todos os itens de consumo da unidade
        $rawItems = Itens_estoque::with('produto', 'secao')
            ->where('unidade', Auth::user()->fk_unidade)
            ->get();

        // Carrega todos os itens permanentes da unidade
        $rawPatrimoniais = ItenPatrimonial::with('produto', 'secao')
            ->whereHas('produto', function($q) {
                $q->whereHas('unidade', function($qu) {
                    $qu->where('unidades.id', Auth::user()->fk_unidade);
                });
            })
            ->whereNull('data_saida') // Apenas itens que não saíram
            ->get();

        // Agrupa itens de consumo por produto+seção
        $groupedByProdSec = $rawItems->groupBy(function ($item) {
            return $item->fk_produto . '_' . ($item->fk_secao ?? 0);
        });

        $sectionsMap = [];
        
        // Processa itens de consumo
        foreach ($groupedByProdSec as $key => $group) {
            $first = $group->first();
            $parts = explode('_', $key);
            $prodId = (int)$parts[0];
            $secaoId = (int)$parts[1];
            $quantidade = $group->sum('quantidade');

            if ($quantidade <= 0) continue;

            if (!isset($sectionsMap[$prodId])) $sectionsMap[$prodId] = [];
            $sectionsMap[$prodId][] = [
                'estoque_id' => $first->id,
                'secao_id' => $secaoId,
                'secao_nome' => optional($first->secao)->nome ?? 'Sem seção',
                'quantidade' => $quantidade,
                'tipo' => 'consumo',
                'patrimonios' => []
            ];
        }

        // Processa itens permanentes (agrupa por produto+seção)
        $groupedPatrimoniais = $rawPatrimoniais->groupBy(function ($item) {
            return $item->fk_produto . '_' . ($item->fk_secao ?? 0);
        });

        foreach ($groupedPatrimoniais as $key => $group) {
            $first = $group->first();
            $parts = explode('_', $key);
            $prodId = (int)$parts[0];
            $secaoId = (int)$parts[1];
            
            // Lista de patrimônios disponíveis nesta seção
            $patrimonios = $group->map(function($item) {
                return [
                    'id' => $item->id,
                    'patrimonio' => $item->patrimonio,
                    'serie' => $item->serie,
                    'condicao' => $item->condicao,
                    'observacao' => $item->observacao
                ];
            })->toArray();

            if (!isset($sectionsMap[$prodId])) $sectionsMap[$prodId] = [];
            $sectionsMap[$prodId][] = [
                'estoque_id' => $first->id, // id do primeiro item patrimonial (para referência)
                'secao_id' => $secaoId,
                'secao_nome' => optional($first->secao)->nome ?? 'Sem seção',
                'quantidade' => count($patrimonios),
                'tipo' => 'permanente',
                'patrimonios' => $patrimonios
            ];
        }

        // Monta lista de produtos únicos com estoque total > 0
        $productsGrouped = [];
        $allItems = $rawItems->merge($rawPatrimoniais);
        
        foreach ($sectionsMap as $prodId => $secs) {
            $produto = $allItems->firstWhere('fk_produto', $prodId)->produto ?? null;
            if (!$produto) continue;
            $total = array_sum(array_column($secs, 'quantidade'));
            if ($total <= 0) continue;
            
            $productsGrouped[] = [
                'id' => $produto->id,
                'nome' => $produto->nome,
                'quantidade_total' => $total,
                'tipo_controle' => $produto->tipo_controle ?? 'consumo'
            ];
        }

        $itens_estoque = collect($productsGrouped);
        $militares = \App\Models\EfetivoMilitar::all();
        
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
            
            // Containers não mais suportados - eh_container foi removido
            $containers = [];
            
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

    /**
     * Listar estoque unificado (consumo + permanente)
     */
    public function listarUnificado(Request $request)
    {
        $service = new EstoqueUnificadoService();

        $podeVerOutrasUnidades = Gate::allows('autorizacao', 8);
        $unidadeFiltro = $podeVerOutrasUnidades
            ? $request->query('unidade', '')
            : Auth::user()->fk_unidade;
        
        $filtros = [
            'tipo' => $request->query('tipo', ''),
            'fk_categoria' => $request->query('categoria', ''),
            'fk_secao' => $request->query('secao', ''),
            'search' => $request->query('search', ''),
            'per_page' => $request->query('per_page', 15),
            'unidade' => $unidadeFiltro,
        ];

        $itens = $service->obterEstoqueUnificado($filtros);
        $categorias = Categoria::all();
        $secoes = Secao::all();

        return view('estoque.listarUnificado', [
            'itens' => $itens,
            'categorias' => $categorias,
            'secoes' => $secoes,
            'filtros' => $filtros,
            'service' => $service
        ]);
    }
}

