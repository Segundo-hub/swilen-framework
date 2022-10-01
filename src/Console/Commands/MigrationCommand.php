<?php

namespace Swilen\Console\Commands;

use Swilen\Console\SwilenCommand;

class MigrationCommand extends SwilenCommand
{
    /**
     * @var string
     */
    protected $command = 'make:migration';

    /**
     * Handle command
     *
     * @return void
     */
    public function handle()
    {
        $migration = $this->migrationName();

        $handle = fopen($this->Swilen->path('app/storage/migrations/' . $migration->final), 'w+');

        @fwrite($handle, $this->getMigrateTemplate($migration->pascal));

        fclose($handle);

        $this->Swilen->getOutput()->prepare(sprintf(' 0 Created migration: %s ', $migration->final), 'blue')->print();
    }

    public function migrationName()
    {
        $commands = $this->Swilen->getInput()->getCommands();

        if (!$name = $commands[1]) {
            throw new \Error("Is necesary name for migration ", 1);
        }

        return (object) [
            'snake' => $this->stringToSnakeCase($name),
            'pascal' => $this->stringToPascalCase($name),
            'final' => date('Y_m_d_His_') . $this->stringToSnakeCase($name) . ".php"
        ];
    }

    protected function getMigrateTemplate(string $name)
    {
        $stub = fopen($this->Swilen->stub('migration.stub'), 'r');
        $template = str_replace('{{ className }}', $name, stream_get_contents($stub));

        fclose($stub);

        return $template;
    }

    public function stringToSnakeCase($input)
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
    }

    public function stringToPascalCase($input)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $input)));
    }
}
