<?php
namespace ScriptFUSION\Porter\Cache;

use ScriptFUSION\Porter\Connector\CachingConnector;

class JsonCacheKeyGenerator implements CacheKeyGenerator
{
    public function generateCacheKey($source, array $sortedOptions)
    {
        return str_replace(str_split(CachingConnector::RESERVED_CHARACTERS), '.', json_encode([$source, $sortedOptions], JSON_UNESCAPED_SLASHES));
    }
}
