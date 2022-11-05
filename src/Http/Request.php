<?php

namespace Swilen\Http;

use Swilen\Http\Common\HttpTransformJson;
use Swilen\Http\Common\SupportRequest;
use Swilen\Http\Component\FileHunt;
use Swilen\Http\Component\HeaderHunt;
use Swilen\Http\Component\InputHunt;
use Swilen\Http\Component\ServerHunt;
use Swilen\Http\Exception\HttpNotOverridableMethodException;
use Swilen\Validation\Validator;

class Request extends SupportRequest implements \ArrayAccess
{
    /**
     * Http request headers collections.
     *
     * @var \Swilen\Http\Component\HeaderHunt
     */
    public $headers;

    /**
     * Http server variables collections.
     *
     * @var \Swilen\Http\Component\ServerHunt
     */
    public $server;

    /**
     * Http files collections.
     *
     * @var \Swilen\Http\Component\FileHunt
     */
    public $files;

    /**
     * Http params collections via $_POST.
     *
     * @var \Swilen\Http\Component\InputHunt
     */
    public $request;

    /**
     * Http params collections via $_GET.
     *
     * @var \Swilen\Http\Component\InputHunt
     */
    public $query;

    /**
     * The content of request body decoded as json.
     *
     * @var \Swilen\Http\Component\InputHunt
     */
    public $json;

    /**
     * Http request raw body content.
     *
     * @var string|resource|bool|null
     */
    protected $content;

    /**
     * Http current request method.
     *
     * @var string
     */
    protected $method;

    /**
     * Current http request uri.
     *
     * @var string
     */
    protected $uri;

    /**
     * Current http request path info.
     *
     * @var string
     */
    protected $pathInfo;

    /**
     * Http body params accepted for override.
     *
     * @var string[]
     */
    protected $acceptMethodOverrides = ['DELETE', 'PUT'];

    /**
     * Http current user logged prvided by token.
     *
     * @var mixed
     */
    protected $user;

    /**
     * Create new request instance from incoming request.
     *
     * @param array                $server  The server variables collection
     * @param array                $headers The request headers collection
     * @param array                $files   The request files collection
     * @param array                $request The request variables sending from client collection
     * @param array                $query   The request query params or send from client into form
     * @param string|resource|null $content The raw body data
     *
     * @return void
     */
    public function __construct(array $server = [], array $files = [], array $request = [], array $query = [], $content = null)
    {
        $this->server = new ServerHunt($server);
        $this->headers = new HeaderHunt($this->server->headers());
        $this->files = new FileHunt($files);
        $this->request = new InputHunt($request);
        $this->query = new InputHunt($query);

        $this->content = $content;
    }

    /**
     * Create new request instance from static method.
     *
     * @param array                $server  The server variables collection
     * @param array                $headers The request headers collection
     * @param array                $files   The request files collection
     * @param array                $request The request variables sending from client collection
     * @param array                $query   The request query params or send from client into form
     * @param string|resource|null $content The raw body data
     *
     * @return \Swilen\Http\Request
     */
    public static function createFrom(array $server = [], array $files = [], array $request = [], array $query = [], $content = null)
    {
        return new static($server, $files, $request, $query, $content);
    }

    /**
     * Create new request instance from PHP SuperGlobals.
     *
     * @return \Swilen\Http\Request
     */
    public static function create()
    {
        return new static($_SERVER, $_FILES, $_POST, $_GET);
    }

    /**
     * Creates a Request based on a given URI and configuration.
     *
     * @param string               $uri        The URI
     * @param string               $method     The HTTP method
     * @param array                $parameters The query (GET) or request (POST) parameters
     * @param array                $files      The request files ($_FILES)
     * @param array                $server     The server parameters ($_SERVER)
     * @param string|resource|null $content    The raw body data
     *
     * @return \Swilen\Http\Request
     */
    public static function make(string $uri, string $method = 'GET', array $parameters = [], array $files = [], array $server = [], $content = null)
    {
        [$server, $files, $request, $query, $content] = parent::makeFetchRequest($uri, $method, $parameters, $files, $server);

        return new static($server, $files, $request, $query, $content);
    }

    /**
     * Set method for request.
     *
     * @return $this
     */
    public function setMethod(string $method)
    {
        $this->method = strtoupper($method);
        $this->server->set('REQUEST_METHOD', $this->method);

        return $this;
    }

    /**
     * Get request method.
     *
     * @return string
     *
     * @see getRealMethod()
     */
    public function getMethod()
    {
        if ($this->method !== null) {
            return $this->method;
        }

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

        throw new HttpNotOverridableMethodException(sprintf('%s non-overwritable method', $method), 400);
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
     * Checks if the request method is of specified type.
     *
     * @return bool
     */
    public function isMethod(string $method)
    {
        return $this->getMethod() === strtoupper($method);
    }

    /**
     * Return current request path info.
     *
     * @return string
     */
    public function getPathInfo()
    {
        if ($this->pathInfo !== null) {
            return $this->pathInfo;
        }

        return $this->pathInfo = $this->trimed(preg_replace('/\\?.*/', '', $this->filteredRequestUri()));
    }

    /**
     * Returns REQUEST_URI replaced with app base uri.
     *
     * @return string
     */
    private function filteredRequestUri()
    {
        if (isset($_ENV['APP_BASE_URI']) && !empty($base = $_ENV['APP_BASE_URI'])) {
            return $this->uri = preg_replace('#^'.$base.'#', '', $this->server->get('REQUEST_URI'));
        }

        return $this->uri = $this->server->get('REQUEST_URI');
    }

    /**
     * Remove slashes at the beginning and end of the path.
     *
     * @param string|null $path
     *
     * @return string
     */
    private function trimed($path)
    {
        return '/'.trim($path ?: '/', '\/');
    }

    /**
     * Check if uri contains query string.
     *
     * @return bool
     */
    public function hasQueryString()
    {
        return strpos($this->server->get('REQUEST_URI'), '?') !== false;
    }

    /**
     * Determine and decode content type request is json, return request content type is not json.
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
     * Transform input source to json valid encode or decode.
     *
     * @return \Swilen\Http\Common\HttpTransformJson
     */
    public function transformInputSource()
    {
        return new HttpTransformJson($this->getContent());
    }

    /**
     * Set user to current request.
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
     * Get user from request.
     *
     * @return array|object
     */
    public function user()
    {
        return $this->user;
    }

    /**
     * Get bearer token from authorization header.
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
     * Get current body content request.
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
     * Determine if request content type is json.
     *
     * @return bool
     */
    private function isJsonRequest()
    {
        foreach (['/json', '+json'] as $contentType) {
            if (mb_strpos($this->headers->get('Content-Type'), $contentType) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all variables captured and stored.
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
     * Get input value from input source.
     *
     * @param string|int $key
     * @param mixed      $default
     *
     * @return mixed
     */
    public function input($key, $default = null)
    {
        return $this->getInputSource()->get($key, $default);
    }

    /**
     * Get query value from query collection.
     *
     * @param string|int $key
     * @param mixed      $default
     *
     * @return mixed
     */
    public function query($key, $default = null)
    {
        return $this->query->get($key, $default);
    }

    /**
     * Get file(s) from UploadedFiles collection.
     *
     * @param string $filename The filename
     *
     * @return \Swilen\Http\Component\UploadedFile|null
     */
    public function file(string $filename)
    {
        return $this->files->get($filename);
    }

    /**
     * Validate request values with rules.
     *
     * @return \Swilen\Validation\Validator
     */
    public function validate(array $rules)
    {
        return Validator::make($this->all(), $rules);
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
     * @param mixed  $value
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
     * Set value to input source.
     *
     * @param string $key
     * @param mixed  $value
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
        return $this->all()[$key];
    }
}
