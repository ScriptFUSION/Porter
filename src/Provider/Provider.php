<?php
namespace ScriptFUSION\Porter\Provider;

use ScriptFUSION\Porter\Connector\Connector;

/**
 * Provides a method for getting a connector.
 */
interface Provider
{
    /**
     * Gets a connector for fetching resource data.
     *
     * @param string $resourceType The resource type from which data will be fetched.
     *
     * @return Connector
     */
    public function getConnector($resourceType);
}
