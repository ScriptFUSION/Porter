<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Connector;

use Amp\Coroutine;
use Amp\Promise;
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

    public function __construct(bool $mustCache, FetchExceptionHandler $fetchExceptionHandler, int $maxFetchAttempts)
    {
        $this->mustCache = $mustCache;
        $this->fetchExceptionHandler = $fetchExceptionHandler;
        $this->maxFetchAttempts = $maxFetchAttempts;
    }

    /**
     * Gets a value indicating whether the response for this request must be cached.
     *
     * @return bool True if the response must be cached, otherwise false.
     */
    public function mustCache(): bool
    {
        return $this->mustCache;
    }

    /**
     * Retries the specified callback up to a predefined number of times. If it throws a recoverable exception, the
     * resource fetch exception handler is invoked, if set, and then the predefined fetch exception handler.
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
            $this->createExceptionHandler()
        );
    }

    /**
     * Closes over the specified async generator function with a static factory method that invokes it as a coroutine
     * and retries it up to the predefined number of fetch attempts. If it throws a recoverable exception, the resource
     * fetch exception handler is invoked, if set, and then the predefined fetch exception handler.
     *
     * @param \Closure $asyncGenerator Async generator function.
     *
     * @return Promise The result returned by the async function.
     */
    public function retryAsync(\Closure $asyncGenerator): Promise
    {
        return \ScriptFUSION\Retry\retryAsync(
            $this->maxFetchAttempts,
            static function () use ($asyncGenerator): Coroutine {
                return new Coroutine($asyncGenerator());
            },
            $this->createExceptionHandler()
        );
    }

    private function createExceptionHandler(): \Closure
    {
        $userHandlerCloned = $providerHandlerCloned = false;

        return function (\Exception $exception) use (&$userHandlerCloned, &$providerHandlerCloned): void {
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
        };
    }

    /**
     * Invokes the specified fetch exception handler, cloning it if required.
     *
     * @param FetchExceptionHandler $handler Fetch exception handler.
     * @param \Exception $exception Exception to pass to the handler.
     * @param bool $cloned False if handler requires cloning, true if handler has already been cloned.
     */
    private static function invokeHandler(FetchExceptionHandler &$handler, \Exception $exception, bool &$cloned): void
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
    public function setResourceFetchExceptionHandler(FetchExceptionHandler $resourceFetchExceptionHandler): void
    {
        if ($this->resourceFetchExceptionHandler !== null) {
            throw new \LogicException('Cannot set resource fetch exception handler: already set!');
        }

        $this->resourceFetchExceptionHandler = $resourceFetchExceptionHandler;
    }
}
