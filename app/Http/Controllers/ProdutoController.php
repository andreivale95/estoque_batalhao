<?php

namespace App\Http\Controllers;

use App\Models\Alertas;
use App\Models\Categoria;
use App\Models\Condicao;
use App\Models\HistoricoRevisoes;
use App\Models\Itens_estoque;
use App\Models\Kit;
use App\Models\MinMaxKm;
use App\Models\Tamanho;
use App\Models\TipoBem;
use App\Models\TipoProduto;
use App\Models\Unidade;
use App\Models\Patrimonio;
use App\Models\Fonte;
use App\Models\Produto;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Secao;


class ProdutoController extends Controller {

    public function getDetalhesPorSecao($id)
    {
        try {
            $detalhes = Itens_estoque::select(
                'secaos.nome as secao_nome',
                DB::raw('SUM(itens_estoque.quantidade) as quantidade')
            )
            ->leftJoin('secaos', 'secaos.id', '=', 'itens_estoque.fk_secao')
            ->where('itens_estoque.fk_produto', $id)
            ->groupBy('secaos.id', 'secaos.nome')
            ->get();

            return response()->json($detalhes);
        } catch (Exception $e) {
            Log::error('Erro ao buscar detalhes por seção', [$e]);
            return response()->json(['error' => 'Erro ao buscar detalhes'], 500);
        }
    }
    
    /**
     * Página de detalhes do produto com quebra por seção
     */
    public function detalhes($id)
    {
        try {
            $produto = Produto::findOrFail($id);

            $quantidadeTotal = Itens_estoque::where('fk_produto', $id)->sum('quantidade');

            // Busca todos os itens deste produto com suas hierarquias
            $todosOsItens = Itens_estoque::where('fk_produto', $id)
                ->with(['secao', 'itemPai.produto', 'itensFilhos'])
                ->orderBy('fk_secao')
                ->get();

            $detalhesSecao = Itens_estoque::select(
                'itens_estoque.fk_secao',
                'secaos.nome as secao_nome',
                DB::raw('SUM(itens_estoque.quantidade) as quantidade')
            )
            ->leftJoin('secaos', 'secaos.id', '=', 'itens_estoque.fk_secao')
            ->where('itens_estoque.fk_produto', $id)
            ->groupBy('itens_estoque.fk_secao', 'secaos.nome')
            ->get()
            ->map(function ($row) {
                return [
                    'secao_id' => $row->fk_secao,
                    'secao_nome' => $row->secao_nome ?? 'Sem seção',
                    'quantidade' => $row->quantidade,
                ];
            });

            return view('produtos.detalhes', compact('produto', 'quantidadeTotal', 'detalhesSecao', 'todosOsItens'));
        } catch (Exception $e) {
            Log::error('Erro ao carregar detalhes do produto', [$e]);
            return back()->with('warning', 'Erro ao carregar detalhes do produto.');
        }
    }
    public function ativarProduto(Request $request, $id)
    {
        try {
            $produto = Produto::find($id);
            if (!$produto) {
                return back()->with('warning', 'Produto não encontrado.');
            }
            $produto->ativo = 'Y';
            $produto->save();
            return redirect()->route('produtos.listar')->with('success', 'Produto reativado com sucesso!');
        } catch (Exception $e) {
            Log::error('Erro ao reativar produto', [$e]);
            return back()->with('warning', 'Erro ao reativar produto.');
        }
    }
    public function excluirProduto(Request $request, $id)
    {
        try {
            $produto = Produto::find($id);
            if (!$produto) {
                return back()->with('warning', 'Produto não encontrado.');
            }
            $estoque = Itens_estoque::where('fk_produto', $id)->sum('quantidade');
            if ($estoque > 0) {
                return back()->with('warning', 'Não é possível inativar: o estoque deste produto não está zerado.');
            }
            $produto->ativo = 'N';
            $produto->save();
            return redirect()->route('produtos.listar')->with('success', 'Produto inativado com sucesso!');
        } catch (Exception $e) {
            Log::error('Erro ao inativar produto', [$e]);
            return back()->with('warning', 'Erro ao inativar produto.');
        }
    }



    public function verProduto(Request $request, $id)
    {

        $mesAtual = Carbon::now()->month;

        try {

            $unidades = Unidade::all();
            $kit = Produto::find($id)->kit()->first();
            $kits = Kit::all();
            $condicoes = Condicao::all();
            $produto = Produto::find($id);
            $produtos = Produto::all();
            $tamanhos = Tamanho::all();
            $categorias = Categoria::all();

            return view('produtos/verProduto', compact(
                'produto',
                'condicoes',
                'categorias',
                'unidades',
                'kit',
                'produtos',
                'kits',
                'tamanhos',


            ));
        } catch (Exception $e) {
            Log::error('Error ao consultar Produto', [$e]);
            return back()->with('warning', 'Houve um erro ao consultar o Produto');
        }
    }

    public function listarProdutos(Request $request)
    {


        $request['nome'] = empty($request['nome']) ? '' : $request->get('nome');
        $request['categoria'] = empty($request['categoria']) ? '' : $request->get('categoria');
        $request['marca'] = empty($request['marca']) ? '' : $request->get('marca');


        $sort = $request->get('sort', 'nome');
        $direction = $request->get('direction', 'asc');

        $sortable = [
            'nome', 'patrimonio', 'descricao', 'marca', 'categoria', 'unidade'
        ];

        $patrimonio = $request->get('patrimonio', '');

        try {
            $categorias = Categoria::all();
            $todasMarcas = Produto::select('marca')->distinct()->pluck('marca');


            $produtos = Produto::query()
                ->when($request->filled('ativo'), function ($query) use ($request) {
                    return $query->where('ativo', $request->get('ativo'));
                })
                ->when(filled($request->get('categoria')), function (Builder $query) use ($request) {
                    return $query->whereHas('categoria', function ($q) use ($request) {
                        $q->where('nome', 'like', '%' . $request->get('categoria') . '%');
                    });
                })
                ->when(filled($request->get('marca')), function (Builder $query) use ($request) {
                    return $query->where('marca', 'like', '%' . $request->get('marca') . '%');
                })
                ->when(filled($request->get('nome')), function (Builder $query) use ($request) {
                    return $query->where('nome', 'like', '%' . $request->get('nome') . '%');
                })
                ->when(filled($patrimonio), function (Builder $query) use ($patrimonio) {
                    return $query->where('patrimonio', 'like', '%' . $patrimonio . '%');
                })
                ->when(in_array($sort, $sortable), function (Builder $query) use ($sort, $direction) {
                    if ($sort === 'categoria') {
                        return $query->join('categorias', 'produtos.fk_categoria', '=', 'categorias.id')
                            ->orderBy('categorias.nome', $direction)
                            ->select('produtos.*');
                    } else {
                        return $query->orderBy($sort, $direction);
                    }
                })
                ->paginate(10)
                ->appends($request->all());

            return view('produtos/listarProdutos', compact('produtos', 'categorias', 'todasMarcas'));
        } catch (\Exception $e) {
            Log::error('Erro ao buscar produtos', [$e]);
            return back()->with('warning', 'Erro ao buscar produtos.');
        }
    }

    public function formProduto(Request $request)
    {

        //$this->authorize('autorizacao', 3);

        try {

            $categorias = Categoria::all();

            // $fontes = Fonte::all();
            $condicoes = Condicao::all();
            $tamanhos = Tamanho::all();

            $kits = Kit::all();
            $secoes = Secao::all();
            $unidadeUsuario = Unidade::find(Auth::user()->fk_unidade);

            return view('produtos/formProduto', compact('categorias', 'condicoes', 'kits', 'tamanhos', 'secoes', 'unidadeUsuario'));



        } catch (Exception $e) {
            Log::error('Error ao consultar produto', [$e]);
            return back()->with('warning', 'Houve um erro ao abrir Formulário');
        }
    }

    public function inserirProdutoForm(Request $request)
    {

        //$this->authorize('autorizacao', 3);

        try {

            $produtos = Produto::all();
            $unidades = Unidade::all();

            return view('produtos/entradaProdutos', compact('produtos', 'unidades'));



        } catch (Exception $e) {
            Log::error('Error ao consultar produto', [$e]);
            return back()->with('warning', 'Houve um erro ao abrir Formulário');
        }
    }

    public function formInserirProduto()
    {
        try {
            $categorias = Categoria::all();
            $unidades = Unidade::all();
            $unidadeUsuario = Unidade::find(Auth::user()->fk_unidade);
            return view('produtos.inserirProduto', compact('categorias', 'unidades', 'unidadeUsuario'));
        } catch (Exception $e) {
            Log::error('Erro ao carregar formulário de inserção de produto', [$e]);
            return back()->with('warning', 'Erro ao carregar o formulário de inserção de produto.');
        }
    }

    public function cadastrarProduto(Request $request)
    {
        try {
            $request->validate([
                'nome' => 'required|string|max:255',
                'categoria_id' => 'required|exists:categorias,id',
                'fk_secao' => 'nullable|exists:secaos,id',
            ]);

            DB::beginTransaction();

            // Verifica se o produto já existe
            $existeMesmoProduto = Produto::where('nome', $request->nome)
                ->where('tamanho', $request->tamanho)
                ->exists();

            if ($existeMesmoProduto) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Esse produto já existe nesse tamanho!');
            }

            // Cria o produto: unidade fixada à unidade do usuário
            $produto = Produto::create([
                'nome' => $request->nome,
                'descricao' => $request->descricao,
                'marca' => $request->marca,
                'tamanho' => $request->tamanho,
                'unidade' => Auth::user()->fk_unidade,
                'fk_categoria' => $request->categoria_id,
                'fk_secao' => $request->get('fk_secao'),
                'patrimonio' => $request->patrimonio,
                'ativo' => 'Y',
            ]);

            DB::commit();
            Log::info('Produto registrado com sucesso', ['produto_id' => $produto->id, 'usuario' => Auth::user()->cpf]);

            return redirect()->route('estoque.listar')->with('success', 'Produto cadastrado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao cadastrar produto', ['exception' => $e->getMessage()]);
            return back()->with('error', 'Erro ao cadastrar produto: ' . $e->getMessage());
        }
    }

    public function editarProduto(Request $request, $id)
    {
        try {

            $kits = Kit::all();
            $kit = Produto::find($id)->kit()->first();
            $unidades = Unidade::all();
            $condicoes = Condicao::all();
            $produto = Produto::find($id);
            $produtos = Produto::all();
            $categorias = Categoria::all();
            $tamanhos = Tamanho::all();
            $secoes = Secao::all();
            $unidadeUsuario = Unidade::find(Auth::user()->fk_unidade);

            //dd($tipoprodutos);

            return view('produtos/editarProduto', compact(
                'produto',
                'condicoes',
                'categorias',
                'unidades',
                'kits',
                'kit',
                'produtos',
                'tamanhos',
                'secoes',
                'unidadeUsuario'

            ));
        } catch (Exception $e) {
            Log::error('Error ao consultar Produto', [$e]);
            return back()->with('warning', 'Houve um erro ao consultar o Produto');
        }
    }

    public function atualizarProduto(Request $request, $id)
    {
      // dd($request->all());
        try {
            DB::beginTransaction();

            $request->validate([
                'nome' => 'required|string|max:255',
                'descricao' => 'nullable|string',
                'marca' => 'nullable|string',
                'categoria' => 'required|exists:categorias,id',
                'fk_secao' => 'nullable|exists:secaos,id',
            ]);



            // Verifica se o número já existe no banco de dados
            $existeMesmoProduto = Produto::where('nome', $request->get('nome'))
                ->where('tamanho', $request->get('tamanho'))
                ->where('id', '!=', $id)
                ->exists();

            if ($existeMesmoProduto) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Esse produto já existe nesse tamanho!');
            }




            Produto::where('id', $id)->update([
                'nome' => $request->get('nome'),
                'descricao' => $request->get('descricao'),
                'marca' => $request->get('marca'),
                'tamanho' => $request->get('tamanho'),
                'unidade' => Auth::user()->fk_unidade,
                'fk_kit' => $request->get('fk_kit'),
                'fk_categoria' => $request->get('categoria'),
                'fk_secao' => $request->get('fk_secao'),
            ]);

            DB::commit();

            Log::info('Produto atualizado com sucesso', [Produto::find($id), Auth::user()]);

            return redirect()->route('produto.ver', $id)->with('success', 'Produto atualizado com sucesso');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar o Produto', [$e]);
            return back()->with('warning', 'Erro ao atualizar o Produto');
        }
    }


    public function getProdutosPorUnidade($unidadeId)
    {
        $produtos = Itens_estoque::where('unidade', $unidadeId)
            ->where('quantidade', '>', 0)
            ->with('produto')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->produto->id,
                    'nome' => $item->produto->nome,
                    'quantidade' => $item->quantidade
                ];
            });

        return response()->json($produtos);
    }


    public function createEstoque()
    {
        $categorias = Categoria::all();
        $secoes = \App\Models\Secao::all();
        return view('produtos.cadastrar_produto_estoque', compact('categorias', 'secoes'));
    }

    public function storeEstoque(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'categoria_id' => 'required|exists:categorias,id',
            'quantidade' => 'required|integer|min:1',
        ]);
        DB::beginTransaction();
        try {
            $produto = Produto::create([
                'nome' => $request->nome,
                'descricao' => $request->descricao,
                'fk_categoria' => $request->categoria_id,
                'ativo' => 'Y',
            ]);
            Itens_estoque::create([
                'fk_produto' => $produto->id,
                'quantidade' => $request->quantidade,
                'lote' => $request->lote,
                'fk_secao' => $request->secao_id,
                'data_entrada' => now(),
            ]);
            DB::commit();
            return redirect()->route('produtos.estoque.create')->with('success', 'Produto cadastrado e adicionado ao estoque com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao cadastrar produto ou adicionar ao estoque.');
        }
    }
}
