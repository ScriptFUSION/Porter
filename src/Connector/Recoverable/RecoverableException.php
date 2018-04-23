<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Connector\Recoverable;

/**
 * The exception that is thrown when a recoverable (non-fatal) error occurs, indicating the operation may be retried.
 */
interface RecoverableException extends \Throwable
{
    // Intentionally empty marker interface.
}
