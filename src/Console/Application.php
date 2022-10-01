<?php

namespace Swilen\Console;

use Swilen\Console\Input\ArgvInput;
use Swilen\Console\Output\ConsoleOutput;
use Swilen\Container\Container;
use Swilen\Database\DatabaseServiceProvider;
use Swilen\Petiole\Facades\Facade;

final class Application extends Container
{
    /**
     * @var \Swilen\Console\Input\ArgvInput
     */
    private $input;

    /**
     * @var \Swilen\Console\Output\ConsoleOutput
     */
    private $output;

    protected $basePath;

    protected $stubPath;

    protected $commands = [
        \Swilen\Console\Commands\MigrationCommand::class,
        \Swilen\Console\Commands\MigratorCommand::class,
    ];

    protected $booted = [];

    public function __construct(string $path)
    {
        $this->basePath = rtrim($path, '\/');
        $this->stubPath = rtrim(dirname(__FILE__) . DIRECTORY_SEPARATOR . "Stubs", '\/');
        $this->bootstrap();
        set_exception_handler(function (\Throwable $th) {
            $errorLength = strlen(get_class($th) . ": " . $th->getMessage());
            $message = PHP_EOL .
                " " . str_pad('-', $errorLength + 4, '-') . " " . PHP_EOL .
                " | " . get_class($th) . ": " . $th->getMessage() . " | " . PHP_EOL .
                " " . str_pad('-', $errorLength + 4, '-') . " ";

            (new ConsoleOutput)->prepare($message, 'light_gray', 'red')->print();
        });
    }

    /**
     * Execute current commad
     *
     * @param \Swilen\Console\Input\ArgvInput $input
     * @param \Swilen\Console\Output\ConsoleOutput $output
     *
     * @return int
     */
    public function exec(ArgvInput $input, ConsoleOutput $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->handle();

        return 0;
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
        $this->instance('config', require_once($this->path('app/app.config.php')));

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

    public function path($path = '')
    {
        return $this->basePath . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }

    public function stub(string $name)
    {
        return $this->stubPath . ($name ? DIRECTORY_SEPARATOR . $name : '');
    }

    public function writeTimeExecuted()
    {
        $this->output->printExecutionTime();
    }

    public function getInput()
    {
        return $this->input;
    }

    public function getOutput()
    {
        return $this->output;
    }
}
