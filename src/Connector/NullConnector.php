<?php
namespace ScriptFUSION\Porter\Connector;

class NullConnector implements Connector
{
    public function fetch(ConnectionContext $context, $source)
    {
        // Intentionally empty.
    }
}
