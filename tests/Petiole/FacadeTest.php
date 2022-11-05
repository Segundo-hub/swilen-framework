<?php

use Swilen\Container\Container;
use Swilen\Petiole\Facade;

uses()->group('Petiole', 'Facade');

beforeAll(function () {
    Facade::setFacadeApplication(Container::getInstance());
});

beforeEach(function () {
    $this->app = Container::getInstance();
});

afterAll(function () {
    Facade::flushFacadeInstances();
    Container::getInstance()->flush();
});

it('Facade implemetacion', function () {
    $this->app->bind('test-facade', function () {
        return new class {
            public function retrieve(int $number)
            {
                return $number;
            }
        };
    });

    expect(TestFacade::retrieve(20))->toBe(20);
});
/**
 * @method static int retrieve(int $number)
 */
class TestFacade extends Facade
{
    protected static function getFacadeName(): string
    {
        return 'test-facade';
    }
}
