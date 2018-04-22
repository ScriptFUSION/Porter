<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Connector;

/**
 * The exception that is thrown when a recoverable (non-fatal) error occurs during Connector::fetch.
 */
class RecoverableConnectorException extends \RuntimeException
{
    // Intentionally empty.
}
