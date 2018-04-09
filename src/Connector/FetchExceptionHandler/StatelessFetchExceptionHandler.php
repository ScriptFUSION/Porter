<?php
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

    final public function initialize()
    {
        // Intentionally empty.
    }

    final public function __invoke(\Exception $exception)
    {
        call_user_func($this->handler, $exception);
    }
}
