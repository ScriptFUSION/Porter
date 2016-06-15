<?php
namespace ScriptFUSION\Porter\Cache;

use Psr\Cache\CacheItemInterface;

final class CacheItem implements CacheItemInterface
{
    private $key;

    private $value;

    private $hit;

    public function __construct($key, $value, $hit = false)
    {
        $this->key = "$key";
        $this->value = $value;
        $this->hit = (bool)$hit;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function get()
    {
        return $this->value;
    }

    public function isHit()
    {
        return $this->hit;
    }

    public function set($value)
    {
        throw new CacheOperationProhibitedException('Cannot directly modify an item\'s value.');
    }

    public function expiresAt($expiration)
    {
        // TODO: Implement expiresAt() method.
    }

    public function expiresAfter($time)
    {
        // TODO: Implement expiresAfter() method.
    }
}
