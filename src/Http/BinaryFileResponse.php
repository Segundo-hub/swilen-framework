<?php

namespace Swilen\Http;

use Swilen\Http\Component\File\File;

final class BinaryFileResponse extends Response
{
    /**
     * The parsed content as string or reource for put into client
     *
     * @var \Swilen\Http\Component\File\File
     */
    protected $file;

    /**
     * @var int
     */
    protected $offset = 0;

    /**
     * @var int
     */
    protected $maxlen = -1;

    /**
     * @var int
     */
    protected $chunkSize = 8 * 1024;

    /**
     * Create new response instance
     *
     * @param resource|string $file
     * @param int $status
     * @param array $headers
     * @param bool $attachment
     *
     * @return void
     */
    public function __construct($file, int $status = 200, array $headers = [], bool $attachment = false)
    {
        parent::__construct(null, $status, $headers);

        $this->setFile($file, $attachment);
    }

    /**
     * Resolve file instance
     *
     * @param resource|string|\Swilen\Http\Component\File\File $file
     * @param bool $attachment
     */
    protected function setFile($file, bool $attachment)
    {
        if ($file instanceof File) {
            $this->$file = $file;
        } else {
            $this->file = new File($file, true);
        }

        if ($attachment) {
            $this->setContentDisposition('');
        }
    }

    /**
     * Make content disposition to file for download
     *
     * @param string $filename
     */
    public function setContentDisposition(string $filename)
    {
        if ($filename === '') {
            $filename = $this->file->getFilename();
        }

        $this->headers([
            'Content-Description' => 'File Transfer',
            'Cache-Control' => 'no-cache, must-revalidate',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'public'
        ]);
    }

    /**
     * Update filename for to send
     *
     * @param string $filename
     *
     * @return $this
     */
    public function updateFilename(string $filename)
    {
        $this->headers->replace('Content-Disposition', 'attachment; filename="' . $filename . '"',);

        return $this;
    }

    /**
     * Prepare response for send this current file
     *
     * @param \Swilen\Http\Request $request
     *
     * @return $this
     */
    public function prepare(Request $request)
    {
        $this->headers->set('Content-Type', $this->file->getMimeType() ?: 'application/octet-stream');

        if ('HTTP/1.0' !== $request->server->get('SERVER_PROTOCOL')) {
            $this->setProtocolVersion('1.1');
        }

        $this->offset = 0;
        $this->maxlen = -1;

        if (false === $fileSize = $this->file->getSize()) {
            return $this;
        }

        $this->headers->set('Content-Length', $fileSize);

        if (!$this->headers->has('Accept-Ranges')) {
            $this->headers->set('Accept-Ranges', $request->isMethodSafe() ? 'bytes' : 'none');
        }

        if ($request->headers->has('Range') && $request->getMethod() === 'GET') {
            // Process the range headers.
            if (!$request->headers->has('If-Range')) {
                $range = $request->headers->get('Range');

                if (substr($range, 0, 6) === 'bytes=') {
                    [$start, $end] = explode('-', substr($range, 6), 2) + [0];

                    $end = ('' === $end) ? $fileSize - 1 : (int) $end;

                    if ('' === $start) {
                        $start = $fileSize - $end;
                        $end = $fileSize - 1;
                    } else {
                        $start = (int) $start;
                    }

                    if ($start <= $end) {
                        $end = min($end, $fileSize - 1);
                        if ($start < 0 || $start > $end) {
                            $this->setStatusCode(416);
                            $this->headers->set('Content-Range', sprintf('bytes */%s', $fileSize));
                        } elseif ($end - $start < $fileSize - 1) {
                            $this->maxlen = $end < $fileSize ? $end - $start + 1 : -1;
                            $this->offset = $start;

                            $this->setStatusCode(206);
                            $this->headers->set('Content-Range', sprintf('bytes %s-%s/%s', $start, $end, $fileSize));
                            $this->headers->set('Content-Length', $end - $start + 1);
                        }
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Send data as stream to client
     *
     * @return $this
     */
    protected function sendResponseContent()
    {
        if (!$this->isSuccessful()) {
            return parent::sendResponseContent();
        }

        if (0 === $this->maxlen) return $this;

        $InputStream  = fopen($this->file->getPathname(), 'r');
        $OutputStream = fopen('php://output', 'w');

        ignore_user_abort(true);

        if (0 !== $this->offset) {
            fseek($InputStream, $this->offset);
        }

        $length = $this->maxlen;
        while ($length && !feof($InputStream)) {
            $read = ($length > $this->chunkSize) ? $this->chunkSize : $length;
            $length -= $read;

            stream_copy_to_stream($InputStream, $OutputStream, $read);

            if (connection_aborted()) {
                break;
            }
        }

        fclose($OutputStream);
        fclose($InputStream);

        return $this;
    }
}
