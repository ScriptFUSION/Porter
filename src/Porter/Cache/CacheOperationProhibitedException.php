<?php
namespace ScriptFUSION\Porter\Cache;

use Psr\Cache\CacheException;

/**
 * The exception that is thrown when a cache operation is prohibited.
 */
class CacheOperationProhibitedException extends \RuntimeException implements CacheException
{
    // Intentionally empty.
}
