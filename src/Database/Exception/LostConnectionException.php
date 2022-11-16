<?php

namespace Swilen\Database\Exception;

class LostConnectionException extends \RuntimeException
{
    /**
     * Create a new query exception instance.
     *
     * @param int        $attempts
     * @param float      $time
     * @param \Throwable $previous
     *
     * @return void
     */
    public function __construct($attempts, $time, $previous)
    {
        parent::__construct('', 0, $previous);

        $this->code     = $previous instanceof \Throwable ? $previous->getCode() : 2002;
        $this->message  = $this->formatMessage($previous, $attempts.' reconnection attempts in '.$time.' ms.');

        if ($previous instanceof \PDOException) {
            $this->errorInfo = $previous->errorInfo;
        }
    }

    /**
     * Create a new query exception instance.
     *
     * @param \Throwable $previous
     *
     * @return string
     */
    protected function formatMessage($previous, string $meessage)
    {
        $error = $previous instanceof \Throwable ? $previous->getMessage() : 'Unknow Error';

        return sprintf('%s (INFO: %s)', $error, $meessage);
    }
}
