<?php

namespace Swilen\Console\Commands;

use Swilen\Console\SwilenCommand;

class KeySecretCommand extends SwilenCommand
{
    /**
     * @var string
     */
    protected $command = 'key:generate';

    /**
     * Handle command
     *
     * @return void
     */
    public function handle()
    {
        $secret = $this->safeKey($this->generateKey(32), (bool) $this->inputOption('--safe'));

        $this->insertSecretKey($secret);
    }

    /**
     * Generate random key with defined length
     *
     * @param int $length
     * @return string
     */
    protected function generateKey(int $length = 32)
    {
        $max = ceil($length / 20);
        $random = '';

        for ($i = 0; $i < $max; $i++) {
            $random .= hash('SHA512', microtime(true) . random_int(10000, 90000));
        }

        return substr($random, 0, $length);
    }

    /**
     * Safe encode key with aditional security add
     *
     * @param string $key
     * @return string
     */
    protected function safeKey(string $key, bool $safe = true)
    {
        $key = base64_encode($key);

        return $safe ? 'swilen:' . rtrim($key, '=') : 'base64:' . $key;
    }

    /**
     * Insert secret key to env file
     *
     * @param string $secret
     */
    protected function insertSecretKey(string $secret)
    {
        file_put_contents($this->app->path('.env'),
            preg_replace('/APP_SECRET=(.*)/', 'APP_SECRET=' . $secret, file_get_contents($this->app->path('.env'))
        ));
    }
}
