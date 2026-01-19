<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cautela;
use App\Models\CautelaProduto;
use App\Models\Produto;
use App\Models\Secao;
use App\Models\Itens_estoque;
use Illuminate\Support\Facades\Auth;

class CautelaController extends Controller
{
    public function index(Request $request)
    {
        $query = Cautela::with('produtos.produto')->orderBy('created_at', 'desc');
        
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
        // Carrega todos os itens de estoque da unidade do usuário agrupados por produto+seção
        $rawItems = Itens_estoque::with('produto', 'secao')
            ->where('unidade', Auth::user()->fk_unidade)
            ->where('quantidade', '>', 0)  // apenas com estoque > 0
            ->get();

        // Agrupa por produto+seção
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

            if ($quantidade <= 0) continue;

            if (!isset($sectionsMap[$prodId])) $sectionsMap[$prodId] = [];
            $sectionsMap[$prodId][] = [
                'estoque_id' => (int)$first->id,
                'secao_id' => $secaoId,
                'secao_nome' => optional($first->secao)->nome ?? 'Sem seção',
                'quantidade' => (int)$quantidade,
            ];
        }

        // Converte chaves para string para garantir compatibilidade com JS
        $sectionsMap = array_combine(
            array_map('strval', array_keys($sectionsMap)),
            array_values($sectionsMap)
        );

        // Lista de produtos únicos
        $productsGrouped = [];
        foreach ($sectionsMap as $prodId => $secs) {
            $produto = $rawItems->firstWhere('fk_produto', (int)$prodId)->produto ?? null;
            if (!$produto) continue;
            $total = array_sum(array_column($secs, 'quantidade'));
            if ($total <= 0) continue;
            $productsGrouped[] = [
                'id' => (int)$produto->id,
                'nome' => $produto->nome,
                'quantidade_total' => (int)$total,
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
        ]);

        $cautela = Cautela::create([
            'nome_responsavel' => $request->nome_responsavel,
            'telefone' => $request->telefone,
            'instituicao' => $request->instituicao,
            'responsavel_unidade' => Auth::user()->name,
            'data_cautela' => $request->data_cautela,
            'data_prevista_devolucao' => $request->data_prevista_devolucao,
        ]);

        foreach ($request->produtos as $index => $produtoId) {
            $estoqueId = $request->secoes[$index];
            $quantidade = $request->quantidades[$index];

            // Subtrai do estoque
            $itemEstoque = Itens_estoque::findOrFail($estoqueId);
            if ($itemEstoque->quantidade < $quantidade) {
                return redirect()->back()->with('error', 'Quantidade insuficiente em estoque para o produto: ' . $itemEstoque->produto->nome);
            }
            
            $itemEstoque->quantidade -= $quantidade;
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
        $cautela = Cautela::with(['produtos.produto', 'produtos.estoque.secao'])->findOrFail($id);
        return view('cautelas.show', compact('cautela'));
    }

    public function devolucao($id)
    {
        $cautela = Cautela::with(['produtos.produto', 'produtos.estoque.secao'])->findOrFail($id);
        return view('cautelas.devolucao', compact('cautela'));
    }

    public function processDevolucao(Request $request, $id)
    {
        $cautela = Cautela::findOrFail($id);
        
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
            
            // Valida se a quantidade não excede o pendente
            $pendente = $cautelaItem->quantidadePendente();
            if ($quantidadeDevolver > $pendente) {
                return redirect()->back()->with('error', 'Quantidade de devolução excede a quantidade pendente para o produto: ' . $cautelaItem->produto->nome);
            }

            // Retorna ao estoque
            $itemEstoque = Itens_estoque::findOrFail($cautelaItem->estoque_id);
            $itemEstoque->quantidade += $quantidadeDevolver;
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
            $cautela = Cautela::with('produtos.produto')->findOrFail($id);
            
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
            $cautela = Cautela::with('produtos.produto')->findOrFail($id);
            return view('cautelas.preview-comprovante', compact('cautela'));
        } catch (\Exception $e) {
            \Log::error('Erro ao visualizar comprovante', [$e]);
            return back()->with('error', 'Erro ao visualizar comprovante');
        }
    }
}