<?php
namespace ScriptFUSION\Porter\Connector;

use Psr\Cache\CacheItemPoolInterface;
use ScriptFUSION\Porter\Cache\CacheKeyGeneratorInterface;
use ScriptFUSION\Porter\Cache\CacheToggle;
use ScriptFUSION\Porter\Cache\InvalidCacheKeyException;
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

    /**
     * @param string $source
     * @param EncapsulatedOptions|null $options
     * @param CacheKeyGeneratorInterface $cacheKeyGenerator
     * @return mixed
     * @throws InvalidCacheKeyException
     */
    public function fetch(
        $source,
        EncapsulatedOptions $options = null,
        CacheKeyGeneratorInterface $cacheKeyGenerator = null
    ) {
        $optionsCopy = $options ? $options->copy() : [];

        $key = null;

        if ($this->isCacheEnabled()) {
            ksort($optionsCopy);

            if ($cacheKeyGenerator !== null) {
                $key = $cacheKeyGenerator->generateCacheKey($source, $options);
                if (!is_string($key)) {
                    throw new InvalidCacheKeyException('Cache key must be of type string.');
                }
                if (strpbrk($key, '{}()/\@:') !== false) {
                    throw new InvalidCacheKeyException(sprintf(
                        'Cache key "%s" contains reserved characters {}()/\@:',
                        $key
                    ));
                }
            } else {
                $key = $this->hash([$source, $optionsCopy]);
            }

            if ($this->cache->hasItem($key)) {
                return $this->cache->getItem($key)->get();
            }
        }

        $data = $this->fetchFreshData($source, $options);

        $key !== null && $this->cache->save($this->cache->getItem($key)->set($data));

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
