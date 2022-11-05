<?php

namespace Swilen\Http\Component\File;

use Swilen\Http\Exception\FileException;

final class UploadedFile extends File
{
    /**
     * @var string
     */
    protected $originalName;

    /**
     * @var string
     */
    protected $mimeType;

    /**
     * @var string
     */
    protected $error;

    /**
     * Create new UploadedFile instance.
     *
     * @param string $path     The full path or tmp_name $FILES property of the file
     * @param string $name     The filename or name $_FILES property of the file
     * @param string $mimeType
     * @param int    $error
     *
     * @return void
     */
    public function __construct(string $path, string $name, string $mimeType = null, int $error = null)
    {
        $this->originalName = $this->getName($name);
        $this->mimeType = $mimeType ?: 'application/octet-stream';
        $this->error = $error ?: \UPLOAD_ERR_OK;

        parent::__construct($path, $this->error === \UPLOAD_ERR_OK);
    }

    /**
     * @return string
     */
    public function getClientOriginalName()
    {
        return $this->originalName;
    }

    /**
     * @return string|string[]
     */
    public function getClientOriginalExtension()
    {
        return pathinfo($this->originalName, \PATHINFO_EXTENSION);
    }

    /**
     * Check if the file is valid and can be downloaded.
     *
     * @return bool
     */
    public function isValid()
    {
        $isOk = $this->error === \UPLOAD_ERR_OK;

        return $isOk && is_uploaded_file($this->getPathname());
    }

    /**
     * Move uploaded file to another directory.
     *
     * @param string $directory
     * @param string $name
     *
     * @return string
     */
    public function store(string $directory, string $name = null)
    {
        if ($this->isValid()) {
            $target = $this->getTargetFile($directory, $name);
            $error = '';

            set_error_handler(function ($type, $msg) use (&$error) {
                $error = $msg;
            });

            try {
                $moved = move_uploaded_file($this->getPathname(), $target);
            } finally {
                restore_error_handler();
            }

            if ($moved === false) {
                throw new FileException(sprintf('Could not move the file "%s" to "%s" (%s).', $this->getPathname(), $target, strip_tags($error)));
            }

            @$this->changePermissions($target);

            return $target;
        }

        $this->handleExceptions();
    }

    /**
     * Handle file exceptions.
     */
    protected function handleExceptions()
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

        throw new FileException(sprintf($message, $this->getClientOriginalName()));
    }
}
