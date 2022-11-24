<?php

namespace Swilen\Database;

use Swilen\Database\Concerns\DetectLostConnections;
use Swilen\Database\Exception\DatabaseConnectionException;

class MySqlConnector
{
    use DetectLostConnections;

    /**
     * The default PDO connection options.
     *
     * @var array
     */
    protected $options = [
        \PDO::ATTR_CASE => \PDO::CASE_NATURAL,
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_ORACLE_NULLS => \PDO::NULL_NATURAL,
        \PDO::ATTR_STRINGIFY_FETCHES => false,
        \PDO::ATTR_EMULATE_PREPARES => false,
    ];

    /**
     * Create PDO connection and connect.
     *
     * @param array $config
     *
     * @return \PDO
     */
    public function connect(array $config)
    {
        $dsn = $this->formatAndGetDSN($config);

        $connection = $this->createConnection($dsn, $config, $this->parseOptions($config));

        if (!empty($schema = $config['schema'])) {
            $connection->exec('use `'.$schema.'`;');
        }

        return $connection;
    }

    /**
     * Create a new PDO connection.
     *
     * @param string $dsn
     * @param array  $config
     * @param array  $options
     *
     * @return \PDO
     *
     * @throws \Throwable
     */
    public function createConnection($dsn, array $config, array $options)
    {
        [$username, $password] = [
            $config['username'] ?? null, $config['password'] ?? null,
        ];

        try {
            return $this->createPdoConnection(
                $dsn, $username, $password, $options
            );
        } catch (\Throwable $e) {
            return $this->tryAgainIfCausedByLostConnection(
                $e, $dsn, $username, $password, $options
            );
        }
    }

    /**
     * Create a new PDO connection instance.
     *
     * @param string $dsn
     * @param string $username
     * @param string $password
     * @param array  $options
     *
     * @return \PDO
     */
    protected function createPdoConnection($dsn, $username, $password, $options)
    {
        return new \PDO($dsn, $username, $password, $options);
    }

    /**
     * Handle an exception that occurred during connect execution.
     *
     * @param \Throwable $e
     * @param string     $dsn
     * @param string     $username
     * @param string     $password
     * @param array      $options
     *
     * @return \PDO
     *
     * @throws \Swilen\Database\Exception\DatabaseConnectionException
     */
    protected function tryAgainIfCausedByLostConnection(\Throwable $e, $dsn, $username, $password, $options)
    {
        if ($this->causedByLostConnection($e)) {
            return $this->createPdoConnection($dsn, $username, $password, $options);
        }

        throw new DatabaseConnectionException($e->getMessage(), $e->getCode(), $e->getPrevious());
    }

    /**
     * Get the DSN string for a host / port configuration.
     *
     * @param array $config
     *
     * @return string
     */
    protected function formatAndGetDSN(array $config)
    {
        [$host, $port, $schema] = [
            $config['host'] ?? '', $config['port'] ?? null, $config['schema'] ?? '',
        ];

        return $port
            ? 'mysql:host='.$host.';port='.$port.';dbname='.$schema
            : 'mysql:host='.$host.';dbname='.$schema;
    }

    /**
     * Get the PDO options based on the configuration.
     *
     * @param array $config
     *
     * @return array
     */
    public function parseOptions(array $config)
    {
        $options = $config['options'] ?? [];

        return array_diff_key($this->options, $options) + $options;
    }
}
