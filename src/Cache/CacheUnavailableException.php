<?php
namespace ScriptFUSION\Porter\Cache;

use Psr\Cache\CacheException;

/**
 * The exception that is thrown when cache is unavailable.
 */
class CacheUnavailableException extends \RuntimeException implements CacheException
{
    public static function modify()
    {
        return new self('Cannot modify cache: cache unavailable.');
    }
}
