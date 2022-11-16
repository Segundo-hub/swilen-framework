<?php

use Psr\Container\ContainerInterface;
use Swilen\Container\Container;
use Swilen\Container\Exception\BindingResolutionException;
use Swilen\Container\Exception\EntryNotFoundException;
use Swilen\Shared\Container\Container as ContainerContract;

uses()->group('Container');

beforeAll(function () {
    Container::setInstance(null);
});

beforeEach(function () {
    $this->container = Container::getInstance();
});

afterAll(function () {
    Container::getInstance()->flush();
    Container::setInstance(null);
});

it('Wait for the container instance not to be broken', function () {
    expect($this->container)->toBeObject();
    expect($this->container)->toBeInstanceOf(Container::class);
    expect($this->container)->toBeInstanceOf(ContainerInterface::class);
    expect($this->container)->toBeInstanceOf(ContainerContract::class);
});

it('Resolve the target class without register into container bindings', function () {
    $instance = $this->container->make(TestingClassStub::class);

    expect($instance)->toBeInstanceOf(TestingClassStub::class);
    expect($instance)->toBeObject();
    expect($this->container->getBindings())->toBeEmpty();
});

it('Resolve the target class without register into container bindings with parameters', function () {
    $instance = $this->container->make(TestingClassStub::class, ['id' => 1]);

    expect($instance)->toBeInstanceOf(TestingClassStub::class);
    expect($instance->getProperty())->toBeInt();
});

it('Generate exception if service with binding resolution', function () {
    $this->container->make('Swilen\Container\EntryNotFound');
})->throws(BindingResolutionException::class);

it('Generate exception if service not found in PSR', function () {
    $this->container->get('Swilen\Container\EntryNotFound');
})->throws(EntryNotFoundException::class);

it('Register the class as simple binding. Create new instance when done by container', function () {
    $this->container->bind('bind', function ($app) {
        return new TestingClassStub(10);
    });

    $this->container->make('bind')->increment();
    $this->container->make('bind')->increment();

    expect($this->container->isShared('bind'))->toBeFalse();
    expect($this->container->make('bind')->getProperty())->toBe(10);
});

it('Register the class as singleton instance. Return only instance when done by container', function () {
    $this->container->singleton('singleton', function ($app) {
        return new TestingClassStub(18);
    });

    $this->container->make('singleton')->increment();
    $this->container->make('singleton')->increment();

    expect($this->container->isShared('singleton'))->toBeTrue();
    expect($this->container->make('singleton')->getProperty())->toBe(20);
});

it('Register interface with implementation into container', function () {
    $this->container->bind(MongoRepositoryStub::class, function ($app) {
        return new UserRepositoryStub(100);
    });

    expect($this->container->isShared(MongoRepositoryStub::class))->toBeFalse();

    expect($this->container->make(UserServiceStub::class)->find())->toBeInt();
});

it('Call Closure with dependency injection in parameter', function () {
    $this->container->bind(MongoRepositoryStub::class, function ($app) {
        return new UserRepositoryStub(100);
    });

    $closure = function (MongoRepositoryStub $repository) {
        return $repository->find();
    };

    expect($this->container->call($closure))->toBeInt();
});

it('Call __invoke function was class is used as function', function () {
    $this->container->bind(MongoRepositoryStub::class, function ($app) {
        return new UserRepositoryStub(100);
    });

    $class = new class() {
        public function __invoke(MongoRepositoryStub $repository)
        {
            return $repository->find();
        }
    };

    $result = $this->container->call($class);

    expect($result)->toBeInt();
});

it('Resolve the target when inserted into the container it is treated as an array with \ArrayAcces', function () {
    $this->container[MongoRepositoryStub::class] = function ($app) {
        return new UserRepositoryStub(100);
    };

    $this->container['depend'] = function ($app) {
        return new class() {
            public function __invoke(MongoRepositoryStub $repository)
            {
                return $repository->find();
            }
        };
    };

    expect($this->container->call($this->container['depend']))->toBeInt();
});

it('Detect that core methods are called successfully', function () {
    $container = new Container();

    expect($container->getBindings())->toBeEmpty();
    expect($container->make(TestingClassStub::class))->toBeObject();
    expect($container->get(TestingClassStub::class))->toBeObject();
    expect($container[TestingClassStub::class])->toBeObject();

    expect($container->bound(TestingClassStub::class))->toBeFalsy();
    expect($container->has(TestingClassStub::class))->toBeFalsy();

    $container->bind(TestingClassStub::class);

    $container->tag([MemoryReportTaggedStub::class, CpuReportTaggedStub::class], PerformaceReportStub::class);

    /** @var \ArrayIterator<PerformaceReportStub> */
    $tagged = $container->tagged(PerformaceReportStub::class);

    $results = [];
    foreach ($tagged as $key => $value) {
        $results[$key] = $value;
    }

    expect($tagged->count())->toBe(2);
    expect($results[0])->toBeInstanceOf(PerformaceReportStub::class);
    expect($results[0])->toBeInstanceOf(MemoryReportTaggedStub::class);
    expect($results[1])->toBeInstanceOf(CpuReportTaggedStub::class);

    $reports = [];
    foreach ($tagged as $key => $value) {
        $reports[$key] = $value->report();
    }

    expect($reports)->toBe([20, 60]);

    expect($container->getBindings())->not->toBeEmpty();
    expect($container->getBindings())->toHaveKey(TestingClassStub::class);
});

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

interface PerformaceReportStub
{
    public function report();
}

class MemoryReportTaggedStub implements PerformaceReportStub
{
    public function report()
    {
        return 20;
    }
}

class CpuReportTaggedStub implements PerformaceReportStub
{
    public function report()
    {
        return 60;
    }
}
