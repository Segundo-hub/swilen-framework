<?php

namespace Swilen\Http;

use Swilen\Http\Contract\ResponseFactoryContract;

final class ResponseFactory implements ResponseFactoryContract
{
    /**
     * {@inheritdoc}
     */
    public function make($content = '', int $status = 200, array $headers = [])
    {
        return new Response($content, $status, $headers);
    }

    /**
     * {@inheritdoc}
     */
    public function send($content = '', int $status = 200, array $headers = [])
    {
        return $this->make($content, $status, $headers);
    }

    /**
     * {@inheritdoc}
     */
    public function stream($resource, int $status = 200, array $headers = [])
    {
        // TODO
    }

    /**
     * {@inheritdoc}
     */
    public function file($file, array $headers = [])
    {
        return new BinaryFileResponse($file, 200, $headers);
    }

    /**
     * {@inheritdoc}
     */
    public function download($file, string $name = null, array $headers = [], bool $attachemnt = true)
    {
        $response = new BinaryFileResponse($file, 200, $headers, $attachemnt);

        if (!is_null($name)) {
            return $response->updateFilename($name);
        }

        return $response;
    }
}
