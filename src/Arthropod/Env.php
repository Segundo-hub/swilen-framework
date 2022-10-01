<?php

namespace Swilen\Arthropod;

final class Env
{
    /**
     * The directory for load .env file
     *
     * @var string
     */
    protected $path;

    /**
     * The file name
     *
     * @var string
     */
    protected $filename = '.env';

    /**
     * List of env saved
     *
     * @var string[]
     */
    protected static $store = [];

    /**
     * @var bool
     */
    protected $isInmutable = true;

    /**
     * The stack variables resolved
     *
     * @var array
     */
    private static $envStack = [];

    /**
     * Define path and enviroments variables as inmutable
     *
     * @param string $path
     * @param bool $isInmutable
     *
     * @return $this
     */
    public function createFrom(string $path, bool $isInmutable = true)
    {
        $this->path = $path;
        $this->isInmutable = $isInmutable;

        return $this;
    }

    /**
     * Return path of env file
     *
     * @return string
     */
    public function path()
    {
        return $this->path . DIRECTORY_SEPARATOR . $this->filename;
    }

    /**
     * Config the enviroment needed configuation
     *
     * @param array $config
     *
     * @return $this
     */
    public function config(array $config)
    {
        $this->filename = $config['file'];
        if (isset($config['path'])) {
            $this->path = $config['path'];
        }
        if (isset($config['inmutable'])) {
            $this->isInmutable = (bool) $config['inmutable'];
        }

        return $this;
    }

    /**
     * Load variables from defined path
     *
     * @return $this
     */
    public function load()
    {
        if (!is_readable($this->path())) {
            throw new \RuntimeException('Env file is not readable ' . $this->path(), 200);
        }

        $linesFiltered = file($this->path(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($linesFiltered as $line) {

            if (strpos(trim($line), '#') === 0) continue;

            [$key, $value] = explode('=', $line, 2);

            $this->compile($key, $value);
        }

        return $this;
    }

    /**
     * Compile variables with variables replaced
     *
     * @param string $key
     * @param string|int|bool|null $value
     *
     * @return void
     */
    protected function compile(string $key, $value)
    {
        $key   = $this->formatKey($key);
        $value = $this->formatValue($value);

        static::$envStack[$key] = $value;

        if (preg_match_all('/\{(.[A-Z\_\-]+?)\}/', $value, $matches)) {
            foreach ($matches[0] as $match) {
                $name = $this->formatKey(trim($match, '{\}'));
                $value = str_replace($match, $this->wrapStack($name), $value);
                static::$envStack[$key] = $value;
            }
        }

        $this->write($key, $value);
    }

    /**
     * Format key and replace special characters
     *
     * @param string $key
     *
     * @return string
     */
    private function formatKey(string $key)
    {
        return str_replace(['-', '_'], '_', strtoupper(trim($key)));
    }

    /**
     * Format value and remove comments
     *
     * @param int|string|bool $value
     *
     * @return int|string|bool
     */
    private function formatValue($value)
    {
        if (is_null($value) || empty($value)) return '';

        if (($startComment = strpos($value, '#')) !== false) {
            $value = trim(substr($value, 0, $startComment));
        }

        return $this->parseToPrimitive($value);
    }

    /**
     * Parse values to php primitives
     *
     * @param string|int|bool $value
     */
    protected function parseToPrimitive($value)
    {
        $primitive = str_replace(['"', '\''], '', $value);

        if (in_array($primitive, [true, 'true', 'on', 1, '1'], true)) {
            return (bool) true;
        }

        if (in_array($primitive, [false, 'false', 'off', 0, '0'], true)) {
            return (bool) false;
        }

        if (is_numeric($primitive) && !$this->contains($value, ['+', '-', '"', '\''])) {
            return (int) $primitive;
        }

        return (string) $primitive;
    }

    /**
     * Find key into env stack and
     * return empty if value not exists
     *
     * @param string|int $key
     *
     * @return string
     */
    private function wrapStack($key)
    {
        return static::$envStack[$key] ?? '';
    }

    /**
     * Write value to env collection with mutability checked
     *
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    private function write(string $key, $value)
    {
        if (!$this->isInmutable()) {
            $this->writeMutableOrInmutable($key, $value);
        } else {
            if (!$this->exists($key)) {
                $this->writeMutableOrInmutable($key, $value);
            }
        }
    }

    /**
     * Write value to env collection, $_ENV and $_SERVER
     *
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    protected function writeMutableOrInmutable(string $key, $value)
    {
        putenv(sprintf('%s=%s', $key, $value));
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
        static::$store[$key] = $value;
    }

    /**
     * Check key exists into enn collection
     *
     * @param string|int $key
     *
     * @return bool
     */
    public function exists($key)
    {
        return (key_exists($key, $_SERVER) && key_exists($key, $_ENV) && key_exists($key, static::$store));
    }

    /**
     * Check if enviroment is inmutable
     *
     * @return bool
     */
    public function isInmutable()
    {
        return (bool) $this->isInmutable;
    }

    /**
     * Get value with keyed from stored env variables
     *
     * @param string|int $key
     * @param string|int|bool|null $default
     *
     * @return string|int|null
     */
    public static function get($key, $default = null)
    {
        $collection = static::all();

        return key_exists($key, $collection)
            ? $collection[$key]
            : $default;
    }

    /**
     * Return all env variables
     *
     * @return array
     */
    public static function all()
    {
        return array_merge($_ENV, $_SERVER, static::$store);
    }

    /**
     * @internal
     * Check string exists into string
     *
     * @param string $haystack
     * @param array|string $needle
     *
     * @return bool
     */
    protected function contains(string $haystack, $needle)
    {
        foreach ((array) $needle as $what) {
            if (strpos($haystack, $what) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * return array of variables values registered
     *
     * @return array<string, mixed>
     */
    public static function registered()
    {
        return static::$store;
    }

    /**
     * Return stack with variables resolved
     *
     * @return array<string, mixed>
     */
    public static function stack()
    {
        return static::$envStack;
    }
}
