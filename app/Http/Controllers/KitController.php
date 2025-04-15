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


class KitController extends Controller
{

    public function listarKits()
    {
        $kits = Kit::all();
        return view('kits.listarKits', compact('kits'));
    }

    public function toggleDisponibilidade($id)
    {
        $kit = Kit::findOrFail($id);
        $kit->disponivel = $kit->disponivel === 'S' ? 'N' : 'S';
        $kit->save();

        return redirect()->back()->with('success', 'Disponibilidade do kit atualizada com sucesso!');
    }


    public function formKit()
    {
        $unidades = Unidade::all();
        $produtos = Produto::all();
        return view('kits.formKit', compact('produtos', 'unidades'));
    }

    public function editarKit($kit)
    {
        $kit = Kit::findOrFail($kit);
        $unidades = Unidade::all();

        return view('kits.editarKit', compact('unidades', 'kit'));
    }

    public function atualizarKit($kit)
    {
        try {

            $kit = Kit::where('id', $kit)->update([
                'nome' => request('nome'),
                'fk_unidade' => request('fk_unidade'),
                'descricao' => request('descricao'),
                'disponivel' => request('disponivel'),
            ]);


            return redirect()->route('kits.listar')->with('success', 'Kit atualizado com sucesso!');
        } catch (Exception $e) {
            Log::error('Erro ao atualizar Kit', [$e]);
            return back()->with('warning', 'Houve um erro ao atualiazr Kit.');
        }
    }

    public function criarKit(Request $request)
    {
        //dd($request->all());
        $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            // Criar o kit
              Kit::create([
                'nome' => $request->nome,
                'descricao' => $request->descricao,
                'fk_unidade' => 14,
            ]);


            DB::commit();
            return redirect()->route('kits.listar')->with('success', 'Kit criado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar kit: ', [$e]);
            return back()->with('warning', 'Erro ao criar kit.');
        }
    }

    public function deletarKit($kitId)
    {
        DB::beginTransaction();

        try {
            // Verifica se há produtos associados a esse kit
            $kitEmUso = Produto::where('fk_kit', $kitId)->exists();

            if ($kitEmUso) {
                return back()->with('warning', 'Este kit está associado a produtos e não pode ser deletado.');
            }

            // Deleta o kit
            Kit::where('id', $kitId)->delete();

            DB::commit();
            return redirect()->route('kits.listar')->with('success', 'Kit deletado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao deletar kit: ' . $e->getMessage(), ['exception' => $e]);

            return back()->with('warning', 'Ocorreu um erro ao tentar deletar o kit.');
        }
    }











}
