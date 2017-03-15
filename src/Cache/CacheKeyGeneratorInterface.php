<?php

namespace ScriptFUSION\Porter\Cache;

use ScriptFUSION\Porter\Options\EncapsulatedOptions;

interface CacheKeyGeneratorInterface
{
    /**
     * @param string $source
     * @param EncapsulatedOptions|null $options
     * @return string A PSR-6 compatible cache key.
     */
    public function generateCacheKey($source, EncapsulatedOptions $options = null);
}
