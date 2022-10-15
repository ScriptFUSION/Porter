<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Connector\Recoverable;

use ScriptFUSION\Retry\ExceptionHandler\AsyncExponentialBackoffExceptionHandler;

/**
 * Delays async execution for an exponentially increasing series of delays.
 */
class ExponentialAsyncDelayRecoverableExceptionHandler implements RecoverableExceptionHandler
{
    private $initialDelay;

    /**
     * @var AsyncExponentialBackoffExceptionHandler
     */
    private $handler;

    /**
     * Initializes this instance with the specified initial delay. The initial delay will be used when the first
     * exception is handled; subsequent exceptions will cause longer delays.
     *
     * @param int $initialDelay Initial delay in milliseconds.
     */
    public function __construct(int $initialDelay = AsyncExponentialBackoffExceptionHandler::DEFAULT_COEFFICIENT)
    {
        $this->initialDelay = $initialDelay;
    }

    public function initialize(): void
    {
        $this->handler = new AsyncExponentialBackoffExceptionHandler($this->initialDelay);
    }

    public function __invoke(RecoverableException $exception): void
    {
        ($this->handler)();
    }
}
