<?php

namespace Swilen\Http\Response;

use Swilen\Http\Response;
use Swilen\Shared\Support\Arr;
use Swilen\Shared\Support\Json;
use Swilen\Shared\Support\Jsonable;

class JsonResponse extends Response
{
    /**
     * The mimeType for the response.
     *
     * @var string
     */
    public const CONTENT_TYPE = 'application/json; charset=UTF-8';

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
    protected $encodingOptions = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

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
        parent::__construct(null, $status, $headers + ['Content-Type' => self::CONTENT_TYPE]);

        $this->setBody($content, $json);
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
    public function setBody($content, bool $json = false)
    {
        $this->original = $content;

        $content = $content ? $content : new \ArrayObject([]);

        if (!$json && (!Json::shouldBeJson($content))) {
            throw new \TypeError(sprintf('The type "%s" is not JSON serializable', get_debug_type($content)));
        }

        parent::setBody($json ? $content : $this->toJson($content));
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
            return Json::from(Arr::morph($content))->encode($this->encodingOptions);
        }

        return $content;
    }

    /**
     * Create new JsonResponse instance from json.
     *
     * @param string $content The json serialized
     * @param int    $status  The http status code
     * @param array  $headers The headers collection
     *
     * @return static
     */
    public static function fromJson(string $content, int $status = 200, $headers = [])
    {
        return new static($content, $status, $headers, true);
    }
}
