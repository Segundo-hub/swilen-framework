<?php

namespace Swilen\Http\Response;

use Swilen\Http\Response;
use Swilen\Shared\Support\Json;

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
        parent::__construct(null, $status, ['Content-Type' => self::CONTENT_TYPE] + $headers);

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

        if ($json && !is_string($content) && !is_numeric($content)) {
            throw new \TypeError(sprintf('"%s": If $json is set to true, argument $data must be a string, "%s" given.', __METHOD__, get_debug_type($content)));
        }

        $content = (!$json && $content === null) ? new \ArrayObject() : $content;

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
        return Json::morphToJson($content, $this->encodingOptions);
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
