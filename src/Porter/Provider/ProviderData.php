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
     * @param Connector $connector
     *
     * @return \Iterator
     */
    public function fetch(Connector $connector);
}
