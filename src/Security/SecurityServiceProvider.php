<?php

namespace Swilen\Security;

use Swilen\Petiole\ServiceProvider;
use Swilen\Security\Contract\JwtService;
use Swilen\Security\Token\Jwt;

class SecurityServiceProvider extends ServiceProvider
{
    /**
     * Register token manager into container.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(JwtService::class, function ($app) {
            return Jwt::register($app->make('config')->get('app.secret', ''), [
                'algorithm' => 'HS512',
                'expires' => '120s',
            ]);
        });
    }
}
