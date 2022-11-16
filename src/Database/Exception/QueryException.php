<?php

namespace Swilen\Database\Exception;

class QueryException extends \PDOException
{
    /**
     * The SQL for the query.
     *
     * @var string
     */
    protected $sql;

    /**
     * The bindings for the query.
     *
     * @var array
     */
    protected $bindings;

    /**
     * Create a new query exception instance.
     *
     * @param string     $sql
     * @param array      $bindings
     * @param \Throwable $previous
     *
     * @return void
     */
    public function __construct($sql, array $bindings, \Throwable $previous)
    {
        parent::__construct('', 0, $previous);

        $this->sql      = $sql;
        $this->bindings = $bindings;
        $this->code     = $previous->getCode();
        $this->message  = $this->replaceMessagePlaceholders($sql, $bindings, $previous);

        if ($previous instanceof \PDOException) {
            $this->errorInfo = $previous->errorInfo;
        }
    }

    /**
     * Format the SQL error message with replace placeholder with real value.
     *
     * @param string     $sql
     * @param array      $bindings
     * @param \Throwable $previous
     *
     * @return string
     */
    protected function replaceMessagePlaceholders($sql, $bindings, \Throwable $previous)
    {
        $segments = explode('?', $sql);
        $result   = array_shift($segments);

        foreach ($segments as $segment) {
            $result .= $this->parseToPrimitive(array_shift($bindings)).$segment;
        }

        if (strpos($sql, ':') !== false) {
            foreach ($bindings as $key => $value) {
                $result = str_replace($key, $this->parseToPrimitive($value), $result);
            }
        }

        return sprintf('%s (SQL: %s)', $previous->getMessage(), $result);
    }

    /**
     * Parse given value to php primitive.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    protected function parseToPrimitive($value)
    {
        if (is_numeric($value)) {
            return (int) $value;
        }

        if (is_string($value)) {
            return sprintf("'%s'", $value);
        }

        if (is_null($value)) {
            return '(null)';
        }

        return sprintf('(%s)', (string) $value);
    }
}
