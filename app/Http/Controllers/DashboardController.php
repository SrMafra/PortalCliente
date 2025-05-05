<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ErpApiService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Titulo;

class DashboardController extends Controller
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
     * Exibe o dashboard do cliente
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id ?? config('erp.empresa_padrao');

        // Verificar tipo de usuário
        if ($user->tipo === 'cliente') {
            return $this->dashboardCliente($user, $empresaId);
        } elseif ($user->tipo === 'admin' || $user->tipo === 'vendedor') {
            return $this->dashboardAdmin($user, $empresaId);
        }

        // Usuário com tipo não reconhecido
        return view('dashboard', ['error' => 'Tipo de usuário não reconhecido.']);
    }

    /**
     * Dashboard para clientes
     *
     * @param \App\Models\User $user
     * @param string $empresaId
     * @return \Illuminate\View\View
     */
    protected function dashboardCliente($user, $empresaId)
    {
        try {
            $clienteId = $user->cliente_id;

            // Se não tem ID de cliente, redireciona para completar cadastro
            if (empty($clienteId)) {
                return view('dashboard', [
                    'error' => 'Seu perfil de cliente não está completo. Por favor, contate o administrador.'
                ]);
            }

            // Buscar dados básicos do cliente
            $dadosCliente = $this->erpService->getCliente($clienteId, $empresaId);

            // Buscar títulos em aberto - filtrando apenas do cliente logado
            $params = [
                'codEmpresa' => $empresaId,
                'listaCliente' => $clienteId,  // Usar o ID do cliente atual
                'tipoTitulo' => 0 // Títulos em aberto
            ];

            $response = $this->erpService->getTitulosEmAberto($params, true);
            $titulos = isset($response['data']) ? $response['data'] : $response;

            // Filtrar explicitamente só para garantir (caso a API não respeite o parâmetro listaCliente)
            $titulos = collect($titulos)->filter(function ($titulo) use ($clienteId) {
                return (isset($titulo['codCliente']) && $titulo['codCliente'] == $clienteId) ||
                    (isset($titulo['cliente']) && $titulo['cliente'] == $clienteId);
            })->values()->all();

            // Buscar boletos pagos do mês atual
            $dataInicio = Carbon::now()->startOfMonth()->format('Y-m-d');
            $dataFim = Carbon::now()->endOfMonth()->format('Y-m-d');

            $responseBoletos = $this->erpService->getBoletosPagos($dataInicio, $dataFim, $empresaId, true);
            $boletosPagos = isset($responseBoletos['titulosPagos'])
                ? $responseBoletos['titulosPagos']
                : (isset($responseBoletos['data']) ? $responseBoletos['data'] : $responseBoletos);

            // Filtrar boletos pagos apenas do cliente atual
            $boletosPagos = collect($boletosPagos)->filter(function ($boleto) use ($clienteId, $user) {
                if (isset($boleto['codCliente']) && $boleto['codCliente'] == $clienteId) {
                    return true;
                }
                if (isset($boleto['cliente'])) {
                    if (is_numeric($boleto['cliente']) && $boleto['cliente'] == $clienteId) {
                        return true;
                    } elseif (is_string($boleto['cliente']) && stripos($boleto['cliente'], $user->name) !== false) {
                        return true;
                    }
                }
                return false;
            })->values()->all();

            // Calcular totais
            $hoje = Carbon::today();

            // Converter para objetos Titulo com informações de vencimento
            $titulosAbertos = collect($titulos)->map(function ($item) use ($hoje) {
                $titulo = new Titulo((array)$item);

                // Adicionar data de vencimento e calcular dias de vencimento
                if (!empty($item['dataVenc'])) {
                    $dataVenc = Carbon::parse($item['dataVenc']);
                    $titulo->dataVencimento = $dataVenc; // Garantir que dataVencimento esteja disponível
                    $titulo->diasVencimento = $hoje->diffInDays($dataVenc, false); // negativo se vencido
                    $titulo->vencido = $dataVenc->isPast();
                }

                return $titulo;
            })->take(5);

            $boletosPagosCollection = collect($boletosPagos)->map(function ($item) {
                return new Titulo((array)$item);
            })->take(5);

            // Calcular totais
            $totalEmAberto = collect($titulos)->sum(function ($item) {
                return isset($item['valorTitulo']) ? floatval($item['valorTitulo']) : 0;
            });

            $totalPago = collect($boletosPagos)->sum(function ($item) {
                return isset($item['vlrPago']) ? floatval($item['vlrPago']) : 0;
            });

            // Calcular boletos vencidos
            $titulosVencidos = collect($titulos)->filter(function ($item) use ($hoje) {
                if (empty($item['dataVenc'])) return false;
                $dataVenc = Carbon::parse($item['dataVenc']);
                return $dataVenc->lt($hoje);
            });

            $totalVencido = $titulosVencidos->sum(function ($item) {
                return isset($item['valorTitulo']) ? floatval($item['valorTitulo']) : 0;
            });

            // Calcular boletos a vencer em 30 dias
            $trintaDias = $hoje->copy()->addDays(30);
            $titulos30Dias = collect($titulos)->filter(function ($item) use ($hoje, $trintaDias) {
                if (empty($item['dataVenc'])) return false;
                $dataVenc = Carbon::parse($item['dataVenc']);
                return $dataVenc->gte($hoje) && $dataVenc->lte($trintaDias);
            });

            $totalAVencer30Dias = $titulos30Dias->sum(function ($item) {
                return isset($item['valorTitulo']) ? floatval($item['valorTitulo']) : 0;
            });

            return view('dashboard', [
                'cliente' => $dadosCliente,
                'titulosAbertos' => $titulosAbertos,
                'boletosPagos' => $boletosPagosCollection,
                'totalEmAberto' => $totalEmAberto,
                'totalPago' => $totalPago,
                'totalAVencer30Dias' => $totalAVencer30Dias,
                'totalVencido' => $totalVencido,
                'countTitulosAbertos' => count($titulos),
                'countBoletosPagos' => count($boletosPagos),
                'empresaId' => $empresaId,
                'clienteId' => $clienteId
            ]);
        } catch (\Exception $e) {
            return view('dashboard', [
                'error' => 'Não foi possível carregar os dados. Erro: ' . $e->getMessage(),
                'empresaId' => $empresaId,
                'clienteId' => $user->cliente_id
            ]);
        }
    }

    /**
     * Dashboard para administradores
     *
     * @param \App\Models\User $user
     * @param string $empresaId
     * @return \Illuminate\View\View
     */
    protected function dashboardAdmin($user, $empresaId)
    {
        // Para admin, exibe um dashboard com acesso a funcionalidades administrativas
        return view('dashboard_admin', [
            'empresaId' => $empresaId
        ]);
    }
}
