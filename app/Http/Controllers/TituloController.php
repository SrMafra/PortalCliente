<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ErpApiService;
use App\Models\Titulo;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class TituloController extends Controller
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
     * Exibe os títulos em aberto do cliente
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function emAberto(Request $request)
    {
        try {
            $user = Auth::user();
            $empresaId = $user->empresa_id ?? config('erp.empresa_padrao');

            // Determinar o ID do cliente com base no usuário logado
            $clienteId = null;
            if ($user->tipo === 'cliente') {
                // Se for um cliente, usar seu próprio cliente_id
                $clienteId = $user->cliente_id;
            } else if ($user->tipo === 'admin' || $user->tipo === 'vendedor') {
                // Se for admin ou vendedor, permitir ver qualquer cliente especificado
                $clienteId = $request->input('cliente_id');

                if (!$clienteId) {
                    return redirect()->route('dashboard')
                        ->with('error', 'É necessário especificar um cliente.');
                }
            }

            // Definir a página atual
            $pagina = $request->input('pagina', 1);
            $itensPorPagina = 50; // Número de itens por página

            // Preparar parâmetros para a busca
            $params = [
                'codEmpresa' => $empresaId,
                'listaCliente' => $clienteId, // Usar listaCliente em vez de codCliente
                'tipoTitulo' => 0, // Títulos em aberto
                'paginacao' => $itensPorPagina // Configurar paginação
            ];

            // Adicionar token de continuação se estiver navegando pelas páginas
            if ($request->filled('continuation_token')) {
                $params['continuationToken'] = $request->input('continuation_token');
            }

            // Adicionar filtros de data se informados
            if ($request->filled('data_emissao_ini')) {
                $params['dataEmissaoIni'] = $request->input('data_emissao_ini');
            }

            if ($request->filled('data_emissao_fim')) {
                $params['dataEmissaoFim'] = $request->input('data_emissao_fim');
            }

            if ($request->filled('data_vencimento_ini')) {
                $params['dataVencimentoIni'] = $request->input('data_vencimento_ini');
            }

            if ($request->filled('data_vencimento_fim')) {
                $params['dataVencimentoFim'] = $request->input('data_vencimento_fim');
            }

            // Buscar títulos em aberto da API
            $response = $this->erpService->getTitulosEmAberto($params, true); // true para retornar a resposta completa com token

            // Extrair dados e token de continuação
            $titulos = $response['data'] ?? [];
            $continuationToken = $response['continuationToken'] ?? '';

            // Filtrar para garantir apenas títulos do cliente 
            $titulos = collect($titulos)->filter(function ($titulo) use ($clienteId) {
                return (isset($titulo['codCliente']) && $titulo['codCliente'] == $clienteId) ||
                    (isset($titulo['cliente']) && $titulo['cliente'] == $clienteId);
            })->values()->all();

            // Calcular os dias de vencimento
            $hoje = Carbon::today();

            // Converter resposta para objetos do modelo
            $titulosCollection = collect($titulos)->map(function ($item) use ($hoje) {
                $titulo = new Titulo((array)$item);

                // Adicionar data de vencimento e calcular dias de vencimento
                if (!empty($item['dataVenc'])) {
                    $dataVenc = Carbon::parse($item['dataVenc']);
                    $titulo->dataVencimento = $dataVenc;
                    $titulo->diasVencimento = $hoje->diffInDays($dataVenc, false); // negativo se vencido

                    // Definir status de vencimento
                    if ($dataVenc->isPast()) {
                        $titulo->vencido = true;
                        $titulo->situacao = 'Vencido há ' . abs($titulo->diasVencimento) . ' dia(s)';
                    } else {
                        $titulo->vencido = false;
                        $titulo->situacao = 'Vence em ' . $titulo->diasVencimento . ' dia(s)';
                    }
                } else {
                    $titulo->vencido = false;
                    $titulo->situacao = 'Sem data de vencimento';
                }

                return $titulo;
            });

            return view('titulos.em-aberto', [
                'titulos' => $titulosCollection,
                'empresaId' => $empresaId,
                'clienteId' => $clienteId,
                'filtros' => $request->only([
                    'data_emissao_ini',
                    'data_emissao_fim',
                    'data_vencimento_ini',
                    'data_vencimento_fim'
                ]),
                'continuationToken' => $continuationToken,
                'paginaAtual' => $pagina
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar títulos em aberto: ' . $e->getMessage());

            return view('titulos.em-aberto', [
                'error' => 'Não foi possível carregar os títulos em aberto. Tente novamente mais tarde.',
                'empresaId' => $empresaId ?? config('erp.empresa_padrao'),
                'clienteId' => $clienteId ?? null
            ]);
        }
    }

    /**
     * Exibe os boletos pagos do cliente
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function pagos(Request $request)
    {
        try {
            $user = Auth::user();
            $empresaId = $user->empresa_id ?? config('erp.empresa_padrao');

            // Verificar se o usuário é cliente ou admin/vendedor
            if ($user->tipo === 'cliente') {
                $clienteId = $user->cliente_id;
                $clienteNome = $user->name;
            } else {
                // Permitir que admin/vendedor visualize cliente específico
                $clienteId = $request->input('cliente_id');
                $clienteNome = null; // Poderia buscar o nome do cliente se necessário

                if (!$clienteId) {
                    return redirect()->route('dashboard')
                        ->with('error', 'É necessário especificar um cliente.');
                }
            }

            // Definir datas do filtro
            $dataInicio = $request->input('data_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $dataFim = $request->input('data_fim', Carbon::now()->endOfMonth()->format('Y-m-d'));

            // Buscar boletos pagos da API
            $response = $this->erpService->getBoletosPagos($dataInicio, $dataFim, $empresaId, true);

            // Extrair dados - considerar tanto o formato 'titulosPagos' quanto 'data'
            $boletos = isset($response['titulosPagos'])
                ? $response['titulosPagos']
                : (isset($response['data']) ? $response['data'] : $response);

            $continuationToken = $response['continuationToken'] ?? '';

            // Filtrar boletos apenas do cliente selecionado
            // Para isso, precisamos verificar tanto o ID quanto o nome do cliente
            $boletos = collect($boletos)->filter(function ($item) use ($clienteId, $clienteNome) {
                // Verificar por ID de cliente
                if (isset($item['codCliente']) && $item['codCliente'] == $clienteId) {
                    return true;
                }

                // Verificar por cliente que pode estar como ID ou nome
                if (isset($item['cliente'])) {
                    if (is_numeric($item['cliente']) && $item['cliente'] == $clienteId) {
                        return true;
                    } elseif (
                        is_string($item['cliente']) && $clienteNome &&
                        stripos($item['cliente'], $clienteNome) !== false
                    ) {
                        return true;
                    }
                }

                return false;
            });

            // Converter resposta para objetos do modelo
            $boletosCollection = $boletos->map(function ($item) {
                return new Titulo((array)$item);
            });

            return view('titulos.pagos', [
                'boletos' => $boletosCollection,
                'empresaId' => $empresaId,
                'clienteId' => $clienteId,
                'dataInicio' => $dataInicio,
                'dataFim' => $dataFim,
                'continuationToken' => $continuationToken
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar boletos pagos: ' . $e->getMessage());

            return view('titulos.pagos', [
                'error' => 'Não foi possível carregar os boletos pagos. Tente novamente mais tarde.',
                'empresaId' => $empresaId ?? config('erp.empresa_padrao'),
                'clienteId' => $clienteId ?? null,
                'dataInicio' => $dataInicio ?? Carbon::now()->startOfMonth()->format('Y-m-d'),
                'dataFim' => $dataFim ?? Carbon::now()->endOfMonth()->format('Y-m-d')
            ]);
        }
    }

    /**
     * Gera boleto para uma nota fiscal
     *
     * @param string $notaFiscal
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function gerarBoleto($notaFiscal, Request $request)
    {
        try {
            $user = Auth::user();
            $empresaId = $user->empresa_id ?? config('erp.empresa_padrao');

            // Verificar se o usuário é cliente ou admin/vendedor
            if ($user->tipo === 'cliente') {
                $cnpj = $user->documento;
                $clienteId = $user->cliente_id;
            } else {
                // Permitir que admin/vendedor gere boleto para cliente específico
                $clienteId = $request->input('cliente_id');
                $cnpj = $request->input('cnpj');

                if (!$clienteId || !$cnpj) {
                    return redirect()->route('titulos.em-aberto')
                        ->with('error', 'É necessário especificar um cliente e CNPJ.');
                }
            }

            // Remover formatação do CNPJ se houver
            $cnpj = preg_replace('/[^0-9]/', '', $cnpj);

            // Log para debug
            Log::debug('Solicitação de boleto', [
                'notaFiscal' => $notaFiscal,
                'cnpj' => $cnpj,
                'empresaId' => $empresaId
            ]);

            // Buscar boleto da API (retorna em base64)
            $boletoPdf = $this->erpService->gerarBoleto($notaFiscal, $cnpj, $empresaId);

            if (empty($boletoPdf)) {
                throw new \Exception("API retornou um boleto vazio");
            }

            // Nome do arquivo para download
            $fileName = "boleto_nf_{$notaFiscal}.pdf";

            // Log para debug
            Log::debug('Boleto gerado com sucesso', [
                'notaFiscal' => $notaFiscal,
                'tamanho' => strlen($boletoPdf)
            ]);

            // Retornar o PDF para download
            return Response::make(base64_decode($boletoPdf), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "inline; filename=\"{$fileName}\"",
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao gerar boleto: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Não foi possível gerar o boleto. Erro: ' . $e->getMessage());
        }
    }
}
