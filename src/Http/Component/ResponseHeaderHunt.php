<?php

namespace Swilen\Http\Component;

class ResponseHeaderHunt extends HeaderHunt
{
    /**
     * Create new response headers collection from current params.
     *
     * @param array<string, mixed> $headers
     *
     * @return void
     */
    public function __construct(array $headers = [])
    {
        $this->headers = [];

        parent::__construct($headers);

        $this->disableVersionHeader();
    }

    /**
     * Remove php version from headers collection for safe response.
     *
     * @return void
     */
    public function disableVersionHeader()
    {
        @header_remove('X-Powered-By');

        $this->remove('X-Powered-By');
    }
}
