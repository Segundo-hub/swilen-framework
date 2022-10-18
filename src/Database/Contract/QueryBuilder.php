<?php

namespace Swilen\Database\Contract;

interface QueryBuilder
{
    /**
     * Set schema or table name for select before
     *
     * @param string $table
     * @param string|null $alias
     *
     * @return self
     */
    public function schema(string $table, string $alias = null);

    // public function table(string $table, string $alias = null);

    // public function select(array $select = ['*']);

    // public function delete();

    // public function update(array $dataset);

    // public function set(array $dataset);

    // public function from(string $table, string $alias = null);

    // public function join($join, $alias, $conditionType = null, $condition = null, $indexBy = null);

    // public function innerJoin($join, $alias, $conditionType = null, $condition = null, $indexBy = null);

    // public function leftJoin($join, $alias, $conditionType = null, $condition = null, $indexBy = null);

    // public function where($where);

    // public function orWhere($where);

    // public function groupBy($groupBy);

    // public function addGroupBy($groupBy);

    // public function having($having);

    // public function orHaving($having);

    // public function orderBy($sort, $order = null);
}
