<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Produto;
use App\Models\Categoria;
use App\Models\Unidade;

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
        return view('inventario.form', compact('categorias', 'unidades'));
    }

    public function salvar(Request $request)
    {
        $item = new Produto();
        $item->nome = $request->nome;
        $item->categoria_id = $request->categoria_id;
        $item->unidade_id = $request->unidade_id;
        $item->quantidade = 0;
        $item->save();
        return redirect()->route('inventario.index')->with('success', 'Item cadastrado com sucesso!');
    }
}
