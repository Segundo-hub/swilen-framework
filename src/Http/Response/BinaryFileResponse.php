<?php

namespace Swilen\Http\Response;

use Swilen\Http\Common\Http;
use Swilen\Http\Component\File\File;
use Swilen\Http\Component\ResponseHeaderHunt;
use Swilen\Http\Exception\FileException;
use Swilen\Http\Request;
use Swilen\Http\Response;

class BinaryFileResponse extends Response
{
    /**
     * The file for send to client.
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
     * @var string
     */
    protected $disposition;

    /**
     * Create new binary file response.
     *
     * @param \SplFileInfo|string $file       The file: filepath or File instance
     * @param bool                $attachment The disposition for send file
     *
     * @return void
     */
    public function __construct($file, int $status = 200, array $headers = [], ?string $disposition = null)
    {
        parent::__construct(null, $status, $headers);

        $this->setBinaryFile($file);

        if ($disposition) {
            $this->addContentDisposition($disposition);
        }
    }

    /**
     * Resolve file instance.
     *
     * @param \SplFileInfo|string $file
     *
     * @return $this
     */
    protected function setBinaryFile($file)
    {
        if (!$file instanceof File) {
            if ($file instanceof \SplFileInfo) {
                $file = new File($file->getPathname(), true);
            } else {
                $file = new File((string) $file, true);
            }
        }

        if (!$file->isReadable()) {
            throw new FileException('File must be readable.');
        }

        $this->file = $file;

        return $this;
    }

    /**
     * Make content disposition to file for download.
     */
    protected function addContentDisposition(string $disposition)
    {
        $filename = $this->file->getFilename();

        $this->withHeaders([
            'Content-Description' => 'File Transfer',
            'Cache-Control' => 'no-cache, must-revalidate',
        ]);

        $this->headers->makeDisposition($disposition, $filename);

        $this->disposition = $disposition;
    }

    /**
     * Update filename for to send.
     *
     * @param string $filename
     *
     * @return $this
     */
    public function updateFilename(string $filename)
    {
        $this->headers->makeDisposition(
            $this->disposition ?? ResponseHeaderHunt::DISPOSITION_ATTACHMENT, $filename
        );

        return $this;
    }

    /**
     * Prepare response for send this current file.
     *
     * @param \Swilen\Http\Request $request
     *
     * @return $this
     */
    public function prepare(Request $request)
    {
        if ($this->isInformational() || $this->isEmpty()) {
            parent::prepare($request);

            $this->maxlen = 0;

            return $this;
        }

        if (!$this->headers->has('Content-Type')) {
            $this->headers->set('Content-Type', $this->file->getMimeType() ?: 'application/octet-stream');
        }

        parent::prepare($request);

        $this->offset = 0;
        $this->maxlen = -1;

        if (($fileSize = $this->file->getSize()) === false) {
            return $this;
        }

        $this->headers->set('Content-Length', $fileSize);

        if (!$this->headers->has('Accept-Ranges')) {
            $this->headers->set('Accept-Ranges', $request->isMethodSafe() ? 'bytes' : 'none');
        }

        if ($request->headers->has('Range') && $request->getMethod() === Http::METHOD_GET) {
            // Process the range headers.
            if (!$request->headers->has('If-Range')) {
                $range = $request->headers->get('Range');

                if (substr($range, 0, 6) === 'bytes=') {
                    [$start, $end] = explode('-', substr($range, 6), 2) + [0];

                    $end = ($end === '') ? $fileSize - 1 : (int) $end;

                    if ($start === '') {
                        $start = $fileSize - $end;
                        $end   = $fileSize - 1;
                    } else {
                        $start = (int) $start;
                    }

                    if ($start <= $end) {
                        $end = min($end, $fileSize - 1);
                        if ($start < 0 || $start > $end) {
                            $this->setStatusCode(Http::REQUESTED_RANGE_NOT_SATISFIABLE);
                            $this->headers->set('Content-Range', sprintf('bytes */%s', $fileSize));
                        } elseif ($end - $start < $fileSize - 1) {
                            $this->maxlen = $end < $fileSize ? $end - $start + 1 : -1;
                            $this->offset = $start;

                            $this->setStatusCode(Http::PARTIAL_CONTENT);
                            $this->headers->set('Content-Range', sprintf('bytes %s-%s/%s', $start, $end, $fileSize));
                            $this->headers->set('Content-Length', $end - $start + 1);
                        }
                    }
                }
            }
        }

        if ($request->getMethod() === Http::METHOD_HEAD) {
            $this->maxlen = 0;
        }

        return $this;
    }

    /**
     * Sends file for the current web response.
     *
     * @return $this
     */
    protected function sendBody()
    {
        if (!$this->isSuccessful()) {
            return parent::sendBody();
        }

        if ($this->maxlen === 0) {
            return $this;
        }

        $file = fopen($this->file->getPathname(), 'r');
        $out  = fopen('php://output', 'w');

        ignore_user_abort(true);

        if ($this->offset !== 0) {
            fseek($file, $this->offset);
        }

        $length = $this->maxlen;
        while ($length && !feof($file)) {
            $read = ($length > $this->chunkSize) ? $this->chunkSize : $length;
            $length -= $read;

            stream_copy_to_stream($file, $out, $read);

            if (connection_aborted()) {
                break;
            }
        }

        fclose($file);
        fclose($out);

        return $this;
    }

    /**
     * Return the file for the response.
     *
     * @return \Swilen\Http\Component\File\File
     */
    public function getFile()
    {
        return $this->file;
    }
}
