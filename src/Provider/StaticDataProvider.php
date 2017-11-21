<?php
namespace ScriptFUSION\Porter\Provider;

use ScriptFUSION\Porter\Connector\NullConnector;

class StaticDataProvider implements Provider
{
    private $connector;

    public function __construct()
    {
        $this->connector = new NullConnector;
    }

    public function getConnector($resourceType)
    {
        return $this->connector;
    }
}
