<?php
namespace ScriptFUSION\Porter\Connector;

/**
 * Specifies runtime connection settings and provides utility methods.
 */
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

    /**
     * Gets a value indicating whether the response for this request must be cached.
     *
     * @return bool True if the response must be cached, otherwise false.
     */
    public function mustCache()
    {
        return $this->mustCache;
    }

    /**
     * Retries the specified callback a predefined number of times with a predefined exception handler.
     *
     * @param callable $callback Callback.
     *
     * @return mixed The result of the callback invocation.
     */
    public function retry(callable $callback)
    {
        return \ScriptFUSION\Retry\retry(
            $this->maxFetchAttempts,
            $callback,
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
