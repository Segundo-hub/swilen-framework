<?php

namespace Swilen\Routing\Contract;

use Swilen\Http\Common\Http;

interface ResponseFactory
{
    /**
     * Sends the HTTP response with given content.
     *
     * @param string $content
     * @param int    $status
     * @param array  $headers
     *
     * @return \Swilen\Http\Response\Response
     */
    public function send(?string $content = null, int $status = 200, array $headers = []);

    /**
     * Send empty response with given status code.
     *
     * @param int   $status
     * @param array $headers
     *
     * @return \Swilen\Http\Response\Response
     */
    public function status(int $status = Http::OK, array $headers = []);

    /**
     * Send a JSON response (with the correct content-type).
     *
     * @param mixed $content
     * @param int   $status
     * @param array $headers
     *
     * @return \Swilen\Http\Response\JsonResponse
     */
    public function json($content = null, int $status = 200, array $headers = []);

    /**
     * Transfers the file at the given path or File instance.
     *
     * @param \SplFileInfo|string $file
     * @param array               $headers
     *
     * @return \Swilen\Http\Response\BinaryFileResponse
     */
    public function file($file, array $headers = []);

    /**
     * Transfer the file in the path as an "attachment" for download.
     *
     * @param \SplFileInfo|string $file
     * @param string|null         $name
     * @param array               $headers
     *
     * @return \Swilen\Http\Response\BinaryFileResponse
     */
    public function download($file, ?string $name = null, array $headers = [], string $disposition = 'attachment');

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
}
