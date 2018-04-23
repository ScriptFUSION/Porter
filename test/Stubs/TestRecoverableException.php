<?php
declare(strict_types=1);

namespace ScriptFUSIONTest\Stubs;

use ScriptFUSION\Porter\Connector\Recoverable\RecoverableException;

final class TestRecoverableException extends \Exception implements RecoverableException
{
    // Intentionally empty.
}
