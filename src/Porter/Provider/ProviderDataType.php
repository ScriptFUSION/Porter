<?php
namespace ScriptFUSION\Porter\Provider;

use ScriptFUSION\Porter\Connector\Connector;

/**
 * Specifies how to fetch a type of data from the specified provider.
 */
interface ProviderDataType
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
