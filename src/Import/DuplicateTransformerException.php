<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Import;

/**
 * The exception that is thrown when a transformer already exists at the target site.
 */
final class DuplicateTransformerException extends \RuntimeException
{
    // Intentionally empty.
}
