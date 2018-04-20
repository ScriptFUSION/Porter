<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Cache;

final class InvalidArgumentException extends \InvalidArgumentException implements \Psr\Cache\InvalidArgumentException
{
    // Intentionally empty.
}
