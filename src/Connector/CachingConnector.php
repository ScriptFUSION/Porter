<?php
namespace ScriptFUSION\Porter\Connector;

use Psr\Cache\CacheItemPoolInterface;
use ScriptFUSION\Porter\Cache\CacheToggle;
use ScriptFUSION\Porter\Cache\MemoryCache;
use ScriptFUSION\Porter\Options\EncapsulatedOptions;

/**
 * Caches remote data using PSR-6-compliant objects.
 */
abstract class CachingConnector implements Connector, CacheToggle
{
    /**
     * @var CacheItemPoolInterface
     */
    private $cache;

    /**
     * @var bool
     */
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

        isset($hash) && $this->cache->save($this->cache->getItem($hash)->set($data));

        return $data;
    }

    abstract public function fetchFreshData($source, EncapsulatedOptions $options = null);

    public function getCache()
    {
        return $this->cache;
    }

    public function setCache(CacheItemPoolInterface $cache)
    {
        $this->cache = $cache;
    }

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
        return json_encode($structure);
    }
}
