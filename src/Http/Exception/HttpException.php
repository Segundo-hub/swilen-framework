<?php

namespace Swilen\Http\Exception;

use Swilen\Arthropod\Exception\HandleExceptions;

class HttpException extends HandleExceptions
{
    public function getTitle()
    {
        return $this->title ?? '';
    }

    public function setTitle(string $title)
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription()
    {
        return $this->description ?? '';
    }

    public function setDescription(string $description)
    {
        $this->description = $description;

        return $this;
    }
}
