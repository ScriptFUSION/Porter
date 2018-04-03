<?php
namespace ScriptFUSION\Porter\Connector\FetchExceptionHandler;

use ScriptFUSION\Retry\ExceptionHandler\ExponentialBackoffExceptionHandler;

/**
 * Sleeps for an exponentially increasing series of delays specified in microseconds.
 */
class ExponentialSleepFetchExceptionHandler implements FetchExceptionHandler
{
    private $handler;

    public function reset()
    {
        $this->handler = new ExponentialBackoffExceptionHandler;
    }

    public function __invoke(\Exception $exception)
    {
        call_user_func($this->handler, $exception);
    }
}
