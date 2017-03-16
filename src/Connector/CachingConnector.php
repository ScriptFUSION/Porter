<?php
namespace ScriptFUSION\Porter\Connector;

use Psr\Cache\CacheItemPoolInterface;
use ScriptFUSION\Porter\Cache\CacheKeyGenerator;
use ScriptFUSION\Porter\Cache\CacheToggle;
use ScriptFUSION\Porter\Cache\InvalidCacheKeyException;
use ScriptFUSION\Porter\Cache\JsonCacheKeyGenerator;
use ScriptFUSION\Porter\Cache\MemoryCache;
use ScriptFUSION\Porter\Options\EncapsulatedOptions;

/**
 * Caches remote data using PSR-6-compliant objects.
 */
abstract class CachingConnector implements Connector, CacheToggle
{
    const RESERVED_CHARACTERS = '{}()/\@:';

    /**
     * @var CacheItemPoolInterface
     */
    private $cache;

    /**
     * @var bool
     */
    private $cacheEnabled = true;

    /**
     * @var CacheKeyGenerator
     */
    private $cacheKeyGenerator;

    public function __construct(CacheItemPoolInterface $cache = null, CacheKeyGenerator $cacheKeyGenerator = null)
    {
        $this->cache = $cache ?: new MemoryCache;
        $this->cacheKeyGenerator = $cacheKeyGenerator ?: new JsonCacheKeyGenerator;
    }

    /**
     * @param string $source
     * @param EncapsulatedOptions|null $options
     *
     * @return mixed
     *
     * @throws InvalidCacheKeyException
     */
    public function fetch($source, EncapsulatedOptions $options = null)
    {
        if ($this->isCacheEnabled()) {
            $optionsCopy = $options ? $options->copy() : [];

            ksort($optionsCopy);

            $key = $this->validateCacheKey($this->getCacheKeyGenerator()->generateCacheKey($source, $optionsCopy));

            if ($this->cache->hasItem($key)) {
                return $this->cache->getItem($key)->get();
            }
        }

        $data = $this->fetchFreshData($source, $options);

        isset($key) && $this->cache->save($this->cache->getItem($key)->set($data));

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

    public function getCacheKeyGenerator()
    {
        return $this->cacheKeyGenerator;
    }

    public function setCacheKeyGenerator(CacheKeyGenerator $cacheKeyGenerator)
    {
        $this->cacheKeyGenerator = $cacheKeyGenerator;
    }

    /**
     * @param mixed $key
     *
     * @return string
     *
     * @throws InvalidCacheKeyException Cache key contains invalid data.
     */
    private function validateCacheKey($key)
    {
        if (!is_string($key)) {
            throw new InvalidCacheKeyException('Cache key must be a string.');
        }

        if (strpbrk($key, self::RESERVED_CHARACTERS) !== false) {
            throw new InvalidCacheKeyException(sprintf(
                'Cache key "%s" contains one or more reserved characters: "%s".',
                $key,
                self::RESERVED_CHARACTERS
            ));
        }

        return $key;
    }
}
