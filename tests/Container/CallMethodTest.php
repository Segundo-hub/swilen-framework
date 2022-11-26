<?php

use Swilen\Container\Container;
use Swilen\Container\Method;

uses()->group('Container');

beforeEach(function () {
    $this->container = new Container();
});

afterEach(function () {
    unset($this->container);
});

it('Call method', function () {
    $result = Method::call($this->container, function () {
        return 10;
    });

    expect($result)->toBeInt();
});

it('Call invoke class', function () {
    $result = Method::call($this->container, InvokableStub::class);

    expect($result)->toBeInt();
});

it('Call invoke class in array', function () {
    $result = Method::call($this->container, InvokableStub::class.'@__invoke');

    expect($result)->toBeInt();
});

it('Call class method as normal', function () {
    $result = Method::call($this->container, InvokableStub::class.'@method');
    expect($result)->toBeString();

    $result = Method::call($this->container, [new InvokableStub(), 'method']);
    expect($result)->toBeString();
});

it('Call method binding into container', function () {
    $this->container->bindMethod(InvokableStub::class.'@method', function ($app) {
        return 'echo';
    });

    $result = Method::call($this->container, InvokableStub::class.'@method');

    expect($result)->toBe('echo');
});

it('Call class method as static', function () {
    $result = Method::call($this->container, InvokableStub::class.'::staticMethod');

    expect($result)->toBeArray();
});

it('Throw error when call class without method and not contains __invoke method', function () {
    Method::call($this->container, NormalyClassStub::class.'@');
})->throws(InvalidArgumentException::class, 'Method not provided.');

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

it('call Closure with parameters', function () {
    $result = Method::call($this->container, function ($var) {
        return $var;
    }, ['var' => 'foo']);
    expect($result)->toBe('foo');

    $result = Method::call($this->container, function ($val = 'bar') {
        return $val;
    });
    expect($result)->toBe('bar');

    $instance       = new stdClass();
    $instance->data = true;

    $result = Method::call($this->container, NormalyClassStub::class.'@withParams', [
        stdClass::class => $instance,
    ]);
    expect($result)->toBeInstanceOf(stdClass::class);
    expect($result->data)->toBeTrue();
});

it('call Closure with variadic parameters', function () {
    $result = Method::call($this->container, function (...$var) {
        return $var;
    }, ['foo', 'bar']);
    expect($result)->toBe(['foo', 'bar']);

    $result = Method::call($this->container, NormalyVariadicClassStub::class.'@method');
    expect($result)->toBe(100);
});

it('Throw error when call Closure missing parameters', function () {
    Method::call($this->container, function ($var) {
        return $var;
    });
})->throws(RuntimeException::class);

class InvokableStub
{
    public function __invoke()
    {
        return 10;
    }

    public function method()
    {
        return 'string';
    }

    public static function staticMethod()
    {
        return [];
    }
}

class NormalyClassStub
{
    public function method()
    {
        return 100;
    }

    public function withParams(stdClass $object)
    {
        return $object;
    }
}

class NormalyVariadicClassStub
{
    public function method(NormalyClassStub ...$args)
    {
        return array_reduce($args, function ($stack, $arg) {
            return $stack += $arg->method();
        }, 0);
    }
}
