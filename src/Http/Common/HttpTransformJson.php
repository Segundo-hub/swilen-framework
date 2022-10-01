<?php

namespace Swilen\Http\Common;

final class HttpTransformJson
{
    /**
     * The transform json encoding options
     *
     * @var int
     */
    protected $encodingOptions = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;

    /**
     * The content to transform as json
     *
     * @var mixed
     */
    protected $content;

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
     * Transform given content as json
     *
     * @param int $encodingOptions
     *
     * @return string
     */
    public function encode(int $encodingOptions = 0)
    {
        @json_decode('[]');

        $content = json_encode($this->content, $encodingOptions ?: $this->encodingOptions);

        if (!$this->isValidJsonEncoded(json_last_error())) {
            throw new \JsonException(json_last_error_msg(), json_last_error());
        }

        return $content;
    }

    /**
     * Transform given content as valid php array or object
     *
     * @param bool $assoc
     * @param int $encodingOptions
     *
     * @return array|object
     */
    public function decode(bool $assoc = false, int $decodingOptions = 0)
    {
        @json_decode('[]');

        $content = json_decode($this->content, $assoc, 512, $decodingOptions);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \JsonException('Could not decode request body: ' . json_last_error_msg(), json_last_error());
        }

        return $content;
    }

    /**
     * Determine if json is valid encoded
     *
     * @param int $error
     *
     * @return bool
     */
    public function isValidJsonEncoded($error)
    {
        if ($error === JSON_ERROR_NONE) {
            return true;
        }

        return $this->hasEncodingOption(JSON_PARTIAL_OUTPUT_ON_ERROR) &&
            in_array($error, [
                JSON_ERROR_RECURSION,
                JSON_ERROR_INF_OR_NAN,
                JSON_ERROR_UNSUPPORTED_TYPE,
            ]);
    }

    /**
     * Determine if a JSON encoding option is set.
     *
     * @param int $option
     *
     * @return bool
     */
    public function hasEncodingOption($option)
    {
        return (bool) ($this->encodingOptions & $option);
    }
}
