<?php

namespace Swilen\Http;

use Swilen\Http\Common\Http;
use Swilen\Http\Common\SupportResponse;
use Swilen\Http\Component\ResponseHeaderHunt;
use Swilen\Http\Contract\ResponseContract;

class Response extends SupportResponse implements ResponseContract
{
    /**
     * The response headers collection.
     *
     * @var \Swilen\Http\Component\ResponseHeaderHunt
     */
    public $headers;

    /**
     * The parsed content as string or resource for put into client.
     *
     * @var string|resource
     */
    protected $content;

    /**
     * The http version for the response.
     *
     * @var string
     */
    protected $version;

    /**
     * The status code for response.
     *
     * @var int
     */
    protected $statusCode;

    /**
     * The status text for response.
     *
     * @var string
     */
    protected $statusText;

    /**
     * The charset encoding for response.
     *
     * @var string
     */
    protected $charset = 'utf-8';

    /**
     * Create new response instance.
     *
     * @param mixed $content The content for send client
     * @param int   $status  The http status for response
     * @param array $headers The headers collection for response
     *
     * @return void
     */
    public function __construct($content = null, int $status = 200, array $headers = [])
    {
        $this->headers = new ResponseHeaderHunt($headers);

        $this->setStatusCode($status);
        $this->setContent($content);
        $this->setProtocolVersion('1.0');
    }

    /**
     * Prepares the Response before it is sent to the client.
     *
     * @param \Swilen\Http\Request $request
     *
     * @return $this
     */
    public function prepare(Request $request)
    {
        if ($this->isInformational() || $this->isEmpty()) {
            $this->prepareEmptyResponse();
        }
        // Add headers when http state allows it.
        else {
            // Add the content-type and charset when not provided.
            $charset = $this->charset ?: 'utf-8';
            if (!$this->headers->has('Content-Type')) {
                $this->headers->set('Content-Type', 'text/html; charset='.$charset);
            } elseif (stripos($this->headers->get('Content-Type'), 'text/') === 0 && stripos($this->headers->get('Content-Type'), 'charset') === false) {
                // add the charset
                $this->headers->set('Content-Type', $this->headers->get('Content-Type').'; charset='.$charset);
            }

            // Fix Content-Length
            if ($this->headers->has('Transfer-Encoding')) {
                $this->headers->remove('Content-Length');
            }

            // @see https://www.rfc-editor.org/rfc/rfc7231#section-4.3.2
            if ($request->getMethod() === Http::METHOD_HEAD) {
                $this->setContent(null);
                $this->headers->set('Content-Length', $this->headers->get('Content-Length'));
            }
        }

        if ('HTTP/1.0' != $request->server->get('SERVER_PROTOCOL')) {
            $this->setProtocolVersion('1.1');
        }

        if ('1.1' == $this->getProtocolVersion() && mb_strpos($this->headers->get('Cache-Control', ''), 'no-cache') !== false) {
            $this->headers->set('pragma', 'no-cache');
            $this->headers->set('expires', -1);
        }

        return $this;
    }

    /**
     * Prepare headers and content for empty response.
     *
     * @return void
     */
    protected function prepareEmptyResponse()
    {
        $this->setContent(null);
        $this->headers->remove('Content-Type');
        $this->headers->remove('Content-Length');
        // prevent PHP from sending the Content-Type header based on default_mimetype
        @ini_set('default_mimetype', '');
    }

    /**
     * Terminate http request and send content to client.
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
     * Sends content for the current web response.
     *
     * @return $this
     */
    protected function sendResponseContent()
    {
        $this->morphSendContent($this->content);

        return $this;
    }

    /**
     * Sends HTTP headers for the current web response.
     *
     * @return $this
     */
    protected function sendResponseHeaders()
    {
        if (headers_sent() === false) {
            $this->headers->each(function ($name, $value) {
                header($name.': '.$value, strcasecmp($name, 'Content-Type') === 0, $this->statusCode);
            });

            $this->sendStatusLine();
        }

        return $this;
    }

    /**
     * Send status line to response. `HTTP/1.1 200 OK`.
     *
     * @return void
     */
    protected function sendStatusLine()
    {
        header(
            sprintf('HTTP/%s %s %s', $this->version, $this->statusCode, $this->statusText), true, $this->statusCode
        );
    }

    /**
     * Morph send content to client.
     *
     * @param mixed $content
     *
     * @return void
     */
    private function morphSendContent($content)
    {
        echo (string) $content;
    }

    /**
     * Is response invalid?
     *
     * @return bool
     */
    final public function isInvalid()
    {
        return $this->statusCode < 100 || $this->statusCode >= 600;
    }

    /**
     * Is the response a redirect?
     *
     * @return bool
     */
    final public function isRedirection()
    {
        return $this->statusCode >= 300 && $this->statusCode < 400;
    }

    /**
     * Is there a client error?
     *
     * @return bool
     */
    final public function isClientError()
    {
        return $this->statusCode >= 400 && $this->statusCode < 500;
    }

    /**
     * Was there a server side error?
     *
     * @return bool
     */
    final public function isServerError()
    {
        return $this->statusCode >= 500 && $this->statusCode < 600;
    }

    /**
     * Is the response OK?
     *
     * @return bool
     */
    final public function isOk()
    {
        return $this->statusCode === 200;
    }

    /**
     * Is the response forbidden?
     *
     * @return bool
     */
    final public function isForbidden()
    {
        return $this->statusCode === 403;
    }

    /**
     * Is the response a not found error?
     *
     * @return bool
     */
    final public function isNotFound()
    {
        return $this->statusCode === 404;
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
        return in_array($this->statusCode, [204, 205, 304], true);
    }

    /**
     * Modifies the response so that it conforms to the rules defined for a 304 status code.
     *
     * @return $this
     */
    final public function setNotModified()
    {
        $this->setStatusCode(304);
        $this->setContent(null);

        // Remove headers that MUST NOT be included with 304 Not Modified responses
        $this->headers->removeAt(['Allow', 'Content-Encoding', 'Content-Language', 'Content-Length', 'Content-MD5', 'Content-Type', 'Last-Modified']);

        return $this;
    }

    /**
     * Get current content.
     *
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set new content or override if exists content.
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
     * Alias for setContent and return this instance.
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
     * Insert header scollection to response.
     *
     * @return $this
     */
    public function headers(array $headers = [])
    {
        return $this->withHeaders($headers);
    }

    /**
     * Alias for `headers(array $headers = [])`.
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
     * Insert header to response.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function header($key, $value)
    {
        return $this->withHeader($key, $value);
    }

    /**
     * Alias for `header(string key, mixed $value)`.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function withHeader($key, $value)
    {
        $this->headers->set($key, $value);

        return $this;
    }

    /**
     * Set status code.
     *
     * @param int    $status
     * @param string $text
     *
     * @return void
     */
    final public function setStatusCode(int $status, string $text = null)
    {
        $this->statusCode = $status;
        $this->statusText = $text ?? static::STATUS_TEXTS[$this->getStatusCode()] ?? 'Internal Server Error';
    }

    /**
     * Set status code and return this instance.
     *
     * @return $this
     */
    public function status(int $status)
    {
        $this->setStatusCode($status);

        return $this;
    }

    /**
     * Returns current status code.
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Returns current status text.
     *
     * @return string
     */
    public function statusText()
    {
        return $this->statusText;
    }

    /**
     * Returns current HTTP version.
     *
     * @return void
     */
    public function getProtocolVersion()
    {
        return $this->version;
    }

    /**
     * Set HTTP version.
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
