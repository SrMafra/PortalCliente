<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class LoginController extends Controller
{
    /**
     * Mostrar formulário de login
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Processar tentativa de login
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'remember' => 'boolean',
        ]);

        // Verificar se o usuário está ativo
        $user = User::where('email', $request->email)->first();
        
        if ($user && !$user->ativo) {
            throw ValidationException::withMessages([
                'email' => ['Esta conta está desativada. Entre em contato com o suporte.'],
            ]);
        }

        // Tentativa de login
        if (Auth::attempt([
            'email' => $request->email,
            'password' => $request->password,
        ], $request->filled('remember'))) {
            $request->session()->regenerate();
            
            // Registrar data/hora do login
            $user = Auth::user();
            $user->ultimo_login = now();
            $user->save();
            
            // Gerar novo token de API para acessar o ERP
            session(['erp_auth_token' => $user->createApiToken('erp_api')]);
            
            // Redirecionar para a página inicial ou para onde o usuário tentava acessar
            return redirect()->intended(route('dashboard'));
        }

        // Falha no login
        throw ValidationException::withMessages([
            'email' => [trans('auth.failed')],
        ]);
    }

    /**
     * Logout do usuário
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        // Revogar token de API
        if (Auth::check()) {
            Auth::user()->tokens()->delete();
        }
        
        // Fazer logout
        Auth::logout();
        
        // Invalidar a sessão
        $request->session()->invalidate();
        
        // Regenerar o token CSRF
        $request->session()->regenerateToken();
        
        // Redirecionar para a página de login
        return redirect()->route('login');
    }
}