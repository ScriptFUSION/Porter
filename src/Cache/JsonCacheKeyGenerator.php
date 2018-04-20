<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Cache;

class JsonCacheKeyGenerator implements CacheKeyGenerator
{
    public function generateCacheKey(string $source, array $sortedOptions): string
    {
        return str_replace(
            str_split(self::RESERVED_CHARACTERS),
            '.',
            json_encode([$source, $sortedOptions], JSON_UNESCAPED_SLASHES)
        );
    }
}
