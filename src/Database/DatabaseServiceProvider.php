<?php

namespace Swilen\Database;

use Swilen\Database\DatabaseManager;
use Swilen\Petiole\ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * Register database singleton instance to service container
     *
     * {@inheritdoc}
     */
    public function register()
    {
        $this->registerDatabaseManager();
    }

    /**
     * Register database manager to Application
     *
     * @return void
     */
    protected function registerDatabaseManager()
    {
        $this->app->singleton('db', function ($app) {
            return new DatabaseManager($app);
        });
    }
}
