<?php

namespace Swilen\Database;

use Swilen\Database\Exception\DatabaseConnectionException;

use PDO;
use Swilen\Database\Contract\ConnectionContract;

class Connection implements ConnectionContract
{
    /**
     * PDO collection of initial atributes
     *
     * @var array<int, int>
     */
    protected $options = [
        PDO::ATTR_CASE => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
    ];

    /**
     * Driver of PDO
     *
     * @var array
     */
    protected $driver;

    /**
     * The PDO instance
     *
     * @var \PDO|null
     */
    protected $connection;

    /**
     * The final Data Source Name for database connection
     *
     * @var string
     */
    protected $DSN;

    /**
     * Connection times count
     *
     * @var int
     */
    private $connected = 0;

    /**
     * The schema for connected
     *
     * @var string
     */
    protected $schema;

    /**
     * The charset of connection
     *
     * @var string
     */
    protected $charset;

    /**
     * The port of connection
     *
     * @var int
     */
    protected $port;

    /**
     * Inject database array config and init database instance
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->driver = $config['driver'] ?? 'mysql';
        $this->createConnection($config);
    }

    /**
     * Create database connection
     *
     * @param array $config
     */
    final private function createConnection(array $config)
    {
        [$username, $password] = [
            $config['username'] ?? null, $config['password'] ?? null
        ];

        $this->parseConnectionOptions((object) $config);

        try {
            if ($this->isMissingConnection()) {
                $this->connection = $this->createPdoConnection($this->DSN, $username, $password, $this->options);
                $this->connected++;
            }
        } catch (\PDOException $e) {
            throw new DatabaseConnectionException();
        }
    }

    /**
     * Parse options for connection
     *
     * @param object $config The config for database connection
     *
     * @return void
     */
    protected function parseConnectionOptions(object $config)
    {
        $this->schema = $config->schema ?? $config->database ?? '';

        $this->charset = $config->charset ?? 'UTF-8';

        $this->port = $config->port ?? null;

        $this->DSN = $this->driver . ':host=' . $config->host . ';dbname=' . $this->schema . ';charset=' . $this->charset . ($this->port ? ';port=' . $this->port : '');

        if ($this->driver === 'mysql') {
            array_push($this->options, [
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . $this->charset . ($config->collation ? 'COLLATE ' . $config->collation . ';' : ';')
            ]);
        }
    }

    /**
     * Check if connection is empty or not defined
     *
     * @return bool
     */
    public function isMissingConnection()
    {
        return empty($this->connection) || is_null($this->connection);
    }

    /**
     * Create and return PDO connection
     *
     * @param string $DSN
     * @param string $username
     * @param string $password
     * @param array<int, int> $options
     *
     * @return \PDO
     */
    protected function createPdoConnection($DSN, $username, $password, $options)
    {
        return new PDO($DSN, $username, $password, $options);
    }

    /**
     * Get database connection
     *
     * @return \PDO|null
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Prepare queries statements for prevent sql injection
     *
     * @param string $stmt
     * @param array|null $bindings
     *
     * @return \PDOStatement|false
     */
    public function prepare(string $stmt, array $bindings = [])
    {
        $statement = $this->connection->prepare($stmt);

        foreach ($bindings as $key => $value) {
            $PARAM_TYPE  = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $PARAM_INDEX = is_int($key) ? $key + 1 : $key;
            $statement->bindValue($PARAM_INDEX, $value, $PARAM_TYPE);
        }

        return $statement;
    }

    /**
     * Make raw sql queries
     *
     * @param string $stmt
     * @param array|null $bindings
     *
     * @return mixed
     */
    public function raw(string $stmt, array $bindings = [])
    {
        return $this->connection->query($stmt)->fetchAll();
    }

    /**
     * Select all rows with prepare statement
     *
     * @param string $stmt
     * @param array|null $bindings
     *
     * @return mixed[]
     */
    public function select(string $stmt, array $bindings = [])
    {
        $statement = $this->prepare($stmt, $bindings);

        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * Select one row with prepare statement
     *
     * @param string $stmt
     * @param array|null $bindings
     *
     * @return mixed
     */
    public function selectOne(string $stmt, array $bindings = [])
    {
        $statement = $this->prepare($stmt, $bindings);

        $statement->execute();

        return $statement->fetch();
    }

    /**
     * Insert data to row with prepare statement
     *
     * @param string $query
     * @param array|null $bindings
     *
     * @return string|false
     */
    public function insert(string $query, array $bindings = [])
    {
        $statement = $this->prepare($query, $bindings);
        if ($statement->execute()) {
            return $this->connection->lastInsertId();
        }
        return false;
    }

    /**
     * Update data to row with prepare statement
     *
     * @param string $query
     * @param array|null $bindings
     *
     * @return bool
     */
    public function update(string $query, array $bindings = [])
    {
        $statement = $this->prepare($query, $bindings);
        if ($statement->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Delete data to row with prepare statement
     *
     * @param string $query
     * @param array|null $bindings
     *
     * @return bool
     */
    public function delete(string $query, array $bindings = [])
    {
        $statement = $this->prepare($query, $bindings);
        if ($statement->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Execute statement and return true is succesfully
     *
     * @param string $query
     * @param array|null $bindings
     *
     * @return bool
     */
    public function statement(string $query, array $bindings = [])
    {
        $statement = $this->prepare($query, $bindings);

        return $statement->execute();
    }

    /**
     * Return the connections made during the process
     *
     * @return int
     */
    public function connectionTimes()
    {
        return $this->connected;
    }

    /**
     * Begin transaction for reject if error found
     *
     * @return void
     */
    public function beginTransaction()
    {
        $this->connection->beginTransaction();
    }

    /**
     * Commit transacction if is safe
     *
     * @return void
     */
    public function commit()
    {
        $this->connection->commit();
    }

    /**
     * Reject transaction if error found
     *
     * @return void
     */
    public function rollBack()
    {
        $this->connection->rollBack();
    }
}
