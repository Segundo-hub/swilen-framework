<?php

namespace Swilen\Http;

use Swilen\Http\Exception\HttpNotOverridableMethodException;

use Swilen\Http\Component\FileHunt;
use Swilen\Http\Component\HeaderHunt;
use Swilen\Http\Component\InputHunt;
use Swilen\Http\Component\ServerHunt;
use Swilen\Http\Common\HttpTransformJson;
use Swilen\Validation\Validator;
use Swilen\Contracts\Support\Arrayable;

final class Request implements \ArrayAccess, Arrayable
{
    /**
     * Http request headers collections
     *
     * @var \Swilen\Http\Component\HeaderHunt
     */
    public $headers;

    /**
     * Http server variables collections
     *
     * @var \Swilen\Http\Component\ServerHunt
     */
    public $server;

    /**
     * Http files collections
     *
     * @var \Swilen\Http\Component\FileHunt
     */
    public $files;

    /**
     * Http params collections via $_POST
     *
     * @var \Swilen\Http\Component\InputHunt
     */
    public $request;

    /**
     * Http params collections via $_GET
     *
     * @var \Swilen\Http\Component\InputHunt
     */
    public $query;

    /**
     * The content of request body decoded as json
     *
     * @var \Swilen\Http\Component\InputHunt
     */
    public $json;

    /**
     * Http request raw body content
     *
     * @var string|resource|false|null
     */
    protected $content;

    /**
     * Http current request method
     *
     * @var string
     */
    protected $method;

    /**
     * Current http request uri
     *
     * @var string
     */
    protected $currentUri;

    /**
     * Http body params accepted for override
     *
     * @var string[]
     */
    protected $acceptMethodOverrides = ['DELETE', 'PUT'];

    /**
     * Http current user logged prvided by token
     *
     * @var mixed
     */
    protected $user;

    /**
     * Http request mime-types
     *
     * @var array<string, string>
     */
    protected $requestMimeTypes = [
        'html'  => ['text/html', 'application/xhtml+xml'],
        'txt'   => ['text/plain'],
        'js'    => ['application/javascript', 'application/x-javascript', 'text/javascript'],
        'css'   => ['text/css'],
        'json'  => ['application/json', 'application/x-json'],
        'xml'   => ['text/xml', 'application/xml', 'application/x-xml'],
        'rdf'   => ['application/rdf+xml'],
        'atom'  => ['application/atom+xml'],
        'rss'   => ['application/rss+xml'],
        'form'  => ['application/x-www-form-urlencoded', 'multipart/form-data'],
        'jsonld' => ['application/ld+json'],
    ];

    /**
     * Create new request instance from incoming request
     *
     * @param array $server The server variables collection
     * @param array $headers
     * @param array $files
     * @param array $request
     * @param array $query
     *
     * @return void
     */
    public function __construct(array $server = [], array $files = [], array $request = [], array $query = [])
    {
        $this->server  = new ServerHunt($server);
        $this->headers = new HeaderHunt($this->server->headers());
        $this->files   = new FileHunt($files);
        $this->request = new InputHunt($request);
        $this->query   = new InputHunt($query);
    }

    /**
     * Create new request instance from static method and initialized with php superglobals
     *
     * @return \Swilen\Http\Request
     */
    public static function create()
    {
        return new static($_SERVER, $_FILES, $_POST, $_GET);
    }

    /**
     * Get request method
     *
     * @return string
     *
     * @see getRealMethod()
     */
    public function getMethod()
    {
        $this->method = strtoupper($this->server->filter('REQUEST_METHOD', 'GET'));

        if ($this->method !== 'POST') {
            return $this->method;
        }

        $method = $this->server->filter('HTTP_X_METHOD_OVERRIDE')
            ?: $this->server->filter('HTTP_X_HTTP_METHOD_OVERRIDE');

        if (is_null($method) && !$method) {
            return $this->method;
        }

        $method = strtoupper($method);

        if (\in_array($method, $this->acceptMethodOverrides, true)) {
            return $this->method = $method;
        }

        throw new HttpNotOverridableMethodException(sprintf(
            '%s non-overwritable method',
            $method
        ), 400);
    }

    /**
     * Gets the "real" request method.
     *
     * @return string
     *
     * @see getMethod()
     */
    public function getRealMethod()
    {
        return strtoupper($this->server->filter('REQUEST_METHOD', 'GET'));
    }

    /**
     * Make and return request action
     *
     * @return string
     */
    public function getAction()
    {
        $path = preg_replace('/\\?.*/', '', $this->filteredActionRequest());

        return $this->trim($path);
    }

    /**
     * Returns REQUEST_URI replaced with app base uri
     *
     * @return string
     */
    private function filteredActionRequest()
    {
        if (getenv('APP_BASE_URI') && !empty(getenv('APP_BASE_URI'))) {
            return preg_replace('#^' . getenv('APP_BASE_URI') . '#', '', $this->server->get('REQUEST_URI'));
        }

        return $this->server->get('REQUEST_URI');
    }

    /**
     * Strip slashes from current uri
     *
     * @param string $path
     *
     * @return string
     */
    private function trim(string $path)
    {
        $path = ltrim(rtrim($path ?: '', '/'), '/');

        return $path === '/' ? $path : '/' . $path;
    }

    /**
     * Check if uri contains query string
     *
     * @return bool
     */
    public function hasQueryString()
    {
        return strpos($this->server->get('REQUEST_URI'), '?') !== false;
    }

    /**
     * Determine and decode content type request is json, return request content type is not json
     *
     * @return \Swilen\Http\Component\InputHunt
     */
    public function getInputSource()
    {
        if ($this->isJsonRequest()) {
            $this->json = new InputHunt($this->transformInputSource()->decode(true));

            return $this->json;
        }

        return $this->request;
    }

    /**
     * Transform input source to json valid encode or decode
     *
     * @return \Swilen\Http\Common\HttpTransformJson
     */
    public function transformInputSource()
    {
        return new HttpTransformJson($this->getContent());
    }

    /**
     * Set user to current request
     *
     * @param object|array $user
     *
     * @return $this
     */
    public function withUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user from request
     *
     * @return array|object
     */
    public function user()
    {
        return $this->user;
    }

    /**
     * Get bearer token from authorization header
     *
     * @return string|null
     */
    public function bearerToken()
    {
        if (preg_match('/Bearer\s(\S+)/', $this->headers->get('Authorization'), $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Return file from filename
     *
     * @param string $filename The name of file
     *
     * @return \Swilen\Http\Component\UploadedFile|null
     */
    public function file(string $filename)
    {
        return $this->files->get($filename);
    }

    /**
     * Get current body content request
     *
     * @return resource|string|false|null
     */
    public function getContent()
    {
        if (\is_resource($this->content)) {
            rewind($this->content);

            return stream_get_contents($this->content);
        }

        if (!$this->content) {
            $this->content = file_get_contents('php://input');
        }

        return $this->content;
    }

    /**
     * Checks whether or not the method is safe.
     *
     * @return bool
     */
    final public function isMethodSafe()
    {
        return \in_array($this->getMethod(), ['GET', 'HEAD', 'OPTIONS', 'TRACE'], true);
    }

    /**
     * Determine if request content type is json
     *
     * @return boolean
     */
    private function isJsonRequest()
    {
        foreach (['/json', '+json'] as $contentType) {
            if (strpos($this->headers->get('Content-Type'), $contentType) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get all variables captured and stored
     *
     * @return array
     */
    public function all()
    {
        return array_replace_recursive(
            (array) $this->getInputSource()->all(),
            (array) $this->query->all(),
            (array) $this->files->all(),
        );
    }

    /**
     * Validate request values with rules
     *
     * @param array $rules
     *
     * @return \Swilen\Validation\Validator
     */
    public function validate(array $rules)
    {
        return Validator::make($this->all(), $rules);
    }

    /**
     * Get all of the input and files from the request as array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->all();
    }

    /**
     * Determine if the given offset exists.
     *
     * @param string $offset
     *
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return $this->getInputSource()->has($offset);
    }

    /**
     * Get the value at the given offset.
     *
     * @param string $offset
     *
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    /**
     * Set the value at the given offset.
     *
     * @param string $offset
     * @param mixed $value
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        $this->getInputSource()->set($offset, $value);
    }

    /**
     * Remove the value at the given offset.
     *
     * @param string $offset
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        $this->getInputSource()->remove($offset);
    }

    /**
     * Check if an input element is set on the request.
     *
     * @param string $key
     *
     * @return bool
     */
    public function __isset($key)
    {
        return !is_null($this->__get($key));
    }

    /**
     * Set value to input source
     *
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    public function __set($key, $value)
    {
        $this->getInputSource()->set($key, $value);
    }

    /**
     * Get an input element from the request.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        $store = (object) $this->all();

        return $store->{$key};
    }
}
