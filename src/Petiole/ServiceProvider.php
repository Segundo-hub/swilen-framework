<?php

namespace Swilen\Petiole;

abstract class ServiceProvider
{
    /**
     * The application instance.
     *
     * @var \Swilen\Shared\Arthropod\Application
     */
    protected $app;

    /**
     * @param \Swilen\Shared\Arthropod\Application $app
     *
     * @return void
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Register services to application.
     *
     * @return void
     */
    public function register()
    {
    }
}
