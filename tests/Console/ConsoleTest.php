<?php

use Swilen\Arthropod\Env;
use Swilen\Console\Application;
use Swilen\Console\Input\ArgvInput;
use Swilen\Console\Output\ConsoleOutput;

uses()->group('Console');

function Command(string $command) {
    $commands = explode(' ', $command);
    $_SERVER['argv'] = $commands;
}

define('SWILEN_SUCCESS', 0);

beforeEach(function () {
    define('SWILEN_CMD_START', microtime(true));

    (new Env())->createFrom(dirname(__DIR__))->load();

    $this->console = new Application(dirname(__DIR__));
});

afterEach(function () {
    $code = $this->console->terminate();

    exit($code);
});

it('Expect not error when', function () {

    Command('swilen key:generate --safe');

    $code = $this->console->exec(new ArgvInput(), new ConsoleOutput());

    expect($code)->toBe(SWILEN_SUCCESS);
});

