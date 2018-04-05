<?php
namespace ScriptFUSION\Porter\Connector;

use ScriptFUSION\Porter\Connector\FetchExceptionHandler\FetchExceptionHandler;
use ScriptFUSION\Porter\Connector\FetchExceptionHandler\StatelessFetchExceptionHandler;

/**
 * Specifies runtime connection settings and provides utility methods.
 */
final class ConnectionContext
{
    private $mustCache;

    /**
     * User-defined exception handler called when a recoverable exception is thrown by Connector::fetch().
     *
     * @var FetchExceptionHandler
     */
    private $fetchExceptionHandler;

    /**
     * Resource-defined exception handler called when a recoverable exception is thrown by Connector::fetch().
     *
     * @var FetchExceptionHandler
     */
    private $resourceFetchExceptionHandler;

    private $maxFetchAttempts;

    public function __construct($mustCache, FetchExceptionHandler $fetchExceptionHandler, $maxFetchAttempts)
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
        $userHandlerCloned = $providerHandlerCloned = false;

        return \ScriptFUSION\Retry\retry(
            $this->maxFetchAttempts,
            $callback,
            function (\Exception $exception) use (&$userHandlerCloned, &$providerHandlerCloned) {
                // Throw exception instead of retrying, if unrecoverable.
                if (!$exception instanceof RecoverableConnectorException) {
                    throw $exception;
                }

                // Call provider's exception handler, if defined.
                if ($this->resourceFetchExceptionHandler) {
                    self::invokeHandler($this->resourceFetchExceptionHandler, $exception, $providerHandlerCloned);
                }

                // Call user's exception handler.
                self::invokeHandler($this->fetchExceptionHandler, $exception, $userHandlerCloned);
            }
        );
    }

    /**
     * Invokes the specified fetch exception handler, cloning it if required.
     *
     * @param FetchExceptionHandler $handler Fetch exception handler.
     * @param \Exception $exception Exception to pass to the handler.
     * @param bool $cloned False if handler requires cloning, true if handler has already been cloned.
     */
    private static function invokeHandler(FetchExceptionHandler &$handler, \Exception $exception, &$cloned)
    {
        if (!$cloned && !$handler instanceof StatelessFetchExceptionHandler) {
            $handler = clone $handler;
            $handler->initialize();
            $cloned = true;
        }

        $handler($exception);
    }

    /**
     * Sets an exception handler to be called when a recoverable exception is thrown by Connector::fetch().
     *
     * The handler is intended to be set by provider resources only once and is called before the user-defined handler.
     *
     * @param FetchExceptionHandler $resourceFetchExceptionHandler Exception handler.
     */
    public function setResourceFetchExceptionHandler(FetchExceptionHandler $resourceFetchExceptionHandler)
    {
        if ($this->resourceFetchExceptionHandler !== null) {
            throw new \LogicException('Cannot set resource fetch exception handler: already set!');
        }

        $this->resourceFetchExceptionHandler = $resourceFetchExceptionHandler;
    }
}
