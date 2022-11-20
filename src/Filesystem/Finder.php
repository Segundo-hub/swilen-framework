<?php

namespace Swilen\Filesystem;

use Swilen\Filesystem\Exception\DirectoryNotFoundException;

class Finder
{
    /**
     * Const fro filetype() function return is dir.
     *
     * @var string
     */
    public const FILETYPE_DIR = 'dir';

    /**
     * Const fro filetype() function return is file.
     *
     * @var string
     */
    public const FILETYPE_FILE = 'file';

    /**
     * Const fro filetype() function return is symbolic link.
     *
     * @var string
     */
    public const FILETYPE_LINK = 'link';

    /**
     * The current path for find.
     *
     * @var string
     */
    protected $path;

    /**
     * The current depth for find.
     *
     * @var int
     */
    protected $depth = 0;

    /**
     * Indicates if include dot files.
     *
     * @var bool
     */
    protected $dot = true;

    /**
     * The extension of file included.
     *
     * @var string|string[]
     */
    protected $ext;

    /**
     * The collection of regular file paths.
     *
     * @var string[]
     */
    protected $files = [];

    /**
     * The collection of directory paths.
     *
     * @var string[]
     */
    protected $directories = [];

    /**
     * The collection of symbolic link paths.
     *
     * @var string[]
     */
    protected $links = [];

    /**
     * Create new filesystem finder instance.
     *
     * @param string|null $path
     */
    public function __construct(string $path = null)
    {
        if ($path !== null) {
            $this->in($path);
        }
    }

    /**
     * Set finder target path.
     *
     * @param string $path
     *
     * @return $this
     *
     * @throws \Swilen\Filesystem\Exception\DirectoryNotFoundException
     */
    public function in(string $path)
    {
        if (is_readable($path) && is_dir($path)) {
            $this->path = $path;

            return $this;
        }

        throw new DirectoryNotFoundException(sprintf('The "%s" directory does not exist.', $path));
    }

    /**
     * Set the depth for find.
     *
     * @param int|null $depth
     *
     * @return $this
     */
    public function depth(int $depth = null)
    {
        $this->depth = $depth;

        return $this;
    }

    /**
     * Set if dot files include in the find.
     *
     * @param int|null $include
     *
     * @return $this
     */
    public function dotFiles(bool $include = true)
    {
        $this->dot = $include;

        return $this;
    }

    /**
     * Set only php files finded.
     *
     * @return $this
     */
    public function phpOnly()
    {
        $this->ext = ['php'];

        return $this;
    }

    /**
     * Find files with Finder configuration.
     *
     * @return $this
     */
    public function find()
    {
        $this->finderToForest($this->path);

        return $this;
    }

    /**
     * Get an array of all files in a directory.
     *
     * @param bool $spl
     *
     * @return \SplFileInfo[]|string[]
     */
    public function files(bool $spl = false)
    {
        if ($spl) {
            return array_map(function ($file) {
                return new \SplFileInfo($file);
            }, $this->files);
        }

        return $this->files;
    }

    /**
     * Get an array of all files in a directory.
     *
     * @param bool $spl
     *
     * @return \SplFileInfo[]|string[]
     */
    public function directories()
    {
        return $this->directories;
    }

    /**
     * Find files recursive based in depth.
     *
     * @param string $path
     * @param int    $depth
     *
     * @return void
     */
    protected function finderToForest(string $path, int $depth = 0)
    {
        $directory = realpath($path);
        $resource  = opendir($directory);
        ++$depth;

        while (($f = readdir($resource)) !== false && ($depth - 1) <= $this->depth) {
            if (in_array($f, ['.', '..', 'cgi-bin'], true)) {
                continue;
            }

            $finalPath = $directory.DIRECTORY_SEPARATOR.$f;

            if (filetype($finalPath) === Finder::FILETYPE_FILE) {
                if ($this->isDotFileSkippable($f)) {
                    continue;
                }

                if ($this->isInvalidExtension($f)) {
                    continue;
                }

                $this->files[] = $finalPath;
            }

            if (filetype($finalPath) === Finder::FILETYPE_LINK) {
                $this->links[] = $finalPath;
            }

            if (filetype($finalPath) === Finder::FILETYPE_DIR) {
                $this->directories[] = $finalPath;
                $this->finderToForest($finalPath, $depth);
            }
        }

        closedir($resource);
    }

    protected function isDotFileSkippable(string $file)
    {
        return $this->dot === false && $file['0'] === '.';
    }

    protected function isInvalidExtension(string $file)
    {
        if ($this->ext === null || $this->ext === []) {
            return false;
        }

        return !in_array(pathinfo($file, PATHINFO_EXTENSION), $this->ext, true);
    }
}
