<?php

namespace Swilen\Arthropod;

class ProviderRepository
{
    /**
     * The application instance.
     *
     * @var \Swilen\Arthropod\Application
     */
    protected $app;

    /**
     * @param \Swilen\Arthropod\Application $app
     *
     * @return void
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Load the application service providers.
     *
     * @param array $providers
     *
     * @return void
     */
    public function load(array $providers = [])
    {
        foreach ($providers as $provider) {
            $this->app->register($provider);
        }
    }
}
