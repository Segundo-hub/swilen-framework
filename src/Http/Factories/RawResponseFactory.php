<?php

namespace Swilen\Http\Factories;

use Swilen\Http\Contract\ResponseFactory;
use Swilen\Http\Request;
use Swilen\Http\Response;

final class RawResponseFactory implements ResponseFactory
{
    /**
     * The response instance
     *
     * @var \Swilen\Http\Response
     */
    protected $response;

    /**
     * Create new raw response factory
     *
     * @param \Swilen\Http\Response $response
     * @param resource|string|array|object|null $content
     * @param int $status
     * @param array $headers
     *
     * @return void
     */
    public function __construct(Response $response, $content = null, int $status = 200, array $headers = [])
    {
        $this->response = $response->withOptions($content, $status, $headers);
    }

    /**
     * Prepare response for raw content-type
     *
     * {@inheritdoc}
     */
    public function prepare(Request $request)
    {
        if (!$this->response->headers->has('Content-Type')) {
            $this->response->headers->set('Content-Type', 'plain/text; charset=UTF-8');
        }

        if ($this->response->isInformational() || $this->response->isEmpty()) {
            $this->response->setContent(null);
            $this->response->headers->remove('Content-Type');
            $this->response->headers->remove('Content-Length');
            ini_set('default_mimetype', '');
        } else {
            if ($this->response->headers->has('Transfer-Encoding')) {
                $this->response->headers->remove('Content-Length');
            }

            if ($request->getRealMethod() === 'HEAD') {
                $length = $this->response->headers->get('Content-Length');
                $this->response->setContent(null);
                if ($length) {
                    $this->response->headers->set('Content-Length', $length);
                }
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function sendContent()
    {
        $this->printContentDetermineByType($this->response->getContent());
    }

    /**
     * Determine for use strategie of print data into client
     */
    protected function printContentDetermineByType($content)
    {
        if (strpos($this->response->headers->get('Content-Type'), 'application/json') !== false) {
            echo $content;
        } else if (is_string($content) || $content instanceof \Stringable) {
            echo $content;
        } else {
            print_r($content);
        }
    }
}
