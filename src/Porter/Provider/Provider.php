<?php
namespace ScriptFUSION\Porter\Provider;

use ScriptFUSION\Porter\Cache\CacheEnabler;
use ScriptFUSION\Porter\Cache\CacheOperationProhibitedException;
use ScriptFUSION\Porter\Connector\Connector;

abstract class Provider implements CacheEnabler
{
    private $connector;

    public function __construct(Connector $connector)
    {
        $this->connector = $connector;
    }

    /**
     * @param ProviderDataType $providerDataType
     *
     * @return \Iterator
     */
    public function fetch(ProviderDataType $providerDataType)
    {
        if ($providerDataType->getProviderName() !== static::class) {
            // TODO. Proper exception type.
            throw new \RuntimeException('Cannot fetch data for foreign type: ' . get_class($providerDataType));
        }

        return $providerDataType->fetch($this->connector);
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

        if (!$connector instanceof CacheEnabler) {
            throw $this->createCacheUnavailableException();
        }

        $connector->enableCache();
    }

    public function disableCache()
    {
        $connector = $this->getConnector();

        if (!$connector instanceof CacheEnabler) {
            throw $this->createCacheUnavailableException();
        }

        $connector->disableCache();
    }

    public function isCacheEnabled()
    {
        $connector = $this->getConnector();

        if (!$connector instanceof CacheEnabler) {
            return false;
        }

        return $connector->isCacheEnabled();
    }

    private function createCacheUnavailableException()
    {
        return new CacheOperationProhibitedException('Cannot modify cache: cache unavailable.');
    }
}
