<?php

namespace Swilen\Arthropod\Bootable;

use Swilen\Arthropod\Application;
use Swilen\Arthropod\Contract\BootableServiceContract;
use Swilen\Config\ConfigContract;
use Swilen\Config\Repository;

class Configuration implements BootableServiceContract
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
     * Create new Load Configuration instance.
     *
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
        $configPath = $this->ensureConfigPath($this->app->make('path.config'));

        $this->app->instance('config', $config = new Repository((array) require $configPath));

        if (!in_array($env = $config->get('app.env', 'production'), $this->modes, true)) {
            throw new \LogicException(sprintf('The "%s" is invalid app env. Only accepts: "%s".', $env, implode(', ', $this->modes)), 500);
        }

        $this->app->useEnvironment($env);

        $this->normalizePhpInternalConfig($config);
    }

    /**
     * Validate config location path.
     *
     * @param string $config
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function ensureConfigPath(string $config = '')
    {
        if (file_exists($config = $config ?: $this->app->appPath('app.config.php'))) {
            return $config;
        }

        throw new \InvalidArgumentException('"config" not correctly resolve. Please check path', 500);
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
