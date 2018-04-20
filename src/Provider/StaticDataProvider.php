<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Provider;

use ScriptFUSION\Porter\Connector\Connector;
use ScriptFUSION\Porter\Connector\NullConnector;

class StaticDataProvider implements Provider
{
    private $connector;

    public function __construct()
    {
        $this->connector = new NullConnector;
    }

    public function getConnector(): Connector
    {
        return $this->connector;
    }
}
