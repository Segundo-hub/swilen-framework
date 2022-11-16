<?php

namespace Swilen\Http\Component\File;

use Swilen\Http\Common\MimeTypes;
use Swilen\Http\Exception\FileException;
use Swilen\Http\Exception\FileNotFoundException;
use Swilen\Shared\Support\Arrayable;

class File extends \SplFileInfo implements Arrayable
{
    /**
     * Create new File instance.
     *
     * @param string $path  The file path
     * @param bool   $check Check file exists
     *
     * @return void
     */
    public function __construct(string $path, bool $check = false)
    {
        if ($check && !is_file($path)) {
            throw new FileNotFoundException($path);
        }

        parent::__construct($path);
    }

    /**
     * Move file not another directory or rename directory.
     *
     * @param string $directory
     * @param string $name
     *
     * @return string
     */
    public function move(string $directory, string $name = null)
    {
        $target = $this->getTargetFile($directory, $name);
        $error  = '';

        set_error_handler(function ($type, $msg) use (&$error) {
            $error = $msg;
        });
        try {
            $renamed = rename($this->getPathname(), $target);
        } finally {
            restore_error_handler();
        }
        if ($renamed === false) {
            throw new FileException(sprintf('Could not move the file "%s" to "%s" (%s).', $this->getPathname(), $target, strip_tags($error)));
        }

        $this->changePermissions($target);

        return $target;
    }

    /**
     * Change the file permisions.
     *
     * @param string $target
     *
     * @return void
     */
    protected function changePermissions($target)
    {
        @chmod($target, 0666 & ~umask());
    }

    /**
     * Get target with directory and filename provided.
     *
     * @param string $directory
     * @param string $name
     *
     * @return self
     */
    public function getTargetFile(string $directory, string $name = null)
    {
        if (!is_dir($directory)) {
            if (@mkdir($directory, 0777, true) === false && !is_dir($directory)) {
                throw new FileException(sprintf('Unable to create the "%s" directory.', $directory));
            }
        } elseif (!is_writable($directory)) {
            throw new FileException(sprintf('Unable to write in the "%s" directory.', $directory));
        }

        $target = rtrim($directory, '/\\').\DIRECTORY_SEPARATOR.($name === null ? $this->getBasename() : $this->normalizedFilename($name));

        return new self($target, false);
    }

    /**
     * Get content from file provided.
     *
     * @return string
     */
    public function getContent()
    {
        $content = file_get_contents($this->getPathname());

        if ($content === false) {
            throw new FileException(sprintf('Could not get the content of the file "%s".', $this->getPathname()));
        }

        return $content;
    }

    /**
     * Returns locale independent base name of the given path.
     *
     * @param string $name
     *
     * @return string
     */
    protected function normalizedFilename(string $name)
    {
        $replaced = str_replace('\\', '/', $name);

        if (($pos = strrpos($replaced, '/')) === false) {
            return $replaced;
        }

        return substr($replaced, $pos + 1);
    }

    /**
     * Get the file MimeType from based in extension.
     *
     * @return string
     */
    public function getMimeType()
    {
        return MimeTypes::get($this->getExtension());
    }

    /**
     * Get the real file attributes.
     *
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'path' => $this->getPathname(),
            'name' => $this->getFilename(),
            'ext' => $this->getExtension(),
            'type' => $this->getMimeType(),
            'size' => $this->getSize(),
        ];
    }
}
