<?php

namespace App\Http\Controllers;


use App\Models\Itens_estoque;
use App\Models\Patrimonio;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;




class SiteController extends Controller
{
    public function dashboard(Request $request)
    {
        $this->authorize('autorizacao', 1);

        $podeVerOutrasUnidades = Gate::allows('autorizacao', 8);
        $unidadeId = Auth::user()->fk_unidade;

        try {
            $tudo = Itens_estoque::when(!$podeVerOutrasUnidades, function ($q) use ($unidadeId) {
                $q->where('unidade', $unidadeId);
            })->count();

            $cautelasPendentes = \App\Models\CautelaProduto::query()
                ->where('quantidade_devolvida', '<', DB::raw('quantidade'))
                ->when(!$podeVerOutrasUnidades, function ($q) use ($unidadeId) {
                    $q->whereHas('produto', function ($p) use ($unidadeId) {
                        $p->where('unidade', $unidadeId);
                    });
                })
                ->count();

            return view('dashboard', compact('tudo', 'cautelasPendentes'));
        } catch (Exception $e) {
            Log::error('Error ao consultar patrimonios', [$e]);
            return back()->with('warning', 'Houve um erro ao consultar patrimonios');
        }
    }

    //--------------------------------------------------------------

    public function Site(Request $request)
    {
        return view('auth/login');
    }



}
