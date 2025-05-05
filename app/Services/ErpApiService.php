<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Exceptions\ApiException;
use Exception;

class ErpApiService
{
    protected $baseUrl;
    protected $timeout;
    protected $apiKey;
    protected $cacheEnabled;
    protected $cacheTtl;
    protected $endpoints;
    protected $empresaPadrao;

    /**
     * Construtor do serviço
     */
    public function __construct()
    {
        $this->baseUrl = config('erp.url');
        $this->timeout = config('erp.timeout');
        $this->apiKey = config('erp.api_key');
        $this->cacheEnabled = config('erp.cache.enabled');
        $this->cacheTtl = config('erp.cache.ttl');
        $this->endpoints = config('erp.endpoints');
        $this->empresaPadrao = config('erp.empresa_padrao');

        // Verificar se a chave API está configurada
        if (empty($this->apiKey)) {
            Log::warning('API ERP: Chave de API não configurada');
        }
    }

    /**
     * Obtém os dados do cliente pelo ID
     *
     * @param string $id ID do cliente
     * @param string|null $empresa Código da empresa
     * @return array
     * @throws ApiException
     */
    public function getCliente($id, $empresa = null)
    {
        $empresa = $empresa ?? $this->empresaPadrao;
        $endpoint = str_replace('{id}', $id, $this->endpoints['cliente']['show']);

        // Chave para cache
        $cacheKey = "cliente_{$empresa}_{$id}";

        // Verificar cache
        if ($this->cacheEnabled && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $response = $this->makeRequest('GET', $endpoint, [
            'empresa' => $empresa
        ]);

        // Armazenar em cache
        if ($this->cacheEnabled) {
            Cache::put($cacheKey, $response, $this->cacheTtl * 60);
        }

        return $response;
    }

    /**
     * Obtém lista de títulos em aberto
     *
     * @param array $params Parâmetros da consulta
     * @param bool $returnRaw Retornar resposta completa (com token de continuação)
     * @return array
     * @throws ApiException
     */
    public function getTitulosEmAberto($params = [], $returnRaw = false)
    {
        $endpoint = $this->endpoints['titulos']['lista'];

        // Garantir que busca apenas títulos em aberto
        $params['tipoTitulo'] = 0;

        // Chave para cache
        $cacheKey = 'titulos_' . md5(json_encode($params));

        // Verificar cache
        if ($this->cacheEnabled && Cache::has($cacheKey)) {
            $response = Cache::get($cacheKey);
            return $returnRaw ? $response : ($response['data'] ?? $response);
        }

        $response = $this->makeRequest('GET', $endpoint, $params);

        // Armazenar em cache (com TTL menor para títulos)
        if ($this->cacheEnabled) {
            Cache::put($cacheKey, $response, 15 * 60); // 15 minutos
        }

        // Retornar resposta completa ou apenas os dados
        return $returnRaw ? $response : ($response['data'] ?? $response);
    }

    /**
     * Gera boleto para uma nota fiscal
     *
     * @param string $notaFiscal Número da nota fiscal
     * @param string $cnpj CNPJ do cliente
     * @param string|null $empresa Código da empresa
     * @return string Base64 do PDF do boleto
     * @throws ApiException
     */
    public function gerarBoleto($notaFiscal, $cnpj, $empresa = null)
    {
        $empresa = $empresa ?? $this->empresaPadrao;
        $endpoint = str_replace('{notaFiscal}', $notaFiscal, $this->endpoints['titulos']['boleto']);

        try {
            // Fazer a requisição diretamente para ter mais controle
            $url = $this->baseUrl . $endpoint;

            $request = Http::timeout($this->timeout)
                ->withHeaders([
                    'accept' => 'application/json',
                    'empresa' => $empresa,
                    'Authorization' => $this->apiKey
                ])
                ->withoutVerifying(); // Desabilitar verificação SSL

            $response = $request->get($url, [
                'cliente' => $cnpj
            ]);

            // Log completo da resposta para debug
            Log::debug('Resposta da API de boleto', [
                'status' => $response->status(),
                'url' => $url,
                'notaFiscal' => $notaFiscal,
                'cliente' => $cnpj,
                'headers' => $response->headers(),
                'content_type' => $response->header('Content-Type'),
                'body' => $response->body() // Log do corpo completo para investigação
            ]);

            // Verificar status da resposta
            if ($response->failed()) {
                $statusCode = $response->status();
                $errorMsg = $response->json()['message'] ?? "Erro $statusCode";

                throw new ApiException("Falha ao gerar boleto: $errorMsg", $statusCode);
            }

            // Verificar o tipo de conteúdo para determinar como processar
            $contentType = $response->header('Content-Type');

            if (strpos($contentType, 'application/json') !== false) {
                // Se a resposta for JSON, verificar se há um campo com o PDF em base64
                $data = $response->json();

                // Log dos campos disponíveis no JSON
                Log::debug('Campos disponíveis no JSON da resposta:', [
                    'campos' => array_keys($data)
                ]);

                // Verificar vários campos possíveis onde o PDF pode estar
                $possibleFields = ['pdf', 'base64', 'content', 'documento', 'boleto', 'arquivo'];
                foreach ($possibleFields as $field) {
                    if (isset($data[$field]) && !empty($data[$field])) {
                        return $data[$field];
                    }
                }

                // Se não encontrar em campos específicos, procurar por qualquer campo com conteúdo base64 grande
                foreach ($data as $key => $value) {
                    if (is_string($value) && strlen($value) > 1000) {
                        // Provavelmente é o conteúdo do PDF em base64
                        return $value;
                    }
                }

                throw new ApiException("Resposta JSON não contém o PDF do boleto. Campos disponíveis: " . implode(", ", array_keys($data)));
            } else if (strpos($contentType, 'application/pdf') !== false) {
                // Se a resposta for diretamente um PDF, converter para base64
                return base64_encode($response->body());
            } else {
                // Assumir que o corpo da resposta já é o PDF em base64 ou o próprio PDF
                $body = $response->body();

                // Verificar se parece um base64 válido
                if (preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $body)) {
                    return $body; // Já é base64
                } else {
                    // Provavelmente é o PDF binário
                    return base64_encode($body);
                }
            }
        } catch (\Exception $e) {
            Log::error('Erro ao gerar boleto: ' . $e->getMessage(), [
                'notaFiscal' => $notaFiscal,
                'cliente' => $cnpj
            ]);
            throw new ApiException("Erro ao gerar boleto: " . $e->getMessage());
        }
    }

    /**
     * Obtém lista de boletos pagos
     *
     * @param string $dataInicio Data inicial (YYYY-MM-DD)
     * @param string $dataFim Data final (YYYY-MM-DD)
     * @param string|null $empresa Código da empresa
     * @param bool $returnRaw Retornar resposta completa (com token de continuação)
     * @return array
     * @throws ApiException
     */
    public function getBoletosPagos($dataInicio, $dataFim, $empresa = null, $returnRaw = false)
    {
        $empresa = $empresa ?? $this->empresaPadrao;
        $endpoint = $this->endpoints['titulos']['pagos'];

        // Chave para cache
        $cacheKey = "titulos_pagos_{$empresa}_{$dataInicio}_{$dataFim}";

        // Verificar cache
        if ($this->cacheEnabled && Cache::has($cacheKey)) {
            $response = Cache::get($cacheKey);
            // Se estiver no cache, retornar o formato correto
            if ($returnRaw) {
                return $response;
            } else {
                return $response['titulosPagos'] ?? $response['data'] ?? $response;
            }
        }

        $response = $this->makeRequest('GET', $endpoint, [
            'empresa' => $empresa,
            'dataInicio' => $dataInicio,
            'dataFim' => $dataFim,
            'paginacao' => 50 // Adicionar paginação
        ]);

        // Armazenar em cache
        if ($this->cacheEnabled) {
            Cache::put($cacheKey, $response, $this->cacheTtl * 60);
        }

        // Log para debug
        Log::debug('Resposta da API de boletos pagos', [
            'campos_disponiveis' => array_keys($response),
            'estrutura' => $response
        ]);

        // Retornar resposta completa ou apenas os dados
        if ($returnRaw) {
            return $response;
        } else {
            // Verificar os diferentes formatos possíveis da resposta
            if (isset($response['titulosPagos'])) {
                return $response['titulosPagos'];
            } elseif (isset($response['data'])) {
                return $response['data'];
            } else {
                return $response;
            }
        }
    }

    /**
     * Executa uma requisição para a API
     *
     * @param string $method Método HTTP
     * @param string $endpoint Endpoint da API
     * @param array $params Parâmetros da requisição
     * @param bool $parseJson Converter resposta para JSON
     * @return mixed
     * @throws ApiException
     */
    protected function makeRequest($method, $endpoint, $params = [], $parseJson = true)
    {
        try {
            $url = $this->baseUrl . $endpoint;

            // Configurar cabeçalhos conforme o padrão da API
            $request = Http::timeout($this->timeout)
                ->withHeaders([
                    'accept' => 'application/json',
                    'empresa' => $params['empresa'] ?? $this->empresaPadrao,
                    'Authorization' => $this->apiKey
                ]);

            // Remover 'empresa' dos parâmetros se estiver presente pois já foi enviado no cabeçalho
            if (isset($params['empresa'])) {
                unset($params['empresa']);
            }

            // Verificar SSL (importante em ambientes de desenvolvimento)
            if (str_starts_with($this->baseUrl, 'https://')) {
                if (!config('erp.ssl.verify', true)) {
                    $request->withoutVerifying();
                }
            }

            // Determinar se parâmetros vão na URL ou no corpo
            $response = null;
            if ($method === 'GET') {
                $response = $request->get($url, $params);
            } elseif ($method === 'POST') {
                $response = $request->post($url, $params);
            } elseif ($method === 'PUT') {
                $response = $request->put($url, $params);
            } elseif ($method === 'DELETE') {
                $response = $request->delete($url, $params);
            }

            if (!$response) {
                throw new ApiException("Método HTTP não suportado: $method");
            }

            // Log da resposta para debug se configurado
            if (config('erp.log.responses', false)) {
                Log::debug('API ERP Response', [
                    'url' => $url,
                    'method' => $method,
                    'status' => $response->status(),
                    'body' => substr($response->body(), 0, 500) . '...' // Limitar tamanho do log
                ]);
            }

            // Verificar se houve falha na requisição
            if ($response->failed()) {
                $statusCode = $response->status();
                $errorMsg = $response->json()['message'] ?? "Erro $statusCode";

                Log::error('Erro na API ERP', [
                    'url' => $url,
                    'status' => $statusCode,
                    'response' => substr($response->body(), 0, 500) . '...' // Limitar tamanho do log
                ]);

                throw new ApiException("Falha na requisição à API: $errorMsg", $statusCode);
            }

            // Retornar resposta conforme solicitado
            if ($parseJson) {
                return $response->json();
            }

            return $response->body();
        } catch (Exception $e) {
            Log::error('Erro ao fazer requisição para API do ERP: ' . $e->getMessage(), [
                'endpoint' => $endpoint,
                'method' => $method
            ]);
            throw new ApiException("Erro na comunicação com o ERP: " . $e->getMessage());
        }
    }
}
