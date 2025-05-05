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
        $query = HistoricoMovimentacao::with(['produto', 'origem', 'destino'])->where('fk_unidade', Auth::user()->fk_unidade);

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
        if ($request->filled('militar')) {
            $query->where('militar', 'like', '%' . $request->militar . '%');
        }

        if ($request->filled('fonte')) {
            $query->where('fonte', 'like', '%' . $request->fonte . '%');
        }

        if ($request->filled('estoque')) {
            $query->whereHas('unidade', function ($q) use ($request) {
                $q->where('nome', 'like', '%' . $request->estoque . '%');
            });
        }

        if ($request->filled('sei')) {
            $query->where('sei', 'like', '%' . $request->sei . '%');
        }


        $movimentacoes = $query->orderByDesc('data_movimentacao')->paginate(20);

        $produtos = Produto::all();

        return view('movimentacoes.index', compact('movimentacoes', 'produtos'));
    }












}
