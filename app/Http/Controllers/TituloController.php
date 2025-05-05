<?php

namespace App\Http\Controllers;

use App\Services\ErpApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TitulosController extends Controller
{
    protected $erpApiService;
    
    /**
     * Construtor do controlador com injeção de dependência
     */
    public function __construct(ErpApiService $erpApiService)
    {
        $this->erpApiService = $erpApiService;
    }
    
    /**
     * Exibe a lista de títulos em aberto
     */
    public function emAberto(Request $request)
    {
        $clienteId = Auth::user()->cliente_id;
        
        // Preparar filtros baseados nos parâmetros da requisição
        $filtros = [];
        
        if ($request->has('data_inicio')) {
            $filtros['data_inicio'] = $request->input('data_inicio');
        }
        
        if ($request->has('data_fim')) {
            $filtros['data_fim'] = $request->input('data_fim');
        }
        
        if ($request->has('valor_minimo')) {
            $filtros['valor_minimo'] = $this->formatarValorMonetario($request->input('valor_minimo'));
        }
        
        if ($request->has('valor_maximo')) {
            $filtros['valor_maximo'] = $this->formatarValorMonetario($request->input('valor_maximo'));
        }
        
        // Buscar títulos da API do ERP
        $responseData = $this->erpApiService->getTitulosEmAberto($clienteId, $filtros);
        
        // Verificar se há erros na resposta
        if (isset($responseData['error']) && $responseData['error']) {
            return view('titulos.em-aberto', [
                'titulos' => collect([]),
                'erro' => $responseData['message']
            ]);
        }
        
        // Transformar dados da API em collection paginada
        $titulos = $this->transformarTitulosParaCollection($responseData);
        
        return view('titulos.em-aberto', [
            'titulos' => $titulos
        ]);
    }
    
    /**
     * Exibe a lista de títulos pagos
     */
    public function pagos(Request $request)
    {
        $clienteId = Auth::user()->cliente_id;
        
        // Preparar filtros baseados nos parâmetros da requisição
        $filtros = [];
        
        if ($request->has('data_inicio')) {
            $filtros['data_inicio'] = $request->input('data_inicio');
        }
        
        if ($request->has('data_fim')) {
            $filtros['data_fim'] = $request->input('data_fim');
        }
        
        if ($request->has('valor_minimo')) {
            $filtros['valor_minimo'] = $this->formatarValorMonetario($request->input('valor_minimo'));
        }
        
        if ($request->has('valor_maximo')) {
            $filtros['valor_maximo'] = $this->formatarValorMonetario($request->input('valor_maximo'));
        }
        
        // Buscar títulos da API do ERP
        $responseData = $this->erpApiService->getTitulosPagos($clienteId, $filtros);
        
        // Verificar se há erros na resposta
        if (isset($responseData['error']) && $responseData['error']) {
            return view('titulos.pagos', [
                'titulos' => collect([]),
                'erro' => $responseData['message']
            ]);
        }
        
        // Transformar dados da API em collection paginada
        $titulos = $this->transformarTitulosParaCollection($responseData);
        
        return view('titulos.pagos', [
            'titulos' => $titulos
        ]);
    }
    
    /**
     * Exibe detalhes de um título específico
     */
    public function visualizar($id)
    {
        $clienteId = Auth::user()->cliente_id;
        
        // Buscar detalhes do título da API do ERP
        $titulo = $this->erpApiService->getDetalhesTitulo($clienteId, $id);
        
        // Verificar se há erros na resposta
        if (isset($titulo['error']) && $titulo['error']) {
            return redirect()->route('titulos.em-aberto')
                ->with('error', $titulo['message']);
        }
        
        return view('titulos.visualizar', [
            'titulo' => $titulo
        ]);
    }
    
    /**
     * Redireciona para o download do boleto
     */
    public function downloadBoleto($id)
    {
        $clienteId = Auth::user()->cliente_id;
        
        // Buscar link do boleto da API do ERP
        $linkBoleto = $this->erpApiService->getLinkBoleto($clienteId, $id);
        
        if (!$linkBoleto) {
            return redirect()->back()
                ->with('error', 'Não foi possível gerar o boleto para este título.');
        }
        
        // Redirecionar para o link do boleto
        return redirect()->away($linkBoleto);
    }
    
    /**
     * Redireciona para página de pagamento
     */
    public function pagar($id)
    {
        $clienteId = Auth::user()->cliente_id;
        
        // Buscar detalhes do título da API do ERP
        $titulo = $this->erpApiService->getDetalhesTitulo($clienteId, $id);
        
        // Verificar se há erros na resposta
        if (isset($titulo['error']) && $titulo['error']) {
            return redirect()->route('titulos.em-aberto')
                ->with('error', $titulo['message']);
        }
        
        return view('titulos.pagar', [
            'titulo' => $titulo
        ]);
    }
    
    /**
     * Formata valor monetário para o formato correto
     */
    protected function formatarValorMonetario($valor)
    {
        // Remover formatação (pontos e vírgulas)
        $valor = str_replace('.', '', $valor);
        $valor = str_replace(',', '.', $valor);
        
        return (float) $valor;
    }
    
    /**
     * Transforma dados da API em collection paginada
     */
    protected function transformarTitulosParaCollection($responseData)
    {
        // Criar collection a partir dos dados
        $items = collect($responseData['titulos'] ?? []);
        
        // Aplicar paginação manual
        $page = request()->get('page', 1);
        $perPage = 10;
        
        $items = $items->map(function ($item) {
            // Converter strings de data para objetos Carbon
            if (isset($item['data_emissao'])) {
                $item['data_emissao'] = Carbon::parse($item['data_emissao']);
            }
            
            if (isset($item['data_vencimento'])) {
                $item['data_vencimento'] = Carbon::parse($item['data_vencimento']);
            }
            
            if (isset($item['data_pagamento'])) {
                $item['data_pagamento'] = Carbon::parse($item['data_pagamento']);
            }
            
            return (object) $item;
        });
        
        // Criar paginador
        $paginatedItems = new \Illuminate\Pagination\LengthAwarePaginator(
            $items->forPage($page, $perPage),
            $items->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
        
        return $paginatedItems;
    }
}