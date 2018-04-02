<?php
namespace ScriptFUSION\Porter\Connector;

/**
 * Specifies runtime connection settings and provides utility methods.
 */
final class ConnectionContext
{
    private $mustCache;

    /**
     * User-defined exception handler called when a recoverable exception is thrown by Connector::fetch().
     *
     * @var callable
     */
    private $fetchExceptionHandler;

    /**
     * Provider-defined exception handler called when a recoverable exception is thrown by Connector::fetch().
     *
     * @var callable
     */
    private $providerFetchExceptionHandler;

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

                // Call provider's exception handler, if defined.
                if ($this->providerFetchExceptionHandler) {
                    call_user_func($this->providerFetchExceptionHandler, $exception);
                }

                // TODO Clone exception handler to avoid persisting state between calls.
                call_user_func($this->fetchExceptionHandler, $exception);
            }
        );
    }

    /**
     * Sets an exception handler to be called when a recoverable exception is thrown by Connector::fetch().
     *
     * This handler is intended to be set by provider resources only and is called before the user-defined handler.
     *
     * @param callable $providerFetchExceptionHandler Exception handler.
     */
    public function setProviderFetchExceptionHandler(callable $providerFetchExceptionHandler)
    {
        if ($this->providerFetchExceptionHandler !== null) {
            throw new \LogicException('Cannot set provider fetch exception handler: already set!');
        }

        $this->providerFetchExceptionHandler = $providerFetchExceptionHandler;
    }
}
