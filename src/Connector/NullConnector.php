<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Connector;

final class NullConnector implements Connector
{
    public function fetch(string $source)
    {
        // Intentionally empty.
    }
}
