<?php

namespace Swilen\Arthropod;

use Swilen\Routing\RoutingServiceProvider;
use Swilen\Database\DatabaseServiceProvider;
use Swilen\Http\Request;
use Swilen\Petiole\Facades\Facade;
use Swilen\Pipeline\Pipeline;
use Swilen\Container\Container;
use Swilen\Contracts\Arthropod\Application as ArthropodApplication;

class Application extends Container implements ArthropodApplication
{
    /**
     * The Swilen current version
     *
     * @var string
     */
    public const VERSION = '0.1.0-dev';

    /**
     * Indicates if the application has been bootstrapped before.
     *
     * @var bool
     */
    protected $hasBeenBootstrapped = false;

    /**
     * Indicates if the application has booted.
     *
     * @var bool
     */
    protected $booted = false;

    /**
     * The bootstrappers collection
     *
     * @var \Swilen\Arthropod\Contracts\BootableContract[]
     */
    protected $bootstrappers = [
        \Swilen\Arthropod\Bootable\BootEnviromment::class,
        \Swilen\Arthropod\Bootable\BootFacades::class,
        \Swilen\Arthropod\Bootable\BootProviders::class
    ];

    /**
     * Resolved service provider collection for boot
     *
     * @var \Swilen\Petiole\ServiceProvider[]
     */
    protected $serviceProviders = [];

    /**
     * The application base path
     *
     * @var string
     */
    protected $basePath;

    /**
     * The application app path
     *
     * @var string
     */
    protected $appPath = 'app';

    /**
     * The application config path
     *
     * @var string
     */
    protected $configPath;

    /**
     * The application base uri
     *
     * @var string
     */
    protected $appUri;

    /**
     * Create application instance and boot necessary packages for dispatch incoming request.
     *
     * @param string $path Define base path for your application.
     *
     * @return void
     */
    public function __construct(string $path = '')
    {
        $this->configureCoreApplication($path);
        $this->registerBaseBindings();
        $this->registerServiceProviders();
        $this->registerCoreContainerAliases();
    }

    /**
     * Return version of Swilen
     *
     * @return string
     */
    public function version()
    {
        return static::VERSION;
    }

    /**
     * Bootstrap core php configuration and insert paths into container
     *
     * @param string $path
     *
     * @return void
     */
    protected function configureCoreApplication(string $path)
    {
        $this->registerExceptionHandler();

        $this->defineBasePath($path);
    }

    /**
     * Register core exception handler
     *
     * @return \Swilen\Arthropod\Exception\CoreExceptionHandler
     */
    private function registerExceptionHandler()
    {
        return new \Swilen\Arthropod\Exception\CoreExceptionHandler($this);
    }

    /**
     * Register base container bindings
     *
     * @return void
     */
    private function registerBaseBindings()
    {
        static::setInstance($this);

        $this->instance('app', $this);

        $this->instance(Container::class, $this);

        $this->instance('config', require_once($this->make('path.config')));
    }

    /**
     * Register base service providers
     *
     * @return void
     */
    private function registerServiceProviders()
    {
        $this->register(new RoutingServiceProvider($this));
        $this->register(new DatabaseServiceProvider($this));
    }

    /**
     * Create and define Application base path
     *
     * @param string $basePath
     *
     * @return $this
     */
    private function defineBasePath($basePath)
    {
        $this->basePath = rtrim($basePath, '\/');

        $this->registerApplicationPaths();

        return $this;
    }

    /**
     * Register application paths
     *
     * @return void
     */
    private function registerApplicationPaths()
    {
        $this->instance('path', $this->basePath());
        $this->instance('path.app', $this->appPath());
        $this->instance('path.config', $this->configPath());
    }

    /**
     * Application paths part
     *
     * @param string $path
     *
     * @return string
     */
    public function basePath(string $path = '')
    {
        return $this->basePath . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Set application paths part
     *
     * @param string $path
     *
     * @return $this
     */
    public function useBasePath(string $path = '')
    {
        $this->basePath = $path;

        $this->instance('path', $path);

        return $this;
    }

    /**
     * Application paths part
     *
     * @param string $path
     *
     * @return string
     */
    public function appPath(string $path = '')
    {
        return $this->basePath() . DIRECTORY_SEPARATOR . $this->appPath . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Application paths part
     *
     * @param string $path
     *
     * @return $this
     */
    public function useAppPath(string $path = '')
    {
        $this->appPath = $path;

        $this->instance('path.app', $path);

        return $this;
    }

    /**
     * Application paths part
     *
     * @param string $path
     *
     * @return string
     */
    public function configPath(string $path = '')
    {
        return $this->guardConfigPath($path);
    }

    /**
     * Application paths part
     *
     * @param string $path
     *
     * @return $this
     */
    public function useConfigPath(string $path = '')
    {
        $this->configPath = $this->guardConfigPath($path);

        $this->instance('path.config', $this->configPath);

        return $this;
    }

    private function guardConfigPath(string $config = '')
    {
        $config = $config ? $config : 'app.config.php';

        if (file_exists($this->appPath($config))) {
            return $this->appPath($config);
        }

        throw new \InvalidArgumentException(
            "{$config} filename not found or not correctly resolve. Please check path " . $this->appPath(),
            500
        );
    }

    /**
     * Return application base uri
     *
     * @param string $path
     *
     * @return string
     */
    public function appUri(string $path = '')
    {
        return $this->appUri . ($path ? "/$path" : "");
    }

    /**
     * Replace application uri provided from param
     *
     * @param string $uri
     *
     * @return $this
     */
    public function useAppUri(string $path = '')
    {
        $this->appUri = $path;

        return $this;
    }

    /**
     * Initial register service providers
     *
     * @param mixed $provider
     */
    public function register($provider)
    {
        $provider->register();

        $this->serviceProviders[] = $provider;

        return $provider;
    }

    /**
     * Bootsatrap the application with packages implimenting BootableContract
     *
     * @return void
     */
    protected function bootstrap()
    {
        if ($this->hasBeenBootstrapped()) return;

        foreach ($this->bootstrappers as $bootstrap) {
            $this->make($bootstrap)->puriyboot($this);
        }

        $this->hasBeenBootstrapped = true;
    }

    /**
     * Determine if the application has been bootstrapped before.
     *
     * @return bool
     */
    public function hasBeenBootstrapped()
    {
        return $this->hasBeenBootstrapped === true;
    }

    /**
     * Boot application with boot method into service containers
     *
     * @return void
     */
    public function boot()
    {
        if ($this->isBooted()) return;

        foreach ($this->serviceProviders as $provider) {
            if (method_exists($provider, 'boot')) {
                $this->call([$provider, 'boot']);
            }
        }

        $this->booted = true;
    }

    /**
     * Verify if the application is booted
     *
     * @return bool
     */
    public function isBooted()
    {
        return $this->booted === true;
    }

    /**
     * Verify the application is development mode
     *
     * @return bool
     */
    public function isDevelopmentMode()
    {
        return (bool) env('APP_ENV', 'development') === 'development';
    }

    /**
     * Verify the application is debug mode
     *
     * @return bool
     */
    public function isDebugMode()
    {
        return (bool) env('APP_DEBUG', true);
    }


    /**
     * Dispatch request and listen http router
     *
     * @param \Swilen\Http\Request $request
     * @return \Swilen\Http\Response
     */
    public function dispatch(Request $request)
    {
        try {
            $response = $this->dispatchRequestThroughRouter($request);
        } catch (\Throwable $e) {
            throw $e;
        }

        return $response->terminate();
    }

    /**
     * Handle for dispatch request for route
     *
     * @param \Swilen\Http\Request $request
     * @return \Swilen\Http\Response
     */
    protected function dispatchRequestThroughRouter(Request $request)
    {
        $this->instance('request', $request);

        Facade::flushFacadeInstance('request');

        $this->bootstrap();

        return (new Pipeline($this))
            ->from($request)
            ->through([])
            ->then(function ($request) {
                return $this['router']->dispatch($request);
            });
    }

    /**
     * Register core aliases into container
     *
     * @return void
     */
    protected function registerCoreContainerAliases()
    {
        foreach ([
            'app' => \Swilen\Arthropod\Application::class,
            'request' => \Swilen\Http\Request::class,
            'response' => \Swilen\Http\Response::class
        ] as $key => $value) {
            $this->alias($key, $value);
        }
    }
}
