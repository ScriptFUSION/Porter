<?php
namespace ScriptFUSION\Porter\Cache;

interface CacheKeyGenerator
{
    /**
     * @param string $source
     * @param array $sortedOptions Options sorted by key.
     *
     * @return string A PSR-6 compatible cache key.
     */
    public function generateCacheKey($source, array $sortedOptions);
}
