<?php
namespace ScriptFUSION\Porter\Cache;

class InvalidCacheKeyException extends \DomainException implements \Psr\Cache\InvalidArgumentException
{
    // Intentionally empty.
}
