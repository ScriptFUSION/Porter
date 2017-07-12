<?php
namespace ScriptFUSION\Porter\Cache;

/**
 * Defines caching methods.
 */
interface Cache
{
    /**
     * Gets a value indicating whether the cache is available.
     *
     * @return bool True if cache is available, otherwise false.
     */
    public function isCacheAvailable();
}
