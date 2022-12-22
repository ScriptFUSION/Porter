<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Cache;

use Psr\Cache\CacheItemInterface;

/**
 * @internal Only this library may create instances of this class.
 */
final class CacheItem implements CacheItemInterface
{
    private function __construct(private readonly string $key, private mixed $value, private readonly bool $hit)
    {
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function get(): mixed
    {
        return $this->value;
    }

    public function set(mixed $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function isHit(): bool
    {
        return $this->hit;
    }

    public function expiresAt($expiration): static
    {
        throw new NotImplementedException;
    }

    public function expiresAfter($time): static
    {
        throw new NotImplementedException;
    }
}
