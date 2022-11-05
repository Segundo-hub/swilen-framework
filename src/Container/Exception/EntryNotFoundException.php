<?php

namespace Swilen\Container\Exception;

use Psr\Container\NotFoundExceptionInterface;

class EntryNotFoundException extends \Exception implements NotFoundExceptionInterface
{
}
