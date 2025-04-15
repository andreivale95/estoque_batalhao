<?php

namespace App\Http\Controllers;

use App\Models\EfetivoMilitar;
use App\Models\Itens_estoque;
use App\Models\Unidade;
use App\Models\Produto;
use App\Models\Kit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\HistoricoMovimentacao;
use Illuminate\Support\Facades\Auth;

class SaidaEstoqueController extends Controller
{
    // Exibe o formulário inicial com lista de militares e kits disponíveis
    public function index(Request $request)
    {
        $query = EfetivoMilitar::query();

        if ($request->filled('nome')) {
            $query->where('nome', 'like', '%' . $request->nome . '%');
        }

        if ($request->filled('unidade_id')) {
            $query->where('unidade_id', $request->unidade_id);
        }

        if ($request->filled('posto_graduacao')) {
            $query->where('posto_graduacao', $request->posto_graduacao);
        }

        $militares = EfetivoMilitar::whereHas('produtos', function ($query) {
            $query->where('entregue', 'NAO');
        })->orderBy('nome')->paginate(10)->withQueryString();




        $unidades = Unidade::orderBy('nome')->get();
        $postos = EfetivoMilitar::select('posto_graduacao')->distinct()->orderBy('posto_graduacao')->pluck('posto_graduacao');

        $kits = Kit::where('disponivel', 'S')->orderBy('nome')->get();

        return view('saida_kit.index', compact('militares', 'unidades', 'postos', 'kits'));
    }



    // Exibe a view de confirmação com os produtos do kit para o militar escolhido
    public function selecionarKit(Request $request)
    {
        $militar = EfetivoMilitar::findOrFail($request->militar_id);
        $kit = Kit::findOrFail($request->kit_id);

        // 1. Buscar produtos associados ao militar e que pertencem ao kit selecionado
        $produtosParaSaida = Produto::with('tamanho')
        ->join('efetivo_militar_produto', 'produtos.id', '=', 'efetivo_militar_produto.fk_produto')
        ->where('efetivo_militar_produto.entregue', 'NAO')
        ->where('efetivo_militar_produto.fk_efetivo_militar', $militar->id)
        ->where('produtos.fk_kit', $kit->id)
        ->select('produtos.*') // cuidado: só seleciona da tabela produtos
        ->get();


        // 2. Montar a lista de itens com disponibilidade
        $itens = [];

        foreach ($produtosParaSaida as $produto) {
            $estoque = Itens_estoque::where('fk_produto', $produto->id)->where('unidade', $militar->fk_unidade)->first();

            $itens[] = [
                'produto' => $produto,
                'tamanho' => $produto->tamanho()->first()->tamanho ?? 'Único',
                'quantidade' => 1, // já que cada item é único
                'disponivel' => ($estoque && $estoque->quantidade >= 1) ? 'Sim' : 'Não'
            ];
            $temProdutoDisponivel = collect($itens)->contains(fn($item) => $item['disponivel'] === 'Sim');

        }


        return view('saida_kit.confirmar', compact('militar', 'kit', 'itens', 'temProdutoDisponivel'));
    }



    public function confirmarSaida(Request $request)
    {
        $militarId = $request->militar_id;
        $kitId = $request->kit_id;
        $militar = EfetivoMilitar::findOrFail($militarId);
        $unidadeMilitar = $militar->fk_unidade;

        // Busca os IDs dos produtos associados ao militar que ainda não foram entregues
        $produtosAssociados = DB::table('efetivo_militar_produto')
            ->where('fk_efetivo_militar', $militarId)
            ->where('entregue', 'NAO')
            ->pluck('fk_produto');

        // Filtra os produtos que fazem parte do kit selecionado
        $produtosParaSaida = Produto::whereIn('id', $produtosAssociados)
            ->where('fk_kit', $kitId)
            ->get();

        // Verifica o estoque dos produtos somente da unidade do militar
        $itensEstoque = Itens_estoque::whereIn('fk_produto', $produtosParaSaida->pluck('id'))
            ->where('unidade', $unidadeMilitar)
            ->get()
            ->groupBy('fk_produto');

        $produtosSemEstoque = [];
        $produtosComSaida = [];
        $produtosComSaidaIds = [];

        foreach ($produtosParaSaida as $produto) {
            $estoqueProduto = $itensEstoque[$produto->id] ?? collect();
            $item = $estoqueProduto->firstWhere('quantidade', '>', 0);

            if ($item) {
                // Dar baixa
                $item->quantidade -= 1;
                if ($item->quantidade == 0) {
                    $item->data_saida = Carbon::now();
                }
                $item->save();

                // Registrar histórico
                HistoricoMovimentacao::create([
                    'fk_produto' => $produto->id,
                    'tipo_movimentacao' => 'saida_kit',
                    'quantidade' => 1,
                    'responsavel' => Auth::user()->nome,
                    'observacao' => "Saída de kit '{$produto->kit->nome}' para o militar {$militar->nome} ({$militar->matricula}) produto '{$produto->nome}' Tamanho {$produto->tamanho()->first()->tamanho}",
                    'data_movimentacao' => now(),
                    'fk_unidade' => $item->fk_unidade,
                ]);

                $produtosComSaida[] = $produto->nome;
                $produtosComSaidaIds[] = $produto->id;
            } else {
                $produtosSemEstoque[] = $produto->nome;
            }
        }

        // Atualiza o status de entrega apenas dos produtos que tiveram saída
        if (!empty($produtosComSaidaIds)) {
            DB::table('efetivo_militar_produto')
                ->where('fk_efetivo_militar', $militarId)
                ->whereIn('fk_produto', $produtosComSaidaIds)
                ->update(['entregue' => 'SIM']);
        }

        if (count($produtosSemEstoque) > 0) {
            $produtosSemEstoque = array_unique($produtosSemEstoque);
            $mensagem = 'Saída realizada parcialmente.';
            $mensagem .= ' Os seguintes produtos estavam indisponíveis na unidade e não foram baixados: ' . implode(', ', $produtosSemEstoque);
            return redirect()->route('saida_estoque.index')->with('warning', $mensagem);
        }

        return redirect()->route('saida_estoque.index')->with('success', 'Saída de produtos realizada com sucesso!');
    }



}


