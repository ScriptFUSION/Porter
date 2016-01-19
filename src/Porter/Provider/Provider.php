<?php
namespace ScriptFUSION\Porter\Provider;

use ScriptFUSION\Porter\Connector\Connector;

abstract class Provider
{
    private $connector;

    public function __construct(Connector $connector)
    {
        $this->connector = $connector;
    }

    /**
     * @param ProviderData $data
     *
     * @return \Iterator
     */
    public function fetch(ProviderData $data)
    {
        return $data->fetch($this->getConnector());
    }

    /**
     * @return Connector
     */
    public function getConnector()
    {
        return $this->connector;
    }
}
