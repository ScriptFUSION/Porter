<?php
namespace ScriptFUSION\Porter\Connector\FetchExceptionHandler;

/**
 * Contains a stateless fetch exception handler that does not respond to reset() calls.
 */
final class StatelessFetchExceptionHandler implements FetchExceptionHandler
{
    private $handler;

    public function __construct(callable $handler)
    {
        $this->handler = $handler;
    }

    public function reset()
    {
        // Intentionally empty.
    }

    public function __invoke(\Exception $exception)
    {
        call_user_func($this->handler, $exception);
    }
}
