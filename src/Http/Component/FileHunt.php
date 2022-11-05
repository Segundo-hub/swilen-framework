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
        $this->params = [];

        $this->addFiles($files);
    }

    /**
     * Add files collection and transform UploadedFile instance.
     *
     * @param array $files
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function addFiles(array $files = [])
    {
        foreach ($files as $key => $file) {
            if (!is_array($file) && !$file instanceof UploadedFile) {
                throw new \InvalidArgumentException(sprintf('Need this file "%s" as instance of "%s"', $file, UploadedFile::class));
            }

            parent::set($key, $this->transformToUploadedFile($file));
        }
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
}
