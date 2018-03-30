<?php
namespace ScriptFUSION\Porter\Connector;

use Psr\Cache\CacheItemPoolInterface;
use ScriptFUSION\Porter\Cache\CacheKeyGenerator;
use ScriptFUSION\Porter\Cache\InvalidCacheKeyException;
use ScriptFUSION\Porter\Cache\JsonCacheKeyGenerator;
use ScriptFUSION\Porter\Cache\MemoryCache;

/**
 * Wraps a connector to cache fetched data using PSR-6-compliant objects.
 */
class CachingConnector implements Connector
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

    /**
     * @param ConnectionContext $context
     * @param string $source
     *
     * @return mixed
     *
     * @throws InvalidCacheKeyException
     */
    public function fetch(ConnectionContext $context, $source)
    {
        if ($context->mustCache()) {
            $options = $this->connector instanceof ConnectorOptions ? $this->connector->getOptions()->copy() : [];
            ksort($options);

            $this->validateCacheKey($key = $this->cacheKeyGenerator->generateCacheKey($source, $options));

            if ($this->cache->hasItem($key)) {
                return $this->cache->getItem($key)->get();
            }
        }

        $data = $this->connector->fetch($context, $source);

        isset($key) && $this->cache->save($this->cache->getItem($key)->set($data));

        return $data;
    }

    /**
     * @param mixed $key
     *
     * @return void
     *
     * @throws InvalidCacheKeyException Cache key contains invalid data.
     */
    private function validateCacheKey($key)
    {
        // TODO: Remove when PHP 5 support dropped and replace with string hint.
        if (!is_string($key)) {
            throw new InvalidCacheKeyException('Cache key must be a string.');
        }

        if (strpbrk($key, CacheKeyGenerator::RESERVED_CHARACTERS) !== false) {
            throw new InvalidCacheKeyException(sprintf(
                'Cache key "%s" contains one or more reserved characters: "%s".',
                $key,
                CacheKeyGenerator::RESERVED_CHARACTERS
            ));
        }
    }
}
