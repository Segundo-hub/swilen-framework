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
     * Overwritable public function to register the dependencies or logic needed to start this service.
     *
     * @return void
     */
    public function register()
    {
    }
}
