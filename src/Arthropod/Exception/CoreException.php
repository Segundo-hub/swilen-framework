<?php

namespace Swilen\Arthropod\Exception;

abstract class CoreException extends \Exception
{
    /**
     * Get exception title if exists
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title ?? '';
    }

    /**
     * Get exception description if exists
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description ?? '';
    }
}
