<?php
// config/erp.php

return [
    /*
    |--------------------------------------------------------------------------
    | Configurações da API do ERP
    |--------------------------------------------------------------------------
    |
    | Configurações para conexão com a API do sistema ERP.
    |
    */

    'url' => env('ERP_API_URL', 'https://10.10.10.5/api'),
    
    'timeout' => env('ERP_API_TIMEOUT', 30),
    
    'empresa_padrao' => env('ERP_EMPRESA_PADRAO', '1'),
    
    // Chave de API para autenticação
    'api_key' => env('ERP_API_KEY', 'eyJhbGciOiJFUzI1NiJ9.eyJpc3MiOiJhcGkiLCJhdWQiOiJhcGkiLCJleHAiOjE5MDM3MjAyOTQsInN1YiI6IkFOVE9OSU8iLCJjc3dUb2tlbiI6IkhGc3BDRFE5IiwiZGJOYW1lU3BhY2UiOiJjb25zaXN0ZW0ifQ.qCTXKraoeKh51JlDYIJnpl0SC24fF4oirXJgzDnOhnmSAK6XkVpWrn3SP0TZKsuYfSMH8KITAigG1fXvmA2wIQ'),
    
    // Cache
    'cache' => [
        'enabled' => env('ERP_API_CACHE_ENABLED', true),
        'ttl' => env('ERP_API_CACHE_TTL', 60), // Tempo em minutos
    ],
    
    // Endpoints da API
    'endpoints' => [
        'cliente' => [
            'show' => '/cadastrosgerais/v10/cliente/{id}',
        ],
        'titulos' => [
            'lista' => '/financeiro/v10/contasReceber',
            'boleto' => '/financeiro/v10/boletoNota/{notaFiscal}',
            'pagos' => '/financeiro/v10/titulosPagos',
        ],
    ],
    
    // Opções de SSL
    'ssl' => [
        // Se deve verificar certificado SSL em ambiente de desenvolvimento
        'verify' => env('ERP_API_SSL_VERIFY', false),
    ],
    
    // Opções de log
    'log' => [
        // Se deve registrar todas as requisições
        'requests' => env('ERP_API_LOG_REQUESTS', false),
        // Se deve registrar todas as respostas
        'responses' => env('ERP_API_LOG_RESPONSES', false),
    ],
];