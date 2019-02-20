<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Connector\Recoverable;

use Amp\Promise;

/**
 * Contains a fetch exception handler that does not have private state and therefore does not require initialization.
 */
class StatelessRecoverableExceptionHandler implements RecoverableExceptionHandler
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

    final public function __invoke(RecoverableException $exception): ?Promise
    {
        return ($this->handler)($exception);
    }
}
