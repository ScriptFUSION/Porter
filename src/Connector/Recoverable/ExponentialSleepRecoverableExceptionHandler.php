<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Connector\Recoverable;

use ScriptFUSION\Retry\ExceptionHandler\ExponentialBackoffExceptionHandler;

/**
 * Sleeps for an exponentially increasing series of delays specified in microseconds.
 */
class ExponentialSleepRecoverableExceptionHandler implements RecoverableExceptionHandler
{
    private $initialDelay;

    private $handler;

    /**
     * Initializes this instance with the specified initial delay. The initial delay will be used when the first
     * exception is handled; subsequent exceptions will cause longer delays.
     *
     * @param int $initialDelay Initial delay.
     */
    public function __construct($initialDelay = ExponentialBackoffExceptionHandler::DEFAULT_COEFFICIENT)
    {
        $this->initialDelay = $initialDelay | 0;
    }

    public function initialize(): void
    {
        $this->handler = new ExponentialBackoffExceptionHandler($this->initialDelay);
    }

    public function __invoke(RecoverableException $exception): void
    {
        ($this->handler)($exception);
    }
}
