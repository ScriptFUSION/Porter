<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Connector;

use ScriptFUSION\Async\Throttle\Throttle;
use ScriptFUSION\Porter\Cache\CacheUnavailableException;
use ScriptFUSION\Porter\Connector\Recoverable\RecoverableException;
use ScriptFUSION\Porter\Connector\Recoverable\RecoverableExceptionHandler;
use ScriptFUSION\Porter\Connector\Recoverable\StatelessRecoverableExceptionHandler;
use ScriptFUSION\Porter\Provider\Provider;
use function ScriptFUSION\Retry\retry;

/**
 * Connector whose lifecycle is synchronised with an import operation. Intercepts failed connections to facilitate
 * automatic retries and manages caching.
 *
 * Do not store references to this connector that would prevent it expiring when an import operation ends.
 *
 * @internal Do not create instances of this class in client code.
 */
final class ImportConnector implements ConnectorWrapper
{
    private Connector $connector;

    /**
     * User-defined exception handler called when a recoverable exception is thrown by Connector::fetch().
     */
    private RecoverableExceptionHandler $userExceptionHandler;

    /**
     * Resource-defined exception handler called when a recoverable exception is thrown by Connector::fetch().
     */
    private ?RecoverableExceptionHandler $resourceExceptionHandler = null;

    /**
     * @param Provider $provider Provider.
     * @param Connector $connector Wrapped connector.
     * @param RecoverableExceptionHandler $recoverableExceptionHandler User's recoverable exception handler.
     * @param int $maxFetchAttempts Maximum fetch attempts.
     * @param bool $mustCache True if the response must be cached, otherwise false.
     * @param Throttle $throttle Connection throttle invoked each time the connector fetches data.
     */
    public function __construct(
        private readonly Provider $provider,
        Connector $connector,
        RecoverableExceptionHandler $recoverableExceptionHandler,
        private readonly int $maxFetchAttempts,
        bool $mustCache,
        private readonly Throttle $throttle
    ) {
        if ($mustCache && !$connector instanceof CachingConnector) {
            throw CacheUnavailableException::createUnsupported();
        }

        $this->connector = clone (
            $connector instanceof CachingConnector && !$mustCache
                // Bypass cache when not required.
                ? $connector->getWrappedConnector()
                : $connector
        );
        $this->userExceptionHandler = $recoverableExceptionHandler;
    }

    /**
     * Fetches data from the specified data source.
     *
     * @param DataSource $source Data source.
     *
     * @return mixed Data.
     */
    public function fetch(DataSource $source): mixed
    {
        return retry(
            $this->maxFetchAttempts,
            fn () => $this->throttle->async($this->connector->fetch(...), $source)->await(),
            $this->createExceptionHandler()
        );
    }

    private function createExceptionHandler(): \Closure
    {
        $userHandlerCloned = $resourceHandlerCloned = false;

        return function (\Exception $exception) use (&$userHandlerCloned, &$resourceHandlerCloned): void {
            // Throw exception instead of retrying, if unrecoverable.
            if (!$exception instanceof RecoverableException) {
                throw $exception;
            }

            // Call resource's exception handler, if defined.
            if ($this->resourceExceptionHandler) {
                self::invokeHandler($this->resourceExceptionHandler, $exception, $resourceHandlerCloned);
            }

            // Call user's exception handler.
            self::invokeHandler($this->userExceptionHandler, $exception, $userHandlerCloned);
        };
    }

    /**
     * Invokes the specified fetch exception handler, cloning it if required.
     *
     * @param RecoverableExceptionHandler $handler Fetch exception handler.
     * @param RecoverableException $recoverableException Recoverable exception to pass to the handler.
     * @param bool $cloned False if handler requires cloning, true if handler has already been cloned.
     */
    private static function invokeHandler(
        RecoverableExceptionHandler &$handler,
        RecoverableException $recoverableException,
        bool &$cloned
    ): void {
        if (!$cloned && !$handler instanceof StatelessRecoverableExceptionHandler) {
            $handler = clone $handler;
            $handler->initialize();
            $cloned = true;
        }

        $handler($recoverableException);
    }

    /**
     * Gets the provider owning the resource being imported.
     */
    public function getProvider(): Provider
    {
        return $this->provider;
    }

    /**
     * Gets the wrapped connector.
     */
    public function getWrappedConnector(): Connector
    {
        return $this->connector;
    }

    /**
     * Finds the base connector by traversing the stack of wrapped connectors.
     */
    public function findBaseConnector(): Connector
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
