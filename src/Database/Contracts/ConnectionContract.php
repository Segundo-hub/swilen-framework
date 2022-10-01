<?php

namespace Swilen\Database\Contracts;

interface ConnectionContract
{
    public function prepare(string $query, array $bindings = []);

    public function raw(string $query, array $bindings = []);

    public function select(string $query, array $bindings = []);

    public function selectOne(string $query, array $bindings = []);

    public function insert(string $query, array $bindings = []);

    public function update(string $query, array $bindings = []);

    public function delete(string $query, array $bindings = []);
}
