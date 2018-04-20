<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Connector\FetchExceptionHandler;

/**
 * Contains a fetch exception handler that does not have private state and therefore does not require initialization.
 */
class StatelessFetchExceptionHandler implements FetchExceptionHandler
{
    private $handler;

    public function __construct(callable $handler)
    {
        $this->handler = $handler;
    }

    final public function initialize(): void
    {
        // Intentionally empty.
    }

    final public function __invoke(\Exception $exception): void
    {
        ($this->handler)($exception);
    }
}
