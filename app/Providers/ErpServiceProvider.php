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
        // Código para inicialização do serviço, se necessário
    }
}