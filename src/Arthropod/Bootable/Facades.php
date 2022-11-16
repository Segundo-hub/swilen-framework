<?php

namespace Swilen\Arthropod\Bootable;

use Swilen\Arthropod\Application;
use Swilen\Arthropod\Contract\BootableServiceContract;
use Swilen\Petiole\Facade;

class Facades implements BootableServiceContract
{
    /**
     * @param \Swilen\Arthropod\Application $app
     *
     * @return void
     */
    public function puriyboot(Application $app)
    {
        Facade::flushFacadeInstances();

        Facade::setFacadeApplication($app);
    }
}
