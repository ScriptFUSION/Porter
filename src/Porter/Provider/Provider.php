<?php
namespace ScriptFUSION\Porter\Provider;

use ScriptFUSION\Porter\Cache\CacheOperationProhibitedException;
use ScriptFUSION\Porter\Cache\MutableCacheState;
use ScriptFUSION\Porter\Connector\Connector;
use ScriptFUSION\Porter\Provider\DataSource\ProviderDataSource;

abstract class Provider implements MutableCacheState
{
    private $connector;

    public function __construct(Connector $connector)
    {
        $this->connector = $connector;
    }

    /**
     * @param ProviderDataSource $dataSource
     *
     * @return \Iterator
     *
     * @throws ForeignDataSourceException A foreign data source was received.
     */
    public function fetch(ProviderDataSource $dataSource)
    {
        if ($dataSource->getProviderName() !== static::class) {
            throw new ForeignDataSourceException(sprintf(
                'Cannot fetch data from foreign source: "%s".',
                get_class($dataSource)
            ));
        }

        return $dataSource->fetch($this->connector);
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

        if (!$connector instanceof MutableCacheState) {
            throw $this->createCacheUnavailableException();
        }

        $connector->enableCache();
    }

    public function disableCache()
    {
        $connector = $this->getConnector();

        if (!$connector instanceof MutableCacheState) {
            throw $this->createCacheUnavailableException();
        }

        $connector->disableCache();
    }

    public function isCacheEnabled()
    {
        $connector = $this->getConnector();

        if (!$connector instanceof MutableCacheState) {
            return false;
        }

        return $connector->isCacheEnabled();
    }

    private function createCacheUnavailableException()
    {
        return new CacheOperationProhibitedException('Cannot modify cache: cache unavailable.');
    }
}
