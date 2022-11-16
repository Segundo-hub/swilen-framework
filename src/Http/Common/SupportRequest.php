<?php

namespace Swilen\Http\Common;

class SupportRequest
{
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
     * @return static
     */
    public static function make(string $uri, string $method = 'GET', array $parameters = [], array $files = [], array $server = [], $content = null)
    {
        [$server, $files, $request, $query, $content] = self::createServerRequest($uri, $method, $parameters, $files, $server);

        return new static($server, $files, $request, $query, $content);
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
     * @return array
     */
    protected static function createServerRequest(string $uri, string $method = 'GET', array $parameters = [], array $files = [], array $server = [], $content = null)
    {
        $server = static::replaceServerVars($server, $method);

        $components = parse_url($uri);

        if (isset($components['host'])) {
            $server['SERVER_NAME'] = $components['host'];
            $server['HTTP_HOST']   = $components['host'];
        }

        if (isset($components['scheme'])) {
            if ($components['scheme'] === 'https') {
                $server['HTTPS']       = 'on';
                $server['SERVER_PORT'] = 443;
            } else {
                unset($server['HTTPS']);
                $server['SERVER_PORT'] = 80;
            }
        }

        if (isset($components['port'])) {
            $server['SERVER_PORT'] = $components['port'];
            $server['HTTP_HOST'] .= ':'.$components['port'];
        }

        if (isset($components['user'])) {
            $server['PHP_AUTH_USER'] = $components['user'];
        }

        if (isset($components['pass'])) {
            $server['PHP_AUTH_PW'] = $components['pass'];
        }

        if (!isset($components['path'])) {
            $components['path'] = '/';
        }

        switch (strtoupper($method)) {
            case 'POST':
            case 'PUT':
            case 'DELETE':
                if (!isset($server['CONTENT_TYPE'])) {
                    $server['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
                }
                // no break
            case 'PATCH':
                $request = $parameters;
                $query   = [];
                break;
            default:
                $request = [];
                $query   = $parameters;
                break;
        }

        $queryString = '';
        if (isset($components['query'])) {
            parse_str(html_entity_decode($components['query']), $qs);

            if ($query) {
                $query       = array_replace($qs, $query);
                $queryString = http_build_query($query, '', '&');
            } else {
                $query       = $qs;
                $queryString = $components['query'];
            }
        } elseif ($query) {
            $queryString = http_build_query($query, '', '&');
        }

        $server['REQUEST_URI']  = $components['path'].($queryString !== '' ? '?'.$queryString : '');
        $server['QUERY_STRING'] = $queryString;

        return [$server, $files, $request, $query, $content];
    }

    /**
     * Replace $_SERVER variables with given server values.
     *
     * @param array<string, mixed> $server
     * @param string               $method
     *
     * @return array<string, mixed>
     */
    private static function replaceServerVars(array $server = [], string $method = 'GET')
    {
        return array_replace([
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => 80,
            'HTTP_HOST' => 'localhost',
            'HTTP_USER_AGENT' => 'Swilen',
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'HTTP_ACCEPT_LANGUAGE' => 'en-us,en;q=0.5',
            'HTTP_ACCEPT_CHARSET' => 'ISO-8859-1,utf-8;q=0.7,*;q=0.7',
            'REMOTE_ADDR' => '127.0.0.1',
            'SCRIPT_NAME' => '',
            'SCRIPT_FILENAME' => '',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'REQUEST_TIME' => time(),
            'REQUEST_TIME_FLOAT' => microtime(true),
            'PATH_INFO' => '',
            'REQUEST_METHOD' => strtoupper($method),
        ], $server);
    }

    /**
     * Http request mime-types.
     *
     * @var array<string, string[]>
     */
    protected $requestMimeTypes = [
        'html' => ['text/html', 'application/xhtml+xml'],
        'txt' => ['text/plain'],
        'js' => ['application/javascript', 'application/x-javascript', 'text/javascript'],
        'css' => ['text/css'],
        'json' => ['application/json', 'application/x-json'],
        'xml' => ['text/xml', 'application/xml', 'application/x-xml'],
        'rdf' => ['application/rdf+xml'],
        'atom' => ['application/atom+xml'],
        'rss' => ['application/rss+xml'],
        'form' => ['application/x-www-form-urlencoded', 'multipart/form-data'],
        'jsonld' => ['application/ld+json'],
    ];
}
