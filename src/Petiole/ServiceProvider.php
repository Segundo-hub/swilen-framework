<?php

namespace Swilen\Petiole;

abstract class ServiceProvider
{
    /**
     * The application instance
     *
     * @var \Swilen\Contracts\Arthropod\Application
     */
    protected $app;

    /**
     * @param \Swilen\Contracts\Arthropod\Application $app
     *
     * @return void
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Public overridable function for register the dependencys or logic needed for boot this service
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
