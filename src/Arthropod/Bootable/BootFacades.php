<?php

namespace Swilen\Arthropod\Bootable;

use Swilen\Arthropod\Application;
use Swilen\Arthropod\Contract\BootableContract;
use Swilen\Petiole\Facades\Facade;

class BootFacades implements BootableContract
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
