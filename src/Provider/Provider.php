<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Provider;

use ScriptFUSION\Porter\Connector\Connector;

/**
 * Provides a method for accessing a connector.
 */
interface Provider
{
    /**
     * Gets a connector for accessing resource data.
     */
    public function getConnector(): Connector;
}
