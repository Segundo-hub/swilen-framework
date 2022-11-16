<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Returns a builder object to create mock objects using a fluent interface.
 *
 * @param string $classname
 *
 * @return \PHPUnit\Framework\MockObject\MockBuilder
 */
function getMockBuilder(string $classname)
{
    return test()->getMockBuilder($classname);
}

/**
 * Simulate navigator fetch.
 *
 * @param string $uri
 * @param string $method
 * @param array  $jheaders
 * @param array  $files
 * @param array  $parameters
 *
 * @return \Swilen\Http\Request
 */
function fetch(string $uri, string $method = 'GET', array $headers = [], array $files = [], array $parameters = [])
{
    return \Swilen\Http\Request::make($uri, $method, $parameters, $files, $headers);
}

/**
 * Simulate call command.
 *
 * @param string $command
 *
 * @return string[]
 */
function command(string $command)
{
    $_SERVER['argv'] = $command = explode(' ', $command);

    return $command;
}

/**
 * Debug content at json.
 *
 * @param mixed $value
 *
 * @return void
 */
function debug_json($value)
{
    print_r(PHP_EOL.json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_SLASHES).PHP_EOL);
}

/*
|--------------------------------------------------------------------------
| Common stubs
|--------------------------------------------------------------------------
*/

/**
 * Shared readable file with content 'test'.
 * Use for objects that require a file.
 *
 * @return string
 */
function getReadableFileStub()
{
    return __DIR__.'/__fixtures__/readablefile.txt';
}
