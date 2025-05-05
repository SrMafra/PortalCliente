<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class VerifyApiKey
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
        // Verificar se a chave da API está configurada
        $apiKey = Config::get('erp.api_key');
        
        if (empty($apiKey)) {
            Log::error('API ERP: Chave de API não configurada');
            
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Não foi possível estabelecer conexão com o ERP. Chave API não configurada.'
                ], 503);
            }
            
            return redirect()->route('login')
                ->with('error', 'Não foi possível estabelecer conexão com o ERP. Entre em contato com o suporte.');
        }
        
        return $next($request);
    }
}