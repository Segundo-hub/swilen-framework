<?php

namespace Swilen\Database;

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
        $this->bootstrapConnection();
    }

    /**
     * Bootstrap database connection
     *
     * @return void
     */
    protected function bootstrapConnection()
    {
        $databaseConfig = app_config('database') ?? [];

        parent::__construct($databaseConfig);
    }
}
