<?php
namespace ScriptFUSION\Porter\Cache;

interface CacheEnabler
{
    /**
     * @return void
     */
    public function enableCache();

    /**
     * @return void
     */
    public function disableCache();

    /**
     * @return bool
     */
    public function isCacheEnabled();
}
