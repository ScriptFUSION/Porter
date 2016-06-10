<?php
namespace ScriptFUSION\Porter\Connector;

use ScriptFUSION\Porter\Cache\CacheEnabler;
use ScriptFUSION\Porter\Cache\MemoryCache;
use ScriptFUSION\Porter\Options\EncapsulatedOptions;

abstract class CachingConnector implements Connector, CacheEnabler
{
    private $cache;

    public function __construct()
    {
        $this->cache = new MemoryCache;
    }

    public function fetch($source, EncapsulatedOptions $options = null)
    {
        $params = $options ? $options->copy() : [];

        if ($this->cache->isEnabled()) {
            ksort($params);

            $hash = $this->hash([$source, $params]);

            if ($this->cache->has($hash)) {
                return $this->cache->get($hash);
            }
        }

        $data = $this->fetchFreshData($source, $options);

        isset($hash) && $this->cache->set($hash, $data);

        return $data;
    }

    abstract public function fetchFreshData($source, EncapsulatedOptions $options = null);

    public function enableCache()
    {
        $this->cache->enable();
    }

    public function disableCache()
    {
        $this->cache->disable();
    }

    public function isCacheEnabled()
    {
        return $this->cache->isEnabled();
    }

    private function hash(array $structure)
    {
        return sha1(json_encode($structure));
    }
}
