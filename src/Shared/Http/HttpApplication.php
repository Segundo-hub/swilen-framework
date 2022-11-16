<?php

namespace Swilen\Shared\Http;

use Swilen\Http\Request;

interface HttpApplication
{
    /**
     * Return application base uri.
     *
     * @param string $path
     *
     * @return string
     */
    public function appUri(string $path = '');

    /**
     * Replace application uri provided from param.
     *
     * @param string $uri
     *
     * @return $this
     */
    public function useAppUri(string $path = '');

    /**
     * Handle the incoming request and send it to the router.
     *
     * @param \Swilen\Http\Request $request
     *
     * @return \Swilen\Http\Response
     */
    public function handle(Request $request);
}
