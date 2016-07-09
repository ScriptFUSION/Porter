<?php
namespace ScriptFUSION\Porter\Provider;

use ScriptFUSION\Porter\Connector\NullConnector;

class StaticDataProvider extends AbstractProvider
{
    public function __construct()
    {
        parent::__construct(new NullConnector);
    }
}
