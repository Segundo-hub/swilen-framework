<?php

namespace Swilen\Shared\Arthropod;

use Swilen\Shared\Container\Container;

interface Application extends Container
{
    /**
     * Register application path parts.
     *
     * @param string $path
     *
     * @return string
     */
    public function basePath(string $path = '');

    /**
     * Use application path part.
     *
     * @param string $path
     *
     * @return $this
     */
    public function useBasePath(string $path = '');

    /**
     * Register application path parts.
     *
     * @param string $path
     *
     * @return string
     */
    public function appPath(string $path = '');

    /**
     * Use application path part.
     *
     * @param string $path
     *
     * @return $this
     */
    public function useAppPath(string $path = '');

    /**
     * Register application path parts.
     *
     * @param string $path
     *
     * @return string
     */
    public function configPath(string $path = '');

    /**
     * Use application path part.
     *
     * @param string $path
     *
     * @return $this
     */
    public function useConfigPath(string $path = '');

    /**
     * Return application base uri.
     *
     * @param string $path
     *
     * @return string
     */
    public function storagePath(string $path = '');

    /**
     * Retrive environment file path.
     *
     * @return string
     */
    public function environmentPath();

    /**
     * Use user defined environment file path.
     *
     * @param string $path
     *
     * @return $this
     */
    public function useEnvironmentPath(string $path);

    /**
     * Retrive environment filename.
     *
     * @return string
     */
    public function environmentFile();

    /**
     * Use user defined environment filename.
     *
     * @param string $filename
     *
     * @return $this
     */
    public function useEnvironmentFile(string $filename);

    /**
     * Set application environment.
     *
     * @param string $env The Environment valid `production|development`
     *
     * @return $this
     */
    public function useEnvironment(string $env);
}
