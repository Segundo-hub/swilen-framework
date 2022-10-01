<?php

namespace Swilen\Http\Contract;

interface ResponseFactoryContract
{
    /**
     * Create new response from values of params args
     *
     * @param resource|string|array|object|null $content
     * @param int $status
     * @param array $headers
     *
     * @return \Swilen\Http\Response
     */
    public function make($content = '', int $status = 200, array $headers = []);

    /**
     * Alias for make function
     *
     * @param resource|string|array|object|null $content
     * @param int $status
     * @param array $headers
     *
     * @return \Swilen\Http\Response
     */
    public function send($content = '', int $status = 200, array $headers = []);

    /**
     * Create response with file and sending to client
     *
     * @param \SplFileInfo|string $file
     * @param array $headers
     *
     * @return \Swilen\Http\BinaryFileResponse
     */
    public function file($file, array $headers = []);

    /**
     * Create streamed response
     *
     * @param \SplFileInfo|resource|string $resource
     * @param int $status
     * @param array $headers
     *
     * @return \Swilen\Http\Response
     */
    public function stream($resource, int $status = 200, array $headers = []);

    /**
     * Create downloadble file response
     *
     * @param \SplFileInfo|resource|string $resource
     * @param string $name
     * @param array $headers
     * @param bool $attachemnt
     *
     * @return \Swilen\Http\BinaryFileResponse
     */
    public function download($file, string $name = null, array $headers = [], bool $attachemnt = true);
}
