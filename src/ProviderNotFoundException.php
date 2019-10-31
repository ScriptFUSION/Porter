<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter;

/**
 * The exception that is thrown when the specified provider name is not found.
 */
final class ProviderNotFoundException extends \RuntimeException
{
    public function __construct(string $message, \Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
