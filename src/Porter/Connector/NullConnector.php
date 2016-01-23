<?php
namespace ScriptFUSION\Porter\Connector;

class NullConnector implements Connector
{
    public function fetch($destination, array $parameters = [])
    {
        // Intentionally empty.
    }
}
