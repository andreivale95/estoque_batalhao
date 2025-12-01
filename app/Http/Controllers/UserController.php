<?php
namespace App\Http\Controllers;

use App\Models\Unidade;
use App\Models\User;
use App\Models\Perfil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function excluirUserInterno(Request $request, $cpf)
    {
        $this->authorize('autorizacao', 4);
        try {
            DB::beginTransaction();
            $user = User::where('cpf', $cpf)->firstOrFail();
            $user->delete();
            DB::commit();
            Log::info('Usuário excluído', [Auth::user(), $user]);
            return redirect()->route('usi.listar')->with('success', 'Usuário excluído com sucesso.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro ao excluir usuário', [$e]);
            return back()->with('warning', 'Houve um erro ao excluir o usuário');
        }
    }
    public function listarUsersInternos(Request $request)
    {
        $this->authorize('autorizacao', 4);
        $perfis = Perfil::all();
        try {
            $users = User::query()
                ->when(filled($request->get('search')), function ($query) use ($request) {
                    return $query
                        ->where('nome', 'like', '%' . $request->search . '%')
                        ->orWhere('cpf', 'like', '%' . $request->search . '%')
                        ->orWhere('sobrenome', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%');
                })
                ->when(filled($request->get('perfil')), function ($query) use ($request) {
                    return $query->where('fk_perfil', $request->perfil);
                })
                ->paginate(10);
            return view('profile/user/listarUsersInternos', compact('users', 'perfis'));
        } catch (Exception $e) {
            Log::error('Erro ao consultar usuarios', [$e]);
            return back()->with('warning', 'Houve um erro ao consultar o usuario');
        }
    }


    public function verUserInterno(Request $request, string $id)
    {


        $this->authorize('autorizacao', 4);

        try {
            $user = User::where('cpf', $id)->first();
            return view('profile/user/verUserInterno', compact('user'));
        } catch (Exception $e) {
            Log::error('Erro ao consultar usuario', [$e]);
            return back()->with('warning', 'Houve um erro ao consultar o usuario');
        }
    }

    public function criarUserInterno(Request $request)
    {

        //dd($request);


        $this->authorize('autorizacao', 4);

        try {
            // Validação dos dados
            $request->validate([
                'nome' => 'required|string|max:255',
                'sobrenome' => 'required|string|max:255',
                'cpf' => 'required|string|size:14|unique:users,cpf',
                'email' => 'required|email|max:255|unique:users,email',
                'telefone' => 'required|string|max:20',
                'unidade' => 'required|exists:unidades,id',
                'fk_perfil' => 'required|exists:perfis,id_perfil',
                'password' => 'required|string|min:6|confirmed',
            ], [
                'email.required' => 'O campo email é obrigatório.',
                'email.email' => 'Formato de email inválido.',
                'email.unique' => 'Este email já está cadastrado.',
                'cpf.unique' => 'Este CPF já está cadastrado.',
                'password.confirmed' => 'As senhas não coincidem.',
            ]);

            DB::beginTransaction();

            $cpf = str_replace(['.', '-'], '', $request->get('cpf'));

            $u = User::create([
                'nome' => $request->get('nome'),
                'sobrenome' => $request->get('sobrenome'),
                'cpf' => $cpf,
                'email' => $request->get('email'),
                'telefone' => $request->get('telefone'),
                'fk_unidade' => $request->get('unidade'),
                'status' => 's',
                'password' => $request->get('password'),
                'fk_perfil' => $request->get('fk_perfil'),
                'email_verified_at' => date('Y-m-d h:m:s')
            ]);

            DB::commit();
            Log::info('Usuário criado', [Auth::user(), $u]);
            return redirect()->route('usi.ver', $cpf)->with('success', 'Cadastrado com Sucesso.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar usuario', [$e]);
            return back()->with('warning', 'Houve um erro na criação do usuário');
        }
    }

    public function formUserInterno(Request $request)
    {

        $this->authorize('autorizacao', 4);

        try {
            $perfis = Perfil::where('status', 's')->get();

            $unidades = Unidade::all();

            return view('profile/user/formUserInterno', compact('perfis', 'unidades'));
        } catch (Exception $e) {
            Log::error('Erro ao consultar usuario', [$e]);
            return back()->with('warning', 'Houve um erro ao consultar o usuario');
        }
    }

    public function editarUserInterno(Request $request, string $id)
    {


        $this->authorize('autorizacao', 4);

        try {
            $user = User::where('cpf', $id)->first();
            $perfis = Perfil::where('status', 's')->get();
            $unidades = Unidade::all();

            return view('profile/user/editarUserInterno', compact('user', 'perfis', 'unidades'));
        } catch (Exception $e) {
            Log::error('Erro ao consultar usuario', [$e]);
            return back()->with('warning', 'Houve um erro ao consultar o usuario');
        }
    }

    public function atualizarUserInterno(Request $request, string $id)
    {

        $this->authorize('autorizacao', 4);

        try {
            DB::beginTransaction();

            $u = User::query()
                ->where('cpf', $id)
                ->update([
                    'nome' => $request->get('nome'),
                    'sobrenome' => $request->get('sobrenome'),
                    'email' => $request->get('email'),
                    'telefone' => $request->get('telefone'),
                    'fk_unidade' => $request->get('unidade'),
                    'status' => $request->get('status'),

                    'fk_perfil' => $request->get('perfil')
                ]);

            DB::commit();
            Log::info('Usuário atualizado', [Auth::user(), $u]);
            return redirect()->route('usi.ver', $id)->with('success', 'atualizado com Sucesso.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar usuario', [$e]);
            return back()->with('warning', 'Houve um erro ao atualizar o  usuário');
        }
    }



}
