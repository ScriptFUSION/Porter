<?php
namespace ScriptFUSION\Porter\Cache;

/**
 * Defines methods for getting and setting whether a cache is enabled.
 */
interface CacheToggle
{
    /**
     * Enables the cache, permitting subsequent cache access operations.
     *
     * @return void
     */
    public function enableCache();

    /**
     * Disables the cache, prohibiting subsequent cache access operations.
     *
     * @return void
     */
    public function disableCache();

    /**
     * Gets a value indicating whether the cache is enabled.
     *
     * @return bool True if cache is enabled, otherwise false.
     */
    public function isCacheEnabled();
}
