<?php

namespace Swilen\Console;

use Swilen\Console\Input\ArgvInput;
use Swilen\Console\Output\ConsoleOutput;
use Swilen\Container\Container;
use Swilen\Contracts\Console\Application as ConsoleApplication;
use Swilen\Database\DatabaseServiceProvider;
use Swilen\Petiole\Facades\Facade;

class Application extends Container implements ConsoleApplication
{
    /**
     * @var \Swilen\Console\Input\ArgvInput
     */
    private $input;

    /**
     * @var \Swilen\Console\Output\ConsoleOutput
     */
    private $output;

    /**
     * @var int
     */
    protected $exitCode = 0;

    /**
     * @var string
     */
    protected $basePath;

    /**
     * @var string
     */
    protected $stubPath;

    protected $commands = [
        \Swilen\Console\Commands\MigrationCommand::class,
        \Swilen\Console\Commands\MigratorCommand::class,
        \Swilen\Console\Commands\KeySecretCommand::class,
    ];

    protected $booted = [];

    public function __construct(string $path)
    {
        $this->output = new ConsoleOutput();

        $this->definePaths($path);
        $this->configureExceptionHandler();
        $this->bootstrap();
    }

    protected function definePaths(string $path)
    {
        $this->basePath = rtrim($path, '\/');
        $this->stubPath = dirname(__FILE__) . DIRECTORY_SEPARATOR . "Stubs";
    }

    public function appPath(string $path = '')
    {

    }

    public function basePath(string $path = '')
    {

    }

    /**
     * Execute current commad
     *
     * @param \Swilen\Console\Input\ArgvInput $input
     *
     * @return int
     */
    public function exec(ArgvInput $input)
    {
        $this->input = $input;
        $this->handle();

        return $this->getCode();
    }

    protected function handle()
    {
        $this->output->prepare(PHP_EOL)->print();
        $command = $this->input->getCommand();
        if (key_exists($command, $this->booted)) {

            $object = $this->booted[$command];

            $this->call([$object, 'handle']);
        } else {
            $this->output->prepare(' Not commad found by ' . $command)->print();
        }
    }

    public function bootstrap()
    {
        $this->instance('config', require_once($this->configPath()));

        static::setInstance($this);

        $service = new DatabaseServiceProvider($this);
        $service->register();
        Facade::setFacadeApplication($this);

        foreach ($this->commands as $command) {
            /** @var \Swilen\Console\SwilenCommand */
            $instance = new $command($this);

            $this->booted[$instance->getCommand()] = $instance;
        }
    }

    /**
     * Configure application exception
     */
    protected function configureExceptionHandler()
    {
        set_exception_handler(function (\Throwable $th) {
            $message = get_class($th) . ": " . $th->getMessage();
            $stringPadding = str_pad('-', strlen($message) + 4, '-');

            $finalMessage = PHP_EOL .
                " " . $stringPadding . " " . PHP_EOL .
                " | " . $message . " | " . PHP_EOL .
                " " . $stringPadding . " ";

            (new ConsoleOutput)->prepare($finalMessage, 'light_gray', 'red')->print();
        });
    }

    public function configPath()
    {
        return $this->path('app/app.config.php');
    }

    public function path($path = '')
    {
        return $this->basePath . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }

    public function stub(string $name)
    {
        return $this->stubPath . ($name ? DIRECTORY_SEPARATOR . $name : '');
    }

    /**
     * Terminate Console Application
     *
     * @return int
     */
    public function terminate()
    {
        $this->output->terminateSwilenScript();

        return $this->getCode();
    }

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->exitCode ?: 0;
    }

    public function getInput()
    {
        return $this->input;
    }

    public function input()
    {
        return $this->input;
    }

    public function getOutput()
    {
        return $this->output;
    }
}
