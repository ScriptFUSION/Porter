<?php
namespace ScriptFUSION\Porter\Cache;

use Eloquent\Enumeration\AbstractEnumeration;

/**
 * Specifies cache behaviour.
 */
final class CacheAdvice extends AbstractEnumeration
{
    /**
     * @var string An object should be cached if a cache is available.
     */
    const SHOULD_CACHE = 'SHOULD_CACHE';

    /**
     * @var string An object should not be cached even if a cache is available.
     */
    const SHOULD_NOT_CACHE = 'SHOULD_NOT_CACHE';

    /**
     * @var string An object must be cached otherwise a fatal error may occur.
     */
    const MUST_CACHE = 'MUST_CACHE';

    /**
     * @var string An object must not be cached otherwise a fatal error may occur.
     */
    const MUST_NOT_CACHE = 'MUST_NOT_CACHE';
}
