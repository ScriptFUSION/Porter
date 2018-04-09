<?php
namespace ScriptFUSION\Porter\Cache;

use Psr\Cache\CacheException;

/**
 * The exception that is thrown when cache is unavailable.
 */
final class CacheUnavailableException extends \RuntimeException implements CacheException
{
    public static function createUnsupported()
    {
        return new self('Cannot cache: connector does not support caching.');
    }
}
