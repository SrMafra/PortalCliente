<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ErpApiService
{
    protected $baseUrl;
    protected $apiToken;
    protected $cacheExpiration;
    
    public function __construct()
    {
        $this->baseUrl = env('ERP_API_BASE_URL');
        $this->apiToken = env('ERP_API_TOKEN');
        $this->cacheExpiration = env('ERP_API_CACHE_MINUTES', 60); // Padrão: 60 minutos
    }
    
    /**
     * Obtém títulos em aberto para um cliente
     *
     * @param int $clienteId
     * @param array $filtros
     * @return array
     */
    public function getTitulosEmAberto($clienteId, $filtros = [])
    {
        $cacheKey = "titulos_aberto_cliente_{$clienteId}_" . md5(json_encode($filtros));
        
        return Cache::remember($cacheKey, $this->cacheExpiration * 60, function () use ($clienteId, $filtros) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => "Bearer {$this->apiToken}",
                    'Accept' => 'application/json',
                ])->get("{$this->baseUrl}/clientes/{$clienteId}/titulos/abertos", $filtros);
                
                if ($response->successful()) {
                    return $response->json();
                } else {
                    Log::error('Erro ao obter títulos em aberto do ERP', [
                        'status' => $response->status(),
                        'response' => $response->body(),
                        'cliente_id' => $clienteId
                    ]);
                    return ['error' => true, 'message' => 'Não foi possível recuperar os títulos em aberto.'];
                }
            } catch (\Exception $e) {
                Log::error('Exceção ao conectar com a API do ERP', [
                    'message' => $e->getMessage(),
                    'cliente_id' => $clienteId
                ]);
                return ['error' => true, 'message' => 'Erro de conexão com o sistema ERP.'];
            }
        });
    }
    
    /**
     * Obtém títulos pagos para um cliente
     *
     * @param int $clienteId
     * @param array $filtros
     * @return array
     */
    public function getTitulosPagos($clienteId, $filtros = [])
    {
        $cacheKey = "titulos_pagos_cliente_{$clienteId}_" . md5(json_encode($filtros));
        
        return Cache::remember($cacheKey, $this->cacheExpiration * 60, function () use ($clienteId, $filtros) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => "Bearer {$this->apiToken}",
                    'Accept' => 'application/json',
                ])->get("{$this->baseUrl}/clientes/{$clienteId}/titulos/pagos", $filtros);
                
                if ($response->successful()) {
                    return $response->json();
                } else {
                    Log::error('Erro ao obter títulos pagos do ERP', [
                        'status' => $response->status(),
                        'response' => $response->body(),
                        'cliente_id' => $clienteId
                    ]);
                    return ['error' => true, 'message' => 'Não foi possível recuperar os títulos pagos.'];
                }
            } catch (\Exception $e) {
                Log::error('Exceção ao conectar com a API do ERP', [
                    'message' => $e->getMessage(),
                    'cliente_id' => $clienteId
                ]);
                return ['error' => true, 'message' => 'Erro de conexão com o sistema ERP.'];
            }
        });
    }
    
    /**
     * Obtém detalhes de um título específico
     *
     * @param int $clienteId
     * @param string $tituloId
     * @return array
     */
    public function getDetalhesTitulo($clienteId, $tituloId)
    {
        $cacheKey = "titulo_detalhe_{$clienteId}_{$tituloId}";
        
        return Cache::remember($cacheKey, $this->cacheExpiration * 60, function () use ($clienteId, $tituloId) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => "Bearer {$this->apiToken}",
                    'Accept' => 'application/json',
                ])->get("{$this->baseUrl}/clientes/{$clienteId}/titulos/{$tituloId}");
                
                if ($response->successful()) {
                    return $response->json();
                } else {
                    Log::error('Erro ao obter detalhes do título no ERP', [
                        'status' => $response->status(),
                        'response' => $response->body(),
                        'cliente_id' => $clienteId,
                        'titulo_id' => $tituloId
                    ]);
                    return ['error' => true, 'message' => 'Não foi possível recuperar os detalhes do título.'];
                }
            } catch (\Exception $e) {
                Log::error('Exceção ao conectar com a API do ERP', [
                    'message' => $e->getMessage(),
                    'cliente_id' => $clienteId,
                    'titulo_id' => $tituloId
                ]);
                return ['error' => true, 'message' => 'Erro de conexão com o sistema ERP.'];
            }
        });
    }
    
    /**
     * Obtém informações do cliente
     *
     * @param int $clienteId
     * @return array
     */
    public function getDadosCliente($clienteId)
    {
        $cacheKey = "dados_cliente_{$clienteId}";
        
        return Cache::remember($cacheKey, $this->cacheExpiration * 60, function () use ($clienteId) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => "Bearer {$this->apiToken}",
                    'Accept' => 'application/json',
                ])->get("{$this->baseUrl}/clientes/{$clienteId}");
                
                if ($response->successful()) {
                    return $response->json();
                } else {
                    Log::error('Erro ao obter dados do cliente no ERP', [
                        'status' => $response->status(),
                        'response' => $response->body(),
                        'cliente_id' => $clienteId
                    ]);
                    return ['error' => true, 'message' => 'Não foi possível recuperar os dados do cliente.'];
                }
            } catch (\Exception $e) {
                Log::error('Exceção ao conectar com a API do ERP', [
                    'message' => $e->getMessage(),
                    'cliente_id' => $clienteId
                ]);
                return ['error' => true, 'message' => 'Erro de conexão com o sistema ERP.'];
            }
        });
    }
    
    /**
     * Obtém link de boleto para um título
     *
     * @param int $clienteId
     * @param string $tituloId
     * @return string|null
     */
    public function getLinkBoleto($clienteId, $tituloId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiToken}",
                'Accept' => 'application/json',
            ])->get("{$this->baseUrl}/clientes/{$clienteId}/titulos/{$tituloId}/boleto");
            
            if ($response->successful()) {
                $data = $response->json();
                return $data['link_boleto'] ?? null;
            } else {
                Log::error('Erro ao obter link do boleto no ERP', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'cliente_id' => $clienteId,
                    'titulo_id' => $tituloId
                ]);
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Exceção ao conectar com a API do ERP', [
                'message' => $e->getMessage(),
                'cliente_id' => $clienteId,
                'titulo_id' => $tituloId
            ]);
            return null;
        }
    }
    
    /**
     * Limpa o cache relacionado a um cliente específico
     *
     * @param int $clienteId
     */
    public function limparCacheCliente($clienteId)
    {
        $cacheKeys = [
            "dados_cliente_{$clienteId}",
            "titulos_aberto_cliente_{$clienteId}_*",
            "titulos_pagos_cliente_{$clienteId}_*",
            "titulo_detalhe_{$clienteId}_*"
        ];
        
        foreach ($cacheKeys as $pattern) {
            if (strpos($pattern, '*') !== false) {
                $this->limparCachePorPadrao($pattern);
            } else {
                Cache::forget($pattern);
            }
        }
    }
    
    /**
     * Método auxiliar para limpar cache usando padrões com wildcard
     *
     * @param string $pattern
     */
    protected function limparCachePorPadrao($pattern)
    {
        // Implementação básica que funciona para cache baseado em arquivos
        // Para outros drivers, pode ser necessário implementar de forma diferente
        if (Cache::getStore() instanceof \Illuminate\Cache\FileStore) {
            $pattern = str_replace('*', '', $pattern);
            $files = glob(storage_path('framework/cache/*'));
            
            foreach ($files as $file) {
                if (strpos($file, $pattern) !== false) {
                    @unlink($file);
                }
            }
        }
    }
}