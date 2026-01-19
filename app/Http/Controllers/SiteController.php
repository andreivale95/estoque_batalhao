<?php

namespace App\Http\Controllers;


use App\Models\Itens_estoque;
use App\Models\Patrimonio;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;




class SiteController extends Controller
{
    public function dashboard(Request $request)
    {
        $this->authorize('autorizacao', 1);


        if (Auth::user()->fk_unidade <> 14) {

            try {

                // Obtém todos os veículos da unidade do usuário autenticado
           //     $patrimonios = Itens_estoque::where('unidade', Auth::user()->fk_unidade)->pluck('id');


            //    $tudo = Itens_estoque::where('unidade', Auth::user()->fk_unidade)->count();

                // $caminhao = Veiculos::where('tipo', 5)->where('unidade', Auth::user()->fk_unidade)->count();

            //    $baixados = Itens_estoque::where('status', 'b')->where('unidade', Auth::user()->fk_unidade)->count();
             //   $aguardando = Itens_estoque::where('status', 'r')->where('unidade', Auth::user()->fk_unidade)->count();

             $tudo = Itens_estoque::all()->count();
             
             // Busca cautelas pendentes (com produtos não totalmente devolvidos)
             $cautelasPendentes = \App\Models\CautelaProduto::where('quantidade_devolvida', '<', \DB::raw('quantidade'))
                ->count();


            //  $baixados = Itens_estoque::where('status', 'b')->count();
             //$aguardando = Itens_estoque::where('status', 'r')->count();

             return view('dashboard', compact(

                 'tudo', 'cautelasPendentes'

             ));
            } catch (Exception $e) {
                Log::error('Error ao consultar patrimonios', [$e]);
                return back()->with('warning', 'Houve um erro ao consultar patrimonios');
            }
        } else {

            try {



                $tudo = Itens_estoque::all()->count();
                
                // Busca cautelas pendentes (com produtos não totalmente devolvidos)
                $cautelasPendentes = \App\Models\CautelaProduto::where('quantidade_devolvida', '<', \DB::raw('quantidade'))
                   ->count();


               //   $baixados = Itens_estoque::where('status', 'b')->count();
                //$aguardando = Itens_estoque::where('status', 'r')->count();

                return view('dashboard', compact(

                    'tudo', 'cautelasPendentes'

                ));
            } catch (Exception $e) {
                Log::error('Error ao consultar patrimonios', [$e]);
                return back()->with('warning', 'Houve um erro ao consultar patrimonios');
            }


        }
    }

    //--------------------------------------------------------------

    public function Site(Request $request)
    {
        return view('auth/login');
    }



}
