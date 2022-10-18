<?php

use Swilen\Arthropod\Env;
use Swilen\Console\Application;
use Swilen\Console\Input\ArgvInput;
use Swilen\Contracts\Console\Application as ConsoleApplication;

uses()->group('Console');

beforeEach(function () {
    define('SWILEN_CMD_START', microtime(true));

    (new Env())->createFrom(dirname(__DIR__))->load();

    $this->console = new Application(dirname(__DIR__));
});

// afterEach(function () {
//     $code = $this->console->terminate();

//     exit($code);
// });

it('Expect not error when', function () {

    command('swilen key:generate --safe length=24');

    $code = $this->console->exec(new ArgvInput());

    expect($code)->toBe(ConsoleApplication::SUCCESS);
})->skip('Generate an app key and it uses a lot of CPU resources');

