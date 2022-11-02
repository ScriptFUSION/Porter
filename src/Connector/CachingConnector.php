<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Connector;

use Psr\Cache\CacheItemPoolInterface;
use ScriptFUSION\Porter\Cache\InvalidCacheKeyException;
use ScriptFUSION\Porter\Cache\MemoryCache;

/**
 * Wraps a connector to cache fetched data using PSR-6-compliant objects.
 */
class CachingConnector implements Connector, ConnectorWrapper
{
    public const RESERVED_CHARACTERS = '{}()/\@:';

    private CacheItemPoolInterface $cache;

    public function __construct(
        private Connector $connector,
        CacheItemPoolInterface $cache = null
    ) {
        $this->cache = $cache ?: new MemoryCache;
    }

    public function __clone()
    {
        $this->connector = clone $this->connector;

        /* It doesn't make sense to clone the cache because we want cache state to be shared between imports.
           We're also not cloning the CacheKeyGenerator because they're expected to be stateless algorithms. */
    }

    /**
     * @throws InvalidCacheKeyException Cache key contains invalid data.
     */
    public function fetch(DataSource $source): mixed
    {
        $this->validateCacheKey($key = $source->computeHash());

        if ($this->cache->hasItem($key)) {
            return $this->cache->getItem($key)->get();
        }

        $data = $this->connector->fetch($source);

        isset($key) && $this->cache->save($this->cache->getItem($key)->set($data));

        return $data;
    }

    /**
     * @throws InvalidCacheKeyException Cache key contains invalid data.
     */
    private function validateCacheKey(string $key): void
    {
        if (strpbrk($key, self::RESERVED_CHARACTERS) !== false) {
            throw new InvalidCacheKeyException(sprintf(
                'Cache key "%s" contains one or more reserved characters: "%s".',
                $key,
                self::RESERVED_CHARACTERS
            ));
        }
    }

    public function getWrappedConnector(): Connector
    {
        return $this->connector;
    }
}
