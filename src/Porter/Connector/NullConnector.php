<?php
namespace ScriptFUSION\Porter\Connector;

class NullConnector implements Connector
{
    public function fetch($source, array $parameters = [])
    {
        // Intentionally empty.
    }
}
