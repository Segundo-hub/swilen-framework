<?php

namespace Swilen\Database\Concerns;

trait DetectLostConnections
{
    /**
     * Detect if exception caused by lost connection.
     *
     * @param \Throwable $e
     *
     * @return bool
     */
    protected function causedByLostConnection(\Throwable $e)
    {
        $isMissing = $e->getCode() === 'HY000' || stristr($e->getMessage(), 'server has gone away');
        $code      = isset($e->errorInfo) ? (isset($e->errorInfo[1]) ? $e->errorInfo[1] : 0) : 0;

        return in_array($code, [1317, 2002, 2006]) || $isMissing;
    }
}
