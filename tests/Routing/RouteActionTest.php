<?php

use Swilen\Routing\RouteAction;

uses()->group('Routing', 'RoutingAction');

it('Resolve uses action is closure', function () {
    $action = RouteAction::parse('/', InvokableTesting::class);

    expect($action['uses'])->toBe(InvokableTesting::class . '@__invoke');
    expect($action)->not->toHaveKey('controller');

    $action = RouteAction::parse('/', function () {
        return 'closure';
    });

    expect($action['uses'])->toBeCallable();
    expect($action['uses']())->toBe('closure');
    expect($action)->not->toHaveKey('controller');
});

it('Resolve uses action is controller', function ($actin) {
    $action = RouteAction::parse('/', $actin);

    expect($action)->toHaveKeys(['controller', 'uses']);
    expect($action['uses'])->toBe('MethodAllowedController@index');
    expect($action['controller'])->toBe('MethodAllowedController@index');
})->with([
    'array'  => array([MethodAllowedController::class, 'index']),
    'string' => 'MethodAllowedController@index'
]);

it('Throw uses action is null', function () {
    $action = RouteAction::parse('/', null);

    expect($action['uses'])->toBeCallable();
    expect($action)->not->toHaveKey('controller');
    $action['uses']();
})->throws(LogicException::class);

it('Throw uses action not contains __invoke method', function () {
    RouteAction::parse('/', MethodAllowedController::class);
})->throws(UnexpectedValueException::class, 'Invalid route action: [MethodAllowedController]');


class InvokableTesting
{
    public function __invoke()
    {
        return 5;
    }
}


class MethodAllowedController
{
    public function index()
    {
        return 'Hi!';
    }
}
