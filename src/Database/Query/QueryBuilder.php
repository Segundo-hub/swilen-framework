<?php

namespace Swilen\Database\Query;

use Swilen\Database\Contract\QueryBuilder as QueryBuilderContract;

class QueryBuilder implements QueryBuilderContract
{
    /**
     * @var \PDO
     */
    protected $pdo;

    /**
     * @param \PDO $connection
     */
    public function __construct($connection = null)
    {
        $this->pdo = $connection;
    }

    public function schema(string $table, ?string $alias = null)
    {
        //
    }

}
