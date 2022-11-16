<?php

namespace Swilen\Security\Middleware;

use Swilen\Http\Exception\HttpForbiddenException;
use Swilen\Http\Exception\HttpUnauthorizedException;
use Swilen\Http\Request;
use Swilen\Security\Token\Payload;

class Authenticate
{
    /**
     * Handle incoming request for authenticate user.
     *
     * @param \Swilen\Http\Request $request
     * @param \Closure             $next
     *
     * @return \Closure
     */
    public function handle(Request $request, \Closure $next)
    {
        if (!$token = $request->bearerToken()) {
            throw new HttpForbiddenException();
        }

        if (!$result = $this->isAuthenticated($token)) {
            throw new HttpUnauthorizedException();
        }

        return $next($request->withUser($result->data()));
    }

    /**
     * Verify if user is authenticated.
     *
     * @param string $token
     *
     * @return \Swilen\Security\Token\Payload
     */
    protected function isAuthenticated(string $token)
    {
        return new Payload(['data' => 'name', 'token' => $token]);
    }
}
