<?php

namespace Swilen\Http\Component;

use Swilen\Http\Component\File\UploadedFile;

final class FileHunt extends ParameterHunt
{
    /**
     * The normalized file information keys.
     *
     * @var string[]
     */
    protected const FILE_KEYS = ['error', 'name', 'size', 'tmp_name', 'type'];

    /**
     * Create new files hunt instance.
     *
     * @param array|\Swilen\Http\Component\File\UploadedFile[] $files
     *
     * @return void
     */
    public function __construct(array $files = [])
    {
        $this->replace($files);
    }

    /**
     * Replace files stored with given files.
     *
     * @param array|\Swilen\Http\Component\File\UploadedFile[] $files
     *
     * @return void
     */
    public function replace(array $files = [])
    {
        $this->params = [];

        $this->add($files);
    }

    /**
     * Add given files to store.
     *
     * @param array|\Swilen\Http\Component\File\UploadedFile[] $files
     *
     * @return void
     */
    public function add(array $files = [])
    {
        foreach ($files as $key => $file) {
            $this->set($key, $file);
        }
    }

    /**
     * Set a file with key name to collection.
     *
     * @param string                                   $key
     * @param \Swilen\Http\Component\File\UploadedFile $file
     *
     * @return void
     */
    public function set(string $key, $value)
    {
        if (!\is_array($value) && !$value instanceof UploadedFile) {
            throw new \InvalidArgumentException(sprintf('Need this file "%s" as instance of "%s"', $value, UploadedFile::class));
        }

        parent::set($key, $this->transformToUploadedFile($value));
    }

    /**
     * Converts uploaded files to UploadedFile instances.
     *
     * @param \Swilen\Http\Component\UploadedFile|array|object $file
     *
     * @return \Swilen\Http\Component\UploadedFile[]|\Swilen\Http\Component\UploadedFile|null
     */
    protected function transformToUploadedFile($file)
    {
        if ($file instanceof UploadedFile) {
            return $file;
        }

        $file = $this->toNormalizedFiles($file);
        $keys = array_keys($file);
        sort($keys);

        if (self::FILE_KEYS == $keys) {
            if (\UPLOAD_ERR_NO_FILE == $file['error']) {
                $file = null;
            } else {
                $file = new UploadedFile($file['tmp_name'], $file['name'], $file['type'], $file['error'], false);
            }
        } else {
            $file = array_map(function ($v) {
                return ($v instanceof UploadedFile || \is_array($v))
                    ? $this->transformToUploadedFile($v)
                    : $v;
            }, $file);

            if (array_keys($keys) === $keys) {
                $file = array_filter($file);
            }
        }

        return $file;
    }

    /**
     * Normalized php files.
     *
     * @param array $dataset
     *
     * @return array
     */
    protected function toNormalizedFiles(array $dataset)
    {
        $keys = $this->fixFileKeys($dataset);

        if (static::FILE_KEYS != $keys || !isset($dataset['name']) || !\is_array($dataset['name'])) {
            return $dataset;
        }

        $files = $dataset;

        foreach (static::FILE_KEYS as $k) {
            unset($files[$k]);
        }

        foreach ($dataset['name'] as $key => $name) {
            $files[$key] = $this->toNormalizedFiles([
                'error' => $dataset['error'][$key],
                'name' => $name,
                'type' => $dataset['type'][$key],
                'tmp_name' => $dataset['tmp_name'][$key],
                'size' => $dataset['size'][$key],
            ]);
        }

        return $files;
    }

    /**
     * Fix file keys in PHP 8.
     *
     * @param array &$files
     */
    private function fixFileKeys(array &$files)
    {
        unset($files['full_path']);
        $keys = array_keys($files);
        sort($keys);

        return $keys;
    }

    /**
     * Retrieve all files from collection.
     *
     * @return array<string, \Swilen\Http\Component\File\UploadedFile>
     */
    public function all()
    {
        return $this->params;
    }
}
