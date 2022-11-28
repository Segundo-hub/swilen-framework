<?php

namespace Swilen\Routing;

use Swilen\Http\Common\Http;
use Swilen\Http\Response;
use Swilen\Http\Response\BinaryFileResponse;
use Swilen\Http\Response\JsonResponse;
use Swilen\Http\Response\StreamedResponse;
use Swilen\Routing\Contract\ResponseFactory as ContractResponseFactory;

final class ResponseFactory implements ContractResponseFactory
{
    /**
     * {@inheritdoc}
     */
    public function send(?string $content = null, int $status = 200, array $headers = [])
    {
        return new Response($content, $status, $headers);
    }

    /**
     * {@inheritdoc}
     */
    public function status(int $status = Http::OK, array $headers = [])
    {
        return $this->send(null, $status, $headers);
    }

    /**
     * {@inheritdoc}
     */
    public function json($content = null, int $status = 200, array $headers = [])
    {
        return new JsonResponse($content, $status, $headers);
    }

    /**
     * {@inheritdoc}
     */
    public function file($file, array $headers = [])
    {
        return new BinaryFileResponse($file, 200, $headers, null);
    }

    /**
     * {@inheritdoc}
     */
    public function download($file, ?string $name = null, array $headers = [], string $disposition = 'attachment')
    {
        $instance = new BinaryFileResponse($file, 200, $headers, $disposition);

        if ($name !== null && !empty($name)) {
            $instance->updateFilename($name);
        }

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function stream(\Closure $callback, int $status = 200, array $headers = [])
    {
        return new StreamedResponse($callback, $status, $headers);
    }
}
