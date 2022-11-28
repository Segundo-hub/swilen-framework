<?php

namespace Swilen\Http\Component\File;

use Psr\Http\Message\StreamInterface;

class Stream implements StreamInterface
{
    /**
     * Bit mask to determine if the stream is a pipe.
     *
     * This is octal as per header stat.h
     */
    public const FSTAT_MODE_S_IFIFO = 0010000;

    /**
     * The underlying stream resource.
     *
     * @var resource|null
     */
    protected $stream;

    /**
     * The resource metada.
     *
     * @var array
     */
    protected $meta;

    /**
     * Is resource stream readabale?
     *
     * @var bool
     */
    protected $readable;

    /**
     * Is given resource stream a writable?
     *
     * @var bool
     */
    protected $writable;

    /**
     * Is given resource stream a seekable?
     *
     * @var bool
     */
    protected $seekable;

    /**
     * The given resource stream size.
     *
     * @var int
     */
    protected $size;

    /**
     * Is given resource stream a pipe?
     *
     * @var bool
     */
    protected $isPipe;

    /**
     * Is resource stream finished?
     *
     * @var bool
     */
    protected $finished;

    /**
     * Create new Streamed reasource implmented by PSR standar.
     *
     * @param resource $stream a PHP resource handle
     *
     * @throws InvalidArgumentException
     */
    public function __construct($stream)
    {
        $this->attach($stream);
    }

    /**
     * {@inheritdoc}
     *
     * @return array|mixed
     */
    public function getMetadata($key = null)
    {
        if (!$this->stream) {
            return null;
        }

        $this->meta = stream_get_meta_data($this->stream);

        if (!$key) {
            return $this->meta;
        }

        return $this->meta[$key] ?? null;
    }

    /**
     * Attach new resource to this object.
     *
     * @param resource $stream
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    protected function attach($stream)
    {
        if (!is_resource($stream)) {
            throw new \InvalidArgumentException(__METHOD__.' argument must be a valid PHP resource');
        }

        if ($this->stream) {
            $this->detach();
        }

        $this->stream = $stream;
    }

    /**
     * {@inheritdoc}
     */
    public function detach()
    {
        $old = $this->stream;

        $this->stream   = null;
        $this->meta     = null;
        $this->readable = null;
        $this->writable = null;
        $this->seekable = null;
        $this->size     = null;
        $this->isPipe   = null;
        $this->finished = false;

        return $old;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        if ($this->stream) {
            if ($this->isPipe()) {
                pclose($this->stream);
            } else {
                fclose($this->stream);
            }
        }

        $this->detach();
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        if ($this->stream && !$this->size) {
            $stats = fstat($this->stream);

            if ($stats) {
                $this->size = !$this->isPipe() ? $stats['size'] : null;
            }
        }

        return $this->size;
    }

    /**
     * {@inheritdoc}
     */
    public function tell(): int
    {
        $position = false;

        if ($this->stream) {
            $position = ftell($this->stream);
        }

        if ($position === false || $this->isPipe()) {
            throw new \RuntimeException('Could not get the position of the pointer in stream.');
        }

        return $position;
    }

    /**
     * {@inheritdoc}
     */
    public function eof()
    {
        return !$this->stream || feof($this->stream);
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable(): bool
    {
        if ($this->readable !== null) {
            return $this->readable;
        }

        $this->readable = false;

        if ($this->stream) {
            $mode = $this->getMetadata('mode');

            if (is_string($mode) && (strstr($mode, 'r') !== false || strstr($mode, '+') !== false)) {
                $this->readable = true;
            }
        }

        return $this->readable;
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable()
    {
        if ($this->writable === null) {
            $this->writable = false;

            if ($this->stream) {
                $mode = $this->getMetadata('mode');

                if (is_string($mode) && (strstr($mode, 'w') !== false || strstr($mode, '+') !== false)) {
                    $this->writable = true;
                }
            }
        }

        return $this->writable;
    }

    /**
     * {@inheritdoc}
     */
    public function isSeekable()
    {
        if ($this->seekable === null) {
            $this->seekable = false;

            if ($this->stream) {
                $this->seekable = !$this->isPipe() && $this->getMetadata('seekable');
            }
        }

        return $this->seekable;
    }

    /**
     * {@inheritdoc}
     */
    public function seek($offset, $whence = SEEK_SET): void
    {
        if (!$this->isSeekable() || $this->stream && fseek($this->stream, $offset, $whence) === -1) {
            throw new \RuntimeException('Could not seek in stream.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        if (!$this->isSeekable() || $this->stream && rewind($this->stream) === false) {
            throw new \RuntimeException('Could not rewind stream.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function read($length)
    {
        $data = false;

        if ($this->isReadable() && $this->stream && $length >= 0) {
            $data = fread($this->stream, $length);
        }

        if (is_string($data)) {
            if ($this->eof()) {
                $this->finished = true;
            }

            return $data;
        }

        throw new \RuntimeException('Could not read from stream.');
    }

    /**
     * {@inheritdoc}
     */
    public function write($string)
    {
        $written = false;

        if ($this->isWritable() && $this->stream) {
            $written = fwrite($this->stream, $string);
        }

        if ($written !== false) {
            $this->size = null;

            return $written;
        }

        throw new \RuntimeException('Could not write to stream.');
    }

    /**
     * {@inheritdoc}
     */
    public function getContents(): string
    {
        $contents = false;

        if ($this->stream) {
            $contents = stream_get_contents($this->stream);
        }

        if (is_string($contents)) {
            if ($this->eof()) {
                $this->finished = true;
            }

            return $contents;
        }

        throw new \RuntimeException('Could not get contents of stream.');
    }

    /**
     * Determine if current stream is a pipe resource.
     *
     * @return bool
     */
    public function isPipe()
    {
        if ($this->isPipe === null) {
            $this->isPipe = false;

            if ($this->stream) {
                $stats = fstat($this->stream);

                if (is_array($stats)) {
                    $this->isPipe = ($stats['mode'] & self::FSTAT_MODE_S_IFIFO) !== 0;
                }
            }
        }

        return $this->isPipe;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        if (!$this->stream) {
            return '';
        }

        if ($this->isSeekable()) {
            $this->rewind();
        }

        return $this->getContents();
    }
}
