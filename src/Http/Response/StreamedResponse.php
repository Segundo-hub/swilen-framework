<?php

namespace Swilen\Http\Response;

use Swilen\Http\Response;

class StreamedResponse extends Response
{
    /**
     * The callback to call in stream as closure.
     *
     * @var \Closure
     */
    protected $callback;

    /**
     * Indicates if callback is streamed.
     *
     * @var bool
     */
    protected $streamed;

    /**
     * Indicates headers has been sent.
     *
     * @var bool
     */
    protected $sent;

    /**
     * Create new Streamed response.
     *
     * @param callable $callback The callback for call once
     *
     * @return void
     */
    public function __construct(callable $callback, int $status = 200, array $headers = [])
    {
        parent::__construct(null, $status, $headers);

        $this->callback = $callback;
    }

    /**
     * Sends HTTP headers for the current web response.
     *
     * @return $this
     */
    protected function sendHeaders()
    {
        if ($this->sent) {
            return $this;
        }

        $this->sent = true;

        return parent::sendHeaders();
    }

    /**
     * {@inheritdoc}
     *
     * This method only sends the content once.
     *
     * @return $this
     */
    protected function sendBody()
    {
        if ($this->streamed) {
            return $this;
        }

        $this->streamed = true;

        ($this->callback)($this);

        return $this;
    }
}
