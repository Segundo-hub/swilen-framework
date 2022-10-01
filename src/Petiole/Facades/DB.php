<?php

namespace Swilen\Petiole\Facades;

/**
 * @method static \Swilen\Database\Connection raw($value)
 * @method static mixed[] select(string $query, array $bindings = [])
 * @method static mixed selectOne(string $query, array $bindings = [])
 * @method static bool insert(string $query, array $bindings = [])
 * @method static int delete(string $query, array $bindings = [])
 * @method static int update(string $query, array $bindings = [])
 * @method static bool statement(string $query, array $bindings = [])
 * @method static void beginTransaction()
 * @method static void commit()
 * @method static void rollBack()
 *
 *
 * @see \Swilen\Database\Contracts\DatabaseConnection
 */

class DB extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeName()
    {
        return 'db';
    }
}
