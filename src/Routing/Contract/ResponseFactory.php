<?php

namespace Swilen\Routing\Contract;

interface ResponseFactory
{
    /**
     * Create new response from values of params args.
     *
     * @param mixed $content
     * @param int   $status
     * @param array $headers
     *
     * @return \Swilen\Http\Response
     */
    public function make($content = '', int $status = 200, array $headers = []);

    /**
     * Create response with json content.
     *
     * @param mixed $content
     * @param int   $status
     * @param array $headers
     *
     * @return \Swilen\Http\Response\JsonResponse
     */
    public function send($content = null, int $status = 200, array $headers = []);

    /**
     * Alias for send() method.
     *
     * @param mixed $content
     * @param int   $status
     * @param array $headers
     *
     * @return \Swilen\Http\Response\JsonResponse
     */
    public function json($content = null, int $status = 200, array $headers = []);

    /**
     * Create response with file and sending to client.
     *
     * @param \SplFileInfo|string $file
     * @param array               $headers
     *
     * @return \Swilen\Http\Response\BinaryFileResponse
     */
    public function file($file, array $headers = []);

    /**
     * Create streamed response.
     *
     * @param \Closure $callback
     * @param int      $status
     * @param array    $headers
     *
     * @return \Swilen\Http\Response\StreamedResponse
     */
    public function stream(\Closure $callback, int $status = 200, array $headers = []);

    /**
     * Create downloadble file response.
     *
     * @param \SplFileInfo|resource|string $file
     * @param string|null                  $name
     * @param array                        $headers
     * @param bool                         $attachemnt
     *
     * @return \Swilen\Http\Response\BinaryFileResponse
     */
    public function download($file, string $name = null, array $headers = [], bool $attachemnt = true);
}
