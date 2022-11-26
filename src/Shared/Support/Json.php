<?php

namespace Swilen\Shared\Support;

final class Json
{
    /**
     * The transform json encoding options.
     *
     * @var int
     */
    private $encodingOptions = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

    /**
     * The transform jsonp encoding options.
     *
     * @var int
     */
    private $encodingJsonpOptions = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;

    /**
     * The content for transform as json.
     *
     * @var array|string|mixed
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
     * Create new Json serializer instance.
     *
     * @param array|string|mixed $content
     *
     * @return void
     */
    public function __construct($content)
    {
        $this->content = $content;
    }

    /**
     * Create new instance from given content.
     *
     * @param array|string|mixed $content
     *
     * @return static
     */
    public static function from($content)
    {
        return new static($content);
    }

    /**
     * Transform given content as json.
     *
     * @param int $encodingOptions
     *
     * @return string
     *
     * @throws \JsonException
     */
    public function encode(int $encodingOptions = null)
    {
        @json_decode('[]');

        $content = json_encode($this->content, $encodingOptions ?: $this->encodingOptions);

        if (!$this->jsonSerializedWithoutErrors($content)) {
            $this->handleJsonException('Failed encode json');
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
     *
     * @throws \InvalidArgumentException
     * @throws \JsonException
     */
    public function decode(bool $assoc = false, int $decodingOptions = 0)
    {
        @json_decode('[]');

        if (!is_string($this->content)) {
            throw new \InvalidArgumentException(sprintf('Invalid data for decode. Expect string, found "%s"', get_debug_type($this->content)));
        }

        $content = json_decode($this->content, $assoc, 512, $decodingOptions);

        if (!$this->jsonSerializedWithoutErrors($content)) {
            $this->handleJsonException('Failed decode json');
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
        $message = static::$errorMessages[$code] ?? json_last_error_msg() ?: 'Unknow error in encode/decode json';

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
               $content instanceof JsonSerializable ||
               $content instanceof \stdClass ||
               is_array($content);
    }

    /**
     * Serialize the given content into json.
     *
     * @param mixed $content
     *
     * @return string
     */
    public static function morphToJson($content)
    {
        if ($content instanceof Jsonable) {
            return $content->toJson();
        }

        if ($content instanceof Arrayable) {
            $content = $content->toArray();
        } elseif ($content instanceof \JsonSerializable) {
            $content = $content->jsonSerialize();
        } elseif ($content instanceof \stdClass) {
            $content = (array) $content;
        }

        return json_encode($content);
    }
}
