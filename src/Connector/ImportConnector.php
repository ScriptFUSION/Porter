<?php
namespace ScriptFUSION\Porter\Connector;

use ScriptFUSION\Porter\Cache\CacheUnavailableException;
use ScriptFUSION\Porter\Connector\FetchExceptionHandler\FetchExceptionHandler;

/**
 * Connector whose lifecycle is synchronised with an import operation. Ensures correct ConnectionContext is delivered
 * to each fetch() operation.
 *
 * Do not store references to this connector that would prevent it expiring when an import operation ends.
 */
final class ImportConnector implements ConnectorWrapper
{
    private $connector;

    private $context;

    public function __construct(Connector $connector, ConnectionContext $context)
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

    /**
     * Gets the wrapped connector. Useful for resources to reconfigure connector options during this import.
     *
     * @return Connector Wrapped connector.
     */
    public function getWrappedConnector()
    {
        return $this->connector;
    }

    /**
     * Finds the base connector by traversing the stack of wrapped connectors.
     *
     * @return Connector Base connector.
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
    public function setExceptionHandler(FetchExceptionHandler $exceptionHandler)
    {
        $this->context->setResourceFetchExceptionHandler($exceptionHandler);
    }
}
