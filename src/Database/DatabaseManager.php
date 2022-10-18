<?php

namespace Swilen\Database;

use Swilen\Database\Exception\DatabaseInvalidConfigFormat;

class DatabaseManager extends Connection
{
    /**
     * The app instance
     *
     * @var \Swilen\Arthropod\Application
     */
    protected $app;

    /**
     * Initialize Database Manager
     *
     * @param \Swilen\Arthropod\Application $app
     */
    public function __construct($app)
    {
        $this->app = $app;

        $this->bootstrap();
    }

    /**
     * Bootstrap database connection
     *
     * @return void
     */
    protected function bootstrap()
    {
        if ($config = app_config('database')) {
            parent::__construct($config);
        }

        throw new DatabaseInvalidConfigFormat(sprintf(
            'Invalid config format. Espect array with {username, password, host, schema|database} keys, found "%s"', gettype($config)
        ), 500);
    }
}
