<?php
namespace ScriptFUSION\Porter\Connector;

use ScriptFUSION\Porter\Cache\CacheUnavailableException;

/**
 * Connector whose lifecycle is synchronised with an import operation. Ensures correct ConnectionContext is delivered
 * to each fetch() operation.
 *
 * Do not store references to this connector that would prevent it expiring when an import operation ends.
 */
final class ImportConnector
{
    private $connector;

    private $context;

    public function __construct(Connector $connector, ConnectionContext $context)
    {
        if ($context->mustCache() && !$connector instanceof CachingConnector) {
            throw CacheUnavailableException::createUnsupported();
        }

        $this->connector = $connector;
        $this->context = $context;
    }

    public function fetch($source)
    {
        return $this->connector->fetch($this->context, $source);
    }

    /**
     * Gets the wrapped connector. Useful for resources to reconfigure connector options during this import.
     *
     * @return Connector
     */
    public function getWrappedConnector()
    {
        return $this->connector;
    }

    /**
     * Sets the exception handler to be called when a recoverable exception is thrown by Connector::fetch().
     *
     * @param callable $exceptionHandler Exception handler.
     */
    public function setExceptionHandler(callable $exceptionHandler)
    {
        $this->context->setProviderFetchExceptionHandler($exceptionHandler);
    }
}
