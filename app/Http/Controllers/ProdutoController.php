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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class ProdutoController extends Controller
{



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

        try {
            $categorias = Categoria::all();
            $todasMarcas = Produto::select('marca')->distinct()->pluck('marca');

            $produtos = Produto::query()
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
                ->paginate(10);


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

            return view('produtos/formProduto', compact('categorias', 'condicoes', 'kits', 'tamanhos'));



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

    public function cadastrarProduto(Request $request)
    {

        try {

            DB::beginTransaction();


            // Converte valor formatado brasileiro ("1.500,00") para formato decimal ("1500.00")
            $valorBr = $request->get('valor'); // "250000"
            $valorFinal = ((float) $valorBr) / 100; // resultado: 250.00




            // Verifica se o número já existe no banco de dados
            $existeMesmoProduto = Produto::where('nome', $request->get('nome'))
                ->where('tamanho', $request->get('tamanho'))
                ->exists();

            if ($existeMesmoProduto) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Esse produto já existe nesse tamanho!');
            }

            // Se não existir, criar o produto
            $produto = Produto::create([
                'nome' => $request->get('nome'),
                'descricao' => $request->get('descricao'),
                'marca' => $request->get('marca'),
                'tamanho' => $request->get('tamanho'),
                'unidade' => $request->get('unidade'),
                'valor' => $valorFinal, // agora decimal válido
                'fk_categoria' => $request->get('categoria'),
                'fk_kit' => $request->get('kit'),
                'ativo' => 'Y',
            ]);

            DB::commit();
            Log::info('Produto registrado com sucesso', [$produto, Auth::user()->cpf]);

            return redirect()->route('produto.ver', $produto->id)->with('success', 'Produto registrado com sucesso');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error ao cadastrar novo Produto', [$e]);
            return back()->with('warning', 'Houve um erro ao registrar Produto');
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
            ]);



            $valorBr = $request->get('valor'); // "250000"
            $valorFinal = ((float) $valorBr) / 100; // resultado: 250.00
           // dd($valorFinal);



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
                'valor' => $valorFinal,
                'tamanho' => $request->get('tamanho'),
                'unidade' => $request->get('unidade'),
                'fk_kit' => $request->get('fk_kit'),
                'fk_categoria' => $request->get('categoria'),
                'ativo' => 'Y',
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


}
