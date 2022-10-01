<?php

namespace Swilen\Http\Component;

final class ResponseHeaderHunt extends HeaderHunt
{
    /**
     * Create new response headers collection from current params
     *
     * @param array<string, mixed> $headers
     *
     * @return void
     */
    public function __construct(array $headers = [])
    {
        parent::__construct($headers);

        if (!isset($this->headers['date'])) {
            $this->set('Date', gmdate('D, d M Y H:i:s') . ' GMT');
        }
    }
}
