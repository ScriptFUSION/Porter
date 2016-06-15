<?php
namespace ScriptFUSION\Porter\Cache;

use Psr\Cache\CacheItemInterface;

/**
 * @internal Only this library may create instances of this class.
 */
final class CacheItem implements CacheItemInterface
{
    private $key;

    private $value;

    private $hit;

    private function __construct($key, $value, $hit)
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
        $this->value = $value;

        return $this;
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
