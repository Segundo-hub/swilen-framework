<?php

namespace Swilen\Http\Contract;

use Swilen\Http\Request;

interface ResponseContract
{
    /**
     * Prepares the Response before it is sent to the client.
     *
     * @param \Swilen\Http\Request $request
     *
     * @return $this
     */
    public function prepare(Request $request);

    /**
     * Terminate response with sends HTTP headers and content.
     *
     * @return $this
     */
    public function terminate();
}
