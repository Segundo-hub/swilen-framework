<?php

namespace Swilen\Http\Component\File;

use Psr\Http\Message\UploadedFileInterface;
use Swilen\Http\Exception\FileException;

class UploadedFile extends File implements UploadedFileInterface
{
    /**
     * The client-provided full path to the file.
     *
     * @var string
     */
    protected $file;

    /**
     * The client-provided file name.
     *
     * @var string
     */
    protected $name;

    /**
     * The client-provided media type of the file.
     *
     * @var string
     */
    protected $type;

    /**
     * A valid PHP UPLOAD_ERR_xxx code for the file upload.
     *
     * @var int
     */
    protected $error = \UPLOAD_ERR_OK;

    /**
     * Indicates if the uploaded file has already been moved.
     *
     * @var bool
     */
    protected $moved = false;

    /**
     * The stream instance for read content.
     *
     * @var \Psr\Http\Message\StreamInterface
     */
    protected $stream;

    /**
     * Create new UploadedFile instance.
     *
     * @param string $path  The full path or tmp_name $FILES property of the file
     * @param string $name  The filename or name $_FILES property of the file
     * @param string $type  The type of $_FILES property of the file
     * @param int    $error The error of $_FILES property of the file
     *
     * @return void
     */
    public function __construct(string $path, string $name, string $type = null, int $error = null)
    {
        $this->file  = $path;
        $this->name  = $this->normalizedFilename($name);
        $this->type  = $type ?: 'application/octet-stream';
        $this->error = $error ?: \UPLOAD_ERR_OK;

        parent::__construct($path, $this->error === \UPLOAD_ERR_OK);
    }

    /**
     * Retrieve the extension of the file.
     *
     * @return string
     */
    public function getClientOriginalExtension()
    {
        return pathinfo($this->name, \PATHINFO_EXTENSION);
    }

    /**
     * Retrieve the extension of the file.
     *
     * @return string
     */
    public function getClientOriginalName()
    {
        return $this->name;
    }

    /**
     * Check if the file is valid and can be downloaded.
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->error === \UPLOAD_ERR_OK && is_uploaded_file($this->getPathname());
    }

    /**
     * Move uploaded file to another directory.
     *
     * @param string $directory
     * @param string $name
     *
     * @return string
     *
     * @throws \Swilen\Http\Exception\FileException
     */
    public function move(string $directory, string $name = null)
    {
        if (!$this->isValid()) {
            return $this->handleFileExceptions();
        }

        $target = $this->getTargetFile($directory, $name);
        $error  = '';
        set_error_handler(function ($type, $msg) use (&$error) {
            $error = $msg;
        });

        try {
            $moved = move_uploaded_file($this->getPathname(), $target);
        } finally {
            restore_error_handler();
        }

        if ($moved === false) {
            $message = sprintf('Could not move the file "%s" to "%s" (%s).', $this->getPathname(), $target, strip_tags($error));

            throw new FileException($message);
        }

        $this->changePermissions($target);
        $this->moved = true;

        return $target;
    }

    /**
     * {@inheritdoc}
     *
     * @return string The directory as moved
     */
    public function moveTo($targetPath)
    {
        return $this->move($targetPath);
    }

    /**
     * {@inheritdoc}
     */
    public function getStream()
    {
        if ($this->moved) {
            throw new \RuntimeException('Uploaded file ['.$this->name.'] has already been moved');
        }

        if (!$this->stream) {
            if (!is_resource($file = fopen($this->file, 'rb'))) {
                throw new \RuntimeException('The file ['.$this->name.'] is not resource');
            }

            $this->stream = new Stream($file);
        }

        return $this->stream;
    }

    /**
     * {@inheritdoc}
     */
    public function getClientFilename()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getClientMediaType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Handle common file exceptions.
     *
     * @return void
     *
     * @throws \Swilen\Http\Exception\FileException
     */
    protected function handleFileExceptions()
    {
        static $errors = [
            \UPLOAD_ERR_INI_SIZE => 'The file "%s" exceeds your upload_max_filesize ini directive (limit is %d KiB).',
            \UPLOAD_ERR_FORM_SIZE => 'The file "%s" exceeds the upload limit defined in your form.',
            \UPLOAD_ERR_PARTIAL => 'The file "%s" was only partially uploaded.',
            \UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
            \UPLOAD_ERR_CANT_WRITE => 'The file "%s" could not be written on disk.',
            \UPLOAD_ERR_NO_TMP_DIR => 'File could not be uploaded: missing temporary directory.',
            \UPLOAD_ERR_EXTENSION => 'File upload was stopped by a PHP extension.',
        ];

        $message = $errors[$this->error] ?? 'The file "%s" was not uploaded due to an unknown error.';

        throw new FileException(sprintf($message, $this->getClientFilename()));
    }
}
