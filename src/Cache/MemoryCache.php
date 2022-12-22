<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Cache;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Provides an in-memory cache with a PSR-6 interface.
 */
final class MemoryCache extends \ArrayObject implements CacheItemPoolInterface
{
    /**
     * @param string $key
     */
    public function getItem($key): CacheItemInterface
    {
        return \Closure::bind(
            function () use ($key): self {
                return new self($key, $this->hasItem($key) ? $this[$key] : null, $this->hasItem($key));
            },
            $this,
            CacheItem::class
        )();
    }

    public function getItems(array $keys = []): iterable
    {
        foreach ($keys as $key) {
            yield $this->getItem($key);
        }
    }

    public function hasItem($key): bool
    {
        return isset($this[$key]);
    }

    public function clear(): bool
    {
        $this->exchangeArray([]);

        return true;
    }

    public function deleteItem($key): bool
    {
        unset($this[$key]);

        return true;
    }

    public function deleteItems(array $keys): bool
    {
        foreach ($keys as $key) {
            if (!$this->hasItem($key)) {
                throw new InvalidArgumentException("No such key in cache: \"$key\".");
            }

            $this->deleteItem($key);
        }

        return true;
    }

    public function save(CacheItemInterface $item): bool
    {
        $this[$item->getKey()] = $item->get();

        return true;
    }

    public function saveDeferred(CacheItemInterface $item): bool
    {
        $this->save($item);

        return true;
    }

    public function commit(): bool
    {
        return true;
    }
}
