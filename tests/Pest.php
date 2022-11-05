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

function fetch(string $uri, string $method = 'GET', array $headers = [], array $files = [], array $parameters = [])
{
    return \Swilen\Http\Request::make($uri, $method, $parameters, $files, $headers);
}

function command(string $command)
{
    $_SERVER['argv'] = explode(' ', $command);;
}

function print_time($start_time, $print = true)
{
    $formatted = number_format((hrtime(true) - $start_time) / 1e+6, 3);

    $print && fwrite(STDERR, print_r('Executed: ' . $formatted . ' miliseconds' . PHP_EOL, true));

    return $formatted;
}
