<?php

namespace Swilen\Arthropod\Exception;

abstract class HandleExceptions extends \RuntimeException
{
    /**
     * @return string
     */
    abstract public function getTitle();

    /**
     * @param string $title
     *
     * @return $this
     */
    abstract public function setTitle(string $title);

    /**
     * @return string
     */
    abstract public function getDescription();

    /**
     * @param string $description
     *
     * @return $this
     */
    abstract public function setDescription(string $description);
}
