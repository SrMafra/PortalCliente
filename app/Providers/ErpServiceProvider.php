<?php

namespace App\Providers;

use App\Services\ErpApiService;
use Illuminate\Support\ServiceProvider;

class ErpServiceProvider extends ServiceProvider
{
    /**
     * Registra o serviço ErpApiService no container de serviços.
     *
     * @return void
     */
    public function register()
    {
        // Registra o ErpApiService como um singleton
        // Isso significa que a mesma instância será usada em toda a aplicação
        $this->app->singleton(ErpApiService::class, function ($app) {
            return new ErpApiService();
        });
    }

    /**
     * Bootstrap de serviços.
     *
     * @return void
     */
    public function boot()
    {
        // Aqui você pode colocar código que deve ser executado 
        // após todos os outros provedores de serviço terem sido registrados
        // Por exemplo: registrar eventos, publicar arquivos de configuração, etc.
    }
}