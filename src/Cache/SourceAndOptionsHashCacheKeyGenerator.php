<?php
namespace ScriptFUSION\Porter\Cache;

use ScriptFUSION\Porter\Options\EncapsulatedOptions;

class SourceAndOptionsHashCacheKeyGenerator implements CacheKeyGenerator
{
    public function generateCacheKey($source, EncapsulatedOptions $options = null)
    {
        $optionsCopy = $options ? $options->copy() : [];
        ksort($optionsCopy);
        return str_replace(str_split('{}()/\@:'), '.', json_encode([$source, $optionsCopy], JSON_UNESCAPED_SLASHES));
    }
}
