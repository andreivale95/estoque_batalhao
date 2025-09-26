<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth/login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request): RedirectResponse
    {


        $credentials = $request->validate([
            'cpf' => 'required|max:14|min:14',
            'password' => 'required|min:3|max:255'
        ], [
            'password.required' => 'O campo da senha é obrigatório.',
            'password.min' => 'A senha deve ter no mínimo 6 caracteres.',
            'password.max' => 'A senha não pode exceder 255 caracteres.',
            'cpf.required' => 'O CPF é obrigatório.',
            'cpf.min' => 'O CPF deve ter no mínimo 14 caracteres.',
            'cpf.max' => 'O CPF não pode exceder a 14 caracteres.',
        ]);


        $credentials['cpf'] = str_replace(['.', '-'], '', $credentials['cpf']);

        $user = User::where('cpf', $credentials['cpf'])->first();

        if (!$user) {
            return back()->withErrors([
                'cpf' => 'Cpf ou senha não conferem.',
            ])->onlyInput('cpf');
        }

        if ($user->status == 'n')
            return back()->withErrors(['cpf' => 'Esse usuário está inativo, contate o administrador do sistema para ativalo']);

        // Verifica se o perfil do usuário possui permissões
        $perfil = $user->perfil()->get()->first();
        if (!$perfil || $perfil->permissoes()->count() == 0) {
            return back()->withErrors([
                'cpf' => 'Seu perfil não possui permissões cadastradas. Contate o administrador.'
            ]);
        }

        if (Auth::attempt($credentials)) {
            if (isset($request['remember'])&&!empty($request['remember']))  {
                setcookie("cpf", $request['cpf'], time()+3600);
                setcookie("password", $request['password'], time()+3600);
            }else{
                setcookie("cpf", "");
                setcookie("password", "");
            }
            $request->session()->regenerate();
            Log::info('Usuário logado', [$user]);
            return redirect()->intended('dashboard')->with('success', 'Seja Bem-Vindo'.'‎ '.  $user->nome);
        }

        return back()->withErrors([
            'cpf' => 'Cpf ou senha não conferem.',
        ])->onlyInput('cpf');
    }
    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Log::info('Usuário deslogado', [Auth::user()]);

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();


        return redirect('/login');
    }
}
