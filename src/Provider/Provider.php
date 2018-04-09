<?php
namespace ScriptFUSION\Porter\Provider;

use ScriptFUSION\Porter\Connector\Connector;

/**
 * Provides a method for accessing a connector.
 */
interface Provider
{
    /**
     * Gets a connector for accessing resource data.
     *
     * @return Connector
     */
    public function getConnector();
}
