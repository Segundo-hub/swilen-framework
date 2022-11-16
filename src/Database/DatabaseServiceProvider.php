<?php

namespace Swilen\Database;

use Swilen\Petiole\ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * Register database singleton instance to service container.
     *
     * {@inheritdoc}
     */
    public function register()
    {
        $this->app->singleton('db', function ($app) {
            $config = $app->make('config')->get('database', []);

            return new Connection(function () use ($config) {
                return (new MySqlConnector())->connect($config);
            }, $config['schema'] ?? '', $config);
        });

        $this->app->singleton('db.connection', function ($app) {
            return $app['db']->getConnection();
        });
    }
}
