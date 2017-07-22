<?php
namespace ScriptFUSION\Porter\Connector;

final class ConnectionContext
{
    private $mustCache;

    private $fetchExceptionHandler;

    private $maxFetchAttempts;

    public function __construct($mustCache, callable $fetchExceptionHandler, $maxFetchAttempts)
    {
        $this->mustCache = (bool)$mustCache;
        $this->fetchExceptionHandler = $fetchExceptionHandler;
        $this->maxFetchAttempts = (int)$maxFetchAttempts;
    }

    public function mustCache()
    {
        return $this->mustCache;
    }

    public function retry(callable $callable)
    {
        return \ScriptFUSION\Retry\retry(
            $this->maxFetchAttempts,
            $callable,
            function (\Exception $exception) {
                // Throw exception if unrecoverable.
                if (!$exception instanceof RecoverableConnectorException) {
                    throw $exception;
                }

                // TODO Clone exception handler to avoid persisting state between calls.
                call_user_func($this->fetchExceptionHandler, $exception);
            }
        );
    }
}
