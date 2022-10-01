<?php

namespace Swilen\Arthropod\Contract;

use Swilen\Arthropod\Application;

interface BootableContract
{
    /**
     * @param \Swilen\Arthropod\Application $app
     *
     * @return void
     */
    public function puriyboot(Application $app);
}
