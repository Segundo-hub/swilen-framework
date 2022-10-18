<?php

namespace Swilen\Arthropod\Contract;

interface ExceptionHandler
{
    /**
     * @param \Throwable $exception
     *
     * @return void
     */
    public function report(\Throwable $exception);

    /**
     * @param \Throwable $exception
     *
     * @return \Swilen\Http\Response
     */
    public function render(\Throwable $exception);
}
