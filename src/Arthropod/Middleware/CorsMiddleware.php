<?php

namespace Swilen\Arthropod\Middleware;

use Swilen\Container\Container;
use Swilen\Http\Common\Http;
use Swilen\Http\Request;
use Swilen\Http\Response;

class CorsMiddleware
{
    /**
     * The container instance.
     *
     * @var \Swilen\Container\Container
     */
    protected $container;

    /**
     * Default headers list fro CORS.
     *
     * @var array<string,string[]|string|bool|int>
     */
    protected $headers = [
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Headers' => 'X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Authorization',
        'Access-Control-Allow-Methods' => 'GET, HEAD, PUT, PATCH, POST, DELETE',
        'Access-Control-Allow-Credentials' => true,
        'Access-Control-Max-Age' => 86400,
    ];

    /**
     * Create new CorsMiddleware for all routes.
     *
     * @param \Swilen\Container\Container $container
     *
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Handle incoming request for authenticate user.
     *
     * @param \Swilen\Http\Request $request
     * @param \Closure             $next
     *
     * @return \Swilen\Http\Response
     */
    public function handle(Request $request, \Closure $next)
    {
        if (!$this->shouldRun($request)) {
            return $next($request);
        }

        if ($this->isPreflightRequest($request)) {
            return $this->handlePreflightRequest($request);
        }

        $response = $next($request);

        return $this->handleCorsRequest($response);
    }

    /**
     * Determine if current request is a preflight.
     *
     * @param \Swilen\Http\Request $request
     *
     * @return bool
     */
    protected function isPreflightRequest(Request $request)
    {
        return $request->getMethod() === Http::METHOD_OPTIONS &&
            $request->headers->has('Access-Control-Request-Method');
    }

    /**
     * Determine if current request is a preflight.
     *
     * @param \Swilen\Http\Request $request
     *
     * @return \Swilen\Http\Response
     */
    protected function handlePreflightRequest(Request $request)
    {
        return new Response(null, 204, [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Credentials' => true,
            'Access-Control-Allow-Methods' => 'GET, HEAD, PUT, PATCH, POST, DELETE',
            'Access-Control-Allow-Headers' => 'X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Authorization',
            'Access-Control-Max-Age' => 600,
        ]);
    }

    /**
     * Determine if the request has a Origin that should pass through the CORS flow.
     *
     * @param \Swilen\Http\Request $request
     *
     * @return bool
     */
    protected function shouldRun(Request $request)
    {
        if (!$request->headers->get('Origin', false)) {
            return false;
        }

        return true;
    }

    /**
     * Handle cors request and append headers to response.
     *
     * @param \Swilen\Http\Response $response
     *
     * @return \Swilen\Http\Response
     */
    protected function handleCorsRequest(Response $response)
    {
        return $response->withHeaders([
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Credentials' => true,
        ]);
    }
}
