<?php
namespace ScriptFUSION\Porter\Cache;

class InvalidCacheKeyException extends \RuntimeException implements \Psr\Cache\InvalidArgumentException
{
    // Intentionally empty.
}
