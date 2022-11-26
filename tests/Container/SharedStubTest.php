<?php

/**
 * Testing clases.
 */
class TestingClassStub
{
    protected $property;

    public function __construct(int $id = 0)
    {
        $this->property = $id;
    }

    public function getProperty()
    {
        return $this->property;
    }

    public function increment()
    {
        ++$this->property;
    }
}

interface MongoRepositoryStub
{
    public function persist(int $id): void;

    public function find(): int;
}

class UserRepositoryStub implements MongoRepositoryStub
{
    protected $id;

    public function __construct(int $id)
    {
        $this->persist($id);
    }

    public function persist(int $id): void
    {
        $this->id = $id;
    }

    public function find(): int
    {
        return $this->id;
    }
}

final class UserServiceStub
{
    private $repository;

    public function __construct(MongoRepositoryStub $repository)
    {
        $this->repository = $repository;
    }

    public function find()
    {
        return $this->repository->find();
    }
}
