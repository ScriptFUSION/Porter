<?php
namespace ScriptFUSION\Porter\Connector;

class HttpConnector implements Connector
{
    public function fetch($destination, array $parameters = [])
    {
        return file_get_contents($destination);
    }
}
