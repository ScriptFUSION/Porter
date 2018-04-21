<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Connector;

use Amp\Promise;
use ScriptFUSION\Porter\Cache\CacheUnavailableException;
use ScriptFUSION\Porter\Connector\Recoverable\RecoverableConnectorException;
use ScriptFUSION\Porter\Connector\Recoverable\RecoverableExceptionHandler;
use ScriptFUSION\Porter\Connector\Recoverable\StatelessRecoverableExceptionHandler;

/**
 * Connector whose lifecycle is synchronised with an import operation. Ensures correct ConnectionContext is delivered
 * to the wrapped connector.
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
    private $userReh;

    /**
     * Resource-defined exception handler called when a recoverable exception is thrown by Connector::fetch().
     *
     * @var RecoverableExceptionHandler
     */
    private $resourceReh;

    private $maxFetchAttempts;

    /**
     * @param Connector|AsyncConnector $connector Wrapped connector.
     * @param ConnectionContext $connectionContext Connection context.
     * @param RecoverableExceptionHandler $recoverableExceptionHandler
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
        $this->userReh = $recoverableExceptionHandler;
        $this->maxFetchAttempts = $maxFetchAttempts;
    }

    public function fetch(string $source)
    {
        return \ScriptFUSION\Retry\retry(
            $this->maxFetchAttempts,
            function () use ($source) {
                return $this->connector->fetch($this->connectionContext, $source);
            },
            $this->createExceptionHandler()
        );
    }

    public function fetchAsync(string $source): Promise
    {
        return \ScriptFUSION\Retry\retryAsync(
            $this->maxFetchAttempts,
            function () use ($source): Promise {
                return \Amp\call($this->connector->fetchAsync($this->connectionContext, $source));
            },
            $this->createExceptionHandler()
        );
    }

    private function createExceptionHandler(): \Closure
    {
        $userHandlerCloned = $resourceHandlerCloned = false;

        return function (\Exception $exception) use (&$userHandlerCloned, &$resourceHandlerCloned): void {
            // Throw exception instead of retrying, if unrecoverable.
            if (!$exception instanceof RecoverableConnectorException) {
                throw $exception;
            }

            // Call resource's exception handler, if defined.
            if ($this->resourceReh) {
                self::invokeHandler($this->resourceReh, $exception, $resourceHandlerCloned);
            }

            // Call user's exception handler.
            self::invokeHandler($this->userReh, $exception, $userHandlerCloned);
        };
    }

    /**
     * Invokes the specified fetch exception handler, cloning it if required.
     *
     * @param RecoverableExceptionHandler $handler Fetch exception handler.
     * @param \Exception $exception Exception to pass to the handler.
     * @param bool $cloned False if handler requires cloning, true if handler has already been cloned.
     */
    private static function invokeHandler(
        RecoverableExceptionHandler &$handler,
        \Exception $exception,
        bool &$cloned
    ): void {
        if (!$cloned && !$handler instanceof StatelessRecoverableExceptionHandler) {
            $handler = clone $handler;
            $handler->initialize();
            $cloned = true;
        }

        $handler($exception);
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
        if ($this->resourceReh !== null) {
            throw new \LogicException('Cannot set resource\'s recoverable exception handler: already set!');
        }

        $this->resourceReh = $recoverableExceptionHandler;
    }
}
