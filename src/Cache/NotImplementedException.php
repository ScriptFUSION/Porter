<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Cache;

use Psr\Cache\CacheException;

final class NotImplementedException extends \LogicException implements CacheException
{
    // Intentionally empty.
}
