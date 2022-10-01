<?php

namespace Swilen\Routing;

use Swilen\Http\ResponseFactory;
use Swilen\Http\Response;
use Swilen\Petiole\Facades\Route;
use Swilen\Petiole\ServiceProvider;

class RoutingServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->registerRouter();
        $this->registerResponse();
    }

    /**
     * Register Router Manager
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
     * Register Response
     *
     * @return void
     */
    protected function registerResponse()
    {
        $this->app->bind('response', function ($app) {
            return new Response();
        });

        $this->app->bind(ResponseFactory::class, function ($app) {
            return new ResponseFactory();
        });
    }

    public function boot()
    {
        Route::prefix('api')->group(app_path('app.routes.php'));
    }
}
