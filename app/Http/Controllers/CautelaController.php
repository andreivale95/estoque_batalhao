<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Cautela;
use App\Models\CautelaProduto;
use App\Models\Produto;
use App\Models\Secao;
use App\Models\Itens_estoque;
use App\Models\ItenPatrimonial;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class CautelaController extends Controller
{
    public function index(Request $request)
    {
        $query = Cautela::with('produtos.produto')->orderBy('created_at', 'desc');

        $podeVerOutrasUnidades = Gate::allows('autorizacao', 8);
        if (!$podeVerOutrasUnidades) {
            $unidadeId = Auth::user()->fk_unidade;
            $query->whereHas('produtos.produto', function ($q) use ($unidadeId) {
                $q->where('unidade', $unidadeId);
            });
        }
        
        // Filtro por responsável
        if ($request->filled('responsavel')) {
            $query->where('nome_responsavel', 'like', '%' . $request->get('responsavel') . '%');
        }
        
        // Filtro por data
        if ($request->filled('data_inicio')) {
            $query->whereDate('data_cautela', '>=', $request->get('data_inicio'));
        }
        if ($request->filled('data_fim')) {
            $query->whereDate('data_cautela', '<=', $request->get('data_fim'));
        }
        
        // Filtro por status (pendente ou concluído)
        if ($request->filled('status')) {
            $status = $request->get('status');
            if ($status === 'pendente') {
                // Cautelas com produtos não totalmente devolvidos
                $query->whereHas('produtos', function ($q) {
                    $q->whereRaw('quantidade_devolvida < quantidade');
                }, '>', 0);
            } elseif ($status === 'concluido') {
                // Cautelas com todos os produtos devolvidos
                $query->whereDoesntHave('produtos', function ($q) {
                    $q->whereRaw('quantidade_devolvida < quantidade');
                });
            }
        }
        
        $cautelas = $query->get();
        $filtros = [
            'responsavel' => $request->get('responsavel', ''),
            'data_inicio' => $request->get('data_inicio', ''),
            'data_fim' => $request->get('data_fim', ''),
            'status' => $request->get('status', ''),
        ];
        
        return view('cautelas.index', compact('cautelas', 'filtros'));
    }

    public function create()
    {
        // Carrega itens de consumo
        $rawItems = Itens_estoque::with('produto', 'secao')
            ->where('unidade', Auth::user()->fk_unidade)
            ->where('quantidade', '>', 0)
            ->get();

        // Carrega itens permanentes disponíveis
        $rawPatrimoniais = ItenPatrimonial::with('produto', 'secao')
            ->whereNull('data_saida')
            ->where(function($q) {
                $q->whereNull('quantidade_cautelada')
                  ->orWhere('quantidade_cautelada', 0);
            })
            ->whereHas('produto', function($q) {
                $q->where('unidade', Auth::user()->fk_unidade);
            })
            ->get();

        // Agrupa consumo por produto+seção
        $groupedByProdSec = $rawItems->groupBy(function ($item) {
            return $item->fk_produto . '_' . ($item->fk_secao ?? 0);
        });

        $sectionsMap = [];

        foreach ($groupedByProdSec as $key => $group) {
            $first = $group->first();
            $parts = explode('_', $key);
            $prodId = (int)$parts[0];
            $secaoId = (int)$parts[1];
            $quantidade = (int)$group->sum('quantidade');

            if ($quantidade <= 0) continue;

            if (!isset($sectionsMap[$prodId])) $sectionsMap[$prodId] = [];
            $sectionsMap[$prodId][] = [
                'estoque_id' => (int)$first->id,
                'secao_id' => $secaoId,
                'secao_nome' => optional($first->secao)->nome ?? 'Sem seção',
                'quantidade' => $quantidade,
                'tipo' => 'consumo',
                'patrimonios' => [],
            ];
        }

        // Agrupa permanentes por produto+seção
        $groupedPatrimoniais = $rawPatrimoniais->groupBy(function ($item) {
            return $item->fk_produto . '_' . ($item->fk_secao ?? 0);
        });

        foreach ($groupedPatrimoniais as $key => $group) {
            $first = $group->first();
            $parts = explode('_', $key);
            $prodId = (int)$parts[0];
            $secaoId = (int)$parts[1];

            $patrimonios = $group->map(function($item) {
                return [
                    'id' => $item->id,
                    'patrimonio' => $item->patrimonio,
                    'serie' => $item->serie,
                    'condicao' => $item->condicao,
                    'observacao' => $item->observacao,
                ];
            })->values()->toArray();

            if (count($patrimonios) <= 0) continue;

            if (!isset($sectionsMap[$prodId])) $sectionsMap[$prodId] = [];
            $sectionsMap[$prodId][] = [
                'estoque_id' => (int)$first->id,
                'secao_id' => $secaoId,
                'secao_nome' => optional($first->secao)->nome ?? 'Sem seção',
                'quantidade' => count($patrimonios),
                'tipo' => 'permanente',
                'patrimonios' => $patrimonios,
            ];
        }

        // Converte chaves para string para garantir compatibilidade com JS
        $sectionsMap = array_combine(
            array_map('strval', array_keys($sectionsMap)),
            array_values($sectionsMap)
        );

        // Lista de produtos (mostrar todos da unidade do usuario)
        $productsGrouped = [];
        $produtos = Produto::where('unidade', Auth::user()->fk_unidade)
            ->where('ativo', true)
            ->orderBy('nome')
            ->get();

        foreach ($produtos as $produto) {
            $secs = $sectionsMap[(string)$produto->id] ?? [];
            $total = array_sum(array_column($secs, 'quantidade'));
            $productsGrouped[] = [
                'id' => (int)$produto->id,
                'nome' => $produto->nome,
                'quantidade_total' => (int)$total,
                'tipo_controle' => $produto->tipo_controle ?? 'consumo',
            ];
        }

        $itens_estoque = collect($productsGrouped);
        $secoes = Secao::all();
        \Log::info('Debug Cautela Create', [
            'user_unidade' => Auth::user()->fk_unidade,
            'raw_items_count' => $rawItems->count(),
            'sections_map' => $sectionsMap,
            'produtos_count' => count($productsGrouped),
        ]);
        return view('cautelas.create', compact('itens_estoque', 'secoes'))->with('sectionsMap', $sectionsMap);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome_responsavel' => 'required|string',
            'telefone' => 'required|string',
            'instituicao' => 'required|string',
            'data_cautela' => 'required|date',
            'data_prevista_devolucao' => 'required|date',
            'produtos' => 'required|array',
            'secoes' => 'required|array',
            'quantidades' => 'required|array',
            'tipos' => 'required|array',
            'patrimonios' => 'nullable|array',
            'fotos' => 'nullable|array',
            'fotos.*' => 'image|mimes:jpeg,png,gif|max:5120',
        ]);

        $fotos = [];
        if ($request->hasFile('fotos')) {
            foreach ($request->file('fotos') as $file) {
                $path = $file->store('cautelas', 'public');
                $fotos[] = $path;
            }
        }

        $cautela = Cautela::create([
            'nome_responsavel' => $request->nome_responsavel,
            'telefone' => $request->telefone,
            'instituicao' => $request->instituicao,
            'responsavel_unidade' => Auth::user()->nome,
            'data_cautela' => $request->data_cautela,
            'data_prevista_devolucao' => $request->data_prevista_devolucao,
            'fotos' => $fotos,
        ]);

        foreach ($request->produtos as $index => $produtoId) {
            $tipo = $request->tipos[$index] ?? 'consumo';

            if ($tipo === 'permanente') {
                $patrimonioId = $request->patrimonios[$index] ?? null;
                if (!$patrimonioId) {
                    return redirect()->back()->with('error', 'Selecione o patrimônio para o item permanente.');
                }

                $itemPatrimonial = ItenPatrimonial::where('id', $patrimonioId)
                    ->whereNull('data_saida')
                    ->where(function($q) {
                        $q->whereNull('quantidade_cautelada')
                          ->orWhere('quantidade_cautelada', 0);
                    })
                    ->firstOrFail();

                if ((int)$itemPatrimonial->fk_produto !== (int)$produtoId) {
                    return redirect()->back()->with('error', 'Patrimônio não corresponde ao produto selecionado.');
                }

                // Marca como cautelado
                $itemPatrimonial->quantidade_cautelada = 1;
                $itemPatrimonial->save();

                // Registra na cautela
                CautelaProduto::create([
                    'cautela_id' => $cautela->id,
                    'produto_id' => $produtoId,
                    'estoque_id' => null,
                    'iten_patrimonial_id' => $itemPatrimonial->id,
                    'quantidade' => 1,
                ]);
                continue;
            }

            $estoqueId = $request->secoes[$index];
            $quantidade = $request->quantidades[$index];

            // Subtrai do estoque
            $itemEstoque = Itens_estoque::findOrFail($estoqueId);
            if ($itemEstoque->quantidade < $quantidade) {
                return redirect()->back()->with('error', 'Quantidade insuficiente em estoque para o produto: ' . $itemEstoque->produto->nome);
            }
            
            $itemEstoque->quantidade -= $quantidade;
            $itemEstoque->quantidade_cautelada += $quantidade;
            $itemEstoque->save();

            // Registra na cautela
            CautelaProduto::create([
                'cautela_id' => $cautela->id,
                'produto_id' => $produtoId,
                'estoque_id' => $estoqueId,
                'quantidade' => $quantidade,
            ]);
        }

        return redirect()->route('cautelas.index')->with('success', 'Cautela cadastrada com sucesso!');
    }

    public function show($id)
    {
        $podeVerOutrasUnidades = Gate::allows('autorizacao', 8);
        $unidadeId = Auth::user()->fk_unidade;

        $cautela = Cautela::with(['produtos.produto', 'produtos.estoque.secao', 'produtos.itenPatrimonial.secao'])
            ->when(!$podeVerOutrasUnidades, function ($q) use ($unidadeId) {
                $q->whereHas('produtos.produto', function ($p) use ($unidadeId) {
                    $p->where('unidade', $unidadeId);
                });
            })
            ->findOrFail($id);
        return view('cautelas.show', compact('cautela'));
    }

    public function devolucao($id)
    {
        $podeVerOutrasUnidades = Gate::allows('autorizacao', 8);
        $unidadeId = Auth::user()->fk_unidade;

        $cautela = Cautela::with(['produtos.produto', 'produtos.estoque.secao', 'produtos.itenPatrimonial.secao'])
            ->when(!$podeVerOutrasUnidades, function ($q) use ($unidadeId) {
                $q->whereHas('produtos.produto', function ($p) use ($unidadeId) {
                    $p->where('unidade', $unidadeId);
                });
            })
            ->findOrFail($id);
        return view('cautelas.devolucao', compact('cautela'));
    }

    public function processDevolucao(Request $request, $id)
    {
        $podeVerOutrasUnidades = Gate::allows('autorizacao', 8);
        $unidadeId = Auth::user()->fk_unidade;

        $cautela = Cautela::when(!$podeVerOutrasUnidades, function ($q) use ($unidadeId) {
            $q->whereHas('produtos.produto', function ($p) use ($unidadeId) {
                $p->where('unidade', $unidadeId);
            });
        })->findOrFail($id);
        
        $request->validate([
            'itens' => 'required|array',
            'quantidades' => 'required|array',
        ]);

        foreach ($request->itens as $index => $itemId) {
            $quantidadeDevolver = (int)$request->quantidades[$index];
            
            if ($quantidadeDevolver <= 0) {
                continue;
            }

            $cautelaItem = CautelaProduto::findOrFail($itemId);

            // Itens permanentes: devolução unitária
            if ($cautelaItem->iten_patrimonial_id) {
                $pendente = $cautelaItem->quantidadePendente();
                if ($quantidadeDevolver > $pendente) {
                    return redirect()->back()->with('error', 'Quantidade de devolução excede a quantidade pendente para o patrimônio do produto: ' . $cautelaItem->produto->nome);
                }

                $itemPatrimonial = ItenPatrimonial::findOrFail($cautelaItem->iten_patrimonial_id);
                $itemPatrimonial->quantidade_cautelada = 0;
                $itemPatrimonial->save();

                $cautelaItem->quantidade_devolvida += $quantidadeDevolver;
                if ($cautelaItem->isDevolvido() && !$cautelaItem->data_devolucao) {
                    $cautelaItem->data_devolucao = now();
                }
                $cautelaItem->save();
                continue;
            }
            
            // Valida se a quantidade não excede o pendente
            $pendente = $cautelaItem->quantidadePendente();
            if ($quantidadeDevolver > $pendente) {
                return redirect()->back()->with('error', 'Quantidade de devolução excede a quantidade pendente para o produto: ' . $cautelaItem->produto->nome);
            }

            // Retorna ao estoque
            $itemEstoque = Itens_estoque::findOrFail($cautelaItem->estoque_id);
            $itemEstoque->quantidade += $quantidadeDevolver;
            $itemEstoque->quantidade_cautelada -= $quantidadeDevolver;
            $itemEstoque->save();

            // Atualiza a cautela
            $cautelaItem->quantidade_devolvida += $quantidadeDevolver;
            if ($cautelaItem->isDevolvido() && !$cautelaItem->data_devolucao) {
                $cautelaItem->data_devolucao = now();
            }
            $cautelaItem->save();
        }

        return redirect()->route('cautelas.show', $cautela->id)->with('success', 'Devolução registrada com sucesso!');
    }

    public function gerarPDF($id)
    {
        try {
            $podeVerOutrasUnidades = Gate::allows('autorizacao', 8);
            $unidadeId = Auth::user()->fk_unidade;

            $cautela = Cautela::with('produtos.produto')
                ->when(!$podeVerOutrasUnidades, function ($q) use ($unidadeId) {
                    $q->whereHas('produtos.produto', function ($p) use ($unidadeId) {
                        $p->where('unidade', $unidadeId);
                    });
                })
                ->findOrFail($id);
            
            $pdf = \PDF::loadView('cautelas.comprovante', compact('cautela'));
            
            return $pdf->download('Comprovante-Cautela-' . $cautela->id . '.pdf');
        } catch (\Exception $e) {
            \Log::error('Erro ao gerar PDF da cautela', [$e]);
            return back()->with('error', 'Erro ao gerar comprovante');
        }
    }

    /**
     * Exibe visualização do comprovante em HTML para preview
     */
    public function previewPDF($id)
    {
        try {
            $podeVerOutrasUnidades = Gate::allows('autorizacao', 8);
            $unidadeId = Auth::user()->fk_unidade;

            $cautela = Cautela::with('produtos.produto')
                ->when(!$podeVerOutrasUnidades, function ($q) use ($unidadeId) {
                    $q->whereHas('produtos.produto', function ($p) use ($unidadeId) {
                        $p->where('unidade', $unidadeId);
                    });
                })
                ->findOrFail($id);
            return view('cautelas.preview-comprovante', compact('cautela'));
        } catch (\Exception $e) {
            \Log::error('Erro ao visualizar comprovante', [$e]);
            return back()->with('error', 'Erro ao visualizar comprovante');
        }
    }

    /**
     * Lista itens que estão em cautela (por item)
     */
    public function listarPorItem(Request $request)
    {
        // Obtém todos os CautelaProduto com relacionamentos
        $podeVerOutrasUnidades = Gate::allows('autorizacao', 8);
        $unidadeId = Auth::user()->fk_unidade;

        $cautelas = CautelaProduto::with(['produto', 'cautela'])
            ->when(!$podeVerOutrasUnidades, function ($q) use ($unidadeId) {
                $q->whereHas('produto', function ($p) use ($unidadeId) {
                    $p->where('unidade', $unidadeId);
                });
            })
            ->get();

        // Agrupa por produto e calcula
        $produtosAgrupados = [];
        
        foreach ($cautelas as $cautelaItem) {
            $prodId = $cautelaItem->produto_id;
            $quantidade_cautelada = $cautelaItem->quantidade - (int)($cautelaItem->quantidade_devolvida ?? 0);
            
            // Ignora itens totalmente devolvidos
            if ($quantidade_cautelada <= 0) {
                continue;
            }
            
            if (!isset($produtosAgrupados[$prodId])) {
                $produtosAgrupados[$prodId] = [
                    'id' => $prodId,
                    'produto' => $cautelaItem->produto,
                    'nome' => $cautelaItem->produto->nome ?? 'Produto desconhecido',
                    'quantidade_cautelada' => 0,
                    'quantidade_devolvida' => 0,
                    'quantidade_pessoas' => 0,
                ];
            }
            
            $produtosAgrupados[$prodId]['quantidade_cautelada'] += $quantidade_cautelada;
            $produtosAgrupados[$prodId]['quantidade_devolvida'] += (int)($cautelaItem->quantidade_devolvida ?? 0);
        }

        // Calcula quantidade de pessoas por produto
        foreach ($produtosAgrupados as $prodId => &$dados) {
            $pessoasUnicas = CautelaProduto::where('produto_id', $prodId)
                ->where('quantidade', '>', DB::raw('COALESCE(quantidade_devolvida, 0)'))
                ->distinct('cautela_id')
                ->count('cautela_id');
            $dados['quantidade_pessoas'] = $pessoasUnicas;
        }

        // Ordena por nome
        uasort($produtosAgrupados, function ($a, $b) {
            return strcasecmp($a['nome'], $b['nome']);
        });

        // Converte para Collection
        $produtos = collect($produtosAgrupados);

        return view('cautelas.por-item', compact('produtos'));
    }

    /**
     * Mostra detalhes de um item em cautela (para quem está cautelado)
     */
    public function detalhesPorItem($prodId)
    {
        // Obtém o produto
        $produto = Produto::findOrFail($prodId);

        // Obtém todas as cautelas ativas para este produto
        $cautelasProduto = CautelaProduto::with(['cautela', 'produto'])
            ->where('produto_id', $prodId)
            ->get();

        // Filtra apenas aquelas com saldo
        $cautelas = [];
        $quantidadeTotalCautelada = 0;

        foreach ($cautelasProduto as $cp) {
            $saldo = $cp->quantidade - (int)($cp->quantidade_devolvida ?? 0);
            if ($saldo > 0) {
                $cautelas[] = $cp;
                $quantidadeTotalCautelada += $saldo;
            }
        }

        if (empty($cautelas)) {
            return back()->with('info', 'Nenhuma cautela ativa para este produto');
        }

        // Converte para Collection
        $cautelas = collect($cautelas);

        return view('cautelas.detalhes-item', compact('produto', 'cautelas', 'quantidadeTotalCautelada', 'prodId'));
    }
}