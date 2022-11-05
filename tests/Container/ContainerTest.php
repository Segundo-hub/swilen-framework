<?php

use Psr\Container\ContainerInterface;
use Swilen\Container\Container;
use Swilen\Container\Exception\EntryNotFoundException;
use Swilen\Shared\Container\Container as ContainerContract;

uses()->group('Container');

beforeAll(function () {
    Container::setInstance(new Container());
});

beforeEach(function () {
    $this->app = Container::getInstance();
});

afterAll(function () {
    Container::getInstance()->flush();
});

it('Waiting for the \Container instance to be generated successfully', function () {
    expect($this->app)->toBeInstanceOf(Container::class);
    expect($this->app)->toBeObject();
});

it('Waiting for the \Container is instance of Psr\Container\ContainerInterface', function () {
    expect($this->app)->toBeInstanceOf(ContainerInterface::class);
});

it('Waiting for the \Container is instance of Swilen\Shared\Container\Container', function () {
    expect($this->app)->toBeInstanceOf(ContainerContract::class);
});

it('Resolve class correctly', function () {
    $instance = $this->app->make(TestingClass::class);

    expect($instance)->toBeInstanceOf(TestingClass::class);
    expect($instance)->toBeObject();
});

it('Resolve class correctly with params', function () {
    $instance = $this->app->make(TestingClass::class, ['id' => 1]);

    expect($instance)->toBeInstanceOf(TestingClass::class);
    expect($instance->getProperty())->toBeOne();
});

it('Generate exception if service not found', function () {
    $this->app->make('Swilen\Container\EntryNotFound');
})->throws(EntryNotFoundException::class);

it('Register the class as simple binding. Create new instance when done by container', function () {
    $this->app->bind('bind', function ($app) {
        return new TestingClass(10);
    });

    $this->app->make('bind')->increment();
    $this->app->make('bind')->increment();

    expect($this->app->isShared('bind'))->toBeFalse();
    expect($this->app->make('bind')->getProperty())->toBe(10);
});

it('Register the class as singleton instance. Return only instance when done by container', function () {
    $this->app->singleton('singleton', function ($app) {
        return new TestingClass(18);
    });

    $this->app->make('singleton')->increment();
    $this->app->make('singleton')->increment();

    expect($this->app->isShared('singleton'))->toBeTrue();
    expect($this->app->make('singleton')->getProperty())->toBe(20);
});

it('Register interface for dependency injection', function () {
    $this->app->bind(MongoRepository::class, function ($app) {
        return new UserRepository(100);
    });

    expect($this->app->isShared(MongoRepository::class))->toBeFalse();

    expect($this->app->make(UserService::class)->find())->toBeInt();
});

it('Call function with dependency injection in parameter', function () {
    $this->app->bind(MongoRepository::class, function ($app) {
        return new UserRepository(100);
    });

    $closure = function (MongoRepository $repository) {
        return $repository->find();
    };

    $result = $this->app->call($closure);

    expect($result)->toBeInt();
});

it('Call __invoke function was class is used as function', function () {
    $this->app->bind(MongoRepository::class, function ($app) {
        return new UserRepository(100);
    });

    $class = new class() {
        public function __invoke(MongoRepository $repository)
        {
            return $repository->find();
        }
    };

    $result = $this->app->call($class);

    expect($result)->toBeInt();
});

it('Depency resolve with ArrayAccess', function () {
    $this->app->bind(MongoRepository::class, function ($app) {
        return new UserRepository(100);
    });

    $this->app['depend'] = function ($app) {
        return new class() {
            public function __invoke(MongoRepository $repository)
            {
                return $repository->find();
            }
        };
    };

    expect($this->app->call($this->app['depend']))->toBeInt();

    $this->app->flush();

    expect($this->app->getBindings())->toBeEmpty();

    expect($this->app['depend'])->toBeNull();
})->throws(EntryNotFoundException::class);

/**
 * Testing clases.
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
        ++$this->property;
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
    private $repository;

    public function __construct(MongoRepository $repository)
    {
        $this->repository = $repository;
    }

    public function find()
    {
        return $this->repository->find();
    }
}
