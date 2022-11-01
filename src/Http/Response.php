<?php

namespace Swilen\Http;

use Swilen\Http\Common\SupportResponse;
use Swilen\Http\Component\ResponseHeaderHunt;
use Swilen\Http\Contract\ResponseContract;
use Swilen\Http\Factories\{BinaryFileResponseFactory, JsonResponseFactory, RawResponseFactory};

final class Response extends SupportResponse implements ResponseContract
{
    /**
     * The current respone factory implement
     *
     * @var \Swilen\Http\Contract\ResponseFactory
     */
    protected $factory;

    /**
     * The response headers collection
     *
     * @var \Swilen\Http\Component\ResponseHeaderHunt
     */
    public $headers;

    /**
     * The parsed content as string or resource for put into client
     *
     * @var string|resource
     */
    protected $content;

    /**
     * The http version for the response
     *
     * @var string
     */
    protected $version;

    /**
     * The status code for response
     *
     * @var int
     */
    protected $statusCode;

    /**
     * The status text for response
     *
     * @var string
     */
    protected $statusText;

    /**
     * The charset encoding for response
     *
     * @var string
     */
    protected $charset = 'UTF-8';

    /**
     * Create new response instance
     *
     * @param resource|string|array|object|null $content
     * @param int $status
     * @param array $headers
     *
     * @return void
     */
    public function __construct($content = null, int $status = 200, array $headers = [])
    {
        $this->headers = new ResponseHeaderHunt($headers);

        $this->factory = new JsonResponseFactory($this, $content, $status, $headers);

        $this->version = '1.1';
    }

    /**
     * Update default response options
     *
     * @param resource|string|array|object|null $content
     * @param int $status
     * @param array $headers
     *
     * @return $this
     */
    public function withOptions($content = null, int $status = 200, array $headers = [])
    {
        $this->setContent($content);

        $this->setStatusCode($status);

        $this->withHeaders($headers);

        return $this;
    }

    /**
     * Create response with raw data encoded
     * {@inheritdoc}
     *
     * @return $this
     */
    public function make($content = '', int $status = 200, array $headers = [])
    {
        $this->factory = new RawResponseFactory($this, $content, $status, $headers);

        return $this;
    }

    /**
     * Create response with json encoded
     * {@inheritdoc}
     *
     * @return $this
     */
    public function send($content = null, int $status = 200, array $headers = [])
    {
        $this->factory = new JsonResponseFactory($this, $content, $status, $headers);

        return $this;
    }

    /**
     * Create response with binary file
     * {@inheritdoc}
     *
     * @return $this
     */
    public function file($file, array $headers = [])
    {
        $this->factory = new BinaryFileResponseFactory($this, $file, 200, $headers);

        return $this;
    }

    /**
     * Create response with downloadable binary file
     * {@inheritdoc}
     *
     * @return $this
     */
    public function download($file, $name = null, array $headers = [], bool $attachment = true)
    {
        $this->factory = new BinaryFileResponseFactory($this, $file, 200, $headers, $attachment);

        if (!$name) {
            $this->factory->updateFilename($name);
        }

        return $this;
    }

    /**
     * @deprecated
     *
     * {@inheritdoc}
     */
    public function stream($resource, int $status = 200, array $headers = [])
    {
        // TODO
    }

    /**
     * Determine and prepare response with factory
     *
     * @param \Swilen\Http\Request $request
     */
    public function prepare(Request $request)
    {
        $this->factory->prepare($request);

        return $this;
    }

    /**
     * Terminate http request and send content to client
     *
     * @return $this
     */
    public function terminate()
    {
        $this->sendResponseHeaders();

        $this->sendResponseContent();

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } elseif (!in_array(\PHP_SAPI, ['cli', 'phpdbg'], true)) {
            parent::closeOutputBuffer(0, true);
            flush();
        }

        return $this;
    }

    /**
     * Send content into client
     *
     * @return \Swilen\Http\Response
     */
    public function sendResponseContent()
    {
        $this->factory->sendContent();

        return $this;
    }

    /**
     * Send headers to client
     *
     * @return \Swilen\Http\Response
     */
    protected function sendResponseHeaders()
    {
        if (headers_sent()) {
            return $this;
        }

        foreach ($this->headers->all() as $name => $value) {
            $replace = 0 === strcasecmp($name, 'Content-Type');

            header($name . ': ' . $value, $replace, $this->statusCode());
        }

        $this->performResponseStatus();

        return $this;
    }

    /**
     * Send http status of response
     *
     * @return void
     */
    protected function performResponseStatus()
    {
        $this->statusCode = $this->statusCode ?? 500;
        $this->statusText = static::STATUS_TEXTS[$this->statusCode()] ?? 'Internal Server Error';

        header(
            sprintf('HTTP/%s %s %s', $this->getProtocolVersion(), $this->statusCode(), $this->statusText()),
            true,
            $this->statusCode()
        );
    }

    /**
     * Is response successful?
     *
     * @return bool
     */
    final public function isSuccessful()
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    /**
     * Is response informative?
     *
     * @return bool
     */
    final public function isInformational()
    {
        return $this->statusCode >= 100 && $this->statusCode < 200;
    }

    /**
     * Is the response empty?
     *
     * @return bool
     */
    final public function isEmpty()
    {
        return in_array($this->statusCode, [204, 304]);
    }

    /**
     * Get current content
     *
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set new content or override if exists content
     *
     * @param mixed $content
     *
     * @return void
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Alias for setContent and return this instance
     *
     * @param mixed $content
     *
     * @return $this
     */
    public function content($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Insert header scollection to response
     *
     * @param array $headers
     *
     * @return $this
     */
    public function headers(array $headers = [])
    {
        return $this->withHeaders($headers);
    }

    /**
     * Alias for `headers(array $headers = [])`
     *
     * @param array $headers
     *
     * @return $this
     */
    public function withHeaders(array $headers = [])
    {
        foreach ($headers as $key => $value) {
            $this->headers->set($key, $value);
        }

        return $this;
    }

    /**
     * Insert header to response
     *
     * @param string $key
     * @param mixed $value
     *
     * @return $this
     */
    public function header($key, $value)
    {
        return $this->withHeader($key, $value);
    }

    /**
     * Alias for `header(string key, mixed $value)`
     *
     * @param string $key
     * @param mixed $value
     *
     * @return $this
     */
    public function withHeader($key, $value)
    {
        $this->headers->set($key, $value);

        return $this;
    }

    /**
     * Set status code
     *
     * @param int $status
     *
     * @return void
     */
    public function setStatusCode(int $status)
    {
        $this->statusCode = $status;
    }

    /**
     * Set status code and return this instance
     *
     * @param int $status
     *
     * @return $this
     */
    public function status(int $status)
    {
        $this->statusCode = $status;

        return $this;
    }

    /**
     * Returns current status code
     *
     * @return int
     */
    public function statusCode()
    {
        return $this->statusCode;
    }

    /**
     * Returns current status text
     *
     * @return string
     */
    public function statusText()
    {
        return $this->statusText;
    }

    /**
     * Returns current HTTP version
     *
     * @return void
     */
    public function getProtocolVersion()
    {
        return $this->version;
    }

    /**
     * Set HTTP version
     *
     * @param string $version
     *
     * @return $this
     */
    public function setProtocolVersion($version)
    {
        $this->version = $version;

        return $this;
    }
}
