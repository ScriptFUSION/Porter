<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Provider\Resource;

use Amp\Iterator;
use ScriptFUSION\Porter\Connector\ImportConnector;

/**
 * Represents a pseudo bisynchronous resource by wrapping an async-only resource.
 */
class PseudoBisyncResource implements ProviderResource, AsyncResource
{
    private $resource;

    public function __construct(AsyncResource $resource)
    {
        $this->resource = $resource;
    }

    public function getProviderClassName(): string
    {
        return $this->resource->getProviderClassName();
    }

    public function fetch(ImportConnector $connector): \Iterator
    {
        throw new \LogicException('Not implemented. Did you call fetch() instead of fetchAsync()?');
    }

    public function fetchAsync(ImportConnector $connector): Iterator
    {
        return $this->resource->fetchAsync($connector);
    }
}
