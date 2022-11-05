<?php

namespace Swilen\Arthropod\Bootable;

use Swilen\Arthropod\Application;
use Swilen\Arthropod\Contract\BootableServiceContract;
use Swilen\Arthropod\Env;

class BootEnvironment implements BootableServiceContract
{
    /**
     * The application instance.
     *
     * @var \Swilen\Arthropod\Application
     */
    protected $app;

    /**
     * Env instance if defined or null by default.
     *
     * @var \Swilen\Arthropod\Env|null
     */
    protected static $instance;

    /**
     * @param \Swilen\Arthropod\Application $app
     *
     * @return void
     */
    public function puriyboot(Application $app)
    {
        $this->app = $app;

        $this->loadEnvironment();
    }

    /**
     * Create enviroment instance from factory.
     *
     * @return void
     */
    protected function loadEnvironment()
    {
        static::$instance instanceof Env
            ? static::$instance
            : Env::createFrom($this->app->environmentPath())->config([
                'file' => $this->app->environmentFile(),
            ])->load();
    }

    /**
     * Use custom enviromment instance from factory function.
     *
     * @param \Closure $callback
     *
     * @return void
     */
    public static function factory(\Closure $callback)
    {
        if (($instance = $callback()) && !$instance instanceof Env) {
            throw new \TypeError('The callback must return an instance of '.Env::class);
        }

        static::$instance = $instance;
    }

    /**
     * Use custom enviromment instance.
     *
     * @param \Swilen\Arthropod\Env $instance
     *
     * @return void
     */
    public static function use(Env $instance)
    {
        static::$instance = $instance;
    }
}
