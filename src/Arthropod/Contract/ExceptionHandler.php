<?php

namespace Swilen\Arthropod\Contract;

interface ExceptionHandler
{
    /**
     * Report exception and save in error_logs.
     *
     * @param \Throwable $exception
     *
     * @return void
     */
    public function report(\Throwable $exception);

    /**
     * Render exceptions to response.
     *
     * @param \Throwable $exception
     *
     * @return \Swilen\Http\Response
     */
    public function render(\Throwable $exception);
}
