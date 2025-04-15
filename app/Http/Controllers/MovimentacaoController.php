<?php

namespace App\Http\Controllers;



use App\Models\Itens_estoque;
use App\Models\Unidade;
use App\Models\Produto;
use App\Models\Kit;
use App\Models\KitProduto;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\HistoricoMovimentacao;
use Illuminate\Support\Facades\Auth;


class MovimentacaoController extends Controller
{

    public function index(Request $request)
    {
        $query = HistoricoMovimentacao::with(['produto', 'origem', 'destino']);

        if ($request->filled('produto')) {
            $query->where('fk_produto', $request->produto);
        }

        if ($request->filled('tipo')) {
            $query->where('tipo_movimentacao', $request->tipo);
        }

        if ($request->filled('data_inicio')) {
            $query->whereDate('data_movimentacao', '>=', $request->data_inicio);
        }

        if ($request->filled('data_fim')) {
            $query->whereDate('data_movimentacao', '<=', $request->data_fim);
        }

        if ($request->filled('responsavel')) {
            $query->where('responsavel', 'like', '%' . $request->responsavel . '%');
        }

        $movimentacoes = $query->orderByDesc('data_movimentacao')->paginate(20);

        $produtos = Produto::all();

        return view('movimentacoes.index', compact('movimentacoes', 'produtos'));
    }












}
