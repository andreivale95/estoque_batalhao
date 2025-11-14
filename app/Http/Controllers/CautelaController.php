<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cautela;
use App\Models\CautelaProduto;
use App\Models\Produto;
use App\Models\Secao;

class CautelaController extends Controller
{
    public function create()
    {
        $secoes = Secao::all();
        return view('cautelas.create', compact('secoes'));
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
            'quantidades' => 'required|array',
        ]);

        $cautela = Cautela::create([
            'nome_responsavel' => $request->nome_responsavel,
            'telefone' => $request->telefone,
            'instituicao' => $request->instituicao,
            'data_cautela' => $request->data_cautela,
            'data_prevista_devolucao' => $request->data_prevista_devolucao,
        ]);

        foreach ($request->produtos as $index => $produtoId) {
            CautelaProduto::create([
                'cautela_id' => $cautela->id,
                'produto_id' => $produtoId,
                'quantidade' => $request->quantidades[$index],
            ]);
        }

        return redirect()->route('cautelas.create')->with('success', 'Cautela cadastrada com sucesso!');
    }
}