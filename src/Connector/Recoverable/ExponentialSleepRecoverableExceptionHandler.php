<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Connector\Recoverable;

use ScriptFUSION\Retry\ExceptionHandler\ExponentialBackoffExceptionHandler;

/**
 * Sleeps for an exponentially increasing series of delays.
 */
class ExponentialSleepRecoverableExceptionHandler implements RecoverableExceptionHandler
{
    private ExponentialBackoffExceptionHandler $handler;

    /**
     * Initializes this instance with the specified initial delay. The initial delay will be used when the first
     * exception is handled; subsequent exceptions will cause longer delays.
     *
     * @param int $initialDelay Initial delay in microseconds.
     */
    public function __construct(
        private readonly int $initialDelay = ExponentialBackoffExceptionHandler::DEFAULT_COEFFICIENT
    ) {
    }

    public function initialize(): void
    {
        $this->handler = new ExponentialBackoffExceptionHandler($this->initialDelay);
    }

    public function __invoke(RecoverableException $exception): void
    {
        ($this->handler)();
    }
}
