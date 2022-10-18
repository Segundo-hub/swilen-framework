<?php

namespace Swilen\Arthropod\Bootable;

use Swilen\Arthropod\Application;
use Swilen\Arthropod\Contract\BootableContract;
use Swilen\Arthropod\Env;

class BootEnviromment implements BootableContract
{
    /**
     * The application instance
     *
     * @var \Swilen\Arthropod\Application
     */
    protected $app;

    /**
     * Env instance if defined or null by default
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

        $this->factoryEnviromment();
    }

    /**
     * Create enviroment instance from factory
     *
     * @return void
     */
    protected function factoryEnviromment()
    {
        $objectInstance = static::$instance instanceof Env
            ? static::$instance
            : (new Env())->config([
                'file' => $this->app->enviromentFile(),
                'path' => $this->app->enviromentPath()
            ])->load();

        $this->app->instance('env', $objectInstance);
    }

    /**
     * Use custom enviromment instance
     *
     * @param \Closure $callback
     *
     * @return void
     */
    public static function factory(\Closure $callback)
    {
        static::$instance = $callback();
    }

    /**
     * Use custom enviromment instance
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
