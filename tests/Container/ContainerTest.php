<?php

use Psr\Container\ContainerInterface;
use Swilen\Container\Container;
use Swilen\Container\EntryNotFoundException;
use Swilen\Contracts\Container\Container as ContainerContract;

uses()->group('Container');

it('Waiting for the \Container instance to be generated successfully', function () {
    $container = Container::getInstance();

    expect($container)->toBeInstanceOf(Container::class);
    expect($container)->toBeObject();
});

it('Waiting for the \Container is instance of Psr\Container\ContainerInterface', function () {
    $container = Container::getInstance();

    expect($container)->toBeInstanceOf(ContainerInterface::class);
});

it('Waiting for the \Container is instance of Swilen\Contracts\Container\Container', function () {
    $container = Container::getInstance();

    expect($container)->toBeInstanceOf(ContainerContract::class);
});

it('Resolve class correctly', function () {
    $instance = Container::getInstance()->make(TestingClass::class);

    expect($instance)->toBeInstanceOf(TestingClass::class);
    expect($instance)->toBeObject();
});

it('Resolve class correctly with params', function () {
    $instance = Container::getInstance()->make(TestingClass::class, ['id' => 1]);

    expect($instance)->toBeInstanceOf(TestingClass::class);
    expect($instance->getProperty())->toBeOne();
});

it('Throw if class entry not found', function () {
    Container::getInstance()->make('Swilen\Container\EntryNotFound');
})->throws(EntryNotFoundException::class);

it('Register the class as simple binding. Create new instance when done by container', function () {
    $app = Container::getInstance();

    $app->bind('bind', function ($app) {
        return new TestingClass(10);
    });

    $app->make('bind')->increment();
    $app->make('bind')->increment();

    expect($app->isShared('bind'))->toBeFalse();
    expect($app->make('bind')->getProperty())->toBe(10);
});

it('Register the class as singleton instance. Return only instance when done by container', function () {
    $app = Container::getInstance();

    $app->singleton('singleton', function ($app) {
        return new TestingClass(18);
    });

    $app->make('singleton')->increment();
    $app->make('singleton')->increment();

    expect($app->isShared('singleton'))->toBeTrue();
    expect($app->make('singleton')->getProperty())->toBe(20);
});

it('Register interface for dependency injection', function () {
    $app = Container::getInstance();

    $app->bind(MongoRepository::class, function ($app) {
        return new UserRepository(100);
    });

    expect($app->isShared(MongoRepository::class))->toBeFalse();

    expect($app->make(UserService::class)->find())->toBeInt();
});



/**
 * Testing clases
 */
class TestingClass
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
        $this->property++;
    }
}

interface MongoRepository
{
    public function persist(int $id): void;

    public function find(): int;
}


class UserRepository implements MongoRepository
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

final class UserService
{
    protected $repository;

    public function __construct(MongoRepository $repository)
    {
        $this->repository = $repository;
    }

    public function find()
    {
        return $this->repository->find();
    }
}
