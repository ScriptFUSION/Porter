<?php
namespace ScriptFUSION\Porter\Provider;

use ScriptFUSION\Porter\Connector\Connector;

interface ProviderData
{
    /**
     * @return string
     */
    public function getProviderName();

    /**
     * @return \Iterator
     */
    public function fetch(Connector $connector);
}
