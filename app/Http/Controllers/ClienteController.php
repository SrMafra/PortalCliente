<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ErpApiService;
use App\Models\Cliente;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ClienteController extends Controller
{
    protected $erpService;
    
    /**
     * Construtor do Controller
     *
     * @param ErpApiService $erpService
     */
    public function __construct(ErpApiService $erpService)
    {
        $this->middleware('auth');
        $this->erpService = $erpService;
    }
    
    /**
     * Exibe os dados do cliente autenticado
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function show(Request $request)
    {
        try {
            $user = Auth::user();
            $empresaId = $user->empresa_id ?? config('erp.empresa_padrao');
            
            // Se for cliente, busca os dados do cliente logado
            if ($user->isCliente()) {
                $clienteId = $user->cliente_id;
                
                // Buscar dados atualizados da API
                $dadosCliente = $this->erpService->getCliente($clienteId, $empresaId);
                
                // Criar/atualizar o objeto Cliente com os dados da API
                $cliente = new Cliente($dadosCliente);
                
                return view('clientes.show', [
                    'cliente' => $cliente,
                    'empresaId' => $empresaId
                ]);
            }
            
            // Se não for cliente, não tem permissão para ver essa página
            return redirect()->route('dashboard')
                ->with('error', 'Você não tem permissão para acessar essa página.');
        } catch (\Exception $e) {
            Log::error('Erro ao buscar dados do cliente: ' . $e->getMessage());
            
            return view('clientes.show', [
                'error' => 'Não foi possível carregar os dados do cliente. Tente novamente mais tarde.',
                'empresaId' => $empresaId ?? config('erp.empresa_padrao')
            ]);
        }
    }
    
    /**
     * Exibe o cliente específico (para administradores)
     *
     * @param string $clienteId
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function showById($clienteId, Request $request)
    {
        try {
            $user = Auth::user();
            
            // Verificar se usuário tem permissão
            if (!$user->isAdmin() && !$user->isVendedor()) {
                return redirect()->route('dashboard')
                    ->with('error', 'Você não tem permissão para acessar os dados deste cliente.');
            }
            
            $empresaId = $request->input('empresa_id') ?? $user->empresa_id ?? config('erp.empresa_padrao');
            
            // Buscar dados atualizados da API
            $dadosCliente = $this->erpService->getCliente($clienteId, $empresaId);
            
            // Criar/atualizar o objeto Cliente com os dados da API
            $cliente = new Cliente($dadosCliente);
            
            return view('clientes.show', [
                'cliente' => $cliente,
                'empresaId' => $empresaId
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar dados do cliente: ' . $e->getMessage());
            
            return view('clientes.show', [
                'error' => 'Não foi possível carregar os dados do cliente. Tente novamente mais tarde.',
                'empresaId' => $empresaId ?? config('erp.empresa_padrao')
            ]);
        }
    }
}