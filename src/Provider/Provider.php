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
     * Gets a connector compatible with this provider's resources.
     *
     * NB: this should not be a factory method as it will break things like caching.
     */
    public function getConnector(): Connector;
}
