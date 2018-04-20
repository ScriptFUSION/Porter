<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Connector;

use Amp\Promise;
use ScriptFUSION\Porter\Cache\CacheUnavailableException;
use ScriptFUSION\Porter\Connector\FetchExceptionHandler\FetchExceptionHandler;

/**
 * Connector whose lifecycle is synchronised with an import operation. Ensures correct ConnectionContext is delivered
 * to the wrapped connector.
 *
 * Do not store references to this connector that would prevent it expiring when an import operation ends.
 */
final class ImportConnector implements ConnectorWrapper
{
    private $connector;

    private $context;

    /**
     * @param Connector|AsyncConnector $connector Wrapped connector.
     * @param ConnectionContext $context Connection context.
     */
    public function __construct($connector, ConnectionContext $context)
    {
        if ($context->mustCache() && !$connector instanceof CachingConnector) {
            throw CacheUnavailableException::createUnsupported();
        }

        $this->connector = clone $connector;
        $this->context = $context;
    }

    public function fetch($source)
    {
        return $this->connector->fetch($this->context, $source);
    }

    public function fetchAsync(string $source): Promise
    {
        return $this->connector->fetchAsync($this->context, $source);
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
     * Sets the exception handler to be called when a recoverable exception is thrown by Connector::fetch().
     *
     * @param FetchExceptionHandler $exceptionHandler Fetch exception handler.
     */
    public function setExceptionHandler(FetchExceptionHandler $exceptionHandler): void
    {
        $this->context->setResourceFetchExceptionHandler($exceptionHandler);
    }
}
