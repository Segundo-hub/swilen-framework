<?php

namespace Swilen\Http;

use Swilen\Http\Common\HttpResponseSupport;
use Swilen\Http\Common\HttpTransformJson;
use Swilen\Http\Component\ResponseHeaderHunt;

class Response extends HttpResponseSupport
{
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
    protected $charset;

    /**
     * The body json encoding options
     *
     * @var int
     */
    protected $encodingOptions = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;

    /**
     *  Set initial headers for response
     *
     *  @var array<string, string>
     */
    protected $boot_headers = [
        'Content-Type' => 'application/json; charset=utf-8',
        'Access-Control-Allow-Methods' => 'OPTIONS, GET, POST, PUT, PATCH, DELETE, TRACE, CONNECT',
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Credentials' => true,
        'Access-Control-Max-Age' => 86400
    ];

    /**
     * Create new response instance
     *
     * @param resource|string|array|object|null $content
     * @param int $status
     * @param array $headers
     *
     * @return void
     */
    public function __construct($content = '', int $status = 200, array $headers = [])
    {
        $this->headers = $this->initializeHeaders($headers);
        $this->setContent($this->toJson($content));
        $this->setStatusCode($status);
        $this->version = '1.1';
    }

    /**
     * Initialize response with default headers
     *
     * @param array $headers
     *
     * @return \Swilen\Http\Component\ResponseHeaderHunt
     */
    private function initializeHeaders(array $headers = [])
    {
        return new ResponseHeaderHunt(array_merge(
            (array) $headers,
            (array) $this->boot_headers
        ));
    }

    /**
     * Send content to client
     *
     * @param resource|string|array|object|null $content
     * @param int $status
     * @param array $headers
     *
     * @return $this
     */
    public function send($content = null, int $status = 200, array $headers = [])
    {
        $this->updateResponseOptions($content, $status, $headers);

        return $this;
    }

    /**
     * Update default response options, content, headers and statusCode
     *
     * @param resource|string|array|object|null $content
     * @param int $status
     * @param array $headers
     *
     * @return void
     */
    protected function updateResponseOptions($content = null, int $status = 200, array $headers = [])
    {
        $this->setContent($this->toJson($content));

        $this->setStatusCode($status);

        $this->headers($headers);
    }

    /**
     * Transform response content to json
     *
     * @param resource|string|array|null $content
     *
     * @return string|false
     */
    private function toJson($content = null)
    {
        $content = (new HttpTransformJson($content))->encode($this->encodingOptions);

        return $content;
    }

    public function prepare(Request $request)
    {
        if ($this->isInformational() || $this->isEmpty()) {
            $this->setContent(null);
            $this->headers->remove('Content-Type');
            $this->headers->remove('Content-Length');
            ini_set('default_mimetype', '');
        } else {
            if ($this->headers->has('Transfer-Encoding')) {
                $this->headers->remove('Content-Length');
            }

            if ($request->getRealMethod() === 'HEAD') {
                $length = $this->headers->get('Content-Length');
                $this->setContent(null);
                if ($length) {
                    $this->headers->set('Content-Length', $length);
                }
            }
        }

        return $this;
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
     * Terminate request with send content abnd header to client
     *
     * @return $this
     */
    public function terminate()
    {
        $this->sendResponseHeaders();

        $this->sendResponseContent();

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } elseif (!in_array(PHP_SAPI, ['cli', 'phpdbg'], true)) {
            static::closeOutputBuffer(0, true);
        }

        return $this;
    }

    /**
     * Send dataContent into client
     *
     * @return \Swilen\Http\Response
     */
    protected function sendResponseContent()
    {
        echo $this->getContent();

        return $this;
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
     * Print data into client
     *
     * @return \Swilen\Http\Response
     */
    protected function sendResponseHeaders()
    {
        if (headers_sent()) {
            return $this;
        }

        foreach ($this->headers->all() as $key => $value) {
            header($key.':'.$value, true, $this->statusCode());
        }

        header($this->transformHeader(), true, $this->statusCode());

        return $this;
    }

    /**
     * Transform header for response
     *
     * @return string
     */
    private function transformHeader()
    {
        return sprintf('HTTP/%s %s %s', $this->version(), $this->statusCode(), $this->writeStatusText());
    }

    /**
     * To normalize status text for write into header
     *
     * @return string|null
     */
    protected function writeStatusText()
    {
        return isset(static::$statusTexts[$this->statusCode()])
            ? static::$statusTexts[$this->statusCode()]
            : NULL;
    }

    /**
     * Set collection response headers
     *
     * @return $this
     */
    public function headers(array $headers = [])
    {
        foreach ($headers as $key => $value) {
            $this->headers->set($key, $value);
        }

        return $this;
    }

    public function header($key, $value)
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
     * @return string
     */
    public function version()
    {
        return $this->version;
    }

    public function setProtocolVersion($version)
    {
        $this->version = $version;

        return $this;
    }
}
