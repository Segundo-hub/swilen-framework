<?php

namespace Swilen\Http\Response;

use Swilen\Http\Common\HttpTransformJson;
use Swilen\Http\Component\ResponseHeaderHunt;
use Swilen\Http\Response;
use Swilen\Shared\Support\Jsonable;

class JsonResponse extends Response
{
    /**
     * The original content to pased in given instance.
     *
     * @var mixed
     */
    protected $original;

    /**
     * The body json encoding options.
     *
     * @var int
     */
    protected $encodingOptions = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;

    /**
     * Create new JsonResponse instance and prepare content to json.
     *
     * @param mixed $content The content for transforming to json
     * @param bool  $json    Indicates id content is parsed as json
     *
     * @return void
     */
    public function __construct($content = null, int $status = 200, array $headers = [], bool $json = false)
    {
        $this->prepareJsonResponse($headers);
        $this->setStatusCode($status);

        $this->setContent($content, $json);
    }

    /**
     * Set new content as json or override if exists.
     *
     * @param mixed $content
     * @param bool  $json    - Indicates id content is parsed as json
     *
     * @return void
     *
     * @throws \JsonException
     * @throws \TypeError
     */
    public function setContent($content, bool $json = false)
    {
        $this->original = $content;

        $content = $content ? $content : new \ArrayObject([]);

        if (!$json && !HttpTransformJson::shouldBeJson($content)) {
            throw new \TypeError(sprintf('The type "%s" is not JSON serializable', get_debug_type($content)));
        }

        parent::setContent($json ? $content : $this->toJson($content));
    }

    /**
     * Serialize reponse content into json.
     *
     * @param mixed $content
     *
     * @return string|false
     *
     * @throws \JsonException
     */
    private function toJson($content = null)
    {
        if ($content instanceof Jsonable) {
            return $content->toJson($this->encodingOptions);
        }

        if ($content) {
            return (new HttpTransformJson(HttpTransformJson::morphToJsonable($content)))
                ->encode($this->encodingOptions);
        }

        return $content;
    }

    /**
     * Prepares the JsonResponse before it is sent to the client.
     *
     * @return void
     */
    protected function prepareJsonResponse(array $headers)
    {
        $this->headers = new ResponseHeaderHunt($headers);

        $this->headers->set('Content-Type', 'application/json');
    }

    /**
     * Create new JsonResponse instance from json.
     *
     * @param string $content - The json serialized
     * @param int    $status  - The http status code
     * @param array  $headers - The headers collection
     *
     * @return static
     */
    public static function fromJson(string $content, int $status = 200, $headers = [])
    {
        return new static($content, $status, $headers, true);
    }
}
