<?php
namespace ScriptFUSION\Porter\Connector;

use Psr\Cache\CacheItemPoolInterface;
use ScriptFUSION\Porter\Cache\CacheEnabler;
use ScriptFUSION\Porter\Cache\CacheItem;
use ScriptFUSION\Porter\Cache\MemoryCache;
use ScriptFUSION\Porter\Options\EncapsulatedOptions;

abstract class CachingConnector implements Connector, CacheEnabler
{
    private $cache;

    private $cacheEnabled = true;

    public function __construct(CacheItemPoolInterface $cache = null)
    {
        $this->cache = $cache ?: new MemoryCache;
    }

    public function fetch($source, EncapsulatedOptions $options = null)
    {
        $optionsCopy = $options ? $options->copy() : [];

        if ($this->isCacheEnabled()) {
            ksort($optionsCopy);

            $hash = $this->hash([$source, $optionsCopy]);

            if ($this->cache->hasItem($hash)) {
                return $this->cache->getItem($hash)->get();
            }
        }

        $data = $this->fetchFreshData($source, $options);

        isset($hash) && $this->cache->save(new CacheItem($hash, $data));

        return $data;
    }

    abstract public function fetchFreshData($source, EncapsulatedOptions $options = null);

    public function enableCache()
    {
        $this->cacheEnabled = true;
    }

    public function disableCache()
    {
        $this->cacheEnabled = false;
    }

    public function isCacheEnabled()
    {
        return $this->cacheEnabled;
    }

    private function hash(array $structure)
    {
        return sha1(json_encode($structure));
    }
}
