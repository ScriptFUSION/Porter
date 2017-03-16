<?php
namespace ScriptFUSION\Porter\Cache;

interface CacheKeyGenerator
{
    /**
     * @param string $source
     * @param array $optionsSorted Key sorted options.
     *
     * @return string A PSR-6 compatible cache key.
     */
    public function generateCacheKey($source, array $optionsSorted);
}
