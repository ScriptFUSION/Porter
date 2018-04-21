<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Connector;

use Psr\Cache\CacheItemPoolInterface;
use ScriptFUSION\Porter\Cache\CacheKeyGenerator;
use ScriptFUSION\Porter\Cache\InvalidCacheKeyException;
use ScriptFUSION\Porter\Cache\JsonCacheKeyGenerator;
use ScriptFUSION\Porter\Cache\MemoryCache;

/**
 * Wraps a connector to cache fetched data using PSR-6-compliant objects.
 *
 * TODO: Async support
 */
class CachingConnector implements Connector, ConnectorWrapper
{
    /**
     * @var Connector
     */
    private $connector;

    /**
     * @var CacheItemPoolInterface
     */
    private $cache;

    /**
     * @var CacheKeyGenerator
     */
    private $cacheKeyGenerator;

    public function __construct(
        Connector $connector,
        CacheItemPoolInterface $cache = null,
        CacheKeyGenerator $cacheKeyGenerator = null
    ) {
        $this->connector = $connector;
        $this->cache = $cache ?: new MemoryCache;
        $this->cacheKeyGenerator = $cacheKeyGenerator ?: new JsonCacheKeyGenerator;
    }

    public function __clone()
    {
        $this->connector = clone $this->connector;

        /* It doesn't make sense to clone the cache because we want cache state to be shared between imports.
           We're also not cloning the CacheKeyGenerator because they're expected to be stateless algorithms. */
    }

    /**
     * @param string $source
     * @param ConnectionContext $context
     *
     * @return mixed
     *
     * @throws InvalidCacheKeyException Cache key contains invalid data.
     */
    public function fetch(string $source, ConnectionContext $context)
    {
        if ($context->mustCache()) {
            $options = $this->connector instanceof ConnectorOptions ? $this->connector->getOptions()->copy() : [];
            ksort($options);

            $this->validateCacheKey($key = $this->cacheKeyGenerator->generateCacheKey($source, $options));

            if ($this->cache->hasItem($key)) {
                return $this->cache->getItem($key)->get();
            }
        }

        $data = $this->connector->fetch($source, $context);

        isset($key) && $this->cache->save($this->cache->getItem($key)->set($data));

        return $data;
    }

    /**
     * @throws InvalidCacheKeyException Cache key contains invalid data.
     */
    private function validateCacheKey(string $key): void
    {
        if (strpbrk($key, CacheKeyGenerator::RESERVED_CHARACTERS) !== false) {
            throw new InvalidCacheKeyException(sprintf(
                'Cache key "%s" contains one or more reserved characters: "%s".',
                $key,
                CacheKeyGenerator::RESERVED_CHARACTERS
            ));
        }
    }

    public function getWrappedConnector(): Connector
    {
        return $this->connector;
    }
}
