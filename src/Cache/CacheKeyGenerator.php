<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Cache;

interface CacheKeyGenerator
{
    public const RESERVED_CHARACTERS = '{}()/\@:';

    /**
     * @param string $source
     * @param array $sortedOptions Options sorted by key.
     *
     * @return string A PSR-6 compatible cache key.
     */
    public function generateCacheKey(string $source, array $sortedOptions): string;
}
