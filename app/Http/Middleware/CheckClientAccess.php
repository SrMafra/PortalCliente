<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckClientAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Verificar se o usuário está autenticado
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        
        $user = Auth::user();
        
        // Verificar se o usuário está ativo
        if (!$user->ativo) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Sua conta está desativada. Entre em contato com o suporte.'], 403);
            }
            
            return redirect()->route('login')
                ->with('error', 'Sua conta está desativada. Entre em contato com o suporte.');
        }
        
        // Verificar se o usuário é um cliente com ID de cliente
        if ($user->isCliente() && empty($user->cliente_id)) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Sua conta não está corretamente vinculada a um cliente. Entre em contato com o suporte.'], 403);
            }
            
            return redirect()->route('login')
                ->with('error', 'Sua conta não está corretamente vinculada a um cliente. Entre em contato com o suporte.');
        }
        
        // Se o ID do cliente estiver na rota ou nos parâmetros da requisição
        $clienteId = $request->route('cliente_id') ?? $request->input('cliente_id');
        
        if ($clienteId) {
            // Se for cliente, só pode acessar os próprios dados
            if ($user->isCliente() && $user->cliente_id != $clienteId) {
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'Você não tem permissão para acessar os dados deste cliente.'], 403);
                }
                
                return redirect()->route('dashboard')
                    ->with('error', 'Você não tem permissão para acessar os dados deste cliente.');
            }
        }
        
        return $next($request);
    }
}