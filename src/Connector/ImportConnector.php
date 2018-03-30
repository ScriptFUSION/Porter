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
}
