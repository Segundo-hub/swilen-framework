<?php

namespace Swilen\Http\Factories;

use Swilen\Http\Common\HttpTransformJson;
use Swilen\Http\Contract\ResponseFactory;
use Swilen\Http\Request;
use Swilen\Http\Response;

final class JsonResponseFactory implements ResponseFactory
{
    /**
     * The response instance
     *
     * @var \Swilen\Http\Response
     */
    protected $response;

    /**
     * The body json encoding options
     *
     * @var int
     */
    protected static $encodingOptions = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;

    /**
     * Create new json response factory
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
        $this->response = $response->withOptions($this->toJson($content), $status, $headers);
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
        if ($content) {
            return (new HttpTransformJson($content))->encode($this->encodingOptions());
        }

        return $content;
    }

    /**
     * Prepare response for json content-type
     *
     * {@inheritdoc}
     */
    public function prepare(Request $request)
    {
        $this->response->headers->set('Content-Type', 'application/json; charset=UTF-8');

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
        echo $this->response->getContent();
    }

    public static function withEncodingOptions(int $encodingOptions)
    {
        static::$encodingOptions = $encodingOptions;
    }

    public function encodingOptions()
    {
        return static::$encodingOptions;
    }
}
