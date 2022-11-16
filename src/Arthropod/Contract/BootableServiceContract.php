<?php

namespace Swilen\Arthropod\Contract;

use Swilen\Arthropod\Application;

interface BootableServiceContract
{
    /**
     * Bootstrap this service.
     *
     * @param \Swilen\Arthropod\Application $app
     *
     * @return void
     */
    public function puriyboot(Application $app);
}
