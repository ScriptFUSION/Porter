<?php
namespace ScriptFUSION\Porter\Provider;

use ScriptFUSION\Porter\Cache\CacheToggle;
use ScriptFUSION\Porter\Cache\CacheUnavailableException;
use ScriptFUSION\Porter\Connector\Connector;
use ScriptFUSION\Porter\Provider\Resource\ProviderResource;

abstract class AbstractProvider implements Provider, CacheToggle
{
    private $connector;

    public function __construct(Connector $connector)
    {
        $this->connector = $connector;
    }

    /**
     * @param ProviderResource $resource
     *
     * @return \Iterator
     *
     * @throws ForeignResourceException A foreign resource was received.
     */
    public function fetch(ProviderResource $resource)
    {
        if ($resource->getProviderClassName() !== static::class) {
            throw new ForeignResourceException(sprintf(
                'Cannot fetch data from foreign source: "%s".',
                get_class($resource)
            ));
        }

        return $resource->fetch($this->connector);
    }

    /**
     * @return Connector
     */
    public function getConnector()
    {
        return $this->connector;
    }

    public function enableCache()
    {
        $connector = $this->getConnector();

        if (!$connector instanceof CacheToggle) {
            throw CacheUnavailableException::modify();
        }

        $connector->enableCache();
    }

    public function disableCache()
    {
        $connector = $this->getConnector();

        if (!$connector instanceof CacheToggle) {
            throw CacheUnavailableException::modify();
        }

        $connector->disableCache();
    }

    public function isCacheEnabled()
    {
        $connector = $this->getConnector();

        if (!$connector instanceof CacheToggle) {
            return false;
        }

        return $connector->isCacheEnabled();
    }
}
