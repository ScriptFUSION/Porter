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
     * @return ProviderName
     */
    abstract public function getName();

    /**
     * @param ProviderDataType $dataType
     * @param array $parameters
     *
     * @return \Iterator
     */
    public function fetch(ProviderDataType $dataType, array $parameters = [])
    {
        throw new \LogicException("Unhandled type: $dataType.");
    }

    /**
     * @return Connector
     */
    public function getConnector()
    {
        return $this->connector;
    }
}
