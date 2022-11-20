<?php

namespace Swilen\Filesystem;

use Swilen\Filesystem\Exception\FileNotFoundException;

class Filesystem
{
    /**
     * Determine if a file or directory exists.
     *
     * @param string $path
     *
     * @return bool
     */
    public function exists(string $path)
    {
        return file_exists($path);
    }

    /**
     * Determine if the given path is a file.
     *
     * @param string $file
     *
     * @return bool
     */
    public function isFile(string $file)
    {
        return is_file($file);
    }

    /**
     * Extract the file name from a file path.
     *
     * @param string $path
     *
     * @return string
     */
    public function name(string $path)
    {
        return pathinfo($path, PATHINFO_FILENAME);
    }

    /**
     * Extract the trailing name component from a file path.
     *
     * @param string $path
     *
     * @return string
     */
    public function basename(string $path)
    {
        return pathinfo($path, PATHINFO_BASENAME);
    }

    /**
     * Extract the parent directory from a file path.
     *
     * @param string $path
     *
     * @return string
     */
    public function dirname(string $path)
    {
        return pathinfo($path, PATHINFO_DIRNAME);
    }

    /**
     * Extract the file extension from a file path.
     *
     * @param string $path
     *
     * @return string
     */
    public function extension(string $path)
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * Get the file type of a given file.
     *
     * @param string $path
     *
     * @return string
     */
    public function type(string $path)
    {
        return filetype($path);
    }

    /**
     * Get the mime-type of a given file.
     *
     * @param string $path
     *
     * @return string|false
     */
    public function mimeType(string $path)
    {
        return finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path);
    }

    /**
     * Get the file size of a given file.
     *
     * @param string $path
     *
     * @return int
     */
    public function size(string $path)
    {
        return filesize($path);
    }

    /**
     * Get the file's last modification time.
     *
     * @param string $path
     *
     * @return int
     */
    public function lastModified(string $path)
    {
        return filemtime($path);
    }

    /**
     * Determine if the given path is a directory.
     *
     * @param string $directory
     *
     * @return bool
     */
    public function isDirectory(string $directory)
    {
        return is_dir($directory);
    }

    /**
     * Move a file to a new location.
     *
     * @param string $from
     * @param string $target
     *
     * @return bool
     */
    public function move(string $from, string $target)
    {
        return rename($from, $target);
    }

    /**
     * Copy a file to a new location.
     *
     * @param string $from
     * @param string $target
     *
     * @return bool
     */
    public function copy(string $from, string $target)
    {
        return copy($from, $target);
    }

    /**
     * Determine if the given path is readable.
     *
     * @param string $path
     *
     * @return bool
     */
    public function isReadable(string $path)
    {
        return is_readable($path);
    }

    /**
     * Determine if the given path is writable.
     *
     * @param string $path
     *
     * @return bool
     */
    public function isWritable(string $path)
    {
        return is_writable($path);
    }

    /**
     * Find path names matching a given pattern.
     *
     * @param string $pattern
     * @param int    $flags
     *
     * @return array
     */
    public function glob(string $pattern, int $flags = 0)
    {
        return glob($pattern, $flags);
    }

    /**
     * Get files from given path.
     *
     * @param string $path
     * @param bool   $hidden
     *
     * @return array
     */
    public function files(string $path, bool $hidden = false)
    {
        return (new Finder($path))->dotFiles($hidden)->depth(0)->find()->files();
    }

    /**
     * Get the returned value of a file.
     *
     * @param string $path
     * @param array  $data
     *
     * @return mixed
     *
     * @throws \Swilen\Filesystem\Exception\FileNotFoundException
     */
    public function require(string $path, array $data = [])
    {
        return $this->requireFile($path, $data);
    }

    /**
     * Require the given file once.
     *
     * @param string $path
     * @param array  $data
     *
     * @return mixed
     *
     * @throws \Swilen\Filesystem\Exception\FileNotFoundException
     */
    public function requireOnce(string $path, array $data = [])
    {
        return $this->requireFile($path, $data, true);
    }

    /**
     * Require php file and pass variables to scope.
     *
     * @param string $__path
     * @param array  $__data
     * @param bool   $__once
     *
     * @return mixed
     *
     * @throws \Swilen\Filesystem\Exception\FileNotFoundException
     */
    public function requireFile(string $__path, array $__data = [], bool $__once = true)
    {
        if ($this->isFile($__path)) {
            return (static function () use ($__path, $__data, $__once) {
                extract($__data, EXTR_SKIP);

                return $__once ? require_once $__path : require $__path;
            })();
        }

        throw new FileNotFoundException("File does not exist at path {$__path}.");
    }
}
