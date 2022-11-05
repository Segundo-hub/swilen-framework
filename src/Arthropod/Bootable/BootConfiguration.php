<?php

namespace Swilen\Arthropod\Bootable;

use Swilen\Arthropod\Application;
use Swilen\Arthropod\Contract\BootableServiceContract;
use Swilen\Config\ConfigContract;
use Swilen\Config\Repository;

class BootConfiguration implements BootableServiceContract
{
    /**
     * The application instance.
     *
     * @var \Swilen\Arthropod\Application
     */
    protected $app;

    /**
     * Application environment modes.
     *
     * @var string[]
     */
    protected $modes = ['development', 'production', 'test'];

    /**
     * @param \Swilen\Arthropod\Application $app
     *
     * @return void
     */
    public function puriyboot(Application $app)
    {
        $this->app = $app;

        $this->loadConfiguration();
    }

    /**
     * Create configuration repository.
     *
     * @return void
     */
    protected function loadConfiguration()
    {
        $this->app->instance('config', $config = new Repository((array) require $this->app['path.config']));

        if (!in_array($env = $config->get('app.env', 'production'), $this->modes, true)) {
            throw new \LogicException(sprintf('The "%s" is invalid key. Only accept: "%s".', $env, implode(', ', $this->modes)), 500);
        }

        $this->app->useEnvironment($env);

        $this->normalizePhpInternalConfig($config);
    }

    /**
     * Config internal php configuration.
     *
     * @param \Swilen\Config\ConfigContract $config
     *
     * @return void
     */
    protected function normalizePhpInternalConfig(ConfigContract $config)
    {
        date_default_timezone_set($config->get('app.timezone', 'UTC'));
        mb_internal_encoding('UTF-8');
    }
}
