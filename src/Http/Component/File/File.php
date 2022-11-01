<?php

namespace Swilen\Http\Component\File;

use Swilen\Shared\Support\Arrayable;
use Swilen\Http\Common\MimeTypes;
use Swilen\Http\Exception\FileException;
use Swilen\Http\Exception\FileNotFoundException;

class File extends \SplFileInfo implements Arrayable
{
    /**
     * Create new File instance
     *
     * @param string $path
     * @param bool $check
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
     * Move file not another directory or rename directory
     *
     * @param string $directory
     * @param string $name
     *
     * @return string
     */
    public function move(string $directory, string $name = null)
    {
        $target = $this->getTargetFile($directory, $name);
        $error = '';

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

        @$this->changePermissions($target);

        return $target;
    }

    /**
     * Change file permisions
     *
     * @param string $target
     */
    protected function changePermissions($target)
    {
        @chmod($target, 0666 & ~umask());
    }

    /**
     * Get target with directory and filename provided
     *
     * @param string $directory
     * @param string $name
     *
     * @return self
     */
    public function getTargetFile(string $directory, string $name = null)
    {
        if (!is_dir($directory)) {
            if (false === @mkdir($directory, 0777, true) && !is_dir($directory)) {
                throw new FileException(sprintf('Unable to create the "%s" directory.', $directory));
            }
        } elseif (!is_writable($directory)) {
            throw new FileException(sprintf('Unable to write in the "%s" directory.', $directory));
        }

        $target = rtrim($directory, '/\\') . \DIRECTORY_SEPARATOR . (null === $name ? $this->getBasename() : $this->getName($name));

        return new self($target, false);
    }

    /**
     * Get content from file provided
     *
     * @return string
     */
    public function getContent()
    {
        $content = file_get_contents($this->getPathname());

        if (false === $content) {
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
    protected function getName(string $name)
    {
        $finalName = str_replace('\\', '/', $name);

        if (($pos = strrpos($finalName, '/')) === false) {
            return $finalName;
        }

        return substr($finalName, $pos + 1);
    }

    /**
     * Get mime Type from extension
     *
     * @return string
     */
    public function getMimeType()
    {
        return MimeTypes::get($this->getExtension());
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'path' => $this->getPathname(),
            'name' => $this->getFilename(),
            'ext' => $this->getExtension(),
            'size' => $this->getSize()
        ];
    }
}
