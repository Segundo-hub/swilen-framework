<?php

namespace Swilen\Routing;

use Swilen\Http\Component\ResponseHeaderHunt;
use Swilen\Http\Response;
use Swilen\Http\Response\BinaryFileResponse;
use Swilen\Http\Response\JsonResponse;
use Swilen\Http\Response\StreamedResponse;
use Swilen\Routing\Contract\ResponseFactory as ContractResponseFactory;

final class ResponseFactory implements ContractResponseFactory
{
    /**
     * {@inheritdoc}
     *
     * Create response with raw data encoded
     *
     * @return \Swilen\Http\Response
     */
    public function make(?string $content = null, int $status = 200, array $headers = [])
    {
        return new Response($content, $status, $headers);
    }

    /**
     * {@inheritdoc}
     *
     * Create response serialized in json
     *
     * @return \Swilen\Http\Response\JsonResponse
     */
    public function send($content = null, int $status = 200, array $headers = [])
    {
        return new JsonResponse($content, $status, $headers);
    }

    /**
     * {@inheritdoc}
     *
     * Create response serialized in json.
     *
     * @return \Swilen\Http\Response\JsonResponse
     */
    public function json($content = null, int $status = 200, array $headers = [])
    {
        return $this->send($content, $status, $headers);
    }

    /**
     * {@inheritdoc}
     *
     * Create response with binary file
     *
     * @return \Swilen\Http\Response\BinaryFileResponse
     */
    public function file($file, array $headers = [])
    {
        return new BinaryFileResponse($file, 200, $headers, null);
    }

    /**
     * {@inheritdoc}
     *
     * Create response with downloadable binary file
     *
     * @return \Swilen\Http\Response\BinaryFileResponse
     */
    public function download($file, $name = null, array $headers = [], string $disposition = 'attachment')
    {
        $factory = new BinaryFileResponse($file, 200, $headers, $disposition);

        if ($name !== null) {
            $factory->updateFilename($name);
        }

        return $factory;
    }

    /**
     * {@inheritdoc}
     *
     * Create streamed response
     *
     * @return \Swilen\Http\Response\StreamedResponse
     */
    public function stream(\Closure $callback, int $status = 200, array $headers = [])
    {
        return new StreamedResponse($callback, $status, $headers);
    }
}
