<?php
namespace ScriptFUSION\Porter\Cache;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Provides an in-memory cache with a PSR-6 interface.
 */
class MemoryCache extends \ArrayObject implements CacheItemPoolInterface
{
    public function getItem($key)
    {
        return call_user_func(
            \Closure::bind(
                function () use ($key) {
                    return new self($key, $this->hasItem($key) ? $this[$key] : null, $this->hasItem($key));
                },
                $this,
                CacheItem::class
            )
        );
    }

    public function getItems(array $keys = [])
    {
        foreach ($keys as $key) {
            yield $this->getItem($key);
        }
    }

    public function hasItem($key)
    {
        return isset($this[$key]);
    }

    public function clear()
    {
        $this->exchangeArray([]);
    }

    public function deleteItem($key)
    {
        unset($this[$key]);
    }

    public function deleteItems(array $keys)
    {
        foreach ($keys as $key) {
            if (!$this->hasItem($key)) {
                throw new InvalidArgumentException("No such key in cache: \"$key\".");
            }

            $this->deleteItem($key);
        }
    }

    public function save(CacheItemInterface $item)
    {
        $this[$item->getKey()] = $item->get();
    }

    public function saveDeferred(CacheItemInterface $item)
    {
        $this->save($item);
    }

    public function commit()
    {
        return true;
    }
}
