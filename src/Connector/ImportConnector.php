<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Connector;

use Amp\Promise;
use ScriptFUSION\Porter\Cache\CacheUnavailableException;
use ScriptFUSION\Porter\Connector\Recoverable\RecoverableException;
use ScriptFUSION\Porter\Connector\Recoverable\RecoverableExceptionHandler;
use ScriptFUSION\Porter\Connector\Recoverable\StatelessRecoverableExceptionHandler;

/**
 * Connector whose lifecycle is synchronised with an import operation. Ensures correct ConnectionContext is delivered
 * to the wrapped connector and intercepts failed connections to facilitate automatic retries.
 *
 * Do not store references to this connector that would prevent it expiring when an import operation ends.
 *
 * @internal Do not create instances of this class in client code.
 */
final class ImportConnector implements ConnectorWrapper
{
    private $connector;

    private $connectionContext;

    /**
     * User-defined exception handler called when a recoverable exception is thrown by Connector::fetch().
     *
     * @var RecoverableExceptionHandler
     */
    private $userExceptionHandler;

    /**
     * Resource-defined exception handler called when a recoverable exception is thrown by Connector::fetch().
     *
     * @var RecoverableExceptionHandler
     */
    private $resourceExceptionHandler;

    private $maxFetchAttempts;

    /**
     * @param Connector|AsyncConnector $connector Wrapped connector.
     * @param ConnectionContext $connectionContext Connection context.
     * @param RecoverableExceptionHandler $recoverableExceptionHandler User's recoverable exception handler.
     * @param int $maxFetchAttempts
     */
    public function __construct(
        $connector,
        ConnectionContext $connectionContext,
        RecoverableExceptionHandler $recoverableExceptionHandler,
        int $maxFetchAttempts
    ) {
        if ($connectionContext->mustCache() && !$connector instanceof CachingConnector) {
            throw CacheUnavailableException::createUnsupported();
        }

        $this->connector = clone $connector;
        $this->connectionContext = $connectionContext;
        $this->userExceptionHandler = $recoverableExceptionHandler;
        $this->maxFetchAttempts = $maxFetchAttempts;
    }

    public function fetch(string $source)
    {
        return \ScriptFUSION\Retry\retry(
            $this->maxFetchAttempts,
            function () use ($source) {
                return $this->connector->fetch($source, $this->connectionContext);
            },
            $this->createExceptionHandler()
        );
    }

    public function fetchAsync(string $source): Promise
    {
        return \ScriptFUSION\Retry\retryAsync(
            $this->maxFetchAttempts,
            function () use ($source): Promise {
                return \Amp\call(
                    function () use ($source) {
                        return $this->connector->fetchAsync($source, $this->connectionContext);
                    }
                );
            },
            $this->createExceptionHandler()
        );
    }

    private function createExceptionHandler(): \Closure
    {
        $userHandlerCloned = $resourceHandlerCloned = false;

        return function (\Exception $exception) use (&$userHandlerCloned, &$resourceHandlerCloned): ?Promise {
            // Throw exception instead of retrying, if unrecoverable.
            if (!$exception instanceof RecoverableException) {
                throw $exception;
            }

            // Call resource's exception handler, if defined.
            if ($this->resourceExceptionHandler) {
                $results[] = self::invokeHandler($this->resourceExceptionHandler, $exception, $resourceHandlerCloned);
            }

            // Call user's exception handler.
            $results[] = self::invokeHandler($this->userExceptionHandler, $exception, $userHandlerCloned);

            /*
             * Handlers may return a Promise, but all other return values are discarded. Although the underlying
             * library supports returning false, Porter only allows exceptions to short-circuit. However,
             * Porter does nothing to restrict promises that return false, although it is discouraged.
             */
            return ($promises = array_filter(
                $results,
                static function ($value): bool {
                    return $value instanceof Promise;
                }
            )) ? \Amp\Promise\all($promises) : null;
        };
    }

    /**
     * Invokes the specified fetch exception handler, cloning it if required.
     *
     * @param RecoverableExceptionHandler $handler Fetch exception handler.
     * @param RecoverableException $recoverableException Recoverable exception to pass to the handler.
     * @param bool $cloned False if handler requires cloning, true if handler has already been cloned.
     *
     * @return Promise|null
     */
    private static function invokeHandler(
        RecoverableExceptionHandler &$handler,
        RecoverableException $recoverableException,
        bool &$cloned
    ): ?Promise {
        if (!$cloned && !$handler instanceof StatelessRecoverableExceptionHandler) {
            $handler = clone $handler;
            $handler->initialize();
            $cloned = true;
        }

        return $handler($recoverableException);
    }

    /**
     * Gets the wrapped connector. Useful for resources to reconfigure connector options during this import.
     *
     * @return Connector|AsyncConnector Wrapped connector.
     */
    public function getWrappedConnector()
    {
        return $this->connector;
    }

    /**
     * Finds the base connector by traversing the stack of wrapped connectors.
     *
     * @return Connector|AsyncConnector Base connector.
     */
    public function findBaseConnector()
    {
        $connector = $this->connector;

        while ($connector instanceof ConnectorWrapper) {
            $connector = $connector->getWrappedConnector();
        }

        return $connector;
    }

    /**
     * Sets an exception handler to be called when a recoverable exception is thrown by Connector::fetch().
     *
     * The handler is intended to be set by provider resources, once only, and is called before the user-defined
     * handler.
     *
     * @param RecoverableExceptionHandler $recoverableExceptionHandler Recoverable exception handler.
     */
    public function setRecoverableExceptionHandler(RecoverableExceptionHandler $recoverableExceptionHandler): void
    {
        if ($this->resourceExceptionHandler !== null) {
            throw new \LogicException('Cannot set resource\'s recoverable exception handler: already set!');
        }

        $this->resourceExceptionHandler = $recoverableExceptionHandler;
    }
}
