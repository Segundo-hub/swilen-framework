<?php

namespace Swilen\Petiole\Facades;

use Swilen\Petiole\Facade;

/**
 * @method static mixed         selectOne(string $query, array $bindings = [])
 * @method static mixed[]       select(string $query, array $bindings = [])
 * @method static bool          insert(string $query, array $bindings = [])
 * @method static int           delete(string $query, array $bindings = [])
 * @method static int           update(string $query, array $bindings = [])
 * @method static \PDOStatement partial(string $query, array $bindings = [])
 * @method static bool          statement(string $query, array $bindings = [])
 * @method static int           affectingStatement(string $query, array $bindings = [])
 * @method static bool          unprepared(string $query)
 * @method static void          bindValues(\PDOStatement $statement, array $bindings = [])
 * @method static array         prepareBindings(array $bindings)
 * @method static void          beginTransaction()
 * @method static void          commit()
 * @method static void          rollBack()
 * @method static void          disconnect()
 * @method static void          close()
 * @method static string        getSchema()
 * @method static int|false     getInsertId()
 *
 * @see \Swilen\Database\Contract\ConnectionContract
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
