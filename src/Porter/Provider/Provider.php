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
     * @param ProviderDataType $providerDataType
     *
     * @return \Iterator
     */
    public function fetch(ProviderDataType $providerDataType)
    {
        if ($providerDataType->getProviderName() !== static::class) {
            // TODO. Proper exception type.
            throw new \RuntimeException('Cannot fetch data for foreign type: ' . get_class($providerDataType));
        }

        return $providerDataType->fetch($this->connector);
    }
}
