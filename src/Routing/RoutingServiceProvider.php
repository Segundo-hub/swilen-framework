<?php

namespace Swilen\Routing;

use Swilen\Http\Contract\ResponseContract;
use Swilen\Http\Response;
use Swilen\Petiole\Facades\Route;
use Swilen\Petiole\ServiceProvider;
use Swilen\Routing\Contract\ResponseFactory;
use Swilen\Routing\ResponseFactory as RoutingResponseFactory;

class RoutingServiceProvider extends ServiceProvider
{
    /**
     * Register routing base services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerRouter();

        $this->registerResponse();
    }

    /**
     * Register Router Manager.
     *
     * @return void
     */
    protected function registerRouter()
    {
        $this->app->singleton('router', function ($app) {
            return new Router($app);
        });
    }

    /**
     * Register Response.
     *
     * @return void
     */
    protected function registerResponse()
    {
        $this->app->bind('response', function () {
            return new Response();
        });

        $this->app->bind(ResponseContract::class, function () {
            return new Response();
        });

        $this->app->bind(ResponseFactory::class, function () {
            return new RoutingResponseFactory();
        });
    }

    public function boot()
    {
        Route::prefix('api')->group(app_path('app.routes.php'));
    }
}
