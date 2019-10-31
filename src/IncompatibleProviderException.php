<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter;

/**
 * The exception that is thrown when a provider is incompatible with an import operation because it only supports
 * sync during an async import or vice-versa.
 */
final class IncompatibleProviderException extends \LogicException
{
    public function __construct(string $expectedType)
    {
        parent::__construct("Provider does not implement $expectedType.");
    }
}
