<?php

namespace Swilen\Http\Contract;

use Swilen\Http\Request;

interface ResponseFactory
{
    /**
     * Prepare response with $request information
     *
     * @param \Swilen\Http\Request $request
     *
     * @return Swilen\Http\Contract\ResponseFactory
     */
    public function prepare(Request $request);

    /**
     * Send content to client
     *
     * @return Swilen\Http\Contract\ResponseFactory
     */
    public function sendContent();
}
