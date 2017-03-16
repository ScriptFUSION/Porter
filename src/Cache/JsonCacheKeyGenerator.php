<?php
namespace ScriptFUSION\Porter\Cache;

class JsonCacheKeyGenerator implements CacheKeyGenerator
{
    public function generateCacheKey($source, array $optionsSorted)
    {
        return str_replace(str_split('{}()/\@:'), '.', json_encode([$source, $optionsSorted], JSON_UNESCAPED_SLASHES));
    }
}
