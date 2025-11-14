<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Produto;
use App\Models\Categoria;
use App\Models\Unidade;
use App\Models\Secao;
use App\Models\Itens_estoque;

class InventarioController extends Controller
{
    public function index()
    {
        $itens = Produto::with(['categoria', 'unidade'])->get();
        return view('inventario.index', compact('itens'));
    }

    public function form()
    {
        $categorias = Categoria::all();
        $unidades = Unidade::all();
        $secoes = Secao::all();
        return view('inventario.form', compact('categorias', 'unidades', 'secoes'));
    }

    public function salvar(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'categoria_id' => 'nullable|integer|exists:categorias,id',
            'unidade_id' => 'nullable|integer|exists:unidades,id',
            'secao_id' => 'nullable|integer|exists:secaos,id',
            'quantidade_inicial' => 'nullable|integer|min:0',
        ]);

        // Criar produto
        $item = new Produto();
        $item->nome = $request->nome;
        // modelo Produto utiliza fk_categoria e unidade (campo 'unidade')
        if ($request->filled('categoria_id')) {
            $item->fk_categoria = $request->categoria_id;
        }
        if ($request->filled('unidade_id')) {
            $item->unidade = $request->unidade_id;
        }
        $item->save();

        // Se informar seção e quantidade inicial, criar registro em itens_estoque
        $qtd = intval($request->input('quantidade_inicial', 0));
        if ($request->filled('secao_id') && $qtd > 0) {
            Itens_estoque::create([
                'quantidade' => $qtd,
                'unidade' => $request->input('unidade_id'),
                'fk_secao' => $request->input('secao_id'),
                'data_entrada' => now(),
                'fk_produto' => $item->id,
            ]);
        }

        return redirect()->route('inventario.index')->with('success', 'Item cadastrado com sucesso!');
    }
}
