<?php

namespace Swilen\Http\Common;

use Swilen\Shared\Support\Arrayable;
use Swilen\Shared\Support\Jsonable;

final class HttpTransformJson
{
    /**
     * The transform json encoding options.
     *
     * @var int
     */
    private $encodingOptions = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;

    /**
     * The content to transform as json.
     *
     * @var mixed
     */
    private $content;

    /**
     * Common json serialize Exception messages.
     *
     * @var array<int, string>
     */
    public static $errorMessages = [
        JSON_ERROR_NONE => 'No error has occurred',
        JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded',
        JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON',
        JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
        JSON_ERROR_SYNTAX => 'Syntax error',
        JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded',
    ];

    /**
     * @param string|array|object $content
     *
     * @return void
     */
    public function __construct($content)
    {
        $this->content = $content;
    }

    /**
     * Transform given content as json.
     *
     * @param int $encodingOptions
     *
     * @return string
     */
    public function encode(int $encodingOptions = null)
    {
        @json_decode('[]');

        $content = json_encode($this->content, $encodingOptions ?: $this->encodingOptions);

        if (!$this->jsonSerializedWithoutErrors($content)) {
            $this->handleJsonException('Failed encode to json');
        }

        return $content;
    }

    /**
     * Transform given content as valid php array or object.
     *
     * @param bool $assoc
     * @param int  $encodingOptions
     *
     * @return array|object
     */
    public function decode(bool $assoc = false, int $decodingOptions = 0)
    {
        @json_decode('[]');

        $content = json_decode($this->content, $assoc, 512, $decodingOptions);

        if (!$this->jsonSerializedWithoutErrors($content)) {
            $this->handleJsonException('Failed decode from json');
        }

        return $content;
    }

    /**
     * Determine given value is serialized to withouts errors.
     *
     * @param mixed $content
     *
     * @return bool
     */
    public function jsonSerializedWithoutErrors($content)
    {
        return ($content !== null || $content !== false) && json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Create exception for json failed in serialization.
     *
     * @param string $info
     *
     * @return void
     *
     * @throws \JsonException
     */
    private function handleJsonException(string $info)
    {
        $code    = json_last_error();
        $message = static::$errorMessages[$code] ?? json_last_error_msg();

        throw new \JsonException($info.': '.$message, $code);
    }

    /**
     * Determine if the given content should be turned into JSON.
     *
     * @param mixed $content
     *
     * @return bool
     */
    public static function shouldBeJson($content)
    {
        return $content instanceof Arrayable ||
               $content instanceof Jsonable ||
               $content instanceof \ArrayObject ||
               $content instanceof \JsonSerializable ||
               $content instanceof \stdClass ||
               is_array($content);
    }

    /**
     * Morph the given content into Array for after serialization.
     *
     * @param mixed $content
     *
     * @return mixed|array
     */
    public static function morphToJsonable($content)
    {
        if ($content instanceof Arrayable) {
            return $content->toArray();
        }

        if ($content instanceof \JsonSerializable) {
            return $content->jsonSerialize();
        }

        return $content;
    }
}
