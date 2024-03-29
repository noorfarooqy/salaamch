<?php

namespace Noorfarooqy\Salaamch;

use Illuminate\Support\ServiceProvider;

class SalaamchServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        $this->publishes([
            __DIR__ . '/../config/salaamch.php' => config_path('salaamch.php'),
        ], 'salaamch-config');
        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'salaamch-migrations');
    }
    public function register()
    {
        $this->app->register(\L5Swagger\L5SwaggerServiceProvider::class);
    }
}
