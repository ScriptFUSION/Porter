<?php
namespace ScriptFUSION\Porter\Cache;

use Psr\Cache\CacheException;

/**
 * The exception that is thrown when cache is unavailable.
 */
class CacheUnavailableException extends \RuntimeException implements CacheException
{
    public static function unsupported()
    {
        return new self('Cannot cache: connector does not support caching.');
    }

    public static function unavailable()
    {
        return new self('Cannot cache: connector reported cache currently unavailable.');
    }
}
