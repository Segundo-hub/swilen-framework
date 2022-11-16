<?php

namespace Swilen\Routing\Exception;

use Swilen\Http\Response;

class HttpResponseException extends \RuntimeException
{
    /**
     * The underlying response instance.
     *
     * @var \Swilen\Http\Response
     */
    protected $response;

    /**
     * Create a new HTTP response exception instance.
     *
     * @param \Swilen\Http\Response $response
     *
     * @return void
     */
    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    /**
     * Get the underlying response instance.
     *
     * @return \Swilen\Http\Response
     */
    public function getResponse()
    {
        return $this->response;
    }
}
