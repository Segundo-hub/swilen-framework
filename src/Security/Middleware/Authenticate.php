<?php

namespace Swilen\Security\Middleware;

use Closure;
use Swilen\Security\Token\Jwt;
use Swilen\Http\Exception\HttpForbiddenException;
use Swilen\Http\Exception\HttpUnauthorizedException;
use Swilen\Http\Request;

class Authenticate
{
    /**
     * Handle incoming request and proccesing
     *
     * @param \Swilen\Http\Request $request
     * @param \Closure $next
     *
     * @return \Closure
     */
    public function handle(Request $request, Closure $next)
    {
        if ($token = $this->isAuthenticated($request)) {
            return $next($request->withUser($token->data()));
        }

        throw new HttpUnauthorizedException;
    }

    private function isAuthenticated(Request $request)
    {
        if (!$token = $request->bearerToken()) {
            throw new HttpForbiddenException;
        }

        return (new Jwt)->verify($token, $this->secret());
    }

    /**
     * Return secret of token for matching
     *
     * @return string
     */
    protected function secret()
    {
        return '';
    }
}
