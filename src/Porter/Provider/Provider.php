<?php
namespace ScriptFUSION\Porter\Provider;

use ScriptFUSION\Porter\Cache\CacheOperationProhibitedException;
use ScriptFUSION\Porter\Cache\MutableCacheState;
use ScriptFUSION\Porter\Connector\Connector;

abstract class Provider implements MutableCacheState
{
    private $connector;

    public function __construct(Connector $connector)
    {
        $this->connector = $connector;
    }

    /**
     * @param ProviderDataFetcher $dataFetcher
     *
     * @return \Iterator
     *
     * @throws ForeignDataFetcherException A foreign data fetcher was received.
     */
    public function fetch(ProviderDataFetcher $dataFetcher)
    {
        if ($dataFetcher->getProviderName() !== static::class) {
            throw new ForeignDataFetcherException(sprintf(
                'Cannot fetch data from foreign type: "%s".',
                get_class($dataFetcher)
            ));
        }

        return $dataFetcher->fetch($this->connector);
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
