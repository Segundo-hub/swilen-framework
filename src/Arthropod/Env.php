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
    protected static $envs = [];

    /**
     * List of all env saved
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
     * The env instance as singleton
     *
     * @var static
     */
    protected static $instance;

    /**
     * Create new env instance
     *
     * @param string $path
     * @param bool $isInmutable
     *
     * @return void
     */
    public function __construct(string $path = null, bool $isInmutable = true)
    {
        $this->path = $path;
        $this->isInmutable = $isInmutable;
    }

    /**
     * Define path and enviroments variables as inmutable
     *
     * @param string $path
     * @param bool $isInmutable
     *
     * @return $this
     */
    public static function createFrom(string $path, bool $isInmutable = true)
    {
        return new static($path, $isInmutable);
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

        static::$instance = $this;

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
     * @param bool $replace
     *
     * @return void
     */
    protected function compile(string $key, $value, bool $replace = false)
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

        $this->write($key, $value, $replace);
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
        if (is_bool($value)) return $value;

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

        if ($this->startWith($primitive, 'swilen:')) {
            return (string) base64_decode(substr($primitive, 7) . '=');
        }

        if ($this->startWith($primitive, 'base64:')) {
            return (string) base64_decode(substr($primitive, 7));
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
     * @param bool $replace
     *
     * @return void
     */
    private function write(string $key, $value, bool $replace = false)
    {
        if (!$this->isInmutable() || $replace) {
            $this->writeMutableOrInmutable($key, $value);
            return;
        }

        if (!$this->exists($key)) {
            $this->writeMutableOrInmutable($key, $value);
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
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
        static::$envs[$key] = $value;
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
        return (key_exists($key, $_SERVER) && key_exists($key, $_ENV) && key_exists($key, static::$envs));
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
        if (!empty(static::$store)) {
            return static::$store;
        }

        return static::$store = array_merge($_ENV, $_SERVER, static::$envs);
    }

    /**
     * Force refilling store collection
     */
    protected function refillingStore()
    {
        static::$store = array_merge($_ENV, $_SERVER, static::$envs);
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
     * @internal
     * Check string starts with
     *
     * @param string $haystack
     * @param array|string $needle
     *
     * @return bool
     */
    protected function startWith(string $haystack, $needle)
    {
        return \strpos($haystack, $needle) === 0;
    }

    /**
     * Return instance for manipule content has singleton
     *
     * @return static
     */
    public static function getInstance()
    {
        return static::$instance;
    }

    /**
     * Set enviroment in runtime
     *
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    public static function set($key, $value)
    {
        $instance = static::getInstance();

        $instance->compile($key, $value);

        $instance->refillingStore();
    }

    /**
     * Set enviroment in runtime
     *
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    public static function replace($key, $value)
    {
        $instance = static::getInstance();

        $instance->compile($key, $value, true);

        $instance->refillingStore();
    }

    /**
     * return array of variables values registered
     *
     * @return array<string, mixed>
     */
    public static function registered()
    {
        return static::$envs;
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

    /**
     * Reset enviroment
     */
    public static function forget()
    {
        static::$instance = null;

        foreach (static::$envs as $key => $value) {
            unset($_ENV[$key]);
            unset($_SERVER[$key]);
        }

        static::$envs = [];
        static::$store = [];
    }
}
