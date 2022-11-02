<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Provider;

use ScriptFUSION\Porter\Connector\Connector;
use ScriptFUSION\Porter\Connector\NullConnector;

class StaticDataProvider implements Provider
{
    public function __construct(private readonly Connector $connector = new NullConnector)
    {
    }

    public function getConnector(): Connector
    {
        return $this->connector;
    }
}
