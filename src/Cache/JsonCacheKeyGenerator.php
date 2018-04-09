<?php
namespace ScriptFUSION\Porter\Cache;

class JsonCacheKeyGenerator implements CacheKeyGenerator
{
    public function generateCacheKey($source, array $sortedOptions)
    {
        return str_replace(
            str_split(self::RESERVED_CHARACTERS),
            '.',
            json_encode([$source, $sortedOptions], JSON_UNESCAPED_SLASHES)
        );
    }
}
