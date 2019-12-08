<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Cache;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Provides an in-memory cache with a PSR-6 interface.
 */
class MemoryCache extends \ArrayObject implements CacheItemPoolInterface
{
    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getItem($key)
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

    public function clear(): void
    {
        $this->exchangeArray([]);
    }

    public function deleteItem($key): void
    {
        unset($this[$key]);
    }

    public function deleteItems(array $keys): void
    {
        foreach ($keys as $key) {
            if (!$this->hasItem($key)) {
                throw new InvalidArgumentException("No such key in cache: \"$key\".");
            }

            $this->deleteItem($key);
        }
    }

    public function save(CacheItemInterface $item): void
    {
        $this[$item->getKey()] = $item->get();
    }

    public function saveDeferred(CacheItemInterface $item): void
    {
        $this->save($item);
    }

    public function commit(): bool
    {
        return true;
    }
}
