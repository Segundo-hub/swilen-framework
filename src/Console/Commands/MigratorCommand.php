<?php

namespace Swilen\Console\Commands;

use Swilen\Console\SwilenCommand;

class MigratorCommand extends SwilenCommand
{
    /**
     * @var string
     */
    protected $command = 'migrate';

    /**
     * Handle command
     *
     * @return void
     */
    public function handle()
    {
        $files = $this->getMigrationFiles();

        foreach ($files as $file) {
            include_once($file->name);

            $start = microtime(true);
            $this->Swilen->getOutput()->prepare(sprintf(' Migrating: %s', basename($file->name)), 'blue')->print();

            $method = key_exists('--rollback', $this->Swilen->getInput()->getFlags()) ? 'down' : 'up';

            $this->runMethod(new $file->class, $method);

            $this->Swilen->getOutput()->prepare(sprintf(' Migrated:  %s (%.5f sec)', basename($file->name), microtime(true) - $start), 'purple')->print();
        }
    }

    public function getMigrationFiles()
    {
        $files = [];
        foreach (glob($this->Swilen->path('app/storage/migrations/*.php')) as $value) {
            $files[] = (object) [
                'name' => $value,
                'class' =>  $this->getClassName($value)
            ];
        }

        return $files;
    }

    protected function runMethod(object $instance, string $method)
    {
        try {
            $instance->{$method}();
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    protected function getClassName(string $file)
    {
        $fp = fopen($file, 'r');
        $class = $buffer = '';
        $i = 0;
        while (!$class) {
            if (feof($fp)) break;

            $buffer .= fread($fp, 512);
            $tokens = token_get_all($buffer);

            if (strpos($buffer, '{') === false) continue;

            for (; $i < count($tokens); $i++) {
                if ($tokens[$i][0] === T_CLASS) {
                    for ($j = $i + 1; $j < count($tokens); $j++) {
                        if ($tokens[$j] === '{') {
                            $class = $tokens[$i + 2][1];
                        }
                    }
                }
            }
        }

        return str_replace('/', '', "\/" . $class);
    }
}
